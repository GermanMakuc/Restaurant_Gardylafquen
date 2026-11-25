<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function actingAsRole(UserRole $role = UserRole::ADMIN): User
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => $role->value]);
        Sanctum::actingAs($user);

        return $user;
    }
}
