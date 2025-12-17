# 🚀 PROMPT 47 Quick Reference

## New Timeline Stages (3)

| Stage | Type | Icon | Scheduled | Notifications |
|-------|------|------|-----------|---------------|
| Purchase Order | Manual | 📄 fa-file-contract | After quotation | Customer, Vendor, Admin |
| Designing | Manual | ✏️ fa-pencil-ruler | -12 days | Vendor |
| Survey | Manual | 📋 fa-clipboard-check | Mid-campaign (optional) | Vendor |

## API Cheat Sheet

### Start Stage with Note
```bash
curl -X POST /bookings/123/timeline/start-stage-with-note \
  -H "Content-Type: application/json" \
  -d '{
    "event_type": "designing",
    "note": "Client approved concept"
  }'
```

### Complete Stage with Note
```bash
curl -X POST /bookings/123/timeline/complete-stage-with-note \
  -H "Content-Type: application/json" \
  -d '{
    "event_type": "purchase_order",
    "note": "PO #12345 signed"
  }'
```

### Add Note to Event
```bash
curl -X POST /bookings/123/timeline/events/456/add-note \
  -H "Content-Type: application/json" \
  -d '{
    "note": "Client requested color change"
  }'
```

### Existing Endpoints (Enhanced)
```bash
# Now supports: designing, survey, purchase_order + optional note
POST /bookings/123/timeline/start-stage
{
  "stage": "designing",
  "note": "Optional note"
}

POST /bookings/123/timeline/complete-stage
{
  "stage": "purchase_order",
  "note": "Optional note"
}
```

## PHP Usage

### Start Stage with Note
```php
use App\Services\BookingTimelineService;
use App\Models\BookingTimelineEvent;

$timelineService = app(BookingTimelineService::class);

$event = $timelineService->startStageWithNote(
    $booking,
    BookingTimelineEvent::TYPE_DESIGNING,
    'Client approved concept, starting design',
    auth()->user()
);
```

### Complete Stage with Note
```php
$event = $timelineService->completeStageWithNote(
    $booking,
    BookingTimelineEvent::TYPE_PO,
    'PO #12345 signed by client',
    auth()->user()
);
```

### Add Note to Event
```php
$event = $timelineService->updateEventWithNote(
    $event,
    'Client requested minor color adjustments',
    auth()->user()
);
```

## Event Types Constants

```php
// New (PROMPT 47)
BookingTimelineEvent::TYPE_PO            // 'purchase_order'
BookingTimelineEvent::TYPE_DESIGNING     // 'designing'
BookingTimelineEvent::TYPE_SURVEY        // 'survey'

// Existing (PROMPT 38)
BookingTimelineEvent::TYPE_ENQUIRY       // 'enquiry'
BookingTimelineEvent::TYPE_OFFER         // 'offer'
BookingTimelineEvent::TYPE_QUOTATION     // 'quotation'
BookingTimelineEvent::TYPE_PAYMENT_HOLD  // 'payment_hold'
BookingTimelineEvent::TYPE_PAYMENT_SETTLED // 'payment_settled'
BookingTimelineEvent::TYPE_GRAPHICS      // 'graphics'
BookingTimelineEvent::TYPE_PRINTING      // 'printing'
BookingTimelineEvent::TYPE_MOUNTING      // 'mounting'
BookingTimelineEvent::TYPE_PROOF         // 'proof'
BookingTimelineEvent::TYPE_CAMPAIGN      // 'campaign'
```

## Notification Recipients

```php
// Set in generateFullTimeline()
'notify_customer' => true,  // Email + database notification to customer
'notify_vendor' => true,    // Email + database notification to vendor
'notify_admin' => true,     // Email + database notification to all admins
```

## Note Structure

```json
{
  "notes": [
    {
      "note": "Client approved design",
      "user_id": 1,
      "user_name": "John Doe",
      "user_role": "admin",
      "timestamp": "2025-01-15 10:00:00"
    }
  ]
}
```

## Timeline Progression

```
1. Enquiry Received (auto) ✅
2. Offer Created (auto) ✅
3. Quotation Generated (auto) ✅ → 👥 notify customer, vendor
4. Purchase Order (manual) 📄 → 👥 notify customer, vendor, admin
5. Payment Hold (auto) ✅ → 👥 notify customer, admin
6. Payment Settled (auto) ✅ → 👥 notify customer, vendor, admin
7. Designing (manual, -12d) ✏️ → 👥 notify vendor
8. Graphics (manual, -10d) 🎨 → 👥 notify vendor
9. Printing (manual, -7d) 🖨️ → 👥 notify vendor
10. Mounting (manual, -2d) 🔧 → 👥 notify vendor, customer
11. Campaign Started (auto) ✅ → 👥 notify customer, vendor, admin
12. Survey (manual, optional) 📋 → 👥 notify vendor
13. Proof of Display (manual, +1d) 📸 → 👥 notify customer, admin
14. Campaign Running (auto) 🔄 → 👥 notify admin
15. Campaign Completed (auto) ✅ → 👥 notify customer, vendor, admin
```

## Troubleshooting

### No Notifications Sent?
```bash
# Check queue is running
php artisan queue:work

# Check observer registered
grep "ObservedBy" app/Models/BookingTimelineEvent.php

# Check logs
tail -f storage/logs/laravel.log
```

### Notes Not Saving?
```php
// Ensure using *WithNote() methods
$timelineService->startStageWithNote(/* ... */);
// NOT: $timelineService->startProductionEvent(/* ... */);

// Check user authenticated
if (auth()->check()) {
    // Good to go
}
```

### Stage Won't Start?
```php
// Check event exists and is pending
$event = $booking->timelineEvents()
    ->where('event_type', BookingTimelineEvent::TYPE_DESIGNING)
    ->where('status', 'pending')
    ->first();

if (!$event) {
    // Generate timeline first
    $timelineService->generateFullTimeline($booking);
}
```

## Testing

```bash
# Run tests
php artisan test --filter BookingTimelineEnhancedTest

# Test specific method
php artisan test --filter it_starts_stage_with_note
```

## Files Modified

✅ `app/Models/BookingTimelineEvent.php` (3 constants, 1 field, observer)  
✅ `app/Services/BookingTimelineService.php` (+143 lines, 3 methods)  
✅ `app/Http/Controllers/Admin/BookingTimelineController.php` (+90 lines, 3 endpoints)  
✅ `routes/web.php` (+3 routes)  
✅ `app/Notifications/BookingTimelineStageNotification.php` (NEW, 180 lines)  
✅ `app/Observers/BookingTimelineEventObserver.php` (NEW, 75 lines)  
✅ `database/migrations/*_add_notify_admin_to_booking_timeline_events_table.php` (NEW)  

## Migration

```bash
php artisan migrate
```

## Documentation

📖 Full docs: `docs/BOOKING_TIMELINE_ENHANCED.md`  
📝 Summary: `PROMPT_47_SUMMARY.md`  
🧪 Tests: `tests/Feature/BookingTimelineEnhancedTest.php`  

---
**Status**: ✅ Production Ready  
**Breaking Changes**: None  
**Backward Compatible**: Yes
