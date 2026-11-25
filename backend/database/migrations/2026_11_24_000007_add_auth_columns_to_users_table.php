<?php

declare(strict_types=1);

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuthColumnsToUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->default('')->after('email');
            $table->string('role', 30)->default(UserRole::WAITER)->after('password');
            $table->string('api_token', 80)->nullable()->unique('users_api_token_unique')->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_api_token_unique');
            $table->dropColumn(['password', 'role', 'api_token']);
        });
    }
}
