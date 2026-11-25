<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\DTOs\Category\ListCategoriesDto;
use Illuminate\Foundation\Http\FormRequest;

final class ListCategoriesRequest extends FormRequest
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
            'filter.name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sort' => ['sometimes', 'string', 'in:id,-id,name,-name,created_at,-created_at'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): ListCategoriesDto
    {
        return ListCategoriesDto::fromArray($this->validated());
    }
}
