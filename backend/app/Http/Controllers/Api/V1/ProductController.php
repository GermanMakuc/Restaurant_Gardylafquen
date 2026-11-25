<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ListProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UploadProductImageRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController extends Controller
{
    private ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index(ListProductsRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->service->list($request->toDto());

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->service->create($request->toDto());

        $response = ProductResource::make($product)->response();
        $response->setStatusCode(201);
        $response->headers->set('Location', $request->getSchemeAndHttpHost() . '/api/v1/products/' . $product->id);

        return $response;
    }

    public function show(string $product): ProductResource
    {
        $this->authorize('view', Product::class);

        return ProductResource::make($this->service->findOrFail((int) $product));
    }

    public function update(UpdateProductRequest $request, string $product): ProductResource
    {
        $this->authorize('update', Product::class);

        $updated = $this->service->update((int) $product, $request->toDto());

        return ProductResource::make($updated);
    }

    public function uploadImage(UploadProductImageRequest $request, string $product): ProductResource
    {
        $this->authorize('update', Product::class);

        $updated = $this->service->uploadImage((int) $product, $request->toDto());

        return ProductResource::make($updated);
    }

    public function destroy(string $product): JsonResponse
    {
        $this->authorize('delete', Product::class);

        $this->service->delete((int) $product);

        return new JsonResponse(null, 204);
    }
}
