<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

$apiDomain = env('API_DOMAIN', 'api.ms-smart-test.test');

Route::domain($apiDomain)->group(function () {

    Route::prefix('v1')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
        });

        Route::middleware('auth:sanctum')->group(function () {
            Route::prefix('auth')->group(function () {
                Route::get('/me', [AuthController::class, 'me']);
                Route::post('/logout', [AuthController::class, 'logout']);
            });
        });

    });
});
