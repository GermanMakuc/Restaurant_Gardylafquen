<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use App\DTOs\Category\UpdateCategoryDto;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    public function toDto(): UpdateCategoryDto
    {
        return UpdateCategoryDto::fromArray($this->validated());
    }
}
