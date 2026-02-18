<?php

namespace Modules\Import\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $moduleNamespace = 'Modules\Import\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * @return void
     */
    public function map()
    {
        $this->mapVendorWebRoutes();
        $this->mapAdminWebRoutes();
        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for vendors.
     *
     * @return void
     */
    protected function mapVendorWebRoutes()
    {
        Route::prefix('vendor/import')
            ->middleware(['web', 'auth'])
            ->name('vendor.import.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('import', 'Routes/web.php'));
    }

    /**
     * Define the "web" routes for admins.
     *
     * @return void
     */
    protected function mapAdminWebRoutes()
    {
        Route::prefix('admin/import')
            ->middleware(['web', 'auth'])
            ->name('admin.import.')
            ->namespace($this->moduleNamespace)
            ->group(module_path('import', 'Routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('import', 'Routes/api.php'));
    }
}
