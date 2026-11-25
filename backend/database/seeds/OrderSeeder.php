<?php

declare(strict_types=1);

use App\DTOs\Order\CreateOrderDto;
use App\DTOs\Order\OrderItemInputDto;
use App\Enums\TableStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\Waiter;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private OrderService $orders;

    public function __construct(OrderService $orders)
    {
        $this->orders = $orders;
    }

    public function run(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        /** @var Table|null $table */
        $table = Table::query()->where('status', TableStatus::AVAILABLE)->orderBy('number')->first();
        /** @var Waiter|null $waiter */
        $waiter = Waiter::query()->where('is_active', true)->first();
        $products = Product::query()->limit(2)->get();

        if ($table === null || $waiter === null || $products->isEmpty()) {
            return;
        }

        $items = [];
        foreach ($products as $product) {
            /** @var Product $product */
            $items[] = new OrderItemInputDto((int) $product->id, 2);
        }

        $this->orders->create(new CreateOrderDto(
            (int) $table->id,
            (int) $waiter->id,
            'Sin cebolla en la entrada.',
            $items
        ));
    }
}
