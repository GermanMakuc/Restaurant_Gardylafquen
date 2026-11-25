<?php

declare(strict_types=1);

namespace App\DTOs\Product;

use Illuminate\Http\UploadedFile;

final readonly class UploadProductImageDto
{
    public function __construct(
        public UploadedFile $image,
    ) {
    }
}
