<?php

declare(strict_types=1);

namespace App\Http\Requests\Table;

use App\DTOs\Table\CreateTableDto;
use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;

final class StoreTableRequest extends FormRequest
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
            'number' => ['required', 'integer', 'min:1', 'max:9999'],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'status' => ['sometimes', 'string', 'in:' . implode(',', TableStatus::values())],
            'label' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    public function toDto(): CreateTableDto
    {
        return CreateTableDto::fromArray($this->validated());
    }
}
