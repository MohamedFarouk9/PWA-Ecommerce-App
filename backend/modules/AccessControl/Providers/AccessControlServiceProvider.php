<?php

namespace Modules\AccessControl\Providers;

use Modules\Core\Providers\BaseModuleServiceProvider;

class AccessControlServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'access-control';
    }

    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
