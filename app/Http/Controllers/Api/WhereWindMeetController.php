<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhereWindMeetController extends Controller
{
    public function index()
    {
        // Lấy dữ liệu đơn hàng từ database
        $order = \App\Models\WwmOrder::orderByDesc('id')
            ->where('status', 'pending')
            ->first();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($order);
    }
}
