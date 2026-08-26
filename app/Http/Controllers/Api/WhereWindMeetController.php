<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWhereWindMeetOrderRequest;
use Illuminate\Http\Request;

class WhereWindMeetController extends Controller
{
    public function index()
    {
        // Lấy dữ liệu đơn hàng từ database
        $order = \App\Models\WwmOrder::orderBy('sales_agent_id')
            ->where('status', 'pending')
            ->first();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($order);
    }

    public function store(StoreWhereWindMeetOrderRequest $request)
    {
        $data = $request->validated();

        $order = \App\Models\WwmOrder::create($data);

        return response()->json($order, 201);
    }
}
