<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Product\CreateProductDto;
use App\DTOs\Product\ListProductsDto;
use App\DTOs\Product\UpdateProductDto;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(ListProductsDto $dto): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function create(CreateProductDto $dto): Product;

    public function update(Product $product, UpdateProductDto $dto): Product;

    public function updateImagePath(Product $product, string $imagePath): Product;

    public function delete(Product $product): void;
}
