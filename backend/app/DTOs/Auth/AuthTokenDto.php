<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Models\User;

final readonly class AuthTokenDto
{
    public function __construct(
        public string $token,
        public User $user,
    ) {
    }
}
