<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Netflix;
use App\Services\InboundMailService;
use DateInterval;
use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CodeController extends Controller
{
    const API_URL = "https://api.mail.tm/";

    public function search(Request $request)
    {
        $email = mb_strtolower(trim((string) $request->email), 'UTF-8');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => 'Email không hợp lệ'], 400);
        }

        // email trong DB có thể dư khoảng trắng / khác hoa thường (nhập từ Excel)
        $account = Netflix::query()->whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        // chỉ lấy mail trong vòng 15 phút (giờ VN)
        $now = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $cutoff = clone $now;
        $cutoff->sub(new DateInterval('PT15M'));

        if ($account != null) {
            // json_encode trong getToken đã tự escape, không escape thêm ở đây
            $tokenResp = $this->getToken([
                'address' => $email,
                'password' => trim((string) $account['password']),
            ]);
            $tokenValue = $tokenResp ? (json_decode($tokenResp)->token ?? null) : null;
            if (!$tokenValue) {
                return response()->json(['success' => false, 'message' => 'Không đăng nhập được hộp thư (sai mật khẩu hoặc email không thuộc mail.tm)'], 401);
            }
            $messages = $this->listMailTmMessages($tokenValue);
        } else {
            // Không có trong danh sách tài khoản mail.tm -> lấy từ hộp inbound-mail
            $messages = $this->listInboundMessages($email, $cutoff);
        }

        usort($messages, function ($a, $b) {
            return strtotime($b['createdAt'] ?? '') - strtotime($a['createdAt'] ?? '');
        });

        $items = [];
        foreach ($messages as $item) {
            $fromName = $item['fromName'];
            $fromAddr = $item['fromAddr'];
            $toAddr = $item['toAddr'];
            $messageId = $item['id'];
            $createdAtRaw = $item['createdAt'];
            $subject = $item['subject'];

            $isNetflix = mb_stripos($fromName, 'netflix', 0, 'UTF-8') !== false
                || mb_stripos($fromAddr, 'netflix', 0, 'UTF-8') !== false;
            if (!($isNetflix && $toAddr && mb_strtolower(trim($toAddr), 'UTF-8') === $email)) continue;
            if (!$messageId) continue;
            if (!$createdAtRaw) continue;

            try {
                $createdAt = new DateTime($createdAtRaw, new DateTimeZone('UTC'));
                $createdAt->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
            } catch (Exception $e) {
                continue;
            }

            if ($createdAt < $cutoff) continue;

            $html = ($item['fetchHtml'])();
            if (!$html) continue;

            $verifyLink = $this->extractNetflixVerifyLink($html);

            if ($verifyLink) {
                // Mail có nút "Lấy mã" (truy cập hộ gia đình: travel/verify,
                // update-primary-location). Nút đã đủ đặc trưng nên LUÔN hiển
                // thị, KHÔNG áp bộ lọc tiêu đề 15 phút - tránh bỏ mất mail nút
                // mà tiêu đề không nói "15 phút".
                $code = null;
            } else {
                // Không có nút -> chỉ nhận mail "Mã xác minh. Hết hạn sau 15
                // phút." (và bản dịch) kèm mã 6 số. Xét tiêu đề SAU mốc 15 phút
                // để log tiêu đề-chưa-khớp chỉ ghi mail mới, khỏi ồn.
                if (!$this->isVerifySubject((string) $subject)) continue;

                $code = $this->extractNetflixVerifyCode($html);
                if (!$code) continue;
            }

            $items[] = [
                'id' => $messageId,
                'subject' => $subject,
                'createdAt' => $createdAtRaw,
                'verifyLink' => $verifyLink,
                'code' => $code,
            ];

            // lấy 10 mail mới nhất có link/mã để tránh quá tải
            if (count($items) >= 10) break;
        }

        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => $items,
        ]);
    }

    /**
     * Tiêu đề có phải mail "Mã xác minh. Hết hạn sau 15 phút." hay không.
     *
     * Netflix gửi tiêu đề theo ngôn ngữ của từng tài khoản nên không so khớp
     * được một chuỗi cố định. Danh sách từ/tiêu đề nằm ở config/netflix.php.
     */
    private function isVerifySubject(string $subject): bool
    {
        // Gộp khoảng trắng + bỏ hoa thường để tiêu đề có xuống dòng / 2 dấu cách
        // vẫn khớp được.
        $normalized = mb_strtolower(
            trim(preg_replace('/\s+/u', ' ', $subject) ?? ''),
            'UTF-8'
        );

        if ($normalized === '') {
            return false;
        }

        $config = config('netflix.verify_subject', []);

        // 1. Mail "Mã đăng nhập" cũng hết hạn sau 15 phút -> phải loại trước
        //    luật (3), nếu không sẽ lọt.
        foreach ((array) ($config['exclude'] ?? []) as $word) {
            if ($this->subjectContains($normalized, $word)) {
                return false;
            }
        }

        // 2. Tiêu đề nguyên văn đã biết.
        foreach ((array) ($config['exact'] ?? []) as $exact) {
            if ($this->subjectContains($normalized, $exact)) {
                return true;
            }
        }

        // 3. Lưới an toàn không phụ thuộc ngôn ngữ: có số 15 + một từ nghĩa là "phút".
        if (preg_match('/(?<!\d)15(?!\d)/u', $normalized) === 1) {
            foreach ((array) ($config['minute_words'] ?? []) as $word) {
                if ($this->subjectContains($normalized, $word)) {
                    return true;
                }
            }
        }

        // Ghi lại tiêu đề chưa khớp để biết Netflix dùng câu gì ở ngôn ngữ đó
        // rồi bổ sung vào config/netflix.php -> verify_subject.exact
        Log::info('netflix: tieu de bi loai', ['subject' => $subject]);

        return false;
    }

    /** So khớp kiểu "chứa", đã chuẩn hoá sẵn phía gọi. */
    private function subjectContains(string $normalizedSubject, string $needle): bool
    {
        $needle = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $needle) ?? ''), 'UTF-8');

        return $needle !== '' && mb_strpos($normalizedSubject, $needle, 0, 'UTF-8') !== false;
    }

    /**
     * Mail của một tài khoản mail.tm, đưa về cùng dạng với listInboundMessages().
     */
    private function listMailTmMessages(string $token): array
    {
        $decoded = $this->getJson(self::API_URL . "messages", $token);
        $members = $decoded['hydra:member'] ?? null;
        if (!is_array($members)) return [];

        $messages = [];
        foreach ($members as $member) {
            if (!is_array($member)) continue;
            $messageId = $member['@id'] ?? null;

            $messages[] = [
                'id' => $messageId,
                'subject' => (string) ($member['subject'] ?? ''),
                'createdAt' => $member['createdAt'] ?? null,
                'fromName' => (string) ($member['from']['name'] ?? ''),
                'fromAddr' => (string) ($member['from']['address'] ?? ''),
                'toAddr' => $member['to'][0]['address'] ?? null,
                'fetchHtml' => fn () => $messageId
                    ? $this->extractHtml($this->getJson("https://api.mail.tm" . $messageId, $token))
                    : null,
            ];
        }

        return $messages;
    }

    /**
     * Mail đổ về hộp inbound-mail (Cloudflare Email Routing) - dùng cho email
     * không có trong danh sách tài khoản mail.tm.
     *
     * Bộ lọc "to" của API chỉ so với envelope_to (luôn là hộp chung), nên phải
     * tự lọc theo người nhận thật - xem InboundMailService::recipientOf().
     * Mail mới nhất trả về trước nên lật trang tới khi qua mốc $cutoff.
     */
    private function listInboundMessages(string $email, DateTime $cutoff): array
    {
        $inbound = app(InboundMailService::class);
        $emailDomain = substr(strrchr($email, '@'), 1);

        $messages = [];
        $before = null;
        for ($page = 0; $page < 5; $page++) {
            $result = $inbound->list(null, $before, 100);
            $oldest = null;

            foreach ($result['items'] as $mail) {
                if (!is_array($mail)) continue;
                $messageId = $mail['id'] ?? null;
                $createdAt = $mail['received_at'] ?? $mail['sent_at'] ?? null;
                $oldest = $createdAt ?? $oldest;

                $direct = $inbound->sameAddress($inbound->recipientOf($mail), $email);
                // Mail Cloudflare chuyển tiếp từ đúng domain của $email: danh
                // sách không có người nhận thật, để fetchHtml mở mail rồi so
                // header to. search() chỉ gọi fetchHtml cho mail Netflix còn
                // trong mốc thời gian nên không phải mở cả trăm mail.
                $viaDomain = !$direct && $inbound->forwardedDomainOf($mail) === $emailDomain;
                if (!$direct && !$viaDomain) continue;

                $messages[] = [
                    'id' => $messageId,
                    'subject' => (string) ($mail['subject'] ?? ''),
                    'createdAt' => $createdAt,
                    'fromName' => (string) ($mail['from_name'] ?? ''),
                    'fromAddr' => (string) ($mail['from_address'] ?? ''),
                    // đã khớp người nhận ở trên (có bỏ dấu chấm/+tag của Gmail),
                    // mail chuyển tiếp theo domain thì khớp trong fetchHtml
                    'toAddr' => $email,
                    'fetchHtml' => function () use ($inbound, $messageId, $email, $viaDomain) {
                        if (!$messageId) return null;
                        $full = $inbound->find($messageId);
                        if ($viaDomain && (!$full || !$inbound->isAddressedTo($full, $email))) return null;
                        return $this->extractHtml($full);
                    },
                ];
            }

            $before = $result['next_before'];
            if (!$before || !$oldest || strtotime($oldest) < $cutoff->getTimestamp()) break;
        }

        return $messages;
    }

    private function getJson(string $url, string $token): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($ch);
        curl_close($ch);

        if (!$body) return null;
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function extractHtml(?array $decoded): ?string
    {
        if (!$decoded) return null;

        $html = $decoded['html'] ?? null;
        if (is_array($html)) $html = $html[0] ?? null;
        if (!is_string($html) || $html === '') return null;

        return $html;
    }

    private function extractNetflixVerifyLink(string $html): ?string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        // travel/verify: "Mã truy cập Netflix tạm thời của bạn" (nút "Nhận mã").
        // update-primary-location: "Lưu ý quan trọng: Cách cập nhật Hộ gia đình
        // Netflix" (nút "Đúng, đây là tôi").
        $nodes = $xpath->query(
            '//a[contains(@href,"netflix.com/account/travel/verify")'
            . ' or contains(@href,"netflix.com/account/update-primary-location")]'
        );
        if (!$nodes || $nodes->length === 0) return null;

        $node = $nodes->item(0);
        if (!$node instanceof DOMElement) return null;
        $href = $node->getAttribute('href');
        return $href !== '' ? $href : null;
    }

    private function extractNetflixVerifyCode(string $html): ?string
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//td[contains(@class,"lrg-number")]');
        if (!$nodes || $nodes->length === 0) return null;

        $text = trim($nodes->item(0)->textContent ?? '');
        // chỉ lấy code đúng 6 số; code 4 số (hoặc độ dài khác) bỏ qua, không hiển thị
        if ($text !== '' && preg_match('/(?<!\d)\d{6}(?!\d)/', $text, $m)) {
            return $m[0];
        }
        return null;
    }

    public function getToken(array $accounts)
    {
        $url = 'https://api.mail.tm/token';

        // Dữ liệu cần gửi
        $data = [
            'address' => $accounts['address'],
            'password' => $accounts['password']
        ];

        // Khởi tạo CURL
        $ch = curl_init($url);

        // Thiết lập các tùy chọn
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        // Gửi dữ liệu JSON
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Thực thi CURL và lấy kết quả
        $response = curl_exec($ch);

        // Đóng CURL
        curl_close($ch);
        return $response;
    }
}
