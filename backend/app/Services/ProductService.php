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
use RuntimeException;

final class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly FilesystemFactory $filesystems,
        private readonly string $imageDisk = 'public',
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
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

        $encoded = $this->renderSquareJpeg($dto->image()->getRealPath());

        $newPath = 'products/' . $product->id . '-' . Str::random(20) . '.jpg';
        $disk->put($newPath, $encoded);

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

    private function renderSquareJpeg(string|false $realPath, int $size = 400): string
    {
        if ($realPath === false) {
            throw new RuntimeException('No se pudo localizar la imagen subida.');
        }

        $raw = file_get_contents($realPath);
        if ($raw === false) {
            throw new RuntimeException('No se pudo leer la imagen subida.');
        }

        $src = imagecreatefromstring($raw);
        if ($src === false) {
            throw new RuntimeException('Formato de imagen no soportado.');
        }

        $srcWidth = imagesx($src);
        $srcHeight = imagesy($src);
        $cropSize = max(1, min($srcWidth, $srcHeight));
        $targetSize = max(1, min($cropSize, max(1, $size)));
        $srcX = intdiv($srcWidth - $cropSize, 2);
        $srcY = intdiv($srcHeight - $cropSize, 2);

        $dst = imagecreatetruecolor($targetSize, $targetSize);
        if ($dst === false) {
            imagedestroy($src);
            throw new RuntimeException('No se pudo inicializar el lienzo de imagen.');
        }

        $white = imagecolorallocate($dst, 255, 255, 255);
        if ($white !== false) {
            imagefill($dst, 0, 0, $white);
        }

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $targetSize, $targetSize, $cropSize, $cropSize);

        ob_start();
        imagejpeg($dst, null, 90);
        $encoded = (string) ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $encoded;
    }
}
