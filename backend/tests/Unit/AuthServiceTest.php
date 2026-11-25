<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Auth\LoginDto;
use App\Enums\UserRole;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Contracts\Hashing\Hasher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    public function test_login_issues_token_when_credentials_are_valid(): void
    {
        $user = new User([
            'name' => 'Admin',
            'email' => 'admin@gardylafquen.test',
            'password' => 'hashed-password',
            'role' => UserRole::ADMIN,
        ]);
        $user->id = 1;

        /** @var AuthRepositoryInterface&MockObject $auth */
        $auth = $this->createMock(AuthRepositoryInterface::class);
        $auth->expects($this->once())->method('findByEmail')->with('admin@gardylafquen.test')->willReturn($user);
        $auth->expects($this->once())->method('issueToken')->with($user)->willReturn('plain-token-123');

        /** @var Hasher&MockObject $hasher */
        $hasher = $this->createMock(Hasher::class);
        $hasher->expects($this->once())->method('check')->with('Secret123!', 'hashed-password')->willReturn(true);

        $service = new AuthService($auth, $hasher);
        $result = $service->login(LoginDto::fromArray([
            'email' => 'Admin@Gardylafquen.test',
            'password' => 'Secret123!',
        ]));

        $this->assertSame('plain-token-123', $result->token);
        $this->assertSame($user, $result->user);
    }

    public function test_login_throws_when_user_not_found_or_password_mismatch(): void
    {
        /** @var AuthRepositoryInterface&MockObject $auth */
        $auth = $this->createMock(AuthRepositoryInterface::class);
        $auth->expects($this->once())->method('findByEmail')->with('missing@gardylafquen.test')->willReturn(null);
        $auth->expects($this->never())->method('issueToken');

        /** @var Hasher&Stub $hasher */
        $hasher = $this->createStub(Hasher::class);

        $service = new AuthService($auth, $hasher);

        $this->expectException(InvalidCredentialsException::class);
        $service->login(new LoginDto('missing@gardylafquen.test', 'any'));
    }

    public function test_logout_revokes_token(): void
    {
        $user = new User(['name' => 'Admin']);

        /** @var AuthRepositoryInterface&MockObject $auth */
        $auth = $this->createMock(AuthRepositoryInterface::class);
        $auth->expects($this->once())->method('revokeToken')->with($user);

        /** @var Hasher&Stub $hasher */
        $hasher = $this->createStub(Hasher::class);

        $service = new AuthService($auth, $hasher);
        $service->logout($user);
    }
}
