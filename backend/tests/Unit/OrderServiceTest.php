<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Order\CreateOrderDto;
use App\DTOs\Order\OrderItemInputDto;
use App\DTOs\Order\UpdateOrderStatusDto;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Exceptions\InactiveWaiterException;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Exceptions\OrderNotEditableException;
use App\Exceptions\TableAlreadyOccupiedException;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\Waiter;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\Contracts\WaiterRepositoryInterface;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class OrderServiceTest extends TestCase
{
    public function test_create_aggregates_items_calculates_subtotal_and_marks_table_occupied(): void
    {
        $table = new Table(['number' => 1, 'status' => TableStatus::AVAILABLE->value]);
        $table->id = 1;
        $waiter = new Waiter(['name' => 'Camila', 'is_active' => true]);
        $waiter->id = 2;
        $product = new Product(['name' => 'Empanada', 'price_cents' => 350000]);
        $product->id = 10;

        $createdOrder = new Order(['table_id' => 1, 'waiter_id' => 2, 'status' => OrderStatus::WAITING->value, 'subtotal_cents' => 1050000]);
        $createdOrder->id = 5;

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('hasActiveOrderForTable')->with(1, null)->willReturn(false);
        $orders->expects($this->once())
            ->method('createWithItems')
            ->with(
                1,
                2,
                OrderStatus::WAITING->value,
                'Mesa junto a ventana',
                1050000,
                $this->callback(static function (array $items): bool {
                    return count($items) === 1
                        && $items[0]->quantity === 3
                        && $items[0]->lineTotalCents === 1050000;
                })
            )
            ->willReturn($createdOrder);

        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);
        $tables->expects($this->once())->method('findById')->with(1)->willReturn($table);
        $tables->expects($this->once())->method('updateStatus')->with($table, TableStatus::OCCUPIED->value)->willReturn($table);

        /** @var WaiterRepositoryInterface&MockObject $waiters */
        $waiters = $this->createMock(WaiterRepositoryInterface::class);
        $waiters->expects($this->once())->method('findById')->with(2)->willReturn($waiter);

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);
        $products->expects($this->once())->method('findById')->with(10)->willReturn($product);

        DB::shouldReceive('transaction')->once()->andReturnUsing(static function (callable $callback) {
            return $callback();
        });

        $service = new OrderService($orders, $tables, $waiters, $products);
        $result = $service->create(new CreateOrderDto(1, 2, 'Mesa junto a ventana', [
            new OrderItemInputDto(10, 1),
            new OrderItemInputDto(10, 2),
        ]));

        $this->assertSame($createdOrder, $result);
    }

    public function test_create_throws_when_table_already_has_active_order(): void
    {
        $table = new Table(['number' => 4, 'status' => TableStatus::OCCUPIED->value]);
        $table->id = 4;

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('hasActiveOrderForTable')->with(4, null)->willReturn(true);

        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);
        $tables->expects($this->once())->method('findById')->with(4)->willReturn($table);

        /** @var WaiterRepositoryInterface&MockObject $waiters */
        $waiters = $this->createMock(WaiterRepositoryInterface::class);
        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);

        $service = new OrderService($orders, $tables, $waiters, $products);

        $this->expectException(TableAlreadyOccupiedException::class);
        $service->create(new CreateOrderDto(4, 1, null, [new OrderItemInputDto(1, 1)]));
    }

    public function test_create_throws_when_waiter_is_inactive(): void
    {
        $table = new Table(['number' => 2, 'status' => TableStatus::AVAILABLE->value]);
        $table->id = 2;
        $waiter = new Waiter(['name' => 'Inactivo', 'is_active' => false]);
        $waiter->id = 3;

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('hasActiveOrderForTable')->with(2, null)->willReturn(false);

        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);
        $tables->expects($this->once())->method('findById')->with(2)->willReturn($table);

        /** @var WaiterRepositoryInterface&MockObject $waiters */
        $waiters = $this->createMock(WaiterRepositoryInterface::class);
        $waiters->expects($this->once())->method('findById')->with(3)->willReturn($waiter);

        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);

        $service = new OrderService($orders, $tables, $waiters, $products);

        $this->expectException(InactiveWaiterException::class);
        $service->create(new CreateOrderDto(2, 3, null, [new OrderItemInputDto(1, 1)]));
    }

    public function test_update_status_to_cancelled_frees_table(): void
    {
        $order = new Order(['table_id' => 6, 'waiter_id' => 1, 'status' => OrderStatus::WAITING->value]);
        $order->id = 9;
        $cancelled = new Order(['table_id' => 6, 'waiter_id' => 1, 'status' => OrderStatus::CANCELLED->value]);
        $cancelled->id = 9;
        $table = new Table(['number' => 6, 'status' => TableStatus::OCCUPIED->value]);
        $table->id = 6;

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findById')->with(9)->willReturn($order);
        $orders->expects($this->once())->method('updateStatus')->with($order, OrderStatus::CANCELLED->value)->willReturn($cancelled);

        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);
        $tables->expects($this->once())->method('findById')->with(6)->willReturn($table);
        $tables->expects($this->once())->method('updateStatus')->with($table, TableStatus::AVAILABLE->value)->willReturn($table);

        /** @var WaiterRepositoryInterface&MockObject $waiters */
        $waiters = $this->createMock(WaiterRepositoryInterface::class);
        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);

        DB::shouldReceive('transaction')->once()->andReturnUsing(static function (callable $callback) {
            return $callback();
        });

        $service = new OrderService($orders, $tables, $waiters, $products);
        $result = $service->updateStatus(9, new UpdateOrderStatusDto(OrderStatus::CANCELLED->value));

        $this->assertSame($cancelled, $result);
    }

    public function test_update_status_rejects_transition_from_paid(): void
    {
        $order = new Order(['table_id' => 6, 'waiter_id' => 1, 'status' => OrderStatus::PAID->value]);
        $order->id = 9;

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findById')->with(9)->willReturn($order);

        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);
        /** @var WaiterRepositoryInterface&MockObject $waiters */
        $waiters = $this->createMock(WaiterRepositoryInterface::class);
        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);

        $service = new OrderService($orders, $tables, $waiters, $products);

        $this->expectException(InvalidOrderStatusTransitionException::class);
        $service->updateStatus(9, new UpdateOrderStatusDto(OrderStatus::WAITING->value));
    }

    public function test_delete_rejects_paid_order(): void
    {
        $order = new Order(['table_id' => 6, 'waiter_id' => 1, 'status' => OrderStatus::PAID->value]);
        $order->id = 9;

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findById')->with(9)->willReturn($order);

        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);
        /** @var WaiterRepositoryInterface&MockObject $waiters */
        $waiters = $this->createMock(WaiterRepositoryInterface::class);
        /** @var ProductRepositoryInterface&MockObject $products */
        $products = $this->createMock(ProductRepositoryInterface::class);

        $service = new OrderService($orders, $tables, $waiters, $products);

        $this->expectException(OrderNotEditableException::class);
        $service->delete(9);
    }
}
