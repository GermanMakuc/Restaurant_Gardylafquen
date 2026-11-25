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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Waiter::class, WaiterPolicy::class);
        Gate::policy(Table::class, TablePolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);

        Route::pattern('category', '[0-9]+');
        Route::pattern('product', '[0-9]+');
        Route::pattern('waiter', '[0-9]+');
        Route::pattern('table', '[0-9]+');
        Route::pattern('order', '[0-9]+');
        Route::pattern('sale', '[0-9]+');
    }

    public function register(): void
    {
        //
    }
}
