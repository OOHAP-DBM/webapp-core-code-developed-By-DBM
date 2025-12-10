# PROMPT 49: Vendor Availability Calendar Implementation

## Overview
Implemented a comprehensive availability calendar system for hoardings using FullCalendar.js. Vendors can now visualize bookings, enquiries, and available dates for each hoarding with color-coded events and detailed statistics.

## Features Implemented

### 1. Backend API Controller
**File:** `app/Http/Controllers/Vendor/HoardingCalendarController.php` (300+ lines)

#### Endpoints:
- **GET /vendor/hoarding/{id}/calendar** - Calendar view page
- **GET /vendor/hoarding/{id}/calendar/data** - Calendar events API (JSON)
- **GET /vendor/hoarding/{id}/calendar/stats** - Statistics API (JSON)

#### Calendar Data API Response:
Returns events in FullCalendar format with three event types:

**Booked Dates (Red Spectrum):**
- Confirmed bookings: `#dc2626` (red-600)
- Payment hold: `#ea580c` (orange-600)
- Pending payment hold: `#f59e0b` (amber-500)

**Enquiry Dates (Yellow):**
- Pending enquiries: `#fbbf24` (yellow-400)
- Border: `#f59e0b` (yellow-500)

**Available Dates (Green):**
- No conflicts: `#10b981` (green-500)
- Background event display
- Calculated as continuous date ranges

#### Event Structure:
```json
{
  "id": "booking-123",
  "title": "Booked: Customer Name",
  "start": "2025-12-15",
  "end": "2025-12-31",
  "backgroundColor": "#dc2626",
  "borderColor": "#dc2626",
  "textColor": "#ffffff",
  "extendedProps": {
    "type": "booking",
    "bookingId": 123,
    "customerName": "John Doe",
    "customerPhone": "+91 9876543210",
    "amount": "₹50,000.00",
    "status": "Confirmed",
    "duration": "16 days"
  }
}
```

#### Statistics API:
- Total bookings (all-time confirmed)
- Active bookings (currently running)
- Pending enquiries
- Current month bookings
- Current month revenue
- Occupancy rate (%)

#### Occupancy Rate Calculation:
```php
occupancy_rate = (booked_days / total_days_in_period) * 100
```
- Handles overlapping date ranges
- Clips booking dates to requested period
- Accurate day-by-day calculation

### 2. Frontend Calendar View
**File:** `resources/views/vendor/hoardings/calendar.blade.php` (450+ lines)

#### UI Components:

**Stats Dashboard (6 Cards):**
1. Total Bookings - Blue (#3b82f6)
2. Active Now - Green (#10b981)
3. Enquiries - Yellow (#f59e0b)
4. This Month - Indigo (#6366f1)
5. Revenue - Green (#059669)
6. Occupancy % - Blue (#3b82f6)

**Legend Bar:**
- Visual guide with colored boxes
- Clear labels for all event types
- Horizontal layout for easy scanning

**FullCalendar Integration:**
- Month, Week, and List views
- Today button for quick navigation
- Auto-refresh on month change
- Responsive and mobile-friendly

**Event Details Modal:**
Shows comprehensive information:
- Booking: ID, customer, phone, dates, duration, amount, status
- Enquiry: ID, customer, phone, dates, message, status
- Quick action: "View Details" button links to detail page

#### JavaScript Features:
```javascript
// Auto-load events from API
events: function(info, successCallback, failureCallback) {
    fetch(`/vendor/hoarding/${hoardingId}/calendar/data?start=${info.startStr}&end=${info.endStr}`)
}

// Event click handler
eventClick: function(info) {
    showEventDetails(info.event);
}

// Tooltips on hover
eventDidMount: function(info) {
    info.el.title = 'Booking: Customer Name';
}
```

### 3. Navigation Integration

**Hoarding Index Page:**
Added "Calendar" button to each hoarding card:
```html
<a href="{{ route('vendor.hoarding.calendar', $hoarding->id) }}" 
   class="btn btn-sm btn-outline-success">
    <i class="bi bi-calendar3"></i> Calendar
</a>
```

**Hoarding Edit Page:**
Added header navigation with two buttons:
1. "View Calendar" - Green button with calendar icon
2. "Back to List" - Secondary button

### 4. Model Enhancement
**File:** `app/Models/Hoarding.php`

Added relationships:
```php
public function bookings()
{
    return $this->hasMany(Booking::class);
}

public function enquiries()
{
    return $this->hasMany(Enquiry::class);
}
```

### 5. Routes Configuration
**File:** `routes/web.php`

Added to vendor group:
```php
// Hoarding Availability Calendar (PROMPT 49)
Route::get('/hoarding/{id}/calendar', [HoardingCalendarController::class, 'show'])
    ->name('hoarding.calendar');
Route::get('/hoarding/{id}/calendar/data', [HoardingCalendarController::class, 'getCalendarData'])
    ->name('hoarding.calendar.data');
Route::get('/hoarding/{id}/calendar/stats', [HoardingCalendarController::class, 'getStats'])
    ->name('hoarding.calendar.stats');
```

## Technical Implementation

### Date Range Overlap Detection
Handles complex scenarios:
1. Booking/enquiry starts within range
2. Booking/enquiry ends within range
3. Booking/enquiry spans entire range

```php
where(function ($query) use ($start, $end) {
    $query->whereBetween('start_date', [$start, $end])
        ->orWhereBetween('end_date', [$start, $end])
        ->orWhere(function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $start)
              ->where('end_date', '>=', $end);
        });
})
```

### Available Dates Algorithm
1. Collect all occupied dates from bookings and enquiries
2. Mark each occupied day in hash map
3. Scan date range day-by-day
4. Group continuous available dates
5. Create background events for available ranges

### FullCalendar Configuration
- **Version:** 6.1.10 (latest stable)
- **CDN:** jsdelivr.net
- **Views:** dayGridMonth, timeGridWeek, listMonth
- **Height:** Auto-responsive
- **Event display:** Smart truncation with tooltips

## Security Features

1. **Vendor Ownership Verification:**
```php
$hoarding = Hoarding::where('vendor_id', Auth::id())
    ->findOrFail($id);
```

2. **Auth Middleware:**
All routes protected by `auth` and `role:vendor` middleware

3. **Input Validation:**
Date parameters validated and sanitized

## Performance Optimizations

1. **Eager Loading:**
```php
->with(['customer'])
```

2. **Indexed Queries:**
Using foreign keys and date indexes

3. **Efficient Date Calculations:**
Hash map for O(1) occupied date lookups

4. **AJAX Loading:**
Calendar events loaded asynchronously

5. **Stats Caching Ready:**
Stats endpoint can be easily cached with Redis

## User Experience Enhancements

1. **Color Psychology:**
   - Red: Urgent/confirmed bookings
   - Yellow: Pending/awaiting action
   - Green: Available/positive

2. **Progressive Disclosure:**
   - Summary in calendar view
   - Details on click
   - Full details via link

3. **Visual Hierarchy:**
   - Stats at top for quick scanning
   - Legend for context
   - Calendar as main focus

4. **Responsive Design:**
   - Mobile-friendly calendar
   - Touch-optimized event clicks
   - Adaptive button layouts

## Browser Compatibility
- Chrome 90+ ✓
- Firefox 88+ ✓
- Safari 14+ ✓
- Edge 90+ ✓
- Mobile browsers ✓

## Future Enhancement Opportunities

1. **Drag & Drop:**
   - Admin can adjust booking dates
   - Visual conflict detection

2. **Multi-Hoarding View:**
   - Compare availability across hoardings
   - Side-by-side calendars

3. **Export Features:**
   - PDF calendar reports
   - CSV availability data
   - iCal integration

4. **Booking Creation:**
   - Click available dates to create booking
   - Quick booking form modal

5. **Notifications:**
   - Alert for new enquiries
   - Reminder for ending bookings

6. **Filters:**
   - Show only bookings/enquiries
   - Filter by customer
   - Amount range filter

## Files Created/Modified

### Created:
1. `app/Http/Controllers/Vendor/HoardingCalendarController.php` (300 lines)
2. `resources/views/vendor/hoardings/calendar.blade.php` (450 lines)
3. `docs/PROMPT_49_CALENDAR_IMPLEMENTATION.md` (this file)

### Modified:
1. `routes/web.php` - Added 3 calendar routes
2. `app/Models/Hoarding.php` - Added bookings() and enquiries() relationships
3. `resources/views/vendor/hoardings/index.blade.php` - Added calendar button
4. `resources/views/vendor/hoardings/edit.blade.php` - Added calendar navigation

## Testing Checklist

- [x] Calendar loads for vendor's hoarding
- [x] Non-vendor cannot access other vendor's calendars
- [x] Bookings display in red
- [x] Enquiries display in yellow
- [x] Available dates display in green
- [x] Event click shows modal with details
- [x] Stats load correctly
- [x] Month navigation works
- [x] Week view works
- [x] List view works
- [x] Responsive on mobile
- [x] Calendar refresh button works
- [x] Navigation links work from hoarding pages

## API Documentation

### GET /vendor/hoarding/{id}/calendar/data
**Parameters:**
- `start` (string, required): Start date in Y-m-d format
- `end` (string, required): End date in Y-m-d format

**Response:** Array of FullCalendar event objects

### GET /vendor/hoarding/{id}/calendar/stats
**Response:**
```json
{
  "total_bookings": 45,
  "active_bookings": 3,
  "pending_enquiries": 8,
  "current_month_bookings": 12,
  "current_month_revenue": 450000.00,
  "occupancy_rate": 67.50
}
```

## Dependencies

**External:**
- FullCalendar v6.1.10 (MIT License)
- Bootstrap 5 (for modal and grid)
- Bootstrap Icons (for UI icons)

**Internal:**
- Hoarding model
- Booking model
- Enquiry model
- Laravel 10.x framework

## Conclusion

PROMPT 49 successfully implemented a professional-grade availability calendar system with:
- Color-coded visual representation
- Real-time statistics
- Detailed event information
- Responsive design
- Secure vendor-only access
- Performance-optimized queries
- Extensible architecture

The calendar provides vendors with a powerful tool to manage hoarding availability, track bookings, monitor enquiries, and make data-driven decisions about their inventory.
