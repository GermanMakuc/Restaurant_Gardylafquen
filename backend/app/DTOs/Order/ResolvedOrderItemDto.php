<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final class ResolvedOrderItemDto
{
    private int $productId;

    private string $productName;

    private int $unitPriceCents;

    private int $quantity;

    private int $lineTotalCents;

    public function __construct(
        int $productId,
        string $productName,
        int $unitPriceCents,
        int $quantity,
        int $lineTotalCents
    ) {
        $this->productId = $productId;
        $this->productName = $productName;
        $this->unitPriceCents = $unitPriceCents;
        $this->quantity = $quantity;
        $this->lineTotalCents = $lineTotalCents;
    }

    public function productId(): int
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
    }

    public function unitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function lineTotalCents(): int
    {
        return $this->lineTotalCents;
    }
}
