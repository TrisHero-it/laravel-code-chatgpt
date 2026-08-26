<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMarvelRivalsRequest;
use App\Models\WwmOrder;
use Illuminate\Http\Request;

class MarvelRivalsController extends Controller
{
    protected string $category = 'marvel rivals';

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
        return view('code-muakey.tools.marvel-rivals.index', compact('orders', 'iosProducts'));
    }

    public function create()
    {
        $iosProducts = $this->getProducts();
        return view('code-muakey.tools.marvel-rivals.create', compact('iosProducts'));
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
        return view('code-muakey.tools.marvel-rivals.edit', compact('order', 'iosProducts'));
    }

    public function update(UpdateMarvelRivalsRequest $request, int $id)
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
            $destinationPath = public_path('uploads/marvel-rivals');

            // Tạo folder nếu chưa tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Di chuyển file
            $image->move($destinationPath, $fileName);

            // Lưu đường dẫn vào DB
            $data['image'] = 'uploads/marvel-rivals/' . $fileName;
        }
        $order->update($data);

        return redirect()->back()->with('success', 'Đơn hàng đã được cập nhật thành công.');
    }

    public function getProducts()
    {
        // Mảng sản phẩm tĩnh - bạn có thể chỉnh sửa mảng này theo nhu cầu
        return [
            [
                'goodsid' => 'x20.jg100.cn_S7BONUS.jg5',
                'goodsinfo' => '100 Lattices Marvel Rivals Chỉ Cần ID',
                'platform' => 'pc'
            ],
            [
                'goodsid' => 'x20.jg500.cn_S7BONUS.jg25',
                'goodsinfo' => '500 Lattices Marvel Rivals Chỉ Cần ID',
                'platform' => 'pc'
            ],
            [
                'goodsid' => 'x20.jg1000.cn_S7BONUS.jg55',
                'goodsinfo' => '1.000 Lattices Marvel Rivals Chỉ Cần ID',
                'platform' => 'pc'
            ],
            [
                'goodsid' => 'x20.jg2180.cn_S7BONUS.jg115',
                'goodsinfo' => '2180 Lattices Marvel Rivals Chỉ Cần ID',
                'platform' => 'pc'
            ],
            [
                'goodsid' => 'x20.jg5680.cn_S7BONUS.jg300',
                'goodsinfo' => '5680 Lattices Marvel Rivals Chỉ Cần ID',
                'platform' => 'pc'
            ],
            [
                'goodsid' => 'x20.jg11680.cn_S7BONUS.jg620',
                'goodsinfo' => '11680 Lattices Marvel Rivals Chỉ Cần ID',
                'platform' => 'pc'
            ],
        ];
    }
}
