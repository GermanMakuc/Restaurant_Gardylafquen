<?php

declare(strict_types=1);

namespace App\DTOs\Product;

use Illuminate\Http\UploadedFile;

final class UploadProductImageDto
{
    private UploadedFile $image;

    public function __construct(UploadedFile $image)
    {
        $this->image = $image;
    }

    public function image(): UploadedFile
    {
        return $this->image;
    }
}
