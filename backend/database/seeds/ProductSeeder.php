<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['category' => 'Entradas', 'name' => 'Empanada de queso', 'description' => 'Masa artesanal rellena con queso fundido.', 'price_cents' => 350000],
            ['category' => 'Platos principales', 'name' => 'Salmón a la plancha', 'description' => 'Acompañado de papas rústicas y vegetales.', 'price_cents' => 1290000],
            ['category' => 'Bebidas', 'name' => 'Limonada menta jengibre', 'description' => 'Vaso de 450 ml preparada al momento.', 'price_cents' => 320000],
            ['category' => 'Postres', 'name' => 'Kuchen de frambuesa', 'description' => 'Receta tradicional del sur.', 'price_cents' => 450000],
        ];

        foreach ($items as $item) {
            /** @var Category $category */
            $category = Category::query()->firstOrCreate(['name' => $item['category']]);

            Product::query()->firstOrCreate(
                ['name' => $item['name'], 'category_id' => $category->id],
                [
                    'description' => $item['description'],
                    'price_cents' => $item['price_cents'],
                    'price' => intdiv($item['price_cents'], 100),
                    'path' => '',
                    'image_path' => null,
                ]
            );
        }
    }
}
