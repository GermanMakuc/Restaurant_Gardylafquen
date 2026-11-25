<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('order_id')->unique('sales_order_id_unique');
            $table->unsignedTinyInteger('tip_percent')->default(10);
            $table->unsignedBigInteger('subtotal_cents');
            $table->unsignedBigInteger('tip_cents');
            $table->unsignedBigInteger('total_cents');
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('restaurant_orders')->onDelete('restrict');
            $table->index('paid_at', 'sales_paid_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
}
