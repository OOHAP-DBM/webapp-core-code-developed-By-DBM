<?php

namespace Modules\Offers\Providers;

use Illuminate\Support\ServiceProvider;

class OfferServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register Offer module services
    }

    public function boot()
    {
        // Load Offer module web routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        // Load Offer module views with namespace 'offers'
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'offers');
    }
}
