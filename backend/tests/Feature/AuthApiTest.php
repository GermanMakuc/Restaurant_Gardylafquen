<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_routes_require_authentication(): void
    {
        $this->get('/api/v1/categories')
            ->assertStatus(401)
            ->assertJson(['code' => 'unauthenticated']);

        $this->getJson('/api/v1/categories')
            ->assertStatus(401)
            ->assertJson(['code' => 'unauthenticated']);

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertJson(['code' => 'unauthenticated']);
    }

    public function test_login_returns_bearer_token_and_stores_hashed_token_and_logout_revokes_it(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create([
            'email' => 'admin@gardylafquen.test',
            'password' => Hash::make('Admin123!'),
            'role' => UserRole::ADMIN,
        ]);

        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@gardylafquen.test',
            'password' => 'Admin123!',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ])
            ->assertJson([
                'data' => [
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $user->id,
                        'role' => UserRole::ADMIN,
                    ],
                ],
            ]);

        $plainToken = (string) $loginRes->json('data.token');
        $this->assertNotSame('', $plainToken);

        /** @var User $fresh */
        $fresh = $user->fresh();
        $this->assertSame(hash('sha256', $plainToken), $fresh->api_token);

        $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJson(['data' => ['id' => $user->id, 'role' => UserRole::ADMIN]]);

        $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(204);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'api_token' => null,
        ]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        factory(User::class)->create([
            'email' => 'admin@gardylafquen.test',
            'password' => Hash::make('Admin123!'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@gardylafquen.test',
            'password' => 'WrongPassword',
        ])
            ->assertStatus(401)
            ->assertJson(['code' => 'invalid_credentials']);
    }

    public function test_role_policies_enforce_permissions(): void
    {
        /** @var Category $category */
        $category = factory(Category::class)->create(['name' => 'Bebidas']);

        $this->actingAsRole(UserRole::WAITER);

        $this->getJson('/api/v1/categories')->assertStatus(200);
        $this->postJson('/api/v1/categories', ['name' => 'No permitido'])
            ->assertStatus(403)
            ->assertJson(['code' => 'forbidden']);
        $this->deleteJson('/api/v1/categories/' . $category->id)
            ->assertStatus(403)
            ->assertJson(['code' => 'forbidden']);
        $this->getJson('/api/v1/sales')
            ->assertStatus(403)
            ->assertJson(['code' => 'forbidden']);

        $this->actingAsRole(UserRole::CASHIER);

        $this->getJson('/api/v1/sales')->assertStatus(200);
        $this->postJson('/api/v1/categories', ['name' => 'No permitido'])
            ->assertStatus(403)
            ->assertJson(['code' => 'forbidden']);
    }
}
