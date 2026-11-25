<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantOrdersAndOrderItemsTables extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_orders', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('table_id');
            $table->unsignedInteger('waiter_id');
            $table->string('status', 20)->default('waiting');
            $table->string('notes', 500)->nullable();
            $table->unsignedBigInteger('subtotal_cents')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('table_id')->references('id')->on('tables')->onDelete('restrict');
            $table->foreign('waiter_id')->references('id')->on('waiters')->onDelete('restrict');
            $table->index('status', 'restaurant_orders_status_index');
            $table->index('table_id', 'restaurant_orders_table_id_index');
            $table->index('waiter_id', 'restaurant_orders_waiter_id_index');
            $table->index('created_at', 'restaurant_orders_created_at_index');
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->string('product_name', 150);
            $table->unsignedBigInteger('unit_price_cents');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total_cents');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('restaurant_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->index('order_id', 'order_items_order_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('restaurant_orders');
    }
}
