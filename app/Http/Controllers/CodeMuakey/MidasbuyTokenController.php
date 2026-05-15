<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\MidasbuyToken\StoreMidasbuyTokenRequest;
use App\Http\Requests\MidasbuyToken\UpdateMidasbuyTokenRequest;
use App\Models\MidasbuyToken;
use Illuminate\Http\Request;

class MidasbuyTokenController extends Controller
{
    public function index()
    {
        $query = MidasbuyToken::query();

        $midasbuyTokens = $query
            ->orderByDesc('id')
            ->paginate(10);

        return view('code-muakey.tools.midasbuy-token.index', compact('midasbuyTokens'));
    }

    public function create()
    {
        return view('code-muakey.tools.midasbuy-token.create');
    }

    public function store(StoreMidasbuyTokenRequest $request)
    {
        $data = $request->validated();

        $midasbuyToken = MidasbuyToken::create($data);

        return redirect()->back()->with('success', 'MidasBuy Token đã được tạo thành công!');
    }

    public function destroy($id)
    {
        $midasbuyToken = MidasbuyToken::findOrFail($id);
        $midasbuyToken->delete();

        return redirect()->back()->with('success', 'MidasBuy Token đã được xóa thành công!');
    }

    public function edit($id)
    {
        $midasbuyToken = MidasbuyToken::findOrFail($id);
        return view('code-muakey.tools.midasbuy-token.edit', compact('midasbuyToken'));
    }

    public function update(UpdateMidasbuyTokenRequest $request, $id)
    {
        $midasbuyToken = MidasbuyToken::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {

            // Xóa ảnh cũ nếu có
            if ($midasbuyToken->image && file_exists(public_path($midasbuyToken->image))) {
                unlink(public_path($midasbuyToken->image));
            }

            $image = $request->file('image');

            // Tạo tên file mới
            $fileName = time() . '_' . $image->getClientOriginalName();

            // Thư mục lưu ảnh
            $destinationPath = public_path('uploads/midasbuy-token');

            // Tạo folder nếu chưa tồn tại
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Di chuyển file
            $image->move($destinationPath, $fileName);

            // Lưu đường dẫn vào DB
            $data['image'] = 'uploads/midasbuy-token/' . $fileName;
        }

        $midasbuyToken->update($data);

        return redirect()->back()->with('success', 'MidasBuy Token đã được cập nhật thành công!');
    }
}
