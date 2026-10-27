<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Bọc API hộp thư inbound-mail (Cloudflare Email Routing -> Worker).
 *
 *   GET /emails?to=&limit=&before=   -> {"items":[...], "next_before": "<received_at>"|null}
 *   GET /emails/{id}                 -> 1 mail đầy đủ (text, html, to, cc, ...)
 *
 * Phân trang: truyền next_before của trang trước vào before để lấy trang cũ hơn.
 */
class InboundMailService
{
    /**
     * @return array{items: array, next_before: ?string}
     */
    public function list(?string $to = null, ?string $before = null, ?int $limit = null): array
    {
        $data = $this->get('/emails', array_filter([
            'to' => $to,
            'before' => $before,
            'limit' => $limit,
        ], fn ($v) => $v !== null && $v !== ''));

        return [
            'items' => is_array($data['items'] ?? null) ? $data['items'] : [],
            'next_before' => $data['next_before'] ?? null,
        ];
    }

    public function find(string $id): ?array
    {
        return $this->get('/emails/' . rawurlencode($id));
    }

    /**
     * Người nhận thật của mail.
     *
     * Mail về hộp này do Gmail CHUYỂN TIẾP nên envelope_to luôn là hộp
     * netflix@lumekza.io.vn. Gmail ghi tài khoản gốc vào envelope_from dạng
     * "<tên>+caf_=netflix=lumekza.io.vn@gmail.com" -> "<tên>@gmail.com".
     * Không phải mail chuyển tiếp thì trả về envelope_to.
     */
    public function recipientOf(array $mail): ?string
    {
        $from = (string) ($mail['envelope_from'] ?? '');
        if (preg_match('/^([^+@]+)\+caf_=[^@]*@(.+)$/i', $from, $m)) {
            return mb_strtolower($m[1] . '@' . $m[2], 'UTF-8');
        }

        $to = $mail['envelope_to'] ?? null;
        return $to ? mb_strtolower(trim($to), 'UTF-8') : null;
    }

    /**
     * Domain gốc của mail do Cloudflare Email Routing CHUYỂN TIẾP từ domain
     * khác (vd taphoavuive.store): envelope_from dạng
     * "cfbounces+...@<domain gốc>", envelope_to vẫn là hộp chung nên không
     * biết người nhận thật - phải mở mail đầy đủ xem header to (isAddressedTo).
     */
    public function forwardedDomainOf(array $mail): ?string
    {
        $from = (string) ($mail['envelope_from'] ?? $mail['envelope']['from'] ?? '');
        if (preg_match('/^cfbounces\+[^@]*@(.+)$/i', $from, $m)) {
            return mb_strtolower($m[1], 'UTF-8');
        }

        return null;
    }

    /**
     * Header to/cc của mail đầy đủ (lấy từ find()) có chứa $email không.
     */
    public function isAddressedTo(array $mail, string $email): bool
    {
        $recipients = array_merge((array) ($mail['to'] ?? []), (array) ($mail['cc'] ?? []));
        foreach ($recipients as $recipient) {
            $address = is_array($recipient) ? ($recipient['address'] ?? null) : $recipient;
            if ($this->sameAddress(is_string($address) ? $address : null, $email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * So 2 địa chỉ, với Gmail thì bỏ dấu chấm và phần +tag (Gmail coi
     * a.b+x@gmail.com và ab@gmail.com là cùng một hộp).
     */
    public function sameAddress(?string $a, ?string $b): bool
    {
        return $a !== null && $b !== null && $this->normalizeAddress($a) === $this->normalizeAddress($b);
    }

    private function normalizeAddress(string $address): string
    {
        $address = mb_strtolower(trim($address), 'UTF-8');
        [$local, $domain] = array_pad(explode('@', $address, 2), 2, '');

        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            $local = str_replace('.', '', explode('+', $local, 2)[0]);
            $domain = 'gmail.com';
        }

        return $local . '@' . $domain;
    }

    private function get(string $path, array $query = []): ?array
    {
        try {
            $response = Http::withToken((string) config('services.inbound_mail.token'))
                ->timeout(10)
                ->get(rtrim(config('services.inbound_mail.base_url'), '/') . $path, $query);
        } catch (\Throwable $e) {
            Log::warning('inbound-mail: loi ket noi', ['path' => $path, 'error' => $e->getMessage()]);
            return null;
        }

        if (!$response->successful()) {
            Log::warning('inbound-mail: loi API', ['path' => $path, 'status' => $response->status()]);
            return null;
        }

        $json = $response->json();
        return is_array($json) ? $json : null;
    }
}
