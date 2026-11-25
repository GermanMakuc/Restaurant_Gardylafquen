<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Category\CreateCategoryDto;
use App\DTOs\Category\ListCategoriesDto;
use App\DTOs\Category\UpdateCategoryDto;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /** @var array<string, array{0: string, 1: string}> */
    private const SORT_MAP = [
        'id' => ['id', 'asc'],
        '-id' => ['id', 'desc'],
        'name' => ['name', 'asc'],
        '-name' => ['name', 'desc'],
        'created_at' => ['created_at', 'asc'],
        '-created_at' => ['created_at', 'desc'],
    ];

    public function __construct(private Category $model)
    {
    }

    /**
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginate(ListCategoriesDto $dto): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($dto->nameFilter !== null) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $dto->nameFilter);
            $query->where('name', 'like', '%' . $escaped . '%');
        }

        $sortConfig = self::SORT_MAP[$dto->sort] ?? self::SORT_MAP['-created_at'];
        $query->orderBy($sortConfig[0], $sortConfig[1]);

        if ($sortConfig[0] !== 'id') {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($dto->perPage);
    }

    public function findById(int $id): ?Category
    {
        /** @var Category|null $category */
        $category = $this->model->newQuery()->find($id);

        return $category;
    }

    public function existsByName(string $name, ?int $ignoreId = null): bool
    {
        $query = $this->model->newQuery()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function create(CreateCategoryDto $dto): Category
    {
        /** @var Category $category */
        $category = $this->model->newQuery()->create([
            'name' => $dto->name,
        ]);

        return $category;
    }

    public function update(Category $category, UpdateCategoryDto $dto): Category
    {
        $category->update([
            'name' => $dto->name,
        ]);

        /** @var Category $fresh */
        $fresh = $category->fresh();

        return $fresh;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
