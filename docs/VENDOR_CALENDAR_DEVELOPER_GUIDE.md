# Vendor Availability Calendar - Developer Guide

## Architecture Overview

The Vendor Availability Calendar is a full-stack feature built with Laravel backend and FullCalendar.js frontend, providing real-time visualization of hoarding availability.

### Technology Stack
- **Backend:** Laravel 10.x, PHP 8.1+
- **Frontend:** FullCalendar.js v6.1.10, Vanilla JavaScript
- **UI Framework:** Bootstrap 5, Bootstrap Icons
- **Database:** MySQL 8.0+
- **API Format:** JSON (FullCalendar compatible)

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend Layer                        │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ calendar.blade.php (View)                              │ │
│  │  - FullCalendar.js initialization                      │ │
│  │  - Event rendering & interaction                       │ │
│  │  - Stats dashboard                                     │ │
│  │  - Modal management                                    │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              ↕ AJAX/JSON
┌─────────────────────────────────────────────────────────────┐
│                      Controller Layer                        │
│  ┌────────────────────────────────────────────────────────┐ │
│  │ HoardingCalendarController                             │ │
│  │  - show() → Blade view                                 │ │
│  │  - getCalendarData() → Events JSON                     │ │
│  │  - getStats() → Statistics JSON                        │ │
│  │  - calculateOccupancyRate() → Helper                   │ │
│  └────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              ↕ Eloquent ORM
┌─────────────────────────────────────────────────────────────┐
│                         Model Layer                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Hoarding   │  │   Booking    │  │   Enquiry    │     │
│  │              │  │              │  │              │     │
│  │  - vendor_id │  │  - hoarding  │  │  - hoarding  │     │
│  │  - bookings()│  │  - customer  │  │  - customer  │     │
│  │  - enquiries│  │  - dates     │  │  - dates     │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                              ↕ SQL
┌─────────────────────────────────────────────────────────────┐
│                       Database Layer                         │
│  - hoardings table                                           │
│  - bookings table (start_date, end_date, status)            │
│  - enquiries table (preferred_start_date, preferred_end_date)│
└─────────────────────────────────────────────────────────────┘
```

---

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── Vendor/
│           └── HoardingCalendarController.php    # Main controller (300 lines)
└── Models/
    ├── Hoarding.php                               # Added relationships
    ├── Booking.php                                # Existing model
    └── Enquiry.php                                # Existing model

resources/
└── views/
    └── vendor/
        └── hoardings/
            ├── calendar.blade.php                 # Calendar view (450 lines)
            ├── index.blade.php                    # Modified: added calendar button
            └── edit.blade.php                     # Modified: added calendar link

routes/
└── web.php                                        # Added 3 routes

docs/
├── PROMPT_49_CALENDAR_IMPLEMENTATION.md          # Technical docs
└── VENDOR_CALENDAR_DEVELOPER_GUIDE.md            # This file
```

---

## Backend Implementation

### Controller: HoardingCalendarController.php

#### Location
```
app/Http/Controllers/Vendor/HoardingCalendarController.php
```

#### Class Structure

```php
namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use App\Models\Booking;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HoardingCalendarController extends Controller
{
    // View methods
    public function show(Request $request, $id)
    
    // API methods
    public function getCalendarData(Request $request, $id): JsonResponse
    public function getStats(Request $request, $id): JsonResponse
    
    // Helper methods
    private function calculateOccupancyRate($hoardingId, $startDate, $endDate): float
}
```

#### Method: show()

**Purpose:** Display calendar view page

**Parameters:**
- `$request` - HTTP request object
- `$id` - Hoarding ID

**Returns:** Blade view

**Security:**
```php
$hoarding = Hoarding::where('vendor_id', Auth::id())
    ->findOrFail($id);
```

**Implementation:**
```php
public function show(Request $request, $id)
{
    // Verify vendor ownership
    $hoarding = Hoarding::where('vendor_id', Auth::id())
        ->findOrFail($id);

    // Return view with hoarding data
    return view('vendor.hoardings.calendar', compact('hoarding'));
}
```

#### Method: getCalendarData()

**Purpose:** Return FullCalendar-compatible events JSON

**Parameters:**
- `$request->input('start')` - Start date (Y-m-d)
- `$request->input('end')` - End date (Y-m-d)
- `$id` - Hoarding ID

**Returns:** `JsonResponse` with events array

**Event Types Generated:**
1. **Booking Events** (Red spectrum)
2. **Enquiry Events** (Yellow)
3. **Available Events** (Green background)

**Algorithm:**

```php
// Step 1: Verify ownership
$hoarding = Hoarding::where('vendor_id', Auth::id())->findOrFail($id);

// Step 2: Query bookings with date overlap
$bookings = Booking::where('hoarding_id', $id)
    ->where(function ($query) use ($start, $end) {
        $query->whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->orWhere(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $start)
                  ->where('end_date', '>=', $end);
            });
    })
    ->whereIn('status', [
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_PAYMENT_HOLD,
        Booking::STATUS_PENDING_PAYMENT_HOLD
    ])
    ->with(['customer'])
    ->get();

// Step 3: Transform bookings to events
foreach ($bookings as $booking) {
    $events[] = [
        'id' => 'booking-' . $booking->id,
        'title' => 'Booked: ' . $booking->customer->name,
        'start' => $booking->start_date,
        'end' => Carbon::parse($booking->end_date)->addDay()->format('Y-m-d'),
        'backgroundColor' => '#dc2626',
        'borderColor' => '#dc2626',
        'textColor' => '#ffffff',
        'extendedProps' => [
            'type' => 'booking',
            'bookingId' => $booking->id,
            // ... more properties
        ],
    ];
}

// Step 4: Query enquiries
// Step 5: Calculate available dates
// Step 6: Return JSON
```

**Date Overlap Query Explained:**

```php
// Scenario 1: Booking starts within range
whereBetween('start_date', [$start, $end])

// Scenario 2: Booking ends within range
orWhereBetween('end_date', [$start, $end])

// Scenario 3: Booking spans entire range
orWhere(function ($q) use ($start, $end) {
    $q->where('start_date', '<=', $start)
      ->where('end_date', '>=', $end);
})
```

**Color Status Mapping:**

```php
$statusColors = [
    Booking::STATUS_CONFIRMED => '#dc2626',              // red-600
    Booking::STATUS_PAYMENT_HOLD => '#ea580c',           // orange-600
    Booking::STATUS_PENDING_PAYMENT_HOLD => '#f59e0b',  // amber-500
];
```

**Available Dates Algorithm:**

```php
// Step 1: Collect all occupied dates
$occupiedDates = [];

foreach ($bookings as $booking) {
    $bookingStart = Carbon::parse($booking->start_date);
    $bookingEnd = Carbon::parse($booking->end_date);
    
    while ($bookingStart->lte($bookingEnd)) {
        $occupiedDates[$bookingStart->format('Y-m-d')] = true;
        $bookingStart->addDay();
    }
}

// Step 2: Find continuous available ranges
$currentDate = $startDate->copy();
$availableStart = null;

while ($currentDate->lte($endDate)) {
    $dateStr = $currentDate->format('Y-m-d');
    
    if (!isset($occupiedDates[$dateStr])) {
        // Available date
        if ($availableStart === null) {
            $availableStart = $currentDate->copy();
        }
    } else {
        // Occupied - close previous range
        if ($availableStart !== null) {
            $events[] = [
                'id' => 'available-' . $availableStart->format('Ymd') . '-' . $currentDate->copy()->subDay()->format('Ymd'),
                'title' => 'Available',
                'start' => $availableStart->format('Y-m-d'),
                'end' => $currentDate->format('Y-m-d'),
                'backgroundColor' => '#10b981',
                'borderColor' => '#059669',
                'textColor' => '#ffffff',
                'display' => 'background',
                'extendedProps' => [
                    'type' => 'available',
                ],
            ];
            $availableStart = null;
        }
    }
    
    $currentDate->addDay();
}
```

**Time Complexity:**
- Date overlap query: O(n) where n = bookings in range
- Occupied dates collection: O(b × d) where b = bookings, d = avg duration
- Available dates scan: O(r) where r = requested date range
- Overall: O(n + b×d + r)

**Space Complexity:**
- O(b×d) for occupied dates hash map

#### Method: getStats()

**Purpose:** Return hoarding statistics

**Returns:** `JsonResponse`

**Response Structure:**
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

**Implementation:**

```php
public function getStats(Request $request, $id): JsonResponse
{
    // Verify ownership
    $hoarding = Hoarding::where('vendor_id', Auth::id())->findOrFail($id);

    $now = Carbon::now();
    $currentMonthStart = $now->copy()->startOfMonth();
    $currentMonthEnd = $now->copy()->endOfMonth();

    $stats = [
        'total_bookings' => Booking::where('hoarding_id', $id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED])
            ->count(),
        
        'active_bookings' => Booking::where('hoarding_id', $id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->count(),
        
        'pending_enquiries' => Enquiry::where('hoarding_id', $id)
            ->where('status', Enquiry::STATUS_PENDING)
            ->count(),
        
        'current_month_bookings' => Booking::where('hoarding_id', $id)
            ->whereIn('status', [Booking::STATUS_CONFIRMED])
            ->whereBetween('start_date', [$currentMonthStart, $currentMonthEnd])
            ->count(),
        
        'current_month_revenue' => Booking::where('hoarding_id', $id)
            ->where('status', Booking::STATUS_CONFIRMED)
            ->whereBetween('start_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('total_amount'),
        
        'occupancy_rate' => $this->calculateOccupancyRate($id, $currentMonthStart, $currentMonthEnd),
    ];

    return response()->json($stats);
}
```

#### Method: calculateOccupancyRate()

**Purpose:** Calculate percentage of days booked in a period

**Formula:**
```
Occupancy Rate = (Booked Days / Total Days) × 100
```

**Algorithm:**

```php
private function calculateOccupancyRate($hoardingId, $startDate, $endDate): float
{
    // Total days in period
    $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    
    // Get confirmed bookings
    $bookedDays = Booking::where('hoarding_id', $hoardingId)
        ->where('status', Booking::STATUS_CONFIRMED)
        ->where(function ($query) use ($startDate, $endDate) {
            // Date overlap query
        })
        ->get()
        ->sum(function ($booking) use ($startDate, $endDate) {
            // Clip booking dates to requested period
            $bookingStart = Carbon::parse($booking->start_date)->max(Carbon::parse($startDate));
            $bookingEnd = Carbon::parse($booking->end_date)->min(Carbon::parse($endDate));
            return $bookingStart->diffInDays($bookingEnd) + 1;
        });

    return $totalDays > 0 ? round(($bookedDays / $totalDays) * 100, 2) : 0;
}
```

**Handles Edge Cases:**
1. Booking starts before period
2. Booking ends after period
3. Overlapping bookings (counted once)
4. Partial month calculations

---

## Frontend Implementation

### View: calendar.blade.php

#### Location
```
resources/views/vendor/hoardings/calendar.blade.php
```

#### Structure

```blade
@extends('layouts.vendor')

@section('page-title', 'Hoarding Availability Calendar')

@section('content')
    <!-- Header -->
    <!-- Stats Cards (6) -->
    <!-- Legend Bar -->
    <!-- Calendar Container -->
    <!-- Event Details Modal -->
@endsection

@push('styles')
    <!-- FullCalendar CSS -->
    <!-- Custom Styles -->
@endpush

@push('scripts')
    <!-- FullCalendar JS -->
    <!-- Custom JavaScript -->
@endpush
```

#### JavaScript Implementation

**Calendar Initialization:**

```javascript
let calendar;
const hoardingId = {{ $hoarding->id }};
const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));

document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    loadStats();
});

function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Today',
            month: 'Month',
            week: 'Week',
            list: 'List'
        },
        height: 'auto',
        events: fetchEvents,
        eventClick: handleEventClick,
        eventDidMount: addTooltips,
        datesSet: loadStats
    });
    
    calendar.render();
}
```

**Events Loading Function:**

```javascript
function fetchEvents(info, successCallback, failureCallback) {
    const url = `/vendor/hoarding/${hoardingId}/calendar/data`;
    const params = new URLSearchParams({
        start: info.startStr,
        end: info.endStr
    });
    
    fetch(`${url}?${params}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            successCallback(data);
        })
        .catch(error => {
            console.error('Error loading calendar data:', error);
            failureCallback(error);
        });
}
```

**Event Click Handler:**

```javascript
function handleEventClick(info) {
    const event = info.event;
    const props = event.extendedProps;
    
    // Don't show modal for available dates
    if (props.type === 'available') {
        return;
    }
    
    let title = '';
    let content = '';
    let detailsLink = '';
    
    if (props.type === 'booking') {
        title = '<i class="bi bi-calendar-check text-danger me-2"></i>Booking Details';
        content = generateBookingContent(event, props);
        detailsLink = `/vendor/bookings/${props.bookingId}`;
    } else if (props.type === 'enquiry') {
        title = '<i class="bi bi-inbox text-warning me-2"></i>Enquiry Details';
        content = generateEnquiryContent(event, props);
        detailsLink = `/vendor/enquiries/${props.enquiryId}`;
    }
    
    // Update modal
    document.getElementById('eventModalTitle').innerHTML = title;
    document.getElementById('eventModalBody').innerHTML = content;
    
    const detailsBtn = document.getElementById('eventViewDetailsBtn');
    if (detailsLink) {
        detailsBtn.href = detailsLink;
        detailsBtn.style.display = 'inline-block';
    } else {
        detailsBtn.style.display = 'none';
    }
    
    eventModal.show();
}
```

**Stats Loading Function:**

```javascript
function loadStats() {
    fetch(`/vendor/hoarding/${hoardingId}/calendar/stats`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-total').textContent = data.total_bookings;
            document.getElementById('stat-active').textContent = data.active_bookings;
            document.getElementById('stat-enquiries').textContent = data.pending_enquiries;
            document.getElementById('stat-month').textContent = data.current_month_bookings;
            document.getElementById('stat-revenue').textContent = 
                '₹' + (data.current_month_revenue / 100000).toFixed(2) + 'L';
            document.getElementById('stat-occupancy').textContent = data.occupancy_rate + '%';
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}
```

**Refresh Function:**

```javascript
function refreshCalendar() {
    if (calendar) {
        calendar.refetchEvents();
        loadStats();
    }
}
```

#### CSS Customization

```css
/* Stats Icons */
.stat-icon-sm {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

/* Legend */
.legend-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.1);
}

/* Calendar Container */
#calendar {
    min-height: 600px;
}

/* FullCalendar Customization */
.fc-event {
    cursor: pointer;
    border: none;
}

.fc-event:hover {
    opacity: 0.85;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600;
}

.fc-button-primary {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
}

.fc-day-today {
    background-color: #eff6ff !important;
}
```

---

## Model Relationships

### Hoarding Model

**Added Relationships:**

```php
// app/Models/Hoarding.php

/**
 * Get all bookings for this hoarding.
 */
public function bookings()
{
    return $this->hasMany(Booking::class);
}

/**
 * Get all enquiries for this hoarding.
 */
public function enquiries()
{
    return $this->hasMany(Enquiry::class);
}
```

**Usage Example:**

```php
// Eager load relationships
$hoarding = Hoarding::with(['bookings', 'enquiries'])->find($id);

// Get bookings count
$bookingCount = $hoarding->bookings()->count();

// Get pending enquiries
$pendingEnquiries = $hoarding->enquiries()
    ->where('status', Enquiry::STATUS_PENDING)
    ->get();
```

---

## Routing Configuration

### Routes Added

```php
// routes/web.php

Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    // ... existing routes
    
    // Hoarding Availability Calendar (PROMPT 49)
    Route::get('/hoarding/{id}/calendar', 
        [\App\Http\Controllers\Vendor\HoardingCalendarController::class, 'show'])
        ->name('hoarding.calendar');
        
    Route::get('/hoarding/{id}/calendar/data', 
        [\App\Http\Controllers\Vendor\HoardingCalendarController::class, 'getCalendarData'])
        ->name('hoarding.calendar.data');
        
    Route::get('/hoarding/{id}/calendar/stats', 
        [\App\Http\Controllers\Vendor\HoardingCalendarController::class, 'getStats'])
        ->name('hoarding.calendar.stats');
});
```

**Route Names:**
- `vendor.hoarding.calendar` - View page
- `vendor.hoarding.calendar.data` - Events API
- `vendor.hoarding.calendar.stats` - Statistics API

**Middleware:**
- `auth` - User must be authenticated
- `role:vendor` - User must have vendor role

---

## Database Queries

### Optimized Queries

**Booking Query with Eager Loading:**

```php
Booking::where('hoarding_id', $id)
    ->where(function ($query) use ($start, $end) {
        $query->whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->orWhere(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $start)
                  ->where('end_date', '>=', $end);
            });
    })
    ->whereIn('status', [
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_PAYMENT_HOLD,
        Booking::STATUS_PENDING_PAYMENT_HOLD
    ])
    ->with(['customer'])  // Eager load relationship
    ->get();
```

**Index Usage:**

Ensure these indexes exist:
```sql
-- bookings table
INDEX idx_hoarding_dates (hoarding_id, start_date, end_date)
INDEX idx_status (status)

-- enquiries table
INDEX idx_hoarding_dates (hoarding_id, preferred_start_date, preferred_end_date)
INDEX idx_status (status)
```

**Query Execution Plan:**

```sql
EXPLAIN SELECT * FROM bookings 
WHERE hoarding_id = 1 
AND status IN ('confirmed', 'payment_hold', 'pending_payment_hold')
AND (
    (start_date BETWEEN '2025-12-01' AND '2025-12-31')
    OR (end_date BETWEEN '2025-12-01' AND '2025-12-31')
    OR (start_date <= '2025-12-01' AND end_date >= '2025-12-31')
);
```

---

## API Documentation

### Endpoint: GET /vendor/hoarding/{id}/calendar/data

**Request:**
```http
GET /vendor/hoarding/123/calendar/data?start=2025-12-01&end=2025-12-31 HTTP/1.1
Authorization: Bearer {token}
Cookie: laravel_session={session_id}
```

**Response:**
```json
[
  {
    "id": "booking-456",
    "title": "Booked: John Doe",
    "start": "2025-12-15",
    "end": "2025-12-31",
    "backgroundColor": "#dc2626",
    "borderColor": "#dc2626",
    "textColor": "#ffffff",
    "extendedProps": {
      "type": "booking",
      "bookingId": 456,
      "customerName": "John Doe",
      "customerPhone": "+91 9876543210",
      "amount": "₹50,000.00",
      "status": "Confirmed",
      "duration": "16 days"
    }
  },
  {
    "id": "enquiry-789",
    "title": "Enquiry: Jane Smith",
    "start": "2025-12-10",
    "end": "2025-12-15",
    "backgroundColor": "#fbbf24",
    "borderColor": "#f59e0b",
    "textColor": "#1f2937",
    "extendedProps": {
      "type": "enquiry",
      "enquiryId": 789,
      "customerName": "Jane Smith",
      "customerPhone": "+91 9876543211",
      "message": "Interested in this location",
      "status": "Pending",
      "duration": "5 days"
    }
  },
  {
    "id": "available-20251201-20251209",
    "title": "Available",
    "start": "2025-12-01",
    "end": "2025-12-10",
    "backgroundColor": "#10b981",
    "borderColor": "#059669",
    "textColor": "#ffffff",
    "display": "background",
    "extendedProps": {
      "type": "available"
    }
  }
]
```

### Endpoint: GET /vendor/hoarding/{id}/calendar/stats

**Request:**
```http
GET /vendor/hoarding/123/calendar/stats HTTP/1.1
Authorization: Bearer {token}
Cookie: laravel_session={session_id}
```

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

---

## Testing

### Unit Tests

**Test Controller Methods:**

```php
// tests/Unit/HoardingCalendarControllerTest.php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Hoarding;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HoardingCalendarControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_view_own_hoarding_calendar()
    {
        $vendor = User::factory()->vendor()->create();
        $hoarding = Hoarding::factory()->create(['vendor_id' => $vendor->id]);

        $response = $this->actingAs($vendor)
            ->get(route('vendor.hoarding.calendar', $hoarding->id));

        $response->assertStatus(200);
        $response->assertViewIs('vendor.hoardings.calendar');
        $response->assertViewHas('hoarding');
    }

    public function test_vendor_cannot_view_other_vendor_hoarding_calendar()
    {
        $vendor1 = User::factory()->vendor()->create();
        $vendor2 = User::factory()->vendor()->create();
        $hoarding = Hoarding::factory()->create(['vendor_id' => $vendor1->id]);

        $response = $this->actingAs($vendor2)
            ->get(route('vendor.hoarding.calendar', $hoarding->id));

        $response->assertStatus(404);
    }

    public function test_calendar_data_returns_bookings()
    {
        $vendor = User::factory()->vendor()->create();
        $hoarding = Hoarding::factory()->create(['vendor_id' => $vendor->id]);
        $customer = User::factory()->customer()->create();
        
        Booking::factory()->create([
            'hoarding_id' => $hoarding->id,
            'customer_id' => $customer->id,
            'start_date' => '2025-12-15',
            'end_date' => '2025-12-31',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($vendor)
            ->get(route('vendor.hoarding.calendar.data', [
                'id' => $hoarding->id,
                'start' => '2025-12-01',
                'end' => '2025-12-31'
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'start',
                'end',
                'backgroundColor',
                'extendedProps'
            ]
        ]);
    }

    public function test_stats_calculation()
    {
        $vendor = User::factory()->vendor()->create();
        $hoarding = Hoarding::factory()->create(['vendor_id' => $vendor->id]);

        $response = $this->actingAs($vendor)
            ->get(route('vendor.hoarding.calendar.stats', $hoarding->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_bookings',
            'active_bookings',
            'pending_enquiries',
            'current_month_bookings',
            'current_month_revenue',
            'occupancy_rate'
        ]);
    }

    public function test_occupancy_rate_calculation()
    {
        $vendor = User::factory()->vendor()->create();
        $hoarding = Hoarding::factory()->create(['vendor_id' => $vendor->id]);
        $customer = User::factory()->customer()->create();
        
        // Book 15 days out of 30 in December
        Booking::factory()->create([
            'hoarding_id' => $hoarding->id,
            'customer_id' => $customer->id,
            'start_date' => '2025-12-01',
            'end_date' => '2025-12-15',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($vendor)
            ->getJson(route('vendor.hoarding.calendar.stats', $hoarding->id));

        // Expected: 15/31 * 100 = 48.39%
        $this->assertEqualsWithDelta(48.39, $response['occupancy_rate'], 0.01);
    }
}
```

### Feature Tests

```php
// tests/Feature/CalendarIntegrationTest.php

public function test_complete_calendar_workflow()
{
    // 1. Create vendor and hoarding
    // 2. Create bookings and enquiries
    // 3. Load calendar view
    // 4. Fetch calendar data
    // 5. Verify all events present
    // 6. Check stats accuracy
}
```

---

## Performance Optimization

### Caching Strategy

**Cache Statistics:**

```php
use Illuminate\Support\Facades\Cache;

public function getStats(Request $request, $id): JsonResponse
{
    $hoarding = Hoarding::where('vendor_id', Auth::id())->findOrFail($id);
    
    $cacheKey = "hoarding.{$id}.stats." . now()->format('Y-m-d');
    
    $stats = Cache::remember($cacheKey, 3600, function () use ($id) {
        // Calculate stats...
        return $stats;
    });
    
    return response()->json($stats);
}
```

**Clear Cache on Booking Changes:**

```php
// app/Observers/BookingObserver.php

public function created(Booking $booking)
{
    $this->clearCalendarCache($booking->hoarding_id);
}

public function updated(Booking $booking)
{
    $this->clearCalendarCache($booking->hoarding_id);
}

private function clearCalendarCache($hoardingId)
{
    Cache::forget("hoarding.{$hoardingId}.stats." . now()->format('Y-m-d'));
}
```

### Database Optimization

**Add Indexes:**

```php
// database/migrations/xxxx_add_calendar_indexes.php

public function up()
{
    Schema::table('bookings', function (Blueprint $table) {
        $table->index(['hoarding_id', 'start_date', 'end_date'], 'idx_hoarding_dates');
        $table->index('status');
    });
    
    Schema::table('enquiries', function (Blueprint $table) {
        $table->index(['hoarding_id', 'preferred_start_date', 'preferred_end_date'], 'idx_enquiry_dates');
        $table->index('status');
    });
}
```

### Frontend Optimization

**Lazy Loading:**

```javascript
// Load FullCalendar only when needed
if (document.getElementById('calendar')) {
    // Initialize calendar
}
```

**Debounced Stats Refresh:**

```javascript
let statsTimeout;

function loadStats() {
    clearTimeout(statsTimeout);
    statsTimeout = setTimeout(() => {
        fetch(/* ... */)
    }, 300);
}
```

---

## Error Handling

### Backend Error Handling

```php
public function getCalendarData(Request $request, $id): JsonResponse
{
    try {
        $hoarding = Hoarding::where('vendor_id', Auth::id())
            ->findOrFail($id);
        
        // Process data...
        
        return response()->json($events);
        
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Hoarding not found'], 404);
    } catch (\Exception $e) {
        Log::error('Calendar data error', [
            'hoarding_id' => $id,
            'error' => $e->getMessage()
        ]);
        return response()->json(['error' => 'Server error'], 500);
    }
}
```

### Frontend Error Handling

```javascript
function fetchEvents(info, successCallback, failureCallback) {
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            successCallback(data);
        })
        .catch(error => {
            console.error('Error loading calendar data:', error);
            
            // Show user-friendly error message
            alert('Unable to load calendar data. Please try again.');
            
            // Return empty array to prevent calendar crash
            failureCallback(error);
        });
}
```

---

## Security Considerations

### Authorization

**Verify Vendor Ownership:**

```php
// Always verify vendor owns the hoarding
$hoarding = Hoarding::where('vendor_id', Auth::id())
    ->findOrFail($id);
```

**Route Middleware:**

```php
// Ensure routes are protected
Route::middleware(['auth', 'role:vendor'])
```

### Input Validation

**Validate Date Parameters:**

```php
$validated = $request->validate([
    'start' => 'required|date',
    'end' => 'required|date|after:start',
]);
```

### SQL Injection Prevention

**Use Parameter Binding:**

```php
// Eloquent automatically uses parameter binding
Booking::where('hoarding_id', $id)->get();

// Raw queries use bindings
DB::select('SELECT * FROM bookings WHERE hoarding_id = ?', [$id]);
```

### XSS Prevention

**Escape Output:**

```blade
{{-- Blade automatically escapes --}}
{{ $hoarding->title }}

{{-- For HTML content, use purifier --}}
{!! clean($hoarding->description) !!}
```

---

## Deployment Checklist

- [ ] Run migrations (if any new indexes added)
- [ ] Clear application cache: `php artisan cache:clear`
- [ ] Clear view cache: `php artisan view:clear`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Optimize autoloader: `composer dump-autoload --optimize`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache config: `php artisan config:cache`
- [ ] Test on staging environment
- [ ] Check browser console for errors
- [ ] Verify mobile responsiveness
- [ ] Test with different vendor accounts
- [ ] Monitor error logs after deployment

---

## Troubleshooting

### Common Issues

**Issue 1: Calendar not loading**

```javascript
// Check console for errors
console.log('FullCalendar loaded:', typeof FullCalendar !== 'undefined');

// Verify CDN is accessible
fetch('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js')
    .then(() => console.log('CDN accessible'))
    .catch(() => console.error('CDN blocked'));
```

**Issue 2: Events not showing**

```php
// Debug query
$bookings = Booking::where('hoarding_id', $id)
    ->get();
    
Log::info('Bookings found', ['count' => $bookings->count()]);
```

**Issue 3: Stats showing zero**

```php
// Check date ranges
Log::info('Date range', [
    'start' => $currentMonthStart,
    'end' => $currentMonthEnd,
    'now' => $now
]);
```

---

## Extension Points

### Adding New Event Types

```php
// In getCalendarData() method

// Add custom event type
$customEvents = CustomModel::where('hoarding_id', $id)->get();

foreach ($customEvents as $custom) {
    $events[] = [
        'id' => 'custom-' . $custom->id,
        'title' => 'Custom: ' . $custom->name,
        'start' => $custom->date,
        'backgroundColor' => '#custom-color',
        'extendedProps' => [
            'type' => 'custom',
            'customId' => $custom->id,
        ],
    ];
}
```

### Adding New Statistics

```php
// In getStats() method

$stats['new_metric'] = Model::where('hoarding_id', $id)
    ->customCalculation()
    ->get();
```

### Custom Calendar Views

```javascript
// Add new view to FullCalendar
calendar = new FullCalendar.Calendar(calendarEl, {
    // ... existing config
    views: {
        customView: {
            type: 'dayGrid',
            duration: { days: 14 },
            buttonText: '2 weeks'
        }
    },
    headerToolbar: {
        right: 'dayGridMonth,timeGridWeek,customView,listMonth'
    }
});
```

---

## Maintenance

### Regular Tasks

**Monthly:**
- Review error logs for calendar-related issues
- Check API response times
- Monitor database query performance
- Review user feedback

**Quarterly:**
- Update FullCalendar.js to latest stable version
- Review and optimize database queries
- Update documentation

**Annually:**
- Full security audit
- Performance benchmarking
- User experience review

### Monitoring

**Key Metrics:**

```php
// Log API performance
Log::info('Calendar API Performance', [
    'endpoint' => 'getCalendarData',
    'hoarding_id' => $id,
    'duration_ms' => $duration,
    'event_count' => count($events)
]);
```

---

## Resources

### Documentation
- [FullCalendar v6 Docs](https://fullcalendar.io/docs/v6)
- [Laravel 10 Documentation](https://laravel.com/docs/10.x)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.0)

### Code Examples
- `docs/PROMPT_49_CALENDAR_IMPLEMENTATION.md` - Technical implementation
- `docs/VENDOR_CALENDAR_USER_GUIDE.md` - User documentation

### Support
- GitHub Issues: Project repository
- Email: dev@oohapp.com
- Slack: #oohapp-dev

---

**Last Updated:** December 10, 2025  
**Version:** 1.0  
**Author:** Development Team  
**Status:** Production Ready
