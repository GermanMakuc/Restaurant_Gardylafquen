<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        if (Schema::hasColumn('users', 'api_token')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropUnique('users_api_token_unique');
                $table->dropColumn('api_token');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');

        if (! Schema::hasColumn('users', 'api_token')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('api_token', 80)->nullable()->unique('users_api_token_unique')->after('role');
            });
        }
    }
};
