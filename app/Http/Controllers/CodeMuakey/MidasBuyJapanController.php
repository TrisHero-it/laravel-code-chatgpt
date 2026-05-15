<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMidasbuyJapanOrderRequest;
use App\Models\MidasbuyJapanOrder;
use Illuminate\Http\Request;

class MidasBuyJapanController extends Controller
{
    public function index()
    {
        $query = MidasbuyJapanOrder::query();

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

        return view('code-muakey.tools.midasbuy-japan.index', compact('orders'));
    }

    public function create()
    {
        return view('code-muakey.tools.midasbuy-japan.create');
    }

    public function store(StoreMidasbuyJapanOrderRequest $request)
    {
        MidasbuyJapanOrder::create([
            'order_id' => $request->input('order_id'),
            'uid' => $request->input('uid'),
            'card' => $request->input('card'),
            'sales_agent_id' => $request->input('sales_agent_id'),
        ]);

        return redirect()->route('midasbuy-japan.index')->with('success', 'Đơn hàng đã được thêm thành công!');
    }

    public function edit($id)
    {
        $order = MidasbuyJapanOrder::findOrFail($id);
        return view('code-muakey.tools.midasbuy-japan.edit', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = MidasbuyJapanOrder::findOrFail($id);
        $data = [
            'order_id' => $request->input('order_id'),
            'uid' => $request->input('uid'),
            'card' => $request->input('card'),
            'sales_agent_id' => $request->input('sales_agent_id'),
            'status' => $request->input('status'),
        ];

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            $image->move(public_path('uploads/midasbuy-japan'), $imageName);

            $data['image'] = 'uploads/midasbuy-japan/' . $imageName;
        }

        $order->update($data);

        return redirect()
            ->route('midasbuy-japan.index')
            ->with('success', 'Đơn hàng đã được cập nhật thành công!');
    }

    public function destroy($id)
    {
        $order = MidasbuyJapanOrder::findOrFail($id);
        $order->delete();

        return redirect()->route('midasbuy-japan.index')->with('success', 'Đơn hàng đã được xóa thành công!');
    }
}
