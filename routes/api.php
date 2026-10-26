<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogueApiController;
use App\Http\Controllers\Api\OrderApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RESTful API Routes - VaishnoMart Platform
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:api')->group(function () {
    // Public API Endpoints
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/categories', [CatalogueApiController::class, 'categories']);
    Route::get('/nearby-vendors', [CatalogueApiController::class, 'nearbyVendors']);
    Route::get('/products', [CatalogueApiController::class, 'products']);

    // Authenticated API Endpoints (Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Orders API
        Route::get('/orders', [OrderApiController::class, 'customerOrders']);
        Route::get('/orders/{id}', [OrderApiController::class, 'show'])->whereNumber('id');
    });
});
