<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Table>
 */
final class TableFactory extends Factory
{
    protected $model = Table::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->numberBetween(1, 500),
            'label' => 'Salón',
            'capacity' => $this->faker->numberBetween(2, 8),
            'status' => TableStatus::AVAILABLE->value,
        ];
    }
}
