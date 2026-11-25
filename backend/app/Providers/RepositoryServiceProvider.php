<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Services\ProductService;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);

        $this->app->when(ProductService::class)
            ->needs('$imageDisk')
            ->give(static function (): string {
                return (string) config('filesystems.product_images_disk', 'public');
            });
    }

    public function boot(): void
    {
        //
    }
}
