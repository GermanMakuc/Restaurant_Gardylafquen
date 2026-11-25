<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Table;
use App\Models\Waiter;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Order::class, static function (Faker $faker): array {
    return [
        'table_id' => static function (): int {
            /** @var Table $table */
            $table = factory(Table::class)->create();

            return (int) $table->id;
        },
        'waiter_id' => static function (): int {
            /** @var Waiter $waiter */
            $waiter = factory(Waiter::class)->create();

            return (int) $waiter->id;
        },
        'status' => OrderStatus::WAITING,
        'notes' => $faker->optional()->sentence(5),
        'subtotal_cents' => 0,
    ];
});
