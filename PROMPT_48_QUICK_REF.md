# 🚀 PROMPT 48 Quick Reference

## Booking Categories

| Category | Route | Logic |
|----------|-------|-------|
| 🆕 **New** | `/vendor/bookings/new` | status = pending_payment OR payment_hold |
| 🔄 **Ongoing** | `/vendor/bookings/ongoing` | today BETWEEN start_date AND end_date |
| ✅ **Completed** | `/vendor/bookings/completed` | end_date < today |
| ❌ **Cancelled** | `/vendor/bookings/cancelled` | status = cancelled OR refunded |

---

## Quick Code Examples

### Get Ongoing Bookings
```php
$vendor = Auth::user();
$bookings = $vendor->bookings()->ongoing()->get();
```

### Get Completed Bookings Without POD
```php
$bookings = $vendor->bookings()
    ->completed()
    ->whereDoesntHave('bookingProofs')
    ->get();
```

### Calculate Revenue
```php
$totalRevenue = $vendor->bookings()->completed()->sum('total_amount');
```

### Filter New Bookings by Search
```php
$bookings = $vendor->bookings()
    ->new()
    ->where(function($q) use ($search) {
        $q->where('id', 'like', "%{$search}%")
          ->orWhereHas('customer', function($q) use ($search) {
              $q->where('name', 'like', "%{$search}%");
          });
    })
    ->paginate(20);
```

---

## Available Scopes

```php
// Booking model scopes
Booking::new()                    // New bookings
Booking::ongoing()                // Active campaigns
Booking::completed()              // Finished campaigns
Booking::cancelledBookings()      // Cancelled/refunded
Booking::byVendor($vendorId)      // Filter by vendor

// User relationships
$vendor->bookings()               // Vendor's bookings
$customer->customerBookings()     // Customer's bookings
$vendor->tasks()                  // Vendor's tasks
```

---

## Common Filters

```php
// Search
?search=customer_name

// Hoarding filter
?hoarding_id=123

// Date range
?date_from=2025-01-01&date_to=2025-12-31

// Amount range
?amount_min=10000&amount_max=100000

// Sorting
?sort_by=date&sort_order=desc
```

---

## Page-Specific Filters

### Ongoing Bookings
```php
?progress=just_started    // Started within 7 days
?progress=mid_campaign    // Less than 50% complete
?progress=ending_soon     // Ends within 7 days
```

### Completed Bookings
```php
?pod_status=submitted     // POD submitted
?pod_status=approved      // POD approved
?pod_status=missing       // No POD
```

### Cancelled Bookings
```php
?cancellation_type=cancelled   // Only cancelled
?cancellation_type=refunded    // Only refunded
```

---

## Controller Methods

```php
// List pages
newBookings(Request $request)        // New bookings
ongoingBookings(Request $request)    // Ongoing campaigns
completedBookings(Request $request)  // Completed campaigns
cancelledBookings(Request $request)  // Cancelled bookings

// Detail/Actions
show($id)                            // Single booking details
confirm($id)                         // Confirm booking
cancel(Request $request, $id)        // Cancel booking
updateStatus(Request $request, $id)  // Update status

// Helper
applyFilters($query, Request $request) // Apply common filters
```

---

## Statistics Structure

### New Bookings
```json
{
    "total": 45,
    "pending_payment": 20,
    "payment_hold": 25
}
```

### Ongoing Bookings
```json
{
    "total": 38,
    "just_started": 12,
    "ending_soon": 8
}
```

### Completed Bookings
```json
{
    "total": 156,
    "with_pod": 142,
    "without_pod": 14,
    "total_revenue": 7800000.00
}
```

### Cancelled Bookings
```json
{
    "total": 23,
    "cancelled": 18,
    "refunded": 5,
    "total_lost_revenue": 1150000.00
}
```

---

## API Calls

### Get New Bookings
```bash
curl -X GET /vendor/bookings/new \
  -H "Authorization: Bearer {token}" \
  -G -d "search=john" -d "amount_min=10000"
```

### Get Ongoing with Progress Filter
```bash
curl -X GET /vendor/bookings/ongoing \
  -H "Authorization: Bearer {token}" \
  -G -d "progress=ending_soon"
```

### Get Completed with POD Filter
```bash
curl -X GET /vendor/bookings/completed \
  -H "Authorization: Bearer {token}" \
  -G -d "pod_status=missing"
```

---

## Integration with Timeline (PROMPT 47)

```php
// Get booking with timeline events
$booking = Booking::with('timelineEvents')->find($id);

// Check current production stage
$currentStage = $booking->timelineEvents()
    ->where('status', 'in_progress')
    ->whereIn('event_type', ['designing', 'graphics', 'printing', 'mounting'])
    ->first();

// Get completed stages
$completedStages = $booking->timelineEvents()
    ->where('status', 'completed')
    ->count();
```

---

## Useful Queries

### Bookings Needing POD
```php
$vendor->bookings()
    ->completed()
    ->whereDoesntHave('bookingProofs')
    ->whereDate('end_date', '>=', now()->subDays(7))
    ->get();
```

### Revenue This Month
```php
$vendor->bookings()
    ->completed()
    ->whereMonth('end_date', now()->month)
    ->sum('total_amount');
```

### Campaigns Starting Tomorrow
```php
$vendor->bookings()
    ->confirmed()
    ->whereDate('start_date', now()->addDay())
    ->get();
```

### Payment Holds Expiring Soon
```php
$vendor->bookings()
    ->where('status', Booking::STATUS_PAYMENT_HOLD)
    ->where('hold_expiry_at', '<=', now()->addHours(2))
    ->get();
```

---

## Files Modified

✅ `app/Models/User.php` - Added relationships  
✅ `app/Models/Booking.php` - Added scopes  
✅ `app/Http/Controllers/Vendor/BookingController.php` - Complete rewrite  
✅ `routes/web.php` - Updated routes  

---

## Testing Commands

```bash
# Test new bookings endpoint
curl http://localhost/vendor/bookings/new

# Test ongoing with filter
curl "http://localhost/vendor/bookings/ongoing?progress=ending_soon"

# Test completed with POD filter
curl "http://localhost/vendor/bookings/completed?pod_status=missing"

# Test search
curl "http://localhost/vendor/bookings/new?search=john"
```

---

## Documentation

📖 Full docs: `docs/VENDOR_BOOKING_MANAGEMENT.md`  
📝 Summary: `PROMPT_48_SUMMARY.md`  
🔗 Related: `docs/BOOKING_TIMELINE_ENHANCED.md` (PROMPT 47)  

---

**Status**: ✅ Production Ready  
**Breaking Changes**: None  
**Backward Compatible**: Yes  
**Performance**: Optimized with eager loading
