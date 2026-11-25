<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;

final readonly class EloquentAuthRepository implements AuthRepositoryInterface
{
    public function __construct(private User $model)
    {
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->model->newQuery()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
            ->first();

        return $user;
    }

    public function issueToken(User $user): string
    {
        return $user->createToken('api')->plainTextToken;
    }

    public function revokeToken(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
