<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Bọc API của trang nạp chính chủ NetEase (https://pay.neteasegames.com/<game>/topup).
 *
 * Toàn bộ endpoint nằm dưới prefix /gameclub và trả về envelope:
 *   {"code":"0000","msg":null,"data":...}   // code "0000" = thành công
 *
 * Các endpoint đọc (games/products/servers/login-role) KHÔNG cần đăng nhập —
 * luồng "nạp chỉ cần ID" lấy luôn aid/username từ chính roleid.
 *
 * Riêng POST tạo đơn thì NetEase bắt buộc có header Signature/Timestamp do
 * client chính chủ sinh ra ("lack signature client authorization invalid"),
 * nên service này cố tình KHÔNG tạo đơn — xem ghi chú ở cuối file.
 */
class NeteaseTopupService
{
    /** Map category trong DB -> gameid_alias của NetEase (client dùng chữ thường). */
    public const GAME_ALIASES = [
        'where wind meet' => 'wherewindsmeet',
        'identity v'      => 'identityv',
        'blood strike'    => 'bloodstrike',
        'marvel rivals'   => 'marvelrivals',
        'racing master'   => 'racingmastersea',
    ];

    /** Phiên bản client mà trang topup tự khai báo trong mọi request. */
    protected const CLIENT_VERSION = '1.13.51';

    public function baseUrl(): string
    {
        return rtrim(config('services.netease.base_url'), '/');
    }

    /** Đổi category nội bộ sang gameid_alias, trả null nếu chưa map. */
    public function aliasForCategory(string $category): ?string
    {
        return self::GAME_ALIASES[strtolower(trim($category))] ?? null;
    }

    /** Danh sách toàn bộ game đang mở nạp trên NetEase. */
    public function games(): array
    {
        return $this->cached('netease:games', fn () => $this->get('/games'), 60 * 24) ?? [];
    }

    /**
     * Danh sách sản phẩm (goodsid, goodsinfo, price, currency, platform) của 1 game.
     * Đây chính là nguồn thay cho các mảng hardcode trong controller.
     */
    public function products(string $gameAlias): array
    {
        return $this->cached(
            "netease:products:{$gameAlias}",
            fn () => $this->get("/products/{$gameAlias}"),
            config('services.netease.products_ttl', 360)
        ) ?? [];
    }

    /** Danh sách server của game. WWM/Blood Strike chỉ có 1 server ảo hostid = -1. */
    public function servers(string $gameAlias): array
    {
        return $this->cached(
            "netease:servers:{$gameAlias}",
            fn () => $this->get("/servers/{$gameAlias}"),
            60 * 24
        ) ?? [];
    }

    /** Danh sách hostid cần thử khi tra UID, theo thứ tự trang topup liệt kê. */
    public function hostIds(string $gameAlias): array
    {
        $hostIds = array_values(array_filter(
            array_column($this->servers($gameAlias), 'hostid'),
            fn ($id) => $id !== null
        ));

        // Game không chia server (WWM, Blood Strike) dùng host ảo -1.
        return $hostIds ?: [-1];
    }

    /**
     * Tra UID trên endpoint "nạp chỉ cần ID" của trang chính chủ:
     *   GET /gameclub/{game}/{hostid}/login-role?roleid=...&client_type=gameclub
     *
     * Endpoint này KHÔNG cần đăng nhập và trả về đủ thông tin định danh để nạp:
     * rolename, aid, username, hostid, ship_hostid, show_roleid, check_role_hostid
     * cùng product_limit (giới hạn số lần mua từng goodsid).
     *
     * Game nhiều server (Identity V) không cho biết UID nằm ở server nào nên
     * phải thử lần lượt; server nào trả 0000 chính là server của UID đó.
     */
    public function lookupRole(string $gameAlias, string $roleId, ?string $server = null): ?array
    {
        $hostIds = ($server !== null && $server !== '')
            ? [$server]
            : $this->hostIds($gameAlias);

        foreach ($hostIds as $hostId) {
            $data = $this->get("/{$gameAlias}/{$hostId}/login-role", [
                'roleid'      => $roleId,
                'client_type' => 'gameclub',
            ]);

            if (! empty($data)) {
                return $data + ['queried_hostid' => $hostId];
            }
        }

        return null;
    }

    /**
     * Gọi GET tới API NetEase, trả về `data` nếu code = 0000, ngược lại null.
     */
    protected function get(string $path, array $query = []): array|null
    {
        // Trang topup gắn 4 tham số này vào mọi request; thiếu chúng một số
        // endpoint (login-role) sẽ từ chối phục vụ.
        $query += [
            'deviceid'          => config('services.netease.device_id'),
            'traceid'           => (string) Str::uuid(),
            'timestamp'         => (int) (microtime(true) * 1000),
            'gc_client_version' => self::CLIENT_VERSION,
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(config('services.netease.timeout', 20))
                ->withHeaders(['User-Agent' => config('services.netease.user_agent')])
                ->get($this->baseUrl() . $path, $query);
        } catch (\Throwable $e) {
            Log::warning('netease: request lỗi', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('netease: HTTP lỗi', ['path' => $path, 'status' => $response->status()]);

            return null;
        }

        $body = $response->json();

        if (($body['code'] ?? null) !== '0000') {
            Log::warning('netease: API trả lỗi', [
                'path' => $path,
                'code' => $body['code'] ?? null,
                'msg'  => $body['msg'] ?? null,
            ]);

            return null;
        }

        $data = $body['data'] ?? null;

        return is_array($data) ? $data : null;
    }

    /**
     * Payload mà POST /gameclub/products/{game} yêu cầu, dựng sẵn từ kết quả
     * lookupRole() + sản phẩm cần nạp. Dùng để điền nhanh khi thao tác tay,
     * hoặc để lái trình duyệt trên chính trang topup.
     *
     * KHÔNG gửi thẳng payload này lên NetEase: endpoint tạo đơn đòi thêm header
     * Signature/Timestamp do client chính chủ ký, thiếu thì trả
     * "lack signature client authorization invalid!" (code 0202).
     */
    public function buildOrderPayload(array $role, array $product, string $payType = 'PayPal', string $payMethod = 'paypal'): array
    {
        return [
            'goodsid'           => $product['goodsid'] ?? null,
            'sub_item'          => $product['sub_item'] ?? '',
            'currency'          => $product['currency'] ?? 'USD',
            'aid'               => (int) ($role['aid'] ?? 0),
            'username'          => $role['username'] ?? null,
            'roleid'            => $role['roleid'] ?? null,
            'show_roleid'       => $role['show_roleid'] ?? null,
            'hostid'            => $role['hostid'] ?? null,
            'ship_hostid'       => $role['ship_hostid'] ?? $role['hostid'] ?? null,
            'check_role_hostid' => $role['check_role_hostid'] ?? -1,
            'pay_type'          => $payType,
            'pay_method'        => $payMethod,
            'platform'          => $product['platform'] ?? 'pc',
            'login_channel'     => 'netease',
            'pay_channel'       => 'netease',
            'app_channel'       => 'netease',
            'lan_code'          => 'en-US',
            'timestamp'         => (int) (microtime(true) * 1000),
        ];
    }

    /** Cache có fallback: lỗi mạng thì KHÔNG ghi cache để lần sau thử lại. */
    protected function cached(string $key, callable $resolver, int $minutes): array|null
    {
        $value = Cache::get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $resolver();

        if ($value !== null) {
            Cache::put($key, $value, now()->addMinutes($minutes));
        }

        return $value;
    }
}
