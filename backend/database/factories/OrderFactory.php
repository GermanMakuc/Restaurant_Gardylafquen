<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Table;
use App\Models\Waiter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'table_id' => Table::factory(),
            'waiter_id' => Waiter::factory(),
            'status' => OrderStatus::WAITING->value,
            'notes' => $this->faker->optional()->sentence(5),
            'subtotal_cents' => 0,
        ];
    }
}
