<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Models\TokenCode;
use Illuminate\Http\Request;

class TokenCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = TokenCode::query();

        if ($search = $request->query('search')) {
            $query->where('code', 'like', "%{$search}%");
        }

        if ($request->filled('token')) {
            $query->where('token', $request->integer('token'));
        }

        if ($request->filled('status') && in_array($request->status, ['unused', 'used'], true)) {
            $query->where('status', $request->status);
        }

        $tokenCodes = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $tokenOptions = [16, 80, 240, 400, 560, 830, 1245, 2508, 4180, 8360];

        $unusedCountForToken = null;
        $selectedToken = $request->filled('token') ? $request->integer('token') : null;

        if ($selectedToken !== null) {
            $unusedCountForToken = TokenCode::query()
                ->where('token', $selectedToken)
                ->where('status', 'unused')
                ->count();
        }

        return view('code-muakey.tools.codes.index', compact(
            'tokenCodes',
            'tokenOptions',
            'unusedCountForToken',
            'selectedToken',
        ));
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
