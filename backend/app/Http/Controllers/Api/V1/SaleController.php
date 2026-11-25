<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\ListSalesRequest;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Resources\SaleResource;
use App\Http\Resources\SalesSummaryResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SaleController extends Controller
{
    private SaleService $service;

    public function __construct(SaleService $service)
    {
        $this->service = $service;
    }

    public function index(ListSalesRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Sale::class);

        $sales = $this->service->list($request->toDto());

        return SaleResource::collection($sales);
    }

    public function summary(ListSalesRequest $request): SalesSummaryResource
    {
        $this->authorize('viewAny', Sale::class);

        return SalesSummaryResource::make($this->service->summary($request->toDto()));
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $this->authorize('create', Sale::class);

        $sale = $this->service->create($request->toDto());

        $response = SaleResource::make($sale)->response();
        $response->setStatusCode(201);
        $response->headers->set('Location', $request->getSchemeAndHttpHost() . '/api/v1/sales/' . $sale->id);

        return $response;
    }

    public function show(string $sale): SaleResource
    {
        $this->authorize('view', Sale::class);

        return SaleResource::make($this->service->findOrFail((int) $sale));
    }
}
