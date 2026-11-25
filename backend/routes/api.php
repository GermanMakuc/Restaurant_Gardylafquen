<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\TableController;
use App\Http\Controllers\Api\V1\WaiterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Versioned REST API routes (/api/v1/...). Protected with provisional api
| token guard until Laravel Sanctum upgrade in Phase 5 (AGENTS.md §13.3).
|
*/

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'show']);

    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::middleware('auth:api')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::post('/products/{product}/image', [ProductController::class, 'uploadImage']);
        Route::apiResource('waiters', WaiterController::class);
        Route::apiResource('tables', TableController::class);
        Route::apiResource('orders', OrderController::class);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);

        Route::get('/sales/summary', [SaleController::class, 'summary']);
        Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);
    });
});
