<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final class UpdateOrderStatusDto
{
    private string $status;

    public function __construct(string $status)
    {
        $this->status = trim($status);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $status = isset($data['status']) && is_string($data['status']) ? $data['status'] : '';

        return new self($status);
    }

    public function status(): string
    {
        return $this->status;
    }
}
