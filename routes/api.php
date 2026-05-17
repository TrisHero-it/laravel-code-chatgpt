<?php

use Illuminate\Support\Facades\Route;

Route::get('/midasbuy-japan-orders', [App\Http\Controllers\Api\MidasbuyJapanController::class, 'index']);
Route::get("/add-midasbuy-japan-order", [App\Http\Controllers\Api\MidasbuyJapanController::class, 'store']);

Route::get('/where-wind-meet-order', [App\Http\Controllers\Api\WhereWindMeetController::class, 'index']);

Route::get("/search-netflix-account", [App\Http\Controllers\Api\CodeController::class, 'search']);

Route::get("/midasbuy-token-order", [App\Http\Controllers\Api\MidasbuyTokenController::class, 'index']);

Route::get("/token-code", [App\Http\Controllers\Api\TokenCodeController::class, 'index']);
Route::get("/token-code/{id}", [App\Http\Controllers\Api\TokenCodeController::class, 'update']);