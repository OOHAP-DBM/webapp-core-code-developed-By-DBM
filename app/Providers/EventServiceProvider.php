<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\HoardingStatusChanged::class => [
            \App\Listeners\SendHoardingStatusNotification::class,
        ],
        \Modules\POS\Events\PosBookingCreated::class => [
            \App\Listeners\SendPosBookingCreatedNotification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
