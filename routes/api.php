<?php

use Illuminate\Support\Facades\Route;

Route::get('/midasbuy-japan-orders', [App\Http\Controllers\Api\MidasbuyJapanController::class, 'index']);
Route::get("/add-midasbuy-japan-order", [App\Http\Controllers\Api\MidasbuyJapanController::class, 'store']);

Route::get('/where-wind-meet-order', [App\Http\Controllers\Api\WhereWindMeetController::class, 'index']);
