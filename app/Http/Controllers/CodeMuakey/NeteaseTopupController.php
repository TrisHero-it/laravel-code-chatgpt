<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Models\WwmOrder;
use App\Services\NeteaseTopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NeteaseTopupController extends Controller
{
    public function __construct(private NeteaseTopupService $netease) {}

    /**
     * Kiểm tra UID trên API chính chủ NetEase trước khi nạp.
     * Dùng cho nút "Kiểm tra UID" ở các màn danh sách đơn.
     */
    public function verifyUid(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category' => 'required|string',
            'uid'      => 'required|string|max:32',
            'server'   => 'nullable|string|max:64',
        ]);

        $alias = $this->netease->aliasForCategory($data['category']);

        if ($alias === null) {
            return response()->json([
                'ok'      => false,
                'message' => 'Category "' . $data['category'] . '" chưa được map sang game NetEase.',
            ], 422);
        }

        $role = $this->netease->lookupRole($alias, $data['uid'], $data['server'] ?? null);

        if ($role === null) {
            return response()->json([
                'ok'      => false,
                'message' => 'UID không tồn tại trên NetEase (hoặc sai server).',
            ]);
        }

        return response()->json([
            'ok'        => true,
            'role_name' => $role['rolename'] ?? null,
            'hostid'    => $role['ship_hostid'] ?? $role['hostid'] ?? null,
            'alpha2'    => $role['alpha2'] ?? null,
            // Giới hạn số lần mua từng goodsid - biết trước để khỏi nạp hụt.
            'limits'    => collect($role['product_limit'] ?? [])
                ->map(fn ($l) => [
                    'goodsid'   => $l['goodsid'] ?? null,
                    'buy_num'   => $l['buy_num'] ?? null,
                    'limit_num' => $l['limit_num'] ?? null,
                ])
                ->all(),
        ]);
    }

    /** Link tới trang nạp chính chủ của game tương ứng với đơn hàng. */
    public function topupLink(int $orderId)
    {
        $order = WwmOrder::findOrFail($orderId);
        $alias = $this->netease->aliasForCategory((string) $order->category);

        abort_if($alias === null, 404, 'Category chưa map sang game NetEase.');

        return redirect()->away("https://pay.neteasegames.com/{$alias}/topup?from=home");
    }
}
