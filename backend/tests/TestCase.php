<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function actingAsRole(string $role = UserRole::ADMIN): User
    {
        /** @var User $user */
        $user = factory(User::class)->create(['role' => $role]);
        $this->actingAs($user, 'api');

        return $user;
    }
}
