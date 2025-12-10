# Vendor Panel: Booking Management (PROMPT 48)

## Overview
Enhanced vendor booking management with categorized views for better workflow management and campaign tracking.

## Booking Categories

### 1. New Bookings (`/vendor/bookings/new`)
**Definition**: Bookings that are pending payment hold or payment authorization but not yet confirmed.

**Logic**:
```php
- Status: PENDING_PAYMENT_HOLD or PAYMENT_HOLD
- AND (start_date is NULL OR start_date > today)
```

**Use Cases**:
- Monitor incoming bookings
- Track payment status
- Identify bookings awaiting customer payment

**Statistics**:
- Total new bookings
- Pending payment count
- Payment hold count

### 2. Ongoing Bookings (`/vendor/bookings/ongoing`)
**Definition**: Confirmed bookings where today falls between start and end dates.

**Logic**:
```php
- Status: CONFIRMED
- AND start_date <= today
- AND end_date >= today
```

**Use Cases**:
- Track active campaigns
- Monitor production stages (designing, printing, mounting)
- Ensure timely proof of display
- Identify campaigns ending soon

**Sub-Filters**:
- Just Started: Campaigns started within last 7 days
- Mid-Campaign: Progress is < 50%
- Ending Soon: End date within 7 days

**Statistics**:
- Total ongoing campaigns
- Just started count
- Ending soon count

### 3. Completed Bookings (`/vendor/bookings/completed`)
**Definition**: Confirmed bookings where end date has passed.

**Logic**:
```php
- Status: CONFIRMED
- AND end_date < today
```

**Use Cases**:
- Review campaign performance
- Track proof of display submissions
- Calculate revenue
- Historical analysis

**Sub-Filters**:
- With POD: Bookings with proof submitted
- Approved POD: Bookings with approved proof
- Missing POD: Bookings without proof

**Statistics**:
- Total completed campaigns
- With POD count
- Without POD count
- Total revenue from completed bookings

### 4. Cancelled Bookings (`/vendor/bookings/cancelled`)
**Definition**: Bookings that were cancelled or refunded.

**Logic**:
```php
- Status: CANCELLED or REFUNDED
```

**Use Cases**:
- Track cancellation patterns
- Analyze refund impact
- Review cancellation reasons
- Calculate lost revenue

**Sub-Filters**:
- Cancelled: Only cancelled bookings
- Refunded: Only refunded bookings

**Statistics**:
- Total cancelled bookings
- Cancelled count
- Refunded count
- Total lost revenue

## API Endpoints

### Booking List Endpoints

#### Get All Bookings (Legacy)
```http
GET /vendor/bookings
```

#### Get New Bookings
```http
GET /vendor/bookings/new
```

**Query Parameters**:
- `search` - Search by booking ID, customer name/phone, hoarding name/location
- `date_from` - Filter by start date (from)
- `date_to` - Filter by end date (to)
- `hoarding_id` - Filter by specific hoarding
- `customer_id` - Filter by specific customer
- `amount_min` - Minimum booking amount
- `amount_max` - Maximum booking amount
- `sort_by` - Sort by: date, amount, customer
- `sort_order` - asc or desc
- `page` - Pagination page number

**Response**:
```json
{
    "bookings": {
        "current_page": 1,
        "data": [
            {
                "id": 123,
                "customer": {...},
                "hoarding": {...},
                "start_date": "2025-01-15",
                "end_date": "2025-02-15",
                "total_amount": 50000.00,
                "status": "payment_hold",
                "hold_expiry_at": "2025-01-10 18:00:00"
            }
        ],
        "total": 45,
        "per_page": 20
    },
    "stats": {
        "total": 45,
        "pending_payment": 20,
        "payment_hold": 25
    }
}
```

#### Get Ongoing Bookings
```http
GET /vendor/bookings/ongoing
```

**Additional Query Parameters**:
- `progress` - just_started, mid_campaign, ending_soon

**Response**:
```json
{
    "bookings": {...},
    "stats": {
        "total": 38,
        "just_started": 12,
        "ending_soon": 8
    }
}
```

#### Get Completed Bookings
```http
GET /vendor/bookings/completed
```

**Additional Query Parameters**:
- `pod_status` - submitted, approved, missing

**Response**:
```json
{
    "bookings": {...},
    "stats": {
        "total": 156,
        "with_pod": 142,
        "without_pod": 14,
        "total_revenue": 7800000.00
    }
}
```

#### Get Cancelled Bookings
```http
GET /vendor/bookings/cancelled
```

**Additional Query Parameters**:
- `cancellation_type` - cancelled, refunded

**Response**:
```json
{
    "bookings": {...},
    "stats": {
        "total": 23,
        "cancelled": 18,
        "refunded": 5,
        "total_lost_revenue": 1150000.00
    }
}
```

### Booking Detail Endpoint

#### Get Single Booking
```http
GET /vendor/bookings/{id}
```

**Response**:
```json
{
    "id": 123,
    "customer": {
        "id": 456,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+91 98765 43210"
    },
    "hoarding": {
        "id": 789,
        "name": "Main Street Billboard",
        "location": "Downtown, Mumbai"
    },
    "quotation": {...},
    "start_date": "2025-01-15",
    "end_date": "2025-02-15",
    "duration_days": 31,
    "total_amount": 50000.00,
    "status": "confirmed",
    "timeline_events": [
        {
            "event_type": "enquiry",
            "status": "completed",
            "scheduled_date": "2025-01-01"
        },
        {
            "event_type": "designing",
            "status": "in_progress",
            "scheduled_date": "2025-01-03"
        }
    ],
    "status_logs": [...],
    "booking_proofs": [...],
    "payments": [...]
}
```

### Booking Actions

#### Confirm Booking (Legacy)
```http
POST /vendor/bookings/{id}/confirm
```

**Response**:
```json
{
    "success": true,
    "message": "Booking confirmed successfully!"
}
```

#### Cancel Booking
```http
POST /vendor/bookings/{id}/cancel

{
    "reason": "Customer requested cancellation"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Booking cancelled successfully!"
}
```

#### Update Booking Status
```http
POST /vendor/bookings/{id}/update-status

{
    "status": "confirmed|cancelled"
}
```

## Database Schema Enhancements

### Booking Model Scopes (PROMPT 48)

```php
// New bookings
Booking::new()

// Ongoing bookings (today between start and end)
Booking::ongoing()

// Completed bookings (end_date < today)
Booking::completed()

// Cancelled bookings (cancelled or refunded)
Booking::cancelledBookings()

// Filter by vendor
Booking::byVendor($vendorId)
```

### User Model Relationships (PROMPT 48)

```php
// Get vendor's bookings
$vendor->bookings()

// Get customer's bookings
$customer->customerBookings()

// Get vendor's tasks
$vendor->tasks()
```

## Controller Methods

### BookingController Methods

1. **`index(Request $request)`**
   - All bookings with unified filtering (legacy)
   - Returns: view with bookings and stats

2. **`newBookings(Request $request)`**
   - New bookings view
   - Filters: All standard filters
   - Stats: total, pending_payment, payment_hold

3. **`ongoingBookings(Request $request)`**
   - Ongoing campaigns view
   - Additional filter: progress (just_started, mid_campaign, ending_soon)
   - Stats: total, just_started, ending_soon

4. **`completedBookings(Request $request)`**
   - Completed campaigns view
   - Additional filter: pod_status (submitted, approved, missing)
   - Stats: total, with_pod, without_pod, total_revenue

5. **`cancelledBookings(Request $request)`**
   - Cancelled bookings view
   - Additional filter: cancellation_type (cancelled, refunded)
   - Stats: total, cancelled, refunded, total_lost_revenue

6. **`show($id)`**
   - Single booking details
   - Includes: customer, hoarding, quotation, timeline, status logs, proofs, payments

7. **`confirm($id)`**
   - Confirm booking (legacy method)

8. **`cancel(Request $request, $id)`**
   - Cancel booking with reason

9. **`updateStatus(Request $request, $id)`**
   - Update booking status

10. **`applyFilters($query, Request $request)` (Private)**
    - Applies common filters to query
    - Filters: search, hoarding_id, customer_id, date_from, date_to, amount_min, amount_max, sort_by

## Filtering System

### Common Filters (All Pages)
- **Search**: Booking ID, customer name/phone/email, hoarding name/location
- **Hoarding**: Filter by specific hoarding
- **Customer**: Filter by specific customer
- **Date Range**: Filter by start/end dates
- **Amount Range**: Min/max booking amount
- **Sorting**: By date, amount, customer (asc/desc)

### Page-Specific Filters

**Ongoing Bookings**:
- Progress: just_started, mid_campaign, ending_soon

**Completed Bookings**:
- POD Status: submitted, approved, missing

**Cancelled Bookings**:
- Cancellation Type: cancelled, refunded

## Usage Examples

### Example 1: Get Ongoing Bookings Ending Soon
```php
use Illuminate\Support\Facades\Auth;

$vendor = Auth::user();

$bookings = $vendor->bookings()
    ->ongoing()
    ->whereDate('end_date', '<=', now()->addDays(7))
    ->with(['customer', 'hoarding', 'timelineEvents'])
    ->latest('start_date')
    ->get();
```

### Example 2: Get Completed Bookings Without POD
```php
$bookings = $vendor->bookings()
    ->completed()
    ->whereDoesntHave('bookingProofs')
    ->with(['customer', 'hoarding'])
    ->latest('end_date')
    ->paginate(20);
```

### Example 3: Calculate Revenue from Completed Campaigns
```php
$totalRevenue = $vendor->bookings()
    ->completed()
    ->sum('total_amount');

$monthlyRevenue = $vendor->bookings()
    ->completed()
    ->whereMonth('end_date', now()->month)
    ->whereYear('end_date', now()->year)
    ->sum('total_amount');
```

### Example 4: Get New Bookings with Filters
```php
$request = request();

$query = $vendor->bookings()
    ->new()
    ->with(['customer', 'hoarding', 'quotation']);

// Apply search
if ($request->filled('search')) {
    $query->where(function($q) use ($request) {
        $q->where('id', 'like', '%' . $request->search . '%')
          ->orWhereHas('customer', function($q) use ($request) {
              $q->where('name', 'like', '%' . $request->search . '%');
          });
    });
}

// Apply amount filter
if ($request->filled('amount_min')) {
    $query->where('total_amount', '>=', $request->amount_min);
}

$bookings = $query->latest()->paginate(20);
```

## Integration with Timeline System (PROMPT 47)

The booking management integrates seamlessly with the enhanced timeline system:

```php
// Get ongoing booking with timeline
$booking = Booking::with('timelineEvents')->find($id);

// Check current stage
$currentStage = $booking->timelineEvents()
    ->where('status', 'in_progress')
    ->orWhere(function($q) {
        $q->where('status', 'pending')
          ->whereDate('scheduled_date', '<=', now());
    })
    ->orderBy('scheduled_date')
    ->first();

// Notify vendor of pending tasks
if ($currentStage && in_array($currentStage->event_type, ['designing', 'graphics', 'printing', 'mounting'])) {
    // Send notification to vendor
}
```

## Best Practices

### 1. Regular Monitoring
- Check "New Bookings" daily for incoming business
- Monitor "Ongoing Bookings" for campaigns needing attention
- Review "Completed Bookings" for missing POD submissions

### 2. Proactive Management
- Set up alerts for bookings ending soon
- Track production stages through timeline
- Ensure POD submission before campaign end

### 3. Performance Tracking
- Analyze completed bookings for revenue trends
- Review cancellation reasons to improve service
- Monitor campaign success rates

### 4. Customer Communication
- Update customers on ongoing campaign progress
- Submit POD promptly for completed campaigns
- Provide refund details for cancelled bookings

## Technical Notes

### Performance Optimization
- Eager loading relationships: customer, hoarding, quotation, timelineEvents
- Indexed columns: vendor_id, status, start_date, end_date
- Pagination: 20 records per page (configurable)
- Query caching for statistics

### Security
- Vendor can only access their own bookings
- Middleware: `auth`, `role:vendor`
- Authorization checks in controller

### Error Handling
- Graceful 404 for non-existent bookings
- Validation for status updates
- Transaction handling for critical operations

## Testing Checklist

- [ ] New bookings page shows correct bookings
- [ ] Ongoing bookings filters work (progress filter)
- [ ] Completed bookings POD filter works
- [ ] Cancelled bookings displays correctly
- [ ] Search filters work across all pages
- [ ] Statistics are accurate
- [ ] Pagination works
- [ ] Sorting works (date, amount, customer)
- [ ] Booking detail page shows all information
- [ ] Timeline integration works
- [ ] Vendor can only see their bookings
- [ ] Performance is acceptable with large datasets

## Future Enhancements

- [ ] Export bookings to Excel/CSV
- [ ] Bulk actions (confirm multiple, update status)
- [ ] Calendar view for ongoing bookings
- [ ] Revenue analytics dashboard
- [ ] Automated reminders for pending tasks
- [ ] WhatsApp notifications for vendors
- [ ] Mobile app integration

---
**Implementation Date**: 2025-12-10  
**Version**: 1.0 (PROMPT 48)  
**Status**: ✅ Complete  
**Breaking Changes**: None (backward compatible)
