# PROMPT 105: Implementation Summary

## Quick Reference

**Feature**: Offer Auto-Expiry Logic  
**Status**: ✅ Complete  
**Tests**: 51 tests (27 service + 24 model)  
**Files Modified/Created**: 9

---

## User Requirement

> "Add system: vendor-created offers auto-expire after X days set by admin or vendor; expired offers cannot be accepted"

---

## What Was Implemented

### Core Features

1. ✅ **Configurable Auto-Expiry**
   - System default: 7 days (admin configurable)
   - Vendor override: Custom days per offer (1-90 days)
   - Settings: `offer_default_expiry_days`, `offer_min_expiry_days`, `offer_max_expiry_days`

2. ✅ **Automated Processing**
   - Scheduled command: `offers:expire` (runs hourly)
   - Batch expiry: `expireAllDueOffers()` method
   - Dry-run mode for testing

3. ✅ **Acceptance Validation**
   - `validateOfferAcceptance()` checks expiry before acceptance
   - Prevents accepting expired offers
   - Returns clear rejection reason

4. ✅ **Expiry Management**
   - Extend expiry: Add days to current expiry
   - Reset expiry: Set new expiry from now
   - Admin/vendor controls

5. ✅ **Dashboard Integration**
   - Statistics: Active, expired, expiring today/soon
   - Expiry rate percentage
   - Expiring soon alerts

6. ✅ **Backward Compatibility**
   - Maintains `valid_until` field support
   - Migration backfills data
   - Dual-field checking

---

## Database Changes

### New Fields (offers table)

| Field | Type | Purpose |
|-------|------|---------|
| `expiry_days` | int NULL | Configurable expiry period |
| `expires_at` | timestamp NULL | Calculated expiry time |
| `sent_at` | timestamp NULL | When offer was sent |
| `expired_at` | timestamp NULL | When marked as expired |

### Indexes

```sql
expires_at
(status, expires_at)
```

### Migration File

`database/migrations/2025_12_13_000002_add_expiry_fields_to_offers_table.php`

**Run**: `php artisan migrate`

---

## Service Architecture

### OfferExpiryService (13 Methods)

**File**: `app/Services/OfferExpiryService.php` (395 lines)

#### Configuration
- `getDefaultExpiryDays()` - Get system default (7 days)

#### Calculation
- `calculateExpiryTimestamp($offer)` - sent_at + expiry_days
- `getDaysRemaining($offer)` - Calculate remaining days

#### Detection
- `isOfferExpired($offer)` - Check if expired

#### Processing
- `setOfferExpiry($offer, $days)` - Set expiry when sending
- `markOfferExpired($offer)` - Mark as expired
- `expireAllDueOffers()` - Batch expire all due offers

#### Queries
- `getOffersExpiringSoon($days)` - Get offers expiring within X days
- `getOffersExpiringToday()` - Today's expiring offers

#### Management
- `extendOfferExpiry($offer, $days)` - Add days to expiry
- `resetOfferExpiry($offer, $days)` - Reset expiry from now

#### Validation
- `validateOfferAcceptance($offer)` - Check if can accept

#### Analytics
- `getExpiryStatistics()` - Get comprehensive stats

---

## Model Updates (Offer)

**File**: `Modules/Offers/Models/Offer.php`

### New Scopes

- `scopeActive()` - Updated to check expiry
- `scopeExpired()` - Get expired offers
- `scopeDueToExpire()` - Get sent offers past expiry
- `scopeExpiringSoon($days)` - Get offers expiring within X days

### New Methods

- `getDaysRemaining()` - Returns int|null
- `getExpiryLabel()` - Returns human-readable label ("Expires in 5 days", etc.)

---

## Scheduled Task

**Command**: `php artisan offers:expire`

**Options**:
- `--dry-run` - Preview without expiring
- `--notify` - Send notifications (placeholder)

**Schedule**: Hourly via Laravel Scheduler

**File**: `routes/console.php`

```php
Schedule::command('offers:expire')
    ->hourly()
    ->withoutOverlapping(5)
    ->onOneServer();
```

---

## Settings Configuration

**Seeder**: `database/seeders/OfferExpirySettingsSeeder.php`

**Run**: `php artisan db:seed --class=OfferExpirySettingsSeeder`

### Settings Created

| Key | Value | Type | Group |
|-----|-------|------|-------|
| `offer_default_expiry_days` | 7 | INTEGER | general |
| `offer_expiry_warning_days` | 2 | INTEGER | notification |
| `offer_allow_vendor_custom_expiry` | true | BOOLEAN | general |
| `offer_min_expiry_days` | 1 | INTEGER | general |
| `offer_max_expiry_days` | 90 | INTEGER | general |

---

## Integration Points

### 1. Sending Offers

**Modified**: `Modules/Offers/Services/OfferService.php`

```php
public function sendOffer($offerId, $expiryDays = null)
{
    $offer = $this->repository->find($offerId);
    
    // Set expiry
    $this->expiryService->setOfferExpiry($offer, $expiryDays);
    
    $offer->status = Offer::STATUS_SENT;
    $offer->save();
    
    return $offer;
}
```

### 2. Accepting Offers

**Modified**: `Modules/Offers/Services/OfferService.php`

```php
public function acceptOffer($offerId)
{
    $offer = $this->repository->find($offerId);
    
    // Validate expiry
    [$canAccept, $reason] = $this->expiryService->validateOfferAcceptance($offer);
    
    if (!$canAccept) {
        throw new \Exception($reason);
    }
    
    // ... proceed with acceptance
}
```

### 3. Frontend Display

```php
// Expiry label
{{ $offer->getExpiryLabel() }}

// Days remaining
{{ $offer->getDaysRemaining() }}

// Check if expired
@if($offer->isExpired())
    <button disabled>Accept Offer (Expired)</button>
@else
    <button>Accept Offer</button>
@endif
```

### 4. Admin Dashboard

```php
$stats = app(OfferExpiryService::class)->getExpiryStatistics();

// Returns:
// [
//     'total_active' => 120,
//     'total_expired' => 45,
//     'expiring_today' => 8,
//     'expiring_soon' => 15,
//     'expiry_rate' => 27.27
// ]
```

---

## Testing

### Test Files

1. **OfferExpiryServiceTest.php** (27 tests)
   - All service methods
   - Settings integration
   - Edge cases

2. **OfferModelExpiryTest.php** (24 tests)
   - Model methods
   - Query scopes
   - Backward compatibility

**Total**: 51 tests

### Running Tests

```bash
# All expiry tests
php artisan test --filter=Expiry

# Service tests
php artisan test tests/Feature/OfferExpiryServiceTest.php

# Model tests
php artisan test tests/Feature/OfferModelExpiryTest.php
```

---

## File Inventory

### Created (6 files)

1. `database/migrations/2025_12_13_000002_add_expiry_fields_to_offers_table.php` (65 lines)
2. `app/Services/OfferExpiryService.php` (395 lines)
3. `app/Console/Commands/ExpireOffersCommand.php` (135 lines)
4. `database/seeders/OfferExpirySettingsSeeder.php` (60 lines)
5. `tests/Feature/OfferExpiryServiceTest.php` (465 lines)
6. `tests/Feature/OfferModelExpiryTest.php` (535 lines)

### Modified (3 files)

1. `Modules/Offers/Models/Offer.php`
   - Added 4 fillable fields
   - Added 4 scopes
   - Added 2 methods
   - Updated `isExpired()` logic

2. `Modules/Offers/Services/OfferService.php`
   - Injected `OfferExpiryService` dependency
   - Updated `sendOffer()` to accept expiry days
   - Updated `acceptOffer()` to validate expiry
   - Added `extendOfferExpiry()` method
   - Added `resetOfferExpiry()` method

3. `routes/console.php`
   - Added scheduled command registration

**Total Lines Added**: ~1,655 lines of production code + tests

---

## Deployment Checklist

### 1. Database

```bash
# Run migration
php artisan migrate

# Seed settings
php artisan db:seed --class=OfferExpirySettingsSeeder
```

### 2. Scheduler (Production)

Add to crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Test Command

```bash
# Dry run to verify
php artisan offers:expire --dry-run
```

### 4. Run Tests

```bash
php artisan test --filter=Expiry
```

### 5. Configure Settings (Admin Panel)

- Set default expiry days (default: 7)
- Set warning days (default: 2)
- Enable/disable vendor custom expiry (default: true)
- Set min/max boundaries (default: 1-90 days)

### 6. Frontend Updates

- Add expiry display to offer list
- Add extend/reset buttons for admin/vendor
- Show expiry warnings
- Disable accept button for expired offers

---

## Usage Examples

### Backend (Controller)

```php
use App\Services\OfferExpiryService;

class OfferController extends Controller
{
    public function __construct(
        protected OfferExpiryService $expiryService
    ) {}
    
    // Send offer with custom expiry
    public function send(Request $request, $offerId)
    {
        $offer = Offer::findOrFail($offerId);
        $this->expiryService->setOfferExpiry($offer, $request->expiry_days);
        
        return response()->json(['message' => 'Offer sent']);
    }
    
    // Extend offer expiry
    public function extend(Request $request, $offerId)
    {
        $offer = Offer::findOrFail($offerId);
        $this->expiryService->extendOfferExpiry($offer, $request->additional_days);
        
        return response()->json(['message' => 'Expiry extended']);
    }
    
    // Get statistics
    public function stats()
    {
        $stats = $this->expiryService->getExpiryStatistics();
        
        return response()->json($stats);
    }
}
```

### Frontend (Blade)

```php
{{-- Offer list --}}
@foreach($offers as $offer)
    <tr>
        <td>#{{ $offer->id }}</td>
        <td>{{ $offer->getExpiryLabel() }}</td>
        <td>
            @if(!$offer->isExpired())
                <button class="btn-accept">Accept</button>
            @else
                <button disabled>Expired</button>
            @endif
        </td>
    </tr>
@endforeach

{{-- Dashboard widget --}}
<div class="stats">
    <div class="stat-card">
        <h3>{{ $stats['total_active'] }}</h3>
        <p>Active Offers</p>
    </div>
    
    <div class="stat-card warning">
        <h3>{{ $stats['expiring_today'] }}</h3>
        <p>Expiring Today</p>
    </div>
</div>
```

---

## Troubleshooting

### Offers not expiring automatically

1. Check scheduler is running: `php artisan schedule:list`
2. Run command manually: `php artisan offers:expire --dry-run`
3. Check crontab has scheduler entry

### Custom expiry not working

1. Check setting: `Setting::getValue('offer_allow_vendor_custom_expiry')`
2. Verify `$expiryDays` parameter is passed to `sendOffer()`

### Performance issues

1. Verify migration ran: `php artisan migrate:status`
2. Check indexes exist: `SHOW INDEX FROM offers`
3. Use chunk for large datasets

---

## Key Concepts

### Expiry Workflow

```
1. Offer Created (STATUS_DRAFT)
   └─> expiry_days = null, expires_at = null

2. Offer Sent (STATUS_SENT)
   └─> sent_at = now()
   └─> expiry_days = 7 (or custom)
   └─> expires_at = sent_at + expiry_days

3. Scheduled Task (Hourly)
   └─> Query: dueToExpire() scope
   └─> Batch: expireAllDueOffers()
   └─> Update: status = STATUS_EXPIRED, expired_at = now()

4. Customer Attempts Acceptance
   └─> Validate: validateOfferAcceptance()
   └─> Check: isExpired()
   └─> Result: Allow or reject with reason
```

### Backward Compatibility

```
isExpired() checks:
1. If status == STATUS_EXPIRED → TRUE
2. If expires_at exists and < now() → TRUE
3. If valid_until exists and < now() → TRUE (fallback)
4. Otherwise → FALSE
```

### Settings Hierarchy

```
Expiry Days Source:
1. Offer-specific (offer.expiry_days) [if vendor allowed]
2. System default (Setting: offer_default_expiry_days)
3. Hardcoded fallback (7 days)
```

---

## Success Metrics

✅ **27 service tests** - All passing  
✅ **24 model tests** - All passing  
✅ **13 service methods** - Fully implemented  
✅ **4 database fields** - With indexes  
✅ **5 system settings** - Configurable  
✅ **1 scheduled command** - Hourly execution  
✅ **Backward compatibility** - Maintains valid_until support  
✅ **Complete documentation** - API reference + examples

---

## Related Documentation

- Full API Reference: [PROMPT_105_OFFER_AUTO_EXPIRY.md](PROMPT_105_OFFER_AUTO_EXPIRY.md)
- Test Coverage: See test files for detailed examples
- Settings Guide: Admin panel > Settings > General

---

**Version**: 1.0  
**Date**: December 13, 2025  
**Status**: ✅ Ready for Production
