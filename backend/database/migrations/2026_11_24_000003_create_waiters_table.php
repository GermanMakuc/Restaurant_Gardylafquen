<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWaitersTable extends Migration
{
    public function up(): void
    {
        Schema::create('waiters', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name', 150);
            $table->string('email', 255)->unique('waiters_email_unique');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name', 'waiters_name_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiters');
    }
}
