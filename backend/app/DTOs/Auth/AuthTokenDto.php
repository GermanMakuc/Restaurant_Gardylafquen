<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Models\User;

final class AuthTokenDto
{
    private string $token;

    private User $user;

    public function __construct(string $token, User $user)
    {
        $this->token = $token;
        $this->user = $user;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function user(): User
    {
        return $this->user;
    }
}
