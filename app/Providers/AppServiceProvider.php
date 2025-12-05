<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Settings\Services\SettingsService;
use Modules\Hoardings\Services\HoardingService;
use Modules\Enquiries\Services\EnquiryService;
use Modules\Enquiries\Events\EnquiryCreated;
use Modules\Enquiries\Listeners\NotifyVendor;
use Modules\Offers\Services\OfferService;
use Modules\Offers\Events\OfferSent;
use Modules\Offers\Listeners\NotifyCustomer;
use Modules\Quotations\Services\QuotationService;
use Modules\Quotations\Events\QuotationApproved;
use Modules\Bookings\Services\BookingService;
use Modules\Bookings\Events\BookingCreated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register SettingsService as singleton
        $this->app->singleton(SettingsService::class, function ($app) {
            return new SettingsService(
                $app->make(\Modules\Settings\Repositories\Contracts\SettingRepositoryInterface::class)
            );
        });

        // Register HoardingService as singleton
        $this->app->singleton(HoardingService::class, function ($app) {
            return new HoardingService(
                $app->make(\Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface::class)
            );
        });

        // Register EnquiryService as singleton
        $this->app->singleton(EnquiryService::class, function ($app) {
            return new EnquiryService(
                $app->make(\Modules\Enquiries\Repositories\Contracts\EnquiryRepositoryInterface::class)
            );
        });

        // Register OfferService as singleton
        $this->app->singleton(OfferService::class, function ($app) {
            return new OfferService(
                $app->make(\Modules\Offers\Repositories\Contracts\OfferRepositoryInterface::class)
            );
        });

        // Register QuotationService as singleton
        $this->app->singleton(QuotationService::class, function ($app) {
            return new QuotationService(
                $app->make(\Modules\Quotations\Repositories\Contracts\QuotationRepositoryInterface::class)
            );
        });

        // Register BookingService as singleton
        $this->app->singleton(BookingService::class, function ($app) {
            return new BookingService(
                $app->make(\Modules\Bookings\Repositories\Contracts\BookingRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(User::class, UserPolicy::class);

        // Register event listeners
        Event::listen(
            EnquiryCreated::class,
            NotifyVendor::class
        );

        Event::listen(
            OfferSent::class,
            NotifyCustomer::class
        );

        Event::listen(
            QuotationApproved::class,
            \Modules\Quotations\Listeners\NotifyVendorOnApproval::class
        );
    }
}
