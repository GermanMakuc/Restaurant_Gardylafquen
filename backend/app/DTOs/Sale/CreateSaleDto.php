<?php

declare(strict_types=1);

namespace App\DTOs\Sale;

final readonly class CreateSaleDto
{
    public function __construct(
        public int $orderId,
        public int $tipPercent = 10,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $orderId = isset($data['order_id']) && is_numeric($data['order_id']) ? (int) $data['order_id'] : 0;
        $rawTip = isset($data['tip_percent']) && is_numeric($data['tip_percent']) ? (int) $data['tip_percent'] : 10;

        return new self($orderId, max(0, min(100, $rawTip)));
    }
}
