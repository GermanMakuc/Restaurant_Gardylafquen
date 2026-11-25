<?php

declare(strict_types=1);

use App\Models\Category;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Category::class, static function (Faker $faker): array {
    return [
        'name' => ucfirst($faker->unique()->words(2, true)),
    ];
});
