<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', static fn (): JsonResponse => new JsonResponse([
    'service' => 'restaurant-gardylafquen-api',
    'api' => '/api/v1/health',
]));
