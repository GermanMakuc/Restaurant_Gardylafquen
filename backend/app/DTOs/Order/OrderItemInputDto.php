<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final readonly class OrderItemInputDto
{
    public function __construct(
        public int $productId,
        public int $quantity,
    ) {
    }
}
