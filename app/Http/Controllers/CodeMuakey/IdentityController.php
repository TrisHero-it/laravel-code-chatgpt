<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIdentityRequest;
use App\Models\WwmOrder;
use Illuminate\Http\Request;

class IdentityController extends Controller
{
    protected string $category = 'identity v';

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
        return view('code-muakey.tools.identity.index', compact('orders', 'iosProducts'));
    }

    public function create()
    {
        $iosProducts = $this->getProducts();
        return view('code-muakey.tools.identity.create', compact('iosProducts'));
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
        return view('code-muakey.tools.identity.edit', compact('order', 'iosProducts'));
    }

    public function update(UpdateIdentityRequest $request, int $id)
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
            $destinationPath = public_path('uploads/identity');

            // Tạo folder nếu chưa tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Di chuyển file
            $image->move($destinationPath, $fileName);

            // Lưu đường dẫn vào DB
            $data['image'] = 'uploads/identity/' . $fileName;
        }
        $order->update($data);

        return redirect()->back()->with('success', 'Đơn hàng đã được cập nhật thành công.');
    }

    public function getProducts()
    {
        // Mảng sản phẩm tĩnh - bạn có thể chỉnh sửa mảng này theo nhu cầu
        return [
            [
                'goodsid' => 'h55na.codashoop.60echoes',
                'goodsinfo' => '60 Echoes Identity V Global Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.codashoop.300echoes',
                'goodsinfo' => '305 + 30 Extra Echoes Identity V Global Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.codashoop.680echoes',
                'goodsinfo' => '690 + 69 Extra Echoes Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.codashoop.1980echoes',
                'goodsinfo' => '2025 + 202 Extra Echoes Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.codashoop.3280echoes',
                'goodsinfo' => '3330 + 333 Extra Echoes Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.codashoop.6480echoes',
                'goodsinfo' => '6590 + 659 Extra Echoes Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.codashoop.180echoes',
                'goodsinfo' => '185 + 18 Extra Echoes Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.codashoop.60echoes',
                'goodsinfo' => '60 + 6 Extra Echoes Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.webshop.pack.1',
                'goodsinfo' => 'Inspirations Package Nạp Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.webshop.pack.2',
                'goodsinfo' => 'Clues Package Nạp Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.webshop.pack.3',
                'goodsinfo' => 'Memory Sphere Package Nạp Identity V Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.680echoes.limit.2026anni',
                'goodsinfo' => '8th Anniversary Special Package Nạp Identity V Global Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.980echoes.limit.2026anni',
                'goodsinfo' => '8th Anniversary Discount Package I Nạp Identity V Global Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.3280echoes.limit.2026anni',
                'goodsinfo' => '8th Anniversary Discount Package II Nạp Identity V Global Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'h55na.6480echoes.limit.2026anni',
                'goodsinfo' => '8th Anniversary Discount Package III Nạp Identity V Global Chỉ Cần ID',
                'platform' => 'ios'
            ]
        ];
    }
}
