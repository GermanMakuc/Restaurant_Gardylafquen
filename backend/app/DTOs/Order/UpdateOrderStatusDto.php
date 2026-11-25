<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final readonly class UpdateOrderStatusDto
{
    public function __construct(
        public string $status,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $status = isset($data['status']) && is_string($data['status']) ? trim($data['status']) : '';

        return new self($status);
    }
}
