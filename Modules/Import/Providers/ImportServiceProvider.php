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
        $this->loadMigrationsFrom(base_path('Modules/Import/Database/Migrations'));
    }

    /**
     * Publish migrations to database/migrations directory.
     *
     * @return void
     */
    protected function publishMigrations()
    {
        $this->publishes([
            base_path('Modules/Import/Database/Migrations') => database_path('migrations'),
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
            base_path('Modules/Import/Config/config.php') => config_path('import.php'),
        ], 'config');
        $this->mergeConfigFrom(
            base_path('Modules/Import/Config/config.php'), 'import'
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

        $sourcePath = base_path('Modules/Import/Resources/views');

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
            $this->loadTranslationsFrom(base_path('Modules/Import/Resources/lang'), 'import');
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
