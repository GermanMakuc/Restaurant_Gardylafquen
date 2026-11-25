<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\HashedTokenGuard;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Table;
use App\Models\Waiter;
use App\Policies\CategoryPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use App\Policies\TablePolicy;
use App\Policies\WaiterPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Category::class => CategoryPolicy::class,
        Product::class => ProductPolicy::class,
        Waiter::class => WaiterPolicy::class,
        Table::class => TablePolicy::class,
        Order::class => OrderPolicy::class,
        Sale::class => SalePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Auth::extend('hashed_token', static function ($app, string $name, array $config): HashedTokenGuard {
            /** @var Request $request */
            $request = $app->make('request');
            $provider = Auth::createUserProvider($config['provider'] ?? 'users');
            if ($provider === null) {
                throw new \RuntimeException('User provider is not configured.');
            }

            $guard = new HashedTokenGuard($provider, $request);
            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
