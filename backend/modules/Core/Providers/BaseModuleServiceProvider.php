<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    /**
     * Lowercase module identifier (e.g. 'dashboard', 'catalog').
     */
    abstract protected function moduleName(): string;

    /**
     * Absolute path to the module root directory (e.g. dirname(__DIR__)).
     */
    abstract protected function modulePath(): string;

    public function boot(): void
    {
        $this->registerModuleMigrations();
        $this->registerModuleRoutes();
    }

    protected function registerModuleMigrations(): void
    {
        $migrationsPath = $this->modulePath() . DIRECTORY_SEPARATOR . 'Database' . DIRECTORY_SEPARATOR . 'Migrations';

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    protected function registerModuleRoutes(): void
    {
        $webRoutes = $this->modulePath() . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
        if (is_file($webRoutes)) {
            Route::middleware(['web', 'auth', 'admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group($webRoutes);
        }

        $apiRoutes = $this->modulePath() . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
        if (is_file($apiRoutes)) {
            Route::middleware(['api'])
                ->prefix('api')
                ->group($apiRoutes);
        }
    }
}
