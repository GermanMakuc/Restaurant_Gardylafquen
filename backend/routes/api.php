<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TableController;
use App\Http\Controllers\Api\V1\WaiterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Versioned REST API routes (/api/v1/...). Legacy web routes remain in
| the repository root until Phase 4. Authentication middleware is added in
| Phase 3 per AGENTS.md §11.
|
*/

Route::prefix('v1')->group(function (): void {
    Route::get('/health', [HealthController::class, 'show']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
    Route::post('/products/{product}/image', [ProductController::class, 'uploadImage']);
    Route::apiResource('waiters', WaiterController::class);
    Route::apiResource('tables', TableController::class);
    Route::apiResource('orders', OrderController::class);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);

    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
});
