<?php

declare(strict_types=1);

namespace App\Providers;

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
    }
}
