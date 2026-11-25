<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final class CreateOrderDto
{
    private int $tableId;

    private int $waiterId;

    private ?string $notes;

    /** @var array<int, OrderItemInputDto> */
    private array $items;

    /**
     * @param  array<int, OrderItemInputDto>  $items
     */
    public function __construct(
        int $tableId,
        int $waiterId,
        ?string $notes,
        array $items
    ) {
        $trimmedNotes = $notes !== null ? trim($notes) : null;
        $this->tableId = $tableId;
        $this->waiterId = $waiterId;
        $this->notes = ($trimmedNotes !== null && $trimmedNotes !== '') ? $trimmedNotes : null;
        $this->items = $items;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $tableId = isset($data['table_id']) && is_numeric($data['table_id']) ? (int) $data['table_id'] : 0;
        $waiterId = isset($data['waiter_id']) && is_numeric($data['waiter_id']) ? (int) $data['waiter_id'] : 0;
        $notes = isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null;

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

    public function tableId(): int
    {
        return $this->tableId;
    }

    public function waiterId(): int
    {
        return $this->waiterId;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return array<int, OrderItemInputDto>
     */
    public function items(): array
    {
        return $this->items;
    }
}
