<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Sale\CreateSaleDto;
use App\DTOs\Sale\ListSalesDto;
use App\DTOs\Sale\SalesSummaryDto;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\OrderNotEditableException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\SaleNotFoundException;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Table;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Services\SaleService;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class SaleServiceTest extends TestCase
{
    public function test_summary_delegates_to_repository(): void
    {
        $dto = new ListSalesDto('2026-11-01', '2026-11-30');
        $summary = new SalesSummaryDto(2, 1000000, 100000, 1100000);

        /** @var SaleRepositoryInterface&MockObject $sales */
        $sales = $this->createMock(SaleRepositoryInterface::class);
        $sales->expects($this->once())->method('summarize')->with($dto)->willReturn($summary);

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);

        $service = new SaleService($sales, $orders, $tables);

        $this->assertSame($summary, $service->summary($dto));
    }

    public function test_find_or_fail_throws_when_missing(): void
    {
        /** @var SaleRepositoryInterface&MockObject $sales */
        $sales = $this->createMock(SaleRepositoryInterface::class);
        $sales->expects($this->once())->method('findById')->with(88)->willReturn(null);

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);

        $service = new SaleService($sales, $orders, $tables);

        $this->expectException(SaleNotFoundException::class);
        $service->findOrFail(88);
    }

    public function test_create_computes_tip_and_total_marks_order_paid_and_frees_table(): void
    {
        $order = new Order([
            'table_id' => 3,
            'waiter_id' => 1,
            'status' => OrderStatus::SERVED->value,
            'subtotal_cents' => 1000000,
        ]);
        $order->id = 7;

        $table = new Table(['number' => 3, 'status' => TableStatus::OCCUPIED->value]);
        $table->id = 3;

        $sale = new Sale([
            'order_id' => 7,
            'tip_percent' => 10,
            'subtotal_cents' => 1000000,
            'tip_cents' => 100000,
            'total_cents' => 1100000,
        ]);
        $sale->id = 12;

        /** @var SaleRepositoryInterface&MockObject $sales */
        $sales = $this->createMock(SaleRepositoryInterface::class);
        $sales->expects($this->once())->method('existsForOrder')->with(7)->willReturn(false);
        $sales->expects($this->once())
            ->method('create')
            ->with(7, 10, 1000000, 100000, 1100000, $this->isType('string'))
            ->willReturn($sale);

        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findById')->with(7)->willReturn($order);
        $orders->expects($this->once())->method('updateStatus')->with($order, OrderStatus::PAID->value)->willReturn($order);

        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);
        $tables->expects($this->once())->method('findById')->with(3)->willReturn($table);
        $tables->expects($this->once())->method('updateStatus')->with($table, TableStatus::AVAILABLE->value)->willReturn($table);

        DB::shouldReceive('transaction')->once()->andReturnUsing(static function (callable $callback) {
            return $callback();
        });

        $service = new SaleService($sales, $orders, $tables);
        $result = $service->create(new CreateSaleDto(7, 10));

        $this->assertSame($sale, $result);
    }

    public function test_create_throws_when_order_does_not_exist(): void
    {
        /** @var SaleRepositoryInterface&MockObject $sales */
        $sales = $this->createMock(SaleRepositoryInterface::class);
        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findById')->with(99)->willReturn(null);
        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);

        $service = new SaleService($sales, $orders, $tables);

        $this->expectException(OrderNotFoundException::class);
        $service->create(new CreateSaleDto(99, 10));
    }

    public function test_create_throws_when_order_is_already_paid(): void
    {
        $order = new Order(['table_id' => 3, 'status' => OrderStatus::PAID->value, 'subtotal_cents' => 500000]);
        $order->id = 7;

        /** @var SaleRepositoryInterface&MockObject $sales */
        $sales = $this->createMock(SaleRepositoryInterface::class);
        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findById')->with(7)->willReturn($order);
        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);

        $service = new SaleService($sales, $orders, $tables);

        $this->expectException(OrderAlreadyPaidException::class);
        $service->create(new CreateSaleDto(7, 10));
    }

    public function test_create_throws_when_order_is_cancelled(): void
    {
        $order = new Order(['table_id' => 3, 'status' => OrderStatus::CANCELLED->value, 'subtotal_cents' => 500000]);
        $order->id = 7;

        /** @var SaleRepositoryInterface&MockObject $sales */
        $sales = $this->createMock(SaleRepositoryInterface::class);
        $sales->expects($this->once())->method('existsForOrder')->with(7)->willReturn(false);
        /** @var OrderRepositoryInterface&MockObject $orders */
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findById')->with(7)->willReturn($order);
        /** @var TableRepositoryInterface&MockObject $tables */
        $tables = $this->createMock(TableRepositoryInterface::class);

        $service = new SaleService($sales, $orders, $tables);

        $this->expectException(OrderNotEditableException::class);
        $service->create(new CreateSaleDto(7, 10));
    }
}
