<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Category\CreateCategoryDto;
use App\DTOs\Category\ListCategoriesDto;
use App\DTOs\Category\UpdateCategoryDto;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginate(ListCategoriesDto $dto): LengthAwarePaginator;

    public function findById(int $id): ?Category;

    public function existsByName(string $name, ?int $ignoreId = null): bool;

    public function create(CreateCategoryDto $dto): Category;

    public function update(Category $category, UpdateCategoryDto $dto): Category;

    public function delete(Category $category): void;
}
