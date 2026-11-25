<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\DTOs\Product\CreateProductDto;
use Illuminate\Foundation\Http\FormRequest;

final class StoreProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price_cents' => ['required', 'integer', 'min:0', 'max:100000000'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function toDto(): CreateProductDto
    {
        return CreateProductDto::fromArray($this->validated());
    }
}
