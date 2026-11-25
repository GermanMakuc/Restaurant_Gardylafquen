<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsRole();
    }

    public function test_index_returns_paginated_filtered_and_sorted_categories(): void
    {
        Category::factory()->create(['name' => 'Postres']);
        Category::factory()->create(['name' => 'Bebidas Calientes']);
        Category::factory()->create(['name' => 'Bebidas Frías']);

        $response = $this->getJson('/api/v1/categories?filter[name]=Bebidas&sort=-name&per_page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at'],
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
                    ['name' => 'Bebidas Frías'],
                ],
            ]);
    }

    public function test_store_creates_category_and_returns_201_with_location(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => '  Entradas  ',
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJson([
                'data' => [
                    'name' => 'Entradas',
                ],
            ]);

        $this->assertDatabaseHas('categories', ['name' => 'Entradas']);
    }

    public function test_store_returns_422_on_invalid_payload(): void
    {
        $response = $this->postJson('/api/v1/categories', [
            'name' => 'A',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 'validation_error',
            ])
            ->assertJsonStructure(['message', 'code', 'errors' => ['name']]);
    }

    public function test_store_returns_409_when_category_name_is_duplicated_case_insensitively(): void
    {
        Category::factory()->create(['name' => 'Bebidas']);

        $response = $this->postJson('/api/v1/categories', [
            'name' => 'bebidas',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'code' => 'duplicate_category',
            ]);
    }

    public function test_show_returns_category_or_404(): void
    {
        /** @var Category $category */
        $category = Category::factory()->create(['name' => 'Cafetería']);

        $this->getJson('/api/v1/categories/' . $category->id)
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $category->id,
                    'name' => 'Cafetería',
                ],
            ]);

        $this->getJson('/api/v1/categories/9999')
            ->assertStatus(404)
            ->assertJson(['code' => 'category_not_found']);
    }

    public function test_update_modifies_category_and_rejects_duplicates(): void
    {
        /** @var Category $first */
        $first = Category::factory()->create(['name' => 'Entradas']);
        Category::factory()->create(['name' => 'Postres']);

        $this->putJson('/api/v1/categories/' . $first->id, ['name' => 'Entradas Frías'])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Entradas Frías',
                ],
            ]);

        $this->patchJson('/api/v1/categories/' . $first->id, ['name' => 'Postres'])
            ->assertStatus(409)
            ->assertJson(['code' => 'duplicate_category']);
    }

    public function test_destroy_deletes_category_and_returns_204(): void
    {
        /** @var Category $category */
        $category = Category::factory()->create(['name' => 'Temporal']);

        $this->deleteJson('/api/v1/categories/' . $category->id)
            ->assertStatus(204);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        $this->deleteJson('/api/v1/categories/' . $category->id)
            ->assertStatus(404)
            ->assertJson(['code' => 'category_not_found']);
    }
}
