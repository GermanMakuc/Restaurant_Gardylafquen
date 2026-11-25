<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Product\CreateProductDto;
use App\DTOs\Product\ListProductsDto;
use App\DTOs\Product\UpdateProductDto;
use App\DTOs\Product\UploadProductImageDto;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ProductService;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProductServiceTest extends TestCase
{
    public function test_list_delegates_to_repository(): void
    {
        $dto = new ListProductsDto('Salmón', 2, 'name', 10);
        /** @var LengthAwarePaginator&MockObject $paginator */
        $paginator = $this->createMock(LengthAwarePaginator::class);

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())
            ->method('paginate')
            ->with($dto)
            ->willReturn($paginator);

        /** @var CategoryRepositoryInterface&MockObject $categories */
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        /** @var FilesystemFactory&MockObject $filesystems */
        $filesystems = $this->createMock(FilesystemFactory::class);

        $service = new ProductService($products, $categories, $filesystems, new ImageManager(['driver' => 'gd']));

        $this->assertSame($paginator, $service->list($dto));
    }

    public function test_find_or_fail_throws_when_missing(): void
    {
        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('findById')->with(42)->willReturn(null);

        /** @var CategoryRepositoryInterface&MockObject $categories */
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        /** @var FilesystemFactory&MockObject $filesystems */
        $filesystems = $this->createMock(FilesystemFactory::class);

        $service = new ProductService($products, $categories, $filesystems, new ImageManager(['driver' => 'gd']));

        $this->expectException(ProductNotFoundException::class);
        $service->findOrFail(42);
    }

    public function test_create_persists_product_when_category_exists(): void
    {
        $dto = new CreateProductDto('Limonada', 3, 320000, 'Con menta');
        $category = new Category(['name' => 'Bebidas']);
        $category->id = 3;
        $created = new Product(['name' => 'Limonada', 'category_id' => 3, 'price_cents' => 320000]);
        $created->id = 10;

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('create')->with($dto)->willReturn($created);

        /** @var CategoryRepositoryInterface&MockObject $categories */
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->expects($this->once())->method('findById')->with(3)->willReturn($category);

        /** @var FilesystemFactory&MockObject $filesystems */
        $filesystems = $this->createMock(FilesystemFactory::class);

        $service = new ProductService($products, $categories, $filesystems, new ImageManager(['driver' => 'gd']));

        $this->assertSame($created, $service->create($dto));
    }

    public function test_create_throws_when_category_does_not_exist(): void
    {
        $dto = new CreateProductDto('Limonada', 99, 320000);

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->never())->method('create');

        /** @var CategoryRepositoryInterface&MockObject $categories */
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->expects($this->once())->method('findById')->with(99)->willReturn(null);

        /** @var FilesystemFactory&MockObject $filesystems */
        $filesystems = $this->createMock(FilesystemFactory::class);

        $service = new ProductService($products, $categories, $filesystems, new ImageManager(['driver' => 'gd']));

        $this->expectException(CategoryNotFoundException::class);
        $service->create($dto);
    }

    public function test_update_modifies_product_when_product_and_category_exist(): void
    {
        $existing = new Product(['name' => 'Café', 'category_id' => 1, 'price_cents' => 200000]);
        $existing->id = 7;
        $updated = new Product(['name' => 'Café Doble', 'category_id' => 2, 'price_cents' => 250000]);
        $updated->id = 7;
        $category = new Category(['name' => 'Cafetería']);
        $category->id = 2;
        $dto = new UpdateProductDto('Café Doble', 2, 250000);

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('findById')->with(7)->willReturn($existing);
        $products->expects($this->once())->method('update')->with($existing, $dto)->willReturn($updated);

        /** @var CategoryRepositoryInterface&MockObject $categories */
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->expects($this->once())->method('findById')->with(2)->willReturn($category);

        /** @var FilesystemFactory&MockObject $filesystems */
        $filesystems = $this->createMock(FilesystemFactory::class);

        $service = new ProductService($products, $categories, $filesystems, new ImageManager(['driver' => 'gd']));

        $this->assertSame($updated, $service->update(7, $dto));
    }

    public function test_upload_image_stores_resized_image_and_deletes_old_file(): void
    {
        $existing = new Product(['name' => 'Kuchen', 'category_id' => 1, 'price_cents' => 450000, 'image_path' => 'products/old.jpg']);
        $existing->id = 8;
        $updated = new Product(['name' => 'Kuchen', 'category_id' => 1, 'price_cents' => 450000, 'image_path' => 'products/new.jpg']);
        $updated->id = 8;

        $file = UploadedFile::fake()->image('kuchen.png', 800, 600);

        /** @var Filesystem&MockObject $disk */
        $disk = $this->createMock(Filesystem::class);
        $disk->expects($this->once())
            ->method('put')
            ->with(
                $this->callback(static function (string $path): bool {
                    return strpos($path, 'products/8-') === 0 && substr($path, -4) === '.jpg';
                }),
                $this->isType('string')
            )
            ->willReturn(true);
        $disk->expects($this->once())
            ->method('delete')
            ->with('products/old.jpg')
            ->willReturn(true);

        /** @var FilesystemFactory&MockObject $filesystems */
        $filesystems = $this->createMock(FilesystemFactory::class);
        $filesystems->expects($this->once())->method('disk')->with('public')->willReturn($disk);

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('findById')->with(8)->willReturn($existing);
        $products->expects($this->once())
            ->method('updateImagePath')
            ->with($existing, $this->stringStartsWith('products/8-'))
            ->willReturn($updated);

        /** @var CategoryRepositoryInterface&MockObject $categories */
        $categories = $this->createMock(CategoryRepositoryInterface::class);

        $service = new ProductService($products, $categories, $filesystems, new ImageManager(['driver' => 'gd']), 'public');
        $result = $service->uploadImage(8, new UploadProductImageDto($file));

        $this->assertSame($updated, $result);
    }

    public function test_delete_soft_deletes_product(): void
    {
        $existing = new Product(['name' => 'Té', 'category_id' => 1, 'price_cents' => 150000]);
        $existing->id = 12;

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('findById')->with(12)->willReturn($existing);
        $products->expects($this->once())->method('delete')->with($existing);

        /** @var CategoryRepositoryInterface&MockObject $categories */
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        /** @var FilesystemFactory&MockObject $filesystems */
        $filesystems = $this->createMock(FilesystemFactory::class);

        $service = new ProductService($products, $categories, $filesystems, new ImageManager(['driver' => 'gd']));
        $service->delete(12);
    }
}
