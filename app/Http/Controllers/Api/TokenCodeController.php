<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TokenCodeController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách code token từ database
        $tokenCode = \App\Models\TokenCode::where('status', 'unused')
            ->where("token", $request->token)
            ->first();

        return response()->json($tokenCode);
    }

    public function update(Request $request, $id)
    {
        // Cập nhật trạng thái code token thành "used"
        $tokenCode = \App\Models\TokenCode::find($id);
        if ($tokenCode) {
            $tokenCode->status = 'used';
            $tokenCode->save();

            return response()->json(['message' => 'Code token đã được sử dụng.']);
        } else {
            return response()->json(['message' => 'Không tìm thấy code token.'], 404);
        }
    }
}
