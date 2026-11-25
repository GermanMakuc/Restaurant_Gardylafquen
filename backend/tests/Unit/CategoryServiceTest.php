<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Category\CreateCategoryDto;
use App\DTOs\Category\ListCategoriesDto;
use App\DTOs\Category\UpdateCategoryDto;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\DuplicateCategoryException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\CategoryService;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CategoryServiceTest extends TestCase
{
    public function test_list_delegates_to_repository(): void
    {
        $dto = new ListCategoriesDto('Bebidas', 'name', 10);
        $paginator = new LengthAwarePaginator([], 0, 10);

        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('paginate')
            ->with($dto)
            ->willReturn($paginator);

        $service = new CategoryService($repository);

        $this->assertSame($paginator, $service->list($dto));
    }

    public function test_find_or_fail_returns_category_when_found(): void
    {
        $category = new Category(['name' => 'Entradas']);
        $category->id = 3;

        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(3)
            ->willReturn($category);

        $service = new CategoryService($repository);

        $this->assertSame($category, $service->findOrFail(3));
    }

    public function test_find_or_fail_throws_when_missing(): void
    {
        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(99)
            ->willReturn(null);

        $service = new CategoryService($repository);

        $this->expectException(CategoryNotFoundException::class);
        $service->findOrFail(99);
    }

    public function test_create_persists_when_name_is_unique(): void
    {
        $dto = new CreateCategoryDto('Postres');
        $created = new Category(['name' => 'Postres']);
        $created->id = 1;

        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('existsByName')
            ->with('Postres', null)
            ->willReturn(false);
        $repository->expects($this->once())
            ->method('create')
            ->with($dto)
            ->willReturn($created);

        $service = new CategoryService($repository);

        $this->assertSame($created, $service->create($dto));
    }

    public function test_create_throws_when_name_already_exists(): void
    {
        $dto = new CreateCategoryDto('Bebidas');

        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('existsByName')
            ->with('Bebidas', null)
            ->willReturn(true);
        $repository->expects($this->never())
            ->method('create');

        $service = new CategoryService($repository);

        $this->expectException(DuplicateCategoryException::class);
        $service->create($dto);
    }

    public function test_update_modifies_category_when_found_and_unique(): void
    {
        $existing = new Category(['name' => 'Bebidas']);
        $existing->id = 5;
        $updated = new Category(['name' => 'Bebidas Frías']);
        $updated->id = 5;
        $dto = new UpdateCategoryDto('Bebidas Frías');

        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($existing);
        $repository->expects($this->once())
            ->method('existsByName')
            ->with('Bebidas Frías', 5)
            ->willReturn(false);
        $repository->expects($this->once())
            ->method('update')
            ->with($existing, $dto)
            ->willReturn($updated);

        $service = new CategoryService($repository);

        $this->assertSame($updated, $service->update(5, $dto));
    }

    public function test_update_throws_when_name_conflicts_with_another_category(): void
    {
        $existing = new Category(['name' => 'Bebidas']);
        $existing->id = 5;
        $dto = new UpdateCategoryDto('Postres');

        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(5)
            ->willReturn($existing);
        $repository->expects($this->once())
            ->method('existsByName')
            ->with('Postres', 5)
            ->willReturn(true);
        $repository->expects($this->never())
            ->method('update');

        $service = new CategoryService($repository);

        $this->expectException(DuplicateCategoryException::class);
        $service->update(5, $dto);
    }

    public function test_delete_removes_existing_category(): void
    {
        $existing = new Category(['name' => 'Entradas']);
        $existing->id = 2;

        /** @var CategoryRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(CategoryRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findById')
            ->with(2)
            ->willReturn($existing);
        $repository->expects($this->once())
            ->method('delete')
            ->with($existing);

        $service = new CategoryService($repository);
        $service->delete(2);
    }
}
