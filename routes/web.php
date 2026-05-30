<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\CodeMuakey\IndexController::class, 'index'])->name('index');
Route::get('/tools/login', [App\Http\Controllers\CodeMuakey\LoginController::class, 'showLoginForm'])->name('tools.login');

Route::prefix('/tools')->middleware('auth.basic')->group(function () {
    Route::get('/manager', function () {
        return view('code-muakey.tools.manager');
    })->name('manager-tools');

    Route::resources([
        'midasbuy-japan' => App\Http\Controllers\CodeMuakey\MidasBuyJapanController::class,
        'wwm-order' => App\Http\Controllers\CodeMuakey\WwmOrderController::class,
        'netflix' => App\Http\Controllers\CodeMuakey\NetflixController::class,
        'midasbuy-token' => App\Http\Controllers\CodeMuakey\MidasbuyTokenController::class,
    ]);

    Route::get("/netflix-export-form-add", [App\Http\Controllers\CodeMuakey\NetflixController::class, 'exportFormAdd'])->name('netflix.export-form-add');
});

Route::middleware('simple.auth')->group(function () {
    Route::resources([
        'token-codes' => App\Http\Controllers\CodeMuakey\TokenCodeController::class,
    ]);
}); 
