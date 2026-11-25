<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DTOs\Order\CreateOrderDto;
use Illuminate\Foundation\Http\FormRequest;

final class StoreOrderRequest extends FormRequest
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
            'table_id' => ['required', 'integer', 'exists:tables,id'],
            'waiter_id' => ['required', 'integer', 'exists:waiters,id'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function toDto(): CreateOrderDto
    {
        return CreateOrderDto::fromArray($this->validated());
    }
}
