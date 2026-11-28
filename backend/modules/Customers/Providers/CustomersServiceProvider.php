<?php

namespace Modules\Customers\Providers;

use Modules\Core\Providers\BaseModuleServiceProvider;

class CustomersServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'customers';
    }

    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }
}
