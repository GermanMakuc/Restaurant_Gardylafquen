<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

final class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::CASHIER, UserRole::WAITER);
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::WAITER);
    }

    public function update(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::WAITER);
    }

    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN, UserRole::WAITER);
    }
}
