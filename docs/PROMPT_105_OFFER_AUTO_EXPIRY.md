# PROMPT 105: Offer Auto-Expiry Logic

## Overview

This feature implements automatic expiry for vendor-created offers after a configurable number of days. Offers that have expired cannot be accepted by customers, ensuring the system maintains data integrity and prevents acceptance of outdated pricing or terms.

### Key Features

- **Configurable Expiry Period**: System-wide default (7 days) with vendor-level overrides
- **Automatic Expiry Processing**: Scheduled hourly task to mark expired offers
- **Expiry Validation**: Prevents acceptance of expired offers
- **Flexible Management**: Admin and vendors can extend or reset offer expiry
- **Backward Compatibility**: Maintains support for existing `valid_until` field
- **Audit Trail**: Tracks `sent_at`, `expires_at`, and `expired_at` timestamps
- **Dashboard Integration**: Statistics and expiring soon alerts
- **Settings-Based Configuration**: Admin control over min/max expiry periods

---

## Database Schema Changes

### Migration

**File**: `database/migrations/2025_12_13_000002_add_expiry_fields_to_offers_table.php`

### New Fields

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `expiry_days` | integer | Yes | Number of days until offer expires (null = use system default) |
| `expires_at` | timestamp | Yes | Calculated expiration timestamp (sent_at + expiry_days) |
| `sent_at` | timestamp | Yes | When offer was sent to customer |
| `expired_at` | timestamp | Yes | When offer was marked as expired by the system |

### Indexes

```sql
$table->index('expires_at'); // For efficient expiry queries
$table->index(['status', 'expires_at']); // For compound queries
```

### Running the Migration

```bash
php artisan migrate
```

### Data Backfill

The migration automatically backfills existing offers:
- `sent_at`: Populated from `created_at` for offers with status `sent`, `accepted`, `rejected`, or `expired`
- `expires_at`: Populated from `valid_until` for backward compatibility

### Backward Compatibility

The system continues to support the existing `valid_until` field:
- If `expires_at` is set, it takes precedence
- If `expires_at` is null, the system falls back to `valid_until`
- Both fields are checked in expiry detection logic

---

## Settings Configuration

### Seeder

**File**: `database/seeders/OfferExpirySettingsSeeder.php`

Run the seeder to create default settings:

```bash
php artisan db:seed --class=OfferExpirySettingsSeeder
```

### Available Settings

| Setting Key | Type | Default | Group | Description |
|-------------|------|---------|-------|-------------|
| `offer_default_expiry_days` | INTEGER | 7 | general | Default days until offer expires |
| `offer_expiry_warning_days` | INTEGER | 2 | notification | Days before expiry to show warnings |
| `offer_allow_vendor_custom_expiry` | BOOLEAN | true | general | Allow vendors to set custom expiry days |
| `offer_min_expiry_days` | INTEGER | 1 | general | Minimum allowed expiry days |
| `offer_max_expiry_days` | INTEGER | 90 | general | Maximum allowed expiry days |

### Changing Settings

**Via Admin Panel**:
1. Navigate to Settings > General
2. Find the "Offer Expiry" section
3. Update values as needed
4. Save changes

**Programmatically**:

```php
use App\Models\Setting;

// Update default expiry days
Setting::setValue('offer_default_expiry_days', 14);

// Disable vendor custom expiry
Setting::setValue('offer_allow_vendor_custom_expiry', false);

// Set min/max boundaries
Setting::setValue('offer_min_expiry_days', 3);
Setting::setValue('offer_max_expiry_days', 60);
```

---

## Service API

### OfferExpiryService

**File**: `app/Services/OfferExpiryService.php`

The centralized service for all offer expiry operations.

#### Dependency Injection

```php
use App\Services\OfferExpiryService;

class YourController extends Controller
{
    public function __construct(protected OfferExpiryService $expiryService)
    {
    }
}
```

---

### Method Reference

#### getDefaultExpiryDays()

Get the system-wide default expiry days from settings.

**Returns**: `int` (default: 7)

**Example**:

```php
$days = $this->expiryService->getDefaultExpiryDays();
// Returns: 7 (or configured value)
```

---

#### calculateExpiryTimestamp(Offer $offer)

Calculate the expiry timestamp for an offer based on `sent_at` + `expiry_days`.

**Parameters**:
- `$offer`: The offer instance

**Returns**: `Carbon|null`
- Returns `null` if offer is not sent or has no expiry days
- Returns Carbon timestamp otherwise

**Example**:

```php
$expiryTime = $this->expiryService->calculateExpiryTimestamp($offer);
// Returns: Carbon instance (e.g., 2025-12-20 10:30:00) or null
```

---

#### setOfferExpiry(Offer $offer, ?int $expiryDays = null)

Set the expiry for an offer when it is sent to a customer.

**Parameters**:
- `$offer`: The offer instance
- `$expiryDays` (optional): Custom expiry days (null = use default)

**Returns**: `Offer` (updated instance)

**Side Effects**:
- Sets `sent_at` to now
- Sets `expiry_days` (from parameter or default)
- Calculates and sets `expires_at`
- Saves the offer
- Logs the action

**Example**:

```php
// Use default expiry (7 days)
$offer = $this->expiryService->setOfferExpiry($offer);

// Use custom expiry (14 days)
$offer = $this->expiryService->setOfferExpiry($offer, 14);
```

---

#### isOfferExpired(Offer $offer)

Check if an offer is expired.

**Parameters**:
- `$offer`: The offer instance

**Returns**: `bool`

**Logic**:
1. If `status` is `STATUS_EXPIRED`, return `true`
2. If `expires_at` is set and in the past, return `true`
3. If `valid_until` is set (backward compat) and in the past, return `true`
4. Otherwise, return `false`

**Example**:

```php
if ($this->expiryService->isOfferExpired($offer)) {
    return response()->json(['error' => 'This offer has expired'], 410);
}
```

---

#### markOfferExpired(Offer $offer)

Mark an offer as expired with proper status and timestamp.

**Parameters**:
- `$offer`: The offer instance

**Returns**: `Offer` (updated instance)

**Side Effects**:
- Sets `status` to `STATUS_EXPIRED`
- Sets `expired_at` to now
- Saves the offer
- Logs the action

**Example**:

```php
$offer = $this->expiryService->markOfferExpired($offer);
```

---

#### expireAllDueOffers()

Process all offers that are past their expiry time and mark them as expired.

**Returns**: `int` (count of expired offers)

**Logic**:
1. Query offers using `dueToExpire()` scope
2. Iterate and mark each as expired
3. Return total count

**Example**:

```php
$count = $this->expiryService->expireAllDueOffers();
Log::info("Expired {$count} offers");
```

**Called By**: Scheduled command (`offers:expire`)

---

#### getOffersExpiringSoon(?int $days = null)

Get offers that will expire within the specified number of days.

**Parameters**:
- `$days` (optional): Number of days threshold (default: from `offer_expiry_warning_days` setting)

**Returns**: `Collection<Offer>`

**Example**:

```php
// Get offers expiring in next 2 days (default)
$offers = $this->expiryService->getOffersExpiringSoon();

// Get offers expiring in next 5 days
$offers = $this->expiryService->getOffersExpiringSoon(5);

foreach ($offers as $offer) {
    // Send warning notification
}
```

---

#### getOffersExpiringToday()

Get all offers expiring today.

**Returns**: `Collection<Offer>`

**Example**:

```php
$offers = $this->expiryService->getOffersExpiringToday();
```

---

#### getExpiryStatistics()

Get comprehensive statistics about offer expiry states.

**Returns**: `array` with keys:
- `total_active`: Count of active (sent, not expired) offers
- `total_expired`: Count of expired offers
- `expiring_today`: Count expiring today
- `expiring_soon`: Count expiring within warning period
- `expiry_rate`: Percentage of expired offers

**Example**:

```php
$stats = $this->expiryService->getExpiryStatistics();

// Output:
// [
//     'total_active' => 120,
//     'total_expired' => 45,
//     'expiring_today' => 8,
//     'expiring_soon' => 15,
//     'expiry_rate' => 27.27
// ]
```

**Use Case**: Admin dashboard widgets

---

#### extendOfferExpiry(Offer $offer, int $additionalDays)

Extend an offer's expiry by adding additional days to the current expiry.

**Parameters**:
- `$offer`: The offer instance (must be `STATUS_SENT`)
- `$additionalDays`: Number of days to add (must be > 0)

**Returns**: `Offer` (updated instance)

**Throws**: `\Exception`
- If offer is not sent
- If `additionalDays` <= 0

**Side Effects**:
- Adds `$additionalDays` to `expires_at`
- Saves the offer
- Logs the action

**Example**:

```php
// Extend offer expiry by 5 days
try {
    $offer = $this->expiryService->extendOfferExpiry($offer, 5);
    return response()->json(['message' => 'Offer expiry extended']);
} catch (\Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
```

---

#### resetOfferExpiry(Offer $offer, int $newExpiryDays)

Reset an offer's expiry to a new period from the current time.

**Parameters**:
- `$offer`: The offer instance (must be `STATUS_SENT`)
- `$newExpiryDays`: New expiry days from now (must be > 0)

**Returns**: `Offer` (updated instance)

**Throws**: `\Exception`
- If offer is not sent
- If `newExpiryDays` <= 0

**Side Effects**:
- Sets `expiry_days` to `$newExpiryDays`
- Sets `expires_at` to now + `$newExpiryDays`
- Saves the offer
- Logs the action

**Example**:

```php
// Reset offer to expire in 10 days from now
try {
    $offer = $this->expiryService->resetOfferExpiry($offer, 10);
    return response()->json(['message' => 'Offer expiry reset']);
} catch (\Exception $e) {
    return response()->json(['error' => $e->getMessage()], 400);
}
```

---

#### validateOfferAcceptance(Offer $offer)

Validate whether an offer can be accepted (checks expiry and status).

**Parameters**:
- `$offer`: The offer instance

**Returns**: `array`
- `['canAccept' => bool, 'reason' => string|null]`

**Example**:

```php
[$canAccept, $reason] = $this->expiryService->validateOfferAcceptance($offer);

if (!$canAccept) {
    return response()->json(['error' => $reason], 400);
}

// Proceed with acceptance
```

**Possible Reasons**:
- `"This offer has expired and cannot be accepted"`
- `"Only sent offers can be accepted"`
- `null` (if can accept)

---

#### getDaysRemaining(Offer $offer)

Calculate how many days remain until the offer expires.

**Parameters**:
- `$offer`: The offer instance

**Returns**: `int|null`
- `null`: No expiry set
- `0`: Expired
- `> 0`: Days remaining (rounded up)

**Example**:

```php
$days = $this->expiryService->getDaysRemaining($offer);

if ($days === null) {
    echo "No expiry";
} elseif ($days === 0) {
    echo "Expired";
} else {
    echo "Expires in {$days} days";
}
```

---

## Model Methods

### Offer Model

**File**: `Modules/Offers/Models/Offer.php`

---

### New Fields

Added to `$fillable`:

```php
protected $fillable = [
    // ... existing fields
    'expiry_days',
    'expires_at',
    'sent_at',
    'expired_at',
];
```

Added to `$casts`:

```php
protected $casts = [
    // ... existing casts
    'expires_at' => 'datetime',
    'sent_at' => 'datetime',
    'expired_at' => 'datetime',
];
```

---

### Query Scopes

#### scopeActive($query)

Get active offers (sent and not expired).

**Updated Logic**:
- Status must be `STATUS_SENT`
- Either no `expires_at` or `expires_at` in the future
- Either no `valid_until` or `valid_until` in the future (backward compat)

**Example**:

```php
$activeOffers = Offer::active()->get();
```

---

#### scopeExpired($query)

Get all offers with `STATUS_EXPIRED`.

**Example**:

```php
$expiredOffers = Offer::expired()->get();
```

---

#### scopeDueToExpire($query)

Get sent offers that are past their expiry time but not yet marked as expired.

**Logic**:
- Status is `STATUS_SENT`
- `expires_at` is not null AND in the past

**Example**:

```php
$dueOffers = Offer::dueToExpire()->get();

foreach ($dueOffers as $offer) {
    $offer->status = Offer::STATUS_EXPIRED;
    $offer->expired_at = now();
    $offer->save();
}
```

---

#### scopeExpiringSoon($query, $days = 3)

Get sent offers expiring within the next X days.

**Parameters**:
- `$days` (optional): Threshold in days (default: 3)

**Logic**:
- Status is `STATUS_SENT`
- `expires_at` is between now and now + X days

**Example**:

```php
// Get offers expiring in next 3 days
$soonOffers = Offer::expiringSoon()->get();

// Get offers expiring in next 7 days
$soonOffers = Offer::expiringSoon(7)->get();
```

---

### Instance Methods

#### isExpired()

Check if the offer is expired.

**Returns**: `bool`

**Updated Logic**:
1. If `status` is `STATUS_EXPIRED`, return `true`
2. If `expires_at` is set and in the past, return `true`
3. If `valid_until` is set (backward compat) and in the past, return `true`
4. Otherwise, return `false`

**Example**:

```php
if ($offer->isExpired()) {
    return redirect()->back()->with('error', 'This offer has expired');
}
```

---

#### getDaysRemaining()

Calculate days remaining until expiry.

**Returns**: `int|null`
- `null`: No expiry set
- `0`: Expired
- `> 0`: Days remaining

**Example**:

```php
$days = $offer->getDaysRemaining();

@if($days === null)
    <span class="badge badge-secondary">No expiry</span>
@elseif($days === 0)
    <span class="badge badge-danger">Expired</span>
@elseif($days === 1)
    <span class="badge badge-warning">Expires tomorrow</span>
@else
    <span class="badge badge-info">Expires in {{ $days }} days</span>
@endif
```

---

#### getExpiryLabel()

Get a human-readable expiry status label.

**Returns**: `string`

**Possible Values**:
- `"Expired"` - Offer is expired
- `"Expires today"` - Expiring within 24 hours
- `"Expires tomorrow"` - Expiring in 1 day
- `"Expires in X days"` - Expiring in X days
- `"No expiry set"` - No expiry configured

**Example**:

```php
<p>Status: {{ $offer->getExpiryLabel() }}</p>

// Output examples:
// "Expires in 5 days"
// "Expires tomorrow"
// "Expired"
```

---

## Scheduled Command

### ExpireOffersCommand

**File**: `app/Console/Commands/ExpireOffersCommand.php`

**Signature**: `offers:expire`

**Options**:
- `--dry-run`: Preview which offers will be expired without actually expiring them
- `--notify`: Send notifications to affected vendors (not yet implemented)

### Schedule Configuration

**File**: `routes/console.php`

```php
Schedule::command('offers:expire')
    ->hourly()
    ->withoutOverlapping(5)
    ->onOneServer();
```

**Runs**: Every hour
**Overlap Prevention**: 5-minute timeout
**Server**: Only one server (for multi-server setups)

### Manual Execution

**Dry Run (Preview)**:

```bash
php artisan offers:expire --dry-run
```

Output:

```
Checking for expired offers...

The following offers will be expired:
┌────┬────────────────┬──────────────┬──────────┬─────────────────────┬──────────┐
│ ID │ Customer       │ Vendor       │ Price    │ Expired At          │ Time Ago │
├────┼────────────────┼──────────────┼──────────┼─────────────────────┼──────────┤
│ 42 │ John Smith     │ ABC Vendors  │ $1,250   │ 2025-12-13 10:30:00 │ 2h ago   │
│ 58 │ Jane Doe       │ XYZ Company  │ $3,000   │ 2025-12-12 15:00:00 │ 1d ago   │
└────┴────────────────┴──────────────┴──────────┴─────────────────────┴──────────┘

DRY RUN: 2 offers would be expired.
```

**Actual Execution**:

```bash
php artisan offers:expire
```

Output:

```
Checking for expired offers...

The following offers will be expired:
[... table output ...]

Do you want to expire these 2 offers? (yes/no) [yes]:
> yes

Expiring offers...
Successfully expired 2 offers.

Expiry Statistics:
Total Active Offers: 120
Total Expired Offers: 47
Expiring Today: 5
Expiring Soon: 12
Expiry Rate: 28.14%
```

### Logging

All expiry actions are logged to `storage/logs/laravel.log`:

```
[2025-12-13 12:00:01] local.INFO: Offer #42 marked as expired
[2025-12-13 12:00:01] local.INFO: Offer #58 marked as expired
[2025-12-13 12:00:01] local.INFO: Expired 2 offers via scheduled command
```

---

## Integration Points

### 1. Sending Offers (OfferService)

**File**: `Modules/Offers/Services/OfferService.php`

**Method**: `sendOffer($offerId, $expiryDays = null)`

When an offer is sent to a customer:

```php
use App\Services\OfferExpiryService;

public function sendOffer($offerId, $expiryDays = null)
{
    $offer = $this->repository->find($offerId);
    
    // ... validation logic ...
    
    // Set expiry when sending
    $this->expiryService->setOfferExpiry($offer, $expiryDays);
    
    $offer->status = Offer::STATUS_SENT;
    $offer->save();
    
    // ... send notification ...
    
    return $offer;
}
```

**Frontend Integration**:

```php
// Vendor dashboard - send offer form
<form action="/api/offers/{{ $offer->id }}/send" method="POST">
    <label>Expiry Days</label>
    <select name="expiry_days">
        <option value="">Use default ({{ Setting::getValue('offer_default_expiry_days', 7) }} days)</option>
        <option value="3">3 days</option>
        <option value="7">7 days</option>
        <option value="14">14 days</option>
        <option value="30">30 days</option>
    </select>
    
    <button type="submit">Send Offer</button>
</form>
```

---

### 2. Accepting Offers (OfferService)

**Method**: `acceptOffer($offerId)`

Before accepting an offer:

```php
public function acceptOffer($offerId)
{
    $offer = $this->repository->find($offerId);
    
    // Validate expiry before acceptance
    [$canAccept, $reason] = $this->expiryService->validateOfferAcceptance($offer);
    
    if (!$canAccept) {
        throw new \Exception($reason);
    }
    
    // ... proceed with acceptance ...
}
```

**Frontend Integration**:

```php
// Customer view offer page
@if($offer->isExpired())
    <div class="alert alert-danger">
        <strong>This offer has expired.</strong> Please contact the vendor for a new offer.
    </div>
    <button class="btn btn-primary" disabled>Accept Offer (Expired)</button>
@else
    <div class="alert alert-info">
        {{ $offer->getExpiryLabel() }}
    </div>
    <button class="btn btn-primary" onclick="acceptOffer({{ $offer->id }})">
        Accept Offer
    </button>
@endif
```

---

### 3. Frontend Display (Offer List)

**Blade Template Example**:

```php
<table class="table">
    <thead>
        <tr>
            <th>Offer #</th>
            <th>Price</th>
            <th>Status</th>
            <th>Expiry</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($offers as $offer)
            <tr class="{{ $offer->isExpired() ? 'table-danger' : '' }}">
                <td>#{{ $offer->id }}</td>
                <td>${{ number_format($offer->price, 2) }}</td>
                <td>
                    <span class="badge badge-{{ $offer->status_color }}">
                        {{ ucfirst($offer->status) }}
                    </span>
                </td>
                <td>
                    @php
                        $days = $offer->getDaysRemaining();
                        $color = 'secondary';
                        if ($days === 0) $color = 'danger';
                        elseif ($days === 1) $color = 'warning';
                        elseif ($days <= 3) $color = 'warning';
                        elseif ($days <= 7) $color = 'info';
                        else $color = 'success';
                    @endphp
                    
                    <span class="badge badge-{{ $color }}">
                        {{ $offer->getExpiryLabel() }}
                    </span>
                </td>
                <td>
                    @if(!$offer->isExpired() && $offer->status === 'sent')
                        <button class="btn btn-sm btn-success" onclick="acceptOffer({{ $offer->id }})">
                            Accept
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="extendExpiry({{ $offer->id }})">
                            Extend
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
```

---

### 4. Admin Dashboard (Statistics Widget)

```php
use App\Services\OfferExpiryService;

class DashboardController extends Controller
{
    public function index(OfferExpiryService $expiryService)
    {
        $stats = $expiryService->getExpiryStatistics();
        
        return view('admin.dashboard', compact('stats'));
    }
}
```

**Blade Template**:

```php
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5>Active Offers</h5>
                <h2>{{ $stats['total_active'] }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5>Expired Offers</h5>
                <h2>{{ $stats['total_expired'] }}</h2>
                <small>{{ number_format($stats['expiry_rate'], 1) }}% rate</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <h5>Expiring Today</h5>
                <h2>{{ $stats['expiring_today'] }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body">
                <h5>Expiring Soon</h5>
                <h2>{{ $stats['expiring_soon'] }}</h2>
            </div>
        </div>
    </div>
</div>
```

---

### 5. Vendor Dashboard (Expiring Soon List)

```php
use App\Services\OfferExpiryService;

class VendorDashboardController extends Controller
{
    public function index(OfferExpiryService $expiryService)
    {
        $vendor = auth()->user();
        $expiringSoon = $expiryService->getOffersExpiringSoon(3)
            ->where('vendor_id', $vendor->id);
        
        return view('vendor.dashboard', compact('expiringSoon'));
    }
}
```

**Blade Template**:

```php
@if($expiringSoon->count() > 0)
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-triangle"></i> Offers Expiring Soon</h5>
        <ul>
            @foreach($expiringSoon as $offer)
                <li>
                    Offer #{{ $offer->id }} to {{ $offer->customer->name }} - 
                    <strong>{{ $offer->getExpiryLabel() }}</strong>
                    <a href="{{ route('offers.extend', $offer->id) }}" class="btn btn-sm btn-primary">
                        Extend
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
```

---

## Testing

### Test Files

1. **OfferExpiryServiceTest.php** (27 tests)
   - Service method functionality
   - Settings integration
   - Expiry calculations
   - Batch operations
   - Statistics

2. **OfferModelExpiryTest.php** (24 tests)
   - Model methods
   - Query scopes
   - Expiry labels
   - Backward compatibility

**Total**: 51 comprehensive tests

### Running Tests

**All expiry tests**:

```bash
php artisan test --filter=Expiry
```

**Service tests only**:

```bash
php artisan test tests/Feature/OfferExpiryServiceTest.php
```

**Model tests only**:

```bash
php artisan test tests/Feature/OfferModelExpiryTest.php
```

**Specific test**:

```bash
php artisan test --filter=it_expires_all_due_offers
```

### Test Coverage

#### OfferExpiryService Methods (27 tests)

- ✅ `getDefaultExpiryDays()` - Settings integration
- ✅ `calculateExpiryTimestamp()` - Timestamp calculation
- ✅ `setOfferExpiry()` - Setting expiry on send
- ✅ `isOfferExpired()` - Expiry detection
- ✅ `markOfferExpired()` - Marking as expired
- ✅ `expireAllDueOffers()` - Batch expiry
- ✅ `getOffersExpiringSoon()` - Expiring soon query
- ✅ `getOffersExpiringToday()` - Today's expiring offers
- ✅ `getExpiryStatistics()` - Analytics
- ✅ `extendOfferExpiry()` - Extending expiry
- ✅ `resetOfferExpiry()` - Resetting expiry
- ✅ `validateOfferAcceptance()` - Acceptance validation
- ✅ `getDaysRemaining()` - Days calculation

#### Offer Model Methods (24 tests)

- ✅ `isExpired()` - Expiry detection
- ✅ `canAccept()` - Acceptance validation
- ✅ `getDaysRemaining()` - Days calculation
- ✅ `getExpiryLabel()` - Label generation
- ✅ `scopeActive()` - Active offers scope
- ✅ `scopeExpired()` - Expired offers scope
- ✅ `scopeDueToExpire()` - Due to expire scope
- ✅ `scopeExpiringSoon()` - Expiring soon scope

#### Edge Cases Covered

- ✅ Null expiry handling
- ✅ Draft offers (no expiry)
- ✅ Backward compatibility with `valid_until`
- ✅ `expires_at` priority over `valid_until`
- ✅ Exception handling (invalid parameters)
- ✅ Same-day expiry (today/tomorrow labels)

### Example Test Code

```php
/** @test */
public function it_expires_all_due_offers()
{
    // Arrange: Create 5 due offers
    Offer::factory()->count(5)->create([
        'status' => Offer::STATUS_SENT,
        'expires_at' => Carbon::now()->subDay(),
    ]);
    
    // Arrange: Create 3 not due offers
    Offer::factory()->count(3)->create([
        'status' => Offer::STATUS_SENT,
        'expires_at' => Carbon::now()->addDays(5),
    ]);
    
    // Act
    $count = $this->service->expireAllDueOffers();
    
    // Assert
    $this->assertEquals(5, $count);
    $this->assertEquals(5, Offer::expired()->count());
    $this->assertEquals(3, Offer::active()->count());
}
```

---

## API Endpoints (Suggested)

While not implemented in this prompt, here are suggested API endpoints for frontend integration:

### Extend Offer Expiry

```http
POST /api/offers/{id}/extend
Content-Type: application/json

{
    "additional_days": 5
}
```

**Response**:

```json
{
    "success": true,
    "message": "Offer expiry extended by 5 days",
    "offer": {
        "id": 42,
        "expires_at": "2025-12-20 15:30:00",
        "days_remaining": 12,
        "expiry_label": "Expires in 12 days"
    }
}
```

---

### Reset Offer Expiry

```http
POST /api/offers/{id}/reset-expiry
Content-Type: application/json

{
    "new_expiry_days": 10
}
```

**Response**:

```json
{
    "success": true,
    "message": "Offer expiry reset to 10 days",
    "offer": {
        "id": 42,
        "expires_at": "2025-12-23 10:00:00",
        "days_remaining": 10,
        "expiry_label": "Expires in 10 days"
    }
}
```

---

### Get Expiry Statistics

```http
GET /api/offers/expiry-statistics
```

**Response**:

```json
{
    "total_active": 120,
    "total_expired": 45,
    "expiring_today": 8,
    "expiring_soon": 15,
    "expiry_rate": 27.27
}
```

---

## Troubleshooting

### Issue: Offers Not Expiring Automatically

**Symptoms**: Offers past their expiry time remain with `STATUS_SENT`

**Possible Causes**:

1. **Scheduler not running**
   - Check if Laravel scheduler is configured in cron
   - Verify: `crontab -l` should show `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`
   - Solution: Add cron entry

2. **Command not scheduled**
   - Check `routes/console.php` has `Schedule::command('offers:expire')->hourly()`
   - Solution: Add schedule entry

3. **Overlapping prevention**
   - Previous command still running
   - Check logs for `withoutOverlapping` timeout
   - Solution: Wait 5 minutes or restart queue

**Testing**:

```bash
# Run command manually
php artisan offers:expire --dry-run

# Check scheduler list
php artisan schedule:list

# Run scheduler manually (for testing)
php artisan schedule:run
```

---

### Issue: Custom Expiry Days Not Working

**Symptoms**: All offers use default 7 days regardless of custom value

**Possible Causes**:

1. **Setting disabled**
   - Check: `Setting::getValue('offer_allow_vendor_custom_expiry')`
   - Solution: Enable via admin panel or `Setting::setValue('offer_allow_vendor_custom_expiry', true)`

2. **Parameter not passed**
   - Verify `sendOffer()` receives `$expiryDays` parameter
   - Check frontend form has `<select name="expiry_days">`

3. **Validation rejection**
   - Custom days outside min/max range
   - Check `offer_min_expiry_days` (default: 1) and `offer_max_expiry_days` (default: 90)

**Testing**:

```php
$offer = Offer::factory()->create(['status' => Offer::STATUS_DRAFT]);
app(OfferExpiryService::class)->setOfferExpiry($offer, 14);
dump($offer->expiry_days); // Should be 14
```

---

### Issue: Performance Issues with Large Datasets

**Symptoms**: Slow queries when checking expiry

**Solution**:

1. **Ensure indexes exist**:
   ```bash
   php artisan migrate:status
   # Verify migration "add_expiry_fields_to_offers_table" is run
   ```

2. **Check query plan**:
   ```sql
   EXPLAIN SELECT * FROM offers 
   WHERE status = 'sent' 
   AND expires_at < NOW() 
   AND expires_at IS NOT NULL;
   
   -- Should use index on (status, expires_at)
   ```

3. **Optimize batch size**:
   ```php
   // In ExpireOffersCommand
   Offer::dueToExpire()->chunk(100, function ($offers) {
       foreach ($offers as $offer) {
           app(OfferExpiryService::class)->markOfferExpired($offer);
       }
   });
   ```

---

### Issue: Backward Compatibility Problems

**Symptoms**: Old offers not detecting expiry

**Verification**:

```php
// Check if old offers have valid_until set
$oldOffers = Offer::whereNull('expires_at')
    ->whereNotNull('valid_until')
    ->count();
    
echo "Old offers: {$oldOffers}";
```

**Solution**: Migration already backfills data, but if issues persist:

```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

---

## Best Practices

### 1. Always Use Service Methods

❌ **Bad**:

```php
// Direct model manipulation
$offer->expires_at = now()->addDays(7);
$offer->save();
```

✅ **Good**:

```php
// Use service
app(OfferExpiryService::class)->setOfferExpiry($offer, 7);
```

**Why**: Service handles logging, validation, and related timestamp updates.

---

### 2. Check Expiry Before Acceptance

❌ **Bad**:

```php
public function acceptOffer($offerId)
{
    $offer = Offer::find($offerId);
    $offer->status = Offer::STATUS_ACCEPTED;
    $offer->save();
}
```

✅ **Good**:

```php
public function acceptOffer($offerId)
{
    $offer = Offer::find($offerId);
    
    [$canAccept, $reason] = app(OfferExpiryService::class)
        ->validateOfferAcceptance($offer);
    
    if (!$canAccept) {
        throw new \Exception($reason);
    }
    
    $offer->status = Offer::STATUS_ACCEPTED;
    $offer->save();
}
```

---

### 3. Display Expiry Information to Users

✅ **Always show**:

```php
<div class="offer-card">
    <h3>Offer #{{ $offer->id }}</h3>
    <p>Price: ${{ number_format($offer->price, 2) }}</p>
    
    <!-- Expiry badge -->
    <span class="badge badge-{{ $offer->getDaysRemaining() <= 2 ? 'danger' : 'info' }}">
        {{ $offer->getExpiryLabel() }}
    </span>
</div>
```

---

### 4. Use Scopes for Queries

❌ **Bad**:

```php
$offers = Offer::where('status', 'sent')
    ->where('expires_at', '<', now())
    ->get();
```

✅ **Good**:

```php
$offers = Offer::dueToExpire()->get();
```

**Why**: Scopes encapsulate complex logic and are reusable.

---

### 5. Monitor Expiry Statistics

✅ **Dashboard widget**:

```php
// In controller
$stats = app(OfferExpiryService::class)->getExpiryStatistics();

// In view
@if($stats['expiring_today'] > 10)
    <div class="alert alert-warning">
        High number of offers expiring today: {{ $stats['expiring_today'] }}
    </div>
@endif
```

---

## Summary

This feature provides a comprehensive offer expiry system with:

- ✅ **Automated expiry processing** (hourly scheduled task)
- ✅ **Configurable expiry periods** (system default + vendor override)
- ✅ **Acceptance validation** (prevents accepting expired offers)
- ✅ **Flexible management** (extend/reset expiry)
- ✅ **Backward compatibility** (supports existing `valid_until` field)
- ✅ **Comprehensive testing** (51 tests covering all scenarios)
- ✅ **Dashboard integration** (statistics and expiring soon alerts)
- ✅ **Audit trail** (sent_at, expires_at, expired_at timestamps)

### Files Modified/Created

**Migrations**:
- `database/migrations/2025_12_13_000002_add_expiry_fields_to_offers_table.php`

**Services**:
- `app/Services/OfferExpiryService.php` (new)
- `Modules/Offers/Services/OfferService.php` (modified)

**Models**:
- `Modules/Offers/Models/Offer.php` (modified)

**Commands**:
- `app/Console/Commands/ExpireOffersCommand.php` (new)

**Seeders**:
- `database/seeders/OfferExpirySettingsSeeder.php` (new)

**Scheduler**:
- `routes/console.php` (modified)

**Tests**:
- `tests/Feature/OfferExpiryServiceTest.php` (27 tests)
- `tests/Feature/OfferModelExpiryTest.php` (24 tests)

### Next Steps

1. **Run migrations**: `php artisan migrate`
2. **Seed settings**: `php artisan db:seed --class=OfferExpirySettingsSeeder`
3. **Test command**: `php artisan offers:expire --dry-run`
4. **Run tests**: `php artisan test --filter=Expiry`
5. **Configure cron**: Add scheduler to crontab
6. **Update frontend**: Add expiry labels and extend/reset buttons
7. **Monitor**: Check dashboard statistics regularly

---

**Document Version**: 1.0  
**Last Updated**: December 13, 2025  
**Author**: Development Team  
**Related Prompts**: PROMPT 105
