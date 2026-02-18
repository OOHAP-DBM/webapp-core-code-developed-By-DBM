<?php

namespace Modules\Import\Providers;

use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->publishMigrations();
        $this->loadMigrationsFrom(module_path('import', 'Database/Migrations'));
    }

    /**
     * Publish migrations to database/migrations directory.
     *
     * @return void
     */
    protected function publishMigrations()
    {
        $this->publishes([
            module_path('import', 'Database/Migrations') => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('import', 'Config/config.php') => config_path('import.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('import', 'Config/config.php'), 'import'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/import');

        $sourcePath = module_path('import', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), 'import');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/import');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'import');
            $this->publishes([
                $langPath => $langPath
            ]);
        } else {
            $this->loadTranslationsFrom(module_path('import', 'Resources/lang'), 'import');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        $modulePaths = \Config::get('modules.paths.modules_path');
        
        if (is_array($modulePaths)) {
            foreach ($modulePaths as $path) {
                if (is_dir($path . '/import/Resources/views')) {
                    $paths[] = $path . '/import/Resources/views';
                }
            }
        }
        
        return $paths;
    }
}
