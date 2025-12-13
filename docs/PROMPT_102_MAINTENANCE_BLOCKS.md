# PROMPT 102: Admin Blocking Periods (Maintenance/Repairs)

**Complete Implementation Guide**

## 📋 Overview

The Maintenance Blocks feature allows **Admin** and **Vendor** users to mark certain dates as unavailable for booking due to maintenance, repairs, inspections, or other operational reasons. These blocked periods are **automatically integrated** with the Booking Overlap Validation Engine (PROMPT 101) to prevent customers from booking hoardings during maintenance periods.

### Key Features

✅ **Admin & Vendor Access** - Both admin and vendors can create blocks for hoardings  
✅ **Multiple Block Types** - Maintenance, Repair, Inspection, Other  
✅ **Conflict Detection** - Warns about existing bookings before creating blocks  
✅ **Calendar Integration** - Provides blocked dates for UI calendar display  
✅ **Overlap Prevention** - Prevents overlapping maintenance blocks  
✅ **Status Management** - Active, Completed, Cancelled statuses  
✅ **Statistics & Analytics** - Get insights on maintenance patterns  
✅ **Automatic Integration** - Works seamlessly with booking overlap validator

---

## 🏗️ Architecture

### Database Schema

**Table:** `maintenance_blocks`

```sql
CREATE TABLE maintenance_blocks (
    id BIGINT UNSIGNED PRIMARY KEY,
    hoarding_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    block_type ENUM('maintenance', 'repair', 'inspection', 'other') DEFAULT 'maintenance',
    affected_by VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    
    FOREIGN KEY (hoarding_id) REFERENCES hoardings(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX hoarding_status_dates_idx (hoarding_id, status, start_date, end_date),
    INDEX (start_date, end_date),
    INDEX (status)
);
```

### File Structure

```
app/
├── Models/
│   └── MaintenanceBlock.php                    # Model with scopes & methods (240 lines)
├── Services/
│   └── MaintenanceBlockService.php             # Business logic service (450 lines)
│   └── BookingOverlapValidator.php             # Updated with block detection
├── Http/
│   ├── Controllers/Api/
│   │   └── MaintenanceBlockController.php      # API endpoints (450 lines)
│   └── Requests/
│       ├── CreateMaintenanceBlockRequest.php   # Validation for create
│       └── UpdateMaintenanceBlockRequest.php   # Validation for update

database/
├── migrations/
│   └── 2025_12_13_000001_create_maintenance_blocks_table.php
└── factories/
    └── MaintenanceBlockFactory.php             # Factory for testing

routes/
└── api_v1/
    └── maintenance_blocks.php                  # API route definitions

tests/
└── Feature/
    ├── MaintenanceBlockServiceTest.php         # Service tests (15 tests)
    ├── Api/MaintenanceBlockApiTest.php         # API tests (20 tests)
    └── MaintenanceBlockOverlapIntegrationTest.php  # Integration tests (7 tests)

docs/
└── PROMPT_102_MAINTENANCE_BLOCKS.md            # This file
```

---

## 📡 API Reference

### Base URL
```
/api/v1/maintenance-blocks
```

All endpoints require `auth:sanctum` authentication.

### 1. List Maintenance Blocks

**GET** `/api/v1/maintenance-blocks`

Get all maintenance blocks for a hoarding with optional filters.

**Query Parameters:**
```
hoarding_id (required)  - Hoarding ID
status (optional)       - Filter by status: active, completed, cancelled
block_type (optional)   - Filter by type: maintenance, repair, inspection, other
start_date (optional)   - Filter by date range start
end_date (optional)     - Filter by date range end (required if start_date provided)
```

**Example Request:**
```bash
GET /api/v1/maintenance-blocks?hoarding_id=1&status=active
Authorization: Bearer {token}
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "hoarding_id": 1,
      "created_by": 2,
      "title": "Annual Maintenance",
      "description": "Routine cleaning and inspection",
      "start_date": "2025-12-20",
      "end_date": "2025-12-25",
      "status": "active",
      "block_type": "maintenance",
      "affected_by": null,
      "notes": null,
      "created_at": "2025-12-13T10:00:00.000000Z",
      "updated_at": "2025-12-13T10:00:00.000000Z",
      "creator": {
        "id": 2,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "hoarding": {
        "id": 1,
        "title": "Billboard on Main Street"
      }
    }
  ],
  "count": 1
}
```

---

### 2. Get Single Maintenance Block

**GET** `/api/v1/maintenance-blocks/{id}`

Retrieve details of a specific maintenance block.

**Authorization:**
- Admin: Can view all blocks
- Vendor: Can only view blocks for their own hoardings

**Example Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "hoarding_id": 1,
    "title": "Annual Maintenance",
    "start_date": "2025-12-20",
    "end_date": "2025-12-25",
    "status": "active",
    "block_type": "maintenance"
  }
}
```

---

### 3. Create Maintenance Block

**POST** `/api/v1/maintenance-blocks`

Create a new maintenance block. Checks for conflicts with existing bookings.

**Authorization:**
- Admin: Can create blocks for any hoarding
- Vendor: Can only create blocks for their own hoardings

**Request Body:**
```json
{
  "hoarding_id": 1,
  "title": "Painting Work",
  "description": "Repainting the billboard surface",
  "start_date": "2025-12-20",
  "end_date": "2025-12-25",
  "status": "active",
  "block_type": "maintenance",
  "affected_by": "Weather",
  "notes": "Scheduled during low booking season",
  "force_create": false
}
```

**Field Validation:**
- `hoarding_id` (required, exists in hoardings)
- `title` (required, max 255 chars)
- `description` (optional, max 1000 chars)
- `start_date` (required, date, >= today)
- `end_date` (required, date, >= start_date)
- `status` (optional, one of: active, completed, cancelled)
- `block_type` (optional, one of: maintenance, repair, inspection, other)
- `affected_by` (optional, max 255 chars)
- `notes` (optional, max 1000 chars)
- `force_create` (optional, boolean) - Create even if bookings conflict

**Success Response (201):**
```json
{
  "success": true,
  "message": "Maintenance block created successfully.",
  "data": {
    "id": 5,
    "hoarding_id": 1,
    "title": "Painting Work",
    "start_date": "2025-12-20",
    "end_date": "2025-12-25"
  },
  "warnings": []
}
```

**Conflict Response (422):**
```json
{
  "success": false,
  "message": "Cannot create maintenance block due to conflicts.",
  "warnings": [
    "WARNING: 2 existing booking(s) will be affected by this maintenance block.",
    "Booking #123 (2025-12-22 to 2025-12-28) for John Doe",
    "Booking #124 (2025-12-24 to 2025-12-30) for Jane Smith"
  ],
  "conflicting_bookings": [...]
}
```

**Force Create (with conflicts):**
```json
{
  "success": true,
  "message": "Maintenance block created successfully.",
  "data": {...},
  "warnings": [
    "WARNING: 2 existing booking(s) will be affected by this maintenance block.",
    "..."
  ]
}
```

---

### 4. Update Maintenance Block

**PUT** `/api/v1/maintenance-blocks/{id}`

Update an existing maintenance block.

**Authorization:**
- Admin: Can update all blocks
- Vendor: Can only update blocks for their own hoardings

**Request Body:**
```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "start_date": "2025-12-21",
  "end_date": "2025-12-26",
  "status": "active",
  "notes": "Extended due to weather"
}
```

All fields are optional.

**Response (200):**
```json
{
  "success": true,
  "message": "Maintenance block updated successfully.",
  "data": {
    "id": 1,
    "title": "Updated Title",
    "description": "Updated description"
  }
}
```

---

### 5. Delete Maintenance Block

**DELETE** `/api/v1/maintenance-blocks/{id}`

Soft delete a maintenance block.

**Authorization:**
- Admin: Can delete all blocks
- Vendor: Can only delete blocks for their own hoardings

**Response (200):**
```json
{
  "success": true,
  "message": "Maintenance block deleted successfully."
}
```

---

### 6. Mark Block as Completed

**POST** `/api/v1/maintenance-blocks/{id}/complete`

Mark a maintenance block as completed.

**Response (200):**
```json
{
  "success": true,
  "message": "Maintenance block marked as completed.",
  "data": {
    "id": 1,
    "status": "completed"
  }
}
```

---

### 7. Mark Block as Cancelled

**POST** `/api/v1/maintenance-blocks/{id}/cancel`

Cancel a maintenance block.

**Response (200):**
```json
{
  "success": true,
  "message": "Maintenance block cancelled.",
  "data": {
    "id": 1,
    "status": "cancelled"
  }
}
```

---

### 8. Check Availability

**GET** `/api/v1/maintenance-blocks/check/availability`

Check if a hoarding is available (no active maintenance blocks) for a date range.

**Query Parameters:**
```
hoarding_id (required)
start_date (required)
end_date (required)
```

**Example Request:**
```bash
GET /api/v1/maintenance-blocks/check/availability?hoarding_id=1&start_date=2025-12-20&end_date=2025-12-25
```

**Response (Available):**
```json
{
  "success": true,
  "data": {
    "available": true,
    "blocks": [],
    "message": "Hoarding is available for the selected dates."
  }
}
```

**Response (Not Available):**
```json
{
  "success": true,
  "data": {
    "available": false,
    "blocks": [
      {
        "id": 1,
        "title": "Maintenance Work",
        "start_date": "2025-12-20",
        "end_date": "2025-12-25"
      }
    ],
    "message": "Hoarding has 1 active maintenance block(s) during this period: Maintenance Work"
  }
}
```

---

### 9. Get Blocked Dates (Calendar)

**GET** `/api/v1/maintenance-blocks/check/blocked-dates`

Get all blocked dates for calendar display (day-by-day breakdown).

**Query Parameters:**
```
hoarding_id (required)
start_date (required)
end_date (required)
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "date": "2025-12-20",
      "blocks": [
        {
          "id": 1,
          "title": "Annual Maintenance",
          "block_type": "maintenance",
          "description": "Routine work"
        }
      ]
    },
    {
      "date": "2025-12-21",
      "blocks": [
        {
          "id": 1,
          "title": "Annual Maintenance",
          "block_type": "maintenance",
          "description": "Routine work"
        }
      ]
    }
  ],
  "count": 2
}
```

---

### 10. Get Statistics

**GET** `/api/v1/maintenance-blocks/check/statistics`

Get statistics about maintenance blocks for a hoarding.

**Query Parameters:**
```
hoarding_id (required)
start_date (optional)
end_date (optional)
```

**Example Response:**
```json
{
  "success": true,
  "data": {
    "total_blocks": 10,
    "active_blocks": 2,
    "completed_blocks": 7,
    "cancelled_blocks": 1,
    "by_type": {
      "maintenance": 5,
      "repair": 3,
      "inspection": 2,
      "other": 0
    },
    "total_blocked_days": 45,
    "current_blocks": 1,
    "future_blocks": 1
  }
}
```

---

### 11. Get Conflicting Bookings

**GET** `/api/v1/maintenance-blocks/check/conflicting-bookings`

Check for existing bookings that would conflict with a proposed block.

**Query Parameters:**
```
hoarding_id (required)
start_date (required)
end_date (required)
```

**Example Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "hoarding_id": 1,
      "customer_id": 45,
      "start_date": "2025-12-22",
      "end_date": "2025-12-28",
      "status": "confirmed",
      "total_amount": 50000,
      "customer": {
        "id": 45,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "count": 1,
  "has_conflicts": true
}
```

---

## 🎯 Service Layer API

### MaintenanceBlockService

```php
use App\Services\MaintenanceBlockService;

$service = app(MaintenanceBlockService::class);
```

#### Methods

**1. Create Block**
```php
public function create(array $data, int $creatorId): MaintenanceBlock

// Example
$block = $service->create([
    'hoarding_id' => 1,
    'title' => 'Maintenance',
    'start_date' => '2025-12-20',
    'end_date' => '2025-12-25',
], auth()->id());
```

**2. Create with Conflict Check**
```php
public function createWithConflictCheck(
    array $data,
    int $creatorId,
    bool $forceCreate = false
): array

// Returns
[
    'success' => bool,
    'block' => MaintenanceBlock|null,
    'warnings' => array,
    'conflicting_bookings' => Collection
]

// Example
$result = $service->createWithConflictCheck($data, auth()->id(), false);
if (!$result['success']) {
    // Show warnings to user
    foreach ($result['warnings'] as $warning) {
        echo $warning;
    }
}
```

**3. Update Block**
```php
public function update(MaintenanceBlock $block, array $data): MaintenanceBlock

$updated = $service->update($block, ['title' => 'New Title']);
```

**4. Delete Block**
```php
public function delete(MaintenanceBlock $block): bool

$service->delete($block);
```

**5. Check Availability**
```php
public function checkAvailability(int $hoardingId, $startDate, $endDate): array

$result = $service->checkAvailability(1, '2025-12-20', '2025-12-25');
if ($result['available']) {
    // Proceed with booking
}
```

**6. Get Blocked Dates**
```php
public function getBlockedDates(int $hoardingId, $startDate, $endDate): array

$blockedDates = $service->getBlockedDates(1, '2025-12-01', '2025-12-31');
// Returns array of dates with block information
```

**7. Get Statistics**
```php
public function getStatistics(int $hoardingId, $startDate = null, $endDate = null): array

$stats = $service->getStatistics(1);
```

**8. Get Conflicting Bookings**
```php
public function getConflictingBookings(int $hoardingId, $startDate, $endDate): Collection

$conflicts = $service->getConflictingBookings(1, '2025-12-20', '2025-12-25');
```

---

## 📦 Model API

### MaintenanceBlock Model

#### Constants

```php
MaintenanceBlock::STATUS_ACTIVE      // 'active'
MaintenanceBlock::STATUS_COMPLETED   // 'completed'
MaintenanceBlock::STATUS_CANCELLED   // 'cancelled'

MaintenanceBlock::TYPE_MAINTENANCE   // 'maintenance'
MaintenanceBlock::TYPE_REPAIR        // 'repair'
MaintenanceBlock::TYPE_INSPECTION    // 'inspection'
MaintenanceBlock::TYPE_OTHER         // 'other'
```

#### Query Scopes

```php
// Filter by hoarding
MaintenanceBlock::forHoarding(1)->get();

// Active blocks only
MaintenanceBlock::active()->get();

// Filter by status
MaintenanceBlock::byStatus('completed')->get();

// Filter by block type
MaintenanceBlock::byType('repair')->get();

// Overlapping with date range
MaintenanceBlock::overlapping('2025-12-20', '2025-12-25')->get();

// Future blocks (end_date >= today)
MaintenanceBlock::future()->get();

// Past blocks (end_date < today)
MaintenanceBlock::past()->get();

// Current blocks (today within range)
MaintenanceBlock::current()->get();

// Combining scopes
MaintenanceBlock::forHoarding(1)
    ->active()
    ->overlapping('2025-12-20', '2025-12-25')
    ->get();
```

#### Instance Methods

```php
$block = MaintenanceBlock::find(1);

// Check if overlaps with date range
$block->overlapsWith('2025-12-22', '2025-12-28'); // Returns bool

// Check if active
$block->isActive(); // Returns bool

// Mark as completed
$block->markCompleted();

// Mark as cancelled
$block->markCancelled();

// Get duration in days
$block->getDurationDays(); // Returns int
```

#### Static Methods

```php
// Check if hoarding has active blocks
MaintenanceBlock::hasActiveBlocks(1, '2025-12-20', '2025-12-25'); // Returns bool

// Get all active blocks for date range
$blocks = MaintenanceBlock::getActiveBlocks(1, '2025-12-20', '2025-12-25');
```

#### Relationships

```php
$block = MaintenanceBlock::with('hoarding', 'creator')->find(1);

$block->hoarding;  // Hoarding model
$block->creator;   // User who created the block
```

---

## 🔗 Integration with Booking Overlap Validator

The Booking Overlap Validator (PROMPT 101) **automatically checks** maintenance blocks when validating booking dates.

### Updated BookingOverlapValidator Behavior

When you call `validateAvailability()` or `isAvailable()`, the validator now checks:

1. ✅ Confirmed bookings
2. ✅ Active payment holds
3. ✅ POS bookings (if module exists)
4. ✅ **Active maintenance blocks** ← NEW

### Example Integration

```php
use App\Services\BookingOverlapValidator;

$validator = app(BookingOverlapValidator::class);

// This now automatically checks maintenance blocks
$result = $validator->validateAvailability(
    hoardingId: 1,
    startDate: '2025-12-20',
    endDate: '2025-12-25',
    excludeBookingId: null,
    includeGracePeriod: true
);

if (!$result['available']) {
    echo $result['message'];
    // Example: "Hoarding not available: Conflicts with maintenance block: Annual Maintenance from 2025-12-20 to 2025-12-25"
    
    foreach ($result['conflicts'] as $conflict) {
        if ($conflict['type'] === 'maintenance_block') {
            echo "Blocked for: " . $conflict['title'];
            echo "Type: " . $conflict['block_type'];
            echo "Created by: " . $conflict['created_by'];
        }
    }
}
```

### Conflict Object Structure

When a maintenance block conflict is detected:

```php
[
    'type' => 'maintenance_block',
    'id' => 1,
    'title' => 'Annual Maintenance',
    'start_date' => '2025-12-20',
    'end_date' => '2025-12-25',
    'block_type' => 'maintenance',
    'description' => 'Routine work',
    'created_by' => 'Admin User',
    'created_at' => '2025-12-13 10:00:00',
]
```

---

## 🧪 Testing

### Run Tests

```bash
# All maintenance block tests
php artisan test --filter=MaintenanceBlock

# Service tests only
php artisan test tests/Feature/MaintenanceBlockServiceTest.php

# API tests only
php artisan test tests/Feature/Api/MaintenanceBlockApiTest.php

# Integration tests only
php artisan test tests/Feature/MaintenanceBlockOverlapIntegrationTest.php
```

### Test Coverage

- **Service Tests (15 tests):**
  - Create block successfully
  - Prevent overlapping blocks
  - Allow non-overlapping blocks
  - Update block
  - Mark completed/cancelled
  - Check availability
  - Get blocked dates
  - Get statistics
  - Detect conflicting bookings
  - Create with conflict warning
  - Model scopes work correctly

- **API Tests (20 tests):**
  - Authentication required
  - Admin can create blocks
  - Vendor can create for own hoarding
  - Vendor cannot create for other hoarding
  - Field validation
  - Date logic validation
  - List blocks with filters
  - Update/delete authorization
  - Status change endpoints
  - Availability check
  - Blocked dates for calendar
  - Statistics endpoint

- **Integration Tests (7 tests):**
  - Overlap validator detects blocks
  - Validator ignores completed blocks
  - Validator ignores cancelled blocks
  - Conflict messages include blocks
  - Quick check returns false with blocks
  - Occupied dates include blocks
  - Conflict details include block info

---

## 📊 Usage Examples

### Example 1: Admin Creates Emergency Repair Block

```php
// Admin creates emergency repair block
$admin = User::find(1); // Admin user

$result = app(MaintenanceBlockService::class)->createWithConflictCheck([
    'hoarding_id' => 5,
    'title' => 'Emergency Structural Repair',
    'description' => 'Critical repairs needed due to storm damage',
    'start_date' => Carbon::tomorrow(),
    'end_date' => Carbon::tomorrow()->addDays(7),
    'block_type' => MaintenanceBlock::TYPE_REPAIR,
    'affected_by' => 'Storm damage',
], $admin->id, false);

if (!$result['success']) {
    // 2 bookings will be affected
    // Show warnings to admin
    Log::warning('Maintenance block conflicts', $result['warnings']);
    
    // Admin decides to proceed anyway
    $result = app(MaintenanceBlockService::class)->createWithConflictCheck([
        // Same data...
    ], $admin->id, true); // Force create
    
    // Notify affected customers
    foreach ($result['conflicting_bookings'] as $booking) {
        $booking->customer->notify(new MaintenanceScheduledNotification($result['block']));
    }
}
```

### Example 2: Vendor Schedules Routine Maintenance

```php
// Vendor schedules maintenance during low season
$vendor = auth()->user();

$hoarding = $vendor->hoardings()->find(10);

// Check for conflicts first
$conflicts = app(MaintenanceBlockService::class)->getConflictingBookings(
    $hoarding->id,
    '2025-01-15',
    '2025-01-20'
);

if ($conflicts->isEmpty()) {
    // Safe to schedule
    $block = app(MaintenanceBlockService::class)->create([
        'hoarding_id' => $hoarding->id,
        'title' => 'Annual Maintenance',
        'description' => 'Cleaning, repainting, and light replacement',
        'start_date' => '2025-01-15',
        'end_date' => '2025-01-20',
        'block_type' => MaintenanceBlock::TYPE_MAINTENANCE,
    ], $vendor->id);
    
    Log::info("Maintenance scheduled for hoarding {$hoarding->id}");
}
```

### Example 3: Display Blocked Dates in Calendar

```php
// Frontend: Get blocked dates for calendar month view
$blockedDates = app(MaintenanceBlockService::class)->getBlockedDates(
    hoardingId: 1,
    startDate: Carbon::parse('2025-12-01'),
    endDate: Carbon::parse('2025-12-31')
);

// Returns:
// [
//     ['date' => '2025-12-20', 'blocks' => [...]],
//     ['date' => '2025-12-21', 'blocks' => [...]],
//     ...
// ]

// In Blade template:
@foreach($blockedDates as $blockedDate)
    <div class="calendar-day blocked" data-date="{{ $blockedDate['date'] }}">
        @foreach($blockedDate['blocks'] as $block)
            <span class="block-indicator" title="{{ $block['title'] }}">
                {{ $block['block_type'] }}
            </span>
        @endforeach
    </div>
@endforeach
```

### Example 4: Check Before Creating Booking

```php
// Before allowing customer to book
use App\Services\BookingOverlapValidator;

$validator = app(BookingOverlapValidator::class);

$result = $validator->validateAvailability(
    hoardingId: 1,
    startDate: $request->start_date,
    endDate: $request->end_date
);

if (!$result['available']) {
    $conflicts = $result['conflicts'];
    
    $maintenanceConflicts = $conflicts->where('type', 'maintenance_block');
    
    if ($maintenanceConflicts->isNotEmpty()) {
        return response()->json([
            'error' => 'This hoarding is scheduled for maintenance during your requested dates.',
            'maintenance_blocks' => $maintenanceConflicts->map(function($block) {
                return [
                    'title' => $block['title'],
                    'dates' => $block['start_date'] . ' to ' . $block['end_date'],
                    'reason' => $block['description'],
                ];
            }),
        ], 422);
    }
}

// Proceed with booking...
```

---

## 🎨 Frontend Implementation Guide

### Calendar UI Requirements

1. **Visual Indicators:**
   - Blocked dates should be visually distinct (e.g., striped background, different color)
   - Show maintenance icon/badge on blocked dates
   - Dates should be unselectable/disabled for booking

2. **Tooltips/Details:**
   - Hover over blocked date shows maintenance details
   - Include: title, type, description, dates

3. **Booking Form:**
   - Date picker should disable blocked dates
   - If user attempts to select blocked date, show error message
   - Suggest alternative dates using `findNextAvailableSlot()` from overlap validator

### Sample Vue.js Calendar Component

```vue
<template>
  <div class="calendar">
    <div v-for="date in calendarDates" :key="date.date" 
         :class="getDateClasses(date)"
         @click="selectDate(date)">
      {{ date.day }}
      
      <div v-if="isBlocked(date)" class="maintenance-indicator">
        <i class="icon-maintenance"></i>
        <div class="tooltip">
          <div v-for="block in date.blocks" :key="block.id">
            <strong>{{ block.title }}</strong>
            <p>{{ block.description }}</p>
            <small>{{ block.start_date }} - {{ block.end_date }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      blockedDates: [],
      hoardingId: null,
    }
  },
  
  mounted() {
    this.fetchBlockedDates();
  },
  
  methods: {
    async fetchBlockedDates() {
      const response = await axios.get('/api/v1/maintenance-blocks/check/blocked-dates', {
        params: {
          hoarding_id: this.hoardingId,
          start_date: this.monthStart,
          end_date: this.monthEnd,
        }
      });
      
      this.blockedDates = response.data.data;
    },
    
    isBlocked(date) {
      return this.blockedDates.some(blocked => blocked.date === date.date);
    },
    
    getDateClasses(date) {
      return {
        'calendar-day': true,
        'blocked': this.isBlocked(date),
        'selectable': !this.isBlocked(date),
      };
    },
    
    selectDate(date) {
      if (this.isBlocked(date)) {
        alert('This date is blocked for maintenance');
        return;
      }
      // Proceed with date selection
    }
  }
}
</script>

<style scoped>
.calendar-day.blocked {
  background: repeating-linear-gradient(
    45deg,
    #f0f0f0,
    #f0f0f0 10px,
    #e0e0e0 10px,
    #e0e0e0 20px
  );
  cursor: not-allowed;
  color: #999;
}

.maintenance-indicator {
  position: absolute;
  top: 2px;
  right: 2px;
  color: orange;
}
</style>
```

---

## 🔒 Authorization Matrix

| Role | Create Block | Update Own Block | Update Any Block | Delete Own Block | Delete Any Block | View Blocks |
|------|--------------|------------------|------------------|------------------|------------------|-------------|
| **Admin** | ✅ All hoardings | ✅ | ✅ | ✅ | ✅ | ✅ All |
| **Vendor** | ✅ Own hoardings only | ✅ | ❌ | ✅ | ❌ | ✅ Own |
| **Customer** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## ⚙️ Configuration

No additional configuration needed. The feature integrates automatically with existing:

- **Settings Service:** Uses existing grace period settings
- **Authentication:** Uses Sanctum API authentication
- **Permissions:** Uses existing Spatie role system
- **Database:** Standard Laravel migrations

---

## 🚀 Deployment Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Test API endpoints with Postman/Insomnia
- [ ] Update frontend calendar component to fetch blocked dates
- [ ] Disable blocked dates in booking date pickers
- [ ] Show maintenance indicators in calendar UI
- [ ] Test overlap validator integration
- [ ] Train admin/vendor users on creating blocks
- [ ] Document internal maintenance workflow

---

## 🐛 Troubleshooting

### Issue: Maintenance blocks not preventing bookings

**Solution:** Ensure BookingOverlapValidator is being called before creating bookings. Check:
```php
$result = app(BookingOverlapValidator::class)->validateAvailability(...);
if (!$result['available']) {
    // Reject booking
}
```

### Issue: Vendor cannot create blocks

**Check:**
1. Vendor owns the hoarding: `$hoarding->vendor_id === auth()->id()`
2. User has vendor role: `auth()->user()->hasRole('vendor')`
3. Hoarding exists and is active

### Issue: Overlapping blocks allowed

**Check:**
- Both blocks have status = 'active'
- Date ranges actually overlap (use overlap logic)
- No validation errors bypassed

### Issue: Calendar not showing blocked dates

**Check:**
1. API call includes correct date range
2. Response includes `blocked-dates` array
3. Frontend correctly processes response
4. CSS styles applied for blocked dates

---

## 📈 Future Enhancements

Potential features for future releases:

1. **Recurring Maintenance Schedules**
   - Weekly/monthly/yearly patterns
   - Auto-create blocks based on schedule

2. **Approval Workflow**
   - Vendor creates block, admin approves
   - Prevents unauthorized downtime

3. **Customer Notifications**
   - Auto-notify affected customers
   - Offer alternative dates

4. **Maintenance Reports**
   - Downtime analytics
   - Cost tracking per block
   - Performance metrics

5. **Integration with Booking System**
   - Auto-cancel conflicting bookings with refunds
   - Reschedule bookings to alternative dates

6. **Mobile App Support**
   - Push notifications for upcoming maintenance
   - Mobile-friendly calendar view

---

## 📞 Support

For issues or questions about this feature:

1. Check this documentation first
2. Review test files for usage examples
3. Check API response errors (422, 403, 500)
4. Verify authentication and authorization
5. Contact development team with error logs

---

## ✅ Summary

**What This Feature Does:**

✅ Allows admin/vendors to block dates for maintenance  
✅ Prevents bookings during blocked periods automatically  
✅ Shows blocked dates in booking calendar UI  
✅ Warns about conflicts with existing bookings  
✅ Integrates seamlessly with existing overlap validator  
✅ Tracks maintenance history and statistics  

**Files Created:** 12 files (model, service, controller, requests, routes, tests, docs)  
**API Endpoints:** 11 endpoints (CRUD + utility endpoints)  
**Tests:** 42 tests (service + API + integration)  
**Status:** ✅ Production Ready

---

*Generated for PROMPT 102: Admin Blocking Periods (Maintenance/Repairs)*  
*Integration with PROMPT 101: Booking Overlap Validation Engine*
