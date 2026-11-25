<?php

declare(strict_types=1);

namespace App\Http\Requests\Table;

use App\DTOs\Table\ListTablesDto;
use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;

final class ListTablesRequest extends FormRequest
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
            'filter.status' => ['sometimes', 'nullable', 'string', 'in:' . implode(',', TableStatus::values())],
            'sort' => ['sometimes', 'string', 'in:id,-id,number,-number,capacity,-capacity,created_at,-created_at'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): ListTablesDto
    {
        return ListTablesDto::fromArray($this->validated());
    }
}
