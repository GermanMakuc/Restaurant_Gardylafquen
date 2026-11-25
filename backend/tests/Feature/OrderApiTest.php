<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Product;
use App\Models\Table;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsRole();
    }

    public function test_store_creates_order_computes_subtotal_and_occupies_table(): void
    {
        /** @var Table $table */
        $table = Table::factory()->create(['number' => 1, 'status' => TableStatus::AVAILABLE->value]);
        /** @var Waiter $waiter */
        $waiter = Waiter::factory()->create(['name' => 'Camila', 'is_active' => true]);
        /** @var Product $p1 */
        $p1 = Product::factory()->create(['name' => 'Empanada', 'price_cents' => 350000]);
        /** @var Product $p2 */
        $p2 = Product::factory()->create(['name' => 'Limonada', 'price_cents' => 320000]);

        $response = $this->postJson('/api/v1/orders', [
            'table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'notes' => 'Sin hielo',
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 2],
                ['product_id' => $p2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201)
            ->assertHeader('Location')
            ->assertJson([
                'data' => [
                    'table_id' => $table->id,
                    'waiter_id' => $waiter->id,
                    'status' => OrderStatus::WAITING->value,
                    'notes' => 'Sin hielo',
                    'subtotal_cents' => 1020000,
                ],
            ]);

        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'status' => TableStatus::OCCUPIED->value,
        ]);

        $this->postJson('/api/v1/orders', [
            'table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 1],
            ],
        ])
            ->assertStatus(409)
            ->assertJson(['code' => 'table_already_occupied']);
    }

    public function test_index_and_show_return_orders_with_relations(): void
    {
        /** @var Table $table */
        $table = Table::factory()->create(['number' => 3, 'status' => TableStatus::AVAILABLE->value]);
        /** @var Waiter $waiter */
        $waiter = Waiter::factory()->create(['name' => 'Mateo', 'is_active' => true]);
        /** @var Product $product */
        $product = Product::factory()->create(['name' => 'Salmón', 'price_cents' => 1290000]);

        $created = $this->postJson('/api/v1/orders', [
            'table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $orderId = (int) $created->json('data.id');

        $this->getJson('/api/v1/orders?filter[status]=waiting')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'table_id', 'table', 'waiter_id', 'waiter', 'status', 'subtotal_cents', 'items'],
                ],
                'links',
                'meta',
            ])
            ->assertJson([
                'meta' => ['total' => 1],
                'data' => [
                    ['id' => $orderId, 'subtotal_cents' => 1290000],
                ],
            ]);

        $this->getJson('/api/v1/orders/' . $orderId)
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $orderId, 'table' => ['number' => 3]]]);
    }

    public function test_update_replaces_items_and_switches_tables(): void
    {
        /** @var Table $t1 */
        $t1 = Table::factory()->create(['number' => 1, 'status' => TableStatus::AVAILABLE->value]);
        /** @var Table $t2 */
        $t2 = Table::factory()->create(['number' => 2, 'status' => TableStatus::AVAILABLE->value]);
        /** @var Waiter $waiter */
        $waiter = Waiter::factory()->create(['is_active' => true]);
        /** @var Product $p1 */
        $p1 = Product::factory()->create(['price_cents' => 100000]);
        /** @var Product $p2 */
        $p2 = Product::factory()->create(['price_cents' => 250000]);

        $created = $this->postJson('/api/v1/orders', [
            'table_id' => $t1->id,
            'waiter_id' => $waiter->id,
            'items' => [['product_id' => $p1->id, 'quantity' => 1]],
        ]);
        $orderId = (int) $created->json('data.id');

        $this->putJson('/api/v1/orders/' . $orderId, [
            'table_id' => $t2->id,
            'waiter_id' => $waiter->id,
            'notes' => 'Cambio de mesa',
            'items' => [['product_id' => $p2->id, 'quantity' => 2]],
        ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'table_id' => $t2->id,
                    'subtotal_cents' => 500000,
                    'notes' => 'Cambio de mesa',
                ],
            ]);

        $this->assertDatabaseHas('tables', ['id' => $t1->id, 'status' => TableStatus::AVAILABLE->value]);
        $this->assertDatabaseHas('tables', ['id' => $t2->id, 'status' => TableStatus::OCCUPIED->value]);
    }

    public function test_update_status_and_destroy_free_table(): void
    {
        /** @var Table $t1 */
        $t1 = Table::factory()->create(['number' => 8, 'status' => TableStatus::AVAILABLE->value]);
        /** @var Waiter $waiter */
        $waiter = Waiter::factory()->create(['is_active' => true]);
        /** @var Product $product */
        $product = Product::factory()->create(['price_cents' => 500000]);

        $created = $this->postJson('/api/v1/orders', [
            'table_id' => $t1->id,
            'waiter_id' => $waiter->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $orderId = (int) $created->json('data.id');

        $this->patchJson('/api/v1/orders/' . $orderId . '/status', ['status' => OrderStatus::SERVED->value])
            ->assertStatus(200)
            ->assertJson(['data' => ['status' => OrderStatus::SERVED->value]]);

        $this->deleteJson('/api/v1/orders/' . $orderId)
            ->assertStatus(204);

        $this->assertSoftDeleted('restaurant_orders', ['id' => $orderId]);
        $this->assertDatabaseHas('tables', ['id' => $t1->id, 'status' => TableStatus::AVAILABLE->value]);
    }
}
