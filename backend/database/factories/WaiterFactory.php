<?php

declare(strict_types=1);

use App\Models\Waiter;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Waiter::class, static function (Faker $faker): array {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'is_active' => true,
    ];
});
