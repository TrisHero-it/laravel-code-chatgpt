<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDunkCityDynastyRequest;
use App\Models\WwmOrder;
use App\Support\EnrichesNeteaseProducts;
use Illuminate\Http\Request;

class DunkCityDynastyController extends Controller
{
    use EnrichesNeteaseProducts;

    protected string $category = 'dunk city dynasty';

    /** 4 server của trang nạp NetEase (hostid 4001-4004), bot gõ đúng tên này vào ô chọn server. */
    public const SERVERS = ['Moore Park', 'Rucker Park', 'Tenement Court', 'Hooper Park'];

    public function index()
    {
        $query = WwmOrder::query()->where('category', $this->category);

        $query->searchOrder(request()->query('search'));

        $orders = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $iosProducts = $this->enrichWithNetease($this->getProducts(), $this->category);
        return view('code-muakey.tools.dunk-city-dynasty.index', compact('orders', 'iosProducts'));
    }

    public function create()
    {
        $iosProducts = $this->enrichWithNetease($this->getProducts(), $this->category);
        $servers = self::SERVERS;
        return view('code-muakey.tools.dunk-city-dynasty.create', compact('iosProducts', 'servers'));
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
        $servers = self::SERVERS;
        return view('code-muakey.tools.dunk-city-dynasty.edit', compact('order', 'iosProducts', 'servers'));
    }

    public function update(UpdateDunkCityDynastyRequest $request, int $id)
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
            $destinationPath = public_path('uploads/dunk-city-dynasty');

            // Tạo folder nếu chưa tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Di chuyển file
            $image->move($destinationPath, $fileName);

            // Lưu đường dẫn vào DB
            $data['image'] = 'uploads/dunk-city-dynasty/' . $fileName;
        }
        $order->update($data);

        return redirect()->back()->with('success', 'Đơn hàng đã được cập nhật thành công.');
    }

    public function getProducts()
    {
        // Lấy từ https://pay.neteasegames.com/gameclub/products/dunkcitydynasty (platform ios)
        $products = [
            'L33naxx2gb.3rd.60dqbonusa' => '60 Tokens + 60 Bonus Diamonds',
            'L33naxx2gb.3rd.300dqbonusa' => '300 Tokens + 300 Bonus Diamonds',
            'L33naxx2gb.3rd.980dqbonusa' => '980 Tokens + 980 Bonus Diamonds',
            'L33naxx2gb.3rd.1980dqbonusa' => '1,980 Tokens + 1,980 Bonus Diamonds',
            'L33naxx2gb.3rd.3280dqbonusa' => '3,280 Tokens + 3,280 Bonus Diamonds',
            'L33naxx2gb.3rd.6480dqbonusa' => '6,480 Tokens + 6,480 Bonus Diamonds',
            'L33naxx2gb.3rd.60dq' => '60 Tokens',
            'L33naxx2gb.3rd.300dq' => '300 Tokens, 30 free.',
            'L33naxx2gb.3rd.980dq' => '980 Tokens, 130 free.',
            'L33naxx2gb.3rd.1980dq' => '1,980 Tokens, 300 free.',
            'L33naxx2gb.3rd.3280dq' => '3,280 Tokens, 600 free.',
            'L33naxx2gb.3rd.6480dq' => '6,480 Tokens, 1,630 free.',
            'L33naxx2gb.3rd.30yueka' => 'Party Membership',
            'L33naxx2gb.3rd.98zsk' => 'Party Permanent Vip',
            'L33naxx2gb.3rd.bplow' => 'Premium Pass',
            'L33naxx2gb.3rd.bphigh' => 'Extra Premium Pass',
            'L33naxx2gb.3rd.0.99giftsuperstar' => 'Superstar Ice break Pack',
            'L33naxx2gb.3rd.zhuanshugift0.99' => 'Superstar Pack',
            'L33naxx2gb.3rd.zhuanshugift4.99' => 'Superstar Warmup Pack',
            'L33naxx2gb.3rd.68texunlb' => 'Superstar Training Pack',
            'L33naxx2gb.3rd.128kaixuanlb' => 'Superstar Triumph Pack',
            'L33naxx2gb.3rd.328jianglinlb' => 'Superstar Arrival Pack',
            'L33naxx2gb.3rd.99.99giftsuperstar' => 'Superstar Dominion Pack',
            'L33naxx2gb.3rd.zhuanshugift9.99' => 'Fashion Pack',
            'L33naxx2gb.3rd.globalnewcyclefashion98' => 'Fashion Warmup Pack',
            'L33naxx2gb.3rd.globalnewcyclefashion328' => 'Fashion Dazzling Pack',
            'L33naxx2gb.3rd.globalnewcyclefashion648' => 'Fashion Pilot Pack',
            'L33naxx2gb.3rd.49.99gift2' => 'Trendsetter Treasure Pack',
            'L33naxx2gb.3rd.99.99gift2' => 'Trendsetter Cool Picks Pack',
            'L33naxx2gb.3rd.9.99giftsupertalent' => 'Superstar Power-up Weekly Pack',
            'L33naxx2gb.3rd.mt4.99goods2' => 'Stamina Bonus Pack',
            'L33naxx2gb.3rd.mt9.99goods2' => 'Stamina Value Pack',
            'L33naxx2gb.3rd.mt14.99goods2' => 'Stamina Special Pack',
            'L33naxx2gb.3rd.mt4.99goods' => 'Star Token Bonus Pack',
            'L33naxx2gb.3rd.mt9.99goods' => 'Star Token Value Pack',
            'L33naxx2gb.3rd.mt14.99goods' => 'Star Token Special Pack',
            'L33naxx2gb.3rd.rookiegift' => 'Rookie Gift Pack',
            'L33naxx2gb.3rd.30usqylb1' => 'Star Player Selected Pack',
            'L33naxx2gb.3rd.68usqylb2' => 'Star Player Treasure Pack',
            'L33naxx2gb.3rd.128usqylb3' => 'Star Player Sparkle Pack',
            'L33naxx2gb.3rd.kitsubscriptiona0.49' => 'Party Jessy 3 Days Access',
            'L33naxx2gb.3rd.kitsubscriptiona0.99' => 'Party Jessy 7 Days Access',
            'L33naxx2gb.3rd.kitsubscriptiona2.99' => 'Party Jessy 28 Days Access',
        ];

        $result = [];
        foreach ($products as $goodsid => $info) {
            $result[] = [
                'goodsid' => $goodsid,
                'goodsinfo' => $info . ' Dunk City Dynasty Top Up Chỉ Cần ID',
                'platform' => 'ios',
            ];
        }

        return $result;
    }
}
