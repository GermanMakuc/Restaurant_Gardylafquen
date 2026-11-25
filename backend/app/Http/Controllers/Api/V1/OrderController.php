<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ListOrdersRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrderController extends Controller
{
    private OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function index(ListOrdersRequest $request): AnonymousResourceCollection
    {
        $orders = $this->service->list($request->toDto());

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->service->create($request->toDto());

        $response = OrderResource::make($order)->response();
        $response->setStatusCode(201);
        $response->headers->set('Location', $request->getSchemeAndHttpHost() . '/api/v1/orders/' . $order->id);

        return $response;
    }

    public function show(string $order): OrderResource
    {
        return OrderResource::make($this->service->findOrFail((int) $order));
    }

    public function update(UpdateOrderRequest $request, string $order): OrderResource
    {
        $updated = $this->service->update((int) $order, $request->toDto());

        return OrderResource::make($updated);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $order): OrderResource
    {
        $updated = $this->service->updateStatus((int) $order, $request->toDto());

        return OrderResource::make($updated);
    }

    public function destroy(string $order): JsonResponse
    {
        $this->service->delete((int) $order);

        return new JsonResponse(null, 204);
    }
}
