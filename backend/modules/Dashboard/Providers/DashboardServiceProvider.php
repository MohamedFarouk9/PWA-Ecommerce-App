<?php

namespace Modules\Dashboard\Providers;

use Modules\Core\Providers\BaseModuleServiceProvider;
use Modules\Dashboard\Repositories\Contracts\DashboardRepositoryInterface;
use Modules\Dashboard\Repositories\Eloquent\DashboardRepository;

class DashboardServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'dashboard';
    }

    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }

    public function register(): void
    {
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
    }
}
