<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AccessControl\Providers\AccessControlServiceProvider;
use Modules\Catalog\Providers\CatalogServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Customers\Providers\CustomersServiceProvider;
use Modules\Dashboard\Providers\DashboardServiceProvider;
use Modules\Marketing\Providers\MarketingServiceProvider;
use Modules\Sales\Providers\SalesServiceProvider;

class AdminModulesServiceProvider extends ServiceProvider
{
    /**
     * Central registry of all Modular Monolith domains.
     * Core registers first so shared views, migrations, and site bindings load before domain modules.
     *
     * @var array<int, class-string<ServiceProvider>>
     */
    protected array $modules = [
        CoreServiceProvider::class,
        DashboardServiceProvider::class,
        CatalogServiceProvider::class,
        SalesServiceProvider::class,
        MarketingServiceProvider::class,
        CustomersServiceProvider::class,
        AccessControlServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->modules as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
}
