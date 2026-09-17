<?php

namespace App\Http\Controllers\CodeMuakey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Netflix\StoreNetflixRequest;
use App\Models\Netflix;
use Illuminate\Http\Request;
use App\Exports\NetflixsExport;
use App\Imports\NetflixsImport;
use Maatwebsite\Excel\Facades\Excel;

class NetflixController extends Controller
{
    public function index()
    {
        $netflixes = Netflix::all();
        return view('code-muakey.tools.netflix.index', compact('netflixes'));
    }

    public function create()
    {
        return view('code-muakey.tools.netflix.add');
    }

    public function store(StoreNetflixRequest $request)
    {
        if (isset($request->excel_file)) {
            $dataExcels = $this->importFormAdd($request);
            $data = [];
            foreach ($dataExcels as $dataExcel) {
                if ($dataExcel[0] === 'Email' && $dataExcel[1] === 'Password') {
                    continue;
                }
                if (trim((string) $dataExcel[0]) === '') {
                    continue;
                }

                // Excel hay dư khoảng trắng và đổi mật khẩu toàn số thành kiểu số
                $data[] = [
                    'email' => mb_strtolower(trim((string) $dataExcel[0]), 'UTF-8'),
                    'password' => trim((string) $dataExcel[1]),
                    'token2fa' => $dataExcel[2] ?? null,
                    'expired_at' => $dataExcel[3] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Netflix::insert($data);
        } else {
            $validated = $request->validated();
            Netflix::create([
                'email' => mb_strtolower(trim($validated['email']), 'UTF-8'),
                'password' => trim((string) $validated['password']),
                'token2fa' => $validated['token2fa'] ?? null,
                'expired_at' => $validated['expired_at'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Tài khoản Netflix đã được thêm thành công.');
    }

    public function destroy($id)
    {
        $netflix = Netflix::findOrFail($id);
        $netflix->delete();

        return redirect()->route('netflix.index')->with('success', 'Tài khoản Netflix đã được xóa thành công.');
    }

    public function exportFormAdd()
    {
        return Excel::download(new NetflixsExport, 'netflix-form-add.xlsx');
    }

    public function importFormAdd(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = new NetflixsImport();
        Excel::import($import, $request->file('excel_file'));
        return $import->rows;
    }
}