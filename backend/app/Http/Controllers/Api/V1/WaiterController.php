<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Waiter\ListWaitersRequest;
use App\Http\Requests\Waiter\StoreWaiterRequest;
use App\Http\Requests\Waiter\UpdateWaiterRequest;
use App\Http\Resources\WaiterResource;
use App\Services\WaiterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WaiterController extends Controller
{
    private WaiterService $service;

    public function __construct(WaiterService $service)
    {
        $this->service = $service;
    }

    public function index(ListWaitersRequest $request): AnonymousResourceCollection
    {
        $waiters = $this->service->list($request->toDto());

        return WaiterResource::collection($waiters);
    }

    public function store(StoreWaiterRequest $request): JsonResponse
    {
        $waiter = $this->service->create($request->toDto());

        $response = WaiterResource::make($waiter)->response();
        $response->setStatusCode(201);
        $response->headers->set('Location', $request->getSchemeAndHttpHost() . '/api/v1/waiters/' . $waiter->id);

        return $response;
    }

    public function show(string $waiter): WaiterResource
    {
        return WaiterResource::make($this->service->findOrFail((int) $waiter));
    }

    public function update(UpdateWaiterRequest $request, string $waiter): WaiterResource
    {
        $updated = $this->service->update((int) $waiter, $request->toDto());

        return WaiterResource::make($updated);
    }

    public function destroy(string $waiter): JsonResponse
    {
        $this->service->delete((int) $waiter);

        return new JsonResponse(null, 204);
    }
}
