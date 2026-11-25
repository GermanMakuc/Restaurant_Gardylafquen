<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final readonly class CreateOrderDto
{
    /**
     * @param  array<int, OrderItemInputDto>  $items
     */
    public function __construct(
        public int $tableId,
        public int $waiterId,
        public ?string $notes,
        public array $items,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $tableId = isset($data['table_id']) && is_numeric($data['table_id']) ? (int) $data['table_id'] : 0;
        $waiterId = isset($data['waiter_id']) && is_numeric($data['waiter_id']) ? (int) $data['waiter_id'] : 0;
        $rawNotes = isset($data['notes']) && is_string($data['notes']) ? trim($data['notes']) : null;
        $notes = ($rawNotes !== null && $rawNotes !== '') ? $rawNotes : null;

        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $rawItem) {
                if (is_array($rawItem)) {
                    $productId = isset($rawItem['product_id']) && is_numeric($rawItem['product_id']) ? (int) $rawItem['product_id'] : 0;
                    $quantity = isset($rawItem['quantity']) && is_numeric($rawItem['quantity']) ? (int) $rawItem['quantity'] : 0;
                    $items[] = new OrderItemInputDto($productId, $quantity);
                }
            }
        }

        return new self($tableId, $waiterId, $notes, $items);
    }
}
