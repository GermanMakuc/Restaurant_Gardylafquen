<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DTOs\Order\ListOrdersDto;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;

final class ListOrdersRequest extends FormRequest
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
            'filter.status' => ['sometimes', 'nullable', 'string', 'in:' . implode(',', OrderStatus::values())],
            'filter.table_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'filter.waiter_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'sort' => ['sometimes', 'string', 'in:id,-id,subtotal_cents,-subtotal_cents,created_at,-created_at'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function toDto(): ListOrdersDto
    {
        return ListOrdersDto::fromArray($this->validated());
    }
}
