<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateProductsTableForApiV1 extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedBigInteger('price_cents')->default(0)->after('price');
            $table->string('image_path')->nullable()->after('path');
            $table->softDeletes();
            $table->index('name', 'products_name_index');
        });

        DB::table('products')->where('price_cents', 0)->where('price', '>', 0)->update([
            'price_cents' => DB::raw('price * 100'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_name_index');
            $table->dropSoftDeletes();
            $table->dropColumn(['price_cents', 'image_path']);
        });
    }
}
