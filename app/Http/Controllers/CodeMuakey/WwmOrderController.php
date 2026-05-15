<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateWhereWindMeetRequest;
use App\Models\WwmOrder;
use Illuminate\Http\Request;

class WwmOrderController extends Controller
{
    public function index()
    {
        $query = WwmOrder::query();

        if ($search = request()->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhere('uid', 'like', "%{$search}%");
            });
        }

        $orders = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $iosProducts = $this->getProducts();
        return view('code-muakey.tools.where-wind-meet.index', compact('orders', 'iosProducts'));
    }

    public function create()
    {
        $iosProducts = $this->getProducts();
        return view('code-muakey.tools.where-wind-meet.create', compact('iosProducts'));
    }

    public function store(Request $request)
    {

        WwmOrder::create($request->all());

        return redirect()->back()->with('success', 'Đơn hàng đã được thêm thành công!');
    }

    public function edit(Request $request, int $id)
    {
        $order = WwmOrder::findOrFail($id);
        $iosProducts = $this->getProducts();
        return view('code-muakey.tools.where-wind-meet.edit', compact('order', 'iosProducts'));
    }

    public function update(UpdateWhereWindMeetRequest $request, int $id)
    {
        $order = WwmOrder::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if ($order->image && file_exists(public_path($order->image))) {
                unlink(public_path($order->image));
            }

            $image = $request->file('image');

            // Tạo tên file mới
            $fileName = time() . '_' . $image->getClientOriginalName();

            // Thư mục lưu ảnh
            $destinationPath = public_path('uploads/wwm');

            // Tạo folder nếu chưa tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Di chuyển file
            $image->move($destinationPath, $fileName);

            // Lưu đường dẫn vào DB
            $data['image'] = 'uploads/wwm/' . $fileName;
        }
        $order->update($data);

        return redirect()->back()->with('success', 'Đơn hàng đã được cập nhật thành công.');
    }

    public function getProducts()
    {
        // Mảng sản phẩm tĩnh - bạn có thể chỉnh sửa mảng này theo nhu cầu
        return [
            [
                'goodsid' => 'yysls.60cmz.oversea',
                'goodsinfo' => '60 Echo Beads Where Winds Meet ID x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.180cmz.oversea',
                'goodsinfo' => '180 Echo Beads Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.monthlycard.oversea',
                'goodsinfo' => 'Monthly Pass Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.300cmz.oversea',
                'goodsinfo' => '300 Echo Beads Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.600cmz.oversea',
                'goodsinfo' => '600 Echo Beads Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.battlepass.oversea',
                'goodsinfo' => 'Elite Battle Pass Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.900cmz.oversea',
                'goodsinfo' => '900 Echo Beads Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.battlepasspro.oversea',
                'goodsinfo' => 'Premium Battle Pass Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.1800cmz.oversea',
                'goodsinfo' => '1800 Echo Beads Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.3000cmz.oversea',
                'goodsinfo' => '3000 Echo Beads Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'yysls.6000cmz.oversea',
                'goodsinfo' => '6000 Echo Beads Where Winds Meet Bản Mobile x 1',
                'platform' => 'ios'
            ],
        ];
    }
}
