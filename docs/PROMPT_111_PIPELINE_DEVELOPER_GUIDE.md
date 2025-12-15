# PROMPT 111: Vendor Booking Pipeline Kanban Board

## Overview

A visual Kanban-style pipeline board for vendors to manage bookings through their entire lifecycle from enquiry to completion. Provides drag-and-drop functionality to move bookings between stages, real-time statistics, and export capabilities.

**Status**: ✅ COMPLETE  
**Date**: December 15, 2025  
**Dependencies**: Laravel 12.x, Bootstrap 5, Font Awesome 6, Booking Timeline (PROMPT 38, 47)

---

## Table of Contents

1. [Features](#features)
2. [Pipeline Stages](#pipeline-stages)
3. [Architecture](#architecture)
4. [Installation](#installation)
5. [Usage Guide](#usage-guide)
6. [API Reference](#api-reference)
7. [Customization](#customization)
8. [Testing](#testing)
9. [Troubleshooting](#troubleshooting)

---

## Features

### Core Functionality

- ✅ **11-Stage Pipeline**: Complete booking lifecycle visualization
- ✅ **Drag & Drop**: Move bookings between stages with validation
- ✅ **Real-time Stats**: Dashboard metrics per stage
- ✅ **Smart Categorization**: Automatic booking placement
- ✅ **Stage Validation**: Prevents invalid transitions
- ✅ **Filters**: Search, priority, customer, hoarding filters
- ✅ **Export**: CSV and PDF reports
- ✅ **Booking Details**: Quick view modal
- ✅ **Visual Indicators**: Urgent and high-value badges
- ✅ **Responsive Design**: Horizontal scroll Kanban board
- ✅ **Bulk Operations**: Move multiple bookings at once

### Business Logic

- Automatic stage detection based on booking status and timeline
- Conversion rate tracking
- Revenue tracking per stage
- Urgency detection (campaigns starting within 7 days)
- High-value booking identification (₹100,000+)

---

## Pipeline Stages

### Stage Flow Diagram

```
New Enquiry → Offer Sent → Quotation Sent → In Payment → Booked
    ↓
Designing → Printing → Mounting → Live → Survey (Optional) → Completed
```

### Stage Definitions

| # | Stage | Key | Color | Icon | Criteria |
|---|-------|-----|-------|------|----------|
| 1 | **New Enquiry** | `new_enquiry` | Info (Blue) | fa-envelope | Has enquiry, no offer sent |
| 2 | **Offer Sent** | `offer_sent` | Primary (Blue) | fa-paper-plane | Offer sent, quotation not created |
| 3 | **Quotation Sent** | `quotation_sent` | Warning (Yellow) | fa-file-invoice | Quotation sent, payment not initiated |
| 4 | **In Payment** | `in_payment` | Purple | fa-credit-card | Status = `payment_hold` |
| 5 | **Booked** | `booked` | Success (Green) | fa-check-circle | Status = `payment_settled`, designing not started |
| 6 | **Designing** | `designing` | Teal | fa-pencil-ruler | Designing timeline event active |
| 7 | **Printing** | `printing` | Cyan | fa-print | Printing timeline event active |
| 8 | **Mounting** | `mounting` | Orange | fa-tools | Mounting timeline event active |
| 9 | **Live** | `live` | Success (Green) | fa-broadcast-tower | Campaign running (between start/end dates) |
| 10 | **Survey** | `survey` | Indigo | fa-clipboard-check | Survey timeline event active (Optional) |
| 11 | **Completed** | `completed` | Secondary (Gray) | fa-flag-checkered | Campaign ended, completion event logged |

### Stage Transition Rules

**Valid Transitions**:
- ✅ Forward progression (max 2 stages skip)
- ✅ Backward movement (any stage)
- ✅ Survey can only be entered from Mounting or Live

**Invalid Transitions**:
- ❌ Skipping more than 2 stages forward
- ❌ Moving to Survey from early stages

---

## Architecture

### File Structure

```
app/
├── Services/
│   └── BookingPipelineService.php          (550 lines - Core business logic)
├── Http/Controllers/Vendor/
│   └── BookingPipelineController.php       (430 lines - HTTP handlers)
└── Models/
    └── Booking.php                         (Extended with pipeline methods)

resources/views/vendor/pipeline/
├── index.blade.php                         (420 lines - Main Kanban board)
└── export-pdf.blade.php                    (130 lines - PDF export template)

routes/
└── web.php                                 (7 new routes added)
```

### Service Layer: BookingPipelineService

**Location**: `app/Services/BookingPipelineService.php`

**Responsibilities**:
- Stage configuration management
- Booking categorization logic
- Stage transition validation
- Statistics calculation
- Data formatting for views

**Key Methods**:

```php
// Get complete pipeline data for vendor
public function getVendorPipeline(User $vendor, array $filters = []): array

// Get bookings for specific stage
protected function getBookingsForStage(User $vendor, string $stage, array $filters = [])

// Format booking for Kanban card display
protected function formatBookingCard(Booking $booking): array

// Get pipeline summary statistics
protected function getPipelineSummary(User $vendor, array $filters = []): array

// Move booking to different stage
public function moveBooking(Booking $booking, string $fromStage, string $toStage, User $user): array

// Validate stage transition
protected function isValidTransition(string $from, string $to): bool

// Update booking status/data for new stage
protected function updateBookingForStage(Booking $booking, string $stage, User $user): void

// Get stage configuration
public static function getStageConfig(string $stage): ?array

// Get all stages
public static function getAllStages(): array
```

#### Stage Configuration

```php
const STAGES = [
    'new_enquiry' => [
        'label' => 'New Enquiry',
        'icon' => 'fa-envelope',
        'color' => 'info',
        'order' => 1,
    ],
    // ... 10 more stages
];
```

#### Booking Card Format

```php
[
    'id' => 123,
    'booking_id' => 'BK-2025-001',
    'customer_name' => 'John Doe',
    'customer_avatar' => '/storage/avatars/123.jpg',
    'hoarding_title' => 'MG Road Billboard',
    'hoarding_location' => 'MG Road, Near Metro',
    'hoarding_city' => 'Bangalore',
    'hoarding_image' => '/storage/hoardings/1.jpg',
    'total_amount' => 250000,
    'total_amount_formatted' => '₹2,50,000',
    'start_date' => 'Jan 15, 2025',
    'end_date' => 'Feb 15, 2025',
    'duration_days' => 31,
    'days_until_start' => 7,
    'days_until_end' => 38,
    'is_urgent' => true,      // Starting within 7 days
    'is_high_value' => true,  // Amount >= ₹100,000
    'status' => 'payment_settled',
    'status_label' => 'Payment Settled',
    'latest_update' => '2 hours ago',
    'latest_update_text' => 'Payment confirmed',
    'payment_status' => 'paid',
    'created_at' => 'Jan 10, 2025',
]
```

#### Pipeline Summary Format

```php
[
    'total_bookings' => 45,
    'total_value' => 12500000,
    'total_value_formatted' => '₹1,25,00,000',
    'active_bookings' => 12,
    'urgent_bookings' => 3,
    'conversion_rate' => 67.5,  // Percentage
]
```

---

### Controller Layer: BookingPipelineController

**Location**: `app/Http/Controllers/Vendor/BookingPipelineController.php`

**Responsibilities**:
- HTTP request handling
- View rendering
- AJAX endpoints
- Export generation
- Authorization checks

**Routes**:

| Method | URL | Action | Description |
|--------|-----|--------|-------------|
| GET | `/vendor/pipeline` | `index()` | Main Kanban board view |
| GET | `/vendor/pipeline/data` | `getData()` | AJAX refresh endpoint |
| POST | `/vendor/pipeline/move` | `moveBooking()` | Drag & drop handler |
| GET | `/vendor/pipeline/booking/{id}` | `getBookingDetails()` | Booking details modal |
| GET | `/vendor/pipeline/stats` | `getStats()` | Statistics endpoint |
| POST | `/vendor/pipeline/bulk-move` | `bulkMove()` | Move multiple bookings |
| GET | `/vendor/pipeline/export` | `export()` | CSV/PDF export |

#### Controller Methods

**1. index(Request $request)**

Main pipeline board view.

```php
public function index(Request $request)
{
    $filters = [
        'search' => $request->get('search'),
        'hoarding_id' => $request->get('hoarding_id'),
        'customer_id' => $request->get('customer_id'),
        'priority' => $request->get('priority'),
    ];

    $pipelineData = $this->pipelineService->getVendorPipeline(Auth::user(), $filters);

    return view('vendor.pipeline.index', [
        'stages' => $pipelineData['stages'],
        'summary' => $pipelineData['summary'],
        'filters' => $filters,
    ]);
}
```

**2. moveBooking(Request $request)**

Handle drag & drop operations.

```php
public function moveBooking(Request $request)
{
    $validated = $request->validate([
        'booking_id' => 'required|exists:bookings,id',
        'from_stage' => 'required|string',
        'to_stage' => 'required|string',
    ]);

    $booking = Booking::findOrFail($validated['booking_id']);
    
    // Authorization check
    if ($booking->vendor_id !== Auth::id()) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    $result = $this->pipelineService->moveBooking(
        $booking,
        $validated['from_stage'],
        $validated['to_stage'],
        Auth::user()
    );

    return response()->json($result);
}
```

**3. getBookingDetails($id)**

Fetch booking details for modal display.

```php
public function getBookingDetails($id)
{
    $booking = Booking::with([
        'customer',
        'hoarding',
        'quotation.offer.enquiry',
        'timeline.events' => function($q) {
            $q->latest()->limit(10);
        },
        'payments',
    ])->findOrFail($id);

    // Authorization check
    if ($booking->vendor_id !== Auth::id()) {
        return response()->json(['success' => false], 403);
    }

    return response()->json([
        'success' => true,
        'booking' => [...] // Formatted booking data
    ]);
}
```

**4. export(Request $request)**

Generate CSV or PDF export.

```php
public function export(Request $request)
{
    $format = $request->get('format', 'csv');
    $filters = $request->only(['search', 'hoarding_id', 'customer_id', 'priority']);
    
    $pipelineData = $this->pipelineService->getVendorPipeline(Auth::user(), $filters);

    if ($format === 'csv') {
        return $this->exportCSV($pipelineData);
    } else {
        return $this->exportPDF($pipelineData);
    }
}
```

---

### View Layer

#### Main Kanban Board View

**Location**: `resources/views/vendor/pipeline/index.blade.php`

**Features**:
- 4 summary cards (Total, Value, Active, Conversion)
- Filter form (search, priority)
- Horizontal scroll Kanban board
- Drag-and-drop cards
- Booking details modal
- Export dropdown

**Layout Structure**:

```html
<div class="container-fluid">
    <!-- Header with title and export buttons -->
    
    <!-- Summary Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">Total Bookings Card</div>
        <div class="col-md-3">Total Value Card</div>
        <div class="col-md-3">Active Campaigns Card</div>
        <div class="col-md-3">Conversion Rate Card</div>
    </div>
    
    <!-- Filters -->
    <div class="card">
        <form>Search, Priority filters</form>
    </div>
    
    <!-- Kanban Board -->
    <div class="pipeline-board">
        <div class="pipeline-container">
            @foreach($stages as $stage)
                <div class="pipeline-column">
                    <div class="pipeline-column-header">
                        Stage title, count, total value
                    </div>
                    <div class="pipeline-cards">
                        @foreach($stage['bookings'] as $booking)
                            <div class="pipeline-card" draggable="true">
                                Booking card content
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Booking Details Modal -->
    <div class="modal" id="bookingDetailsModal">...</div>
</div>
```

**CSS Highlights**:

```css
.pipeline-board {
    overflow-x: auto;
    padding-bottom: 20px;
}

.pipeline-container {
    display: flex;
    gap: 20px;
    min-width: max-content;
}

.pipeline-column {
    flex: 0 0 320px;
    background: #f8f9fa;
    border-radius: 8px;
    max-height: calc(100vh - 400px);
}

.pipeline-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    cursor: move;
    transition: all 0.2s;
}

.pipeline-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.pipeline-card.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.pipeline-cards.drag-over {
    background: rgba(13, 110, 253, 0.1);
    border: 2px dashed #0d6efd;
}
```

**JavaScript Drag & Drop**:

```javascript
let draggedCard = null;
let draggedBookingId = null;
let draggedFromStage = null;

function handleDragStart(e) {
    draggedCard = this;
    draggedBookingId = this.getAttribute('data-booking-id');
    draggedFromStage = this.closest('.pipeline-cards').getAttribute('data-stage');
    this.classList.add('dragging');
}

function handleDrop(e) {
    e.preventDefault();
    const toStage = this.getAttribute('data-stage');
    
    if (draggedFromStage !== toStage) {
        moveBooking(draggedBookingId, draggedFromStage, toStage);
    }
}

function moveBooking(bookingId, fromStage, toStage) {
    fetch('/vendor/pipeline/move', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ booking_id: bookingId, from_stage: fromStage, to_stage: toStage })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Booking moved successfully', 'success');
            refreshPipeline();
        } else {
            showToast(data.message, 'error');
            location.reload();
        }
    });
}
```

---

## Installation

### Step 1: Run Migrations

No additional migrations required. The pipeline uses existing `bookings` and `booking_timelines` tables.

### Step 2: Verify Dependencies

Ensure these models exist:
- `App\Models\Booking`
- `App\Models\User`
- `App\Models\BookingTimeline`
- `App\Models\BookingTimelineEvent`

### Step 3: Add Routes

Routes are already added to `routes/web.php` under the vendor middleware group.

### Step 4: Test Access

1. Login as a vendor
2. Navigate to `/vendor/pipeline`
3. Verify the Kanban board loads

---

## Usage Guide

### For Vendors

#### Accessing the Pipeline

1. Login to vendor panel
2. Click **"Pipeline Board"** in the sidebar
3. View all your bookings organized by stage

#### Using Drag & Drop

1. Click and hold a booking card
2. Drag to the target stage column
3. Drop the card
4. The system validates and updates the booking

**Visual Feedback**:
- Dragged card becomes semi-transparent
- Target column highlights with blue border
- Success/error toast notification appears

#### Filtering Bookings

Use the filter form:
- **Search**: Booking ID, customer name, hoarding title
- **Priority**: Show only high-priority bookings (starting soon or high-value)

#### Viewing Booking Details

1. Click **"View Details"** button on any card
2. Modal displays:
   - Booking information
   - Customer details
   - Hoarding details with image
   - Financial summary
   - Campaign dates
   - Recent timeline events

#### Exporting Data

1. Click **"Export"** dropdown
2. Choose format:
   - **CSV**: Spreadsheet format for Excel
   - **PDF**: Formatted report

Exports respect current filters.

---

### For Developers

#### Adding a New Stage

1. **Update Service Configuration**:

```php
// app/Services/BookingPipelineService.php
const STAGES = [
    // ... existing stages
    'new_stage' => [
        'label' => 'New Stage',
        'icon' => 'fa-icon-name',
        'color' => 'primary',  // Bootstrap color
        'order' => 12,
        'optional' => false,
    ],
];
```

2. **Add Stage Logic**:

```php
protected function getBookingsForStage(User $vendor, string $stage, array $filters = [])
{
    // ... existing cases
    case 'new_stage':
        $query->where('some_condition', 'value');
        break;
}
```

3. **Update Transition Validation** (if needed):

```php
protected function isValidTransition(string $from, string $to): bool
{
    // Add custom validation logic
    if ($to === 'new_stage' && $from !== 'required_previous_stage') {
        return false;
    }
    return true;
}
```

4. **Update Stage Actions**:

```php
protected function updateBookingForStage(Booking $booking, string $stage, User $user): void
{
    case 'new_stage':
        $booking->update(['status' => 'new_status']);
        // Additional logic
        break;
}
```

#### Customizing Card Display

Edit the `formatBookingCard()` method:

```php
protected function formatBookingCard(Booking $booking): array
{
    return [
        // ... existing fields
        'custom_field' => $booking->calculateCustomValue(),
    ];
}
```

Then update the view template:

```blade
<div class="pipeline-card">
    <!-- Existing content -->
    <div class="custom-display">{{ $booking['custom_field'] }}</div>
</div>
```

#### Adding New Filters

1. **Update Controller**:

```php
$filters = [
    // ... existing filters
    'new_filter' => $request->get('new_filter'),
];
```

2. **Update Service**:

```php
if (!empty($filters['new_filter'])) {
    $query->where('column', $filters['new_filter']);
}
```

3. **Update View**:

```blade
<select name="new_filter" class="form-select">
    <option value="">All</option>
    <option value="value1">Option 1</option>
</select>
```

---

## API Reference

### GET /vendor/pipeline

Main pipeline view.

**Query Parameters**:
- `search` (optional): Search term
- `hoarding_id` (optional): Filter by hoarding
- `customer_id` (optional): Filter by customer
- `priority` (optional): `high` for priority bookings

**Response**: HTML view

---

### GET /vendor/pipeline/data

AJAX endpoint for pipeline data.

**Query Parameters**: Same as main view

**Response**:
```json
{
    "success": true,
    "data": {
        "stages": {
            "new_enquiry": {
                "key": "new_enquiry",
                "label": "New Enquiry",
                "count": 5,
                "total_value": 500000,
                "bookings": [...]
            },
            // ... other stages
        },
        "summary": {
            "total_bookings": 45,
            "total_value": 12500000,
            "active_bookings": 12,
            "urgent_bookings": 3,
            "conversion_rate": 67.5
        }
    }
}
```

---

### POST /vendor/pipeline/move

Move booking between stages.

**Request Body**:
```json
{
    "booking_id": 123,
    "from_stage": "quotation_sent",
    "to_stage": "in_payment"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Booking moved successfully",
    "booking": {
        "id": 123,
        "booking_id": "BK-2025-001",
        // ... formatted booking data
    }
}
```

**Error Response**:
```json
{
    "success": false,
    "message": "Invalid stage transition from quotation_sent to completed"
}
```

---

### GET /vendor/pipeline/booking/{id}

Get booking details.

**Response**:
```json
{
    "success": true,
    "booking": {
        "id": 123,
        "booking_id": "BK-2025-001",
        "status": "payment_settled",
        "customer": {
            "id": 45,
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "+91 98765 43210"
        },
        "hoarding": {
            "id": 10,
            "title": "MG Road Billboard",
            "location": "MG Road, Near Metro",
            "city": "Bangalore",
            "type": "billboard",
            "image_url": "/storage/hoardings/10.jpg"
        },
        "dates": {
            "start": "Jan 15, 2025",
            "end": "Feb 15, 2025",
            "duration_days": 31
        },
        "financials": {
            "total_amount": 250000,
            "total_amount_formatted": "₹2,50,000",
            "payment_status": "paid"
        },
        "timeline": [
            {
                "title": "Payment Confirmed",
                "description": "Payment of ₹2,50,000 received",
                "status": "completed",
                "created_at": "Jan 12, 2025 14:30",
                "created_at_human": "2 hours ago"
            }
        ]
    }
}
```

---

### GET /vendor/pipeline/stats

Get pipeline statistics.

**Response**:
```json
{
    "success": true,
    "stats": {
        "total_bookings": 45,
        "total_value": 12500000,
        "total_value_formatted": "₹1,25,00,000",
        "active_bookings": 12,
        "urgent_bookings": 3,
        "conversion_rate": 67.5
    },
    "stage_counts": {
        "new_enquiry": 5,
        "offer_sent": 3,
        "quotation_sent": 7,
        "in_payment": 2,
        "booked": 4,
        "designing": 3,
        "printing": 2,
        "mounting": 1,
        "live": 8,
        "survey": 0,
        "completed": 10
    }
}
```

---

### POST /vendor/pipeline/bulk-move

Move multiple bookings to a stage.

**Request Body**:
```json
{
    "booking_ids": [123, 124, 125],
    "to_stage": "designing"
}
```

**Response**:
```json
{
    "success": true,
    "message": "3 bookings moved successfully, 0 failed",
    "results": [
        {
            "booking_id": 123,
            "success": true,
            "message": "Booking moved successfully"
        },
        {
            "booking_id": 124,
            "success": true,
            "message": "Booking moved successfully"
        },
        {
            "booking_id": 125,
            "success": false,
            "message": "Invalid transition"
        }
    ]
}
```

---

### GET /vendor/pipeline/export

Export pipeline data.

**Query Parameters**:
- `format`: `csv` or `pdf` (default: csv)
- All filter parameters

**Response**: File download (CSV or PDF)

**CSV Format**:
```csv
Stage,Booking ID,Customer,Hoarding,Location,City,Start Date,End Date,Amount,Status,Created At
New Enquiry,BK-2025-001,John Doe,MG Road Billboard,MG Road,Bangalore,Jan 15 2025,Feb 15 2025,250000,Pending,Jan 10 2025
```

---

## Customization

### Changing Stage Colors

Edit `BookingPipelineService::STAGES`:

```php
'designing' => [
    'label' => 'Designing',
    'icon' => 'fa-pencil-ruler',
    'color' => 'danger',  // Change to any Bootstrap color
    'order' => 6,
],
```

Available colors: `primary`, `secondary`, `success`, `danger`, `warning`, `info`, `light`, `dark`

### Custom Stage Icons

Use any Font Awesome icon:

```php
'printing' => [
    'label' => 'Printing',
    'icon' => 'fa-print',  // Change to fa-custom-icon
    'color' => 'cyan',
    'order' => 7,
],
```

### Adjust Card Limits

Change the limit per stage in `getBookingsForStage()`:

```php
return $query->orderBy('created_at', 'desc')
    ->limit(100) // Default is 50
    ->get();
```

### Custom Urgency Threshold

Change the 7-day threshold:

```php
// In formatBookingCard()
'is_urgent' => $daysUntilStart !== null && $daysUntilStart >= 0 && $daysUntilStart <= 3, // Changed from 7
```

### Custom High-Value Threshold

```php
// In formatBookingCard()
'is_high_value' => $booking->total_amount >= 500000, // Changed from 100000
```

---

## Testing

### Manual Testing

1. **Create Test Bookings** in different stages:
   ```php
   php artisan tinker
   
   // Create booking in "New Enquiry" stage
   $booking = Booking::factory()->create([
       'vendor_id' => 1,
       'status' => 'pending',
   ]);
   
   // Create booking in "Booked" stage
   $booking = Booking::factory()->create([
       'vendor_id' => 1,
       'status' => 'payment_settled',
   ]);
   ```

2. **Test Drag & Drop**:
   - Drag a card between adjacent stages (should succeed)
   - Try skipping 3 stages (should fail with error)
   - Drag backward (should succeed)

3. **Test Filters**:
   - Search by booking ID
   - Filter by priority
   - Verify counts update

4. **Test Export**:
   - Export as CSV (check all bookings included)
   - Export as PDF (check formatting)

### Automated Testing

Create test file: `tests/Feature/VendorPipelineTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendorPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_view_pipeline()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        
        $response = $this->actingAs($vendor)
            ->get('/vendor/pipeline');
        
        $response->assertStatus(200);
        $response->assertViewHas(['stages', 'summary']);
    }

    public function test_vendor_can_move_booking()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $booking = Booking::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => 'quotation_sent',
        ]);
        
        $response = $this->actingAs($vendor)
            ->postJson('/vendor/pipeline/move', [
                'booking_id' => $booking->id,
                'from_stage' => 'quotation_sent',
                'to_stage' => 'in_payment',
            ]);
        
        $response->assertJson(['success' => true]);
        $this->assertEquals('payment_hold', $booking->fresh()->status);
    }

    public function test_vendor_cannot_skip_multiple_stages()
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $booking = Booking::factory()->create([
            'vendor_id' => $vendor->id,
            'status' => 'pending',
        ]);
        
        $response = $this->actingAs($vendor)
            ->postJson('/vendor/pipeline/move', [
                'booking_id' => $booking->id,
                'from_stage' => 'new_enquiry',
                'to_stage' => 'completed',
            ]);
        
        $response->assertJson(['success' => false]);
    }
}
```

Run tests:
```bash
php artisan test --filter VendorPipelineTest
```

---

## Troubleshooting

### Issue: Cards Not Appearing

**Symptoms**: Pipeline columns are empty

**Solutions**:
1. Check bookings exist for the vendor: `Booking::where('vendor_id', auth()->id())->count()`
2. Verify timeline events are created: `BookingTimeline::whereHas('booking', fn($q) => $q->where('vendor_id', auth()->id()))->count()`
3. Check stage logic in `getBookingsForStage()`

### Issue: Drag & Drop Not Working

**Symptoms**: Cards don't move or no feedback

**Solutions**:
1. Check browser console for JavaScript errors
2. Verify CSRF token is present: `<meta name="csrf-token" content="{{ csrf_token() }}">`
3. Check if `draggable="true"` attribute is on cards
4. Verify event listeners are attached: `initializeDragAndDrop()` is called

### Issue: Invalid Stage Transition

**Symptoms**: Error when moving booking

**Solutions**:
1. Check `isValidTransition()` logic
2. Verify current stage detection in `detectCurrentStage()`
3. Review stage order numbers
4. Check if trying to skip more than 2 stages

### Issue: Export Not Working

**Symptoms**: PDF/CSV download fails

**Solutions**:
1. For PDF: Ensure `barryvdh/laravel-dompdf` is installed
2. For CSV: Check streaming response headers
3. Verify export view exists: `resources/views/vendor/pipeline/export-pdf.blade.php`
4. Check file permissions on storage directory

### Issue: Statistics Incorrect

**Symptoms**: Summary cards show wrong numbers

**Solutions**:
1. Check `getPipelineSummary()` calculations
2. Verify booking statuses in database
3. Check filter application in queries
4. Clear cache: `php artisan cache:clear`

### Issue: Performance Slow

**Symptoms**: Pipeline loads slowly with many bookings

**Solutions**:
1. Increase limit per stage (currently 50)
2. Add database indexes on frequently queried columns:
   ```php
   Schema::table('bookings', function (Blueprint $table) {
       $table->index(['vendor_id', 'status']);
       $table->index(['vendor_id', 'start_date', 'end_date']);
   });
   ```
3. Implement pagination per stage
4. Use eager loading for relationships

---

## Advanced Features

### Real-time Updates with WebSockets

For live updates when bookings change, integrate Laravel Echo:

```javascript
// Listen for booking updates
Echo.private('vendor.' + vendorId)
    .listen('BookingUpdated', (e) => {
        refreshPipeline();
    });
```

### Stage Automation Rules

Create automated stage transitions based on events:

```php
// app/Listeners/AutoMoveBookingStage.php
public function handle(PaymentConfirmed $event)
{
    $booking = $event->booking;
    
    $pipelineService = app(BookingPipelineService::class);
    $pipelineService->moveBooking(
        $booking,
        'in_payment',
        'booked',
        $booking->vendor
    );
}
```

### Custom Stage Notifications

Send notifications when bookings enter specific stages:

```php
// In updateBookingForStage()
if ($stage === 'live') {
    $booking->customer->notify(new CampaignGoingLive($booking));
    $booking->vendor->notify(new CampaignStarted($booking));
}
```

---

## Performance Optimization

### Database Indexing

```sql
CREATE INDEX idx_bookings_vendor_status ON bookings(vendor_id, status);
CREATE INDEX idx_bookings_dates ON bookings(start_date, end_date);
CREATE INDEX idx_timeline_events_type ON booking_timeline_events(event_type, status);
```

### Query Optimization

Use selective loading:

```php
$query->with([
    'customer:id,name,avatar_url',
    'hoarding:id,title,location,city,image_url',
    'timeline' => function($q) {
        $q->select('id', 'booking_id')->with([
            'events' => function($q2) {
                $q2->select('id', 'timeline_id', 'event_type', 'status', 'created_at')
                   ->latest()
                   ->limit(1);
            }
        ]);
    }
]);
```

### Caching Strategy

Cache pipeline data:

```php
$cacheKey = "pipeline.vendor.{$vendor->id}." . md5(json_encode($filters));

$pipelineData = Cache::remember($cacheKey, 300, function() use ($vendor, $filters) {
    return $this->pipelineService->getVendorPipeline($vendor, $filters);
});

// Clear cache on booking update
Cache::forget("pipeline.vendor.{$booking->vendor_id}.*");
```

---

## Security Considerations

### Authorization Checks

All endpoints verify vendor ownership:

```php
if ($booking->vendor_id !== Auth::id()) {
    abort(403);
}
```

### CSRF Protection

All POST requests include CSRF token:

```javascript
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
}
```

### Input Validation

All requests validated:

```php
$validated = $request->validate([
    'booking_id' => 'required|exists:bookings,id',
    'from_stage' => 'required|string|in:' . implode(',', array_keys(BookingPipelineService::STAGES)),
    'to_stage' => 'required|string|in:' . implode(',', array_keys(BookingPipelineService::STAGES)),
]);
```

---

## Changelog

### Version 1.0.0 (December 15, 2025)

- ✅ Initial release
- ✅ 11-stage pipeline
- ✅ Drag & drop functionality
- ✅ Real-time statistics
- ✅ Export to CSV/PDF
- ✅ Filters and search
- ✅ Booking details modal
- ✅ Responsive design
- ✅ Stage validation
- ✅ Bulk operations

---

## Support

For issues or questions:
- Check this documentation
- Review code comments in service/controller files
- Test with sample data
- Check Laravel logs: `storage/logs/laravel.log`

---

## License

Part of OOHAPP platform - All rights reserved
