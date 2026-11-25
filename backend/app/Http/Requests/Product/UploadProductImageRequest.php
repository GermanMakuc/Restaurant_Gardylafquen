<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\DTOs\Product\UploadProductImageDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

final class UploadProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function toDto(): UploadProductImageDto
    {
        /** @var UploadedFile $file */
        $file = $this->file('image');

        return new UploadProductImageDto($file);
    }
}
