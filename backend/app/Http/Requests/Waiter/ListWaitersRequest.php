<?php

declare(strict_types=1);

namespace App\Http\Requests\Waiter;

use App\DTOs\Waiter\ListWaitersDto;
use Illuminate\Foundation\Http\FormRequest;

final class ListWaitersRequest extends FormRequest
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
            'filter.is_active' => ['sometimes', 'nullable', 'in:true,false,1,0'],
            'sort' => ['sometimes', 'string', 'in:id,-id,name,-name,created_at,-created_at'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): ListWaitersDto
    {
        return ListWaitersDto::fromArray($this->validated());
    }
}
