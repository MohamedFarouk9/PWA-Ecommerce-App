<?php
namespace App\Providers;

use App\Repositories\Contracts\Admin\OrderRepositoryInterface;
use App\Repositories\Contracts\API\ApiCategoryRepositoryInterface;
use App\Repositories\Contracts\API\ApiProductRepositoryInterface;
use App\Repositories\Contracts\API\ApiSliderRepositoryInterface;
use App\Repositories\Contracts\Admin\AdminNotificationRepositoryInterface;
use App\Repositories\Contracts\Admin\AdminProductRepositoryInterface;
use App\Repositories\Contracts\Admin\AdminSectionRepositoryInterface;
use App\Repositories\Contracts\Admin\AdminSiteRepositoryInterface;
use App\Repositories\Contracts\Admin\AdminSliderRepositoryInterface;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Eloquent\Admin\OrderRepository;
use App\Repositories\Eloquent\API\ApiCategoryRepository;
use App\Repositories\Eloquent\API\ApiProductRepository;
use App\Repositories\Eloquent\API\ApiSliderRepository;
use App\Repositories\Eloquent\Admin\AdminNotificationRepository;
use App\Repositories\Eloquent\Admin\AdminProductRepository;
use App\Repositories\Eloquent\Admin\AdminSectionRepository;
use App\Repositories\Eloquent\Admin\AdminSiteRepository;
use App\Repositories\Eloquent\Admin\AdminSliderRepository;
use App\Repositories\Eloquent\AdminUserRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\ContactRepository;
use App\Repositories\Eloquent\NotificationRepository;
use App\Repositories\Eloquent\PaymentRepository;
use App\Repositories\Eloquent\ReviewRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Admin Category Repository Binding
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        // Add to register() method:
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

        // Contact Repository Binding
        $this->app->bind(
            ContactRepositoryInterface::class,
            ContactRepository::class
        );

        // Admin Slider Repository Binding
        $this->app->bind(
            AdminSliderRepositoryInterface::class,
            AdminSliderRepository::class
        );

        // Admin Section Repository Binding
        $this->app->bind(
            AdminSectionRepositoryInterface::class,
            AdminSectionRepository::class
        );

        // Admin Site Repository Binding
        $this->app->bind(
            AdminSiteRepositoryInterface::class,
            AdminSiteRepository::class
        );

        // Admin Notification Repository Binding
        $this->app->bind(
            AdminNotificationRepositoryInterface::class,
            AdminNotificationRepository::class
        );

        // Admin Product Repository Binding
        $this->app->bind(
            AdminProductRepositoryInterface::class,
            AdminProductRepository::class
        );
        // Admin Order Repository Binding
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class

        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
