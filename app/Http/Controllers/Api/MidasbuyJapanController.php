<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMidasbuyJapanOrderRequest;
use Illuminate\Http\Request;

class MidasbuyJapanController extends Controller
{
    public function index()
    {
        // Lấy dữ liệu đơn hàng từ database
        $order = \App\Models\MidasbuyJapanOrder::orderByDesc('id')
            ->where('status', 'pending')
            ->first();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($order);
    }

    public function store(StoreMidasbuyJapanOrderRequest $request)
    {
        $order = \App\Models\MidasbuyJapanOrder::create([
            'order_id' => $request->input('order_id'),
            'uid' => $request->input('uid'),
            'card' => $request->input('card'),
            'sales_agent_id' => $request->input('sales_agent_id'),
        ]);

        return response()->json($order, 201);
    }

    public function show($id)
    {
        // Lấy dữ liệu đơn hàng từ database
        $order = \App\Models\MidasbuyJapanOrder::findOrFail($id);

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($order);
    }
}
