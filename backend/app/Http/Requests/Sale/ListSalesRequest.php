<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use App\DTOs\Sale\ListSalesDto;
use Illuminate\Foundation\Http\FormRequest;

final class ListSalesRequest extends FormRequest
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
            'filter.from' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'filter.to' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'sort' => ['sometimes', 'string', 'in:id,-id,paid_at,-paid_at,total_cents,-total_cents'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): ListSalesDto
    {
        return ListSalesDto::fromArray($this->validated());
    }
}
