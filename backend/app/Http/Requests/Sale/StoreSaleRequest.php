<?php

declare(strict_types=1);

namespace App\Http\Requests\Sale;

use App\DTOs\Sale\CreateSaleDto;
use Illuminate\Foundation\Http\FormRequest;

final class StoreSaleRequest extends FormRequest
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
            'order_id' => ['required', 'integer', 'exists:restaurant_orders,id'],
            'tip_percent' => ['sometimes', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function toDto(): CreateSaleDto
    {
        return CreateSaleDto::fromArray($this->validated());
    }
}
