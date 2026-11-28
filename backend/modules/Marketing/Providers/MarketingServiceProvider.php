<?php

namespace Modules\Marketing\Providers;

use Modules\Core\Providers\BaseModuleServiceProvider;
use Modules\Marketing\Repositories\Contracts\ContactRepositoryInterface;
use Modules\Marketing\Repositories\Contracts\NotificationRepositoryInterface;
use Modules\Marketing\Repositories\Eloquent\ContactRepository;
use Modules\Marketing\Repositories\Eloquent\NotificationRepository;

class MarketingServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'marketing';
    }

    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }

    public function register(): void
    {
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
    }
}
