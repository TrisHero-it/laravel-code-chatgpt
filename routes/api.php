<?php

use Illuminate\Support\Facades\Route;

Route::get('/midasbuy-japan-orders', [App\Http\Controllers\Api\MidasbuyJapanController::class, 'index']);
Route::get("/add-midasbuy-japan-order", [App\Http\Controllers\Api\MidasbuyJapanController::class, 'store']);

Route::get('/where-wind-meet-order', [App\Http\Controllers\Api\WhereWindMeetController::class, 'index']);
Route::get("/add-where-wind-meet-order", [App\Http\Controllers\Api\WhereWindMeetController::class, 'store']);

Route::get("/search-netflix-account", [App\Http\Controllers\Api\CodeController::class, 'search']);

Route::get("/midasbuy-token-order", [App\Http\Controllers\Api\MidasbuyTokenController::class, 'index']);
Route::get("/add-midasbuy-token-order", [App\Http\Controllers\Api\MidasbuyTokenController::class, 'store']);
// Hoãn cả cụm đơn cùng mức token khi kho hết code mức đó (mặc định 30 phút)
Route::get("/midasbuy-token-order/delay-token", [App\Http\Controllers\Api\MidasbuyTokenController::class, 'delayToken']);
// Hoãn một đơn cụ thể (mặc định 30 phút) để tool làm các đơn khác trước
Route::get("/midasbuy-token-order/{id}/delay", [App\Http\Controllers\Api\MidasbuyTokenController::class, 'delay']);

Route::get("/token-code", [App\Http\Controllers\Api\TokenCodeController::class, 'index']);
Route::get("/token-code/{id}", [App\Http\Controllers\Api\TokenCodeController::class, 'update']);

Route::middleware('auth.basic')->group(function () {
    Route::apiResources([
        'midasbuy-tokens' => App\Http\Controllers\Api\MidasbuyTokenController::class,
        'midasbuy-weekly-cards' => App\Http\Controllers\Api\MidasbuyJapanController::class,
    ]);
});
