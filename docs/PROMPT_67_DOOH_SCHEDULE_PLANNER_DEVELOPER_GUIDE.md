# DOOH Schedule Planner - Developer Guide
**PROMPT 67 Implementation**  
**Created:** December 11, 2025  
**Version:** 1.0  
**Status:** Production Ready

---

## Table of Contents
1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [Models & Relationships](#models--relationships)
5. [Service Layer](#service-layer)
6. [Controllers & Routes](#controllers--routes)
7. [Availability Validation Algorithm](#availability-validation-algorithm)
8. [File Upload & Processing](#file-upload--processing)
9. [Cost Calculation Engine](#cost-calculation-engine)
10. [API Integration Guide](#api-integration-guide)
11. [Testing Guide](#testing-guide)
12. [Deployment Checklist](#deployment-checklist)

---

## Overview

The DOOH Schedule Planner is a comprehensive system for managing digital creatives and scheduling their playback on DOOH (Digital Out-of-Home) screens.

### Key Features
- **Creative Upload**: Multi-format support (video, image, GIF, HTML5)
- **Schedule Planner**: Time slot management with loop frequency
- **Availability Validation**: Real-time conflict detection and capacity checking
- **Admin Approval**: Two-level approval workflow (creative → schedule)
- **Performance Tracking**: Actual displays vs planned displays
- **Playback Generation**: Minute-by-minute schedule generation
- **Cost Calculation**: Automatic cost calculation based on displays

### Technology Stack
- **Laravel 10.x** - Backend framework
- **MySQL 8.0+** - Database
- **Bootstrap 5** - Frontend UI
- **jQuery** - AJAX interactions
- **FFMpeg** (optional) - Video processing

---

## System Architecture

### Module Structure
```
Modules/DOOH/
├── Controllers/
│   ├── Customer/
│   │   └── DOOHScheduleController.php      (270 lines)
│   └── Admin/
│       └── AdminDOOHScheduleController.php  (360 lines)
├── Models/
│   ├── DOOHCreative.php                     (440 lines)
│   ├── DOOHCreativeSchedule.php             (570 lines)
│   ├── DOOHScreen.php                       (from PROMPT 39)
│   ├── DOOHPackage.php                      (from PROMPT 39)
│   └── DOOHSlot.php                         (from PROMPT 39)
└── Services/
    └── DOOHScheduleService.php              (580 lines)
```

### Data Flow

```
Customer Upload Creative
    ↓
Auto Validation (format, size, duration, resolution)
    ↓
Admin Reviews → Approve/Reject
    ↓
Customer Creates Schedule
    ↓
Availability Check (conflicts, capacity)
    ↓
Admin Reviews Schedule → Approve/Reject
    ↓
Schedule Activation (on start_date)
    ↓
Playback Engine Generates Timeline
    ↓
Track Actual Displays
```

---

## Database Schema

### Table: `dooh_creatives` (108 lines migration)

**Purpose:** Store digital creative assets (videos, images, GIFs)

#### Key Columns:
```sql
-- Ownership & References
customer_id              BIGINT UNSIGNED NOT NULL
booking_id               BIGINT UNSIGNED NULL
dooh_screen_id           BIGINT UNSIGNED NULL

-- Creative Details
creative_name            VARCHAR(255)
creative_type            ENUM('video', 'image', 'html5', 'gif')
description              TEXT

-- File Information
file_path                VARCHAR(500)
file_url                 VARCHAR(500)
original_filename        VARCHAR(255)
mime_type                VARCHAR(100)
file_size_bytes          BIGINT UNSIGNED

-- Media Specifications
resolution               VARCHAR(50)
width_pixels             INT UNSIGNED
height_pixels            INT UNSIGNED
duration_seconds         DECIMAL(10,2)
fps                      INT UNSIGNED
codec                    VARCHAR(50)
bitrate_kbps             INT UNSIGNED

-- Validation
validation_status        ENUM('pending', 'validating', 'approved', 'rejected', 'revision_required')
validation_results       JSON
format_valid             BOOLEAN DEFAULT FALSE
resolution_valid         BOOLEAN DEFAULT FALSE
duration_valid           BOOLEAN DEFAULT FALSE
file_size_valid          BOOLEAN DEFAULT FALSE
content_policy_valid     BOOLEAN DEFAULT FALSE

-- Processing
processing_status        ENUM('pending', 'processing', 'completed', 'failed')
thumbnail_path           VARCHAR(500)
preview_url              VARCHAR(500)

-- Metadata
tags                     JSON
metadata                 JSON

-- Status
status                   ENUM('draft', 'active', 'archived', 'deleted')
schedule_count           INT UNSIGNED DEFAULT 0
```

#### Indexes:
```sql
INDEX idx_customer_id (customer_id)
INDEX idx_booking_id (booking_id)
INDEX idx_screen_id (dooh_screen_id)
INDEX idx_validation_status (validation_status)
INDEX idx_status (status)
INDEX idx_type (creative_type)
```

---

### Table: `dooh_creative_schedules` (145 lines migration)

**Purpose:** Manage creative playback schedules

#### Key Columns:
```sql
-- References
dooh_creative_id         BIGINT UNSIGNED NOT NULL
dooh_screen_id           BIGINT UNSIGNED NOT NULL
booking_id               BIGINT UNSIGNED NULL
customer_id              BIGINT UNSIGNED NOT NULL

-- Schedule Details
schedule_name            VARCHAR(255)
description              TEXT
start_date               DATE NOT NULL
end_date                 DATE NOT NULL
total_days               INT UNSIGNED

-- Time Configuration
time_slots               JSON            -- Array of {start_time, end_time}
daily_start_time         TIME
daily_end_time           TIME

-- Loop Settings
slots_per_loop           INT UNSIGNED
loop_frequency           INT UNSIGNED
position_in_loop         INT UNSIGNED

-- Display Calculations
displays_per_hour        INT UNSIGNED
displays_per_day         INT UNSIGNED
total_displays           INT UNSIGNED

-- Cost
cost_per_display         DECIMAL(10,2)
daily_cost               DECIMAL(10,2)
total_cost               DECIMAL(12,2)

-- Validation
validation_status        ENUM('pending', 'checking', 'approved', 'rejected', 'conflicts')
availability_confirmed   BOOLEAN DEFAULT FALSE
conflict_warnings        JSON

-- Approval Workflow
approved_by              BIGINT UNSIGNED NULL
approved_at              TIMESTAMP NULL
approval_notes           TEXT

-- Status Management
status                   ENUM('draft', 'pending_approval', 'approved', 'active', 'paused', 'completed', 'cancelled', 'expired')
scheduled_start_at       TIMESTAMP NULL
scheduled_end_at         TIMESTAMP NULL
activated_at             TIMESTAMP NULL
completed_at             TIMESTAMP NULL
paused_at                TIMESTAMP NULL
cancellation_reason      TEXT

-- Performance Tracking
actual_displays          INT UNSIGNED DEFAULT 0
completion_rate          DECIMAL(5,2)
daily_stats              JSON            -- {date: {displays, hours}}

-- Recurring Schedule
active_days              JSON            -- [1,2,3,4,5] for Mon-Fri
is_recurring             BOOLEAN DEFAULT FALSE

-- Priority
priority                 INT UNSIGNED DEFAULT 5
```

#### Indexes:
```sql
INDEX idx_creative_id (dooh_creative_id)
INDEX idx_screen_id (dooh_screen_id)
INDEX idx_customer_id (customer_id)
INDEX idx_booking_id (booking_id)
INDEX idx_status (status)
INDEX idx_validation (validation_status)
INDEX idx_dates (start_date, end_date)
```

---

## Models & Relationships

### DOOHCreative Model (440 lines)

#### Constants
```php
// Creative Types
const TYPE_VIDEO = 'video';
const TYPE_IMAGE = 'image';
const TYPE_HTML5 = 'html5';
const TYPE_GIF = 'gif';

// Validation Statuses
const VALIDATION_PENDING = 'pending';
const VALIDATION_VALIDATING = 'validating';
const VALIDATION_APPROVED = 'approved';
const VALIDATION_REJECTED = 'rejected';
const VALIDATION_REVISION_REQUIRED = 'revision_required';

// Processing Statuses
const PROCESSING_PENDING = 'pending';
const PROCESSING_PROCESSING = 'processing';
const PROCESSING_COMPLETED = 'completed';
const PROCESSING_FAILED = 'failed';

// File Validation Rules
const MAX_FILE_SIZE_MB = 500;
const MAX_VIDEO_DURATION = 60;
const MIN_VIDEO_DURATION = 5;
const ALLOWED_VIDEO_FORMATS = ['mp4', 'mov', 'avi', 'webm'];
const ALLOWED_IMAGE_FORMATS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const STANDARD_RESOLUTIONS = [
    '1920x1080', '3840x2160', '1280x720', '2560x1440',
    '1080x1920', '2160x3840', '720x1280'
];
```

#### Relationships
```php
// Belongs To
public function customer(): BelongsTo
public function booking(): BelongsTo
public function doohScreen(): BelongsTo
public function validator(): BelongsTo

// Has Many
public function schedules(): HasMany
public function activeSchedules(): HasMany
```

#### Key Methods

**Validation Methods:**
```php
public function runValidations(?int $screenId = null): bool
    // Runs all validation checks
    // Updates validation flags
    // Sets validation_status

public function validateFormat(): bool
    // Checks file extension and MIME type
    
public function validateFileSize(): bool
    // Checks against MAX_FILE_SIZE_MB (500MB)

public function validateDuration(): bool
    // For videos: 5-60 seconds
    
public function validateResolution(?int $screenId = null): bool
    // Matches screen resolution or standard resolutions
```

**Workflow Methods:**
```php
public function approve(int $validatorId, ?string $notes = null): void
    // Sets validation_status = 'approved'
    // Records validator and timestamp
    
public function reject(string $reason, int $validatorId): void
    // Sets validation_status = 'rejected'
    // Records rejection reason
    
public function canBeScheduled(): bool
    // Checks: approved + processed + active
```

**Helper Methods:**
```php
public function isVideo(): bool
public function isImage(): bool
public function deleteFile(): bool
    // Cleanup storage on deletion
```

#### Boot Events
```php
protected static function boot(): void
{
    parent::boot();
    
    // Auto-delete files when creative is deleted
    static::deleting(function ($creative) {
        $creative->deleteFile();
    });
}
```

---

### DOOHCreativeSchedule Model (570 lines)

#### Constants
```php
// Statuses
const STATUS_DRAFT = 'draft';
const STATUS_PENDING_APPROVAL = 'pending_approval';
const STATUS_APPROVED = 'approved';
const STATUS_ACTIVE = 'active';
const STATUS_PAUSED = 'paused';
const STATUS_COMPLETED = 'completed';
const STATUS_CANCELLED = 'cancelled';
const STATUS_EXPIRED = 'expired';

// Validation Statuses
const VALIDATION_PENDING = 'pending';
const VALIDATION_CHECKING = 'checking';
const VALIDATION_APPROVED = 'approved';
const VALIDATION_REJECTED = 'rejected';
const VALIDATION_CONFLICTS = 'conflicts';
```

#### Scopes
```php
public function scopeActive($query)
    // status = 'active'

public function scopeApproved($query)
    // status IN ('approved', 'active')

public function scopeForScreen($query, $screenId)
    // Filter by screen

public function scopeForCustomer($query, $customerId)
    // Filter by customer

public function scopeInDateRange($query, $startDate, $endDate)
    // Between dates

public function scopeOverlapping($query, $startDate, $endDate, $screenId, $excludeId = null)
    // Find overlapping schedules on same screen
```

#### Calculation Methods

**Display Calculations:**
```php
public function calculateTotalDays(): int
    // Carbon diff + 1
    
public function calculateDisplaysPerDay(): int
    // Sum of (time_slot_hours * displays_per_hour)
    // Respects time_slots JSON array
    
public function calculateTotalDisplays(): int
    // displays_per_day * total_days
    // Filters by active_days (day of week)
    
public function calculateTotalCost(): float
    // total_displays * cost_per_display
    
public function calculateCompletionRate(): float
    // (actual_displays / total_displays) * 100
```

#### Status Checks
```php
public function isCurrentlyRunning(): bool
    // Now between start_date and end_date
    
public function isInCurrentTimeSlot(): bool
    // Now within time_slots array
    
public function isUpcoming(): bool
public function hasExpired(): bool
```

#### Workflow Methods
```php
public function approve(int $approverId, ?string $notes = null): void
    // Sets status = 'approved'
    // Sets scheduled_start_at and scheduled_end_at
    
public function activate(): void
    // Sets status = 'active'
    // Records activated_at
    
public function pause(): void
    // Sets status = 'paused'
    
public function resume(): void
    // Sets status = 'active'
    
public function complete(): void
    // Sets status = 'completed'
    // Calculates completion_rate
    
public function cancel(string $reason): void
    // Sets status = 'cancelled'
    // Records cancellation_reason
```

#### Performance Tracking
```php
public function recordDisplay(): void
    // Increments actual_displays
    // Updates daily_stats JSON
    // Format: {date: {displays: X, hours: [...]}}
    
public function getTimeSlotsForDate(Carbon $date): array
    // Returns time_slots filtered by active_days
    // Respects day of week configuration
```

#### Boot Events
```php
protected static function boot(): void
{
    parent::boot();
    
    // Auto-calculate totals on create/update
    static::creating(function ($schedule) {
        $schedule->total_days = $schedule->calculateTotalDays();
        $schedule->displays_per_day = $schedule->calculateDisplaysPerDay();
        $schedule->total_displays = $schedule->calculateTotalDisplays();
        $schedule->total_cost = $schedule->calculateTotalCost();
    });
    
    static::updating(function ($schedule) {
        if ($schedule->isDirty(['start_date', 'end_date', 'displays_per_hour'])) {
            $schedule->total_days = $schedule->calculateTotalDays();
            $schedule->displays_per_day = $schedule->calculateDisplaysPerDay();
            $schedule->total_displays = $schedule->calculateTotalDisplays();
            $schedule->total_cost = $schedule->calculateTotalCost();
        }
    });
}
```

---

## Service Layer

### DOOHScheduleService (580 lines)

The service layer handles all business logic for creative and schedule management.

#### Creative Upload

```php
public function uploadCreative(
    int $customerId,
    array $fileData,
    ?int $bookingId = null,
    ?int $screenId = null
): DOOHCreative
```

**Process:**
1. Store file to `storage/app/public/dooh_creatives/{customer_id}/`
2. Detect creative type (video/image/gif) from MIME type
3. Extract metadata:
   - Images: Use `getimagesize()` for dimensions
   - Videos: Use FFMpeg/FFProbe for duration, resolution, codec, fps
4. Create `DOOHCreative` record
5. Run automatic validations
6. Queue thumbnail generation (for videos)
7. Return creative instance

**Example:**
```php
$creative = $scheduleService->uploadCreative(
    customerId: $customerId,
    fileData: [
        'file' => $uploadedFile,
        'name' => 'Summer Campaign 2025',
        'description' => 'Promotional video',
        'tags' => ['summer', 'promo']
    ],
    bookingId: $bookingId,
    screenId: $screenId
);
```

---

#### Creative Validation

```php
public function validateCreative(DOOHCreative $creative, ?int $screenId = null): array
```

**Validation Checks:**
1. **Format Validation:**
   - Videos: mp4, mov, avi, webm
   - Images: jpg, jpeg, png, webp, gif

2. **File Size Validation:**
   - Maximum: 500MB

3. **Duration Validation:**
   - Videos: 5-60 seconds
   - Images: N/A (auto-pass)

4. **Resolution Validation:**
   - Match screen resolution (if screenId provided)
   - OR match standard resolutions
   - Standard: 1920x1080, 3840x2160, 1280x720, etc.

**Returns:**
```php
[
    'all_valid' => true/false,
    'format_valid' => true/false,
    'file_size_valid' => true/false,
    'duration_valid' => true/false,
    'resolution_valid' => true/false,
    'errors' => ['error messages']
]
```

---

#### Schedule Creation

```php
public function createSchedule(array $data): DOOHCreativeSchedule
```

**Process:**
1. Validate creative is approved
2. Validate date range (start >= today)
3. Parse time_slots JSON
4. Calculate displays and costs
5. Create schedule record
6. Check availability automatically
7. Update creative schedule_count

**Required Data:**
```php
[
    'creative_id' => 123,
    'dooh_screen_id' => 45,
    'schedule_name' => 'Summer Campaign',
    'start_date' => '2025-12-15',
    'end_date' => '2026-01-15',
    'time_slots' => [
        ['start_time' => '08:00', 'end_time' => '12:00'],
        ['start_time' => '17:00', 'end_time' => '22:00']
    ],
    'displays_per_hour' => 12,
    'priority' => 5,
    'active_days' => [1, 2, 3, 4, 5] // Mon-Fri
]
```

---

#### Availability Validation

```php
public function checkScheduleAvailability(DOOHCreativeSchedule $schedule): array
```

**Algorithm:**

**Step 1: Find Overlapping Schedules**
```php
$overlapping = DOOHCreativeSchedule::where('dooh_screen_id', $schedule->dooh_screen_id)
    ->where('id', '!=', $schedule->id)
    ->where('status', '!=', 'cancelled')
    ->where(function($q) use ($schedule) {
        $q->whereBetween('start_date', [$schedule->start_date, $schedule->end_date])
          ->orWhereBetween('end_date', [$schedule->start_date, $schedule->end_date])
          ->orWhere(function($q) use ($schedule) {
              $q->where('start_date', '<=', $schedule->start_date)
                ->where('end_date', '>=', $schedule->end_date);
          });
    })
    ->get();
```

**Step 2: Check Time Slot Conflicts**
```php
foreach ($overlapping as $existing) {
    if ($this->timeSlotsOverlap($schedule, $existing)) {
        $conflicts[] = [
            'type' => 'time_slot_conflict',
            'schedule_id' => $existing->id,
            'schedule_name' => $existing->schedule_name,
            'severity' => 'high',
            'message' => "Time slots overlap with schedule #{$existing->id}"
        ];
    }
}
```

**Step 3: Check Screen Capacity**
```php
$capacityCheck = $this->checkScreenCapacity($schedule, $overlapping);
if ($capacityCheck['utilization'] > 100) {
    $conflicts[] = [
        'type' => 'capacity_exceeded',
        'severity' => 'high',
        'utilization' => $capacityCheck['utilization'],
        'message' => "Screen capacity exceeded ({$capacityCheck['utilization']}%)"
    ];
}
```

**Returns:**
```php
[
    'available' => true/false,
    'conflicts' => [
        [
            'type' => 'time_slot_conflict',
            'schedule_id' => 123,
            'schedule_name' => 'Existing Campaign',
            'severity' => 'high',
            'message' => 'Time slots overlap'
        ]
    ],
    'warnings' => [
        [
            'type' => 'high_utilization',
            'severity' => 'low',
            'message' => 'Screen utilization at 85%'
        ]
    ],
    'message' => 'Schedule is available',
    'capacity_info' => [
        'utilization' => 75.5,
        'available_slots' => 72
    ]
]
```

---

#### Time Slot Overlap Detection

```php
public function timeSlotsOverlap(
    DOOHCreativeSchedule $schedule1,
    DOOHCreativeSchedule $schedule2
): bool
```

**Algorithm:**
```php
foreach ($schedule1->time_slots as $slot1) {
    foreach ($schedule2->time_slots as $slot2) {
        // Convert to minutes for comparison
        $start1 = strtotime($slot1['start_time']) / 60;
        $end1 = strtotime($slot1['end_time']) / 60;
        $start2 = strtotime($slot2['start_time']) / 60;
        $end2 = strtotime($slot2['end_time']) / 60;
        
        // Check overlap: (start1 < end2) AND (end1 > start2)
        if ($start1 < $end2 && $end1 > $start2) {
            return true;
        }
    }
}
return false;
```

---

#### Screen Capacity Check

```php
public function checkScreenCapacity(
    DOOHCreativeSchedule $newSchedule,
    Collection $existingSchedules
): array
```

**Calculation:**
```php
// Default: 24 hours * 60 minutes / 5 min slot = 288 slots per day
$totalSlotsPerDay = $screen->total_slots_per_day ?? 288;

// Sum displays per day from all schedules
$usedSlots = $existingSchedules->sum('displays_per_day') + $newSchedule->displays_per_day;

// Calculate utilization percentage
$utilization = ($usedSlots / $totalSlotsPerDay) * 100;

return [
    'utilization' => round($utilization, 2),
    'available_slots' => max(0, $totalSlotsPerDay - $usedSlots),
    'total_slots' => $totalSlotsPerDay
];
```

---

#### Playback Schedule Generation

```php
public function generatePlaybackSchedule(string $date, int $screenId): array
```

**Process:**
1. Get all active schedules for screen on date
2. Iterate through day in 5-minute slots (288 slots)
3. For each slot, check which schedules are active:
   - Date is within schedule's start/end dates
   - Time is within schedule's time_slots
   - Day of week matches active_days
4. Build slot-by-slot playback array

**Returns:**
```php
[
    'date' => '2025-12-15',
    'screen_id' => 45,
    'total_slots' => 288,
    'active_schedules' => 5,
    'schedule' => [
        '08:00' => [
            [
                'schedule_id' => 123,
                'creative_id' => 456,
                'creative_name' => 'Summer Campaign',
                'priority' => 5,
                'position' => 1
            ],
            [
                'schedule_id' => 124,
                'creative_id' => 457,
                'creative_name' => 'Winter Sale',
                'priority' => 3,
                'position' => 2
            ]
        ],
        '08:05' => [...],
        // ... 286 more slots
    ]
]
```

---

## Controllers & Routes

### Customer Routes (13 routes)

**Prefix:** `/customer/dooh`  
**Middleware:** `auth, customer`  
**Controller:** `Modules\DOOH\Controllers\Customer\DOOHScheduleController`

#### Creative Management
```php
GET    /customer/dooh/creatives             → creatives()
GET    /customer/dooh/creatives/create      → createCreative()
POST   /customer/dooh/creatives             → storeCreative()
GET    /customer/dooh/creatives/{creative}  → showCreative()
DELETE /customer/dooh/creatives/{creative}  → destroyCreative()
```

#### Schedule Management
```php
GET    /customer/dooh/schedules             → schedules()
GET    /customer/dooh/schedules/create      → createSchedule()
POST   /customer/dooh/schedules             → storeSchedule()
GET    /customer/dooh/schedules/{schedule}  → showSchedule()
POST   /customer/dooh/schedules/{schedule}/cancel → cancelSchedule()
```

#### AJAX Routes
```php
POST   /customer/dooh/check-availability    → checkAvailability()
POST   /customer/dooh/playback-preview      → playbackPreview()
```

---

### Admin Routes (15 routes)

**Prefix:** `/admin/dooh`  
**Middleware:** `auth, role:admin`  
**Controller:** `Modules\DOOH\Controllers\Admin\AdminDOOHScheduleController`

#### Creative Approval
```php
GET    /admin/dooh/creatives                        → creatives()
GET    /admin/dooh/creatives/{creative}             → showCreative()
POST   /admin/dooh/creatives/{creative}/approve     → approveCreative()
POST   /admin/dooh/creatives/{creative}/reject      → rejectCreative()
```

#### Schedule Management
```php
GET    /admin/dooh/schedules                        → schedules()
GET    /admin/dooh/schedules/{schedule}             → showSchedule()
POST   /admin/dooh/schedules/{schedule}/approve     → approveSchedule()
POST   /admin/dooh/schedules/{schedule}/reject      → rejectSchedule()
POST   /admin/dooh/schedules/{schedule}/pause       → pauseSchedule()
POST   /admin/dooh/schedules/{schedule}/resume      → resumeSchedule()
POST   /admin/dooh/schedules/bulk-approve           → bulkApprove()
```

#### Screens & Export
```php
GET    /admin/dooh/screens/{screen}/calendar        → screenCalendar()
GET    /admin/dooh/screens/{screen}/playback        → dailyPlayback()
GET    /admin/dooh/schedules/export                 → exportSchedules()
```

---

## Availability Validation Algorithm

### Overview
The availability validation system prevents double-booking and ensures screen capacity is not exceeded.

### Components

#### 1. Overlapping Schedule Detection
```sql
SELECT * FROM dooh_creative_schedules
WHERE dooh_screen_id = ?
  AND status NOT IN ('cancelled', 'expired')
  AND (
    (start_date BETWEEN ? AND ?)
    OR (end_date BETWEEN ? AND ?)
    OR (start_date <= ? AND end_date >= ?)
  )
```

#### 2. Time Slot Conflict Detection
```php
function hasTimeSlotConflict($slots1, $slots2) {
    foreach ($slots1 as $slot1) {
        foreach ($slots2 as $slot2) {
            if (timeRangesOverlap($slot1, $slot2)) {
                return true;
            }
        }
    }
    return false;
}

function timeRangesOverlap($range1, $range2) {
    // Convert times to minutes
    $start1 = timeToMinutes($range1['start_time']);
    $end1 = timeToMinutes($range1['end_time']);
    $start2 = timeToMinutes($range2['start_time']);
    $end2 = timeToMinutes($range2['end_time']);
    
    // Overlap if: (start1 < end2) AND (end1 > start2)
    return ($start1 < $end2) && ($end1 > $start2);
}
```

#### 3. Screen Capacity Validation
```php
function checkCapacity($screen, $schedules) {
    $totalSlots = $screen->total_slots_per_day ?? 288; // 5-min slots in 24hrs
    
    $usedSlots = 0;
    foreach ($schedules as $schedule) {
        $usedSlots += $schedule->displays_per_day;
    }
    
    $utilization = ($usedSlots / $totalSlots) * 100;
    
    return [
        'utilization' => $utilization,
        'available' => $utilization <= 100,
        'available_slots' => max(0, $totalSlots - $usedSlots)
    ];
}
```

### Conflict Severity Levels

**High Severity:**
- Direct time slot overlap
- Screen capacity exceeded (>100%)
- Same creative already scheduled

**Medium Severity:**
- Screen capacity high (85-100%)
- Adjacent time slots

**Low Severity:**
- Screen capacity moderate (70-85%)
- Different days but same week

---

## File Upload & Processing

### Upload Flow

1. **Client-side validation** (optional):
   ```javascript
   // Max file size check
   if (file.size > 500 * 1024 * 1024) {
       alert('File too large (max 500MB)');
       return;
   }
   
   // File type check
   const validTypes = ['video/mp4', 'video/quicktime', 'image/jpeg', 'image/png'];
   if (!validTypes.includes(file.type)) {
       alert('Invalid file type');
       return;
   }
   ```

2. **Server-side processing**:
   ```php
   // DOOHScheduleController::storeCreative()
   $validated = $request->validate([
       'file' => 'required|file|max:512000|mimes:mp4,mov,avi,webm,jpg,jpeg,png,gif',
       'creative_name' => 'required|string|max:255',
       'dooh_screen_id' => 'nullable|exists:dooh_screens,id'
   ]);
   
   $creative = $this->scheduleService->uploadCreative(
       customerId: auth()->user()->customer_id,
       fileData: [
           'file' => $request->file('file'),
           'name' => $request->creative_name,
           'description' => $request->description,
           'tags' => json_decode($request->tags, true)
       ],
       screenId: $request->dooh_screen_id
   );
   ```

3. **File storage**:
   ```php
   // Store to: storage/app/public/dooh_creatives/{customer_id}/
   $path = $file->store("dooh_creatives/{$customerId}", 'public');
   
   // Generate URL
   $url = Storage::url($path);
   ```

4. **Metadata extraction**:
   ```php
   // For images
   $imageInfo = getimagesize($filePath);
   $width = $imageInfo[0];
   $height = $imageInfo[1];
   $mimeType = $imageInfo['mime'];
   
   // For videos (requires FFMpeg)
   $ffprobe = FFMpeg\FFProbe::create();
   $duration = $ffprobe->format($filePath)->get('duration');
   $video = $ffprobe->streams($filePath)->videos()->first();
   $width = $video->get('width');
   $height = $video->get('height');
   $fps = eval('return ' . $video->get('r_frame_rate') . ';');
   ```

5. **Thumbnail generation** (queued):
   ```php
   // Dispatch job
   GenerateCreativeThumbnail::dispatch($creative);
   
   // Job implementation
   public function handle() {
       if ($this->creative->isVideo()) {
           $ffmpeg = FFMpeg\FFMpeg::create();
           $video = $ffmpeg->open($this->creative->file_path);
           $frame = $video->frame(TimeCode::fromSeconds(1));
           $frame->save($thumbnailPath);
       } else {
           // Copy or resize image
           Image::make($this->creative->file_path)
               ->fit(300, 200)
               ->save($thumbnailPath);
       }
   }
   ```

---

## Cost Calculation Engine

### Formula

```
Total Cost = Total Displays × Cost Per Display

Where:
  Total Displays = Displays Per Day × Total Active Days
  Displays Per Day = Sum(Time Slot Hours × Displays Per Hour)
  Total Active Days = Days in date range filtered by active_days
```

### Implementation

```php
// DOOHCreativeSchedule::calculateTotalDisplays()
public function calculateTotalDisplays(): int
{
    $displaysPerDay = $this->calculateDisplaysPerDay();
    
    // If no active_days specified, use all days
    if (empty($this->active_days)) {
        return $displaysPerDay * $this->total_days;
    }
    
    // Count only active days
    $activeDayCount = 0;
    $start = Carbon::parse($this->start_date);
    $end = Carbon::parse($this->end_date);
    
    while ($start->lte($end)) {
        // Check if day of week is in active_days (1=Mon, 7=Sun)
        if (in_array($start->dayOfWeekIso, $this->active_days)) {
            $activeDayCount++;
        }
        $start->addDay();
    }
    
    return $displaysPerDay * $activeDayCount;
}

// DOOHCreativeSchedule::calculateDisplaysPerDay()
public function calculateDisplaysPerDay(): int
{
    // If 24/7 schedule
    if (empty($this->time_slots)) {
        return $this->displays_per_hour * 24;
    }
    
    // Sum hours from all time slots
    $totalHours = 0;
    foreach ($this->time_slots as $slot) {
        $start = Carbon::parse($slot['start_time']);
        $end = Carbon::parse($slot['end_time']);
        $hours = $end->diffInHours($start);
        $totalHours += $hours;
    }
    
    return $this->displays_per_hour * $totalHours;
}

// DOOHCreativeSchedule::calculateTotalCost()
public function calculateTotalCost(): float
{
    return $this->total_displays * $this->cost_per_display;
}
```

### Pricing Tiers (Example)

```php
// Can be configured based on screen, location, time
function getCostPerDisplay($screen, $schedule) {
    $baseCost = 2.50; // ₹2.50 per display
    
    // Premium location multiplier
    if ($screen->is_premium) {
        $baseCost *= 1.5;
    }
    
    // Peak hours multiplier
    if (hasPeakHours($schedule->time_slots)) {
        $baseCost *= 1.25;
    }
    
    // High priority multiplier
    if ($schedule->priority >= 8) {
        $baseCost *= 1.3;
    }
    
    // Volume discount
    if ($schedule->total_displays > 10000) {
        $baseCost *= 0.9; // 10% discount
    }
    
    return $baseCost;
}
```

---

## API Integration Guide

### REST API Endpoints

#### Check Availability (AJAX)
```javascript
POST /customer/dooh/check-availability

Request:
{
    "dooh_screen_id": 45,
    "start_date": "2025-12-15",
    "end_date": "2026-01-15",
    "time_slots": [
        {"start_time": "08:00", "end_time": "12:00"},
        {"start_time": "17:00", "end_time": "22:00"}
    ],
    "displays_per_hour": 12
}

Response:
{
    "success": true,
    "availability": {
        "available": true,
        "conflicts": [],
        "warnings": [
            {
                "type": "high_utilization",
                "severity": "low",
                "message": "Screen utilization at 85%"
            }
        ],
        "message": "Schedule is available",
        "capacity_info": {
            "utilization": 85.5,
            "available_slots": 43
        }
    }
}
```

#### Playback Preview (AJAX)
```javascript
POST /customer/dooh/playback-preview

Request:
{
    "dooh_screen_id": 45,
    "date": "2025-12-15"
}

Response:
{
    "success": true,
    "playback": {
        "date": "2025-12-15",
        "screen_id": 45,
        "total_slots": 288,
        "active_schedules": 5,
        "schedule": {
            "08:00": [
                {
                    "schedule_id": 123,
                    "creative_id": 456,
                    "creative_name": "Summer Campaign",
                    "priority": 5
                }
            ],
            "08:05": [...],
            ...
        }
    }
}
```

---

## Testing Guide

### Unit Tests

**Test Creative Upload:**
```php
public function test_creative_upload_with_valid_video()
{
    $customer = Customer::factory()->create();
    $screen = DOOHScreen::factory()->create();
    
    $file = UploadedFile::fake()->create('video.mp4', 50000, 'video/mp4');
    
    $creative = $this->scheduleService->uploadCreative(
        customerId: $customer->id,
        fileData: [
            'file' => $file,
            'name' => 'Test Video',
            'description' => 'Test'
        ],
        screenId: $screen->id
    );
    
    $this->assertNotNull($creative->id);
    $this->assertEquals('video', $creative->creative_type);
    $this->assertTrue(Storage::exists($creative->file_path));
}
```

**Test Validation:**
```php
public function test_creative_validation_rejects_oversized_file()
{
    $creative = DOOHCreative::factory()->create([
        'file_size_bytes' => 600 * 1024 * 1024 // 600MB
    ]);
    
    $result = $this->scheduleService->validateCreative($creative);
    
    $this->assertFalse($result['all_valid']);
    $this->assertFalse($result['file_size_valid']);
}
```

**Test Availability:**
```php
public function test_availability_check_detects_conflict()
{
    $screen = DOOHScreen::factory()->create();
    
    // Existing schedule
    $existing = DOOHCreativeSchedule::factory()->create([
        'dooh_screen_id' => $screen->id,
        'start_date' => '2025-12-15',
        'end_date' => '2025-12-20',
        'time_slots' => [['start_time' => '08:00', 'end_time' => '12:00']],
        'status' => 'active'
    ]);
    
    // New conflicting schedule
    $new = DOOHCreativeSchedule::factory()->make([
        'dooh_screen_id' => $screen->id,
        'start_date' => '2025-12-17',
        'end_date' => '2025-12-22',
        'time_slots' => [['start_time' => '10:00', 'end_time' => '14:00']]
    ]);
    
    $result = $this->scheduleService->checkScheduleAvailability($new);
    
    $this->assertFalse($result['available']);
    $this->assertNotEmpty($result['conflicts']);
}
```

### Feature Tests

**Test Schedule Creation Flow:**
```php
public function test_customer_can_create_schedule()
{
    $customer = $this->actingAsCustomer();
    
    $creative = DOOHCreative::factory()->create([
        'customer_id' => $customer->id,
        'validation_status' => 'approved'
    ]);
    
    $screen = DOOHScreen::factory()->create();
    
    $response = $this->post(route('customer.dooh.schedules.store'), [
        'creative_id' => $creative->id,
        'dooh_screen_id' => $screen->id,
        'schedule_name' => 'Test Campaign',
        'start_date' => now()->addDays(1)->format('Y-m-d'),
        'end_date' => now()->addDays(30)->format('Y-m-d'),
        'displays_per_hour' => 12,
        'time_slots' => [
            ['start_time' => '08:00', 'end_time' => '20:00']
        ]
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('dooh_creative_schedules', [
        'schedule_name' => 'Test Campaign',
        'status' => 'pending_approval'
    ]);
}
```

---

## Deployment Checklist

### Pre-Deployment

- [ ] **Database Migrations**
  ```bash
  php artisan migrate
  ```

- [ ] **Storage Directory**
  ```bash
  mkdir -p storage/app/public/dooh_creatives
  php artisan storage:link
  ```

- [ ] **Install FFMpeg** (optional, for video processing)
  ```bash
  # Ubuntu/Debian
  sudo apt-get install ffmpeg
  
  # macOS
  brew install ffmpeg
  
  # Windows
  # Download from https://ffmpeg.org/download.html
  ```

- [ ] **Configure Queue Worker**
  ```bash
  # For thumbnail generation
  php artisan queue:work --queue=creatives
  ```

- [ ] **Set Permissions**
  ```bash
  chmod -R 775 storage/app/public/dooh_creatives
  chown -R www-data:www-data storage/app/public/dooh_creatives
  ```

### Configuration

**`.env` Settings:**
```env
# File Upload
DOOH_MAX_FILE_SIZE=512000  # KB (500MB)
DOOH_ALLOWED_VIDEO_FORMATS=mp4,mov,avi,webm
DOOH_ALLOWED_IMAGE_FORMATS=jpg,jpeg,png,webp,gif

# Validation
DOOH_MIN_VIDEO_DURATION=5
DOOH_MAX_VIDEO_DURATION=60
DOOH_AUTO_VALIDATE=true

# Costs
DOOH_BASE_COST_PER_DISPLAY=2.50
DOOH_PREMIUM_MULTIPLIER=1.5
DOOH_PEAK_HOURS_MULTIPLIER=1.25

# Capacity
DOOH_DEFAULT_SLOTS_PER_DAY=288
DOOH_MAX_UTILIZATION=100
```

### Post-Deployment

- [ ] **Test Creative Upload**
  - Upload video file
  - Upload image file
  - Verify storage location
  - Check thumbnail generation

- [ ] **Test Schedule Creation**
  - Create schedule
  - Check availability validation
  - Verify cost calculation

- [ ] **Test Admin Approval**
  - Approve creative
  - Approve schedule
  - Check status updates

- [ ] **Monitor Performance**
  - Check queue processing
  - Monitor storage usage
  - Verify playback generation

---

## Troubleshooting

### Common Issues

**1. File Upload Fails**
```
Error: The file "example.mp4" was not uploaded.

Solutions:
- Check upload_max_filesize in php.ini (must be >= 512M)
- Check post_max_size in php.ini (must be >= 512M)
- Verify storage directory permissions
- Check available disk space
```

**2. Video Metadata Extraction Fails**
```
Error: Unable to extract video metadata

Solutions:
- Install FFMpeg: sudo apt-get install ffmpeg
- Verify FFMpeg is in PATH: which ffmpeg
- Check file permissions
- Verify video file is not corrupted
```

**3. Availability Check Returns False Positives**
```
Error: Conflict detected but schedules don't overlap

Solutions:
- Check time_slots JSON format
- Verify timezone settings
- Check active_days array
- Debug with: dd($schedule->time_slots)
```

**4. Cost Calculation Incorrect**
```
Error: Total cost doesn't match expected value

Solutions:
- Verify displays_per_hour value
- Check time_slots array (hours calculation)
- Verify active_days filter
- Debug: dd($schedule->calculateTotalDisplays())
```

---

## Performance Optimization

### Database Indexes
```sql
-- Already created in migrations
CREATE INDEX idx_creative_customer ON dooh_creatives(customer_id);
CREATE INDEX idx_schedule_screen_dates ON dooh_creative_schedules(dooh_screen_id, start_date, end_date);
CREATE INDEX idx_schedule_status ON dooh_creative_schedules(status);
```

### Caching Strategies
```php
// Cache screen schedules
Cache::remember("screen_{$screenId}_schedules_{$date}", 3600, function() {
    return $this->generatePlaybackSchedule($date, $screenId);
});

// Cache availability results
Cache::remember("availability_{$scheduleId}", 300, function() {
    return $this->checkScheduleAvailability($schedule);
});
```

### Query Optimization
```php
// Eager load relationships
$schedules = DOOHCreativeSchedule::with(['creative', 'doohScreen', 'customer'])
    ->forScreen($screenId)
    ->active()
    ->get();

// Use select to limit columns
$creatives = DOOHCreative::select(['id', 'creative_name', 'creative_type', 'thumbnail_path'])
    ->where('customer_id', $customerId)
    ->approved()
    ->get();
```

---

## Appendix

### File Structure Summary
```
Total Files Created: 7
Total Lines of Code: 2,500+

Migrations:     2 files (253 lines)
Models:         2 files (1,010 lines)
Services:       1 file (580 lines)
Controllers:    2 files (630 lines)
Views:          1 file (400+ lines)
Routes:         28 routes added
```

### API Response Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request (validation failed)
- `403` - Forbidden (not authorized)
- `404` - Not Found
- `409` - Conflict (availability issue)
- `422` - Unprocessable Entity (validation errors)
- `500` - Server Error

### Status Flow Diagrams

**Creative Status Flow:**
```
draft → validating → approved → active
                   ↓
                rejected
```

**Schedule Status Flow:**
```
draft → pending_approval → approved → active → completed
                         ↓            ↓
                      rejected     paused → active
                                     ↓
                                 cancelled
```

---

**End of Developer Guide**
