<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Provisional Laravel 5.7 token guard supporting SHA-256 hashed api_token storage.
 * Replaced by Laravel Sanctum in Phase 5 per AGENTS.md §13.3.
 */
final class HashedTokenGuard extends TokenGuard
{
    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();

        if (empty($token)) {
            return null;
        }

        $hashed = hash('sha256', (string) $token);
        $user = $this->provider->retrieveByCredentials([
            $this->storageKey => $hashed,
        ]);

        if ($user === null) {
            return null;
        }

        return $this->user = $user;
    }
}
