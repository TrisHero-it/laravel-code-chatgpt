<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\CodeMuakey\IndexController::class, 'index'])->name('index');
Route::get('/tools/login', [App\Http\Controllers\CodeMuakey\LoginController::class, 'showLoginForm'])->name('tools.login');

Route::prefix('/tools')->group(function () {
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
        'midasbuy-token' => App\Http\Controllers\CodeMuakey\MidasbuyTokenController::class,
    ]);

    // Tra cuu UID + mo trang nap chinh chu NetEase
    Route::post("/netease/verify-uid", [App\Http\Controllers\CodeMuakey\NeteaseTopupController::class, "verifyUid"])->name("netease.verify-uid");
    Route::get("/netease/topup/{order}", [App\Http\Controllers\CodeMuakey\NeteaseTopupController::class, "topupLink"])->name("netease.topup");

    Route::get("/netflix-export-form-add", [App\Http\Controllers\CodeMuakey\NetflixController::class, 'exportFormAdd'])->name('netflix.export-form-add');
});

Route::prefix('/tools')->group(function () {
    Route::resources([
        'netflix' => App\Http\Controllers\CodeMuakey\NetflixController::class,
    ]);
});

Route::resources([
    'token-codes' => App\Http\Controllers\CodeMuakey\TokenCodeController::class,
]);
