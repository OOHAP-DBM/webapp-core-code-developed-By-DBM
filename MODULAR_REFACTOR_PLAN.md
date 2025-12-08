# 🔄 Complete Modular Architecture Refactor Plan

## 📊 Current State Analysis

### Misplaced Files Detected in `app/` Directory

#### **Models** (Should be in respective Modules)
- ✗ `app/Models/Booking.php` → `Modules/Bookings/Models/Booking.php`
- ✗ `app/Models/BookingPayment.php` → `Modules/Payment/Models/BookingPayment.php`
- ✗ `app/Models/BookingPriceSnapshot.php` → `Modules/Bookings/Models/BookingPriceSnapshot.php`
- ✗ `app/Models/BookingProof.php` → `Modules/Bookings/Models/BookingProof.php`
- ✗ `app/Models/BookingStatusLog.php` → `Modules/Bookings/Models/BookingStatusLog.php`
- ✗ `app/Models/CommissionLog.php` → `Modules/Payment/Models/CommissionLog.php`
- ✗ `app/Models/Enquiry.php` → `Modules/Enquiries/Models/Enquiry.php`
- ✗ `app/Models/Hoarding.php` → `Modules/Hoardings/Models/Hoarding.php`
- ✗ `app/Models/HoardingGeo.php` → `Modules/Hoardings/Models/HoardingGeo.php`
- ✗ `app/Models/Offer.php` → `Modules/Offers/Models/Offer.php`
- ✗ `app/Models/Quotation.php` → `Modules/Quotations/Models/Quotation.php`
- ✗ `app/Models/RazorpayLog.php` → `Modules/Payment/Models/RazorpayLog.php`
- ✗ `app/Models/Setting.php` → `Modules/Settings/Models/Setting.php`
- ✗ `app/Models/User.php` → `Modules/Users/Models/User.php`
- ✗ `app/Models/VendorKYC.php` → `Modules/KYC/Models/VendorKYC.php`

#### **Services** (Should be in respective Modules)
- ✗ `app/Services/RazorpayService.php` → `Modules/Payment/Services/RazorpayService.php`
- ✗ `app/Services/RazorpayPayoutService.php` → `Modules/Payment/Services/RazorpayPayoutService.php`
- ✗ `app/Services/CommissionService.php` → `Modules/Payment/Services/CommissionService.php`
- ✗ `app/Services/PODService.php` → `Modules/Bookings/Services/PODService.php`

#### **Events** (Should be in respective Modules)
- ✗ `app/Events/PaymentAuthorized.php` → `Modules/Payment/Events/PaymentAuthorized.php`
- ✗ `app/Events/PaymentCaptured.php` → `Modules/Payment/Events/PaymentCaptured.php`
- ✗ `app/Events/PaymentFailed.php` → `Modules/Payment/Events/PaymentFailed.php`
- ✗ `app/Events/BookingPaymentVoided.php` → `Modules/Payment/Events/BookingPaymentVoided.php`
- ✗ `app/Events/BookingActivated.php` → `Modules/Bookings/Events/BookingActivated.php`

#### **Listeners** (Should be in respective Modules)
- ✗ `app/Listeners/UpdateBookingOnPaymentAuthorized.php` → `Modules/Payment/Listeners/UpdateBookingOnPaymentAuthorized.php`
- ✗ `app/Listeners/OnPaymentCaptured.php` → `Modules/Payment/Listeners/OnPaymentCaptured.php`
- ✗ `app/Listeners/OnPaymentFailed.php` → `Modules/Payment/Listeners/OnPaymentFailed.php`

#### **Jobs** (Should be in respective Modules)
- ✗ `app/Jobs/ScheduleBookingConfirmJob.php` → `Modules/Bookings/Jobs/ScheduleBookingConfirmJob.php`
- ✗ `app/Jobs/CaptureExpiredHoldsJob.php` → `Modules/Payment/Jobs/CaptureExpiredHoldsJob.php`

#### **Controllers** (Should be in respective Modules)
- ✗ `app/Http/Controllers/Api/RazorpayWebhookController.php` → `Modules/Payment/Controllers/Api/RazorpayWebhookController.php`
- ✗ `app/Http/Controllers/Api/Admin/AdminKYCController.php` → `Modules/KYC/Controllers/Api/AdminKYCController.php`
- ✗ `app/Http/Controllers/Api/Vendor/VendorKYCController.php` → `Modules/KYC/Controllers/Api/VendorKYCController.php`
- ✗ `app/Http/Controllers/Api/Vendor/VendorPODController.php` → `Modules/Bookings/Controllers/Api/VendorPODController.php`
- ✗ `app/Http/Controllers/Api/Staff/PODController.php` → `Modules/Bookings/Controllers/Api/StaffPODController.php`
- ✗ `app/Http/Controllers/Admin/BookingHoldController.php` → `Modules/Payment/Controllers/Api/BookingHoldController.php`
- ✗ `app/Http/Controllers/Admin/FinanceController.php` → `Modules/Payment/Controllers/Api/FinanceController.php`
- ✗ `app/Http/Controllers/Web/Admin/*` → `Modules/Admin/Controllers/Web/*`
- ✗ `app/Http/Controllers/Web/Vendor/*` → `Modules/Vendor/Controllers/Web/*`
- ✗ `app/Http/Controllers/Web/Customer/*` → `Modules/Customer/Controllers/Web/*`
- ✗ `app/Http/Controllers/Web/Auth/*` → `Modules/Auth/Controllers/Web/*`
- ✗ `app/Http/Controllers/Web/HoardingController.php` → `Modules/Hoardings/Controllers/Web/HoardingController.php`

#### **Policies** (Should be in respective Modules)
- ✗ `app/Policies/UserPolicy.php` → `Modules/Users/Policies/UserPolicy.php`

---

## 🎯 Refactor Strategy

### Phase 1: Create Module Structure Directories

#### For Each Module, Create:
```
Modules/<ModuleName>/
├── Controllers/
│   ├── Api/
│   └── Web/
├── Models/
├── Services/
├── Repositories/
│   └── Contracts/
├── Events/
├── Listeners/
├── Jobs/
├── Policies/
├── Requests/
├── Resources/
├── Notifications/
└── Tests/
```

---

## 📦 Detailed Migration Plan

### Module: **Bookings**

#### Files to Move:
```
app/Models/Booking.php                           → Modules/Bookings/Models/Booking.php
app/Models/BookingPriceSnapshot.php              → Modules/Bookings/Models/BookingPriceSnapshot.php
app/Models/BookingProof.php                      → Modules/Bookings/Models/BookingProof.php
app/Models/BookingStatusLog.php                  → Modules/Bookings/Models/BookingStatusLog.php
app/Services/PODService.php                      → Modules/Bookings/Services/PODService.php
app/Events/BookingActivated.php                  → Modules/Bookings/Events/BookingActivated.php
app/Jobs/ScheduleBookingConfirmJob.php           → Modules/Bookings/Jobs/ScheduleBookingConfirmJob.php
app/Http/Controllers/Api/Vendor/VendorPODController.php → Modules/Bookings/Controllers/Api/VendorPODController.php
app/Http/Controllers/Api/Staff/PODController.php → Modules/Bookings/Controllers/Api/StaffPODController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Services;
namespace App\Events;
namespace App\Jobs;
namespace App\Http\Controllers\Api\Vendor;
namespace App\Http\Controllers\Api\Staff;

// NEW
namespace Modules\Bookings\Models;
namespace Modules\Bookings\Services;
namespace Modules\Bookings\Events;
namespace Modules\Bookings\Jobs;
namespace Modules\Bookings\Controllers\Api;
```

---

### Module: **Payment**

#### Files to Move:
```
app/Models/BookingPayment.php                    → Modules/Payment/Models/BookingPayment.php
app/Models/CommissionLog.php                     → Modules/Payment/Models/CommissionLog.php
app/Models/RazorpayLog.php                       → Modules/Payment/Models/RazorpayLog.php
app/Services/RazorpayService.php                 → Modules/Payment/Services/RazorpayService.php
app/Services/RazorpayPayoutService.php           → Modules/Payment/Services/RazorpayPayoutService.php
app/Services/CommissionService.php               → Modules/Payment/Services/CommissionService.php
app/Events/PaymentAuthorized.php                 → Modules/Payment/Events/PaymentAuthorized.php
app/Events/PaymentCaptured.php                   → Modules/Payment/Events/PaymentCaptured.php
app/Events/PaymentFailed.php                     → Modules/Payment/Events/PaymentFailed.php
app/Events/BookingPaymentVoided.php              → Modules/Payment/Events/BookingPaymentVoided.php
app/Listeners/UpdateBookingOnPaymentAuthorized.php → Modules/Payment/Listeners/UpdateBookingOnPaymentAuthorized.php
app/Listeners/OnPaymentCaptured.php              → Modules/Payment/Listeners/OnPaymentCaptured.php
app/Listeners/OnPaymentFailed.php                → Modules/Payment/Listeners/OnPaymentFailed.php
app/Jobs/CaptureExpiredHoldsJob.php              → Modules/Payment/Jobs/CaptureExpiredHoldsJob.php
app/Http/Controllers/Api/RazorpayWebhookController.php → Modules/Payment/Controllers/Api/RazorpayWebhookController.php
app/Http/Controllers/Admin/BookingHoldController.php → Modules/Payment/Controllers/Api/BookingHoldController.php
app/Http/Controllers/Admin/FinanceController.php → Modules/Payment/Controllers/Api/FinanceController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Services;
namespace App\Events;
namespace App\Listeners;
namespace App\Jobs;
namespace App\Http\Controllers\Api;
namespace App\Http\Controllers\Admin;

// NEW
namespace Modules\Payment\Models;
namespace Modules\Payment\Services;
namespace Modules\Payment\Events;
namespace Modules\Payment\Listeners;
namespace Modules\Payment\Jobs;
namespace Modules\Payment\Controllers\Api;
```

---

### Module: **Hoardings**

#### Files to Move:
```
app/Models/Hoarding.php                          → Modules/Hoardings/Models/Hoarding.php
app/Models/HoardingGeo.php                       → Modules/Hoardings/Models/HoardingGeo.php
app/Http/Controllers/Web/HoardingController.php  → Modules/Hoardings/Controllers/Web/HoardingController.php
app/Http/Controllers/Web/Vendor/HoardingController.php → Modules/Hoardings/Controllers/Web/VendorHoardingController.php
app/Http/Controllers/Web/Admin/HoardingController.php → Modules/Hoardings/Controllers/Web/AdminHoardingController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Http\Controllers\Web;
namespace App\Http\Controllers\Web\Vendor;
namespace App\Http\Controllers\Web\Admin;

// NEW
namespace Modules\Hoardings\Models;
namespace Modules\Hoardings\Controllers\Web;
```

---

### Module: **Enquiries**

#### Files to Move:
```
app/Models/Enquiry.php                           → Modules/Enquiries/Models/Enquiry.php
app/Http/Controllers/Web/Customer/EnquiryController.php → Modules/Enquiries/Controllers/Web/CustomerEnquiryController.php
app/Http/Controllers/Web/Vendor/EnquiryController.php → Modules/Enquiries/Controllers/Web/VendorEnquiryController.php
app/Http/Controllers/Web/Admin/EnquiryController.php → Modules/Enquiries/Controllers/Web/AdminEnquiryController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Http\Controllers\Web\Customer;
namespace App\Http\Controllers\Web\Vendor;
namespace App\Http\Controllers\Web\Admin;

// NEW
namespace Modules\Enquiries\Models;
namespace Modules\Enquiries\Controllers\Web;
```

---

### Module: **Offers**

#### Files to Move:
```
app/Models/Offer.php                             → Modules/Offers/Models/Offer.php
app/Http/Controllers/Web/Customer/OfferController.php → Modules/Offers/Controllers/Web/CustomerOfferController.php
app/Http/Controllers/Web/Vendor/OfferController.php → Modules/Offers/Controllers/Web/VendorOfferController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Http\Controllers\Web\Customer;
namespace App\Http\Controllers\Web\Vendor;

// NEW
namespace Modules\Offers\Models;
namespace Modules\Offers\Controllers\Web;
```

---

### Module: **Quotations**

#### Files to Move:
```
app/Models/Quotation.php                         → Modules/Quotations/Models/Quotation.php
app/Http/Controllers/Web/Customer/QuotationController.php → Modules/Quotations/Controllers/Web/CustomerQuotationController.php
app/Http/Controllers/Web/Vendor/QuotationController.php → Modules/Quotations/Controllers/Web/VendorQuotationController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Http\Controllers\Web\Customer;
namespace App\Http\Controllers\Web\Vendor;

// NEW
namespace Modules\Quotations\Models;
namespace Modules\Quotations\Controllers\Web;
```

---

### Module: **KYC**

#### Files to Move:
```
app/Models/VendorKYC.php                         → Modules/KYC/Models/VendorKYC.php
app/Http/Controllers/Api/Vendor/VendorKYCController.php → Modules/KYC/Controllers/Api/VendorKYCController.php
app/Http/Controllers/Api/Admin/AdminKYCController.php → Modules/KYC/Controllers/Api/AdminKYCController.php
app/Http/Controllers/Web/Admin/AdminKYCWebController.php → Modules/KYC/Controllers/Web/AdminKYCWebController.php
app/Http/Controllers/Web/Admin/AdminKYCReviewController.php → Modules/KYC/Controllers/Web/AdminKYCReviewController.php
app/Http/Controllers/Web/Vendor/VendorKYCWebController.php → Modules/KYC/Controllers/Web/VendorKYCWebController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Http\Controllers\Api\Vendor;
namespace App\Http\Controllers\Api\Admin;
namespace App\Http\Controllers\Web\Admin;
namespace App\Http\Controllers\Web\Vendor;

// NEW
namespace Modules\KYC\Models;
namespace Modules\KYC\Controllers\Api;
namespace Modules\KYC\Controllers\Web;
```

---

### Module: **Users**

#### Files to Move:
```
app/Models/User.php                              → Modules/Users/Models/User.php
app/Policies/UserPolicy.php                      → Modules/Users/Policies/UserPolicy.php
app/Http/Controllers/Web/Admin/UserController.php → Modules/Users/Controllers/Web/AdminUserController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Policies;
namespace App\Http\Controllers\Web\Admin;

// NEW
namespace Modules\Users\Models;
namespace Modules\Users\Policies;
namespace Modules\Users\Controllers\Web;
```

---

### Module: **Settings**

#### Files to Move:
```
app/Models/Setting.php                           → Modules/Settings/Models/Setting.php
app/Http/Controllers/Web/Admin/SettingController.php → Modules/Settings/Controllers/Web/AdminSettingController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Models;
namespace App\Http\Controllers\Web\Admin;

// NEW
namespace Modules\Settings\Models;
namespace Modules\Settings/Controllers/Web;
```

---

### Module: **Auth**

#### Files to Move:
```
app/Http/Controllers/Web/Auth/LoginController.php → Modules/Auth/Controllers/Web/LoginController.php
app/Http/Controllers/Web/Auth/RegisterController.php → Modules/Auth/Controllers/Web/RegisterController.php
app/Http/Controllers/Web/Auth/OTPController.php  → Modules/Auth/Controllers/Web/OTPController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Http\Controllers\Web\Auth;

// NEW
namespace Modules\Auth\Controllers\Web;
```

---

### Module: **Admin**

#### Files to Move:
```
app/Http/Controllers/Web/Admin/DashboardController.php → Modules/Admin/Controllers/Web/DashboardController.php
app/Http/Controllers/Web/Admin/VendorController.php → Modules/Admin/Controllers/Web/VendorController.php
app/Http/Controllers/Web/Admin/BookingController.php → Modules/Admin/Controllers/Web/BookingController.php
app/Http/Controllers/Web/Admin/PaymentController.php → Modules/Admin/Controllers/Web/PaymentController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Http\Controllers\Web\Admin;

// NEW
namespace Modules\Admin\Controllers\Web;
```

---

### Module: **Vendor**

#### Files to Move:
```
app/Http/Controllers/Web/Vendor/BookingController.php → Modules/Vendor/Controllers/Web/BookingController.php
app/Http/Controllers/Web/Vendor/StaffController.php → Modules/Vendor/Controllers/Web/StaffController.php
```

#### Namespace Changes:
```php
// OLD
namespace App\Http\Controllers\Web\Vendor;

// NEW
namespace Modules\Vendor\Controllers/Web;
```

---

### Module: **Customer** (New Module Needed)

#### Create Structure:
```
Modules/Customer/
├── Controllers/
│   └── Web/
│       ├── BookingController.php
│       ├── PaymentController.php
│       └── ProfileController.php
```

#### Files to Move:
```
app/Http/Controllers/Web/Customer/BookingController.php → Modules/Customer/Controllers/Web/BookingController.php
app/Http/Controllers/Web/Customer/PaymentController.php → Modules/Customer/Controllers/Web/PaymentController.php
```

---

## 🔧 Required Configuration Updates

### 1. **composer.json** - Update PSR-4 Autoloading

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Modules\\": "Modules/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

### 2. **app/Providers/RepositoryServiceProvider.php** - Update Bindings

No changes needed - already using `Modules\` namespace.

### 3. **app/Providers/AppServiceProvider.php** - Update Event Listeners

```php
// Update use statements
use Modules\Payment\Events\PaymentAuthorized;
use Modules\Payment\Events\PaymentCaptured;
use Modules\Payment\Events\PaymentFailed;
use Modules\Payment\Listeners\UpdateBookingOnPaymentAuthorized;
use Modules\Payment\Listeners\OnPaymentCaptured;
use Modules\Payment\Listeners\OnPaymentFailed;
use Modules\Bookings\Events\BookingActivated;
```

### 4. **routes/web.php** - Update Controller References

```php
// OLD
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Admin\DashboardController;

// NEW
use Modules\Auth\Controllers\Web\LoginController;
use Modules\Admin\Controllers\Web\DashboardController;
```

### 5. **routes/api_v1/*.php** - Update Controller References

```php
// vendors.php
use Modules\KYC\Controllers\Api\VendorKYCController;

// bookings.php
use Modules\Bookings\Controllers\Api\StaffPODController;
use Modules\Bookings\Controllers\Api\VendorPODController;

// admin.php
use Modules\Payment\Controllers\Api\BookingHoldController;
use Modules\Payment\Controllers\Api\FinanceController;
use Modules\KYC\Controllers\Api\AdminKYCController;

// webhooks.php (if exists)
use Modules\Payment\Controllers\Api\RazorpayWebhookController;
```

### 6. **config/auth.php** - Update User Model Reference

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => Modules\Users\Models\User::class,  // Changed from App\Models\User
    ],
],
```

### 7. **database/factories/*.php** - Update Model References

```php
// UserFactory.php
use Modules\Users\Models\User;

// HoardingFactory.php
use Modules\Hoardings\Models\Hoarding;
```

### 8. **database/seeders/*.php** - Update Model References

```php
// DatabaseSeeder.php
use Modules\Users\Models\User;
use Modules\Settings\Models\Setting;
```

### 9. **tests/*.php** - Update Model & Service References

```php
// tests/Feature/Hoardings/HoardingApiTest.php
use Modules\Hoardings\Models\Hoarding;
use Modules\Users\Models\User;
```

---

## 🔄 Cross-Module Dependencies to Update

### Files Referencing Models Across Modules:

#### **PODService.php** (Bookings Module)
```php
// Will need to import:
use Modules\Hoardings\Models\Hoarding;
use Modules\Users\Models\User;
```

#### **CommissionService.php** (Payment Module)
```php
// Will need to import:
use Modules\Bookings\Models\Booking;
use Modules\Users\Models\User;
```

#### **RazorpayPayoutService.php** (Payment Module)
```php
// Will need to import:
use Modules\KYC\Models\VendorKYC;
use Modules\Bookings\Models\Booking;
```

#### **BookingService.php** (Bookings Module)
```php
// Will need to import:
use Modules\Hoardings\Models\Hoarding;
use Modules\Users\Models\User;
use Modules\Quotations\Models\Quotation;
```

#### **OnPaymentCaptured.php** (Payment Listener)
```php
// Will need to import:
use Modules\Bookings\Services\BookingService;
use Modules\Bookings\Models\Booking;
use Modules\Bookings\Jobs\ScheduleBookingConfirmJob;
use Modules\KYC\Models/VendorKYC;
```

---

## 📝 Migration Scripts Needed

### Script 1: Move Files with Git History
```bash
# Bookings Module
git mv app/Models/Booking.php Modules/Bookings/Models/Booking.php
git mv app/Models/BookingProof.php Modules/Bookings/Models/BookingProof.php
# ... repeat for all files

# After moving, update namespaces in each file
```

### Script 2: Update Namespaces
```bash
# Use find and replace across project:
# App\Models\Booking → Modules\Bookings\Models\Booking
# App\Services\PODService → Modules\Bookings\Services\PODService
# etc.
```

### Script 3: Update Imports
```bash
# Find all files importing old namespaces
grep -r "use App\\Models\\" --include="*.php"
grep -r "use App\\Services\\" --include="*.php"
grep -r "use App\\Events\\" --include="*.php"
# Update each occurrence
```

---

## ✅ Validation Checklist

After refactoring, verify:

- [ ] Run `composer dump-autoload`
- [ ] All routes resolve correctly: `php artisan route:list`
- [ ] All tests pass: `php artisan test`
- [ ] No broken imports: Search for `use App\Models\` etc.
- [ ] Event listeners registered: Check `EventServiceProvider` or `AppServiceProvider`
- [ ] Queue jobs work: `php artisan queue:work`
- [ ] Policies registered: Check `AuthServiceProvider` or gates
- [ ] Database relationships work (test with tinker)
- [ ] API responses include correct resource paths
- [ ] Web routes load correct views

---

## 🎯 Benefits After Refactor

1. **Clear Separation of Concerns**: Each module is self-contained
2. **Easier Testing**: Test modules independently
3. **Better Scalability**: Add new modules without touching core
4. **Improved Maintainability**: Find files by feature, not by type
5. **Reusable Modules**: Potentially extract modules to packages
6. **Team Collaboration**: Teams can own specific modules
7. **Follows Laravel Best Practices**: Modular monolith architecture

---

## ⚠️ Potential Issues & Solutions

### Issue 1: Circular Dependencies
**Problem**: Module A depends on Module B, which depends on Module A  
**Solution**: Extract shared logic to a `Common` or `Core` module

### Issue 2: Model Relationships Across Modules
**Problem**: Booking model needs Hoarding model  
**Solution**: Use fully qualified class names in relationships:
```php
public function hoarding()
{
    return $this->belongsTo(\Modules\Hoardings\Models\Hoarding::class);
}
```

### Issue 3: Service Provider Registration
**Problem**: Services not found after moving  
**Solution**: Ensure all service providers are registered in `config/app.php`

### Issue 4: Route Caching
**Problem**: Routes still pointing to old controllers  
**Solution**: Clear route cache: `php artisan route:clear`

---

## 📅 Execution Timeline

### Week 1: Preparation
- Create all module directory structures
- Update composer.json autoloading
- Run `composer dump-autoload`

### Week 2-3: Core Modules
- Migrate Models
- Migrate Services
- Update relationships and imports

### Week 4: Controllers & Routes
- Migrate Controllers (API first, then Web)
- Update all route files
- Test each route manually

### Week 5: Events, Listeners, Jobs
- Migrate Events & Listeners
- Migrate Jobs
- Update event service provider

### Week 6: Testing & Validation
- Run full test suite
- Manual QA testing
- Performance testing
- Documentation updates

---

## 🚀 Recommended Execution Order

1. **Foundational Modules First**:
   - Users (everything depends on this)
   - Settings
   - Hoardings

2. **Business Logic Modules**:
   - Enquiries
   - Offers
   - Quotations
   - Bookings

3. **Supporting Modules**:
   - Payment (depends on Bookings)
   - KYC (depends on Users)
   - Auth

4. **Interface Modules Last**:
   - Admin
   - Vendor
   - Customer
   - Staff

---

## 📚 Additional Resources

- [Laravel Modular Architecture Guide](https://laravel.com/docs/modules)
- [Domain-Driven Design in Laravel](https://laravel-news.com/domain-driven-design-in-laravel)
- [Modular Monolith Pattern](https://www.thoughtworks.com/insights/blog/architecture/monolith-vs-microservices)

---

**Generated**: December 8, 2025  
**Project**: OohApp v3  
**Status**: Ready for Implementation
