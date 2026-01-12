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
use App\Services\RazorpayService;
use App\Events\PaymentAuthorized;
use App\Events\PaymentCaptured;
use App\Events\PaymentFailed;
use App\Listeners\UpdateBookingOnPaymentAuthorized;
use App\Listeners\OnPaymentCaptured;
use App\Listeners\OnPaymentFailed;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Modules\Offers\Repositories\Contracts\OfferRepositoryInterface;
use App\Services\OfferExpiryService;

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
        // $this->app->singleton(EnquiryService::class, function ($app) {
        //     return new EnquiryService(
        //         $app->make(\Modules\Enquiries\Repositories\Contracts\EnquiryRepositoryInterface::class)
        //     );
        // });

        // Register OfferService as singleton
        $this->app->singleton(OfferService::class, function ($app) {
            return new OfferService(
                // $app->make(\Modules\Offers\Repositories\Contracts\OfferRepositoryInterface::class)
                $app->make(OfferRepositoryInterface::class),
                $app->make(OfferExpiryService::class)
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
                $app->make(\Modules\Bookings\Repositories\Contracts\BookingRepositoryInterface::class),
                $app->make(\Modules\Settings\Services\SettingsService::class)
            );
        });

        // Register RazorpayService as singleton
        $this->app->singleton(RazorpayService::class, function ($app) {
            return new RazorpayService();
        });

        // Register AdminSidebarService as singleton
        $this->app->singleton(\App\Services\AdminSidebarService::class, function ($app) {
            return new \App\Services\AdminSidebarService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure API rate limiters
        $this->configureRateLimiting();

        // Share sidebar counts with all admin views (View Composer)
        view()->composer('layouts.partials.admin.sidebar', function ($view) {
            // Get counts from AdminSidebarService
            $counts = app(\App\Services\AdminSidebarService::class)->getSidebarCounts();
            // Inline comment: Data source is AdminSidebarService (no DB queries in Blade)
            $view->with('requestedVendorCount', $counts['requestedVendorCount']);
            $view->with('totalCustomerCount', $counts['totalCustomerCount']);
        });

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(\App\Models\Booking::class, \App\Policies\BookingPolicy::class);
        Gate::policy(\App\Models\BookingPayment::class, \App\Policies\BookingPaymentPolicy::class);
        Gate::policy(\App\Models\CommissionLog::class, \App\Policies\CommissionLogPolicy::class);
        Gate::policy(\App\Models\QuoteRequest::class, \App\Policies\QuoteRequestPolicy::class);
        Gate::policy(\App\Models\Offer::class, \Modules\Offers\Policies\OfferPolicy::class);

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

        // PROMPT 107: Generate PO when quotation is approved
        Event::listen(
            QuotationApproved::class,
            \Modules\Quotations\Listeners\GeneratePurchaseOrder::class
        );

        // Register Razorpay webhook event listeners
        Event::listen(
            PaymentAuthorized::class,
            UpdateBookingOnPaymentAuthorized::class
        );

        Event::listen(
            PaymentCaptured::class,
            OnPaymentCaptured::class
        );

        Event::listen(
            PaymentFailed::class,
            OnPaymentFailed::class
        );
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Default API rate limiter - 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Authentication endpoints - Strict limits to prevent brute force
        RateLimiter::for('auth', function (Request $request) {
            // 5 login attempts per minute per IP
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many login attempts. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // OTP endpoints - Very strict to prevent SMS/Email flooding
        RateLimiter::for('otp', function (Request $request) {
            // 3 OTP requests per 5 minutes per phone/email
            $identifier = $request->input('phone') ?? $request->input('email') ?? $request->ip();
            
            return [
                Limit::perMinutes(5, 300)
                    ->by('otp:' . $identifier)
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'message' => 'Too many OTP requests. Please wait before requesting again.',
                        'retry_after' => $headers['Retry-After'] ?? 300
                        // 'retry_after' => 10
                        ], 429);
                    }),
                // Additional IP-based limit
                Limit::perMinutes(5, 100)->by('otp-ip:' . $request->ip())
            ];
        });

        // Image/Media uploads - Prevent storage abuse
        RateLimiter::for('uploads', function (Request $request) {
            $user = $request->user();
            
            if (!$user) {
                // Guests can't upload
                return Limit::none();
            }

            // Role-based upload limits
            return match ($user->role) {
                'admin', 'staff' => Limit::perMinute(100)->by($user->id), // Unlimited for admins
                'vendor' => Limit::perMinute(30)->by($user->id), // 30 uploads per minute for vendors
                'customer' => Limit::perMinute(10)->by($user->id), // 10 uploads per minute for customers
                default => Limit::perMinute(5)->by($user->id), // 5 for others
            };
        });

        // General authenticated API - Role-specific limits
        RateLimiter::for('authenticated', function (Request $request) {
            $user = $request->user();
            
            if (!$user) {
                return Limit::perMinute(30)->by($request->ip());
            }

            return match ($user->role) {
                'admin', 'staff' => Limit::perMinute(300)->by($user->id), // High limit for admins
                'vendor' => Limit::perMinute(120)->by($user->id), // Medium-high for vendors
                'customer' => Limit::perMinute(60)->by($user->id), // Standard for customers
                default => Limit::perMinute(30)->by($user->id),
            };
        });

        // Registration - Prevent spam accounts
        RateLimiter::for('register', function (Request $request) {
            if (app()->environment('local')) {
                return Limit::none();
            }
            // Check if the request is coming from the internal console/generator
            if (app()->runningInConsole()) {
                return Limit::none();
            }
            return [
                // 60 registrations per hour per IP
                Limit::perHour(100)
                    ->by($request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response()->json([
                            'message' => 'Too many registration attempts. Please try again later.',
                            'retry_after' => $headers['Retry-After'] ?? 3600
                        ], 429);
                    }),
                // Also limit by email/phone if provided
                Limit::perDay(1)->by('register:' . ($request->input('email') ?? $request->input('phone') ?? $request->ip()))
            ];
        });

        // Search endpoints - Prevent scraping
        RateLimiter::for('search', function (Request $request) {
            $user = $request->user();
            
            if ($user) {
                return match ($user->role) {
                    'admin', 'staff' => Limit::perMinute(100)->by($user->id),
                    'vendor' => Limit::perMinute(50)->by($user->id),
                    'customer' => Limit::perMinute(30)->by($user->id),
                    default => Limit::perMinute(20)->by($user->id),
                };
            }

            // Stricter for guests
            return Limit::perMinute(10)->by($request->ip());
        });

        // Critical operations (payments, bookings) - Conservative limits
        RateLimiter::for('critical', function (Request $request) {
            $user = $request->user();
            
            if (!$user) {
                return Limit::none();
            }

            return Limit::perMinute(10)
                ->by($user->id)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many requests. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // Webhook endpoints - Higher limits for external services
        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });
    }
}
