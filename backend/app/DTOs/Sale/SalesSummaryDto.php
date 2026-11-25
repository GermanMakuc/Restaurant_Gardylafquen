<?php

declare(strict_types=1);

namespace App\DTOs\Sale;

final readonly class SalesSummaryDto
{
    public function __construct(
        public int $count,
        public int $subtotalCents,
        public int $tipCents,
        public int $totalCents,
    ) {
    }
}
