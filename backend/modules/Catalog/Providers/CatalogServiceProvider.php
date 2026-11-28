<?php

namespace Modules\Catalog\Providers;

use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Repositories\Contracts\ProductDetailsRepositoryInterface;
use Modules\Catalog\Repositories\Contracts\SectionRepositoryInterface;
use Modules\Catalog\Repositories\Contracts\SliderRepositoryInterface;
use Modules\Catalog\Repositories\Eloquent\CategoryRepository;
use Modules\Catalog\Repositories\Eloquent\ProductDetailsRepository;
use Modules\Catalog\Repositories\Eloquent\SectionRepository;
use Modules\Catalog\Repositories\Eloquent\SliderRepository;
use Modules\Core\Providers\BaseModuleServiceProvider;

class CatalogServiceProvider extends BaseModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'catalog';
    }

    protected function modulePath(): string
    {
        return dirname(__DIR__);
    }

    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
        $this->app->bind(SliderRepositoryInterface::class, SliderRepository::class);
        $this->app->bind(ProductDetailsRepositoryInterface::class, ProductDetailsRepository::class);
    }
}
