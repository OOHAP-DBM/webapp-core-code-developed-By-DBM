# PROMPT 106: Implementation Summary

## Quick Reference

**Feature**: Quotation acceptance deadline with auto-cancel booking flow  
**Status**: ✅ Complete  
**Dependencies**: PROMPT 105 (Offer Auto-Expiry)

---

## Core Functionality

When a quotation's related offer expires:
1. ✅ Quotation marked as rejected (AUTO-EXPIRED)
2. ✅ Related bookings auto-cancelled (if pending/draft)
3. ✅ Customer and vendor notified via email + database
4. ✅ System message posted to conversation thread
5. ✅ Warning notifications sent 2 days before expiry

---

## Files Overview

| File | Purpose | Lines | Tests |
|------|---------|-------|-------|
| QuotationExpiryService.php | Main business logic | 395 | - |
| ProcessExpiredQuotationsCommand.php | Artisan command | 145 | - |
| QuotationExpiredNotification.php | Expiry notification | 95 | - |
| QuotationBookingCancelledNotification.php | Booking cancellation | 100 | - |
| QuotationExpiryWarningNotification.php | Warning notification | 95 | - |
| QuotationExpirySettingsSeeder.php | Settings seeder | 75 | - |
| QuotationExpiryServiceTest.php | Feature tests | 565 | 23 |
| routes/console.php | Scheduler config | Modified | - |

**Total**: 1,470 lines (production + tests)

---

## Key Methods (QuotationExpiryService)

```php
// Detection
isQuotationExpired(Quotation): bool
getDaysRemaining(Quotation): int
getExpiryLabel(Quotation): string

// Processing
markQuotationExpired(Quotation): bool
processExpiredQuotations(): int
autoCancelBookingFlow(Quotation): int

// Notifications
notifyExpiry(Quotation): void
sendExpiryWarnings(): int

// Thread Integration
updateThreadForExpiry(Quotation): void

// Analytics
getQuotationsExpiringSoon(int $days): Collection
getQuotationsExpiringToday(): Collection
getExpiryStatistics(): array
```

---

## Artisan Commands

### Process Expired Quotations
```bash
# Production
php artisan quotations:process-expired

# Dry run (preview)
php artisan quotations:process-expired --dry-run

# Send warnings only
php artisan quotations:process-expired --warnings
```

### Scheduled Tasks
```php
// Hourly processing
Schedule::command('quotations:process-expired')->hourly();

// Twice daily warnings (9 AM, 5 PM)
Schedule::command('quotations:process-expired --warnings')->twiceDaily(9, 17);
```

---

## Settings Configuration

| Setting | Default | Type | Description |
|---------|---------|------|-------------|
| quotation_default_expiry_days | 7 | INTEGER | Default expiry period |
| quotation_expiry_warning_days | 2 | INTEGER | Warning threshold |
| quotation_auto_cancel_enabled | true | BOOLEAN | Enable auto-cancel |
| quotation_notify_on_expiry | true | BOOLEAN | Send notifications |
| quotation_update_thread_on_expiry | true | BOOLEAN | Update threads |

**Modify Settings**:
```php
use App\Models\Setting;

Setting::setValue('quotation_default_expiry_days', 10);
Setting::setValue('quotation_auto_cancel_enabled', false);
```

---

## Notification Types

### 1. QuotationExpiredNotification
- **Trigger**: Quotation expires
- **Recipients**: Customer + Vendor
- **Channels**: Mail + Database
- **Content**: Expiry details, auto-cancellation warning

### 2. QuotationBookingCancelledNotification
- **Trigger**: Booking auto-cancelled due to expiry
- **Recipients**: Customer + Vendor
- **Channels**: Mail + Database
- **Content**: Booking details, cancellation reason

### 3. QuotationExpiryWarningNotification
- **Trigger**: X days before expiry (default: 2 days)
- **Recipients**: Customer + Vendor
- **Channels**: Mail + Database
- **Content**: Countdown, urgency message

---

## Booking Auto-Cancellation

**Affected Statuses**:
- `pending_payment`
- `draft`
- `payment_hold`

**Exclusions** (won't be cancelled):
- `confirmed`
- `completed`
- `cancelled`
- `refunded`

**Fields Updated**:
```php
[
    'status' => 'cancelled',
    'payment_status' => 'cancelled',
    'cancellation_reason' => 'Quotation expired before payment completion',
    'cancelled_at' => now(),
    'cancelled_by' => 'system',
]
```

---

## Thread System Messages

**Format**:
```
⚠️ Quotation #42 has expired.

The quotation was based on Offer #15 which expired on Dec 13, 2025 3:30 PM. 
Any related booking requests have been automatically cancelled.

Please contact the vendor for a new quotation if you're still interested.
```

**Attributes**:
- `sender_type`: SENDER_SYSTEM
- `message_type`: TYPE_SYSTEM
- `quotation_id`: Expired quotation ID
- `is_read_customer`: false
- `is_read_vendor`: false
- Increments unread counts for both parties

---

## Test Coverage (23 Tests)

### Expiry Detection
- ✅ Detects expired quotation based on offer
- ✅ Approved quotations don't expire
- ✅ Active offers don't trigger expiry

### Processing
- ✅ Marks quotations as expired
- ✅ Batch processes expired quotations
- ✅ Auto-cancels related bookings
- ✅ Respects auto-cancel setting

### Thread Integration
- ✅ Updates thread with system message
- ✅ Increments unread counts
- ✅ Respects thread update setting

### Queries
- ✅ Gets quotations expiring soon
- ✅ Gets quotations expiring today

### Statistics
- ✅ Calculates total active
- ✅ Calculates total expired
- ✅ Calculates expiry rate
- ✅ Tracks expiring today/soon

### Notifications
- ✅ Sends expiry notifications
- ✅ Sends booking cancellation notifications
- ✅ Sends warning notifications
- ✅ Respects notification setting
- ✅ Queues notifications

### Utilities
- ✅ Calculates days remaining
- ✅ Generates expiry labels

---

## Integration Flow

```
┌─────────────────────────┐
│   Offer Expires         │ (PROMPT 105)
│   (OfferExpiryService)  │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│  Quotation Detected     │
│  (isQuotationExpired)   │
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│  Mark as Rejected       │
│  (markQuotationExpired) │
└────────────┬────────────┘
             │
             ├──────────────────────┐
             │                      │
             ▼                      ▼
┌──────────────────────┐  ┌────────────────────┐
│  Cancel Bookings     │  │  Send Notifications│
│  (autoCancelBooking) │  │  (notifyExpiry)    │
└──────────────────────┘  └────────────────────┘
             │                      │
             └──────────┬───────────┘
                        │
                        ▼
              ┌──────────────────┐
              │  Update Thread   │
              │  (updateThread)  │
              └──────────────────┘
```

---

## Database Schema Impact

**No new tables required**. Uses existing:
- `quotations` (status updated to 'rejected')
- `bookings` (status/payment_status updated)
- `thread_messages` (system messages added)
- `notifications` (notification records)
- `settings` (configuration values)

**Notes Added**:
- Quotation: `AUTO-EXPIRED: Quotation expired on [date]`
- Booking: Cancellation reason field populated

---

## Quick Deployment

```bash
# 1. Seed settings
php artisan db:seed --class=QuotationExpirySettingsSeeder

# 2. Test dry-run
php artisan quotations:process-expired --dry-run

# 3. Run tests
php artisan test tests/Feature/QuotationExpiryServiceTest.php

# 4. Verify scheduler
php artisan schedule:list

# 5. Test manually
php artisan quotations:process-expired
```

---

## Usage Examples

### Check Expiry Status
```php
$service = app(\App\Services\QuotationExpiryService::class);

if ($service->isQuotationExpired($quotation)) {
    echo "Expired " . $service->getDaysRemaining($quotation) . " days ago";
}

echo $service->getExpiryLabel($quotation);
// Output: "Expired 2 days ago" | "Expires in 3 days" | "Expires today"
```

### Get Dashboard Statistics
```php
$stats = $service->getExpiryStatistics();
/*
[
    'total_active' => 145,
    'total_expired' => 58,
    'expiring_today' => 12,
    'expiring_soon' => 23,
    'expiry_rate' => 28.57,
]
*/
```

### Display Expiry Warning
```php
@if($service->getDaysRemaining($quotation) <= 2)
    <div class="alert alert-warning">
        <strong>⚠️ Expiring Soon:</strong>
        {{ $service->getExpiryLabel($quotation) }}
    </div>
@endif
```

---

## Troubleshooting Quick Guide

| Issue | Solution |
|-------|----------|
| Quotations not processing | Check scheduler: `php artisan schedule:list` |
| No notifications | Check queue: `php artisan queue:work` |
| Bookings not cancelling | Verify setting: `quotation_auto_cancel_enabled` |
| Thread not updating | Check ThreadService exists and enquiry linked |
| High expiry rate | Consider increasing `quotation_default_expiry_days` |

---

## Performance Notes

- **Batch Processing**: Processes all expired quotations in single scheduled run
- **Queue**: All notifications queued for background processing
- **Locks**: `withoutOverlapping(5)` prevents concurrent runs
- **Server**: `onOneServer()` ensures single execution in multi-server setup
- **Indexing**: Ensure indexes on `quotations.offer_id`, `bookings.quotation_id`

**Recommended Indexes**:
```sql
CREATE INDEX idx_quotations_offer_id ON quotations(offer_id);
CREATE INDEX idx_bookings_quotation_id ON bookings(quotation_id);
CREATE INDEX idx_bookings_status ON bookings(status, payment_status);
```

---

## Feature Flags

All features can be disabled via settings:

```php
// Disable auto-cancel (keep quotation expiry only)
Setting::setValue('quotation_auto_cancel_enabled', false);

// Disable notifications (silent expiry)
Setting::setValue('quotation_notify_on_expiry', false);

// Disable thread updates (no system messages)
Setting::setValue('quotation_update_thread_on_expiry', false);
```

---

## API Endpoints (Suggested)

For frontend integration, consider adding:

```php
// routes/api.php

// Get expiry info for quotation
Route::get('/quotations/{quotation}/expiry', function (Quotation $quotation) {
    $service = app(QuotationExpiryService::class);
    
    return [
        'is_expired' => $service->isQuotationExpired($quotation),
        'days_remaining' => $service->getDaysRemaining($quotation),
        'expiry_label' => $service->getExpiryLabel($quotation),
        'expires_at' => $quotation->offer?->expires_at,
    ];
});

// Get dashboard statistics
Route::get('/admin/quotations/expiry-stats', function () {
    return app(QuotationExpiryService::class)->getExpiryStatistics();
});

// Get expiring quotations (for dashboard widget)
Route::get('/quotations/expiring-soon', function () {
    return app(QuotationExpiryService::class)->getQuotationsExpiringSoon(3);
});
```

---

## Logging

All operations are logged:

```php
// Check logs
tail -f storage/logs/laravel.log | grep "Quotation Expiry"
```

**Log Entries**:
- "Quotation Expiry: Quotation #X marked as expired"
- "Quotation Expiry: Cancelled Y booking(s) for quotation #X"
- "Quotation Expiry: Sent expiry notifications for quotation #X"
- "Quotation Expiry: Updated thread for quotation #X"
- "Quotation Expiry: Processed X expired quotations"
- "Quotation Expiry: Sent Y expiry warnings"

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | Dec 13, 2025 | Initial implementation |

---

## Dependencies

**Required**:
- PROMPT 105 (OfferExpiryService) - ✅ Complete
- ThreadService - ✅ Existing
- Laravel Notifications - ✅ Built-in
- Laravel Scheduler - ✅ Built-in
- Settings Model - ✅ Existing

**Optional**:
- Queue driver (database/redis) for background processing
- Email service (SMTP/Mailgun/etc.) for notifications

---

## Testing Checklist

- [x] Expiry detection works
- [x] Quotations marked as rejected
- [x] Bookings auto-cancelled
- [x] Customer notifications sent
- [x] Vendor notifications sent
- [x] Thread messages posted
- [x] Warning notifications sent
- [x] Settings respected
- [x] Scheduler runs correctly
- [x] Dry-run mode works
- [x] Statistics calculated
- [x] Days remaining accurate
- [x] Expiry labels correct

---

## Support

**Documentation**: docs/PROMPT_106_QUOTATION_DEADLINE_AUTO_CANCEL.md  
**Tests**: tests/Feature/QuotationExpiryServiceTest.php  
**Related**: PROMPT 105 (Offer Auto-Expiry)

---

**Status**: ✅ Ready for Production  
**Last Updated**: December 13, 2025
