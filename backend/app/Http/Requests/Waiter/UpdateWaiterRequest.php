<?php

declare(strict_types=1);

namespace App\Http\Requests\Waiter;

use App\DTOs\Waiter\UpdateWaiterDto;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateWaiterRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): UpdateWaiterDto
    {
        return UpdateWaiterDto::fromArray($this->validated());
    }
}
