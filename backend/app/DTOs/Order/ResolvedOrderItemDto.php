<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final readonly class ResolvedOrderItemDto
{
    public function __construct(
        public int $productId,
        public string $productName,
        public int $unitPriceCents,
        public int $quantity,
        public int $lineTotalCents,
    ) {
    }
}
