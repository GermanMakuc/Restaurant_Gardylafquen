<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function issueToken(User $user): string;

    public function revokeToken(User $user): void;
}
