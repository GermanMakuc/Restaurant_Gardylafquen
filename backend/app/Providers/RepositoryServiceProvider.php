<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Domain repository interface -> Eloquent implementation bindings go here in Phase 2.
    }

    public function boot(): void
    {
        //
    }
}
