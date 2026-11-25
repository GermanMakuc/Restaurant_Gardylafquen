<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Product\CreateProductDto;
use App\DTOs\Product\ListProductsDto;
use App\DTOs\Product\UpdateProductDto;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class EloquentProductRepository implements ProductRepositoryInterface
{
    /** @var array<string, array{0: string, 1: string}> */
    private const SORT_MAP = [
        'id' => ['id', 'asc'],
        '-id' => ['id', 'desc'],
        'name' => ['name', 'asc'],
        '-name' => ['name', 'desc'],
        'price_cents' => ['price_cents', 'asc'],
        '-price_cents' => ['price_cents', 'desc'],
        'created_at' => ['created_at', 'asc'],
        '-created_at' => ['created_at', 'desc'],
    ];

    public function __construct(private Product $model)
    {
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(ListProductsDto $dto): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('category');

        if ($dto->nameFilter !== null) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $dto->nameFilter);
            $query->where('name', 'like', '%' . $escaped . '%');
        }

        if ($dto->categoryId !== null) {
            $query->where('category_id', $dto->categoryId);
        }

        $sortConfig = self::SORT_MAP[$dto->sort] ?? self::SORT_MAP['-created_at'];
        $query->orderBy($sortConfig[0], $sortConfig[1]);

        if ($sortConfig[0] !== 'id') {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($dto->perPage);
    }

    public function findById(int $id): ?Product
    {
        /** @var Product|null $product */
        $product = $this->model->newQuery()->with('category')->find($id);

        return $product;
    }

    public function create(CreateProductDto $dto): Product
    {
        /** @var Product $product */
        $product = $this->model->newQuery()->create([
            'name' => $dto->name,
            'description' => $dto->description,
            'category_id' => $dto->categoryId,
            'price_cents' => $dto->priceCents,
            'price' => intdiv($dto->priceCents, 100),
            'path' => '',
            'image_path' => null,
        ]);

        /** @var Product $fresh */
        $fresh = $product->fresh(['category']);

        return $fresh;
    }

    public function update(Product $product, UpdateProductDto $dto): Product
    {
        $product->update([
            'name' => $dto->name,
            'description' => $dto->description,
            'category_id' => $dto->categoryId,
            'price_cents' => $dto->priceCents,
            'price' => intdiv($dto->priceCents, 100),
        ]);

        /** @var Product $fresh */
        $fresh = $product->fresh(['category']);

        return $fresh;
    }

    public function updateImagePath(Product $product, string $imagePath): Product
    {
        $product->update([
            'image_path' => $imagePath,
            'path' => $imagePath,
        ]);

        /** @var Product $fresh */
        $fresh = $product->fresh(['category']);

        return $fresh;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
