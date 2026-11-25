<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TableApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_tables_filtered_by_status(): void
    {
        factory(Table::class)->create(['number' => 2, 'status' => TableStatus::OCCUPIED]);
        factory(Table::class)->create(['number' => 1, 'status' => TableStatus::AVAILABLE, 'label' => 'Ventana']);

        $response = $this->getJson('/api/v1/tables?filter[status]=available&sort=number');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'number', 'label', 'capacity', 'status', 'created_at', 'updated_at']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJson([
                'meta' => ['total' => 1],
                'data' => [
                    ['number' => 1, 'status' => 'available', 'label' => 'Ventana'],
                ],
            ]);
    }

    public function test_store_creates_table_and_rejects_duplicate_number(): void
    {
        $response = $this->postJson('/api/v1/tables', [
            'number' => 12,
            'capacity' => 6,
            'status' => TableStatus::AVAILABLE,
            'label' => 'Terraza',
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJson([
                'data' => [
                    'number' => 12,
                    'capacity' => 6,
                    'status' => 'available',
                    'label' => 'Terraza',
                ],
            ]);

        $this->postJson('/api/v1/tables', [
            'number' => 12,
            'capacity' => 4,
        ])
            ->assertStatus(409)
            ->assertJson(['code' => 'duplicate_table_number']);
    }

    public function test_store_rejects_invalid_status_with_422(): void
    {
        $this->postJson('/api/v1/tables', [
            'number' => 15,
            'status' => 'broken',
        ])
            ->assertStatus(422)
            ->assertJson(['code' => 'validation_error'])
            ->assertJsonStructure(['errors' => ['status']]);
    }

    public function test_show_update_and_soft_delete_table(): void
    {
        /** @var Table $table */
        $table = factory(Table::class)->create(['number' => 5, 'capacity' => 4]);

        $this->getJson('/api/v1/tables/' . $table->id)
            ->assertStatus(200)
            ->assertJson(['data' => ['number' => 5]]);

        $this->putJson('/api/v1/tables/' . $table->id, [
            'number' => 5,
            'capacity' => 8,
            'status' => TableStatus::RESERVED,
            'label' => 'VIP',
        ])
            ->assertStatus(200)
            ->assertJson(['data' => ['capacity' => 8, 'status' => 'reserved', 'label' => 'VIP']]);

        $this->deleteJson('/api/v1/tables/' . $table->id)
            ->assertStatus(204);

        $this->assertSoftDeleted('tables', ['id' => $table->id]);

        $this->getJson('/api/v1/tables/' . $table->id)
            ->assertStatus(404)
            ->assertJson(['code' => 'table_not_found']);
    }
}
