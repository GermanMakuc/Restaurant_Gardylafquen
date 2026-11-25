<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Waiter;
use Illuminate\Database\Seeder;

class WaiterSeeder extends Seeder
{
    public function run(): void
    {
        $waiters = [
            ['name' => 'Camila Rojas', 'email' => 'camila.rojas@example.test', 'is_active' => true],
            ['name' => 'Mateo Silva', 'email' => 'mateo.silva@example.test', 'is_active' => true],
            ['name' => 'Valentina Díaz', 'email' => 'valentina.diaz@example.test', 'is_active' => true],
        ];

        foreach ($waiters as $waiter) {
            Waiter::query()->firstOrCreate(
                ['email' => $waiter['email']],
                ['name' => $waiter['name'], 'is_active' => $waiter['is_active']]
            );
        }
    }
}
