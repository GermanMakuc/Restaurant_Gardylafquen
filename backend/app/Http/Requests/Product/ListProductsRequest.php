<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\DTOs\Product\ListProductsDto;
use Illuminate\Foundation\Http\FormRequest;

final class ListProductsRequest extends FormRequest
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
            'filter' => ['sometimes', 'array'],
            'filter.name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'filter.category_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'sort' => ['sometimes', 'string', 'in:id,-id,name,-name,price_cents,-price_cents,created_at,-created_at'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): ListProductsDto
    {
        return ListProductsDto::fromArray($this->validated());
    }
}
