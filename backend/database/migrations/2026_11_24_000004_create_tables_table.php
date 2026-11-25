<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablesTable extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('number')->unique('tables_number_unique');
            $table->string('label', 100)->nullable();
            $table->unsignedSmallInteger('capacity')->default(4);
            $table->string('status', 20)->default('available');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status', 'tables_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
}
