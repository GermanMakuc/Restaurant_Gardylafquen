<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Order\ListOrdersDto;
use App\DTOs\Order\ResolvedOrderItemDto;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class EloquentOrderRepository implements OrderRepositoryInterface
{
    /** @var array<string, array{0: string, 1: string}> */
    private const SORT_MAP = [
        'id' => ['id', 'asc'],
        '-id' => ['id', 'desc'],
        'subtotal_cents' => ['subtotal_cents', 'asc'],
        '-subtotal_cents' => ['subtotal_cents', 'desc'],
        'created_at' => ['created_at', 'asc'],
        '-created_at' => ['created_at', 'desc'],
    ];

    public function __construct(private Order $model)
    {
    }

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginate(ListOrdersDto $dto): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['table', 'waiter', 'items']);

        if ($dto->status !== null) {
            $query->where('status', $dto->status);
        }

        if ($dto->tableId !== null) {
            $query->where('table_id', $dto->tableId);
        }

        if ($dto->waiterId !== null) {
            $query->where('waiter_id', $dto->waiterId);
        }

        $sortConfig = self::SORT_MAP[$dto->sort] ?? self::SORT_MAP['-created_at'];
        $query->orderBy($sortConfig[0], $sortConfig[1]);

        if ($sortConfig[0] !== 'id') {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($dto->perPage);
    }

    public function findById(int $id): ?Order
    {
        /** @var Order|null $order */
        $order = $this->model->newQuery()->with(['table', 'waiter', 'items'])->find($id);

        return $order;
    }

    public function hasActiveOrderForTable(int $tableId, ?int $ignoreOrderId = null): bool
    {
        $query = $this->model->newQuery()
            ->where('table_id', $tableId)
            ->whereIn('status', OrderStatus::activeValues());

        if ($ignoreOrderId !== null) {
            $query->where('id', '!=', $ignoreOrderId);
        }

        return $query->exists();
    }

    public function createWithItems(
        int $tableId,
        int $waiterId,
        string $status,
        ?string $notes,
        int $subtotalCents,
        array $items
    ): Order {
        /** @var Order $order */
        $order = $this->model->newQuery()->create([
            'table_id' => $tableId,
            'waiter_id' => $waiterId,
            'status' => $status,
            'notes' => $notes,
            'subtotal_cents' => $subtotalCents,
        ]);

        $this->syncItems($order, $items);

        /** @var Order $fresh */
        $fresh = $order->fresh(['table', 'waiter', 'items']);

        return $fresh;
    }

    public function replaceWithItems(
        Order $order,
        int $tableId,
        int $waiterId,
        ?string $notes,
        int $subtotalCents,
        array $items
    ): Order {
        $order->update([
            'table_id' => $tableId,
            'waiter_id' => $waiterId,
            'notes' => $notes,
            'subtotal_cents' => $subtotalCents,
        ]);

        $order->items()->delete();
        $this->syncItems($order, $items);

        /** @var Order $fresh */
        $fresh = $order->fresh(['table', 'waiter', 'items']);

        return $fresh;
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        /** @var Order $fresh */
        $fresh = $order->fresh(['table', 'waiter', 'items']);

        return $fresh;
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }

    /**
     * @param  array<int, ResolvedOrderItemDto>  $items
     */
    private function syncItems(Order $order, array $items): void
    {
        $rows = [];
        foreach ($items as $item) {
            $rows[] = [
                'product_id' => $item->productId,
                'product_name' => $item->productName,
                'unit_price_cents' => $item->unitPriceCents,
                'quantity' => $item->quantity,
                'line_total_cents' => $item->lineTotalCents,
            ];
        }

        $order->items()->createMany($rows);
    }
}
