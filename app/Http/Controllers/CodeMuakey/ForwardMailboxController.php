<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Models\ForwardMailbox;
use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ForwardMailboxController extends Controller
{
    const API_URL = 'https://api.mail.tm/';

    public function index()
    {
        $mailboxes = ForwardMailbox::orderBy('id')->get();
        return view('code-muakey.tools.forward-mailbox.index', compact('mailboxes'));
    }

    /**
     * Đăng nhập lại từng hòm bằng mật khẩu (bỏ qua token cache) rồi đọc thử
     * hộp thư, báo hòm nào đọc được / sai mật khẩu / không lấy được token.
     * Token lấy được thì ghi đè cache để lượt tra mã dùng luôn.
     */
    public function check()
    {
        $mailboxes = ForwardMailbox::orderBy('id')->get();
        $results = [];

        // mail.tm giới hạn ~8 request/giây mỗi IP nên chia lô, nghỉ giữa các lô
        foreach ($mailboxes->chunk(5) as $i => $chunk) {
            if ($i > 0) sleep(1);
            $chunk = $chunk->values();

            // as($j): kết quả pool đánh số lại từ 0 nên phải đặt tên để khớp đúng hòm
            $logins = Http::pool(fn (Pool $pool) => $chunk->map(
                fn ($mailbox, $j) => $pool->as((string) $j)->acceptJson()->timeout(15)->post(self::API_URL . 'token', [
                    'address' => $mailbox->address,
                    'password' => $mailbox->password,
                ])
            )->all());

            $tokens = [];
            foreach ($chunk as $j => $mailbox) {
                $response = $logins[$j] ?? null;
                $token = $response instanceof Response ? $response->json('token') : null;

                if (is_string($token) && $token !== '') {
                    $tokens[$j] = $token;
                    Cache::put(ForwardMailbox::tokenCacheKey($mailbox->address), $token, now()->addDay());
                    continue;
                }

                $status = $response instanceof Response ? $response->status() : null;
                $results[$mailbox->id] = [
                    'ok' => false,
                    'message' => $status === 401
                        ? 'Sai mật khẩu (mail.tm trả 401)'
                        : 'Không lấy được token: ' . ($status ? "HTTP {$status}" : 'lỗi kết nối')
                            . ($status === 429 ? ' - bị giới hạn, bấm kiểm tra lại sau' : ''),
                ];
            }

            if (!$tokens) continue;

            $reads = Http::pool(fn (Pool $pool) => array_map(
                // không acceptJson: mail.tm sẽ trả mảng trơn, mất hydra:member
                fn ($j) => $pool->as((string) $j)->withToken($tokens[$j])->timeout(15)->get(self::API_URL . 'messages'),
                array_keys($tokens)
            ));

            foreach ($tokens as $j => $token) {
                $mailbox = $chunk[$j];
                $response = $reads[$j] ?? null;
                $members = $response instanceof Response && $response->successful()
                    ? $response->json('hydra:member')
                    : null;

                if (!is_array($members)) {
                    $status = $response instanceof Response ? "HTTP {$response->status()}" : 'lỗi kết nối';
                    $results[$mailbox->id] = ['ok' => false, 'message' => "Lấy được token nhưng không đọc được thư ({$status})"];
                    continue;
                }

                $netflix = array_filter($members, fn ($m) => stripos(
                    ($m['from']['name'] ?? '') . ' ' . ($m['from']['address'] ?? ''), 'netflix'
                ) !== false);
                $latest = $members[0] ?? null;

                $results[$mailbox->id] = [
                    'ok' => true,
                    'message' => 'Đọc được ' . count($members) . ' thư (' . count($netflix) . ' thư Netflix)'
                        . ($latest ? ' - mới nhất: ' . ($latest['subject'] ?? '') . ' lúc '
                            . Carbon::parse($latest['createdAt'] ?? 'now')->timezone('Asia/Ho_Chi_Minh')->format('d/m H:i') : ''),
                ];
            }
        }

        $failed = count(array_filter($results, fn ($r) => !$r['ok']));

        return view('code-muakey.tools.forward-mailbox.index', compact('mailboxes', 'results'))
            ->with('checkSummary', 'Đã kiểm tra ' . count($results) . " hòm: " . (count($results) - $failed) . " OK, {$failed} lỗi.");
    }

    /**
     * Thêm hàng loạt: mỗi dòng "email|mật khẩu" (hoặc cách bằng tab/khoảng trắng).
     * Email đã có thì cập nhật mật khẩu và bỏ token cache để đăng nhập lại.
     */
    public function store(Request $request)
    {
        $request->validate(['lines' => 'required|string']);

        $added = 0;
        $updated = 0;
        $invalid = [];
        foreach (preg_split('/\R/u', $request->input('lines')) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = preg_split('/\s*\|\s*|\t+|\s+/u', $line, 2);
            $address = mb_strtolower(trim($parts[0] ?? ''), 'UTF-8');
            $password = trim($parts[1] ?? '');
            if (!filter_var($address, FILTER_VALIDATE_EMAIL) || $password === '') {
                $invalid[] = $line;
                continue;
            }

            $mailbox = ForwardMailbox::firstOrNew(['address' => $address]);
            $mailbox->exists ? $updated++ : $added++;
            $mailbox->fill(['password' => $password, 'active' => true])->save();
            Cache::forget(ForwardMailbox::tokenCacheKey($address));
        }

        $message = "Đã thêm {$added} hòm, cập nhật {$updated} hòm.";
        if ($invalid) {
            $message .= ' Bỏ qua ' . count($invalid) . ' dòng sai định dạng: ' . implode(', ', $invalid);
        }

        return redirect()->route('forward-mailbox.index')->with('success', $message);
    }

    public function update($id)
    {
        $mailbox = ForwardMailbox::findOrFail($id);
        $mailbox->update(['active' => !$mailbox->active]);

        return redirect()->route('forward-mailbox.index')
            ->with('success', ($mailbox->active ? 'Đã bật ' : 'Đã tắt ') . $mailbox->address);
    }

    public function destroy($id)
    {
        $mailbox = ForwardMailbox::findOrFail($id);
        Cache::forget(ForwardMailbox::tokenCacheKey($mailbox->address));
        $mailbox->delete();

        return redirect()->route('forward-mailbox.index')->with('success', 'Đã xoá ' . $mailbox->address);
    }
}
