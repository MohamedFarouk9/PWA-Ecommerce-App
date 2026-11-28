<?php

namespace Modules\Sales\Providers;

use Modules\Core\Providers\BaseModuleServiceProvider;
use Modules\Sales\Repositories\Contracts\OrderRepositoryInterface;
use Modules\Sales\Repositories\Eloquent\OrderRepository;

class SalesServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'sales';
    }

    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }

    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }
}
