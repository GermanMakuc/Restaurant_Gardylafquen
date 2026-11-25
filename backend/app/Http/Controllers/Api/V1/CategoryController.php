<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ListCategoriesRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class CategoryController extends Controller
{
    private CategoryService $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index(ListCategoriesRequest $request): AnonymousResourceCollection
    {
        $categories = $this->service->list($request->toDto());

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->service->create($request->toDto());

        $response = CategoryResource::make($category)->response();
        $response->setStatusCode(201);
        $response->headers->set('Location', $request->getSchemeAndHttpHost() . '/api/v1/categories/' . $category->id);

        return $response;
    }

    public function show(string $category): CategoryResource
    {
        return CategoryResource::make($this->service->findOrFail((int) $category));
    }

    public function update(UpdateCategoryRequest $request, string $category): CategoryResource
    {
        $updated = $this->service->update((int) $category, $request->toDto());

        return CategoryResource::make($updated);
    }

    public function destroy(string $category): JsonResponse
    {
        $this->service->delete((int) $category);

        return new JsonResponse(null, 204);
    }
}
