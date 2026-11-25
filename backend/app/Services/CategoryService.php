<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Category\CreateCategoryDto;
use App\DTOs\Category\ListCategoriesDto;
use App\DTOs\Category\UpdateCategoryDto;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\DuplicateCategoryException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, Category>
     */
    public function list(ListCategoriesDto $dto): LengthAwarePaginator
    {
        return $this->categories->paginate($dto);
    }

    public function findOrFail(int $id): Category
    {
        $category = $this->categories->findById($id);

        if ($category === null) {
            throw new CategoryNotFoundException($id);
        }

        return $category;
    }

    public function create(CreateCategoryDto $dto): Category
    {
        if ($this->categories->existsByName($dto->name())) {
            throw new DuplicateCategoryException($dto->name());
        }

        return $this->categories->create($dto);
    }

    public function update(int $id, UpdateCategoryDto $dto): Category
    {
        $category = $this->findOrFail($id);

        if ($this->categories->existsByName($dto->name(), $id)) {
            throw new DuplicateCategoryException($dto->name());
        }

        return $this->categories->update($category, $dto);
    }

    public function delete(int $id): void
    {
        $category = $this->findOrFail($id);

        $this->categories->delete($category);
    }
}
