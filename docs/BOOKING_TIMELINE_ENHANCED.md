# Booking Timeline Enhanced System (PROMPT 47)

## Overview
Enhanced timeline system that tracks complete booking lifecycle with automated notifications and comprehensive audit trail.

## Complete Lifecycle (15 Stages)

### 1. Enquiry Received (Auto-completed)
- **When**: Customer submits enquiry
- **Status**: `completed`
- **Notifications**: None (system event)

### 2. Offer Created (Auto-completed)
- **When**: Customer sends offer
- **Status**: `completed`
- **Notifications**: None (system event)

### 3. Quotation Generated (Auto-completed)
- **When**: Quotation created from offer
- **Status**: `completed`
- **Notifications**: Customer, Vendor
- **Description**: "Quotation generated with pricing and terms"

### 4. Purchase Order (NEW - Manual/Auto)
- **When**: After quotation acceptance
- **Status**: `pending` → `completed`
- **Notifications**: Customer, Vendor, Admin
- **Description**: "Purchase order issued for booking"
- **Icon**: `fa-file-contract`
- **Actions**: 
  - Start: `POST /bookings/{id}/timeline/start-stage` (stage=purchase_order)
  - Complete: `POST /bookings/{id}/timeline/complete-stage` (stage=purchase_order)

### 5. Payment Hold (Auto)
- **When**: Payment initiated
- **Status**: `completed`
- **Notifications**: Customer, Admin
- **Description**: "Payment authorized and on hold"

### 6. Payment Settled (Auto)
- **When**: Payment captured
- **Status**: `completed`
- **Notifications**: Customer, Vendor, Admin
- **Description**: "Payment successfully settled"

### 7. Designing (NEW - Manual, -12 days before campaign)
- **When**: Creative concept phase
- **Status**: `pending` → `in_progress` → `completed`
- **Notifications**: Vendor
- **Description**: "Creative design and concept development"
- **Icon**: `fa-pencil-ruler`
- **Scheduled**: 12 days before campaign start
- **Actions**:
  - Start: `POST /bookings/{id}/timeline/start-stage` (stage=designing)
  - Complete: `POST /bookings/{id}/timeline/complete-stage` (stage=designing)

### 8. Graphics Design (Manual, -10 days)
- **When**: Final graphics creation
- **Status**: `pending` → `in_progress` → `completed`
- **Notifications**: Vendor
- **Description**: "Final graphics and artwork preparation"
- **Scheduled**: 10 days before campaign start
- **Actions**:
  - Start: `POST /bookings/{id}/timeline/start-stage` (stage=graphics)
  - Complete: `POST /bookings/{id}/timeline/complete-stage` (stage=graphics)

### 9. Printing (Manual, -7 days)
- **When**: Material printing phase
- **Status**: `pending` → `in_progress` → `completed`
- **Notifications**: Vendor
- **Description**: "Printing campaign materials"
- **Scheduled**: 7 days before campaign start
- **Actions**:
  - Start: `POST /bookings/{id}/timeline/start-stage` (stage=printing)
  - Complete: `POST /bookings/{id}/timeline/complete-stage` (stage=printing)

### 10. Mounting (Manual, -2 days)
- **When**: Installation on hoarding
- **Status**: `pending` → `in_progress` → `completed`
- **Notifications**: Vendor, Customer
- **Description**: "Mounting campaign on hoarding"
- **Scheduled**: 2 days before campaign start
- **Actions**:
  - Start: `POST /bookings/{id}/timeline/start-stage` (stage=mounting)
  - Complete: `POST /bookings/{id}/timeline/complete-stage` (stage=mounting)

### 11. Campaign Started (Auto)
- **When**: Campaign start date reached
- **Status**: `completed`
- **Notifications**: Customer, Vendor, Admin
- **Description**: "Campaign is now live"

### 12. Survey (NEW - Manual, Optional, Mid-campaign)
- **When**: Optional quality check
- **Status**: `pending` → `in_progress` → `completed`
- **Notifications**: Vendor
- **Description**: "Campaign quality and compliance survey"
- **Icon**: `fa-clipboard-check`
- **Scheduled**: Mid-campaign (optional)
- **Actions**:
  - Start: `POST /bookings/{id}/timeline/start-stage` (stage=survey)
  - Complete: `POST /bookings/{id}/timeline/complete-stage` (stage=survey)

### 13. Proof of Display (Manual, +1 day)
- **When**: After campaign starts
- **Status**: `pending` → `completed`
- **Notifications**: Customer, Admin
- **Description**: "Photo evidence of campaign display"
- **Scheduled**: 1 day after campaign start
- **Actions**:
  - Complete: `POST /bookings/{id}/timeline/complete-stage` (stage=proof)

### 14. Campaign Running (Auto)
- **When**: During campaign period
- **Status**: `in_progress`
- **Notifications**: Admin
- **Description**: "Campaign is actively running"

### 15. Campaign Completed (Auto)
- **When**: Campaign end date reached
- **Status**: `completed`
- **Notifications**: Customer, Vendor, Admin
- **Description**: "Campaign has ended successfully"

## API Endpoints

### Basic Stage Management

#### Start Production Stage
```http
POST /bookings/{booking_id}/timeline/start-stage
Content-Type: application/json

{
    "stage": "designing|graphics|printing|mounting|proof|survey|purchase_order",
    "note": "Optional note about starting this stage"
}
```

#### Complete Production Stage
```http
POST /bookings/{booking_id}/timeline/complete-stage
Content-Type: application/json

{
    "stage": "designing|graphics|printing|mounting|proof|survey|purchase_order",
    "note": "Optional completion note"
}
```

### Enhanced Note Management (PROMPT 47)

#### Start Stage with Note
```http
POST /bookings/{booking_id}/timeline/start-stage-with-note
Content-Type: application/json

{
    "event_type": "designing",
    "note": "Started design phase with client requirements"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Stage started successfully",
    "event": {
        "id": 123,
        "booking_id": 456,
        "event_type": "designing",
        "title": "Designing",
        "status": "in_progress",
        "user_id": 1,
        "user_name": "John Doe",
        "started_at": "2025-01-15 10:00:00",
        "metadata": {
            "notes": [
                {
                    "note": "Started design phase with client requirements",
                    "user_id": 1,
                    "user_name": "John Doe",
                    "user_role": "admin",
                    "timestamp": "2025-01-15 10:00:00"
                }
            ]
        }
    }
}
```

#### Complete Stage with Note
```http
POST /bookings/{booking_id}/timeline/complete-stage-with-note
Content-Type: application/json

{
    "event_type": "designing",
    "note": "Design approved by client, moving to graphics"
}
```

#### Add Note to Existing Event
```http
POST /bookings/{booking_id}/timeline/events/{event_id}/add-note
Content-Type: application/json

{
    "note": "Client requested minor revisions"
}
```

### Timeline Queries

#### Get Complete Timeline
```http
GET /bookings/{booking_id}/timeline/api
```

#### Get Timeline Progress
```http
GET /bookings/{booking_id}/timeline/progress
```

**Response:**
```json
{
    "success": true,
    "progress": {
        "percentage": 60,
        "completed": 9,
        "total": 15,
        "current_stage": "Designing"
    }
}
```

#### Get Current Stage
```http
GET /bookings/{booking_id}/timeline/current-stage
```

## Notification System

### Auto-Notification Triggers
Every stage change automatically sends notifications via `BookingTimelineEventObserver`:
- When event created
- When event status changes

### Recipient Logic

#### Customer Notifications (notify_customer = true)
- Quotation Generated
- Payment Hold
- Payment Settled
- Mounting (installation update)
- Campaign Started
- Proof of Display
- Campaign Completed

**Message Example:**
> "Your booking #1234 for Main Street Billboard has progressed to Designing stage. Our team is working on creating compelling visuals for your campaign."

#### Vendor Notifications (notify_vendor = true)
- Quotation Generated
- Purchase Order
- Payment Settled
- Designing (action required)
- Graphics Design (action required)
- Printing (action required)
- Mounting (action required)
- Survey (action required)
- Campaign Started
- Campaign Completed

**Message Example:**
> "Action required for booking #1234 at Main Street Billboard. The Designing stage needs to be started. Please begin creative concept development."

#### Admin Notifications (notify_admin = true)
- Purchase Order
- Payment Hold
- Payment Settled
- Proof of Display
- Campaign Running
- Campaign Started
- Campaign Completed

**Message Example:**
> "Booking #1234 timeline updated: Purchase Order stage completed by John Doe. All documentation is in place."

### Notification Channels
- **Email**: Queued async emails with booking details
- **Database**: In-app notification panel

## Note Tracking System

### Note Structure
```php
[
    'notes' => [
        [
            'note' => 'Client approved initial concept',
            'user_id' => 1,
            'user_name' => 'John Doe',
            'user_role' => 'admin',
            'timestamp' => '2025-01-15 14:30:00'
        ],
        [
            'note' => 'Revisions requested for color scheme',
            'user_id' => 2,
            'user_name' => 'Jane Smith',
            'user_role' => 'vendor',
            'timestamp' => '2025-01-15 16:45:00'
        ]
    ]
]
```

### Use Cases
- **Handover Notes**: Pass context between team members
- **Issue Tracking**: Document problems and resolutions
- **Client Feedback**: Record customer requests/approvals
- **Audit Trail**: Compliance and quality assurance

## User Attribution

Every event stores:
- `user_id`: Who made the change
- `user_name`: Display name
- Captured from `auth()->user()` automatically

## Code Components

### Models
- `App\Models\BookingTimelineEvent`
  - Added: TYPE_PO, TYPE_DESIGNING, TYPE_SURVEY
  - Added: notify_admin field
  - Observer: BookingTimelineEventObserver

### Services
- `App\Services\BookingTimelineService`
  - Method: `updateEventWithNote($event, $note, $user)`
  - Method: `startStageWithNote($booking, $eventType, $note, $user)`
  - Method: `completeStageWithNote($booking, $eventType, $note, $user)`
  - Updated: `generateFullTimeline()` with 15 stages
  - Updated: `getProductionStatus()` with designing stage

### Notifications
- `App\Notifications\BookingTimelineStageNotification`
  - Universal notification for all stages
  - Dynamic messaging per recipient type
  - Email + Database channels

### Observers
- `App\Observers\BookingTimelineEventObserver`
  - Auto-sends notifications on create/update
  - Handles customer, vendor, admin notifications
  - Error logging for failed sends

### Controllers
- `App\Http\Controllers\Admin\BookingTimelineController`
  - Updated: `startStage()` - supports new stages + notes
  - Updated: `completeStage()` - supports new stages + notes
  - New: `startStageWithNote()`
  - New: `completeStageWithNote()`
  - New: `addNote()`

### Database
- Migration: `add_notify_admin_to_booking_timeline_events_table`
  - Added: `notify_admin` boolean column

## Usage Examples

### Example 1: Start Designing with Note
```php
// Admin starts designing stage
$event = $timelineService->startStageWithNote(
    $booking,
    BookingTimelineEvent::TYPE_DESIGNING,
    'Client approved concept. Starting design phase.',
    auth()->user()
);

// Automatically:
// - Event status → in_progress
// - Vendor receives notification
// - Note stored with user attribution
// - started_at timestamp recorded
```

### Example 2: Complete PO with Note
```php
// Admin completes purchase order
$event = $timelineService->completeStageWithNote(
    $booking,
    BookingTimelineEvent::TYPE_PO,
    'PO #12345 signed by client. Payment expected within 7 days.',
    auth()->user()
);

// Automatically:
// - Event status → completed
// - Customer, Vendor, Admin receive notifications
// - Note stored with timestamp
// - completed_at timestamp recorded
```

### Example 3: Add Note to Existing Event
```php
// Vendor adds progress update
$event = $timelineService->updateEventWithNote(
    $event,
    'Design 60% complete. Client requested minor color adjustments.',
    auth()->user()
);

// Note appended to existing notes array
// No status change
// No new notifications (unless status updated separately)
```

## Integration with Existing Features

### Backward Compatibility
- Existing 13-stage bookings continue working
- New 15-stage timeline generated for new bookings
- No breaking changes to existing API

### HasTimeline Trait
All models using `HasTimeline` automatically get:
- Full 15-stage lifecycle
- Auto-notification support
- Note tracking capability

### Observer Pattern
Zero configuration required:
- Observer registered via `#[ObservedBy]` attribute
- Notifications sent automatically
- Error handling prevents timeline disruption

## Best Practices

### When to Use Notes
✅ **Good Use Cases:**
- Client feedback/approvals
- Issue documentation
- Handover instructions
- Quality checks
- Compliance records

❌ **Avoid:**
- Excessive detail (keep under 1000 chars)
- Sensitive information
- Duplicate status updates

### Stage Progression
1. **Manual Stages**: designing, graphics, printing, mounting, survey, proof, purchase_order
2. **Auto Stages**: enquiry, offer, quotation, payment_hold, payment_settled, campaign_started, campaign_completed

### Notification Preferences
Set flags in `generateFullTimeline()`:
```php
'notify_customer' => true,  // End-user updates
'notify_vendor' => true,    // Action items
'notify_admin' => true,     // Oversight
```

## Troubleshooting

### Notifications Not Sending
1. Check observer registered: `#[ObservedBy([BookingTimelineEventObserver::class])]`
2. Verify notification flags: `notify_customer`, `notify_vendor`, `notify_admin`
3. Check queue running: `php artisan queue:work`
4. Review logs: `storage/logs/laravel.log`

### Notes Not Appearing
1. Ensure using `*WithNote()` methods
2. Verify `metadata` column is JSON
3. Check user authenticated: `auth()->user()` not null

### Stage Not Starting
1. Verify event type in validation: `designing|survey|purchase_order`
2. Check event exists and is `pending` status
3. Ensure booking dates valid

## Performance Considerations

- **Queue Processing**: Notifications sent async via queue
- **Observer Overhead**: Minimal (only on create/update)
- **Note Storage**: JSON field, efficient for < 50 notes per event
- **Index Optimization**: booking_id, event_type indexed

## Security

- **Authorization**: Controller methods require authentication
- **Validation**: All inputs validated (event_type, note length)
- **XSS Protection**: Notes sanitized on display
- **Audit Trail**: Complete user attribution

## Future Enhancements

- [ ] Note mentions (@user notifications)
- [ ] File attachments to notes
- [ ] Timeline export (PDF/Excel)
- [ ] Custom notification preferences
- [ ] Bulk stage operations
- [ ] Timeline analytics dashboard

## Related Documentation
- [PROMPT 38: Original Timeline System](./BOOKING_TIMELINE.md)
- [PROMPT 44 & 45: Vendor Quote System](./VENDOR_QUOTE_SYSTEM.md)
- [Notification System](./NOTIFICATIONS.md)
- [Observer Pattern](./OBSERVERS.md)

---
**Last Updated**: 2025-01-10  
**Version**: 1.0 (PROMPT 47)  
**Status**: ✅ Complete
