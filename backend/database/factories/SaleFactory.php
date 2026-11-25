<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
final class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotalCents = $this->faker->numberBetween(500, 30000) * 100;
        $tipPercent = 10;
        $tipCents = (int) round($subtotalCents * $tipPercent / 100);

        return [
            'order_id' => Order::factory()->state([
                'status' => OrderStatus::PAID->value,
                'subtotal_cents' => $subtotalCents,
            ]),
            'tip_percent' => $tipPercent,
            'subtotal_cents' => $subtotalCents,
            'tip_cents' => $tipCents,
            'total_cents' => $subtotalCents + $tipCents,
            'paid_at' => now(),
        ];
    }
}
