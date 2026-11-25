<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Auth\AuthTokenDto;
use App\DTOs\Auth\LoginDto;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Contracts\Hashing\Hasher;

final class AuthService
{
    public function __construct(
        private readonly AuthRepositoryInterface $auth,
        private readonly Hasher $hasher,
    ) {
    }

    public function login(LoginDto $dto): AuthTokenDto
    {
        $user = $this->auth->findByEmail($dto->email());

        if ($user === null || !$this->hasher->check($dto->password(), (string) $user->password)) {
            throw new InvalidCredentialsException();
        }

        $token = $this->auth->issueToken($user);

        return new AuthTokenDto($token, $user);
    }

    public function logout(User $user): void
    {
        $this->auth->revokeToken($user);
    }
}
