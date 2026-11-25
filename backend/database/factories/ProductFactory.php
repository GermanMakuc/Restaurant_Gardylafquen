<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $priceCents = $this->faker->numberBetween(250, 25000) * 100;

        return [
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'description' => $this->faker->sentence(8),
            'category_id' => Category::factory(),
            'price' => intdiv($priceCents, 100),
            'price_cents' => $priceCents,
            'path' => '',
            'image_path' => null,
        ];
    }
}
