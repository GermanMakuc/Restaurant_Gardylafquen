<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Sale;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Sale::class, static function (Faker $faker): array {
    $subtotalCents = $faker->numberBetween(500, 30000) * 100;
    $tipPercent = 10;
    $tipCents = (int) round($subtotalCents * $tipPercent / 100);

    return [
        'order_id' => static function () use ($subtotalCents): int {
            /** @var Order $order */
            $order = factory(Order::class)->create([
                'status' => OrderStatus::PAID,
                'subtotal_cents' => $subtotalCents,
            ]);

            return (int) $order->id;
        },
        'tip_percent' => $tipPercent,
        'subtotal_cents' => $subtotalCents,
        'tip_cents' => $tipCents,
        'total_cents' => $subtotalCents + $tipCents,
        'paid_at' => now(),
    ];
});
