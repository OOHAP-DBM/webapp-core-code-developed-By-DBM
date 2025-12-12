# OohApp - Fresher Developer Onboarding Guide

> **Complete Guide for New Developers**  
> Everything you need to start contributing to the OohApp platform

---

## 📚 Table of Contents

1. [Welcome & Project Overview](#welcome--project-overview)
2. [Prerequisites & Setup](#prerequisites--setup)
3. [Understanding the Codebase](#understanding-the-codebase)
4. [Core Business Logic](#core-business-logic)
5. [Development Workflow](#development-workflow)
6. [Common Tasks Guide](#common-tasks-guide)
7. [Testing Guide](#testing-guide)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)
10. [Learning Resources](#learning-resources)

---

## 👋 Welcome & Project Overview

### What is OohApp?

**OohApp** is an **Outdoor Hoarding Management SaaS Platform** that connects:

- **👥 Customers**: Book outdoor advertising spaces (hoardings/billboards)
- **🏢 Vendors**: List and manage their hoarding inventory
- **⚙️ Admins**: Oversee platform operations, approve vendors, manage finances

### The Complete Booking Journey

```
┌─────────────────────────────────────────────────────────────────┐
│                     CUSTOMER JOURNEY                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Browse Hoardings                                           │
│     ├── Search by city, location, price                       │
│     ├── Filter by size, type                                  │
│     └── View hoarding details & photos                        │
│                                                                 │
│  2. Select Dates                                               │
│     ├── Choose start & end date                               │
│     ├── System checks availability (with grace period)        │
│     └── Calculate total price (with GST)                      │
│                                                                 │
│  3. Create Booking                                             │
│     ├── Booking created with STATUS_PENDING_PAYMENT_HOLD      │
│     ├── 30-minute payment window starts                       │
│     └── Hoarding locked (no one else can book)               │
│                                                                 │
│  4. Make Payment                                               │
│     ├── Razorpay payment gateway                             │
│     ├── Payment captured & verified                          │
│     └── Webhook triggers backend processing                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                   BACKEND PROCESSING                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  5. Commission Split (Automatic)                               │
│     ├── Customer pays: ₹10,000                                │
│     ├── Admin commission (15%): ₹1,500                        │
│     ├── Payment gateway fee (2%): ₹200                        │
│     └── Vendor payout: ₹8,300                                │
│                                                                 │
│  6. KYC Check                                                  │
│     ├── Is vendor KYC verified?                               │
│     │   ├── YES → Auto-transfer ₹8,300 to vendor bank        │
│     │   └── NO  → Hold funds, manual payout later            │
│     └── Record in VendorLedger                               │
│                                                                 │
│  7. Booking Confirmation                                       │
│     ├── Status: CONFIRMED                                     │
│     ├── Notifications sent (email/SMS)                        │
│     └── Hoarding locked for entire duration                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Key Numbers You Should Know

```
Default Settings:
├── Booking hold time: 30 minutes
├── Grace period: 15 minutes
├── Admin commission: 15%
├── Payment gateway fee: 2%
├── Min booking duration: 7 days
├── Max booking duration: 12 months
└── Max future booking: 12 months ahead
```

---

## ⚙️ Prerequisites & Setup

### Required Software

| Software | Version | Purpose |
|----------|---------|---------|
| **PHP** | 8.2+ | Backend language |
| **Composer** | 2.x | PHP dependency manager |
| **MySQL** | 8.0+ | Database |
| **Node.js** | 18+ | Frontend build tools |
| **npm** | 9+ | JavaScript package manager |
| **Git** | Latest | Version control |

### Installation on Windows

#### 1. Install PHP

```powershell
# Download from https://windows.php.net/download/
# Extract to C:\php
# Add to PATH: C:\php

# Verify
php -v
# Should show: PHP 8.2.x
```

#### 2. Install Composer

```powershell
# Download from https://getcomposer.org/download/
# Run installer
# Verify
composer -V
```

#### 3. Install MySQL

```powershell
# Download MySQL Installer from https://dev.mysql.com/downloads/installer/
# Install MySQL Server 8.0
# Set root password during installation
# Verify
mysql --version
```

#### 4. Install Node.js & npm

```powershell
# Download from https://nodejs.org/
# Install LTS version
# Verify
node -v
npm -v
```

### Project Setup

#### Step 1: Clone Repository

```bash
git clone <repository-url>
cd oohApp_Version3
```

#### Step 2: Install Dependencies

```bash
# Install PHP packages (this may take 5-10 minutes)
composer install

# Install JavaScript packages
npm install
```

#### Step 3: Environment Configuration

```bash
# Copy example environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

#### Step 4: Configure `.env` File

Open `.env` in your text editor and update:

```env
# Application
APP_NAME="OohApp"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oohapp_db
DB_USERNAME=root
DB_PASSWORD=your_mysql_password_here

# Razorpay (Test Mode - get from razorpay.com)
RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxx

# Mail (Use Mailtrap for testing - mailtrap.io)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=noreply@oohapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Step 5: Database Setup

```bash
# Create database
mysql -u root -p
# Enter your password
# Then in MySQL prompt:
CREATE DATABASE oohapp_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

#### Step 6: Storage Setup

```bash
# Create storage symlink
php artisan storage:link

# Set permissions (if on Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

#### Step 7: Start Development Servers

Open **3 separate terminal windows**:

**Terminal 1: Laravel Server**
```bash
php artisan serve
# Server running at http://localhost:8000
```

**Terminal 2: Queue Worker**
```bash
php artisan queue:work
# Processes background jobs
```

**Terminal 3: Vite Dev Server**
```bash
npm run dev
# Hot reload for CSS/JS changes
```

### Verify Installation

Visit `http://localhost:8000` - you should see the OohApp homepage.

**Test Login**:
```
Admin:
Email: admin@oohapp.com
Password: password123

Vendor:
Email: vendor@example.com
Password: password123

Customer:
Email: customer@example.com
Password: password123
```

---

## 📂 Understanding the Codebase

### Project Structure Overview

```
oohApp_Version3/
├── 📁 app/                      # Core application code
│   ├── Console/                 # Artisan commands
│   ├── Events/                  # Event classes
│   ├── Http/
│   │   ├── Controllers/         # Request handlers
│   │   │   ├── Api/             # API endpoints
│   │   │   └── Web/             # Web pages
│   │   ├── Middleware/          # HTTP filters
│   │   └── Requests/            # Form validation
│   ├── Jobs/                    # Queue jobs
│   ├── Listeners/               # Event handlers
│   ├── Models/                  # Database models
│   ├── Notifications/           # Email/SMS templates
│   └── Services/                # Business logic
│
├── 📁 Modules/                   # Feature modules
│   ├── Auth/                    # Login, registration
│   ├── Bookings/                # Booking management
│   ├── Hoardings/               # Hoarding inventory
│   ├── KYC/                     # Vendor verification
│   └── Settings/                # App configuration
│
├── 📁 database/
│   ├── migrations/              # Database schema
│   └── seeders/                 # Sample data
│
├── 📁 resources/
│   ├── css/                     # Stylesheets
│   ├── js/                      # JavaScript
│   └── views/                   # HTML templates
│       ├── admin/               # Admin panel
│       ├── customer/            # Customer dashboard
│       ├── vendor/              # Vendor dashboard
│       └── layouts/             # Shared layouts
│
├── 📁 routes/
│   ├── api.php                  # API routes
│   ├── web.php                  # Web routes
│   └── api_v1/                  # Versioned API
│       ├── admin.php
│       ├── bookings.php
│       ├── customers.php
│       └── vendors.php
│
├── 📁 public/                    # Public files
│   └── index.php                # Entry point
│
└── 📁 storage/                   # App storage
    ├── app/                     # Uploaded files
    └── logs/                    # Log files
```

### Important Files to Know

| File | Purpose |
|------|---------|
| `routes/web.php` | Web page URLs |
| `routes/api_v1/*.php` | API endpoints |
| `app/Models/*.php` | Database models |
| `app/Services/*.php` | Business logic |
| `resources/views/**/*.blade.php` | HTML templates |
| `database/migrations/*.php` | Database structure |
| `.env` | Environment config |

---

## 🎯 Core Business Logic

### 1. Booking Creation Flow

**File**: `Modules/Bookings/Services/DirectBookingService.php`

```php
// When customer creates booking
public function createDirectBooking(array $data): Booking
{
    // 1. Validate hoarding exists
    $hoarding = Hoarding::find($data['hoarding_id']);
    
    // 2. Check dates are valid
    if ($startDate < today()) {
        throw new Exception('Cannot book in the past');
    }
    
    // 3. Check availability (includes grace period)
    $this->validateAvailability($hoarding->id, $startDate, $endDate);
    
    // 4. Calculate pricing
    $pricing = $this->calculatePricing($hoarding, $startDate, $endDate);
    
    // 5. Create booking with hold
    $booking = Booking::create([
        'status' => STATUS_PENDING_PAYMENT_HOLD,
        'hold_expiry_at' => now()->addMinutes(30),
        'total_amount' => $pricing['total']
    ]);
    
    return $booking;
}
```

**Key Validations**:
- ✅ Start date must be in future
- ✅ End date must be after start date
- ✅ Duration: min 7 days, max 12 months
- ✅ No overlapping bookings (including grace period)
- ✅ Hoarding must be active

### 2. Payment Processing

**File**: `app/Listeners/OnPaymentCaptured.php`

```php
// When Razorpay payment succeeds
public function handle(PaymentCaptured $event): void
{
    // 1. Find booking
    $booking = Booking::findByRazorpayOrderId($event->orderId);
    
    // 2. Calculate commission
    $commission = CommissionService::calculateAndRecord($booking);
    // Creates BookingPayment record with split:
    // - gross_amount: 10000
    // - admin_commission: 1500
    // - vendor_payout: 8300
    // - pg_fee: 200
    
    // 3. Record in ledger
    VendorLedger::recordTransaction([
        'type' => 'commission_deduction',
        'amount' => -1500
    ]);
    VendorLedger::recordTransaction([
        'type' => 'booking_earning',
        'amount' => +8300,
        'is_on_hold' => !$vendor->kyc_verified
    ]);
    
    // 4. Route funds
    if ($vendor->kyc_verified) {
        RazorpayPayoutService::transferToVendor($vendor, 8300);
    } else {
        BookingPayment::markAsPendingManualPayout();
    }
    
    // 5. Confirm booking
    $booking->update(['status' => STATUS_CONFIRMED]);
}
```

### 3. Commission Calculation

**File**: `app/Services/CommissionService.php`

```php
public function calculateAndRecord(Booking $booking): array
{
    $gross = 10000;  // Customer payment
    
    // Get commission rate (from rules or default 15%)
    $commissionRate = 15;
    $adminCommission = $gross * 0.15;  // 1500
    
    // Get PG fee (default 2%)
    $pgFee = $gross * 0.02;  // 200
    
    // Calculate vendor payout
    $vendorPayout = $gross - $adminCommission - $pgFee;  // 8300
    
    // Save to database
    BookingPayment::create([
        'gross_amount' => 10000,
        'admin_commission_amount' => 1500,
        'vendor_payout_amount' => 8300,
        'pg_fee_amount' => 200
    ]);
}
```

### 4. Grace Period Logic

**File**: `Modules/Bookings/Services/DirectBookingService.php`

```php
// Grace period prevents immediate back-to-back bookings
protected function validateAvailability($hoardingId, $startDate, $endDate)
{
    $gracePeriod = 15; // minutes
    
    // Expand date range by grace period
    $adjustedStart = $startDate->subMinutes(15);
    $adjustedEnd = $endDate->addMinutes(15);
    
    // Check if any booking conflicts with adjusted range
    $conflict = Booking::where('hoarding_id', $hoardingId)
        ->whereBetween('start_date', [$adjustedStart, $adjustedEnd])
        ->orWhereBetween('end_date', [$adjustedStart, $adjustedEnd])
        ->exists();
    
    if ($conflict) {
        throw new Exception('Not available');
    }
}
```

**Example**:
```
Booking A: Jan 10 10:00 - Jan 20 10:00
Grace Period: Jan 20 10:00 - Jan 20 10:15
Booking B: Earliest start = Jan 20 10:15
```

### 5. Vendor Payout Routing

**File**: `app/Services/PaymentSettlementService.php`

```php
public function recordPaymentInLedger(BookingPayment $payment)
{
    $vendor = $payment->booking->vendor;
    $kyc = $vendor->vendorKYC;
    
    // Check KYC status
    $isVerified = $kyc 
        && $kyc->verification_status == 'approved'
        && $kyc->payout_status == 'verified'
        && $kyc->razorpay_subaccount_id != null;
    
    // Record earning
    VendorLedger::create([
        'amount' => $payment->vendor_payout_amount,
        'is_on_hold' => !$isVerified,  // Hold if not verified
        'metadata' => [
            'reason' => $isVerified ? null : 'KYC incomplete'
        ]
    ]);
    
    // If verified, auto-transfer
    if ($isVerified) {
        RazorpayPayoutService::createTransfer(
            $payment->razorpay_payment_id,
            $kyc->razorpay_subaccount_id,
            $payment->vendor_payout_amount
        );
    }
}
```

---

## 🔄 Development Workflow

### Daily Workflow

```bash
# 1. Start your day
git checkout main
git pull origin main

# 2. Create feature branch
git checkout -b feature/add-booking-filters

# 3. Make changes
# ... code ...

# 4. Test your changes
php artisan test

# 5. Commit
git add .
git commit -m "feat: Add booking date filters"

# 6. Push
git push origin feature/add-booking-filters

# 7. Create Pull Request on GitHub/GitLab
```

### Git Commands Cheat Sheet

```bash
# Check status
git status

# Create branch
git checkout -b feature/my-feature

# Add files
git add .
git add specific-file.php

# Commit
git commit -m "feat: Add new feature"

# Push
git push origin feature/my-feature

# Pull latest
git pull origin main

# Switch branch
git checkout main

# View history
git log --oneline

# Undo last commit (keep changes)
git reset --soft HEAD~1

# Discard local changes
git checkout -- filename.php
```

### Commit Message Format

```
type: Short description

Types:
- feat:     New feature
- fix:      Bug fix
- refactor: Code refactoring
- docs:     Documentation
- test:     Add tests
- style:    Formatting

Examples:
feat: Add booking calendar view
fix: Resolve payment calculation error
refactor: Optimize database queries
docs: Update API documentation
```

---

## 🛠️ Common Tasks Guide

### Task 1: Add New Web Page

**Example**: Add "My Profile" page

#### Step 1: Create Route

**File**: `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/customer/profile', [ProfileController::class, 'show'])
        ->name('customer.profile');
});
```

#### Step 2: Create Controller

```bash
php artisan make:controller Customer/ProfileController
```

**File**: `app/Http/Controllers/Customer/ProfileController.php`

```php
<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        return view('customer.profile', [
            'user' => $user
        ]);
    }
}
```

#### Step 3: Create View

**File**: `resources/views/customer/profile.blade.php`

```blade
@extends('layouts.customer')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <h1>My Profile</h1>
    
    <div class="card">
        <div class="card-body">
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Phone:</strong> {{ $user->phone }}</p>
        </div>
    </div>
</div>
@endsection
```

#### Step 4: Test

Visit: `http://localhost:8000/customer/profile`

### Task 2: Add API Endpoint

**Example**: Get customer bookings

#### Step 1: Create Route

**File**: `routes/api_v1/customers.php`

```php
Route::get('/bookings', [CustomerBookingController::class, 'index']);
```

#### Step 2: Create Controller

```bash
php artisan make:controller Api/Customer/CustomerBookingController
```

**File**: `app/Http/Controllers/Api/Customer/CustomerBookingController.php`

```php
<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CustomerBookingController extends Controller
{
    public function index(): JsonResponse
    {
        $bookings = auth()->user()
            ->bookings()
            ->with(['hoarding', 'vendor'])
            ->latest()
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }
}
```

#### Step 3: Test with Postman

```
GET http://localhost:8000/api/v1/customer/bookings
Headers:
  Authorization: Bearer {your_token}
```

### Task 3: Create Database Migration

**Example**: Add "rating" column to bookings

```bash
# Generate migration
php artisan make:migration add_rating_to_bookings_table
```

**File**: `database/migrations/YYYY_MM_DD_HHMMSS_add_rating_to_bookings_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->integer('rating')->nullable()->after('status');
            $table->text('review')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['rating', 'review']);
        });
    }
};
```

```bash
# Run migration
php artisan migrate

# Rollback if needed
php artisan migrate:rollback
```

### Task 4: Add New Setting

**Example**: Add "booking_cancellation_fee"

**File**: `database/seeders/SettingsSeeder.php`

```php
Setting::updateOrCreate(
    ['key' => 'booking_cancellation_fee'],
    [
        'value' => '10',
        'type' => 'integer',
        'group' => 'booking',
        'description' => 'Cancellation fee percentage',
    ]
);
```

```bash
# Run seeder
php artisan db:seed --class=SettingsSeeder
```

**Usage in code**:

```php
use Modules\Settings\Services\SettingsService;

$feePercent = app(SettingsService::class)->get('booking_cancellation_fee', 10);
$cancellationFee = $bookingAmount * ($feePercent / 100);
```

### Task 5: Debug a Bug

**Example**: Payment not being captured

#### Step 1: Check Logs

```bash
# View latest logs
tail -f storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log
```

#### Step 2: Add Debug Logging

```php
use Illuminate\Support\Facades\Log;

Log::info('Payment processing started', [
    'order_id' => $orderId,
    'payment_id' => $paymentId
]);

Log::error('Payment failed', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

#### Step 3: Use Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

Visit page - you'll see debug toolbar with queries, logs, etc.

#### Step 4: Use dd() for Quick Debug

```php
// Dump and die - shows variable and stops execution
dd($variable);

// Dump - shows variable but continues
dump($variable);
```

---

## 🧪 Testing Guide

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BookingTest.php

# Run specific test method
php artisan test --filter testCustomerCanCreateBooking

# Run with code coverage
php artisan test --coverage
```

### Writing Your First Test

**Example**: Test booking creation

```bash
# Generate test
php artisan make:test BookingTest
```

**File**: `tests/Feature/BookingTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Hoardings\Models\Hoarding;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTest extends TestCase
{
    use RefreshDatabase;  // Reset database after each test
    
    public function test_customer_can_create_booking()
    {
        // 1. Arrange: Set up test data
        $customer = User::factory()->create(['role' => 'customer']);
        $hoarding = Hoarding::factory()->create(['status' => 'active']);
        
        // 2. Act: Perform action
        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/bookings/direct', [
                'hoarding_id' => $hoarding->id,
                'start_date' => '2025-02-01',
                'end_date' => '2025-02-10',
            ]);
        
        // 3. Assert: Check results
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'status', 'total_amount']
        ]);
        
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id,
            'hoarding_id' => $hoarding->id
        ]);
    }
    
    public function test_cannot_book_unavailable_hoarding()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $hoarding = Hoarding::factory()->create(['status' => 'inactive']);
        
        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/bookings/direct', [
                'hoarding_id' => $hoarding->id,
                'start_date' => '2025-02-01',
                'end_date' => '2025-02-10',
            ]);
        
        $response->assertStatus(422);
    }
}
```

### Test Database Setup

Create test environment:

**File**: `.env.testing`

```env
DB_CONNECTION=mysql
DB_DATABASE=oohapp_test
```

```bash
# Create test database
mysql -u root -p
CREATE DATABASE oohapp_test;
EXIT;
```

---

## 🐛 Troubleshooting

### Common Issues & Solutions

#### Issue 1: "Class not found"

**Error**: `Class 'App\Services\BookingService' not found`

**Solution**:
```bash
# Regenerate autoload files
composer dump-autoload
```

#### Issue 2: Migration Fails

**Error**: `SQLSTATE[42S01]: Base table or view already exists`

**Solution**:
```bash
# Option 1: Drop all tables and re-migrate
php artisan migrate:fresh

# Option 2: Rollback specific migration
php artisan migrate:rollback --step=1
```

#### Issue 3: 500 Error on Page

**Solution**:
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check error in logs
tail -f storage/logs/laravel.log
```

#### Issue 4: Payment Not Working

**Checklist**:
- ✅ Check `.env` has correct Razorpay keys
- ✅ Check Razorpay is in test mode
- ✅ Queue worker is running (`php artisan queue:work`)
- ✅ Check webhook URL in Razorpay dashboard

#### Issue 5: Queue Jobs Not Processing

**Solution**:
```bash
# Restart queue worker
# Press Ctrl+C to stop, then:
php artisan queue:work

# Clear failed jobs
php artisan queue:flush

# Check failed jobs table
php artisan queue:failed
```

#### Issue 6: CSS/JS Changes Not Showing

**Solution**:
```bash
# Rebuild assets
npm run build

# Or use dev mode (auto-rebuild)
npm run dev

# Clear browser cache (Ctrl+Shift+R)
```

---

## ✅ Best Practices

### 1. Always Use Services for Business Logic

```php
// ❌ Bad: Logic in controller
public function store(Request $request)
{
    $commission = $booking->total * 0.15;
    $payout = $booking->total - $commission;
    BookingPayment::create([...]);
}

// ✅ Good: Logic in service
public function store(Request $request)
{
    return $this->bookingService->create($request->validated());
}
```

### 2. Validate User Input

```php
// ❌ Bad: No validation
public function update(Request $request, $id)
{
    Booking::find($id)->update($request->all());
}

// ✅ Good: Use Form Request
public function update(UpdateBookingRequest $request, $id)
{
    Booking::find($id)->update($request->validated());
}
```

### 3. Use Eloquent Relationships

```php
// ❌ Bad: Manual joins
$bookings = DB::table('bookings')
    ->join('customers', 'bookings.customer_id', '=', 'customers.id')
    ->get();

// ✅ Good: Eloquent relationships
$bookings = Booking::with('customer')->get();
foreach ($bookings as $booking) {
    echo $booking->customer->name;
}
```

### 4. Prevent N+1 Queries

```php
// ❌ Bad: N+1 problem
$bookings = Booking::all();  // 1 query
foreach ($bookings as $booking) {
    echo $booking->customer->name;  // N queries (one per booking)
}

// ✅ Good: Eager loading
$bookings = Booking::with('customer')->get();  // 2 queries total
foreach ($bookings as $booking) {
    echo $booking->customer->name;  // No extra queries
}
```

### 5. Use Database Transactions

```php
// ❌ Bad: No transaction
$booking = Booking::create($data);
BookingPayment::create(['booking_id' => $booking->id]);

// ✅ Good: Wrapped in transaction
DB::transaction(function () use ($data) {
    $booking = Booking::create($data);
    BookingPayment::create(['booking_id' => $booking->id]);
});
```

### 6. Handle Errors Properly

```php
// ❌ Bad: No error handling
public function create($data)
{
    return Booking::create($data);
}

// ✅ Good: Try-catch with logging
public function create($data)
{
    try {
        return Booking::create($data);
    } catch (\Exception $e) {
        Log::error('Booking creation failed', [
            'error' => $e->getMessage(),
            'data' => $data
        ]);
        throw new BookingException('Could not create booking');
    }
}
```

### 7. Use Environment Variables for Config

```php
// ❌ Bad: Hardcoded values
$apiKey = 'rzp_test_12345';
$commission = 15;

// ✅ Good: Use config/env
$apiKey = config('services.razorpay.key');
$commission = config('app.commission_rate');
```

### 8. Write Descriptive Variable Names

```php
// ❌ Bad: Unclear names
$a = 10000;
$b = $a * 0.15;
$c = $a - $b;

// ✅ Good: Clear names
$grossAmount = 10000;
$adminCommission = $grossAmount * 0.15;
$vendorPayout = $grossAmount - $adminCommission;
```

---

## 📚 Learning Resources

### Laravel Resources

- **Official Docs**: https://laravel.com/docs/11.x
- **Laracasts** (Video Tutorials): https://laracasts.com
- **Laravel Daily** (Tips): https://laraveldaily.com
- **Laravel News**: https://laravel-news.com

### PHP Resources

- **PHP Manual**: https://www.php.net/manual/
- **PSR Standards**: https://www.php-fig.org/psr/

### Git Resources

- **Git Cheat Sheet**: https://education.github.com/git-cheat-sheet-education.pdf
- **Learn Git Branching**: https://learngitbranching.js.org/

### YouTube Channels

- **Traversy Media** - Laravel tutorials
- **CodeWithDary** - Laravel projects
- **The Net Ninja** - Web development

### Practice Platforms

- **LeetCode** - Coding challenges
- **HackerRank** - PHP challenges
- **Laracasts** - Laravel exercises

---

## 🎓 Learning Path (4 Weeks)

### Week 1: Setup & Basics

**Goals**:
- [ ] Complete environment setup
- [ ] Run the project successfully
- [ ] Login as admin, vendor, customer
- [ ] Create a test booking end-to-end
- [ ] Explore admin panel

**Tasks**:
1. Install all software
2. Clone and setup project
3. Read `PROJECT_SCAFFOLD.md`
4. Explore directory structure
5. Make a simple change (e.g., change homepage text)

### Week 2: Understanding Features

**Goals**:
- [ ] Understand booking flow
- [ ] Understand payment system
- [ ] Understand KYC process
- [ ] Read key service files

**Tasks**:
1. Read `DirectBookingService.php` line by line
2. Read `CommissionService.php`
3. Read `OnPaymentCaptured.php`
4. Create a booking and trace code execution
5. Check database tables after booking

### Week 3: Making Changes

**Goals**:
- [ ] Add new web page
- [ ] Create API endpoint
- [ ] Write database migration
- [ ] Add new setting

**Tasks**:
1. Add "My Bookings" page for customer
2. Create API to get booking details
3. Add "notes" column to bookings table
4. Write test for booking creation

### Week 4: Real Work

**Goals**:
- [ ] Fix a real bug
- [ ] Add a small feature
- [ ] Write tests
- [ ] Submit pull request

**Tasks**:
1. Pick issue from tracker
2. Create feature branch
3. Implement fix/feature
4. Write tests
5. Submit PR for review

---

## 🤝 Getting Help

### When You're Stuck

1. **Check Documentation**: Read this guide, Laravel docs
2. **Search Codebase**: Look for similar implementations
3. **Check Logs**: `storage/logs/laravel.log`
4. **Google Error**: Search exact error message
5. **Ask Team**: Post in team chat with:
   - What you're trying to do
   - What you've tried
   - Exact error message
   - Screenshots if applicable

### Debugging Checklist

- [ ] Clear all caches (`php artisan cache:clear`, etc.)
- [ ] Restart queue worker
- [ ] Check `.env` configuration
- [ ] Check database connection
- [ ] Read error logs
- [ ] Add debug logging
- [ ] Use `dd()` to inspect variables

---

## 📝 Quick Reference

### Laravel Artisan Commands

```bash
# Server
php artisan serve                    # Start dev server
php artisan queue:work              # Start queue worker

# Database
php artisan migrate                 # Run migrations
php artisan migrate:fresh           # Drop all tables & re-migrate
php artisan db:seed                 # Run seeders

# Cache
php artisan cache:clear             # Clear application cache
php artisan config:clear            # Clear config cache
php artisan route:clear             # Clear route cache
php artisan view:clear              # Clear view cache

# Generate
php artisan make:controller Name    # Create controller
php artisan make:model Name -m      # Create model with migration
php artisan make:migration name     # Create migration
php artisan make:seeder Name        # Create seeder
php artisan make:test Name          # Create test

# Queue
php artisan queue:work              # Process jobs
php artisan queue:failed            # List failed jobs
php artisan queue:retry all         # Retry failed jobs
php artisan queue:flush             # Clear failed jobs

# Other
php artisan tinker                  # Interactive shell
php artisan route:list              # List all routes
php artisan storage:link            # Create storage symlink
```

### Useful Code Snippets

#### Get Authenticated User

```php
$user = auth()->user();
$userId = auth()->id();
```

#### Query Database

```php
// Get all
$bookings = Booking::all();

// Get with condition
$bookings = Booking::where('status', 'confirmed')->get();

// Get one
$booking = Booking::find($id);
$booking = Booking::where('id', $id)->first();

// Create
$booking = Booking::create([...]);

// Update
$booking->update([...]);

// Delete
$booking->delete();
```

#### Relationships

```php
// One-to-many
$user->bookings  // Get all bookings for user
$booking->customer  // Get customer of booking

// With eager loading
$bookings = Booking::with('customer', 'hoarding')->get();
```

#### JSON Response

```php
return response()->json([
    'success' => true,
    'message' => 'Success',
    'data' => $data
]);
```

#### Redirect

```php
return redirect()->route('customer.dashboard');
return redirect()->back()->with('success', 'Saved!');
```

---

**Welcome aboard! You've got this! 🚀**

Need help? Don't hesitate to ask the team.

*Happy Coding!*

---

*Last Updated: December 12, 2025*
*Version: 1.0*
