<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MidasbuyToken\StoreMidasbuyTokenRequest;
use App\Models\MidasbuyToken;
use Illuminate\Http\Request;

class MidasbuyTokenController extends Controller
{
    public function index()
    {
        $midasbuyToken = MidasbuyToken::where('status', 'pending')
            ->first();
        return response()->json($midasbuyToken);
    }

    public function store(StoreMidasbuyTokenRequest $request)
    {
        $midasbuyToken = MidasbuyToken::create($request->validated());
        return response()->json($midasbuyToken, 201);
    }
}
