<?php

namespace App\Support;

use App\Services\NeteaseTopupService;

/**
 * Ghép giá + tình trạng còn bán từ API NetEase vào catalog tĩnh của controller.
 *
 * LƯU Ý: `goodsinfo` tĩnh được ImportDoitacOrders dùng để khớp tên sản phẩm của
 * đối tác nên tuyệt đối không ghi đè. API chỉ bổ sung thêm field mới.
 */
trait EnrichesNeteaseProducts
{
    protected function enrichWithNetease(array $localProducts, string $category): array
    {
        $service = app(NeteaseTopupService::class);
        $alias = $service->aliasForCategory($category);

        if ($alias === null) {
            return $localProducts;
        }

        // Catalog tĩnh lưu sản phẩm con dưới dạng "<goodsid>_<sub_item>", còn API
        // trả goodsid và sub_item tách rời -> index theo cả hai dạng để khớp được.
        $live = [];
        foreach ($service->products($alias) as $product) {
            if (! isset($product['goodsid'])) {
                continue;
            }

            $subItem = (string) ($product['sub_item'] ?? '');
            $key = $subItem === ''
                ? $product['goodsid']
                : $product['goodsid'] . '_' . $subItem;

            $live[$key] = $product;
        }

        // API không phản hồi -> giữ nguyên catalog tĩnh, không đánh dấu hết hàng.
        if ($live === []) {
            return $localProducts;
        }

        foreach ($localProducts as &$product) {
            $match = $live[$product['goodsid']] ?? null;

            $product['available'] = $match !== null;
            $product['price'] = $match['price'] ?? null;
            $product['currency'] = $match['currency'] ?? null;
            $product['netease_goodsinfo'] = $match['goodsinfo'] ?? null;
        }

        return $localProducts;
    }
}
