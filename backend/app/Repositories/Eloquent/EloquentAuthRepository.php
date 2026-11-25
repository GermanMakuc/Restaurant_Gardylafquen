<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Str;

final class EloquentAuthRepository implements AuthRepositoryInterface
{
    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
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
        $plainToken = Str::random(60);
        $user->api_token = hash('sha256', $plainToken);
        $user->save();

        return $plainToken;
    }

    public function revokeToken(User $user): void
    {
        $user->api_token = null;
        $user->save();
    }
}
