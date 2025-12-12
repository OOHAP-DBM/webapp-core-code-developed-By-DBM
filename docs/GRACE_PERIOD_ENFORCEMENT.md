# Grace Period Enforcement Documentation

## Overview
The Grace Period Enforcement system prevents last-minute bookings by requiring customers to book campaigns a minimum number of days in advance. This protects vendors from rushed operations and ensures adequate preparation time.

## Business Logic

### Grace Period Hierarchy
1. **Vendor-Specific Grace Period** (Priority 1): Each hoarding can have a custom `grace_period_days` value
2. **Admin Default Grace Period** (Priority 2): System-wide default from `BOOKING_GRACE_PERIOD_DAYS` in `.env` (default: 2 days)

### Calculation Formula
```
Earliest Allowed Start Date = Today + Applicable Grace Days
```

Where:
- **Applicable Grace Days** = `hoarding.grace_period_days` ?? `BOOKING_GRACE_PERIOD_DAYS`
- **Today** = Current date at midnight (00:00:00)

### Enforcement Points
All booking flows validate campaign start dates:
1. ✅ **Enquiry Creation** - Customer enquiries
2. ✅ **Direct Booking** - Immediate bookings without quotation
3. ✅ **Quotation → Booking** - Converting quotes to bookings
4. ✅ **POS Booking** - Vendor point-of-sale bookings
5. ✅ **Frontend Calendars** - Date picker minimum dates

---

## Database Schema

### Migration
**File**: `database/migrations/2025_12_11_200000_add_grace_period_to_hoardings.php`

```php
Schema::table('hoardings', function (Blueprint $table) {
    $table->integer('grace_period_days')->nullable()->after('enable_weekly_booking');
    $table->index('grace_period_days');
});
```

**Column Details**:
- **Type**: `integer`, nullable
- **Position**: After `enable_weekly_booking`
- **Default**: `NULL` (uses admin default)
- **Indexed**: Yes (for performance)
- **Range**: 0-90 days (validated in controllers)

---

## Backend Components

### 1. Hoarding Model
**File**: `app/Models/Hoarding.php`

**Added to `$fillable`**:
```php
'grace_period_days',
```

**New Methods**:

#### `getGracePeriodDays(): int`
Returns the applicable grace period for this hoarding.
```php
public function getGracePeriodDays(): int
{
    return $this->grace_period_days ?? (int) config('booking.grace_period_days', env('BOOKING_GRACE_PERIOD_DAYS', 2));
}
```

#### `getEarliestAllowedStartDate(): Carbon`
Calculates the earliest allowed campaign start date.
```php
public function getEarliestAllowedStartDate(): Carbon
{
    return Carbon::today()->addDays($this->getGracePeriodDays());
}
```

#### `isStartDateAllowed(Carbon $startDate): bool`
Validates if a given start date meets grace period requirements.
```php
public function isStartDateAllowed(Carbon $startDate): bool
{
    return $startDate->greaterThanOrEqualTo($this->getEarliestAllowedStartDate());
}
```

#### `getGracePeriodValidationMessage(): string`
Returns user-friendly validation error message.
```php
public function getGracePeriodValidationMessage(): string
{
    $days = $this->getGracePeriodDays();
    $earliestDate = $this->getEarliestAllowedStartDate()->format('d M Y');
    
    return "Campaign start date must be at least {$days} day(s) from today. Earliest allowed date: {$earliestDate}";
}
```

---

### 2. GracePeriodService
**File**: `app/Services/GracePeriodService.php`

**Purpose**: Centralized service for grace period logic and validation.

**Key Methods**:

#### `calculateEarliestStartDate(Hoarding $hoarding): Carbon`
```php
public function calculateEarliestStartDate(Hoarding $hoarding): Carbon
{
    return $hoarding->getEarliestAllowedStartDate();
}
```

#### `validateStartDate(Hoarding $hoarding, $requestedStartDate): bool`
```php
public function validateStartDate(Hoarding $hoarding, $requestedStartDate): bool
{
    $startDate = $requestedStartDate instanceof Carbon 
        ? $requestedStartDate 
        : Carbon::parse($requestedStartDate);

    return $hoarding->isStartDateAllowed($startDate);
}
```

#### `addValidationRule(Validator $validator, string $field, Hoarding $hoarding): void`
Adds grace period validation to Laravel validator:
```php
public function addValidationRule(Validator $validator, string $field, Hoarding $hoarding): void
{
    $validator->after(function ($validator) use ($field, $hoarding) {
        $startDate = $validator->getData()[$field] ?? null;
        
        if ($startDate && !$this->validateStartDate($hoarding, $startDate)) {
            $validator->errors()->add($field, $this->getValidationMessage($hoarding));
        }
    });
}
```

#### `getGracePeriodDetails(Hoarding $hoarding): array`
Returns grace period information for API responses:
```php
public function getGracePeriodDetails(Hoarding $hoarding): array
{
    return [
        'grace_period_days' => $hoarding->getGracePeriodDays(),
        'earliest_allowed_start_date' => $hoarding->getEarliestAllowedStartDate()->format('Y-m-d'),
        'earliest_allowed_start_date_formatted' => $hoarding->getEarliestAllowedStartDate()->format('d M Y'),
        'is_using_default' => $hoarding->grace_period_days === null,
        'default_grace_period_days' => (int) config('booking.grace_period_days', env('BOOKING_GRACE_PERIOD_DAYS', 2)),
    ];
}
```

#### `getSuggestedGracePeriods(): array`
Provides preset options for vendor selection:
```php
public function getSuggestedGracePeriods(): array
{
    return [
        0 => 'Same day booking (No grace period)',
        1 => '1 day in advance',
        2 => '2 days in advance (Recommended)',
        3 => '3 days in advance',
        5 => '5 days in advance',
        7 => '1 week in advance',
        14 => '2 weeks in advance',
        30 => '1 month in advance',
    ];
}
```

---

## API Controllers Updated

### 1. EnquiryController
**File**: `Modules/Enquiries/Controllers/Api/EnquiryController.php`

**Changes**:
```php
use App\Services\GracePeriodService;
use App\Models\Hoarding;

protected GracePeriodService $gracePeriodService;

public function __construct(EnquiryService $service, GracePeriodService $gracePeriodService)
{
    $this->service = $service;
    $this->gracePeriodService = $gracePeriodService;
}

public function store(Request $request): JsonResponse
{
    $hoarding = Hoarding::findOrFail($request->hoarding_id);
    
    $validator = Validator::make($request->all(), [
        'hoarding_id' => 'required|exists:hoardings,id',
        'preferred_start_date' => 'required|date|after_or_equal:today',
        'preferred_end_date' => 'required|date|after:preferred_start_date',
        'duration_type' => 'required|in:days,weeks,months',
        'message' => 'nullable|string|max:1000',
    ]);

    // Add grace period validation
    $this->gracePeriodService->addValidationRule($validator, 'preferred_start_date', $hoarding);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }
    
    // ... rest of method
}
```

### 2. EnquiryWorkflowController
**File**: `Modules/Enquiries/Controllers/Api/EnquiryWorkflowController.php`

Similar pattern with `GracePeriodService` injection and validation.

### 3. QuoteRequestController
**File**: `app/Http/Controllers/Api/V1/QuoteRequestController.php`

**Changes**:
```php
use App\Services\GracePeriodService;

protected $gracePeriodService;

public function __construct(QuoteRequestService $requestService, GracePeriodService $gracePeriodService)
{
    $this->requestService = $requestService;
    $this->gracePeriodService = $gracePeriodService;
}

public function store(Request $request)
{
    $hoarding = \App\Models\Hoarding::findOrFail($request->hoarding_id);
    
    $validator = \Validator::make($request->all(), [
        'hoarding_id' => 'required|exists:hoardings,id',
        'preferred_start_date' => 'required|date|after:today',
        // ... other rules
    ]);

    $this->gracePeriodService->addValidationRule($validator, 'preferred_start_date', $hoarding);
    $validated = $validator->validate();
    
    // ... rest of method
}
```

### 4. DirectBookingController
**File**: `Modules/Bookings/Controllers/Api/DirectBookingController.php`

**Changes**:
- Injected `GracePeriodService`
- Updated `checkAvailability()` method
- Updated `store()` method

```php
use App\Services\GracePeriodService;
use App\Models\Hoarding;

protected GracePeriodService $gracePeriodService;

public function checkAvailability(Request $request): JsonResponse
{
    $hoarding = Hoarding::findOrFail($request->hoarding_id);
    
    $validator = \Validator::make($request->all(), [
        'hoarding_id' => 'required|integer|exists:hoardings,id',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after:start_date',
    ]);

    $this->gracePeriodService->addValidationRule($validator, 'start_date', $hoarding);
    $validated = $validator->validate();
    
    // ... rest of method
}
```

### 5. POSBookingController
**File**: `Modules/POS/Controllers/Api/POSBookingController.php`

**Changes**:
```php
use App\Services\GracePeriodService;
use App\Models\Hoarding;

protected GracePeriodService $gracePeriodService;

public function store(Request $request): JsonResponse
{
    $validator = \Validator::make($request->all(), [
        // ... booking fields
        'hoarding_id' => 'required_if:booking_type,ooh|exists:hoardings,id',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after:start_date',
    ]);

    // Add grace period validation if hoarding_id is present
    if ($request->hoarding_id) {
        $hoarding = Hoarding::findOrFail($request->hoarding_id);
        $this->gracePeriodService->addValidationRule($validator, 'start_date', $hoarding);
    }
    
    $validated = $validator->validate();
    
    // ... rest of method
}
```

---

## Frontend Components

### 1. Customer Enquiry Forms

#### File: `resources/views/customer/enquiries/create.blade.php`
```blade
<input type="date" 
       class="form-control" 
       id="preferred_start_date" 
       name="preferred_start_date" 
       min="{{ $hoarding->getEarliestAllowedStartDate()->format('Y-m-d') }}"
       required>
<small class="text-muted">
    Earliest date: {{ $hoarding->getEarliestAllowedStartDate()->format('d M Y') }} 
    ({{ $hoarding->getGracePeriodDays() }} day grace period)
</small>
```

#### File: `resources/views/customer/enquiry-create.blade.php`
```blade
<label class="form-label">Start Date <span class="text-danger">*</span></label>
<input type="date" name="start_date" class="form-control" 
       min="{{ $hoarding->getEarliestAllowedStartDate()->format('Y-m-d') }}" required>
<small class="text-muted">
    Minimum {{ $hoarding->getGracePeriodDays() }} day(s) advance booking required
</small>
```

### 2. Vendor Hoarding Forms

#### File: `resources/views/vendor/hoardings/create.blade.php`
```blade
<!-- Grace Period -->
<div class="mb-3">
    <label for="grace_period_days" class="form-label">
        Grace Period (Days)
        <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" 
           title="Minimum days in advance customers must book. Leave empty to use system default (2 days)."></i>
    </label>
    <input type="number" min="0" max="90" class="form-control" 
        id="grace_period_days" name="grace_period_days" 
        value="{{ old('grace_period_days') }}"
        placeholder="Default: 2 days">
    <small class="text-muted">
        Customers can't book campaigns starting earlier than this grace period. 
        Leave blank to use the system default.
    </small>
</div>
```

#### File: `resources/views/vendor/hoardings/edit.blade.php`
```blade
<input type="number" min="0" max="90" class="form-control" 
    id="grace_period_days" name="grace_period_days" 
    value="{{ old('grace_period_days', $hoarding->grace_period_days) }}"
    placeholder="Default: 2 days">
<small class="text-muted">
    @if($hoarding->grace_period_days)
        Current: <strong>{{ $hoarding->grace_period_days }} days</strong>
    @else
        Currently using system default: <strong>2 days</strong>
    @endif
</small>
```

---

## Web Controllers Updated

### Vendor HoardingController
**File**: `app/Http/Controllers/Web/Vendor/HoardingController.php`

**Changes**:

#### `store()` Method
```php
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'description' => 'nullable|string',
    'address' => 'required|string',
    'lat' => 'required|numeric|between:-90,90',
    'lng' => 'required|numeric|between:-180,180',
    'weekly_price' => 'nullable|numeric|min:0',
    'monthly_price' => 'required|numeric|min:0',
    'grace_period_days' => 'nullable|integer|min:0|max:90', // NEW
    'enable_weekly_booking' => 'boolean',
    'type' => 'required|in:billboard,digital,transit,street_furniture,wallscape,mobile',
    'status' => 'nullable|in:draft,pending_approval,active,inactive',
]);
```

#### `update()` Method
Same validation rules as `store()`.

---

## Configuration

### Environment Variables
**File**: `.env`

```env
# Grace Period Settings
BOOKING_GRACE_PERIOD_DAYS=2

# Related Settings
BOOKING_HOLD_MINUTES=30
MAX_FUTURE_BOOKING_MONTHS=12
AUTO_APPROVAL_ENABLED=false
```

### Config File (Optional)
**File**: `config/booking.php` (can be created)

```php
return [
    'grace_period_days' => env('BOOKING_GRACE_PERIOD_DAYS', 2),
    'hold_minutes' => env('BOOKING_HOLD_MINUTES', 30),
    'max_future_months' => env('MAX_FUTURE_BOOKING_MONTHS', 12),
    'auto_approval_enabled' => env('AUTO_APPROVAL_ENABLED', false),
];
```

---

## API Response Examples

### Hoarding Details with Grace Period
```json
{
  "id": 123,
  "title": "Times Square Billboard",
  "grace_period": {
    "days": 3,
    "earliest_allowed_start_date": "2025-12-14",
    "earliest_allowed_start_date_formatted": "14 Dec 2025",
    "is_using_default": false,
    "default_grace_period_days": 2
  }
}
```

### Validation Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "preferred_start_date": [
      "Campaign start date must be at least 3 day(s) from today. Earliest allowed date: 14 Dec 2025"
    ]
  }
}
```

---

## Testing Scenarios

### Scenario 1: Vendor-Specific Grace Period
- **Hoarding**: `grace_period_days = 5`
- **Today**: 2025-12-11
- **Earliest Start**: 2025-12-16 (11 + 5 days)
- **Customer selects**: 2025-12-14 → ❌ **REJECTED**
- **Customer selects**: 2025-12-17 → ✅ **ACCEPTED**

### Scenario 2: Admin Default Grace Period
- **Hoarding**: `grace_period_days = NULL`
- **Admin Default**: `BOOKING_GRACE_PERIOD_DAYS=2`
- **Today**: 2025-12-11
- **Earliest Start**: 2025-12-13 (11 + 2 days)
- **Customer selects**: 2025-12-12 → ❌ **REJECTED**
- **Customer selects**: 2025-12-14 → ✅ **ACCEPTED**

### Scenario 3: Zero Grace Period (Same Day)
- **Hoarding**: `grace_period_days = 0`
- **Today**: 2025-12-11
- **Earliest Start**: 2025-12-11 (today)
- **Customer selects**: 2025-12-11 → ✅ **ACCEPTED**

### Scenario 4: POS Booking (OOH)
- **Booking Type**: `ooh` (hoarding-based)
- **Hoarding**: `grace_period_days = 3`
- **Validation**: Grace period enforced

### Scenario 5: POS Booking (DOOH)
- **Booking Type**: `dooh` (package-based)
- **Hoarding ID**: Not provided
- **Validation**: Grace period skipped (no hoarding)

---

## File Summary

### Created Files (3)
1. **database/migrations/2025_12_11_200000_add_grace_period_to_hoardings.php** - Migration
2. **app/Services/GracePeriodService.php** - Service class (149 lines)
3. **docs/GRACE_PERIOD_ENFORCEMENT.md** - This documentation

### Modified Files (10)

#### Models (1)
1. **app/Models/Hoarding.php**
   - Added `grace_period_days` to `$fillable`
   - Added 4 grace period methods

#### Controllers (5)
1. **Modules/Enquiries/Controllers/Api/EnquiryController.php**
   - Injected `GracePeriodService`
   - Added validation in `store()`

2. **Modules/Enquiries/Controllers/Api/EnquiryWorkflowController.php**
   - Injected `GracePeriodService`
   - Added validation in `store()`

3. **app/Http/Controllers/Api/V1/QuoteRequestController.php**
   - Injected `GracePeriodService`
   - Added validation in `store()`

4. **Modules/Bookings/Controllers/Api/DirectBookingController.php**
   - Injected `GracePeriodService`
   - Added validation in `checkAvailability()` and `store()`

5. **Modules/POS/Controllers/Api/POSBookingController.php**
   - Injected `GracePeriodService`
   - Added conditional validation in `store()`

#### Web Controllers (1)
6. **app/Http/Controllers/Web/Vendor/HoardingController.php**
   - Added `grace_period_days` validation in `store()` and `update()`

#### Views (4)
7. **resources/views/customer/enquiries/create.blade.php**
   - Updated date picker min attribute
   - Added grace period help text

8. **resources/views/customer/enquiry-create.blade.php**
   - Updated date picker min attribute
   - Added grace period help text

9. **resources/views/vendor/hoardings/create.blade.php**
   - Added grace period input field
   - Added tooltip and help text

10. **resources/views/vendor/hoardings/edit.blade.php**
    - Added grace period input field
    - Added current value display

---

## Migration Instructions

### Step 1: Run Migration
```bash
php artisan migrate
```

### Step 2: Update Existing Hoardings (Optional)
Set default grace period for existing hoardings:
```php
// Set all existing hoardings to use 3-day grace period
DB::table('hoardings')->update(['grace_period_days' => 3]);

// Or leave as NULL to use admin default
// (Already done in migration)
```

### Step 3: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Future Enhancements

1. **Admin Dashboard**:
   - Global grace period override
   - Hoarding-level grace period bulk update
   - Grace period analytics (rejected bookings due to grace period)

2. **Vendor Permissions**:
   - Permission to override grace period (currently allowed for all vendors)
   - Maximum grace period limit per vendor tier

3. **API Endpoints**:
   - `GET /api/v1/hoardings/{id}/grace-period` - Get grace period details
   - `POST /api/v1/hoardings/{id}/grace-period/validate` - Validate date against grace period

4. **Notifications**:
   - Inform customers when selected dates violate grace period
   - Suggest alternative dates

5. **Analytics**:
   - Track grace period effectiveness
   - Measure booking lead times
   - Identify hoardings with frequent grace period violations

---

## Related Features

- **PROMPT 73**: Fraud Detection System
- **PROMPT 74**: Revenue Dashboard
- **PROMPT 75**: Vendor Performance Dashboard
- **PROMPT 78**: Hoarding Approval Workflow
- **PROMPT 79**: SEO Optimization
- **PROMPT 80**: Multi-Language Support

---

## Support

For issues or questions:
1. Check `.env` for correct `BOOKING_GRACE_PERIOD_DAYS` value
2. Verify database migration ran successfully
3. Clear Laravel cache
4. Check controller validation logic
5. Inspect frontend date picker min attributes

---

**Implementation Date**: December 11, 2025  
**Version**: 1.0  
**Status**: ✅ Complete (Pending Migration Execution)
