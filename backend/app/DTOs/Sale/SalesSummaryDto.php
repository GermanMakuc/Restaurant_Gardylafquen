<?php

declare(strict_types=1);

namespace App\DTOs\Sale;

final class SalesSummaryDto
{
    private int $count;

    private int $subtotalCents;

    private int $tipCents;

    private int $totalCents;

    public function __construct(
        int $count,
        int $subtotalCents,
        int $tipCents,
        int $totalCents
    ) {
        $this->count = $count;
        $this->subtotalCents = $subtotalCents;
        $this->tipCents = $tipCents;
        $this->totalCents = $totalCents;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function subtotalCents(): int
    {
        return $this->subtotalCents;
    }

    public function tipCents(): int
    {
        return $this->tipCents;
    }

    public function totalCents(): int
    {
        return $this->totalCents;
    }
}
