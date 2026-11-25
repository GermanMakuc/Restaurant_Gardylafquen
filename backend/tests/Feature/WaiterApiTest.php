<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WaiterApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsRole();
    }

    public function test_index_returns_paginated_waiters_with_filters(): void
    {
        factory(Waiter::class)->create(['name' => 'Camila Rojas', 'is_active' => true]);
        factory(Waiter::class)->create(['name' => 'Camila Vega', 'is_active' => false]);
        factory(Waiter::class)->create(['name' => 'Mateo Silva', 'is_active' => true]);

        $response = $this->getJson('/api/v1/waiters?filter[name]=Camila&filter[is_active]=true&sort=name');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['*' => ['id', 'name', 'email', 'is_active', 'created_at', 'updated_at']],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJson([
                'meta' => ['total' => 1],
                'data' => [
                    ['name' => 'Camila Rojas', 'is_active' => true],
                ],
            ]);
    }

    public function test_store_creates_waiter_and_rejects_duplicate_email(): void
    {
        $response = $this->postJson('/api/v1/waiters', [
            'name' => 'Valentina Díaz',
            'email' => 'Valentina@Example.Test',
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJson([
                'data' => [
                    'name' => 'Valentina Díaz',
                    'email' => 'valentina@example.test',
                    'is_active' => true,
                ],
            ]);

        $this->postJson('/api/v1/waiters', [
            'name' => 'Otra Valentina',
            'email' => 'valentina@example.test',
        ])
            ->assertStatus(409)
            ->assertJson(['code' => 'duplicate_waiter_email']);
    }

    public function test_show_update_and_soft_delete_waiter(): void
    {
        /** @var Waiter $waiter */
        $waiter = factory(Waiter::class)->create(['name' => 'Lucas', 'email' => 'lucas@example.test']);

        $this->getJson('/api/v1/waiters/' . $waiter->id)
            ->assertStatus(200)
            ->assertJson(['data' => ['name' => 'Lucas']]);

        $this->putJson('/api/v1/waiters/' . $waiter->id, [
            'name' => 'Lucas Morales',
            'email' => 'lucas.m@example.test',
            'is_active' => false,
        ])
            ->assertStatus(200)
            ->assertJson(['data' => ['name' => 'Lucas Morales', 'is_active' => false]]);

        $this->deleteJson('/api/v1/waiters/' . $waiter->id)
            ->assertStatus(204);

        $this->assertSoftDeleted('waiters', ['id' => $waiter->id]);

        $this->getJson('/api/v1/waiters/' . $waiter->id)
            ->assertStatus(404)
            ->assertJson(['code' => 'waiter_not_found']);
    }
}
