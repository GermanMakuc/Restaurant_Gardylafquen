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
    private AuthRepositoryInterface $auth;

    private Hasher $hasher;

    public function __construct(AuthRepositoryInterface $auth, Hasher $hasher)
    {
        $this->auth = $auth;
        $this->hasher = $hasher;
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
