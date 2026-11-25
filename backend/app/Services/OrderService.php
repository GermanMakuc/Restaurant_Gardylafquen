<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Order\CreateOrderDto;
use App\DTOs\Order\ListOrdersDto;
use App\DTOs\Order\OrderItemInputDto;
use App\DTOs\Order\ResolvedOrderItemDto;
use App\DTOs\Order\UpdateOrderDto;
use App\DTOs\Order\UpdateOrderStatusDto;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Exceptions\InactiveWaiterException;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderNotEditableException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\TableAlreadyOccupiedException;
use App\Exceptions\TableNotFoundException;
use App\Exceptions\WaiterNotFoundException;
use App\Models\Order;
use App\Models\Table;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\Contracts\WaiterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly TableRepositoryInterface $tables,
        private readonly WaiterRepositoryInterface $waiters,
        private readonly ProductRepositoryInterface $products,
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function list(ListOrdersDto $dto): LengthAwarePaginator
    {
        return $this->orders->paginate($dto);
    }

    public function findOrFail(int $id): Order
    {
        $order = $this->orders->findById($id);

        if ($order === null) {
            throw new OrderNotFoundException($id);
        }

        return $order;
    }

    public function create(CreateOrderDto $dto): Order
    {
        $table = $this->requireAvailableTable($dto->tableId());
        $this->requireActiveWaiter($dto->waiterId());
        $resolved = $this->resolveItems($dto->items());

        /** @var Order $created */
        $created = DB::transaction(function () use ($dto, $table, $resolved): Order {
            $order = $this->orders->createWithItems(
                $dto->tableId(),
                $dto->waiterId(),
                OrderStatus::WAITING->value,
                $dto->notes(),
                $resolved['subtotalCents'],
                $resolved['items']
            );

            $this->tables->updateStatus($table, TableStatus::OCCUPIED->value);

            return $order;
        });

        return $created;
    }

    public function update(int $id, UpdateOrderDto $dto): Order
    {
        $order = $this->findOrFail($id);

        if (!in_array($order->status, OrderStatus::activeValues(), true)) {
            throw new OrderNotEditableException($id, $order->status);
        }

        $previousTableId = (int) $order->table_id;
        $targetTable = $this->requireAvailableTable($dto->tableId(), $id, $previousTableId);
        $this->requireActiveWaiter($dto->waiterId());
        $resolved = $this->resolveItems($dto->items());

        /** @var Order $updated */
        $updated = DB::transaction(function () use ($order, $dto, $previousTableId, $targetTable, $resolved): Order {
            $saved = $this->orders->replaceWithItems(
                $order,
                $dto->tableId(),
                $dto->waiterId(),
                $dto->notes(),
                $resolved['subtotalCents'],
                $resolved['items']
            );

            if ($previousTableId !== $dto->tableId()) {
                $previousTable = $this->tables->findById($previousTableId);
                if ($previousTable !== null) {
                    $this->tables->updateStatus($previousTable, TableStatus::AVAILABLE->value);
                }
                $this->tables->updateStatus($targetTable, TableStatus::OCCUPIED->value);
            }

            return $saved;
        });

        return $updated;
    }

    public function updateStatus(int $id, UpdateOrderStatusDto $dto): Order
    {
        $order = $this->findOrFail($id);
        $targetStatus = $dto->status();

        if ($order->status === $targetStatus) {
            return $order;
        }

        $allowedTargets = match ($order->status) {
            OrderStatus::WAITING->value => [OrderStatus::SERVED->value, OrderStatus::CANCELLED->value],
            OrderStatus::SERVED->value => [OrderStatus::WAITING->value, OrderStatus::CANCELLED->value],
            default => [],
        };

        if (!in_array($targetStatus, $allowedTargets, true)) {
            throw new InvalidOrderStatusTransitionException($order->status, $targetStatus);
        }

        /** @var Order $updated */
        $updated = DB::transaction(function () use ($order, $targetStatus): Order {
            $saved = $this->orders->updateStatus($order, $targetStatus);

            if ($targetStatus === OrderStatus::CANCELLED->value) {
                $table = $this->tables->findById((int) $order->table_id);
                if ($table !== null) {
                    $this->tables->updateStatus($table, TableStatus::AVAILABLE->value);
                }
            }

            return $saved;
        });

        return $updated;
    }

    public function delete(int $id): void
    {
        $order = $this->findOrFail($id);

        if ($order->status === OrderStatus::PAID->value) {
            throw new OrderNotEditableException($id, $order->status);
        }

        DB::transaction(function () use ($order): void {
            $wasActive = in_array($order->status, OrderStatus::activeValues(), true);
            $tableId = (int) $order->table_id;

            $this->orders->delete($order);

            if ($wasActive) {
                $table = $this->tables->findById($tableId);
                if ($table !== null) {
                    $this->tables->updateStatus($table, TableStatus::AVAILABLE->value);
                }
            }
        });
    }

    private function requireAvailableTable(
        int $tableId,
        ?int $ignoreOrderId = null,
        ?int $currentOrderTableId = null
    ): Table {
        $table = $this->tables->findById($tableId);

        if ($table === null) {
            throw new TableNotFoundException($tableId);
        }

        $isSameTable = $currentOrderTableId !== null && $currentOrderTableId === $tableId;

        if (!$isSameTable && $table->status === TableStatus::INACTIVE->value) {
            throw new TableAlreadyOccupiedException((int) $table->number);
        }

        if ($this->orders->hasActiveOrderForTable($tableId, $ignoreOrderId)) {
            throw new TableAlreadyOccupiedException((int) $table->number);
        }

        return $table;
    }

    private function requireActiveWaiter(int $waiterId): void
    {
        $waiter = $this->waiters->findById($waiterId);

        if ($waiter === null) {
            throw new WaiterNotFoundException($waiterId);
        }

        if (!$waiter->is_active) {
            throw new InactiveWaiterException($waiterId);
        }
    }

    /**
     * @param  array<int, OrderItemInputDto>  $inputItems
     * @return array{subtotalCents: int, items: array<int, ResolvedOrderItemDto>}
     */
    private function resolveItems(array $inputItems): array
    {
        /** @var array<int, int> $quantitiesByProduct */
        $quantitiesByProduct = [];
        foreach ($inputItems as $item) {
            $pid = $item->productId();
            $quantitiesByProduct[$pid] = ($quantitiesByProduct[$pid] ?? 0) + $item->quantity();
        }

        $resolvedItems = [];
        $subtotalCents = 0;

        foreach ($quantitiesByProduct as $productId => $quantity) {
            $product = $this->products->findById($productId);
            if ($product === null) {
                throw new ProductNotFoundException($productId);
            }

            $unitPriceCents = (int) $product->price_cents;
            $lineTotalCents = $unitPriceCents * $quantity;
            $subtotalCents += $lineTotalCents;

            $resolvedItems[] = new ResolvedOrderItemDto(
                $productId,
                (string) $product->name,
                $unitPriceCents,
                $quantity,
                $lineTotalCents
            );
        }

        return [
            'subtotalCents' => $subtotalCents,
            'items' => $resolvedItems,
        ];
    }
}
