# DOOH Package Booking Module - Complete Documentation

**Module:** Prompt 24  
**Version:** 1.0  
**Date:** December 8, 2025  
**Status:** ✅ Production Ready

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Key Differences from Traditional OOH](#key-differences)
3. [Database Schema](#database-schema)
4. [Models](#models)
5. [Services](#services)
6. [API Endpoints](#api-endpoints)
7. [Frontend Views](#frontend-views)
8. [Booking Flow](#booking-flow)
9. [External API Integration](#external-api-integration)
10. [Configuration](#configuration)
11. [Testing Guide](#testing-guide)
12. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The DOOH (Digital Out-of-Home) Package Booking module implements a completely different booking flow compared to traditional OOH hoardings. It is designed for digital advertising screens with flexible package-based slot allocation.

### Core Concepts

**Package-Based Booking:**
- Customers book **packages per month** (not individual campaigns)
- Each package includes a fixed number of **slots per day**
- Minimum booking requirements enforce business rules

**Slot Allocation:**
- **Slot Duration:** How long each ad plays (e.g., 10 seconds)
- **Loop Duration:** Total loop time before ads repeat (e.g., 5 minutes)
- **Slots Per Loop:** Calculated automatically (loop ÷ slot duration)
- **Frequency:** How often customer's ad appears in the loop

**No Physical Work:**
- ❌ No mounting required
- ❌ No printing required
- ✅ Digital content only (upload mp4, jpg, png)
- ✅ Optional survey package for audience analytics

---

## 🔄 Key Differences from Traditional OOH

| Feature | Traditional OOH | DOOH Package Booking |
|---------|----------------|----------------------|
| **Booking Unit** | Individual campaigns | Monthly packages |
| **Pricing** | Per campaign duration | Per month with packages |
| **Minimum Requirement** | Duration (days/weeks) | Slots per day + Minimum amount |
| **Content** | Physical printing | Digital files (mp4, jpg, png) |
| **Mounting** | Required with POD | Not required |
| **Frequency** | Single display | Multiple displays per day (loop-based) |
| **Slot Management** | Not applicable | Slot allocation per day |
| **Survey** | Usually included | Optional add-on |
| **Content Approval** | Before printing | After payment, before going live |

---

## 💾 Database Schema

### 1. `dooh_screens` Table

Stores digital screen inventory with external API sync capability.

```sql
CREATE TABLE dooh_screens (
    id BIGINT PRIMARY KEY,
    vendor_id BIGINT FK → users.id,
    external_screen_id VARCHAR UNIQUE, -- From external API
    
    -- Screen Details
    name VARCHAR,
    description TEXT,
    screen_type VARCHAR DEFAULT 'digital',
    
    -- Location
    address TEXT,
    city VARCHAR,
    state VARCHAR,
    country VARCHAR DEFAULT 'India',
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    
    -- Specifications
    resolution VARCHAR, -- "1920x1080"
    screen_size VARCHAR, -- "55 inch"
    width DECIMAL(8,2), -- in feet
    height DECIMAL(8,2),
    
    -- Slot Configuration
    slot_duration_seconds INT DEFAULT 10,
    loop_duration_seconds INT DEFAULT 300,
    slots_per_loop INT DEFAULT 30,
    
    -- Pricing & Requirements
    min_slots_per_day INT DEFAULT 6,
    price_per_slot DECIMAL(10,2),
    price_per_month DECIMAL(12,2),
    minimum_booking_amount DECIMAL(12,2),
    
    -- Availability
    total_slots_per_day INT DEFAULT 144,
    available_slots_per_day INT DEFAULT 144,
    
    -- Content Rules
    allowed_formats JSON, -- ['mp4', 'jpg', 'png']
    max_file_size_mb INT DEFAULT 50,
    
    -- Status
    status ENUM('draft', 'pending_approval', 'active', 'inactive', 'suspended'),
    sync_status ENUM('pending', 'synced', 'failed'),
    last_synced_at TIMESTAMP,
    sync_metadata JSON,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

**Indexes:** vendor_id, status, city, external_screen_id, [lat, lng]

### 2. `dooh_packages` Table

Defines monthly packages with slot allocation and pricing.

```sql
CREATE TABLE dooh_packages (
    id BIGINT PRIMARY KEY,
    dooh_screen_id BIGINT FK → dooh_screens.id,
    
    -- Package Details
    package_name VARCHAR, -- "Basic Package", "Premium Package"
    description TEXT,
    
    -- Slot Allocation
    slots_per_day INT, -- Number of slots included per day
    slots_per_month INT, -- Total slots (calculated: slots_per_day × 30)
    loop_interval_minutes INT DEFAULT 5,
    time_slots JSON, -- [{"start": "09:00", "end": "18:00"}]
    
    -- Pricing
    price_per_month DECIMAL(12,2),
    price_per_day DECIMAL(10,2),
    min_booking_months INT DEFAULT 1,
    max_booking_months INT DEFAULT 12,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    
    -- Type
    package_type ENUM('standard', 'premium', 'custom'),
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

**Indexes:** dooh_screen_id, is_active, package_type

### 3. `dooh_bookings` Table

Complete booking lifecycle from draft to active campaign.

```sql
CREATE TABLE dooh_bookings (
    id BIGINT PRIMARY KEY,
    dooh_screen_id BIGINT FK → dooh_screens.id,
    dooh_package_id BIGINT FK → dooh_packages.id,
    customer_id BIGINT FK → users.id,
    vendor_id BIGINT FK → users.id,
    
    -- Booking Reference
    booking_number VARCHAR UNIQUE, -- DOOH-YYYYMMDD-XXXXXX
    
    -- Duration
    start_date DATE,
    end_date DATE,
    duration_months INT,
    duration_days INT,
    
    -- Slot Allocation
    slots_per_day INT,
    total_slots INT,
    slot_frequency_minutes INT,
    
    -- Content Management
    content_files JSON, -- [{path, type, size, duration, uploaded_at}]
    content_status ENUM('pending', 'approved', 'rejected'),
    content_rejection_reason TEXT,
    content_approved_at TIMESTAMP,
    content_approved_by BIGINT FK → users.id,
    
    -- Pricing
    package_price DECIMAL(12,2),
    total_amount DECIMAL(12,2),
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    grand_total DECIMAL(12,2),
    
    -- Payment
    payment_status ENUM('pending', 'authorized', 'captured', 'failed', 'refunded'),
    razorpay_order_id VARCHAR,
    razorpay_payment_id VARCHAR,
    payment_authorized_at TIMESTAMP,
    payment_captured_at TIMESTAMP,
    hold_expiry_at TIMESTAMP, -- 30-minute hold
    
    -- Refund
    refund_id VARCHAR,
    refund_amount DECIMAL(12,2),
    refunded_at TIMESTAMP,
    refund_reason TEXT,
    
    -- Lifecycle
    status ENUM('draft', 'payment_pending', 'payment_authorized', 'confirmed',
                'content_pending', 'content_approved', 'active', 'paused', 
                'completed', 'cancelled'),
    confirmed_at TIMESTAMP,
    campaign_started_at TIMESTAMP,
    campaign_ended_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    cancellation_reason TEXT,
    
    -- Snapshot
    booking_snapshot JSON, -- Screen, package, vendor details at booking time
    
    -- Notes
    customer_notes TEXT,
    vendor_notes TEXT,
    admin_notes TEXT,
    
    -- Survey
    survey_required BOOLEAN DEFAULT FALSE,
    survey_status ENUM('not_required', 'pending', 'completed'),
    survey_completed_at TIMESTAMP,
    survey_data JSON,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

**Indexes:** booking_number, customer_id, vendor_id, dooh_screen_id, status, payment_status, [start_date, end_date]

---

## 🧩 Models

### 1. DOOHScreen Model

**Location:** `Modules/DOOH/Models/DOOHScreen.php`

**Key Methods:**

```php
// Relationships
public function vendor(): BelongsTo
public function packages(): HasMany
public function bookings(): HasMany
public function activePackages(): HasMany

// Scopes
public function scopeActive($query)
public function scopeByVendor($query, int $vendorId)
public function scopeByCity($query, string $city)
public function scopeSynced($query)

// Business Logic
public function getAvailableSlots(string $startDate, string $endDate): int
public function calculateSlotsPerLoop(): int
public function isActive(): bool
public function isSynced(): bool

// Attributes
public function getDisplayNameAttribute(): string
public function getStatusLabelAttribute(): string
```

**Status Constants:**
- `STATUS_DRAFT` - Initial creation
- `STATUS_PENDING_APPROVAL` - Awaiting admin approval
- `STATUS_ACTIVE` - Available for booking
- `STATUS_INACTIVE` - Temporarily disabled
- `STATUS_SUSPENDED` - Admin suspended

**Sync Status Constants:**
- `SYNC_STATUS_PENDING` - Not yet synced
- `SYNC_STATUS_SYNCED` - Successfully synced
- `SYNC_STATUS_FAILED` - Sync failed

### 2. DOOHPackage Model

**Location:** `Modules/DOOH/Models/DOOHPackage.php`

**Key Methods:**

```php
// Relationships
public function screen(): BelongsTo
public function bookings(): HasMany

// Scopes
public function scopeActive($query)
public function scopeByScreen($query, int $screenId)
public function scopeByType($query, string $type)

// Business Logic
public function calculateTotalSlotsPerMonth(): int
public function calculateDiscountedPrice(int $months): float
public function meetsMinimumRequirement(): bool

// Attributes
public function getDisplayNameAttribute(): string
public function getTypeLabelAttribute(): string
```

**Package Types:**
- `TYPE_STANDARD` - Standard package
- `TYPE_PREMIUM` - Premium with more slots
- `TYPE_CUSTOM` - Custom tailored package

### 3. DOOHBooking Model

**Location:** `Modules/DOOH/Models/DOOHBooking.php`

**Key Methods:**

```php
// Relationships
public function screen(): BelongsTo
public function package(): BelongsTo
public function customer(): BelongsTo
public function vendor(): BelongsTo
public function contentApprover(): BelongsTo

// Scopes
public function scopeByCustomer($query, int $customerId)
public function scopeByVendor($query, int $vendorId)
public function scopeActive($query)
public function scopeConfirmed($query)
public function scopePaymentAuthorized($query)

// Business Logic
public function isPaymentHoldExpired(): bool
public function isWithinCancellationWindow(): bool // 30 minutes
public function isContentApproved(): bool
public function isCampaignActive(): bool

// Static
public static function generateBookingNumber(): string

// Attributes
public function getStatusLabelAttribute(): string
public function getPaymentStatusLabelAttribute(): string
public function getSnapshotValue(string $key, $default = null)
```

**Status Flow:**
```
draft → payment_pending → payment_authorized → confirmed 
      → content_pending → content_approved → active → completed

OR

any_status → cancelled (with refund if within 30 min)
```

---

## ⚙️ Services

### 1. DOOHPackageBookingService

**Location:** `Modules/DOOH/Services/DOOHPackageBookingService.php`

**Dependencies:**
- `SettingsService` - For admin settings (tax rate, hold minutes)
- `RazorpayService` - For payment processing

**Core Methods:**

#### **Browse & Search**

```php
/**
 * Get available DOOH screens with filters
 * 
 * @param array $filters ['city', 'state', 'search', 'min_slots', 'per_page']
 * @return LengthAwarePaginator
 */
public function getAvailableScreens(array $filters = [])
```

```php
/**
 * Get screen with packages and availability
 * 
 * @param int $screenId
 * @return DOOHScreen|null
 */
public function getScreenDetails(int $screenId): ?DOOHScreen
```

#### **Availability & Pricing**

```php
/**
 * Check package availability for given dates
 * 
 * @param int $packageId
 * @param string $startDate (Y-m-d)
 * @param string $endDate (Y-m-d)
 * @return array [
 *   'available' => bool,
 *   'message' => string,
 *   'pricing' => array (if available)
 * ]
 */
public function checkPackageAvailability(
    int $packageId,
    string $startDate,
    string $endDate
): array
```

**Validation Rules:**
- Start date not in past
- End date after start date
- Duration within package min/max months
- Enough slots available
- Meets minimum booking amount

```php
/**
 * Calculate pricing for package booking
 * 
 * @param DOOHPackage $package
 * @param string $startDate
 * @param string $endDate
 * @return array [
 *   'package_price' => float,
 *   'duration_days' => int,
 *   'duration_months' => int,
 *   'total_amount' => float,
 *   'discount_percent' => float,
 *   'discount_amount' => float,
 *   'tax_rate' => float,
 *   'tax_amount' => float,
 *   'grand_total' => float,
 *   'total_slots' => int,
 *   'slots_per_day' => int,
 *   'slot_frequency_minutes' => int
 * ]
 */
public function calculatePricing(
    DOOHPackage $package,
    string $startDate,
    string $endDate
): array
```

**Calculation Logic:**
1. Calculate duration in months (round up: days ÷ 30)
2. Base amount = package_price × months
3. Discount = base_amount × discount_percent / 100
4. Taxable amount = base_amount - discount
5. Tax = taxable_amount × tax_rate / 100
6. Grand total = taxable_amount + tax
7. Total slots = slots_per_day × duration_days

#### **Booking Lifecycle**

```php
/**
 * Create DOOH package booking
 * 
 * @param array $data [
 *   'customer_id' => int,
 *   'dooh_package_id' => int,
 *   'start_date' => string,
 *   'end_date' => string,
 *   'customer_notes' => string|null,
 *   'survey_required' => bool
 * ]
 * @return DOOHBooking
 * @throws Exception
 */
public function createBooking(array $data): DOOHBooking
```

**Booking Process:**
1. Validate package availability
2. Calculate pricing
3. Generate unique booking number (DOOH-YYYYMMDD-XXXXXX)
4. Create booking snapshot (screen, package, vendor details)
5. Set status to `draft`
6. Return booking with relationships

#### **Payment Integration**

```php
/**
 * Initiate Razorpay payment with 30-min hold
 * 
 * @param int $bookingId
 * @param int $customerId
 * @return array [
 *   'success' => bool,
 *   'order_id' => string,
 *   'amount' => int (in paise),
 *   'currency' => string,
 *   'razorpay_key' => string,
 *   'booking' => DOOHBooking
 * ]
 * @throws Exception
 */
public function initiatePayment(int $bookingId, int $customerId): array
```

**Payment Flow:**
1. Validate booking is in `draft` status
2. Create Razorpay order with `manual` capture
3. Set `hold_expiry_at` = now + 30 minutes
4. Update status to `payment_pending`
5. Return order details for Razorpay checkout

```php
/**
 * Confirm payment and capture amount
 * 
 * @param int $bookingId
 * @param int $customerId
 * @param array $paymentData [
 *   'razorpay_payment_id' => string,
 *   'razorpay_order_id' => string,
 *   'razorpay_signature' => string
 * ]
 * @return DOOHBooking
 * @throws Exception
 */
public function confirmPayment(
    int $bookingId,
    int $customerId,
    array $paymentData
): DOOHBooking
```

**Confirmation Process:**
1. Validate payment data
2. Verify order ID matches
3. Capture payment via Razorpay
4. Update payment_status to `captured`
5. Set status to `confirmed` or `content_pending` (based on content review setting)
6. Record payment_captured_at timestamp

#### **Content Management**

```php
/**
 * Upload content files for booking
 * 
 * @param int $bookingId
 * @param int $customerId
 * @param array $files Array of UploadedFile
 * @return DOOHBooking
 * @throws Exception
 */
public function uploadContent(
    int $bookingId,
    int $customerId,
    array $files
): DOOHBooking
```

**Upload Process:**
1. Validate booking status (must be confirmed/content_pending)
2. Validate each file:
   - Format in allowed list (mp4, jpg, png, gif)
   - Size under max limit
3. Store files in `storage/app/public/dooh_content/{booking_id}/`
4. Save file metadata (path, original_name, mime_type, size, uploaded_at)
5. Set content_status to `pending`
6. Update status to `content_pending`

```php
/**
 * Approve content (Admin/Vendor)
 * 
 * @param int $bookingId
 * @param int $approverId
 * @return DOOHBooking
 * @throws Exception
 */
public function approveContent(int $bookingId, int $approverId): DOOHBooking
```

**Approval Logic:**
- Set content_status to `approved`
- Record content_approved_at and content_approved_by
- Set status to `content_approved`
- If start_date is today or past → activate campaign immediately

```php
/**
 * Reject content with reason
 * 
 * @param int $bookingId
 * @param int $rejectorId
 * @param string $reason
 * @return DOOHBooking
 * @throws Exception
 */
public function rejectContent(
    int $bookingId,
    int $rejectorId,
    string $reason
): DOOHBooking
```

#### **Cancellation & Refund**

```php
/**
 * Cancel booking with auto-refund within 30 minutes
 * 
 * @param int $bookingId
 * @param int $customerId
 * @param string $reason
 * @return DOOHBooking
 * @throws Exception
 */
public function cancelBooking(
    int $bookingId,
    int $customerId,
    string $reason
): DOOHBooking
```

**Cancellation Logic:**
1. Check if within 30-minute window (payment_captured_at + 30 min)
2. If YES and payment captured:
   - Create full refund via Razorpay
   - Update refund details
   - Set payment_status to `refunded`
3. Set status to `cancelled`
4. Record cancellation_reason and cancelled_at

#### **Listing & Filters**

```php
/**
 * Get customer bookings with filters
 * 
 * @param int $customerId
 * @param array $filters ['status', 'start_date', 'per_page']
 * @return LengthAwarePaginator
 */
public function getCustomerBookings(int $customerId, array $filters = [])
```

```php
/**
 * Get vendor bookings with filters
 * 
 * @param int $vendorId
 * @param array $filters ['status', 'screen_id', 'per_page']
 * @return LengthAwarePaginator
 */
public function getVendorBookings(int $vendorId, array $filters = [])
```

---

### 2. DOOHInventoryApiService

**Location:** `Modules/DOOH/Services/DOOHInventoryApiService.php`

**Purpose:** Sync DOOH screens and packages from external inventory API.

**Configuration:**
```php
// .env
DOOH_API_URL=https://api.doohprovider.com/v1
DOOH_API_KEY=your_api_key_here
```

**Core Methods:**

```php
/**
 * Sync all screens from external API
 * 
 * @param int $vendorId Vendor to assign screens to
 * @return array [
 *   'success' => bool,
 *   'synced_count' => int,
 *   'errors_count' => int,
 *   'errors' => array
 * ]
 * @throws Exception
 */
public function syncScreens(int $vendorId): array
```

**Sync Process:**
1. Fetch screens from API: `GET /screens`
2. For each screen:
   - Calculate slots_per_loop = loop_duration ÷ slot_duration
   - Map external status to internal status
   - Update or create screen (upsert by external_screen_id)
   - Sync packages for screen
3. Set sync_status to `synced` and last_synced_at
4. Return summary with errors

```php
/**
 * Get available slots from API
 * 
 * @param string $externalScreenId
 * @param string $startDate
 * @param string $endDate
 * @return array Slot availability data
 */
public function getAvailableSlots(
    string $externalScreenId,
    string $startDate,
    string $endDate
): array
```

**Caching:** Results cached for 60 minutes (configurable)

```php
/**
 * Update slot availability after booking
 * 
 * @param string $externalScreenId
 * @param string $startDate
 * @param string $endDate
 * @param int $slotsBooked
 * @return bool Success status
 */
public function updateSlotAvailability(
    string $externalScreenId,
    string $startDate,
    string $endDate,
    int $slotsBooked
): bool
```

**Post-Booking Hook:** Call after successful booking to update external system.

```php
/**
 * Release slots after cancellation
 * 
 * @param string $externalScreenId
 * @param string $startDate
 * @param string $endDate
 * @param int $slotsToRelease
 * @return bool Success status
 */
public function releaseSlots(
    string $externalScreenId,
    string $startDate,
    string $endDate,
    int $slotsToRelease
): bool
```

```php
/**
 * Test API connection
 * 
 * @return bool
 */
public function testConnection(): bool
```

**External API Expected Format:**

**GET /screens Response:**
```json
{
  "data": [
    {
      "id": "EXT-SCREEN-001",
      "name": "Times Square Screen 1",
      "address": "Times Square, New York",
      "city": "Mumbai",
      "state": "Maharashtra",
      "latitude": 19.0760,
      "longitude": 72.8777,
      "resolution": "1920x1080",
      "screen_size": "55 inch",
      "slot_duration_seconds": 10,
      "loop_duration_seconds": 300,
      "min_slots_per_day": 6,
      "price_per_slot": 100,
      "price_per_month": 50000,
      "minimum_booking_amount": 25000,
      "total_slots_per_day": 144,
      "available_slots_per_day": 120,
      "allowed_formats": ["mp4", "jpg", "png"],
      "max_file_size_mb": 50,
      "status": "active",
      "packages": [
        {
          "name": "Basic Package",
          "slots_per_day": 6,
          "price_per_month": 25000,
          "loop_interval_minutes": 5,
          "min_booking_months": 1,
          "max_booking_months": 12,
          "discount_percent": 0,
          "package_type": "standard"
        }
      ]
    }
  ]
}
```

---

## 🌐 API Endpoints

### Customer Endpoints

**Base:** `/api/v1/customer/dooh`

#### 1. Get Available Screens
```
GET /screens
```

**Query Parameters:**
- `city` (optional) - Filter by city
- `state` (optional) - Filter by state
- `search` (optional) - Search in name/address
- `min_slots` (optional) - Minimum available slots per day
- `per_page` (optional) - Pagination limit

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Times Square Screen",
        "city": "Mumbai",
        "available_slots_per_day": 120,
        "min_slots_per_day": 6,
        "active_packages": [...]
      }
    ],
    "total": 45
  }
}
```

#### 2. Get Screen Details
```
GET /screens/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Times Square Screen",
    "vendor": {...},
    "active_packages": [
      {
        "id": 1,
        "package_name": "Basic Package",
        "slots_per_day": 6,
        "price_per_month": 25000
      }
    ]
  }
}
```

#### 3. Check Package Availability
```
POST /packages/{id}/check-availability
```

**Request Body:**
```json
{
  "start_date": "2025-12-15",
  "end_date": "2026-03-15"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "available": true,
    "message": "Package is available for booking",
    "pricing": {
      "package_price": 25000,
      "duration_months": 3,
      "total_amount": 75000,
      "discount_amount": 0,
      "tax_amount": 13500,
      "grand_total": 88500,
      "total_slots": 540
    }
  }
}
```

#### 4. Create Booking
```
POST /bookings
```

**Request Body:**
```json
{
  "dooh_package_id": 1,
  "start_date": "2025-12-15",
  "end_date": "2026-03-15",
  "customer_notes": "Peak hours preferred",
  "survey_required": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "id": 123,
    "booking_number": "DOOH-20251208-A1B2C3",
    "status": "draft",
    "grand_total": 88500
  }
}
```

#### 5. Initiate Payment
```
POST /bookings/{id}/initiate-payment
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order_id": "order_xxxxx",
    "amount": 8850000,
    "currency": "INR",
    "razorpay_key": "rzp_test_xxxxx"
  }
}
```

#### 6. Confirm Payment
```
POST /bookings/{id}/confirm-payment
```

**Request Body:**
```json
{
  "razorpay_payment_id": "pay_xxxxx",
  "razorpay_order_id": "order_xxxxx",
  "razorpay_signature": "signature_xxxxx"
}
```

#### 7. Upload Content
```
POST /bookings/{id}/upload-content
```

**Request:** `multipart/form-data`
- `files[]` - Array of files (max 5, 50MB each)

**Allowed Formats:** mp4, mov, avi, jpg, jpeg, png, gif

#### 8. Cancel Booking
```
POST /bookings/{id}/cancel
```

**Request Body:**
```json
{
  "reason": "Changed marketing strategy"
}
```

### Vendor Endpoints

**Base:** `/api/v1/vendor/dooh`

#### 1. Get Vendor Bookings
```
GET /bookings?status=content_pending&screen_id=1
```

#### 2. Approve Content
```
POST /bookings/{id}/approve-content
```

#### 3. Reject Content
```
POST /bookings/{id}/reject-content
```

**Request Body:**
```json
{
  "reason": "Poor video quality. Please upload HD version."
}
```

#### 4. Sync Screens from API
```
POST /sync-screens
```

**Response:**
```json
{
  "success": true,
  "data": {
    "synced_count": 25,
    "errors_count": 2,
    "errors": [...]
  }
}
```

#### 5. Test API Connection
```
GET /test-connection
```

---

## 🎨 Frontend Views

### 1. Screen Listing Page

**File:** `resources/views/customer/dooh/index.blade.php`

**URL:** `/customer/dooh`

**Features:**
- ✅ Screen cards with gradient backgrounds
- ✅ Filters: City, State, Min Slots, Search
- ✅ Real-time AJAX loading with pagination
- ✅ Available slots badges
- ✅ Starting price display
- ✅ Responsive grid layout (3 columns on desktop)
- ✅ Hover effects with elevation

**JavaScript Functions:**
- `loadScreens()` - Fetch and render screens
- `renderScreens(screens)` - Create HTML for cards
- `renderPagination(data)` - Build pagination UI
- `changePage(page)` - Navigate pages

### 2. Screen Details & Booking Page

**File:** `resources/views/customer/dooh/show.blade.php`

**URL:** `/customer/dooh/screens/{id}`

**Features:**
- ✅ Screen hero section with specifications
- ✅ Package selection with pricing cards
- ✅ Date range picker with validation
- ✅ Real-time pricing calculator
- ✅ Availability checker
- ✅ Booking summary sidebar (sticky)
- ✅ Razorpay integration
- ✅ Survey package toggle
- ✅ Customer notes textarea

**Workflow:**
1. Select package (highlights selected)
2. Choose dates (validates min/max)
3. Check availability → Shows pricing
4. Proceed to book → Creates booking
5. Payment modal → Razorpay checkout
6. Success → Redirect to booking details

**JavaScript Functions:**
- `selectPackage()` - Handle package selection
- `updateDuration()` - Calculate duration on date change
- `calculatePricing()` - Real-time price updates
- `checkAvailability()` - Validate dates and slots
- `createBooking()` - Create booking record
- `initiatePayment()` - Start Razorpay flow
- `openRazorpayCheckout()` - Open payment modal
- `confirmPayment()` - Complete payment capture

---

## 🔄 Complete Booking Flow

### Step 1: Browse Screens
**Customer → Screen Listing Page**
- View all active DOOH screens
- Filter by location, slots
- Search by name/address
- See starting prices and availability

### Step 2: View Details & Select Package
**Customer → Screen Details Page**
- See screen specifications (resolution, size, slot duration, loop)
- Browse available packages
- Compare slots per day, pricing, discounts
- Select preferred package

### Step 3: Choose Dates
**Customer → Date Selection**
- Pick start date (today or future)
- Pick end date (validates against package min/max months)
- See duration calculation (days and months)
- Real-time pricing updates

### Step 4: Check Availability
**Customer → Availability Validation**
- System checks:
  - Enough slots available?
  - Duration within package limits?
  - Meets minimum booking amount?
- Shows detailed pricing breakdown
- Enables "Proceed to Book" button

### Step 5: Create Booking
**Customer → Booking Creation**
- Optional: Add customer notes
- Optional: Enable survey package
- System creates booking in `draft` status
- Generates unique booking number: `DOOH-20251208-XXXXXX`
- Creates immutable snapshot (screen, package, vendor details)

### Step 6: Payment
**Customer → Razorpay Checkout**
- System creates Razorpay order with `manual` capture
- Sets 30-minute payment hold
- Opens Razorpay modal
- Customer completes payment
- System captures payment
- Updates status to `confirmed`

### Step 7: Content Upload
**Customer → Upload Ad Content**
- Upload digital files (mp4, jpg, png, gif)
- Max 5 files, 50MB each
- System stores in `storage/app/public/dooh_content/{booking_id}/`
- Status changes to `content_pending`

### Step 8: Content Approval
**Vendor/Admin → Review Content**
- View uploaded files
- Approve → Status changes to `content_approved`
  - If start date is today/past → Campaign goes `active` immediately
  - Else → Waits for start date
- Reject → Customer can re-upload

### Step 9: Campaign Active
**System → Auto-Activate on Start Date**
- Campaign status changes to `active`
- Customer's ad starts displaying on screen
- Displays according to slot frequency

### Step 10: Campaign Completion
**System → Auto-Complete on End Date**
- Campaign status changes to `completed`
- Final metrics recorded (if survey enabled)

### Optional: Cancellation
**Customer → Cancel Within 30 Minutes**
- If within 30 minutes of payment capture:
  - Full refund processed automatically via Razorpay
  - Payment status → `refunded`
- Booking status → `cancelled`

---

## 🔧 External API Integration

### Setup

**1. Add API credentials to .env:**
```env
DOOH_API_URL=https://api.doohprovider.com/v1
DOOH_API_KEY=your_api_key_here
```

**2. Configure in services.php:**
```php
'dooh_api' => [
    'base_url' => env('DOOH_API_URL'),
    'key' => env('DOOH_API_KEY'),
],
```

### Sync Workflow

**Vendor initiates sync:**
```
POST /api/v1/vendor/dooh/sync-screens
```

**Service fetches from external API:**
```
GET https://api.doohprovider.com/v1/screens
Authorization: Bearer {DOOH_API_KEY}
```

**System processes:**
1. Parse screen data
2. Calculate slots_per_loop
3. Map external status to internal status
4. Upsert screen (by external_screen_id)
5. Sync packages for each screen
6. Update sync_status and last_synced_at

**Post-Booking Hooks:**

After booking confirmed:
```php
$service->updateSlotAvailability(
    $screen->external_screen_id,
    $booking->start_date,
    $booking->end_date,
    $booking->slots_per_day
);
```

After cancellation:
```php
$service->releaseSlots(
    $screen->external_screen_id,
    $booking->start_date,
    $booking->end_date,
    $booking->slots_per_day
);
```

### Caching Strategy

- **Cache Duration:** 60 minutes (configurable)
- **Cache Key Pattern:** `dooh_slots_{external_screen_id}_{start_date}_{end_date}`
- **Cache Invalidation:** On booking creation/cancellation

---

## ⚙️ Configuration

### Admin Settings

**From SettingsSeeder:**

```php
'dooh_min_slots_per_day' => 6
'dooh_slot_duration_seconds' => 10
'dooh_content_review_required' => 1
'dooh_max_file_size_mb' => 50
'dooh_allowed_formats' => '["mp4", "mov", "avi", "jpg", "png", "gif"]'
```

**Booking Settings (Reused):**
```php
'booking_tax_rate' => 18 // GST/Tax %
'booking_hold_minutes' => 30 // Payment hold duration
```

### Environment Variables

**Required:**
```env
# Razorpay (Payment Gateway)
RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxx

# DOOH External API (Optional if using external inventory)
DOOH_API_URL=https://api.doohprovider.com/v1
DOOH_API_KEY=your_api_key_here
```

---

## 🧪 Testing Guide

### Manual API Testing (Postman)

**1. Browse Screens**
```
GET /api/v1/customer/dooh/screens?city=Mumbai
Authorization: Bearer {customer_token}
```

**2. Get Screen Details**
```
GET /api/v1/customer/dooh/screens/1
Authorization: Bearer {customer_token}
```

**3. Check Availability**
```
POST /api/v1/customer/dooh/packages/1/check-availability
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "start_date": "2025-12-15",
  "end_date": "2026-03-15"
}
```

**4. Create Booking**
```
POST /api/v1/customer/dooh/bookings
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "dooh_package_id": 1,
  "start_date": "2025-12-15",
  "end_date": "2026-03-15",
  "customer_notes": "Test booking",
  "survey_required": false
}
```

**5. Initiate Payment**
```
POST /api/v1/customer/dooh/bookings/1/initiate-payment
Authorization: Bearer {customer_token}
```

**6. Simulate Payment Confirmation**
```
POST /api/v1/customer/dooh/bookings/1/confirm-payment
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "razorpay_payment_id": "pay_test123",
  "razorpay_order_id": "order_test123",
  "razorpay_signature": "signature_test123"
}
```

**7. Upload Content (Multipart)**
```
POST /api/v1/customer/dooh/bookings/1/upload-content
Authorization: Bearer {customer_token}
Content-Type: multipart/form-data

files[]: [video.mp4]
files[]: [banner.jpg]
```

**8. Vendor Approves Content**
```
POST /api/v1/vendor/dooh/bookings/1/approve-content
Authorization: Bearer {vendor_token}
```

### Frontend Testing

**1. Screen Listing**
- Open `/customer/dooh`
- Test filters (city, state, min slots, search)
- Check pagination
- Verify AJAX loading spinner
- Click "View Details" button

**2. Booking Flow**
- Select a package (verify visual selection)
- Choose dates (test validation)
- Click "Check Availability"
- Verify pricing calculation
- Click "Proceed to Book"
- Complete Razorpay payment (test mode)

**3. Content Upload**
- Navigate to booking details
- Upload content files
- Verify file type/size validation
- Check upload progress

### Database Verification

**Check booking created:**
```sql
SELECT * FROM dooh_bookings WHERE booking_number = 'DOOH-20251208-XXXXXX';
```

**Check payment captured:**
```sql
SELECT payment_status, payment_captured_at, grand_total 
FROM dooh_bookings 
WHERE id = 1;
```

**Check content uploaded:**
```sql
SELECT content_files, content_status 
FROM dooh_bookings 
WHERE id = 1;
```

---

## 🐛 Troubleshooting

### Issue 1: "Package not available"

**Cause:** Insufficient slots or minimum amount not met

**Solution:**
```php
// Check available slots
$screen = DOOHScreen::find($screenId);
$availableSlots = $screen->getAvailableSlots($startDate, $endDate);
echo "Available: $availableSlots, Required: {$package->slots_per_day}";

// Check minimum amount
$pricing = $service->calculatePricing($package, $startDate, $endDate);
echo "Grand Total: {$pricing['grand_total']}, Minimum: {$screen->minimum_booking_amount}";
```

### Issue 2: Payment capture fails

**Cause:** Razorpay API error or invalid payment ID

**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log | grep Razorpay

# Verify Razorpay credentials
php artisan tinker
>>> config('services.razorpay.key_id')
>>> config('services.razorpay.key_secret')
```

### Issue 3: Content upload fails

**Cause:** File size/format validation or storage permission

**Solution:**
```bash
# Check storage permissions
chmod -R 775 storage/app/public

# Create symlink if missing
php artisan storage:link

# Check file upload limits
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Issue 4: API sync fails

**Cause:** External API unreachable or invalid credentials

**Solution:**
```php
// Test connection
$service = app(DOOHInventoryApiService::class);
$connected = $service->testConnection();
dd($connected); // Should be true

// Check API configuration
dd(config('services.dooh_api'));
```

### Issue 5: Refund not processing

**Cause:** Outside 30-minute window or payment not captured

**Solution:**
```php
$booking = DOOHBooking::find($bookingId);

// Check if within window
$withinWindow = $booking->isWithinCancellationWindow();
dd($withinWindow);

// Check payment status
dd($booking->payment_status, $booking->payment_captured_at);

// Manual refund if needed (Razorpay dashboard)
```

---

## 📝 Best Practices

### For Developers

**1. Always Use Transactions:**
```php
return DB::transaction(function () use ($data) {
    // Booking creation
    // Payment processing
    // Slot allocation
});
```

**2. Validate Before Processing:**
```php
$availability = $this->checkPackageAvailability($packageId, $startDate, $endDate);
if (!$availability['available']) {
    throw new Exception($availability['message']);
}
```

**3. Log Important Actions:**
```php
Log::info('DOOH booking created', [
    'booking_id' => $booking->id,
    'booking_number' => $booking->booking_number,
    'grand_total' => $booking->grand_total,
]);
```

**4. Use Snapshots for Immutability:**
```php
$snapshot = [
    'screen' => $screen->toArray(),
    'package' => $package->toArray(),
    'vendor' => $vendor->only(['id', 'name', 'email']),
    'captured_at' => now()->toDateTimeString(),
];
```

### For System Admins

**1. Monitor Sync Status:**
```sql
SELECT COUNT(*), sync_status FROM dooh_screens GROUP BY sync_status;
```

**2. Regular Cache Clearing:**
```bash
php artisan cache:clear
```

**3. Monitor Failed Payments:**
```sql
SELECT COUNT(*) FROM dooh_bookings WHERE payment_status = 'failed';
```

**4. Track Refund Rate:**
```sql
SELECT 
    COUNT(CASE WHEN refunded_at IS NOT NULL THEN 1 END) * 100.0 / COUNT(*) as refund_rate
FROM dooh_bookings 
WHERE payment_status = 'captured';
```

---

## 🚀 Future Enhancements

1. **Real-time Slot Availability:** WebSocket integration for live updates
2. **Campaign Analytics:** Impressions, reach, audience demographics
3. **A/B Testing:** Split testing multiple creatives
4. **Automated Content Approval:** AI-based content moderation
5. **Dynamic Pricing:** Peak hours pricing, demand-based rates
6. **Bulk Booking:** Book multiple screens at once
7. **Loyalty Discounts:** Repeat customer discounts
8. **Campaign Scheduler:** Schedule content changes mid-campaign

---

**Document Version:** 1.0  
**Last Updated:** December 8, 2025  
**Maintained By:** Development Team

---

**🎉 DOOH Package Booking Module is Production Ready!**
