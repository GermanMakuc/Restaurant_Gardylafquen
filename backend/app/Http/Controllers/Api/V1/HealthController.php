<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthResource;
use App\Services\HealthService;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    private HealthService $service;

    public function __construct(HealthService $service)
    {
        $this->service = $service;
    }

    public function show(): JsonResponse
    {
        $status = $this->service->check();
        $httpStatus = $status->database() === 'ok' ? 200 : 503;

        return HealthResource::make($status)->response()->setStatusCode($httpStatus);
    }
}
