<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Models\TokenCode;
use Illuminate\Http\Request;

class TokenCodeController extends Controller
{
    public function index()
    {
        $query = TokenCode::query();

        if ($search = request()->query('search')) {
            $query->where('code', 'like', "%{$search}%");
        }

        $tokenCodes = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('code-muakey.tools.codes.index', compact('tokenCodes'));
    }

    public function create()
    {
        return view('code-muakey.tools.codes.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codes' => 'required|string',
            'token' => 'required|integer',
        ]);

        // Tách từng dòng
        $codes = preg_split('/\r\n|\r|\n/', trim($request->codes));

        $insertData = [];

        foreach ($codes as $code) {
            if (TokenCode::where('code', $code)->exists()) {
                continue;
            }
            $code = trim($code);

            // Bỏ qua dòng rỗng
            if (empty($code)) {
                continue;
            }

            $insertData[] = [
                'code' => $code,
                'token' => $request->token,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert hàng loạt
        TokenCode::insert($insertData);

        return redirect()
            ->back()
            ->with('success', 'Đã thêm ' . count($insertData) . ' code thành công!');
    }
}
