<?php
namespace App\Providers;

use App\Repositories\Contracts\API\ApiCategoryRepositoryInterface;
use App\Repositories\Contracts\API\ApiProductRepositoryInterface;
use App\Repositories\Contracts\API\ApiSliderRepositoryInterface;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Eloquent\API\ApiCategoryRepository;
use App\Repositories\Eloquent\API\ApiProductRepository;
use App\Repositories\Eloquent\API\ApiSliderRepository;
use App\Repositories\Eloquent\AdminUserRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\NotificationRepository;
use App\Repositories\Eloquent\PaymentRepository;
use App\Repositories\Eloquent\ReviewRepository;
use App\Services\ImageService;
use App\Services\ImageOptimizationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // API Product Repository Binding
        $this->app->bind(
            ApiProductRepositoryInterface::class,
            ApiProductRepository::class
        );

        // API Category Repository Binding
        $this->app->bind(
            ApiCategoryRepositoryInterface::class,
            ApiCategoryRepository::class
        );

        // API Slider Repository Binding
        $this->app->bind(
            ApiSliderRepositoryInterface::class,
            ApiSliderRepository::class
        );

        // Cart Repository Binding
        $this->app->bind(
            CartRepositoryInterface::class,
            CartRepository::class
        );

        // Review Repository Binding
        $this->app->bind(
            ReviewRepositoryInterface::class,
            ReviewRepository::class
        );

        // Auth Repository Binding
        $this->app->bind(
            AuthRepositoryInterface::class,
            AuthRepository::class
        );

        // Notification Repository Binding
        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );

        // Payment Repository Binding
        $this->app->bind(
            PaymentRepositoryInterface::class,
            PaymentRepository::class
        );

        // Admin User Repository Binding
        $this->app->bind(
            AdminUserRepositoryInterface::class,
            AdminUserRepository::class
        );

        // Image Service Binding
        $this->app->singleton(ImageService::class, function () {
            return new ImageService();
        });

        // Image Optimization Service Binding
        $this->app->singleton(ImageOptimizationService::class, function () {
            return new ImageOptimizationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
