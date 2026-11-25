<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Product::class, static function (Faker $faker): array {
    $priceCents = $faker->numberBetween(250, 25000) * 100;

    return [
        'name' => ucfirst($faker->unique()->words(2, true)),
        'description' => $faker->sentence(8),
        'category_id' => static function (): int {
            /** @var Category $category */
            $category = factory(Category::class)->create();

            return (int) $category->id;
        },
        'price' => intdiv($priceCents, 100),
        'price_cents' => $priceCents,
        'path' => '',
        'image_path' => null,
    ];
});
