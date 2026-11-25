<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Table\ListTablesRequest;
use App\Http\Requests\Table\StoreTableRequest;
use App\Http\Requests\Table\UpdateTableRequest;
use App\Http\Resources\TableResource;
use App\Services\TableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TableController extends Controller
{
    private TableService $service;

    public function __construct(TableService $service)
    {
        $this->service = $service;
    }

    public function index(ListTablesRequest $request): AnonymousResourceCollection
    {
        $tables = $this->service->list($request->toDto());

        return TableResource::collection($tables);
    }

    public function store(StoreTableRequest $request): JsonResponse
    {
        $table = $this->service->create($request->toDto());

        $response = TableResource::make($table)->response();
        $response->setStatusCode(201);
        $response->headers->set('Location', $request->getSchemeAndHttpHost() . '/api/v1/tables/' . $table->id);

        return $response;
    }

    public function show(string $table): TableResource
    {
        return TableResource::make($this->service->findOrFail((int) $table));
    }

    public function update(UpdateTableRequest $request, string $table): TableResource
    {
        $updated = $this->service->update((int) $table, $request->toDto());

        return TableResource::make($updated);
    }

    public function destroy(string $table): JsonResponse
    {
        $this->service->delete((int) $table);

        return new JsonResponse(null, 204);
    }
}
