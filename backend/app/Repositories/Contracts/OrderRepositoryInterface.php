<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Order\ListOrdersDto;
use App\DTOs\Order\ResolvedOrderItemDto;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function paginate(ListOrdersDto $dto): LengthAwarePaginator;

    public function findById(int $id): ?Order;

    public function hasActiveOrderForTable(int $tableId, ?int $ignoreOrderId = null): bool;

    /**
     * @param  array<int, ResolvedOrderItemDto>  $items
     */
    public function createWithItems(
        int $tableId,
        int $waiterId,
        string $status,
        ?string $notes,
        int $subtotalCents,
        array $items
    ): Order;

    /**
     * @param  array<int, ResolvedOrderItemDto>  $items
     */
    public function replaceWithItems(
        Order $order,
        int $tableId,
        int $waiterId,
        ?string $notes,
        int $subtotalCents,
        array $items
    ): Order;

    public function updateStatus(Order $order, string $status): Order;

    public function delete(Order $order): void;
}
