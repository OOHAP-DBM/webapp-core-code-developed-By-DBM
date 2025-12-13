# PROMPT 106: Quotation Deadline + Auto-Cancel

## Overview

This feature implements automatic quotation expiry handling with consequences:
- **Auto-cancel booking flow** when quotations expire
- **Notify customer and vendor** of expiry
- **Update conversation threads** with expiry status
- **Send warning notifications** before expiry

### Key Features

- **Automatic Expiry Detection**: Quotations expire when their related offers expire
- **Booking Auto-Cancellation**: Pending/draft bookings are automatically cancelled
- **Multi-Channel Notifications**: Email + database notifications to both parties
- **Thread Integration**: System messages posted to conversation threads
- **Expiry Warnings**: Proactive warnings sent 2 days before expiry
- **Configurable Settings**: Admin control over all aspects of expiry handling
- **Comprehensive Logging**: Full audit trail of all actions

---

## Business Logic Flow

```
Offer Expiry Detected (from PROMPT 105)
         ↓
QuotationExpiryService Checks Related Quotations
         ↓
   Mark Quotation as Expired (STATUS_REJECTED)
         ↓
   Auto-Cancel Related Bookings (if enabled)
         ├─> Cancel bookings in pending_payment/draft/payment_hold
         └─> Set cancellation_reason = "Quotation expired"
         ↓
   Send Notifications (if enabled)
         ├─> Customer: QuotationExpiredNotification
         ├─> Vendor: QuotationExpiredNotification
         ├─> Customer: QuotationBookingCancelledNotification (if booking cancelled)
         └─> Vendor: QuotationBookingCancelledNotification (if booking cancelled)
         ↓
   Update Conversation Thread
         └─> Post system message with expiry details
```

---

## Files Created/Modified

### Services

**1. QuotationExpiryService** (`app/Services/QuotationExpiryService.php` - 395 lines)

Main service handling quotation expiry logic:

**Methods**:
- `isQuotationExpired(Quotation)` - Check if quotation has expired
- `markQuotationExpired(Quotation)` - Mark quotation as rejected due to expiry
- `processExpiredQuotations()` - Batch process all expired quotations
- `autoCancelBookingFlow(Quotation)` - Cancel related bookings
- `notifyExpiry(Quotation)` - Send notifications to customer and vendor
- `updateThreadForExpiry(Quotation)` - Post system message to thread
- `getQuotationsExpiringSoon($days)` - Get quotations expiring within X days
- `getQuotationsExpiringToday()` - Get quotations expiring today
- `getExpiryStatistics()` - Get analytics data
- `sendExpiryWarnings()` - Send warning notifications
- `getDaysRemaining(Quotation)` - Calculate days until expiry
- `getExpiryLabel(Quotation)` - Get human-readable expiry status

---

### Commands

**2. ProcessExpiredQuotationsCommand** (`app/Console/Commands/ProcessExpiredQuotationsCommand.php` - 145 lines)

Artisan command for processing expired quotations.

**Signature**: `php artisan quotations:process-expired`

**Options**:
- `--dry-run`: Preview without processing
- `--warnings`: Send warning notifications

**Features**:
- Displays table of expired quotations
- Confirmation prompt before processing
- Statistics display after processing
- Dry-run mode for testing

**Schedule**:
- **Main task**: Runs hourly to process expired quotations
- **Warnings**: Runs twice daily (9 AM and 5 PM) to send warnings

---

### Notifications

**3. QuotationExpiredNotification** (`app/Notifications/QuotationExpiredNotification.php` - 95 lines)

Notifies customer and vendor when quotation expires.

**Channels**: `['mail', 'database']`

**Email Content**:
- Subject: "Quotation Expired - #X"
- Quotation details (ID, amount, expired date)
- Warning about auto-cancelled bookings
- Action button (View Enquiries/Quotations)
- Role-specific messaging (customer vs vendor)

**Database Payload**:
```php
[
    'type' => 'quotation_expired',
    'quotation_id' => int,
    'offer_id' => int,
    'customer_id' => int,
    'vendor_id' => int,
    'amount' => decimal,
    'expired_at' => ISO8601 timestamp,
    'message' => string,
]
```

---

**4. QuotationBookingCancelledNotification** (`app/Notifications/QuotationBookingCancelledNotification.php` - 100 lines)

Notifies about auto-cancelled bookings.

**Channels**: `['mail', 'database']`

**Email Content**:
- Subject: "Booking Cancelled - Quotation Expired"
- Booking details (ID, number, amount, status)
- Quotation details (ID, expiry date)
- Cancellation reason
- Action button (View Bookings)

**Database Payload**:
```php
[
    'type' => 'booking_auto_cancelled',
    'booking_id' => int,
    'booking_number' => string,
    'quotation_id' => int,
    'customer_id' => int,
    'vendor_id' => int,
    'amount' => decimal,
    'cancelled_at' => ISO8601 timestamp,
    'message' => string,
]
```

---

**5. QuotationExpiryWarningNotification** (`app/Notifications/QuotationExpiryWarningNotification.php` - 95 lines)

Warning notification sent before expiry.

**Channels**: `['mail', 'database']`

**Email Content**:
- Subject: "⚠️ Quotation Expiring Soon - #X"
- Warning message with days remaining
- Quotation details
- Expiry date and countdown
- Urgency message about auto-cancellation
- Action button (View Quotation)

---

### Seeders

**6. QuotationExpirySettingsSeeder** (`database/seeders/QuotationExpirySettingsSeeder.php` - 75 lines)

Seeds configuration settings.

**Settings Created**:

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `quotation_default_expiry_days` | 7 | INTEGER | Default days for quotation expiry |
| `quotation_expiry_warning_days` | 2 | INTEGER | Days before expiry to send warnings |
| `quotation_auto_cancel_enabled` | true | BOOLEAN | Auto-cancel bookings on expiry |
| `quotation_notify_on_expiry` | true | BOOLEAN | Send expiry notifications |
| `quotation_update_thread_on_expiry` | true | BOOLEAN | Post system message to thread |

---

### Scheduler

**7. routes/console.php** (Modified)

Added scheduled tasks:

```php
// Process expired quotations hourly
Schedule::command('quotations:process-expired')
    ->hourly()
    ->name('process-expired-quotations')
    ->withoutOverlapping(5)
    ->onOneServer();

// Send expiry warnings twice daily
Schedule::command('quotations:process-expired --warnings')
    ->twiceDaily(9, 17) // 9 AM and 5 PM
    ->name('quotation-expiry-warnings')
    ->withoutOverlapping(5)
    ->onOneServer();
```

---

### Tests

**8. QuotationExpiryServiceTest** (`tests/Feature/QuotationExpiryServiceTest.php` - 565 lines, 23 tests)

**Test Coverage**:
- ✅ Expiry detection based on offer expiry
- ✅ Approved quotations don't expire
- ✅ Active offers don't trigger expiry
- ✅ Marking quotations as expired
- ✅ Batch processing of expired quotations
- ✅ Auto-cancellation of related bookings
- ✅ Respects auto-cancel setting
- ✅ Thread update with system message
- ✅ Expiring soon queries
- ✅ Expiring today queries
- ✅ Expiry statistics
- ✅ Expiry notifications (customer and vendor)
- ✅ Respects notification setting
- ✅ Booking cancellation notifications
- ✅ Days remaining calculation
- ✅ Expiry label generation
- ✅ Warning notifications

---

## Integration Points

### 1. OfferExpiryService (PROMPT 105)

QuotationExpiryService builds on offer expiry:

```php
// Check if related offer has expired
if ($quotation->offer) {
    return $this->offerExpiryService->isOfferExpired($quotation->offer);
}

// Get days remaining from offer
return $this->offerExpiryService->getDaysRemaining($quotation->offer);
```

**Dependency**: Quotation expiry is determined by offer expiry. When an offer expires (via PROMPT 105), this triggers quotation consequences.

---

### 2. ThreadService (Threads Module)

Integration with conversation threads:

```php
// Get or create thread
$thread = $this->threadService->getOrCreateThread($quotation->offer->enquiry_id);

// Post system message
ThreadMessage::create([
    'thread_id' => $thread->id,
    'sender_type' => ThreadMessage::SENDER_SYSTEM,
    'message_type' => ThreadMessage::TYPE_SYSTEM,
    'message' => $expiryMessage,
    'quotation_id' => $quotation->id,
    'is_read_customer' => false,
    'is_read_vendor' => false,
]);

// Increment unread counts
$thread->incrementUnread('customer');
$thread->incrementUnread('vendor');
```

**System Message Format**:
```
⚠️ Quotation #42 has expired.

The quotation was based on Offer #15 which expired on Dec 13, 2025 3:30 PM. 
Any related booking requests have been automatically cancelled.

Please contact the vendor for a new quotation if you're still interested.
```

---

### 3. Booking Cancellation

Automatically cancels bookings in these states:
- `pending_payment`
- `draft`
- `payment_hold`

**Cancellation Fields Updated**:
```php
$booking->update([
    'status' => 'cancelled',
    'payment_status' => 'cancelled',
    'cancellation_reason' => 'Quotation expired before payment completion',
    'cancelled_at' => now(),
    'cancelled_by' => 'system',
]);
```

---

## Usage Examples

### Command Line

#### Dry Run (Preview)

```bash
php artisan quotations:process-expired --dry-run
```

Output:
```
Processing expired quotations...

Found 3 expired quotation(s):

┌────┬──────────────┬─────────────┬──────────┬──────────┬─────────────────────┬──────────┐
│ ID │ Customer     │ Vendor      │ Amount   │ Offer ID │ Expired At          │ Time Ago │
├────┼──────────────┼─────────────┼──────────┼──────────┼─────────────────────┼──────────┤
│ 42 │ John Smith   │ ABC Vendors │ $2,500   │ 15       │ 2025-12-11 14:30:00 │ 2d ago   │
│ 58 │ Jane Doe     │ XYZ Co      │ $4,200   │ 22       │ 2025-12-12 10:00:00 │ 1d ago   │
│ 61 │ Bob Johnson  │ ABC Vendors │ $1,800   │ 25       │ 2025-12-13 09:15:00 │ 3h ago   │
└────┴──────────────┴─────────────┴──────────┴──────────┴─────────────────────┴──────────┘

DRY RUN: No quotations were actually processed.
```

---

#### Actual Processing

```bash
php artisan quotations:process-expired
```

Output:
```
Processing expired quotations...

Found 3 expired quotation(s):
[... table ...]

Do you want to process these 3 quotation(s)? (yes/no) [yes]:
> yes

Processing...

✅ Successfully processed 3 quotation(s).

Quotation Expiry Statistics:
┌──────────────────────────┬────────┐
│ Metric                   │ Value  │
├──────────────────────────┼────────┤
│ Total Active Quotations  │ 145    │
│ Total Expired Quotations │ 58     │
│ Expiring Today           │ 12     │
│ Expiring Soon            │ 23     │
│ Expiry Rate              │ 28.57% │
└──────────────────────────┴────────┘
```

---

#### Send Warnings

```bash
php artisan quotations:process-expired --warnings
```

Output:
```
Processing expired quotations...

Sending expiry warnings...
✅ Sent 5 expiry warning(s).

No expired quotations found.
```

---

### Programmatic Usage

#### Check If Quotation Expired

```php
use App\Services\QuotationExpiryService;

$expiryService = app(QuotationExpiryService::class);

if ($expiryService->isQuotationExpired($quotation)) {
    return response()->json(['error' => 'This quotation has expired'], 410);
}
```

---

#### Get Quotations Expiring Soon

```php
// Get quotations expiring in next 3 days
$quotations = $expiryService->getQuotationsExpiringSoon(3);

foreach ($quotations as $quotation) {
    // Send custom reminder
}
```

---

#### Display Expiry Info

```php
// In Blade template
<div class="quotation-card">
    <h3>Quotation #{{ $quotation->id }}</h3>
    <p>Amount: ${{ number_format($quotation->grand_total, 2) }}</p>
    
    <div class="expiry-badge 
        @if($expiryService->getDaysRemaining($quotation) <= 1) badge-danger
        @elseif($expiryService->getDaysRemaining($quotation) <= 3) badge-warning
        @else badge-info @endif">
        
        {{ $expiryService->getExpiryLabel($quotation) }}
    </div>
</div>
```

---

#### Get Statistics

```php
$stats = $expiryService->getExpiryStatistics();

// Dashboard widget
return view('admin.dashboard', [
    'total_active_quotations' => $stats['total_active'],
    'total_expired_quotations' => $stats['total_expired'],
    'expiring_today' => $stats['expiring_today'],
    'expiring_soon' => $stats['expiring_soon'],
    'expiry_rate' => $stats['expiry_rate'],
]);
```

---

## Settings Configuration

### Via Admin Panel

Navigate to **Settings > General** or **Settings > Notifications** to configure:

1. **Default Expiry Days** (7 days)
2. **Warning Days** (2 days before expiry)
3. **Enable Auto-Cancel** (true/false)
4. **Enable Notifications** (true/false)
5. **Enable Thread Updates** (true/false)

---

### Programmatically

```php
use App\Models\Setting;

// Change default expiry
Setting::setValue('quotation_default_expiry_days', 10);

// Disable auto-cancel
Setting::setValue('quotation_auto_cancel_enabled', false);

// Change warning threshold
Setting::setValue('quotation_expiry_warning_days', 3);
```

---

## Email Notification Examples

### Quotation Expired (Customer)

**Subject**: Quotation Expired - #42

```
Hello John Smith,

The quotation from ABC Vendors has expired as the acceptance deadline has passed. 
Any booking requests related to this quotation have been automatically cancelled.

Quotation Details:
- Quotation ID: #42
- Offer ID: #15
- Amount: $2,500.00
- Expired On: Dec 11, 2025 2:30 PM

Important: Any related booking requests have been automatically cancelled.

If you are still interested, please contact the vendor for a new quotation or 
create a new enquiry.

[View Enquiries]

Thank you for using our platform!
```

---

### Quotation Expired (Vendor)

**Subject**: Quotation Expired - #42

```
Hello ABC Vendors,

Your quotation to John Smith has expired as the customer did not respond within 
the deadline. Any pending bookings have been automatically cancelled.

Quotation Details:
- Quotation ID: #42
- Offer ID: #15
- Amount: $2,500.00
- Expired On: Dec 11, 2025 2:30 PM

Important: Any related booking requests have been automatically cancelled.

If the customer reaches out again, you can create a new offer and quotation.

[View Quotations]

Thank you for using our platform!
```

---

### Expiry Warning (2 days before)

**Subject**: ⚠️ Quotation Expiring Soon - #42

```
Hello John Smith,

The quotation from ABC Vendors will expire in 2 days. Please complete your 
booking and payment before the deadline to avoid automatic cancellation.

Quotation Details:
- Quotation ID: #42
- Amount: $2,500.00
- Expires In: Expires in 2 days
- Expiry Date: Dec 15, 2025 2:30 PM

⚠️ Important: After expiry, this quotation and any related booking requests 
will be automatically cancelled.

[View Quotation]

Please review and accept the quotation as soon as possible. Complete payment 
promptly to secure your booking.
```

---

### Booking Auto-Cancelled

**Subject**: Booking Cancelled - Quotation Expired

```
Hello John Smith,

Your booking (#BK-ABC123) has been automatically cancelled because the quotation 
expired before payment was completed. The quotation deadline passed on Dec 11, 2025.

Booking Details:
- Booking ID: #101
- Booking Number: BK-ABC123
- Amount: $2,500.00
- Status: Cancelled

Quotation Details:
- Quotation ID: #42
- Expired On: Dec 11, 2025 2:30 PM

Cancellation Reason: Quotation expired before payment completion.

If you would like to proceed with this booking, please request a new quotation 
from the vendor.

[View Bookings]

Thank you for using our platform!
```

---

## Thread Integration

### System Message Posted

When a quotation expires, this message is posted to the conversation thread:

```
⚠️ Quotation #42 has expired.

The quotation was based on Offer #15 which expired on Dec 13, 2025 3:30 PM. Any 
related booking requests have been automatically cancelled.

Please contact the vendor for a new quotation if you're still interested.
```

**Message Attributes**:
- `sender_type`: `SENDER_SYSTEM`
- `message_type`: `TYPE_SYSTEM`
- `quotation_id`: ID of expired quotation
- `is_read_customer`: false (marked as unread)
- `is_read_vendor`: false (marked as unread)
- Unread counts incremented for both parties

---

## Deployment Steps

### 1. Run Migrations

```bash
# Ensure offer expiry migration from PROMPT 105 is run
php artisan migrate
```

### 2. Seed Settings

```bash
php artisan db:seed --class=QuotationExpirySettingsSeeder
```

### 3. Test Commands

```bash
# Dry run to verify
php artisan quotations:process-expired --dry-run

# Test warnings
php artisan quotations:process-expired --warnings --dry-run
```

### 4. Run Tests

```bash
php artisan test tests/Feature/QuotationExpiryServiceTest.php
```

### 5. Verify Scheduler

```bash
# Check scheduled tasks
php artisan schedule:list

# Test scheduler manually
php artisan schedule:run
```

### 6. Configure Cron (Production)

Ensure Laravel scheduler is running:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Troubleshooting

### Issue: Quotations Not Processing

**Symptoms**: Expired quotations remain active

**Possible Causes**:
1. Scheduler not running
2. Offers not marked as expired
3. Auto-cancel disabled

**Solutions**:
```bash
# 1. Check scheduler
php artisan schedule:list

# 2. Run offers expiry first
php artisan offers:expire

# 3. Run quotations processing
php artisan quotations:process-expired --dry-run

# 4. Check settings
php artisan tinker
>>> App\Models\Setting::getValue('quotation_auto_cancel_enabled')
```

---

### Issue: Notifications Not Sending

**Symptoms**: No emails received on expiry

**Possible Causes**:
1. Notifications disabled in settings
2. Queue not processing
3. Email configuration issues

**Solutions**:
```bash
# 1. Check setting
php artisan tinker
>>> App\Models\Setting::getValue('quotation_notify_on_expiry')

# 2. Process queue
php artisan queue:work

# 3. Test email
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'))
```

---

### Issue: Bookings Not Cancelling

**Symptoms**: Bookings remain active after quotation expiry

**Possible Causes**:
1. Auto-cancel disabled
2. Booking in wrong status
3. No quotation_id link

**Solutions**:
```bash
# 1. Enable auto-cancel
php artisan tinker
>>> App\Models\Setting::setValue('quotation_auto_cancel_enabled', true)

# 2. Check booking status
>>> Booking::where('quotation_id', 42)->get(['id', 'status', 'payment_status'])

# 3. Verify quotation link
>>> $booking->quotation_id
```

---

### Issue: Thread Not Updating

**Symptoms**: No system message in conversation

**Check**:
1. Thread exists for enquiry
2. Setting enabled
3. Service executed without errors

**Debug**:
```bash
php artisan tinker
>>> $quotation = App\Models\Quotation::find(42)
>>> $thread = Modules\Threads\Models\Thread::where('enquiry_id', $quotation->offer->enquiry_id)->first()
>>> $thread->messages()->where('message_type', 'system')->get()
```

---

## Best Practices

### 1. Monitor Expiry Rates

```php
// Add to admin dashboard
$stats = app(QuotationExpiryService::class)->getExpiryStatistics();

if ($stats['expiry_rate'] > 30) {
    // High expiry rate - may indicate:
    // - Expiry period too short
    // - Poor customer engagement
    // - Pricing issues
}
```

---

### 2. Proactive Customer Engagement

```php
// Send reminders to customers with expiring quotations
$expiringSoon = app(QuotationExpiryService::class)->getQuotationsExpiringSoon(3);

foreach ($expiringSoon as $quotation) {
    // Custom follow-up logic
    if ($quotation->getDaysRemaining() === 1) {
        // Send urgent reminder
    }
}
```

---

### 3. Vendor Performance Tracking

```php
// Track vendors with high quotation expiry rates
$vendors = User::where('role', 'vendor')->get();

foreach ($vendors as $vendor) {
    $active = Quotation::where('vendor_id', $vendor->id)
        ->whereIn('status', ['sent', 'draft'])
        ->count();
    
    $expired = Quotation::where('vendor_id', $vendor->id)
        ->where('status', 'rejected')
        ->where('notes', 'LIKE', '%AUTO-EXPIRED%')
        ->count();
    
    $expiryRate = $active > 0 ? ($expired / $active) * 100 : 0;
    
    // Flag vendors with >40% expiry rate
}
```

---

### 4. Extend Expiry for VIP Customers

```php
// Custom logic in OfferService
if ($customer->is_vip) {
    $expiryDays = 14; // Extended period for VIPs
} else {
    $expiryDays = app(OfferExpiryService::class)->getDefaultExpiryDays();
}

app(OfferExpiryService::class)->setOfferExpiry($offer, $expiryDays);
```

---

## Summary

**PROMPT 106 Status**: ✅ Complete

**Files Created**:
- QuotationExpiryService (395 lines)
- ProcessExpiredQuotationsCommand (145 lines)
- 3 Notification classes (290 lines total)
- QuotationExpirySettingsSeeder (75 lines)
- QuotationExpiryServiceTest (565 lines, 23 tests)

**Total**: ~1,470 lines of production code + tests

**Integration**:
- ✅ Extends PROMPT 105 (Offer Auto-Expiry)
- ✅ Integrates with ThreadService
- ✅ Uses existing Booking model
- ✅ Uses existing notification system
- ✅ Configurable via Settings

**Features Delivered**:
- ✅ Auto-expire quotations based on offer deadlines
- ✅ Auto-cancel related booking flow
- ✅ Notify customer and vendor (email + database)
- ✅ Update conversation threads
- ✅ Send proactive warnings
- ✅ Comprehensive testing (23 tests)
- ✅ Full documentation

---

**Version**: 1.0  
**Date**: December 13, 2025  
**Status**: ✅ Ready for Production  
**Dependencies**: PROMPT 105 (Offer Auto-Expiry)
