<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Waiter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Waiter>
 */
final class WaiterFactory extends Factory
{
    protected $model = Waiter::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'is_active' => true,
        ];
    }
}
