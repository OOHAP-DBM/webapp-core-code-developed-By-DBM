# OOHAPP - Project Scaffold Complete ✅

## 📋 Overview

OOHAPP is a **B2B2C Hoarding Marketplace** built on **Laravel 11+** with both server-rendered Blade frontends and JSON API endpoints for SPA/mobile integrations.

### Tech Stack
- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: Blade Templates + Tailwind CSS + Alpine.js
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **Multi-Tenancy**: Stancl Tenancy
- **Media Management**: Spatie Media Library
- **Payment Gateway**: Razorpay (manual capture, holds, route API)
- **Queue**: Database-backed jobs
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel

---

## 🗂️ Project Structure

```
oohApp_Version3/
├── Modules/                         # Domain-driven module structure
│   ├── Auth/                       # Authentication & Authorization
│   ├── Users/                      # User management
│   ├── Hoardings/                  # OOH hoarding catalog
│   ├── DOOH/                       # Digital OOH screens
│   ├── Enquiry/                    # Customer enquiries
│   ├── Offer/                      # Vendor offers
│   ├── Quotation/                  # Finalized quotations (snapshot pricing)
│   ├── Booking/                    # Campaign bookings
│   ├── Payment/                    # Razorpay integration
│   ├── Vendor/                     # Vendor management
│   ├── KYC/                        # KYC verification
│   ├── Staff/                      # Designer, Printer, Mounter, Surveyor
│   ├── Admin/                      # Admin panel features
│   ├── Settings/                   # System configuration
│   ├── Notifications/              # In-app & email notifications
│   ├── Reports/                    # Analytics & exports
│   ├── Media/                      # File uploads
│   └── Search/                     # Global search
│
├── app/
│   ├── Providers/
│   │   └── RepositoryServiceProvider.php   # Repository bindings
│   └── Repositories/
│       ├── Contracts/
│       │   └── BaseRepositoryInterface.php
│       └── BaseRepository.php
│
├── routes/
│   ├── web.php                     # Blade server-rendered routes
│   └── api_v1/                     # API v1 endpoints (JSON)
│       ├── auth.php
│       ├── hoardings.php
│       ├── dooh.php
│       ├── enquiries.php
│       ├── offers.php
│       ├── quotations.php
│       ├── bookings.php
│       ├── payments.php
│       ├── vendors.php
│       ├── kyc.php
│       ├── staff.php
│       ├── admin.php
│       ├── settings.php
│       ├── notifications.php
│       ├── reports.php
│       ├── media.php
│       └── search.php
│
├── resources/views/
│   └── layouts/
│       ├── app.blade.php           # Public layout
│       ├── customer.blade.php      # Customer panel layout
│       ├── vendor.blade.php        # Vendor panel layout
│       ├── admin.blade.php         # Admin panel layout
│       ├── staff.blade.php         # Staff panel layout
│       └── partials/
│           ├── header.blade.php
│           ├── footer.blade.php
│           ├── breadcrumb.blade.php
│           ├── flash-messages.blade.php
│           ├── customer/
│           │   ├── sidebar.blade.php
│           │   └── navbar.blade.php
│           ├── vendor/
│           │   ├── sidebar.blade.php
│           │   └── navbar.blade.php
│           ├── admin/
│           │   ├── sidebar.blade.php
│           │   └── navbar.blade.php
│           └── staff/
│               ├── sidebar.blade.php
│               └── navbar.blade.php
│
├── install-packages.ps1            # PowerShell installation script
├── install-packages.sh             # Bash installation script
└── .env.example                    # Environment configuration template
```

---

## 🚀 Installation

### 1. Clone Repository
```bash
git clone <repository-url>
cd oohApp_Version3
```

### 2. Install Dependencies

#### Option A: PowerShell (Windows)
```powershell
.\install-packages.ps1
```

#### Option B: Bash (Linux/Mac)
```bash
chmod +x install-packages.sh
./install-packages.sh
```

#### Option C: Manual Installation
```bash
# Composer packages
composer require spatie/laravel-permission:^6.0 \
                 spatie/laravel-medialibrary:^11.0 \
                 stancl/tenancy:^3.8 \
                 razorpay/razorpay:^2.9 \
                 guzzlehttp/guzzle:^7.8 \
                 barryvdh/laravel-dompdf:^2.0 \
                 maatwebsite/excel:^3.1

# NPM packages
npm install @tailwindcss/forms @tailwindcss/typography alpinejs axios chart.js
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
# Update .env with your database credentials
DB_DATABASE=oohapp_db
DB_USERNAME=root
DB_PASSWORD=your_password

# Run migrations
php artisan migrate
```

### 5. Publish Package Assets
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
php artisan vendor:publish --tag=tenancy-migrations
php artisan tenancy:install
```

### 6. Seed Database (Optional)
```bash
php artisan db:seed
```

### 7. Build Frontend Assets
```bash
npm run build
```

### 8. Start Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## 🎨 User Panels

### 1. **Customer Panel** (`/customer/*`)
- Browse hoardings/DOOH
- Create enquiries
- Review quotations
- Manage bookings
- Payment history

### 2. **Vendor Panel** (`/vendor/*`)
- Dashboard with analytics
- Manage hoardings/DOOH inventory
- Respond to enquiries
- Create offers & quotations
- Booking management
- Staff assignment
- KYC submission
- Revenue reports

### 3. **Admin Panel** (`/admin/*`)
- System overview dashboard
- User management
- Vendor approval/suspension
- KYC verification
- Hoarding approval
- Payment & payout management
- System settings
- Activity logs
- Reports & analytics

### 4. **Staff Panel** (`/staff/*`)
- View assignments (Designer, Printer, Mounter, Surveyor)
- Accept/complete tasks
- Upload proof of work (POD)
- Track progress

---

## 🔗 API v1 Endpoints

Base URL: `/api/v1`

### Authentication
- `POST /auth/register`
- `POST /auth/login`
- `POST /auth/logout`
- `GET /auth/me`

### Hoardings
- `GET /hoardings` - List all hoardings
- `GET /hoardings/{id}` - Show hoarding details
- `POST /hoardings` - Create (Vendor)
- `PUT /hoardings/{id}` - Update (Vendor)

### Bookings
- `GET /bookings` - List bookings
- `GET /bookings/{id}` - Show booking
- `POST /bookings/{id}/void` - Void booking

### Payments
- `POST /payments/create-order` - Create Razorpay order
- `POST /payments/verify` - Verify payment
- `POST /payments/webhook/razorpay` - Razorpay webhook

*Full API documentation available in each route file under `routes/api_v1/`*

---

## 🔐 Roles & Permissions

### Roles
- **Admin**: Full system access
- **Vendor**: Manage hoardings, respond to enquiries, bookings
- **Customer**: Browse, enquire, book campaigns
- **Staff**: Complete assigned tasks (Designer, Printer, Mounter, Surveyor)

Managed via **Spatie Laravel Permission** package.

---

## 💳 Razorpay Payment Flow

1. **Create Order** (`POST /api/v1/payments/create-order`)
   - Manual capture mode
   - Returns `order_id`, `amount`, `currency`

2. **Payment Authorization**
   - Customer completes payment via Razorpay UI
   - Webhook: `payment.authorized` → Set `payment_hold` status
   - `hold_expiry_at` = now + `BOOKING_HOLD_MINUTES`

3. **Hold Management**
   - Customer can void before expiry
   - Scheduler auto-captures expired holds
   - Fund split via **Route API**

4. **Vendor Payout**
   - If KYC complete → Automatic route transfer
   - If KYC incomplete → Route to admin, mark `pending_manual_payout`

---

## ⚙️ Configuration (.env)

### Key Settings

```env
# Razorpay
RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxx
RAZORPAY_MODE=test

# Booking Settings
BOOKING_HOLD_MINUTES=30
BOOKING_GRACE_PERIOD_DAYS=2
ADMIN_COMMISSION_PERCENTAGE=10
MAX_FUTURE_BOOKING_MONTHS=12
AUTO_APPROVAL_ENABLED=false

# DOOH Settings
DOOH_SLOT_DURATION_SECONDS=10
DOOH_MIN_BOOKING_SLOTS=6

# KYC
KYC_AUTO_APPROVAL=false
KYC_DOCUMENTS_REQUIRED=pan,gst,address_proof,bank_details
```

---

## 📐 Architecture Patterns

### Repository Pattern
All data access goes through repositories:

```php
// Interface
App\Repositories\Contracts\BaseRepositoryInterface

// Implementation
App\Repositories\BaseRepository

// Module-specific (example)
Modules\Hoardings\Repositories\Contracts\HoardingRepositoryInterface
Modules\Hoardings\Repositories\HoardingRepository
```

Bindings registered in `App\Providers\RepositoryServiceProvider`.

### Service Layer
Business logic abstracted into service classes:

```php
Modules\Booking\Services\BookingService
Modules\Payment\Services\RazorpayService
```

### Events & Listeners
Decoupled workflows via events:

```php
Event: EnquiryCreated
Listener: NotifyVendor, LogEnquiryActivity
```

### Queue Jobs
Heavy tasks queued:

```php
Jobs\SendNotificationJob
Jobs\GenerateQuotationPDFJob
Jobs\ProcessVendorPayoutJob
```

---

## 🎯 Next Steps

1. **Generate Module-Specific Code**
   - Migrations, Models, Controllers, Services
   - Follow prompt structure for each module

2. **Implement Core Modules** (Priority Order)
   - Settings & Configuration
   - User Management + Roles
   - Hoarding/DOOH Catalog
   - Enquiry → Quotation → Booking Flow
   - Payment Integration
   - KYC Verification
   - Booking Lifecycle

3. **Testing**
   - Unit tests for repositories & services
   - Feature tests for API endpoints
   - Browser tests for Blade pages (Dusk)

4. **Deployment**
   - Configure production `.env`
   - Setup queue workers
   - Configure scheduler (cron)
   - SSL certificate
   - CDN for media files

---

## 📚 Figma Design References

- **Customer Web**: [Figma Link](https://www.figma.com/design/IVKPt4p1lcnVswR8pUkkMS/)
- **Vendor Web**: [Figma Link](https://www.figma.com/design/pS3dP1ADfV3ZUDNehGWEZ7/)
- **Designer/Printer/Mounter**: [Figma Link](https://www.figma.com/design/GxjpFEw6YYmXgKjJj76csi/)

Use these as UI guides for Blade template implementation.

---

## 🛠️ Artisan Commands Reference

```bash
# Generate module components
php artisan make:migration create_hoardings_table
php artisan make:model Hoarding
php artisan make:controller Api/HoardingController
php artisan make:request StoreHoardingRequest
php artisan make:resource HoardingResource
php artisan make:policy HoardingPolicy

# Queue & Scheduler
php artisan queue:work
php artisan schedule:work

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Testing
php artisan test
```

---

## 📝 License

Proprietary - OOHAPP Project

---

## 👥 Team

LaraCopilot — Expert in Laravel 11+, Blade, API-first design, Spatie packages, Stancl tenancy, and Razorpay integrations.

---

**🎉 Scaffold Complete! Ready for module development.**
