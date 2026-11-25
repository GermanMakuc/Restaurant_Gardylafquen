<?php

declare(strict_types=1);

use App\Enums\TableStatus;
use App\Models\Table;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Table::class, static function (Faker $faker): array {
    return [
        'number' => $faker->unique()->numberBetween(1, 500),
        'label' => 'Salón',
        'capacity' => $faker->numberBetween(2, 8),
        'status' => TableStatus::AVAILABLE,
    ];
});
