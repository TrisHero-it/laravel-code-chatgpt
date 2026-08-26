<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBloodStrikeRequest;
use App\Models\WwmOrder;
use Illuminate\Http\Request;

class BloodStrikeController extends Controller
{
    protected string $category = 'blood strike';

    public function index()
    {
        $query = WwmOrder::query()->where('category', $this->category);

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
        return view('code-muakey.tools.blood-strike.index', compact('orders', 'iosProducts'));
    }

    public function create()
    {
        $iosProducts = $this->getProducts();
        return view('code-muakey.tools.blood-strike.create', compact('iosProducts'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['category'] = $this->category;

        WwmOrder::create($data);

        return redirect()->back()->with('success', 'Đơn hàng đã được thêm thành công!');
    }

    public function edit(Request $request, int $id)
    {
        $order = WwmOrder::where('category', $this->category)->findOrFail($id);
        $iosProducts = $this->getProducts();
        return view('code-muakey.tools.blood-strike.edit', compact('order', 'iosProducts'));
    }

    public function update(UpdateBloodStrikeRequest $request, int $id)
    {
        $order = WwmOrder::where('category', $this->category)->findOrFail($id);
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
            $destinationPath = public_path('uploads/blood-strike');

            // Tạo folder nếu chưa tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Di chuyển file
            $image->move($destinationPath, $fileName);

            // Lưu đường dẫn vào DB
            $data['image'] = 'uploads/blood-strike/' . $fileName;
        }
        $order->update($data);

        return redirect()->back()->with('success', 'Đơn hàng đã được cập nhật thành công.');
    }

    public function getProducts()
    {
        // Mảng sản phẩm tĩnh - bạn có thể chỉnh sửa mảng này theo nhu cầu
        return [
            [
                'goodsid' => 'g83naxx1ena.usd.399pass.ally_100002',
                'goodsinfo' => 'Strike Pass Elite Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.USD.gold500.ally',
                'goodsinfo' => '500 Tặng 40 Gold Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.USD.gold5000.ally',
                'goodsinfo' => '5.000 Tặng 800 Gold Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.USD.gold2000.ally',
                'goodsinfo' => '2.000 Tặng 260 Gold Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.USD.gold1000.ally',
                'goodsinfo' => '1.000 Tặng 100 Gold Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.usd.899pass.ally_100003',
                'goodsinfo' => 'Strike Pass Premium Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.USD.gold300.ally',
                'goodsinfo' => '300 Tặng 20 Gold Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.USD.gold100.ally',
                'goodsinfo' => '100 Tặng 5 Gold Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83us_199newbiepass_100013',
                'goodsinfo' => 'Level-Up Pass Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83us_049deal_1100049',
                'goodsinfo' => 'Ultra Skin Lucky Chest Nạp Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83us_199package_100082',
                'goodsinfo' => 'Enable Cornucopia Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83us_199package_100090',
                'goodsinfo' => 'Attack on Titan Featured Titan I Stash Voucher Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.usd.099package.ally_100091',
                'goodsinfo' => 'Attack on Titan Featured Titan II Stash Voucher Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83us_199package_100089',
                'goodsinfo' => 'Attack on Titan Upgrade Point Lucky Chest Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g83naxx1ena.usd.099package.ally_100084',
                'goodsinfo' => 'Carnival Lucky Bag Week Blood Strike Chỉ Cần ID',
                'platform' => 'ios'
            ]
        ];
    }
}
