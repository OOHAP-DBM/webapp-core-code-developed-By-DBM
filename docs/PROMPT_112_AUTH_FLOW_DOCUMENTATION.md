# PROMPT 112: Login & Signup Flow (Role-Based, Figma-Aligned)

## Overview

Complete implementation of role-based authentication and vendor onboarding system for OohApp with customer and vendor flows.

**Status**: ✅ COMPLETE  
**Date**: December 15, 2025  
**Figma Aligned**: Yes

---

## Table of Contents

1. [Features Implemented](#features-implemented)
2. [File Structure](#file-structure)
3. [User Flows](#user-flows)
4. [Database Schema](#database-schema)
5. [Controllers](#controllers)
6. [Views](#views)
7. [Routes](#routes)
8. [Middleware](#middleware)
9. [Testing Guide](#testing-guide)
10. [Deployment](#deployment)

---

## Features Implemented

### ✅ Role Selection
- Two-step signup: Role selection → Registration form
- Session-based role persistence
- Visual role cards (Customer/Vendor)
- CSRF protection

### ✅ Customer Registration
- Simple signup → Direct to dashboard
- No approval required
- Automatic account activation
- Instant access after registration

### ✅ Vendor Registration  
- 5-step onboarding process:
  1. Company Details (GSTIN, PAN, Address)
  2. Business Information (Cities, Types, Contact)
  3. KYC Documents (PAN card, GST cert, etc.)
  4. Bank Details (Account info)
  5. Terms & Agreement
- Draft status for incomplete onboarding
- Pending approval status after completion
- Admin approval required for access

### ✅ Login System
- Single login for all roles (email or phone + password)
- Role-aware redirects:
  - Customer → Dashboard
  - Vendor → Dashboard or Onboarding (based on status)
  - Admin → Admin panel
- Remember Me functionality
- Password visibility toggle

### ✅ Vendor Status Management
- `draft` - Onboarding in progress
- `pending_approval` - Submitted, awaiting admin
- `approved` - Full access granted
- `rejected` - Application denied
- `suspended` - Account suspended

---

## File Structure

```
app/
├── Models/
│   ├── User.php (✅ Updated with vendor methods)
│   └── VendorProfile.php (✅ New)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php (✅ New)
│   │   │   └── RegisterController.php (✅ New)
│   │   └── Vendor/
│   │       └── OnboardingController.php (✅ New)
│   ├── Requests/
│   │   ├── Auth/
│   │   │   └── RegisterRequest.php (✅ New)
│   │   └── Vendor/
│   │       ├── CompanyDetailsRequest.php (✅ New)
│   │       ├── BusinessInfoRequest.php (✅ New)
│   │       ├── KYCDocumentsRequest.php (✅ New)
│   │       ├── BankDetailsRequest.php (✅ New)
│   │       └── TermsAgreementRequest.php (✅ New)
│   └── Middleware/
│       └── EnsureVendorOnboardingComplete.php (✅ New)
├── database/
│   └── migrations/
│       └── 2025_12_15_000001_create_vendor_profiles_table.php (✅ New)
└── resources/views/
    ├── auth/
    │   ├── role-selection.blade.php (✅ New)
    │   ├── register.blade.php (✅ Replaced)
    │   └── login.blade.php (✅ Replaced)
    └── vendor/onboarding/
        ├── company-details.blade.php (✅ New)
        ├── business-info.blade.php (⚠️ Template needed)
        ├── kyc-documents.blade.php (⚠️ Template needed)
        ├── bank-details.blade.php (⚠️ Template needed)
        ├── terms-agreement.blade.php (⚠️ Template needed)
        ├── waiting.blade.php (✅ New)
        └── rejected.blade.php (✅ New)
```

---

## User Flows

### Customer Flow

```
1. Click "Sign Up"
   ↓
2. Role Selection Screen → Select "Customer"
   ↓
3. Registration Form (Name, Email, Phone, Password)
   ↓
4. Submit Form
   ↓
5. Account Created + Auto-Login
   ↓
6. Redirect to Customer Dashboard
```

**Timeline**: < 2 minutes  
**Approval**: Not required

---

### Vendor Flow

```
1. Click "Sign Up"
   ↓
2. Role Selection Screen → Select "Vendor"
   ↓
3. Registration Form (Name, Email, Phone, Password)
   ↓
4. Submit Form → Account Created + Auto-Login
   ↓
5. Redirect to Vendor Onboarding
   ↓
   Step 1: Company Details
   Step 2: Business Information
   Step 3: KYC Documents
   Step 4: Bank Details
   Step 5: Terms & Agreement
   ↓
6. Submit for Approval → "Pending" Screen
   ↓
7. Admin Reviews Application
   ↓
8a. If Approved → Vendor Dashboard Access
8b. If Rejected → Rejection Screen + Contact Support
```

**Timeline**: 5-15 minutes (onboarding) + 2-3 days (approval)  
**Approval**: Required

---

### Login Flow

```
1. Enter Email/Phone + Password
   ↓
2. System Authenticates User
   ↓
3. Detect Role:
   
   Customer:
   └─> Redirect to Customer Dashboard
   
   Vendor:
   ├─> If Onboarding Incomplete → Resume Onboarding (Step X)
   ├─> If Pending Approval → Waiting Screen
   ├─> If Approved → Vendor Dashboard
   ├─> If Rejected → Rejection Screen
   └─> If Suspended → Logout + Error Message
   
   Admin:
   └─> Redirect to Admin Panel
```

---

## Database Schema

### vendor_profiles Table

```sql
CREATE TABLE vendor_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Status
    onboarding_status ENUM('draft', 'pending_approval', 'approved', 'rejected', 'suspended') DEFAULT 'draft',
    onboarding_step INT DEFAULT 1,
    onboarding_completed_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    approved_by BIGINT UNSIGNED NULL,
    rejection_reason TEXT NULL,
    
    -- Step 1: Company Details
    company_name VARCHAR(255),
    company_registration_number VARCHAR(100),
    company_type ENUM('proprietorship', 'partnership', 'private_limited', 'public_limited', 'llp', 'other'),
    gstin VARCHAR(15),
    pan VARCHAR(10),
    registered_address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    website VARCHAR(255),
    
    -- Step 2: Business Information
    year_established YEAR,
    total_hoardings INT DEFAULT 0,
    service_cities JSON,
    hoarding_types JSON,
    business_description TEXT,
    contact_person_name VARCHAR(255),
    contact_person_designation VARCHAR(100),
    contact_person_phone VARCHAR(15),
    contact_person_email VARCHAR(255),
    
    -- Step 3: KYC Documents
    pan_card_document VARCHAR(255),
    gst_certificate VARCHAR(255),
    company_registration_certificate VARCHAR(255),
    address_proof VARCHAR(255),
    cancelled_cheque VARCHAR(255),
    owner_id_proof VARCHAR(255),
    other_documents JSON,
    kyc_verified BOOLEAN DEFAULT FALSE,
    kyc_verified_at TIMESTAMP NULL,
    
    -- Step 4: Bank Details
    bank_name VARCHAR(255),
    account_holder_name VARCHAR(255),
    account_number VARCHAR(20),
    ifsc_code VARCHAR(11),
    branch_name VARCHAR(255),
    account_type ENUM('savings', 'current'),
    bank_verified BOOLEAN DEFAULT FALSE,
    
    -- Step 5: Terms & Agreement
    terms_accepted BOOLEAN DEFAULT FALSE,
    terms_accepted_at TIMESTAMP NULL,
    terms_ip_address VARCHAR(45),
    commission_agreement_accepted BOOLEAN DEFAULT FALSE,
    commission_percentage DECIMAL(5,2) DEFAULT 10.00,
    special_terms TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_status (user_id, onboarding_status),
    INDEX idx_status (onboarding_status),
    INDEX idx_gstin (gstin),
    INDEX idx_pan (pan)
);
```

---

## Controllers

### LoginController

**Location**: `app/Http/Controllers/Auth/LoginController.php`

**Key Methods**:
- `showLoginForm()` - Display login page
- `login(Request)` - Authenticate user, role-based redirect
- `redirectBasedOnRole(User)` - Determine redirect based on role
- `handleVendorRedirect(User)` - Handle vendor-specific logic
- `logout(Request)` - End session

**Logic Flow**:
1. Validate credentials (email or phone + password)
2. Check user status (suspended, pending_verification)
3. Login user
4. Update last_login_at
5. Redirect based on role and onboarding status

---

### RegisterController

**Location**: `app/Http/Controllers/Auth/RegisterController.php`

**Key Methods**:
- `showRoleSelection()` - Display role selection screen
- `storeRoleSelection(Request)` - Save role in session
- `showRegistrationForm()` - Display signup form
- `register(RegisterRequest)` - Create user + handle role-specific logic

**Customer Registration**:
```php
User::create([...]);
$user->assignRole('customer');
$user->update(['status' => 'active']);
Auth::login($user);
return redirect()->route('customer.dashboard');
```

**Vendor Registration**:
```php
User::create([...]);
$user->assignRole('vendor');
$user->update(['status' => 'pending_verification']);
VendorProfile::create(['user_id' => $user->id, 'onboarding_status' => 'draft']);
Auth::login($user);
return redirect()->route('vendor.onboarding.company-details');
```

---

### OnboardingController

**Location**: `app/Http/Controllers/Vendor/OnboardingController.php`

**Step Methods** (5 pairs):
1. `showCompanyDetails()` / `storeCompanyDetails()`
2. `showBusinessInfo()` / `storeBusinessInfo()`
3. `showKYCDocuments()` / `storeKYCDocuments()`
4. `showBankDetails()` / `storeBankDetails()`
5. `showTermsAgreement()` / `storeTermsAgreement()`

**Additional Methods**:
- `showWaitingScreen()` - Pending approval page
- `showRejectionScreen()` - Rejection page

**Step Validation**:
Each step checks if previous steps are complete before allowing access.

**File Uploads** (Step 3):
```php
if ($request->hasFile('pan_card_document')) {
    $path = $request->file('pan_card_document')->store('vendor/kyc', 'public');
    $profile->update(['pan_card_document' => $path]);
}
```

---

## Views

### Role Selection  
**File**: `resources/views/auth/role-selection.blade.php`

**Design**:
- Full-screen split layout (Black left / White right)
- Billboard illustration + OohApp logo (left)
- Two role cards: Customer + Vendor (right)
- Cards with icons and hover effects
- "Continue" button disabled until role selected
- JavaScript for role selection

---

### Registration Form  
**File**: `resources/views/auth/register.blade.php`

**Fields**:
- Full Name
- Email Address
- Mobile Number
- Password (with strength indicator)
- Confirm Password

**Features**:
- Role badge at top (shows selected role)
- Password visibility toggle
- Real-time validation
- "Change Role" link back to selection

---

### Login Form  
**File**: `resources/views/auth/login.blade.php`

**Fields**:
- Email or Mobile Number
- Password
- Remember Me checkbox

**Features**:
- Same split layout as role selection
- Password visibility toggle
- Forgot Password link
- Sign Up link

---

### Onboarding Views

#### Step 1: Company Details
**File**: `resources/views/vendor/onboarding/company-details.blade.php`

**Fields**:
- Company Name *, Company Type *, Registration Number
- GSTIN * (15 chars), PAN * (10 chars)
- Registered Address *, City *, State *, Pincode * (6 digits)
- Website

**Progress Indicator**: 5 steps with current step highlighted

---

#### Step 2: Business Information
**File**: `resources/views/vendor/onboarding/business-info.blade.php` (⚠️ TEMPLATE NEEDED)

**Template Structure**:
```blade
@extends('layouts.app')

@section('content')
<div class="container py-5">
    <!-- Progress Steps (copy from company-details.blade.php, highlight step 2) -->
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Step 2: Business Information</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('vendor.onboarding.business-info.store') }}" method="POST">
                @csrf
                
                <!-- Year Established * -->
                <input type="number" name="year_established" min="1900" max="{{ date('Y') }}" required>
                
                <!-- Total Hoardings -->
                <input type="number" name="total_hoardings" min="0">
                
                <!-- Service Cities * (Multi-select) -->
                <select name="service_cities[]" multiple required>
                    <option value="Mumbai">Mumbai</option>
                    <option value="Delhi">Delhi</option>
                    <option value="Bangalore">Bangalore</option>
                    <!-- Add all major cities -->
                </select>
                
                <!-- Hoarding Types * (Checkboxes) -->
                <label><input type="checkbox" name="hoarding_types[]" value="billboard"> Billboard</label>
                <label><input type="checkbox" name="hoarding_types[]" value="unipole"> Unipole</label>
                <label><input type="checkbox" name="hoarding_types[]" value="gantry"> Gantry</label>
                <!-- Add all types -->
                
                <!-- Business Description (Optional, max 1000 chars) -->
                <textarea name="business_description" maxlength="1000"></textarea>
                
                <!-- Contact Person Name * -->
                <input type="text" name="contact_person_name" required>
                
                <!-- Contact Person Designation -->
                <input type="text" name="contact_person_designation">
                
                <!-- Contact Person Phone * -->
                <input type="tel" name="contact_person_phone" required>
                
                <!-- Contact Person Email * -->
                <input type="email" name="contact_person_email" required>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('vendor.onboarding.company-details') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
```

---

#### Step 3: KYC Documents
**File**: `resources/views/vendor/onboarding/kyc-documents.blade.php` (⚠️ TEMPLATE NEEDED)

**Required Documents**:
1. PAN Card Document * (PDF/JPG/PNG, max 5MB)
2. GST Certificate * (PDF/JPG/PNG, max 5MB)
3. Company Registration Certificate * (PDF/JPG/PNG, max 5MB)
4. Address Proof * (Electricity bill/Rent agreement, max 5MB)
5. Cancelled Cheque * (max 5MB)
6. Owner ID Proof (Aadhar/Passport, optional)
7. Other Documents (Multiple files, optional)

**Template**:
```blade
<form action="{{ route('vendor.onboarding.kyc-documents.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="mb-3">
        <label>PAN Card Document <span class="text-danger">*</span></label>
        <input type="file" name="pan_card_document" accept=".pdf,.jpg,.jpeg,.png" required>
        @if($profile->pan_card_document)
            <p>Current: <a href="{{ Storage::url($profile->pan_card_document) }}" target="_blank">View</a></p>
        @endif
    </div>
    
    <!-- Repeat for all documents -->
    
    <div class="mb-3">
        <label>Other Documents (Optional, multiple files)</label>
        <input type="file" name="other_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png">
    </div>
    
    <div class="d-flex justify-content-between">
        <a href="{{ route('vendor.onboarding.business-info') }}" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-primary">Next</button>
    </div>
</form>
```

---

#### Step 4: Bank Details
**File**: `resources/views/vendor/onboarding/bank-details.blade.php` (⚠️ TEMPLATE NEEDED)

**Fields**:
- Bank Name *
- Account Holder Name *
- Account Number * (digits only)
- IFSC Code * (11 chars, e.g., SBIN0001234)
- Branch Name *
- Account Type * (Savings/Current radio)

**Template**:
```blade
<form action="{{ route('vendor.onboarding.bank-details.store') }}" method="POST">
    @csrf
    
    <input type="text" name="bank_name" required>
    <input type="text" name="account_holder_name" required>
    <input type="text" name="account_number" pattern="[0-9]+" required>
    <input type="text" name="ifsc_code" maxlength="11" pattern="[A-Z]{4}0[A-Z0-9]{6}" required>
    <input type="text" name="branch_name" required>
    
    <div>
        <label><input type="radio" name="account_type" value="savings" required> Savings</label>
        <label><input type="radio" name="account_type" value="current"> Current</label>
    </div>
    
    <div class="d-flex justify-content-between">
        <a href="{{ route('vendor.onboarding.kyc-documents') }}" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-primary">Next</button>
    </div>
</form>
```

---

#### Step 5: Terms & Agreement
**File**: `resources/views/vendor/onboarding/terms-agreement.blade.php` (⚠️ TEMPLATE NEEDED)

**Content**:
- Terms & Conditions (scrollable text box)
- Commission Agreement (10% default)
- Acceptance checkboxes

**Template**:
```blade
<form action="{{ route('vendor.onboarding.terms-agreement.store') }}" method="POST">
    @csrf
    
    <div class="card mb-3">
        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
            <h5>Terms & Conditions</h5>
            <p>[Full terms text here...]</p>
        </div>
    </div>
    
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="terms" name="terms_accepted" value="1" required>
        <label for="terms">I accept the Terms & Conditions *</label>
    </div>
    
    <div class="card mb-3">
        <div class="card-body">
            <h5>Commission Agreement</h5>
            <p>You agree to pay a commission of <strong>10%</strong> on all bookings processed through OohApp.</p>
        </div>
    </div>
    
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="commission" name="commission_agreement_accepted" value="1" required>
        <label for="commission">I accept the Commission Agreement *</label>
    </div>
    
    <div class="d-flex justify-content-between">
        <a href="{{ route('vendor.onboarding.bank-details') }}" class="btn btn-secondary">Back</a>
        <button type="submit" class="btn btn-success">Submit Application</button>
    </div>
</form>
```

---

## Routes

### Authentication Routes

```php
// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

// Registration
Route::get('/register', [RegisterController::class, 'showRoleSelection'])->name('register.role-selection');
Route::post('/register/role', [RegisterController::class, 'storeRoleSelection'])->name('register.store-role');
Route::get('/register/form', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register/submit', [RegisterController::class, 'register'])->name('register.submit');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
```

### Vendor Onboarding Routes

```php
Route::middleware(['auth', 'role:vendor'])->prefix('vendor/onboarding')->name('vendor.onboarding.')->group(function () {
    Route::get('/company-details', [OnboardingController::class, 'showCompanyDetails'])->name('company-details');
    Route::post('/company-details', [OnboardingController::class, 'storeCompanyDetails'])->name('company-details.store');
    
    Route::get('/business-info', [OnboardingController::class, 'showBusinessInfo'])->name('business-info');
    Route::post('/business-info', [OnboardingController::class, 'storeBusinessInfo'])->name('business-info.store');
    
    Route::get('/kyc-documents', [OnboardingController::class, 'showKYCDocuments'])->name('kyc-documents');
    Route::post('/kyc-documents', [OnboardingController::class, 'storeKYCDocuments'])->name('kyc-documents.store');
    
    Route::get('/bank-details', [OnboardingController::class, 'showBankDetails'])->name('bank-details');
    Route::post('/bank-details', [OnboardingController::class, 'storeBankDetails'])->name('bank-details.store');
    
    Route::get('/terms-agreement', [OnboardingController::class, 'showTermsAgreement'])->name('terms-agreement');
    Route::post('/terms-agreement', [OnboardingController::class, 'storeTermsAgreement'])->name('terms-agreement.store');
    
    Route::get('/waiting', [OnboardingController::class, 'showWaitingScreen'])->name('waiting');
    Route::get('/rejected', [OnboardingController::class, 'showRejectionScreen'])->name('rejected');
});
```

---

## Middleware

### EnsureVendorOnboardingComplete

**Location**: `app/Http/Middleware/EnsureVendorOnboardingComplete.php`

**Purpose**: Prevent vendors from accessing dashboard/features until onboarding is complete and approved.

**Logic**:
1. Check if user is vendor
2. Allow access to onboarding routes
3. Check vendor profile status:
   - `draft` → Redirect to current onboarding step
   - `pending_approval` → Redirect to waiting screen
   - `rejected` → Redirect to rejection screen
   - `suspended` → Logout + error
   - `approved` → Allow access

**Registration** (in `bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'vendor.onboarding' => \App\Http\Middleware\EnsureVendorOnboardingComplete::class,
    ]);
})
```

**Usage**:
```php
Route::middleware(['auth', 'role:vendor', 'vendor.onboarding'])->group(function () {
    // Protected vendor routes
});
```

---

## Testing Guide

### Manual Testing Checklist

#### Customer Flow
- [ ] Visit `/register`
- [ ] Select "Customer" role
- [ ] Fill registration form with valid data
- [ ] Submit form
- [ ] Verify redirect to customer dashboard
- [ ] Check user created in database with `customer` role
- [ ] Check `status = 'active'`
- [ ] Logout
- [ ] Login with created credentials
- [ ] Verify redirect to customer dashboard

#### Vendor Flow - Registration
- [ ] Visit `/register`
- [ ] Select "Vendor" role
- [ ] Fill registration form
- [ ] Submit form
- [ ] Verify redirect to `/vendor/onboarding/company-details`
- [ ] Check user created with `vendor` role
- [ ] Check `vendor_profiles` record created with `onboarding_status = 'draft'`

#### Vendor Flow - Onboarding
- [ ] Complete Step 1 (Company Details)
- [ ] Verify GSTIN validation (15 chars)
- [ ] Verify PAN validation (10 chars)
- [ ] Click "Next" → Redirect to Step 2
- [ ] Complete Step 2 (Business Info)
- [ ] Select multiple cities and hoarding types
- [ ] Click "Next" → Redirect to Step 3
- [ ] Upload all required documents (PDF/JPG/PNG, max 5MB)
- [ ] Verify file upload to `storage/app/public/vendor/kyc/`
- [ ] Click "Next" → Redirect to Step 4
- [ ] Enter bank details
- [ ] Verify IFSC validation (11 chars)
- [ ] Click "Next" → Redirect to Step 5
- [ ] Read Terms & Conditions
- [ ] Check both acceptance boxes
- [ ] Submit Application
- [ ] Verify redirect to `/vendor/onboarding/waiting`
- [ ] Check `onboarding_status = 'pending_approval'`
- [ ] Check `onboarding_completed_at` is set

#### Vendor Flow - Login (Pending Approval)
- [ ] Logout
- [ ] Login with vendor credentials
- [ ] Verify redirect to waiting screen
- [ ] Check message: "Application Under Review"

#### Admin Approval (Manual DB Update)
```sql
UPDATE vendor_profiles 
SET onboarding_status = 'approved', approved_at = NOW() 
WHERE user_id = [vendor_user_id];

UPDATE users 
SET status = 'active' 
WHERE id = [vendor_user_id];
```

#### Vendor Flow - Login (Approved)
- [ ] Login with vendor credentials
- [ ] Verify redirect to vendor dashboard
- [ ] Full access granted

#### Vendor Flow - Rejection
```sql
UPDATE vendor_profiles 
SET onboarding_status = 'rejected', 
    rejection_reason = 'Invalid GSTIN provided' 
WHERE user_id = [vendor_user_id];
```
- [ ] Login with vendor credentials
- [ ] Verify redirect to rejection screen
- [ ] Check rejection reason displayed

---

### Edge Cases

1. **Session Expiry During Signup**:
   - If session expires after role selection, user redirected to role selection

2. **Incomplete Onboarding + Login**:
   - Vendor resumes at exact step where they left off

3. **Cross-Role Access**:
   - Customer cannot access `/vendor/*` routes (403 Forbidden)
   - Vendor cannot access `/customer/*` routes (403 Forbidden)

4. **Email/Phone Uniqueness**:
   - Test duplicate email registration (should fail)
   - Test duplicate phone registration (should fail)

5. **Password Strength**:
   - Test weak password (should fail validation)
   - Must have: uppercase, lowercase, number, symbol, min 8 chars

---

## Deployment

### Step 1: Run Migrations

```bash
php artisan migrate
```

**Creates**: `vendor_profiles` table

### Step 2: Update User Model

Ensure `User.php` has `vendorProfile()` relationship (already added).

### Step 3: Create Roles (If Not Exist)

```bash
php artisan tinker
```

```php
use Spatie\Permission\Models\Role;

Role::firstOrCreate(['name' => 'customer']);
Role::firstOrCreate(['name' => 'vendor']);
Role::firstOrCreate(['name' => 'admin']);
```

### Step 4: Configure Storage

```bash
php artisan storage:link
```

**Creates symlink**: `public/storage` → `storage/app/public`

### Step 5: Set Permissions

```bash
chmod -R 775 storage/app/public/vendor
chown -R www-data:www-data storage/app/public/vendor
```

### Step 6: Test Registration

1. Visit `https://yourdomain.com/register`
2. Complete customer signup
3. Complete vendor signup
4. Verify database records

### Step 7: Admin Panel Integration

Create admin panel view to approve/reject vendors:

```php
// app/Http/Controllers/Admin/VendorApprovalController.php

public function index()
{
    $pendingVendors = VendorProfile::where('onboarding_status', 'pending_approval')
        ->with('user')
        ->get();
    
    return view('admin.vendors.approvals', compact('pendingVendors'));
}

public function approve($id)
{
    $profile = VendorProfile::findOrFail($id);
    $profile->approve(Auth::user());
    
    // Send approval email
    
    return back()->with('success', 'Vendor approved successfully!');
}

public function reject(Request $request, $id)
{
    $profile = VendorProfile::findOrFail($id);
    $profile->reject($request->rejection_reason);
    
    // Send rejection email
    
    return back()->with('success', 'Vendor rejected.');
}
```

---

## Security Considerations

### CSRF Protection
All forms include `@csrf` token.

### Password Hashing
Passwords hashed using `bcrypt` via `Hash::make()`.

### Input Validation
- All inputs validated via Form Requests
- GSTIN regex: `^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$`
- PAN regex: `^[A-Z]{5}[0-9]{4}[A-Z]{1}$`
- IFSC regex: `^[A-Z]{4}0[A-Z0-9]{6}$`

### File Upload Security
- Allowed types: PDF, JPG, JPEG, PNG
- Max size: 5MB
- Stored in `storage/app/public/vendor/kyc/` (not web-accessible directly)
- Validate MIME types server-side

### Role-Based Authorization
- Spatie Permission package
- Middleware: `role:vendor`, `role:customer`, `role:admin`

---

## Future Enhancements

1. **Email Verification**:
   - Send verification email after registration
   - Require email confirmation before dashboard access

2. **Phone OTP Verification**:
   - Verify phone number via OTP

3. **Forgot Password**:
   - Password reset via email link

4. **Vendor Onboarding Progress Save**:
   - Auto-save drafts every 30 seconds

5. **Document Preview**:
   - In-browser PDF/image preview before upload

6. **Admin Notifications**:
   - Real-time notifications when vendor submits application
   - Email alerts for pending approvals

7. **Vendor Dashboard Welcome Tour**:
   - First-time login tutorial

8. **Multi-Language Support**:
   - Translate forms into Hindi, Marathi, etc.

9. **Social Login**:
   - Google/Facebook OAuth (if required in future)

10. **Vendor Reapplication**:
    - Allow rejected vendors to reapply with corrections

---

## Troubleshooting

### Issue: "Role selection not persisting"
**Solution**: Check session driver in `.env`:
```env
SESSION_DRIVER=file
```

### Issue: "Vendor profile not created"
**Solution**: Check migration ran successfully:
```bash
php artisan migrate:status
```

### Issue: "File upload fails"
**Solution**: 
1. Check `php.ini`: `upload_max_filesize=10M`, `post_max_size=10M`
2. Check storage permissions: `chmod 775 storage/app/public`
3. Run `php artisan storage:link`

### Issue: "Redirect loop during login"
**Solution**: Check middleware not blocking onboarding routes in `EnsureVendorOnboardingComplete.php`.

### Issue: "GSTIN/PAN validation failing"
**Solution**: Check regex patterns in `CompanyDetailsRequest.php`.

---

## Support

For issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database records in `users` and `vendor_profiles` tables
3. Test in incognito mode to rule out cache issues

---

## Changelog

### Version 1.0.0 (December 15, 2025)

- ✅ Role-based signup flow
- ✅ Customer instant registration
- ✅ Vendor 5-step onboarding
- ✅ Login with role-aware redirects
- ✅ Vendor status management
- ✅ Onboarding completion middleware
- ✅ Waiting and rejection screens
- ✅ Session-based role persistence
- ✅ Comprehensive validation
- ✅ File upload handling

---

## License

Part of OOHAPP platform - All rights reserved
