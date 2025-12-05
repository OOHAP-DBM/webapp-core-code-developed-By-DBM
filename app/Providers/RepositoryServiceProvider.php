<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider
 * 
 * Binds repository interfaces to their concrete implementations
 * following the repository pattern for clean architecture.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings
     */
    protected array $repositories = [
        // Auth Module
        // \Modules\Auth\Repositories\Contracts\AuthRepositoryInterface::class => \Modules\Auth\Repositories\AuthRepository::class,

        // Users Module
        \Modules\Users\Repositories\Contracts\UserRepositoryInterface::class => \Modules\Users\Repositories\UserRepository::class,

        // Settings Module
        \Modules\Settings\Repositories\Contracts\SettingRepositoryInterface::class => \Modules\Settings\Repositories\SettingRepository::class,

        // Hoardings Module
        \Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface::class => \Modules\Hoardings\Repositories\HoardingRepository::class,

        // Enquiries Module
        \Modules\Enquiries\Repositories\Contracts\EnquiryRepositoryInterface::class => \Modules\Enquiries\Repositories\EnquiryRepository::class,

        // Offers Module
        \Modules\Offers\Repositories\Contracts\OfferRepositoryInterface::class => \Modules\Offers\Repositories\OfferRepository::class,

        // Quotations Module
        \Modules\Quotations\Repositories\Contracts\QuotationRepositoryInterface::class => \Modules\Quotations\Repositories\QuotationRepository::class,

        // DOOH Module
        // \Modules\DOOH\Repositories\Contracts\DOOHRepositoryInterface::class => \Modules\DOOH\Repositories\DOOHRepository::class,

        // Quotation Module
        // \Modules\Quotation\Repositories\Contracts\QuotationRepositoryInterface::class => \Modules\Quotation\Repositories\QuotationRepository::class,

        // Booking Module
        // \Modules\Booking\Repositories\Contracts\BookingRepositoryInterface::class => \Modules\Booking\Repositories\BookingRepository::class,

        // Payment Module
        // \Modules\Payment\Repositories\Contracts\PaymentRepositoryInterface::class => \Modules\Payment\Repositories\PaymentRepository::class,

        // Vendor Module
        // \Modules\Vendor\Repositories\Contracts\VendorRepositoryInterface::class => \Modules\Vendor\Repositories\VendorRepository::class,

        // KYC Module
        // \Modules\KYC\Repositories\Contracts\KYCRepositoryInterface::class => \Modules\KYC\Repositories\KYCRepository::class,

        // Staff Module
        // \Modules\Staff\Repositories\Contracts\StaffRepositoryInterface::class => \Modules\Staff\Repositories\StaffRepository::class,

        // Admin Module
        // \Modules\Admin\Repositories\Contracts\AdminRepositoryInterface::class => \Modules\Admin\Repositories\AdminRepository::class,

        // Settings Module
        // \Modules\Settings\Repositories\Contracts\SettingRepositoryInterface::class => \Modules\Settings\Repositories\SettingRepository::class,

        // Notifications Module
        // \Modules\Notifications\Repositories\Contracts\NotificationRepositoryInterface::class => \Modules\Notifications\Repositories\NotificationRepository::class,

        // Reports Module
        // \Modules\Reports\Repositories\Contracts\ReportRepositoryInterface::class => \Modules\Reports\Repositories\ReportRepository::class,

        // Media Module
        // \Modules\Media\Repositories\Contracts\MediaRepositoryInterface::class => \Modules\Media\Repositories\MediaRepository::class,

        // Search Module
        // \Modules\Search\Repositories\Contracts\SearchRepositoryInterface::class => \Modules\Search\Repositories\SearchRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind all repository interfaces to implementations
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
