<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Table;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SaleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_closes_order_creates_sale_and_frees_table(): void
    {
        /** @var Table $table */
        $table = factory(Table::class)->create(['number' => 4, 'status' => TableStatus::AVAILABLE]);
        /** @var Waiter $waiter */
        $waiter = factory(Waiter::class)->create(['is_active' => true]);
        /** @var Product $product */
        $product = factory(Product::class)->create(['price_cents' => 1000000]);

        $orderRes = $this->postJson('/api/v1/orders', [
            'table_id' => $table->id,
            'waiter_id' => $waiter->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);
        $orderId = (int) $orderRes->json('data.id');

        $saleRes = $this->postJson('/api/v1/sales', [
            'order_id' => $orderId,
            'tip_percent' => 10,
        ]);

        $saleRes->assertStatus(201)
            ->assertHeader('Location')
            ->assertJson([
                'data' => [
                    'order_id' => $orderId,
                    'tip_percent' => 10,
                    'subtotal_cents' => 2000000,
                    'tip_cents' => 200000,
                    'total_cents' => 2200000,
                ],
            ]);

        $this->assertDatabaseHas('restaurant_orders', [
            'id' => $orderId,
            'status' => OrderStatus::PAID,
        ]);
        $this->assertDatabaseHas('tables', [
            'id' => $table->id,
            'status' => TableStatus::AVAILABLE,
        ]);

        $this->postJson('/api/v1/sales', ['order_id' => $orderId])
            ->assertStatus(409)
            ->assertJson(['code' => 'order_already_paid']);
    }

    public function test_index_summary_and_show_filter_by_date_range(): void
    {
        factory(Sale::class)->create([
            'subtotal_cents' => 1000000,
            'tip_percent' => 10,
            'tip_cents' => 100000,
            'total_cents' => 1100000,
            'paid_at' => '2026-11-10 15:00:00',
        ]);
        /** @var Sale $recent */
        $recent = factory(Sale::class)->create([
            'subtotal_cents' => 2000000,
            'tip_percent' => 10,
            'tip_cents' => 200000,
            'total_cents' => 2200000,
            'paid_at' => '2026-11-24 20:00:00',
        ]);

        $this->getJson('/api/v1/sales?filter[from]=2026-11-20&filter[to]=2026-11-25')
            ->assertStatus(200)
            ->assertJson([
                'meta' => ['total' => 1],
                'data' => [
                    ['id' => $recent->id, 'total_cents' => 2200000],
                ],
            ]);

        $this->getJson('/api/v1/sales/summary?filter[from]=2026-11-20&filter[to]=2026-11-25')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'count' => 1,
                    'subtotal_cents' => 2000000,
                    'tip_cents' => 200000,
                    'total_cents' => 2200000,
                ],
            ]);

        $this->getJson('/api/v1/sales/' . $recent->id)
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $recent->id]]);

        $this->getJson('/api/v1/sales/99999')
            ->assertStatus(404)
            ->assertJson(['code' => 'sale_not_found']);
    }
}
