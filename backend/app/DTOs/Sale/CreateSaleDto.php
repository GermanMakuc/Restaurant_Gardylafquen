<?php

declare(strict_types=1);

namespace App\DTOs\Sale;

final class CreateSaleDto
{
    private int $orderId;

    private int $tipPercent;

    public function __construct(int $orderId, int $tipPercent = 10)
    {
        $this->orderId = $orderId;
        $this->tipPercent = max(0, min(100, $tipPercent));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $orderId = isset($data['order_id']) && is_numeric($data['order_id']) ? (int) $data['order_id'] : 0;
        $tipPercent = isset($data['tip_percent']) && is_numeric($data['tip_percent']) ? (int) $data['tip_percent'] : 10;

        return new self($orderId, $tipPercent);
    }

    public function orderId(): int
    {
        return $this->orderId;
    }

    public function tipPercent(): int
    {
        return $this->tipPercent;
    }
}
