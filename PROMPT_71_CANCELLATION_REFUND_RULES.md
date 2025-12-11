# PROMPT 71: Booking Cancellation & Refund Rules System

**Implementation Date:** December 11, 2025  
**Status:** ✅ Complete  
**Dependencies:** Payment Gateway Integration (PROMPT 69), Booking System

---

## Overview

Implements a **flexible, vendor-configurable cancellation and refund policy system** that:
- Allows vendors to create custom cancellation policies for their bookings
- Enforces time-based refund rules (e.g., 7-day partial refund policy)
- Prevents refunds after campaign starts (configurable)
- Supports tiered refund percentages based on cancellation timing
- Auto-calculates fees and penalties
- Integrates with payment gateway for auto-refunds

### Key Features

✅ **Vendor-Specific Policies**: Each vendor can create their own cancellation rules  
✅ **Campaign Start Enforcement**: No refund after campaign begins (configurable)  
✅ **7-Day Default Policy**: Standard policy with partial refunds  
✅ **Time-Based Tiers**: Multiple refund windows (e.g., 7 days = 100%, 3 days = 50%)  
✅ **Auto-Refund Integration**: Automatic refunds through Razorpay  
✅ **Customer Fee Calculation**: Configurable cancellation fees  
✅ **Policy Priority**: Vendor policies override global policies  

---

## Architecture

### Policy Hierarchy

```
Vendor-Specific Policy (highest priority)
    ↓
Global Policy for Role
    ↓
Default Global Policy (lowest priority)
```

### Refund Calculation Flow

```
User Cancels Booking
    ↓
Check if campaign started
    ↓ Yes → No Refund (if enforce_campaign_start = true)
    ↓ No
Calculate hours before start
    ↓
Find applicable policy (vendor first, then global)
    ↓
Find matching time window
    ↓
Calculate refund amount:
    - Refundable Amount = Booking Amount × Refund %
    - Customer Fee = Cancellation Fee (% or fixed)
    - Final Refund = Refundable Amount - Customer Fee
    ↓
Create BookingRefund record
    ↓
Auto-refund (if enabled) OR Manual processing
```

---

## Database Schema

### Enhanced Table: `cancellation_policies`

**New Columns Added:**

```sql
-- Vendor ownership
vendor_id BIGINT UNSIGNED NULL
    COMMENT 'NULL = global/admin policy, specific ID = vendor custom policy'

-- Campaign start enforcement  
enforce_campaign_start BOOLEAN DEFAULT TRUE
    COMMENT 'No refund after campaign/booking starts'

-- Partial refund support
allow_partial_refund BOOLEAN DEFAULT TRUE
    COMMENT 'Allow partial refunds based on time windows'
```

**Complete Schema:**

```sql
CREATE TABLE cancellation_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Ownership
    vendor_id BIGINT UNSIGNED NULL,
    
    -- Policy identification
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    
    -- Applicability
    applies_to ENUM('all', 'customer', 'vendor', 'admin') DEFAULT 'all',
    booking_type ENUM('ooh', 'dooh', 'pos') NULL COMMENT 'null = all types',
    
    -- Time-based cancellation rules
    time_windows JSON NOT NULL COMMENT '
        [{
            hours_before: 168,
            refund_percent: 100,
            customer_fee_percent: 0,
            vendor_penalty_percent: null
        }]
    ',
    
    -- Customer cancellation fees
    customer_fee_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    customer_fee_value DECIMAL(10,2) DEFAULT 0,
    customer_min_fee DECIMAL(10,2) NULL,
    customer_max_fee DECIMAL(10,2) NULL,
    
    -- Vendor cancellation penalties
    vendor_penalty_type ENUM('percentage', 'fixed') DEFAULT 'percentage',
    vendor_penalty_value DECIMAL(10,2) DEFAULT 0,
    vendor_min_penalty DECIMAL(10,2) NULL,
    vendor_max_penalty DECIMAL(10,2) NULL,
    
    -- Refund settings
    auto_refund_enabled BOOLEAN DEFAULT TRUE,
    enforce_campaign_start BOOLEAN DEFAULT TRUE,
    allow_partial_refund BOOLEAN DEFAULT TRUE,
    refund_processing_days INT DEFAULT 7,
    refund_method ENUM('original', 'wallet', 'manual') DEFAULT 'original',
    
    -- POS-specific
    pos_auto_refund_disabled BOOLEAN DEFAULT TRUE,
    pos_refund_note TEXT NULL,
    
    -- Admin override
    allow_admin_override BOOLEAN DEFAULT TRUE,
    override_conditions TEXT NULL,
    
    -- Additional rules
    min_hours_before_start INT NULL,
    max_hours_before_start INT NULL,
    min_booking_amount DECIMAL(10,2) NULL,
    max_booking_amount DECIMAL(10,2) NULL,
    
    -- Audit
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign keys
    FOREIGN KEY (vendor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_vendor_id (vendor_id),
    INDEX idx_is_active (is_active),
    INDEX idx_is_default (is_default),
    INDEX idx_applies_to (applies_to),
    INDEX idx_booking_type (booking_type)
);
```

---

## Default 7-Day Policy

**Seeder:** `DefaultCancellationPolicySeeder`

### Policy Rules

| Cancellation Window | Refund % | Customer Fee | Notes |
|---------------------|----------|--------------|-------|
| **7+ days before** | 100% | 0% | Full refund, no fee |
| **3-7 days before** | 50% | 10% | Partial refund |
| **1-3 days before** | 25% | 15% | Minimal refund |
| **< 24 hours OR after campaign start** | 0% | 100% | No refund |

### Implementation

```php
'time_windows' => [
    [
        'hours_before' => 168,  // 7 days
        'refund_percent' => 100,
        'customer_fee_percent' => 0,
    ],
    [
        'hours_before' => 72,   // 3 days
        'refund_percent' => 50,
        'customer_fee_percent' => 10,
    ],
    [
        'hours_before' => 24,   // 1 day
        'refund_percent' => 25,
        'customer_fee_percent' => 15,
    ],
    [
        'hours_before' => 0,    // < 24 hours
        'refund_percent' => 0,
        'customer_fee_percent' => 100,
    ],
],
'enforce_campaign_start' => true,
```

**Run Seeder:**
```bash
php artisan db:seed --class=DefaultCancellationPolicySeeder
```

---

## Core Components

### 1. CancellationPolicy Model (Enhanced)

**File:** `app/Models/CancellationPolicy.php`

**New Relationships:**

```php
public function vendor(): BelongsTo
{
    return $this->belongsTo(User::class, 'vendor_id');
}
```

**New Scopes:**

```php
// Get global (admin-created) policies
public function scopeGlobal($query)
{
    return $query->whereNull('vendor_id');
}

// Get vendor-specific policies
public function scopeForVendor($query, ?int $vendorId)
{
    return $query->where('vendor_id', $vendorId);
}
```

**New Helper Methods:**

```php
// Check if policy is vendor-specific
public function isVendorPolicy(): bool
{
    return $this->vendor_id !== null;
}

// Check if policy is global (admin-created)
public function isGlobalPolicy(): bool
{
    return $this->vendor_id === null;
}

// Check if refund is allowed after campaign starts
public function allowsRefundAfterCampaignStart(): bool
{
    return !$this->enforce_campaign_start;
}

// Calculate refund with campaign start enforcement
public function calculateRefundWithCampaignCheck(
    float $bookingAmount,
    int $hoursBeforeStart,
    bool $campaignStarted,
    string $cancelledByRole = 'customer'
): array
```

**Campaign Start Enforcement:**

```php
if ($campaignStarted && $this->enforce_campaign_start) {
    return [
        'refundable_amount' => 0,
        'refund_percent' => 0,
        'customer_fee' => $bookingAmount,
        'vendor_penalty' => 0,
        'refund_amount' => 0,
        'time_window' => null,
        'campaign_started' => true,
        'message' => 'No refund allowed after campaign has started',
    ];
}
```

---

### 2. BookingCancellationService (Enhanced)

**File:** `app/Services/BookingCancellationService.php`

**Enhanced Policy Lookup:**

```php
protected function findApplicablePolicy(
    string $bookingType,
    string $role,
    float $amount,
    int $hoursBeforeStart,
    ?int $vendorId = null
): ?CancellationPolicy {
    // First try vendor-specific policy
    if ($vendorId) {
        $vendorPolicy = CancellationPolicy::active()
            ->forVendor($vendorId)
            ->forRole($role)
            ->forBookingType($bookingType)
            ->get()
            ->first(fn($policy) => $policy->appliesTo([
                'amount' => $amount,
                'hours_before_start' => $hoursBeforeStart,
            ]));

        if ($vendorPolicy) {
            return $vendorPolicy; // Vendor policy has priority
        }
    }

    // Fallback to global policy
    return CancellationPolicy::active()
        ->global()
        ->forRole($role)
        ->forBookingType($bookingType)
        ->get()
        ->first(fn($policy) => $policy->appliesTo([
            'amount' => $amount,
            'hours_before_start' => $hoursBeforeStart,
        ]));
}
```

**Campaign Start Detection:**

```php
protected function hasCampaignStarted($booking): bool
{
    // Check for campaign_started_at field (DOOH bookings)
    if (isset($booking->campaign_started_at) && $booking->campaign_started_at) {
        return Carbon::parse($booking->campaign_started_at)->isPast();
    }

    // Check for active status (implies campaign started)
    if (isset($booking->status) && $booking->status === 'active') {
        return true;
    }

    // Check if start date has passed
    $startDate = $booking->start_date ?? $booking->campaign_start_date ?? null;
    if ($startDate) {
        return Carbon::parse($startDate)->isPast();
    }

    return false;
}
```

---

### 3. VendorCancellationPolicyController

**File:** `app/Http/Controllers/Vendor/CancellationPolicyController.php`

**Routes:**

```php
// Vendor Panel
GET    /vendor/cancellation-policies           - List policies
GET    /vendor/cancellation-policies/create    - Create form
POST   /vendor/cancellation-policies           - Store policy
GET    /vendor/cancellation-policies/{id}/edit - Edit form
PUT    /vendor/cancellation-policies/{id}      - Update policy
DELETE /vendor/cancellation-policies/{id}      - Delete policy
POST   /vendor/cancellation-policies/{id}/toggle-status - Toggle active
POST   /vendor/cancellation-policies/preview-refund - Preview calculation
```

**Key Methods:**

#### index()
Displays vendor's custom policies and global default policies.

```php
$vendorPolicies = $policies->filter(fn($p) => $p->vendor_id === $vendor->id);
$globalPolicies = $policies->filter(fn($p) => $p->vendor_id === null);
```

#### store(Request $request)
Creates vendor-specific policy:

```php
$policyData = [
    'vendor_id' => $vendor->id,
    'is_default' => false, // Vendor policies cannot be system default
    'applies_to' => 'customer', // Applies to customer cancellations
    'enforce_campaign_start' => true, // Default enforcement
    // ... other fields
];
```

#### previewRefund(Request $request)
Calculates refund preview for given policy and scenario:

```php
POST /vendor/cancellation-policies/preview-refund
{
    "policy_id": 5,
    "booking_amount": 50000,
    "hours_before_start": 100
}

Response:
{
    "calculation": {
        "refundable_amount": 50000,
        "refund_percent": 100,
        "customer_fee": 0,
        "refund_amount": 50000
    },
    "formatted": {
        "booking_amount": "₹50,000.00",
        "refundable_amount": "₹50,000.00",
        "customer_fee": "₹0.00",
        "refund_amount": "₹50,000.00",
        "refund_percent": "100%"
    }
}
```

---

## Usage Examples

### Example 1: Vendor Creates Custom Policy

```php
// Vendor creates stricter policy
POST /vendor/cancellation-policies
{
    "name": "Premium Hoarding Cancellation Policy",
    "description": "Stricter policy for premium locations",
    "is_active": true,
    "booking_type": "ooh",
    "time_windows": [
        {
            "hours_before": 336,  // 14 days
            "refund_percent": 100,
            "customer_fee_percent": 0
        },
        {
            "hours_before": 168,  // 7 days
            "refund_percent": 60,
            "customer_fee_percent": 15
        },
        {
            "hours_before": 72,   // 3 days
            "refund_percent": 30,
            "customer_fee_percent": 20
        },
        {
            "hours_before": 0,    // < 3 days
            "refund_percent": 0,
            "customer_fee_percent": 100
        }
    ],
    "customer_fee_type": "percentage",
    "customer_fee_value": 10,
    "customer_min_fee": 1000,
    "customer_max_fee": 25000,
    "auto_refund_enabled": true,
    "enforce_campaign_start": true,
    "refund_processing_days": 5
}
```

### Example 2: Cancellation After Campaign Starts

```php
// Booking details
$booking->start_date = '2025-12-10';
$booking->campaign_started_at = '2025-12-10 00:00:00';
$booking->total_amount = 100000;

// User tries to cancel on 2025-12-11
$now = Carbon::parse('2025-12-11');

// Service detects campaign started
$campaignStarted = $this->hasCampaignStarted($booking); // true

// Policy calculation with enforcement
$calculation = $policy->calculateRefundWithCampaignCheck(
    100000,    // booking amount
    -24,       // hours before start (negative = past)
    true,      // campaign started
    'customer'
);

// Result: No refund
[
    'refundable_amount' => 0,
    'refund_percent' => 0,
    'customer_fee' => 100000,
    'refund_amount' => 0,
    'campaign_started' => true,
    'message' => 'No refund allowed after campaign has started'
]
```

### Example 3: Cancellation Within 7 Days (Default Policy)

```php
// Booking details
$booking->start_date = '2025-12-20';
$booking->total_amount = 50000;

// User cancels on 2025-12-14 (6 days before)
$hoursBeforeStart = 144; // 6 days

// Default policy applies
$calculation = $policy->calculateRefund(50000, 144, 'customer');

// Result: 50% refund
[
    'refundable_amount' => 25000,  // 50% of 50000
    'refund_percent' => 50,
    'customer_fee' => 5000,        // 10% fee
    'refund_amount' => 20000,      // 25000 - 5000
    'time_window' => [
        'hours_before' => 72,
        'refund_percent' => 50,
        'customer_fee_percent' => 10
    ]
]
```

### Example 4: Vendor Policy Overrides Global

```php
// Scenario: Booking with Vendor ID 5
$booking->vendor_id = 5;
$booking->total_amount = 30000;
$hoursBeforeStart = 100;

// Service finds vendor policy first
$policy = $this->findApplicablePolicy(
    'ooh',          // booking type
    'customer',     // role
    30000,          // amount
    100,            // hours before
    5               // vendor_id
);

// If Vendor 5 has custom policy → uses vendor policy
// If not → falls back to global default policy
```

---

## Integration with Existing Systems

### Booking Cancellation Flow

```php
// Customer cancels booking
POST /api/v1/bookings/{id}/cancel
{
    "reason": "Change of plans"
}

// BookingController calls BookingCancellationService
$refund = $cancellationService->cancelBooking(
    $booking,
    Auth::id(),        // cancelled_by
    'customer',        // role
    $request->reason,  // reason
    null,              // policy_id (auto-detect)
    false              // admin_override
);

// Service flow:
1. Get vendor_id from booking
2. Calculate hours_before_start
3. Check if campaign started
4. Find applicable policy (vendor first, then global)
5. Calculate refund with campaign enforcement
6. Create BookingRefund record
7. Update booking status = 'cancelled'
8. Process auto-refund if enabled
```

### Timeline Integration

```php
// After cancellation, timeline event added
$booking->addTimelineEvent(
    'booking_cancelled',
    "Booking cancelled by customer",
    [
        'cancelled_by' => Auth::user()->name,
        'refund_amount' => $refund->refund_amount,
        'refund_percent' => $calculation['refund_percent'],
        'policy_used' => $policy->name,
        'campaign_started' => $campaignStarted,
    ]
);
```

---

## Vendor Panel UI Flow

### 1. List Policies

**Page:** `/vendor/cancellation-policies`

**Display:**
- Vendor's Custom Policies (editable)
- Global Policies (read-only, shown for reference)

**Actions:**
- Create New Policy
- Edit Policy
- Toggle Active/Inactive
- Delete Policy
- Preview Refund Calculation

### 2. Create/Edit Policy Form

**Fields:**
- Policy Name
- Description
- Booking Type (OOH/DOOH/POS or All)
- Active Status

**Time Windows (Repeatable):**
- Hours Before Start
- Refund Percentage
- Customer Fee Percentage (optional)

**Customer Fees:**
- Fee Type (Percentage/Fixed)
- Fee Value
- Min/Max Fee Limits

**Refund Settings:**
- Auto-Refund Enabled
- Enforce Campaign Start
- Allow Partial Refund
- Processing Days

**Preview Section:**
- Live calculator showing refund for sample scenarios

### 3. Preview Refund Calculation

**Interactive Calculator:**
```
Booking Amount: ₹ [50000]
Hours Before Start: [100]

[Calculate]

Results:
✓ Time Window Matched: 3-7 days before
✓ Refund Percentage: 50%
✓ Refundable Amount: ₹25,000.00
✓ Customer Fee (10%): ₹5,000.00
✓ Final Refund: ₹20,000.00
```

---

## Testing Scenarios

### Scenario 1: Campaign Started - No Refund

```php
Given: Booking with start_date = '2025-12-10'
  And: campaign_started_at = '2025-12-10 00:00:00'
  And: Current date = '2025-12-11'
  And: Policy enforce_campaign_start = true
  
When: Customer cancels booking
Then: Refund amount = 0
  And: Customer fee = full booking amount
  And: Message = "No refund allowed after campaign has started"
```

### Scenario 2: Vendor Policy Overrides Global

```php
Given: Global policy: 7 days = 100%, 3 days = 50%
  And: Vendor policy: 14 days = 100%, 7 days = 60%
  And: Booking belongs to vendor with custom policy
  And: Cancellation 10 days before start
  
When: Customer cancels
Then: Vendor policy applies
  And: Refund percent = 60% (not 100% from global)
```

### Scenario 3: No Vendor Policy - Falls Back to Global

```php
Given: Vendor has no custom policy
  And: Global default policy exists
  And: Cancellation 5 days before start
  
When: Customer cancels
Then: Global policy applies
  And: Refund percent = 50% (3-7 day window)
```

### Scenario 4: Partial Refund with Fees

```php
Given: Booking amount = ₹50,000
  And: Cancellation 4 days before start
  And: Policy: 3-7 days = 50% refund, 10% fee
  
When: Customer cancels
Then: Refundable amount = ₹25,000 (50% of 50000)
  And: Customer fee = ₹5,000 (10% of 50000)
  And: Final refund = ₹20,000 (25000 - 5000)
```

---

## Admin Panel Integration

**Existing Routes:**
```php
// Admin can view all policies (global + vendor-specific)
GET /admin/cancellation-policies

// Admin can create global policies
POST /admin/cancellation-policies

// Admin can edit any policy
PUT /admin/cancellation-policies/{id}

// Admin can view refund requests
GET /admin/refunds
```

---

## API Endpoints

### Vendor API Routes

```php
GET    /vendor/cancellation-policies
       → List vendor's policies + global defaults

POST   /vendor/cancellation-policies
       → Create vendor-specific policy

PUT    /vendor/cancellation-policies/{id}
       → Update vendor policy

DELETE /vendor/cancellation-policies/{id}
       → Delete vendor policy (if no refunds using it)

POST   /vendor/cancellation-policies/{id}/toggle-status
       → Activate/deactivate policy

POST   /vendor/cancellation-policies/preview-refund
       → Preview refund calculation
```

---

## Migration Guide

### Step 1: Run Migration

```bash
php artisan migrate --path=database/migrations/2025_12_11_150000_add_vendor_policies_to_cancellation_policies.php
```

### Step 2: Run Seeder (Create Default Policy)

```bash
php artisan db:seed --class=DefaultCancellationPolicySeeder
```

### Step 3: Verify

```sql
SELECT * FROM cancellation_policies WHERE is_default = 1;
-- Should show: "Default 7-Day Cancellation Policy"
```

---

## Configuration

### Environment Variables

```env
# Refund settings
REFUND_AUTO_ENABLED=true
REFUND_PROCESSING_DAYS=7
REFUND_MIN_AMOUNT=100

# Campaign start enforcement
ENFORCE_CAMPAIGN_START=true
ALLOW_REFUND_AFTER_START=false
```

---

## Validation Rules

### Create/Update Vendor Policy

```php
'name' => 'required|string|max:255',
'description' => 'nullable|string',
'is_active' => 'boolean',
'booking_type' => 'nullable|in:ooh,dooh,pos',
'time_windows' => 'required|array|min:1',
'time_windows.*.hours_before' => 'required|integer|min:0',
'time_windows.*.refund_percent' => 'required|integer|min:0|max:100',
'time_windows.*.customer_fee_percent' => 'nullable|integer|min:0|max:100',
'customer_fee_type' => 'required|in:percentage,fixed',
'customer_fee_value' => 'required|numeric|min:0',
'customer_min_fee' => 'nullable|numeric|min:0',
'customer_max_fee' => 'nullable|numeric|min:0',
'auto_refund_enabled' => 'boolean',
'enforce_campaign_start' => 'boolean',
'allow_partial_refund' => 'boolean',
'refund_processing_days' => 'required|integer|min:1|max:30',
```

---

## Logging

All cancellation operations are logged:

```php
Log::info('Booking cancellation processed', [
    'booking_id' => $booking->id,
    'refund_id' => $refund->id,
    'refund_amount' => $refund->refund_amount,
    'campaign_started' => $campaignStarted,
    'policy_type' => $policy ? ($policy->isVendorPolicy() ? 'vendor' : 'global') : 'none',
]);

Log::info('Vendor cancellation policy created', [
    'vendor_id' => $vendor->id,
    'policy_id' => $policy->id,
    'policy_name' => $policy->name,
]);
```

---

## Security Considerations

1. **Vendor Isolation**: Vendors can only view/edit their own policies
2. **Policy Deletion**: Prevents deletion if policy used in refunds
3. **Admin Override**: Admins can override any policy calculation
4. **Audit Trail**: All policy changes logged with created_by/updated_by

---

## Performance Optimization

1. **Indexed Queries**: vendor_id, is_active, booking_type indexed
2. **Policy Caching**: Active policies can be cached per vendor
3. **Eager Loading**: Policies loaded with vendor relationship

---

## Summary

### What Was Implemented

✅ **Vendor-Specific Policies** - Vendors can create custom cancellation rules  
✅ **Campaign Start Enforcement** - No refund after campaign begins  
✅ **7-Day Default Policy** - Standard tiered refund system  
✅ **Policy Priority System** - Vendor policies override global  
✅ **Auto-Refund Integration** - Seamless Razorpay integration  
✅ **Flexible Time Windows** - Multiple refund tiers  
✅ **Fee Calculation** - Customer fees and vendor penalties  
✅ **Preview Calculator** - Live refund preview  

### Files Created/Modified

**New Files (3):**
- `database/migrations/2025_12_11_150000_add_vendor_policies_to_cancellation_policies.php`
- `app/Http/Controllers/Vendor/CancellationPolicyController.php`
- `database/seeders/DefaultCancellationPolicySeeder.php`

**Modified Files (3):**
- `app/Models/CancellationPolicy.php` - Added vendor support, campaign enforcement
- `app/Services/BookingCancellationService.php` - Vendor policy lookup, campaign detection
- `routes/web.php` - Added vendor cancellation policy routes

### Next Steps

1. Create vendor UI views (index, create, edit forms)
2. Add refund preview widget to booking pages
3. Create customer-facing cancellation flow
4. Add email notifications for policy changes
5. Build analytics dashboard for refund metrics

---

## Support

**Related Documentation:**
- Payment Gateway Integration (PROMPT 69)
- Booking System Documentation
- Refund Management Guide

**Contact:** Development Team

