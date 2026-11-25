<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\DTOs\Order\UpdateOrderStatusDto;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['required', 'string', 'in:' . implode(',', [OrderStatus::WAITING, OrderStatus::SERVED, OrderStatus::CANCELLED])],
        ];
    }

    public function toDto(): UpdateOrderStatusDto
    {
        return UpdateOrderStatusDto::fromArray($this->validated());
    }
}
