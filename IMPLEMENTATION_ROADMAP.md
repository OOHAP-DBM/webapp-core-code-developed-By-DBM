# OohApp Implementation Roadmap

## Overview
This document tracks the placeholder controllers that need implementation. All routes for these controllers have been commented out in the route files with TODO markers.

## ✅ Currently Working Controllers

### Authentication (Modules/Auth)
- ✅ LoginController (Web)
- ✅ RegisterController (Web)
- ✅ OTPController (Web)
- ✅ AuthController (API)

### Hoardings (Modules/Hoardings)
- ✅ HoardingController (Web) - `Modules\Vendor\Controllers\Web\HoardingController`
- ✅ HoardingController (API) - `Modules\Hoardings\Controllers\Api\HoardingController`

### Settings (Modules/Settings & Modules/Admin)
- ✅ SettingsController (API) - `Modules\Settings\Controllers\Api\SettingsController`
- ✅ SettingController (Web) - `Modules\Admin\Controllers\Web\SettingController`

### Payment & Finance (Modules/Payment)
- ✅ RazorpayWebhookController (API)
- ✅ BookingHoldController (API)
- ✅ FinanceController (API)

### KYC (Modules/KYC)
- ✅ VendorKYCController (API) - `Modules\KYC\Controllers\Api\VendorKYCController`
- ✅ AdminKYCController (API) - `Modules\KYC\Controllers\Api\AdminKYCController`
- ✅ VendorKYCWebController (Web) - `App\Http\Controllers\Web\Vendor\VendorKYCWebController`

### Bookings (Modules/Bookings)
- ✅ BookingController (API) - `Modules\Bookings\Controllers\Api\BookingController`
- ✅ StaffPODController (API) - `Modules\Bookings\Controllers\Api\StaffPODController`
- ✅ VendorPODController (API) - `Modules\Bookings\Controllers\Api\VendorPODController`

### Enquiries (Modules/Enquiries)
- ✅ EnquiryController (API) - `Modules\Enquiries\Controllers\Api\EnquiryController`

### Offers (Modules/Offers)
- ✅ OfferController (API) - `Modules\Offers\Controllers\Api\OfferController`

### Quotations (Modules/Quotations)
- ✅ QuotationController (API) - `Modules\Quotations\Controllers\Api\QuotationController`

---

## ❌ Placeholder Controllers (Need Implementation)

### Priority 1: Core Business Features

#### Customer Portal (Web)
**Location**: `App\Http\Controllers\Web\Customer\`  
**Route File**: `routes/web.php` (lines 53-81 commented)

- ❌ DashboardController
- ❌ EnquiryController
- ❌ OfferController
- ❌ QuotationController
- ❌ BookingController
- ❌ PaymentController
- ❌ ProfileController

**Functionality Needed**:
- Customer dashboard with booking overview, enquiry stats
- Customer-facing enquiry submission and tracking
- View received offers and quotations
- Booking history and status tracking
- Payment history and receipts
- Profile management

---

#### Vendor Dashboard (Web)
**Location**: `App\Http\Controllers\Web\Vendor\`  
**Route File**: `routes/web.php` (lines 88-138 commented)

- ❌ DashboardController
- ❌ EnquiryController (vendor view)
- ❌ OfferController (vendor management)
- ❌ QuotationController (vendor management)
- ❌ BookingController (vendor management)
- ❌ ReportController
- ❌ ProfileController

**Functionality Needed**:
- Vendor dashboard with revenue stats, active bookings
- Manage incoming enquiries and respond
- Create and manage offers
- Create and manage quotations
- View and manage bookings with POD upload
- Revenue and performance reports
- Vendor profile and settings

**Note**: HoardingController (web resource) already exists at `Modules\Vendor\Controllers\Web\HoardingController`

---

#### Admin Panel (Web)
**Location**: `App\Http\Controllers\Web\Admin\`  
**Route File**: `routes/web.php` (lines 157-203 commented)

- ❌ DashboardController
- ❌ UserController
- ❌ VendorController
- ❌ AdminKYCWebController
- ❌ AdminKYCReviewController
- ❌ HoardingController (admin approval)
- ❌ BookingController (admin oversight)
- ❌ PaymentController (admin management)
- ❌ ReportController
- ❌ ActivityLogController

**Functionality Needed**:
- Admin dashboard with system-wide stats
- User management (CRUD for all user types)
- Vendor approval, suspension, management
- KYC verification interface
- Hoarding approval/rejection workflow
- Booking oversight and manual captures
- Payment processing and payout management
- Comprehensive reports (revenue, vendors, bookings)
- System activity audit log

**Note**: Settings already working via `Modules\Admin\Controllers\Web\SettingController`  
**Note**: Finance routes working via `Modules\Payment\Controllers\Api\FinanceController`

---

### Priority 2: Staff Management

#### Staff Panel (Web)
**Location**: `App\Http\Controllers\Web\Staff\`  
**Route File**: `routes/web.php` (lines 207-223 commented)

- ❌ DashboardController
- ❌ AssignmentController
- ❌ ProfileController

**Functionality Needed**:
- Staff dashboard with assigned tasks
- View and manage assignments (Designer, Printer, Mounter, Surveyor)
- Accept/complete assignments
- Upload proof of work (designs, printing, mounting photos)
- Staff profile management

#### Staff Management (API)
**Location**: `Modules\Staff\Controllers\Api\`  
**Route File**: `routes/api_v1/staff.php` (entirely commented)

- ❌ StaffController (staff CRUD and assignment)
- Staff routes commented for: staff, vendor, admin namespaces

**Functionality Needed**:
- CRUD for staff members
- Assign staff to bookings
- Track staff performance and completion rates

---

### Priority 3: Supporting Features

#### Vendor Module (API)
**Location**: `Modules\Vendor\Controllers\Api\`  
**Route File**: `routes/api_v1/vendors.php` (VendorController routes commented)

- ❌ VendorController (vendor profile, stats, etc.)

**Functionality Needed**:
- Vendor profile API endpoints
- Vendor statistics and analytics
- Vendor subaccount management

**Note**: VendorKYCController already working in `Modules\KYC\Controllers\Api\VendorKYCController`

---

#### Admin Module (API)
**Location**: `Modules\Admin\Controllers\Api\`  
**Route File**: `routes/api_v1/admin.php` (AdminController routes commented)

- ❌ AdminController (admin dashboard stats, etc.)

**Functionality Needed**:
- Admin dashboard API (stats, metrics)
- System health checks
- Admin-specific data aggregations

**Note**: FinanceController and AdminKYCController already working

---

#### Generic KYC Controller (API)
**Location**: `Modules\KYC\Controllers\Api\`  
**Route File**: `routes/api_v1/kyc.php` (generic KYCController commented)

- ❌ KYCController (generic, if needed)

**Note**: Specific VendorKYCController and AdminKYCController already working. Generic controller may not be needed.

---

### Priority 4: Nice-to-Have Features

#### Notifications (API)
**Location**: `Modules\Notifications\Controllers\Api\`  
**Route File**: `routes/api_v1/notifications.php` (entirely commented)

- ❌ NotificationController

**Functionality Needed**:
- User notifications (in-app)
- Mark as read/unread
- Notification preferences
- Push notifications integration

---

#### Reports (API)
**Location**: `Modules\Reports\Controllers\Api\`  
**Route File**: `routes/api_v1/reports.php` (entirely commented)

- ❌ ReportController

**Functionality Needed**:
- Generate various reports (bookings, revenue, vendor performance)
- Export reports (PDF, Excel)
- Custom date range reports
- Scheduled reports

---

#### Search (API)
**Location**: `Modules\Search\Controllers\Api\`  
**Route File**: `routes/api_v1/search.php` (entirely commented)

- ❌ SearchController

**Functionality Needed**:
- Global search across entities (hoardings, bookings, vendors, customers)
- Advanced filtering
- Search suggestions and autocomplete

---

#### Media Management (API)
**Location**: `Modules\Media\Controllers\Api\`  
**Route File**: `routes/api_v1/media.php` (entirely commented)

- ❌ MediaController

**Functionality Needed**:
- Upload and manage media files
- Image optimization and processing
- Media library browsing
- Delete unused media

---

#### Home & Search (Web)
**Location**: `App\Http\Controllers\Web\`  
**Route File**: `routes/web.php` (commented)

- ❌ HomeController (public homepage)
- ❌ SearchController (public search)

**Functionality Needed**:
- Public homepage with featured hoardings
- Public search for hoardings by location, size, price
- Hoarding detail view (already working via `Modules\Hoardings\Controllers\Web\HoardingController`)

---

## Implementation Sequence Recommendation

### Phase 1: Essential Business Operations (2-3 weeks)
1. **Customer Portal Web Controllers** - Critical for customer-facing features
   - DashboardController
   - EnquiryController
   - BookingController
   - ProfileController

2. **Vendor Dashboard Web Controllers** - Critical for vendor operations
   - DashboardController
   - EnquiryController
   - OfferController
   - QuotationController
   - BookingController

### Phase 2: Admin Management (2 weeks)
3. **Admin Panel Web Controllers** - Critical for system administration
   - DashboardController
   - UserController
   - VendorController
   - HoardingController
   - BookingController

### Phase 3: Staff & Workflow (1 week)
4. **Staff Management** - Important for operational workflow
   - StaffController (API)
   - Staff Panel Web Controllers

### Phase 4: Supporting Features (1-2 weeks)
5. **Notifications** - Improve user engagement
6. **Reports** - Business intelligence
7. **Public Pages** - HomeController, SearchController

### Phase 5: Enhancements (1 week)
8. **Media Management** - Better asset organization
9. **Advanced Search** - Enhanced user experience

---

## Technical Notes

### Namespace Conventions
- **Web Controllers**: `App\Http\Controllers\Web\{Role}\{Controller}`
- **API Controllers**: `Modules\{Module}\Controllers\Api\{Controller}`
- **Models**: `Modules\{Module}\Models\{Model}`
- **Services**: `Modules\{Module}\Services\{Service}`

### Dependencies
Most placeholder controllers will need:
- Authentication via Laravel Sanctum (already set up)
- Role-based authorization via Spatie Permission (already set up)
- Existing models from Modules (User, Hoarding, Booking, etc.)
- Notification system (Laravel Notifications)
- Job queues for background tasks (already configured)

### Testing Approach
Each controller should include:
- Feature tests for all endpoints
- Authorization tests (correct role access)
- Validation tests (request validation)
- Integration tests with existing modules

---

## Route Summary

**Total Routes**: 111 working routes (as of this commit)

**Breakdown**:
- ✅ API v1 Routes: ~95 routes (Auth, Hoardings, Bookings, Enquiries, Offers, Quotations, KYC, Payment, Settings)
- ✅ Web Routes: ~16 routes (Auth, Hoardings public, Vendor hoardings CRUD, Vendor KYC, Admin settings, Admin finance)

**Commented (Placeholder)**:
- ❌ API Routes: ~50+ routes across 9 route files
- ❌ Web Routes: ~80+ routes across Customer, Vendor, Admin, Staff panels

---

## Files Reference

### Route Files with TODOs
1. `routes/api_v1/vendors.php` - VendorController routes commented
2. `routes/api_v1/admin.php` - AdminController routes commented
3. `routes/api_v1/kyc.php` - Generic KYCController routes commented
4. `routes/api_v1/staff.php` - All StaffController routes commented
5. `routes/api_v1/notifications.php` - All NotificationController routes commented
6. `routes/api_v1/reports.php` - All ReportController routes commented
7. `routes/api_v1/search.php` - All SearchController routes commented
8. `routes/api_v1/media.php` - All MediaController routes commented
9. `routes/web.php` - Customer, Vendor (most), Admin (most), Staff panels commented

### Controllers with Namespace Fixes Applied
- `Modules/KYC/Controllers/Api/VendorKYCController.php` ✅
- `Modules/KYC/Controllers/Api/AdminKYCController.php` ✅

### Known Issues (False Positives)
The following are IDE type inference issues, NOT actual errors:
- `hasRole()` method showing as undefined (Spatie Permission trait - works at runtime)
- `auth()->id()` and `auth()->user()` showing type errors (Laravel helper - works at runtime)
- Spatie MediaLibrary traits showing as undefined (package installed - works at runtime)

---

## Next Steps
1. Choose priority controllers from Phase 1
2. Create controller stubs with proper namespaces
3. Implement business logic
4. Uncomment corresponding routes
5. Write tests
6. Repeat for next phase

---

**Last Updated**: Current commit (9e68ad4)  
**Status**: Modular refactor complete, system operational with core features working
