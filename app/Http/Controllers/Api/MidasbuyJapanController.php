<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
