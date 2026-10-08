<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRacingMasterRequest;
use App\Models\WwmOrder;
use App\Support\EnrichesNeteaseProducts;
use Illuminate\Http\Request;

class RacingMasterController extends Controller
{
    use EnrichesNeteaseProducts;

    protected string $category = 'racing master';

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

        $iosProducts = $this->enrichWithNetease($this->getProducts(), $this->category);
        return view('code-muakey.tools.racing-master.index', compact('orders', 'iosProducts'));
    }

    public function create()
    {
        $iosProducts = $this->enrichWithNetease($this->getProducts(), $this->category);
        return view('code-muakey.tools.racing-master.create', compact('iosProducts'));
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
        $iosProducts = $this->enrichWithNetease($this->getProducts(), $this->category);
        return view('code-muakey.tools.racing-master.edit', compact('order', 'iosProducts'));
    }

    public function update(UpdateRacingMasterRequest $request, int $id)
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
            $destinationPath = public_path('uploads/racing-master');

            // Tạo folder nếu chưa tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Di chuyển file
            $image->move($destinationPath, $fileName);

            // Lưu đường dẫn vào DB
            $data['image'] = 'uploads/racing-master/' . $fileName;
        }
        $order->update($data);

        return redirect()->back()->with('success', 'Đơn hàng đã được cập nhật thành công.');
    }

    public function getProducts()
    {
        // Mảng sản phẩm tĩnh - bạn có thể chỉnh sửa mảng này theo nhu cầu
        return [
            [
                'goodsid' => 'g112sea.online.gem70',
                'goodsinfo' => '70 +4 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem140',
                'goodsinfo' => '140+7 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem210',
                'goodsinfo' => '210 +11 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem350',
                'goodsinfo' => '350 +18 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem700',
                'goodsinfo' => '700 +35 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem1000',
                'goodsinfo' => '1000 +50 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem1400',
                'goodsinfo' => '1400 +70 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem2100',
                'goodsinfo' => '2100 +105 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem3400',
                'goodsinfo' => '3400 +170 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.gem6600',
                'goodsinfo' => '6600 +330 Gems Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.weeklypack',
                'goodsinfo' => 'Weekly Card Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.monthlypack',
                'goodsinfo' => 'Monthly Card Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp1',
                'goodsinfo' => 'DELUXE MP Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp2',
                'goodsinfo' => 'PREMIUM MP Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.growthfund.88',
                'goodsinfo' => 'Growth Fund Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp3',
                'goodsinfo' => 'Upgrade to the Premium MP Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.fashion3in1',
                'goodsinfo' => 'Value Outfit Pack Racing Master Top Up SEA Chỉ Cần ID',
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp4',
                'goodsinfo' => "MP Combo Pack (Porsche 911 Carrera 4 (964) '89) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp5',
                'goodsinfo' => "Upgrade MP (Porsche 911 Carrera 4 (964) '89) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.1650',
                'goodsinfo' => "MP Combo (Alfa Romeo Giulia Quadrifoglio (Type 952) '17) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.1300',
                'goodsinfo' => "Upgrade MP (Alfa Romeo Giulia Quadrifoglio (Type 952) '17) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp7',
                'goodsinfo' => "MP Combo(Alfa Romeo 33 Stradale (33 STRADALE) '67) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp8',
                'goodsinfo' => "Upgrade MP(Alfa Romeo 33 Stradale (33 STRADALE) '67) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.1000',
                'goodsinfo' => "Legendary Returns Car Pack(Alfa Romeo Giulia Quadrifoglio (Type 952) '17) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp6',
                'goodsinfo' => "Legendary Returns Car Pack (Porsche 911 Carrera 4 (964) '89) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
            [
                'goodsid' => 'g112sea.online.bp9',
                'goodsinfo' => "Legendary New Car Pack(Alfa Romeo 33 Stradale (33 STRADALE) '67) Racing Master Top Up SEA Chỉ Cần ID",
                'platform' => 'ios'
            ],
        ];
    }
}
