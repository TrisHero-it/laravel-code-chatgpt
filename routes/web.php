<?php

use Illuminate\Support\Facades\Route;

// Đăng nhập trang quản trị chỉ bật trên server; local (APP_ENV=local) bỏ qua
// cho tiện dev. Server PHẢI để APP_ENV khác "local" (vd production).
$requireLogin = !app()->environment('local');
$toolsAuth = $requireLogin ? ['auth.basic'] : [];
$tokenCodesAuth = $requireLogin ? ['simple.auth'] : [];

Route::get('/', [App\Http\Controllers\CodeMuakey\IndexController::class, 'index'])->name('index');
Route::get('/tools/login', [App\Http\Controllers\CodeMuakey\LoginController::class, 'showLoginForm'])->name('tools.login');

Route::prefix('/tools')->middleware($toolsAuth)->group(function () {
    Route::get('/manager', function () {
        return view('code-muakey.tools.manager');
    })->name('manager-tools');

    Route::get('/netease', function () {
        return view('code-muakey.tools.netease');
    })->name('netease-tools');

    Route::resources([
        'midasbuy-japan' => App\Http\Controllers\CodeMuakey\MidasBuyJapanController::class,
        'wwm-order' => App\Http\Controllers\CodeMuakey\WwmOrderController::class,
        'identity-order' => App\Http\Controllers\CodeMuakey\IdentityController::class,
        'blood-strike-order' => App\Http\Controllers\CodeMuakey\BloodStrikeController::class,
        'marvel-rivals-order' => App\Http\Controllers\CodeMuakey\MarvelRivalsController::class,
        'racing-master-order' => App\Http\Controllers\CodeMuakey\RacingMasterController::class,
        'dunk-city-dynasty-order' => App\Http\Controllers\CodeMuakey\DunkCityDynastyController::class,
        'midasbuy-token' => App\Http\Controllers\CodeMuakey\MidasbuyTokenController::class,
    ]);

    // Tra cuu UID + mo trang nap chinh chu NetEase
    Route::post("/netease/verify-uid", [App\Http\Controllers\CodeMuakey\NeteaseTopupController::class, "verifyUid"])->name("netease.verify-uid");
    Route::get("/netease/topup/{order}", [App\Http\Controllers\CodeMuakey\NeteaseTopupController::class, "topupLink"])->name("netease.topup");

    // Đọc tạm hộp thư netflix@lumekza.io.vn (API inbound-mail)
    Route::get("/inbound-mail", [App\Http\Controllers\CodeMuakey\InboundMailController::class, 'index'])->name('inbound-mail.index');
    Route::get("/inbound-mail/{id}", [App\Http\Controllers\CodeMuakey\InboundMailController::class, 'show'])->name('inbound-mail.show');

    Route::get("/netflix-export-form-add",[App\Http\Controllers\CodeMuakey\NetflixController::class, 'exportFormAdd'])->name('netflix.export-form-add');
});

Route::prefix('/tools')->middleware($toolsAuth)->group(function () {
    Route::resources([
        'netflix' => App\Http\Controllers\CodeMuakey\NetflixController::class,
    ]);
});

Route::middleware($tokenCodesAuth)->group(function () {
    Route::resources([
        'token-codes' => App\Http\Controllers\CodeMuakey\TokenCodeController::class,
    ]);
});
