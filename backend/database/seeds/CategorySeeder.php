<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Entradas',
            'Platos principales',
            'Bebidas',
            'Postres',
            'Cafetería',
        ];

        foreach ($names as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }
    }
}
