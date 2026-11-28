<?php

namespace Modules\Core\Providers;

use Modules\Core\Repositories\Contracts\SiteRepositoryInterface;
use Modules\Core\Repositories\Eloquent\SiteRepository;

class CoreServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'core';
    }

    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }

    public function register(): void
    {
        $this->app->bind(SiteRepositoryInterface::class, SiteRepository::class);
    }
}
