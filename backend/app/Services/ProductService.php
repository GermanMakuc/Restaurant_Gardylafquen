<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Product\CreateProductDto;
use App\DTOs\Product\ListProductsDto;
use App\DTOs\Product\UpdateProductDto;
use App\DTOs\Product\UploadProductImageDto;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;
use Intervention\Image\ImageManager;

final class ProductService
{
    private ProductRepositoryInterface $products;

    private CategoryRepositoryInterface $categories;

    private FilesystemFactory $filesystems;

    private ImageManager $images;

    private string $imageDisk;

    public function __construct(
        ProductRepositoryInterface $products,
        CategoryRepositoryInterface $categories,
        FilesystemFactory $filesystems,
        ImageManager $images,
        string $imageDisk = 'public'
    ) {
        $this->products = $products;
        $this->categories = $categories;
        $this->filesystems = $filesystems;
        $this->images = $images;
        $this->imageDisk = $imageDisk;
    }

    public function list(ListProductsDto $dto): LengthAwarePaginator
    {
        return $this->products->paginate($dto);
    }

    public function findOrFail(int $id): Product
    {
        $product = $this->products->findById($id);

        if ($product === null) {
            throw new ProductNotFoundException($id);
        }

        return $product;
    }

    public function create(CreateProductDto $dto): Product
    {
        if ($this->categories->findById($dto->categoryId()) === null) {
            throw new CategoryNotFoundException($dto->categoryId());
        }

        return $this->products->create($dto);
    }

    public function update(int $id, UpdateProductDto $dto): Product
    {
        $product = $this->findOrFail($id);

        if ($this->categories->findById($dto->categoryId()) === null) {
            throw new CategoryNotFoundException($dto->categoryId());
        }

        return $this->products->update($product, $dto);
    }

    public function uploadImage(int $id, UploadProductImageDto $dto): Product
    {
        $product = $this->findOrFail($id);
        $disk = $this->filesystems->disk($this->imageDisk);

        $image = $this->images->make($dto->image()->getRealPath());
        $image->fit(400, 400, static function (Constraint $constraint): void {
            $constraint->upsize();
        });

        $newPath = 'products/' . $product->id . '-' . Str::random(20) . '.jpg';
        $disk->put($newPath, (string) $image->encode('jpg', 90));

        $oldPath = $product->image_path;
        $updated = $this->products->updateImagePath($product, $newPath);

        if ($oldPath !== null && $oldPath !== '' && $oldPath !== $newPath) {
            $disk->delete($oldPath);
        }

        return $updated;
    }

    public function delete(int $id): void
    {
        $product = $this->findOrFail($id);

        $this->products->delete($product);
    }
}
