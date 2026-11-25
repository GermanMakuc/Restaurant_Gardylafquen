<?php

declare(strict_types=1);

use App\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        for ($number = 1; $number <= 10; $number++) {
            Table::query()->firstOrCreate(
                ['number' => $number],
                [
                    'label' => $number <= 6 ? 'Salón' : 'Terraza',
                    'capacity' => $number % 2 === 0 ? 4 : 2,
                    'status' => TableStatus::AVAILABLE,
                ]
            );
        }
    }
}
