<?php

namespace App\Console\Commands;

use App\Http\Controllers\CodeMuakey\BloodStrikeController;
use App\Http\Controllers\CodeMuakey\IdentityController;
use App\Http\Controllers\CodeMuakey\MarvelRivalsController;
use App\Http\Controllers\CodeMuakey\WwmOrderController;
use App\Models\MidasbuyJapanOrder;
use App\Models\MidasbuyToken;
use App\Models\WwmOrder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Signature('orders:import-doitac')]
#[Description('Lấy đơn hàng mới (status=new) từ đối tác doitac.top và import vào DB nội bộ')]
class ImportDoitacOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $url = config('services.doitac.url');
        $token = config('services.doitac.token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->get($url, [
                'sort' => 'id',
                'per_page' => 20,
                'page' => 1,
                'filter' => ['status' => 'new'],
            ]);

        if ($response->failed()) {
            $this->error('Gọi API đối tác thất bại: HTTP ' . $response->status());
            Log::warning('doitac import: API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return self::FAILURE;
        }

        // JSON_BIGINT_AS_STRING: UID có thể là số nguyên siêu dài (vượt PHP_INT_MAX),
        // nếu không giữ dạng string thì PHP sẽ decode thành float và làm mất độ chính xác.
        $orders = $response->json('data', [], JSON_BIGINT_AS_STRING);

        if (empty($orders)) {
            $this->info('Không có đơn hàng mới.');
            return self::SUCCESS;
        }

        foreach ($orders as $order) {
            $this->importOrder($order);
        }

        return self::SUCCESS;
    }

    protected function importOrder(array $order): void
    {
        $partnerId = $order['id'] ?? null;
        $orderItemId = $order['order_item_id'] ?? $partnerId;
        $name = $order['name'] ?? '';
        $fields = $order['fields'] ?? [];

        if (!$partnerId) {
            return;
        }

        $uid = $this->extractUid($fields);

        if ($this->isInvalidUid($uid)) {
            $rawUid = $uid ?? $this->firstFieldValue($fields);
            Log::info('doitac import: UID không hợp lệ, huỷ đơn', ['id' => $partnerId, 'uid' => $rawUid, 'name' => $name, 'fields' => $fields]);
            $this->markDoitacStatus((int) $partnerId, 'cancel', 'UID không hợp lệ: ' . ($rawUid ?? '(trống)'));
            return;
        }

        $server = $this->extractServer($fields, $uid);

        if (preg_match('/weekly\s*card/i', $name)) {
            $this->importMidasbuyJapan((int) $partnerId, $orderItemId, $name, $uid);
        } elseif (preg_match('/identity\s*v/i', $name)) {
            $this->importIdentity((int) $partnerId, $orderItemId, $name, $uid, $server);
        } elseif (preg_match('/blood\s*strike/i', $name)) {
            $this->importBloodStrike((int) $partnerId, $orderItemId, $name, $uid);
        } elseif (preg_match('/marvel\s*rivals/i', $name)) {
            $this->importMarvelRivals((int) $partnerId, $orderItemId, $name, $uid);
        } elseif (preg_match('/tokens?/i', $name)) {
            $this->importMidasbuyToken((int) $partnerId, $orderItemId, $name, $uid);
        } elseif (preg_match('/where\s*winds?\s*meet/i', $name)) {
            $this->importWwm((int) $partnerId, $orderItemId, $name, $uid);
        } else {
            Log::info('doitac import: bỏ qua sản phẩm chưa hỗ trợ', ['id' => $partnerId, 'name' => $name]);
        }
    }

    /**
     * Field UID trong "fields" của mỗi loại sản phẩm dùng key khác nhau và không cố định
     * (HoK=18, WWM=111, Identity V=23, ...), nhưng luôn là chuỗi số thuần nên tự nhận diện
     * theo giá trị thay vì hardcode key, để tự thích ứng với game mới không cần biết trước key.
     */
    protected function extractUid(array $fields): ?string
    {
        foreach ($fields as $value) {
            if (is_string($value) && preg_match('/^\d+$/', $value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Server (nếu có) là field còn lại không phải UID và không phải số thuần (vd "asia").
     */
    protected function extractServer(array $fields, ?string $uid): ?string
    {
        foreach ($fields as $value) {
            if (is_string($value) && $value !== $uid && !preg_match('/^\d+$/', $value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Chỉ dùng để hiển thị trong log/reason khi UID không hợp lệ (vd "YNL5501"),
     * không dùng để lưu DB vì có thể không phải field UID thật.
     */
    protected function firstFieldValue(array $fields): ?string
    {
        foreach ($fields as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function importMidasbuyJapan(int $partnerId, $orderItemId, string $name, ?string $uid): void
    {
        if (MidasbuyJapanOrder::where('sales_agent_id', $partnerId)->exists()) {
            return;
        }

        MidasbuyJapanOrder::create([
            'order_id' => $orderItemId,
            'uid' => $uid,
            'card' => stripos($name, 'plus') !== false ? 'plus' : 'normal',
            'sales_agent_id' => $partnerId,
            'status' => 'pending',
        ]);

        $this->info("Đã thêm MidasBuy Japan order (partner #{$partnerId})");
        $this->markDoitacStatus($partnerId, 'pending');
    }

    protected function importMidasbuyToken(int $partnerId, $orderItemId, string $name, ?string $uid): void
    {
        if (MidasbuyToken::where('sale_agent_id', $partnerId)->exists()) {
            return;
        }

        if (!preg_match('/([\d\s+]+)\s*Tokens?/i', $name, $matches)) {
            Log::warning('doitac import: không tách được số token từ tên sản phẩm', ['id' => $partnerId, 'name' => $name]);
            return;
        }

        $token = array_sum(array_map(
            fn ($n) => (int) trim($n),
            explode('+', $matches[1])
        ));

        MidasbuyToken::create([
            'order_id' => (string) $orderItemId,
            'uid' => $uid,
            'token' => (string) $token,
            'sale_agent_id' => (string) $partnerId,
            'status' => 'pending',
        ]);

        $this->info("Đã thêm MidasBuy Token order (partner #{$partnerId})");
        $this->markDoitacStatus($partnerId, 'pending');
    }

    protected function importWwm(int $partnerId, $orderItemId, string $name, ?string $uid): void
    {
        if (WwmOrder::where('sales_agent_id', $partnerId)->exists()) {
            return;
        }

        $productId = $this->matchWwmProductId($name);

        if (!$productId) {
            Log::warning('doitac import: không tìm thấy product_id WWM khớp với tên sản phẩm', ['id' => $partnerId, 'name' => $name]);
            return;
        }

        WwmOrder::create([
            'order_id' => $orderItemId,
            'uid' => $uid,
            'product_id' => $productId,
            'category' => 'where wind meet',
            'sales_agent_id' => $partnerId,
            'status' => 'pending',
        ]);

        $this->info("Đã thêm WWM order (partner #{$partnerId})");
        $this->markDoitacStatus($partnerId, 'pending');
    }

    protected function importIdentity(int $partnerId, $orderItemId, string $name, ?string $uid, ?string $server): void
    {
        if (WwmOrder::where('sales_agent_id', $partnerId)->exists()) {
            return;
        }

        $productId = $this->matchIdentityProductId($name);

        if (!$productId) {
            Log::warning('doitac import: không tìm thấy product_id Identity V khớp với tên sản phẩm', ['id' => $partnerId, 'name' => $name]);
            return;
        }

        WwmOrder::create([
            'order_id' => $orderItemId,
            'uid' => $uid,
            'server' => $server,
            'product_id' => $productId,
            'category' => 'identity v',
            'sales_agent_id' => $partnerId,
            'status' => 'pending',
        ]);

        $this->info("Đã thêm Identity V order (partner #{$partnerId})");
        $this->markDoitacStatus($partnerId, 'pending');
    }

    protected function importBloodStrike(int $partnerId, $orderItemId, string $name, ?string $uid): void
    {
        if (WwmOrder::where('sales_agent_id', $partnerId)->exists()) {
            return;
        }

        $productId = $this->matchBloodStrikeProductId($name);

        if (!$productId) {
            Log::warning('doitac import: không tìm thấy product_id Blood Strike khớp với tên sản phẩm', ['id' => $partnerId, 'name' => $name]);
            return;
        }

        WwmOrder::create([
            'order_id' => $orderItemId,
            'uid' => $uid,
            'product_id' => $productId,
            'category' => 'blood strike',
            'sales_agent_id' => $partnerId,
            'status' => 'pending',
        ]);

        $this->info("Đã thêm Blood Strike order (partner #{$partnerId})");
        $this->markDoitacStatus($partnerId, 'pending');
    }

    protected function importMarvelRivals(int $partnerId, $orderItemId, string $name, ?string $uid): void
    {
        if (WwmOrder::where('sales_agent_id', $partnerId)->exists()) {
            return;
        }

        $productId = $this->matchMarvelRivalsProductId($name);

        if (!$productId) {
            Log::warning('doitac import: không tìm thấy product_id Marvel Rivals khớp với tên sản phẩm', ['id' => $partnerId, 'name' => $name]);
            return;
        }

        WwmOrder::create([
            'order_id' => $orderItemId,
            'uid' => $uid,
            'product_id' => $productId,
            'category' => 'marvel rivals',
            'sales_agent_id' => $partnerId,
            'status' => 'pending',
        ]);

        $this->info("Đã thêm Marvel Rivals order (partner #{$partnerId})");
        $this->markDoitacStatus($partnerId, 'pending');
    }

    /**
     * Cập nhật trạng thái đơn hàng bên đối tác doitac.top sau khi import.
     * status hợp lệ: cancel (huỷ), processed (thành công), pending (Đã nhận, đang làm).
     * Khi huỷ (cancel) phải kèm reason trong body, nếu không đối tác từ chối request.
     */
    protected function markDoitacStatus(int $partnerId, string $status, ?string $reason = null): void
    {
        $url = config('services.doitac.url');
        $token = config('services.doitac.token');

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$url}/{$partnerId}/{$status}", $reason !== null ? ['reason' => $reason] : []);

        if ($response->failed()) {
            Log::warning('doitac import: cập nhật status đối tác thất bại', [
                'id' => $partnerId,
                'status' => $status,
                'reason' => $reason,
                'http_status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    /**
     * UID hợp lệ phải là chuỗi số thuần (uid dạng chữ+số như "YNL5501" là không hợp lệ).
     */
    protected function isInvalidUid(?string $uid): bool
    {
        return !$uid || !preg_match('/^\d+$/', $uid);
    }

    protected function matchWwmProductId(string $name): ?string
    {
        $clean = strtolower($this->normalizeProductName($name));

        foreach ((new WwmOrderController())->getProducts() as $product) {
            $normalized = strtolower($this->normalizeProductName($product['goodsinfo']));

            if ($normalized === $clean || str_contains($normalized, $clean) || str_contains($clean, $normalized)) {
                return $product['goodsid'];
            }
        }

        return null;
    }

    protected function matchIdentityProductId(string $name): ?string
    {
        $clean = strtolower($this->normalizeProductName($name));

        foreach ((new IdentityController())->getProducts() as $product) {
            $normalized = strtolower($this->normalizeProductName($product['goodsinfo']));

            if ($normalized === $clean || str_contains($normalized, $clean) || str_contains($clean, $normalized)) {
                return $product['goodsid'];
            }
        }

        return null;
    }

    protected function matchBloodStrikeProductId(string $name): ?string
    {
        $clean = strtolower($this->normalizeProductName($name));

        foreach ((new BloodStrikeController())->getProducts() as $product) {
            $normalized = strtolower($this->normalizeProductName($product['goodsinfo']));

            if ($normalized === $clean || str_contains($normalized, $clean) || str_contains($clean, $normalized)) {
                return $product['goodsid'];
            }
        }

        return null;
    }

    protected function matchMarvelRivalsProductId(string $name): ?string
    {
        $clean = strtolower($this->normalizeProductName($name));

        foreach ((new MarvelRivalsController())->getProducts() as $product) {
            $normalized = strtolower($this->normalizeProductName($product['goodsinfo']));

            if ($normalized === $clean || str_contains($normalized, $clean) || str_contains($clean, $normalized)) {
                return $product['goodsid'];
            }
        }

        return null;
    }

    /**
     * Catalog nội bộ đặt tên sản phẩm với hậu tố "Bản Mobile" trong khi doitac trả về hậu tố "ID"
     * (vd "600 Echo Beads Where Winds Meet ID" vs "... Bản Mobile x 1") nên phải bỏ cả 2 hậu tố mới so khớp được.
     */
    protected function normalizeProductName(string $name): string
    {
        $name = trim(preg_replace('/\s+x\s+\d+$/i', '', $name));
        $name = trim(preg_replace('/\s+(ID|Bản Mobile)\s*$/i', '', $name));
        $name = trim(preg_replace('/\s+(ID|Bản Mobile)\s*$/i', '', $name));

        return $name;
    }
}
