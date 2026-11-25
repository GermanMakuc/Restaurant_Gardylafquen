<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_products_filtered_by_name_and_category(): void
    {
        /** @var Category $drinks */
        $drinks = factory(Category::class)->create(['name' => 'Bebidas']);
        /** @var Category $desserts */
        $desserts = factory(Category::class)->create(['name' => 'Postres']);

        factory(Product::class)->create([
            'name' => 'Jugo Natural',
            'category_id' => $drinks->id,
            'price_cents' => 350000,
        ]);
        factory(Product::class)->create([
            'name' => 'Jugo de Naranja',
            'category_id' => $drinks->id,
            'price_cents' => 380000,
        ]);
        factory(Product::class)->create([
            'name' => 'Torta',
            'category_id' => $desserts->id,
            'price_cents' => 490000,
        ]);

        $response = $this->getJson('/api/v1/products?filter[category_id]=' . $drinks->id . '&filter[name]=Jugo&sort=-price_cents&per_page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'category_id', 'category', 'price_cents', 'image_url', 'created_at', 'updated_at'],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJson([
                'meta' => [
                    'total' => 2,
                    'per_page' => 1,
                ],
                'data' => [
                    [
                        'name' => 'Jugo de Naranja',
                        'price_cents' => 380000,
                        'category' => [
                            'id' => $drinks->id,
                            'name' => 'Bebidas',
                        ],
                    ],
                ],
            ]);
    }

    public function test_store_creates_product_and_returns_201_with_location(): void
    {
        /** @var Category $category */
        $category = factory(Category::class)->create(['name' => 'Entradas']);

        $response = $this->postJson('/api/v1/products', [
            'name' => '  Empanada de pino  ',
            'category_id' => $category->id,
            'price_cents' => 320000,
            'description' => 'Horneada',
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJson([
                'data' => [
                    'name' => 'Empanada de pino',
                    'category_id' => $category->id,
                    'price_cents' => 320000,
                    'description' => 'Horneada',
                    'image_url' => null,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Empanada de pino',
            'price_cents' => 320000,
        ]);
    }

    public function test_store_returns_422_on_invalid_payload(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'X',
            'category_id' => 99999,
            'price_cents' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJson(['code' => 'validation_error'])
            ->assertJsonStructure(['message', 'code', 'errors' => ['name', 'category_id', 'price_cents']]);
    }

    public function test_show_and_update_product(): void
    {
        /** @var Category $cat1 */
        $cat1 = factory(Category::class)->create(['name' => 'Platos']);
        /** @var Category $cat2 */
        $cat2 = factory(Category::class)->create(['name' => 'Especiales']);
        /** @var Product $product */
        $product = factory(Product::class)->create([
            'name' => 'Merluza',
            'category_id' => $cat1->id,
            'price_cents' => 990000,
        ]);

        $this->getJson('/api/v1/products/' . $product->id)
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $product->id, 'name' => 'Merluza']]);

        $this->getJson('/api/v1/products/99999')
            ->assertStatus(404)
            ->assertJson(['code' => 'product_not_found']);

        $this->putJson('/api/v1/products/' . $product->id, [
            'name' => 'Merluza Austral',
            'category_id' => $cat2->id,
            'price_cents' => 1150000,
            'description' => 'Con ensalada',
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Merluza Austral',
                    'category_id' => $cat2->id,
                    'price_cents' => 1150000,
                ],
            ]);
    }

    public function test_upload_image_stores_file_on_configurable_disk_and_returns_url(): void
    {
        Storage::fake('public');

        /** @var Product $product */
        $product = factory(Product::class)->create();

        $file = UploadedFile::fake()->image('salmon.jpg', 600, 600);

        $response = $this->post('/api/v1/products/' . $product->id . '/image', [
            'image' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'image_url'],
            ]);

        /** @var Product $fresh */
        $fresh = $product->fresh();
        $this->assertNotNull($fresh->image_path);
        $this->assertTrue(Storage::disk('public')->exists((string) $fresh->image_path));
    }

    public function test_destroy_soft_deletes_product_and_hides_it_from_api(): void
    {
        /** @var Product $product */
        $product = factory(Product::class)->create(['name' => 'Temporal']);

        $this->deleteJson('/api/v1/products/' . $product->id)
            ->assertStatus(204);

        $this->assertSoftDeleted('products', ['id' => $product->id]);

        $this->getJson('/api/v1/products/' . $product->id)
            ->assertStatus(404)
            ->assertJson(['code' => 'product_not_found']);
    }
}
