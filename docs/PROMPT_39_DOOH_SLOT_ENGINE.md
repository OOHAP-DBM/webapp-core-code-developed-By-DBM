# PROMPT 39: DOOH Slot Rendering Engine

## Table of Contents
1. [Overview](#overview)
2. [Business Problem](#business-problem)
3. [Technical Solution](#technical-solution)
4. [Database Schema](#database-schema)
5. [Architecture](#architecture)
6. [Core Components](#core-components)
7. [Calculation Engine](#calculation-engine)
8. [API Reference](#api-reference)
9. [Usage Examples](#usage-examples)
10. [Common Use Cases](#common-use-cases)
11. [Troubleshooting](#troubleshooting)

---

## Overview

The **DOOH Slot Rendering Engine** is a comprehensive system for managing Digital Out-of-Home (DOOH) advertising slots on digital billboards and LED screens. It provides sophisticated slot frequency calculations, interval management, cost optimization, and real-time booking with looping logic for multi-ad rotation.

### What Problems Does It Solve?

1. **Slot Frequency Management**: Calculate how many times an ad displays per hour/day
2. **Interval Calculation**: Determine time between each ad display
3. **Cost Optimization**: Find optimal frequency within budget constraints
4. **Multi-Ad Rotation**: Manage multiple ads rotating in sequence (looping)
5. **Real-Time Pricing**: Calculate costs based on displays, time, or fixed rates
6. **Availability Tracking**: Prevent double-booking of time slots
7. **ROI Metrics**: Provide CPM, cost per display, and reach estimates

### Key Features

- ✅ Automatic calculation of display frequency and intervals
- ✅ Multiple pricing models (per display, per hour, per day, per month)
- ✅ Prime time slots with multiplier pricing
- ✅ Multi-ad loop rotation with cycle tracking
- ✅ Budget optimization engine
- ✅ Real-time availability checking
- ✅ Daily schedule generation with exact display times
- ✅ ROI and performance metrics (CPM, reach, impressions)
- ✅ Visual booking interface with cost calculator

---

## Business Problem

### Traditional OOH vs DOOH

**Traditional OOH (Static Billboards)**:
- One ad per hoarding for entire booking period
- Simple weekly/monthly pricing
- No rotation, no sharing

**DOOH (Digital Billboards)**:
- Multiple ads can share the same screen
- Ads rotate in loops (e.g., 6 ads, 10 seconds each)
- Pricing based on display frequency, not time alone
- Need to calculate: How many times will my ad show?

### Example Scenario

A customer wants to advertise on a digital billboard:
- **Screen Time**: 8 AM to 8 PM (12 hours)
- **Display Duration**: 10 seconds per ad
- **Frequency**: 6 times per hour
- **Price**: ₹2.50 per display

**Questions to Answer**:
1. How many times will the ad display per day? **→ 72 times**
2. What's the interval between displays? **→ 10 minutes (600 seconds)**
3. What's the total daily cost? **→ ₹180 (72 × 2.50)**
4. If there are 5 other ads in the loop, what's my position? **→ Position 1-6**
5. Can I afford ₹5000/month budget? **→ Need optimization**

This is what PROMPT 39 solves automatically.

---

## Technical Solution

### Architecture Overview

```
Hoarding (Digital Billboard)
    ↓ has many
DOOHSlots (Time Slots)
    ↓ belongs to
Booking
    ↓ tracks
Loop Schedule & Display Metrics
```

### Components Stack

```
User Interface (Blade Views)
    ↓
Controller (DOOHSlotController)
    ↓
Service Layer (DOOHSlotService) ← Calculation Engine
    ↓
Model (DOOHSlot) ← Auto-calculations
    ↓
Database (dooh_slots table)
```

---

## Database Schema

### `dooh_slots` Table

```sql
CREATE TABLE dooh_slots (
    id BIGINT UNSIGNED PRIMARY KEY,
    
    -- Relationships
    hoarding_id BIGINT UNSIGNED NOT NULL,  -- FK to hoardings
    booking_id BIGINT UNSIGNED NULL,       -- FK to bookings (when booked)
    
    -- Slot Configuration
    slot_name VARCHAR(255) NULL,           -- e.g., "Morning Prime"
    start_time TIME NOT NULL,              -- 08:00:00
    end_time TIME NOT NULL,                -- 20:00:00
    duration_seconds INT DEFAULT 10,       -- How long ad shows (10s)
    frequency_per_hour INT DEFAULT 6,      -- Times per hour (6)
    loop_position INT NULL,                -- Position in ad loop (1-6)
    
    -- Calculated Display Metrics (Auto-filled)
    total_daily_displays INT DEFAULT 0,    -- 72 (6 × 12 hours)
    total_hourly_displays INT DEFAULT 0,   -- 6
    interval_seconds DECIMAL(8,2) DEFAULT 0, -- 600 (3600/6)
    
    -- Pricing (Auto-calculated)
    price_per_display DECIMAL(10,2) DEFAULT 0,  -- ₹2.50
    hourly_cost DECIMAL(10,2) DEFAULT 0,        -- ₹15.00 (6 × 2.50)
    daily_cost DECIMAL(10,2) DEFAULT 0,         -- ₹180.00 (72 × 2.50)
    monthly_cost DECIMAL(10,2) DEFAULT 0,       -- ₹5400.00 (180 × 30)
    
    -- Booking Period (When booked)
    start_date DATE NULL,                  -- Booking start
    end_date DATE NULL,                    -- Booking end
    total_booking_days INT NULL,           -- 7 days
    total_booking_cost DECIMAL(12,2) NULL, -- ₹1260 (180 × 7)
    
    -- Status Management
    status ENUM('available', 'booked', 'blocked', 'maintenance'),
    is_active BOOLEAN DEFAULT TRUE,
    is_prime_time BOOLEAN DEFAULT FALSE,   -- Higher pricing
    
    -- Looping Logic
    ads_in_loop INT DEFAULT 1,             -- 6 ads in rotation
    loop_schedule JSON NULL,               -- Detailed schedule
    
    -- Metadata
    metadata JSON NULL,
    notes TEXT NULL,
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

### Key Fields Explained

| Field | Purpose | Example |
|-------|---------|---------|
| `start_time` | Slot begins | 08:00:00 |
| `end_time` | Slot ends | 20:00:00 |
| `duration_seconds` | How long ad shows | 10 seconds |
| `frequency_per_hour` | Shows per hour | 6 times |
| `total_daily_displays` | Total shows per day | 72 (6×12) |
| `interval_seconds` | Gap between shows | 600s (10 min) |
| `price_per_display` | Cost per show | ₹2.50 |
| `daily_cost` | Cost per day | ₹180 |
| `ads_in_loop` | Ads rotating | 6 ads |
| `loop_position` | Position in loop | 1, 2, 3... |

---

## Architecture

### 1. Model Layer: DOOHSlot.php

**Purpose**: Represents a time slot with auto-calculations

**Key Features**:
- Auto-calculates display metrics on create/update
- Auto-calculates costs based on pricing model
- Generates loop schedule automatically
- Prevents modifications when booked

**Auto-Calculation Flow**:
```php
User creates/updates slot
    ↓
boot() method triggers
    ↓
calculateDisplayMetrics()
    → Calculates hours in slot (12 hours)
    → Calculates hourly displays (6)
    → Calculates daily displays (72)
    → Calculates interval (600 seconds)
    → Generates loop_schedule JSON
    ↓
calculateCosts()
    → Calculates per display cost (₹2.50)
    → Calculates hourly cost (₹15)
    → Calculates daily cost (₹180)
    → Calculates monthly cost (₹5400)
    → If booked: calculates total booking cost
    ↓
Slot saved with all calculations
```

**Example Usage**:
```php
use App\Models\DOOHSlot;

// Create slot - calculations happen automatically
$slot = DOOHSlot::create([
    'hoarding_id' => 1,
    'slot_name' => 'Morning Prime',
    'start_time' => '08:00:00',
    'end_time' => '20:00:00',
    'duration_seconds' => 10,
    'frequency_per_hour' => 6,
    'price_per_display' => 2.50,
    'is_prime_time' => true,
]);

// Access calculated values
echo $slot->total_daily_displays;  // 72
echo $slot->interval_seconds;      // 600
echo $slot->daily_cost;            // 180.00
echo $slot->time_range;            // "8:00 AM - 8:00 PM"
echo $slot->frequency_description; // "6 times per hour (every 10 minutes)"
```

### 2. Service Layer: DOOHSlotService.php

**Purpose**: Business logic and complex calculations

**Key Methods**:

#### createSlot()
Creates a slot with automatic calculations
```php
$service = app(DOOHSlotService::class);
$slot = $service->createSlot([
    'hoarding_id' => 1,
    'start_time' => '08:00:00',
    'end_time' => '20:00:00',
    'frequency_per_hour' => 6,
    'price_per_display' => 2.50,
]);
```

#### calculateOptimalFrequency()
Find frequency needed for desired displays
```php
$result = $service->calculateOptimalFrequency(
    desiredDailyDisplays: 100,
    startTime: '06:00:00',
    endTime: '22:00:00'
);

// Returns:
[
    'frequency_per_hour' => 7,
    'interval_seconds' => 514.29,
    'interval_minutes' => 8.57,
    'actual_daily_displays' => 112,
    'hours_in_slot' => 16,
]
```

#### calculatePricing()
Calculate costs using different models
```php
$pricing = $service->calculatePricing([
    'pricing_model' => 'per_display',  // or per_hour, per_day, per_month
    'base_price' => 3.00,
    'frequency_per_hour' => 6,
    'start_time' => '08:00:00',
    'end_time' => '20:00:00',
    'is_prime_time' => true,
    'prime_multiplier' => 1.5,  // 3.00 × 1.5 = 4.50
]);

// Returns:
[
    'price_per_display' => 4.50,
    'hourly_cost' => 27.00,    // 6 displays × 4.50
    'daily_cost' => 324.00,    // 72 displays × 4.50
    'monthly_cost' => 9720.00, // 324 × 30 days
    'total_daily_displays' => 72,
    'pricing_model' => 'per_display',
]
```

#### calculateBookingCost()
Calculate cost for specific date range
```php
$cost = $service->calculateBookingCost(
    $slot,
    Carbon::parse('2025-12-10'),
    Carbon::parse('2025-12-16')  // 7 days
);

// Returns:
[
    'start_date' => '2025-12-10',
    'end_date' => '2025-12-16',
    'total_days' => 7,
    'daily_cost' => 180.00,
    'total_cost' => 1260.00,     // 180 × 7 days
    'total_displays' => 504,     // 72 × 7 days
    'cost_per_display' => 2.50,
    'cpm' => 2500.00,            // Cost Per Mille (per 1000)
]
```

#### generateDailySchedule()
Generate exact display times for a day
```php
$schedule = $service->generateDailySchedule($slot, Carbon::today());

// Returns:
[
    'date' => '2025-12-09',
    'slot' => ['id' => 1, 'name' => 'Morning Prime', 'time_range' => '8:00 AM - 8:00 PM'],
    'total_displays' => 72,
    'schedule' => [
        ['display_number' => 1, 'time' => '08:00:00', 'loop_cycle' => 1, 'position_in_loop' => 1],
        ['display_number' => 2, 'time' => '08:10:00', 'loop_cycle' => 1, 'position_in_loop' => 1],
        // ... 70 more displays
    ]
]
```

#### optimizeForBudget()
Find best frequency within budget
```php
$optimization = $service->optimizeForBudget(
    monthlyBudget: 5000,
    startTime: '08:00:00',
    endTime: '20:00:00',
    pricePerDisplay: 2.50
);

// Returns:
[
    'budget' => ['monthly' => 5000, 'daily' => 166.67],
    'optimized_config' => [
        'frequency_per_hour' => 5,
        'interval_seconds' => 720,
        'daily_displays' => 60,
    ],
    'actual_cost' => ['daily' => 150.00, 'monthly' => 4500.00],
    'savings' => ['daily' => 16.67, 'monthly' => 500.00],
    'utilization' => 90,  // 90% of budget used
]
```

#### calculateMetrics()
Get ROI and performance metrics
```php
$metrics = $service->calculateMetrics(
    $slot,
    Carbon::now(),
    Carbon::now()->addDays(30)
);

// Returns:
[
    'period' => ['start_date' => '2025-12-09', 'end_date' => '2026-01-08', 'total_days' => 30],
    'displays' => ['daily' => 72, 'total' => 2160, 'per_hour' => 6],
    'costs' => [
        'per_display' => 2.50,
        'per_hour' => 15.00,
        'per_day' => 180.00,
        'total' => 5400.00,
        'cpm' => 2500.00,
    ],
    'reach' => [
        'estimated_total_views' => 108000,  // 2160 displays × 50 avg viewers
        'avg_viewers_per_display' => 50,
        'estimated_daily_reach' => 3600,
    ],
    'frequency' => ['per_hour' => 6, 'interval_seconds' => 600, 'interval_minutes' => 10],
]
```

### 3. Trait: HasDOOHSlots

**Purpose**: Add DOOH functionality to Hoarding model

**Usage**:
```php
use App\Traits\HasDOOHSlots;

class Hoarding extends Model
{
    use HasDOOHSlots;
}

// Now hoarding has DOOH methods
$hoarding->isDOOH();                    // Check if digital
$hoarding->createDOOHSlot([...]);      // Create slot
$hoarding->getTotalDailyDisplays();    // Sum of all slots
$hoarding->getSlotOccupancyRate();     // % slots booked
$hoarding->setupDefaultSlots();        // Create 4 default slots
$hoarding->getDOOHStats();             // Statistics
```

### 4. Controller: DOOHSlotController

**Purpose**: HTTP request handling and API endpoints

**Routes**:
```php
// Slot Management
GET    /admin/hoardings/{hoarding}/dooh-slots           → index()
POST   /admin/hoardings/{hoarding}/dooh-slots           → store()
GET    /admin/dooh-slots/{slot}                         → show()
PUT    /admin/dooh-slots/{slot}                         → update()
DELETE /admin/dooh-slots/{slot}                         → destroy()

// Slot Actions
POST   /admin/dooh-slots/{slot}/release                 → release()
POST   /admin/dooh-slots/{slot}/block                   → block()
POST   /admin/dooh-slots/{slot}/maintenance             → maintenance()

// Booking & Calculation APIs
GET    /admin/hoardings/{hoarding}/dooh-slots/booking   → bookingView()
POST   /admin/dooh/calculate-cost                       → calculateCost()
POST   /admin/dooh/book-slots                           → book()
POST   /admin/dooh/calculate-frequency                  → calculateFrequency()
POST   /admin/dooh/optimize-budget                      → optimizeForBudget()
GET    /admin/dooh/roi-calculator                       → roiCalculator()
```

---

## Calculation Engine

### 1. Display Frequency Calculation

**Formula**:
```
total_daily_displays = frequency_per_hour × hours_in_slot
```

**Example**:
```
Slot: 8 AM - 8 PM = 12 hours
Frequency: 6 times per hour
Result: 6 × 12 = 72 displays per day
```

**Code**:
```php
public function calculateDisplayMetrics()
{
    $start = Carbon::parse($this->start_time);
    $end = Carbon::parse($this->end_time);
    $hoursInSlot = $end->diffInHours($start);
    
    $this->total_hourly_displays = $this->frequency_per_hour;
    $this->total_daily_displays = $this->frequency_per_hour * $hoursInSlot;
}
```

### 2. Interval Calculation

**Formula**:
```
interval_seconds = 3600 / frequency_per_hour
```

**Example**:
```
Frequency: 6 times per hour
Interval: 3600 ÷ 6 = 600 seconds (10 minutes)
```

**Meaning**: Your ad displays every 10 minutes throughout the slot.

### 3. Cost Calculation Models

#### Model 1: Per Display Pricing
```
hourly_cost = price_per_display × frequency_per_hour
daily_cost = price_per_display × total_daily_displays
monthly_cost = daily_cost × 30
```

**Example**:
```
Price: ₹2.50 per display
Frequency: 6 per hour
Hours: 12

Hourly: 2.50 × 6 = ₹15
Daily: 2.50 × 72 = ₹180
Monthly: 180 × 30 = ₹5400
```

#### Model 2: Per Hour Pricing
```
daily_cost = hourly_rate × hours_in_slot
price_per_display = hourly_rate / frequency_per_hour
```

#### Model 3: Per Day Pricing
```
price_per_display = daily_rate / total_daily_displays
hourly_cost = daily_rate / hours_in_slot
```

#### Model 4: Per Month Pricing
```
daily_cost = monthly_rate / 30
price_per_display = monthly_rate / (total_daily_displays × 30)
```

### 4. Prime Time Multiplier

```
final_price = base_price × prime_multiplier
```

**Example**:
```
Base: ₹2.50
Prime Multiplier: 1.5
Prime Price: 2.50 × 1.5 = ₹3.75
```

### 5. Loop Calculation

**Scenario**: 6 ads in loop, 10 seconds each

```
Loop Cycle Duration = ads_in_loop × duration_seconds
                    = 6 × 10 = 60 seconds

Ad 1: Displays at 0s, 60s, 120s, 180s...
Ad 2: Displays at 10s, 70s, 130s, 190s...
Ad 3: Displays at 20s, 80s, 140s, 200s...
Ad 4: Displays at 30s, 90s, 150s, 210s...
Ad 5: Displays at 40s, 100s, 160s, 220s...
Ad 6: Displays at 50s, 110s, 170s, 230s...
```

**Code**:
```php
public function generateLoopSchedule()
{
    $schedule = [];
    $currentTime = Carbon::parse($this->start_time);
    $displayNumber = 0;
    
    while ($currentTime->lt($end)) {
        $displayNumber++;
        $loopCycle = ceil($displayNumber / $this->ads_in_loop);
        $positionInLoop = (($displayNumber - 1) % $this->ads_in_loop) + 1;
        
        $schedule[] = [
            'display_number' => $displayNumber,
            'time' => $currentTime->format('H:i:s'),
            'loop_cycle' => $loopCycle,
            'position_in_loop' => $positionInLoop,
        ];
        
        $currentTime->addSeconds($this->interval_seconds);
    }
    
    $this->loop_schedule = $schedule;
}
```

### 6. Budget Optimization

**Goal**: Find maximum frequency within budget

**Algorithm**:
```php
daily_budget = monthly_budget / 30
affordable_displays = floor(daily_budget / price_per_display)
frequency_per_hour = floor(affordable_displays / hours_in_slot)
```

**Example**:
```
Budget: ₹5000/month
Price: ₹2.50/display
Hours: 12

Daily budget: 5000 ÷ 30 = ₹166.67
Affordable displays: 166.67 ÷ 2.50 = 66 displays
Frequency: 66 ÷ 12 = 5 times/hour

Result: Use 5 times/hour to stay within budget
Actual cost: 60 × 2.50 × 30 = ₹4500
Savings: ₹500
```

---

## API Reference

### 1. Calculate Booking Cost

**Endpoint**: `POST /admin/dooh/calculate-cost`

**Request**:
```json
{
    "slot_ids": [1, 2, 3],
    "start_date": "2025-12-10",
    "end_date": "2025-12-16"
}
```

**Response**:
```json
{
    "success": true,
    "total_cost": 3780.00,
    "total_displays": 1512,
    "cost_per_display": 2.50,
    "cpm": 2500.00,
    "slot_details": [
        {
            "slot_id": 1,
            "slot_name": "Morning Prime",
            "time_range": "8:00 AM - 8:00 PM",
            "cost_details": {
                "total_days": 7,
                "daily_cost": 180.00,
                "total_cost": 1260.00,
                "total_displays": 504
            }
        }
    ]
}
```

### 2. Calculate Optimal Frequency

**Endpoint**: `POST /admin/dooh/calculate-frequency`

**Request**:
```json
{
    "desired_daily_displays": 100,
    "start_time": "06:00",
    "end_time": "22:00"
}
```

**Response**:
```json
{
    "success": true,
    "recommendation": {
        "frequency_per_hour": 7,
        "interval_seconds": 514.29,
        "interval_minutes": 8.57,
        "actual_daily_displays": 112,
        "hours_in_slot": 16
    }
}
```

### 3. Optimize for Budget

**Endpoint**: `POST /admin/dooh/optimize-budget`

**Request**:
```json
{
    "monthly_budget": 5000,
    "start_time": "08:00",
    "end_time": "20:00",
    "price_per_display": 2.50
}
```

**Response**:
```json
{
    "success": true,
    "optimization": {
        "budget": {"monthly": 5000, "daily": 166.67},
        "optimized_config": {
            "frequency_per_hour": 5,
            "interval_seconds": 720,
            "interval_minutes": 12,
            "daily_displays": 60
        },
        "actual_cost": {"daily": 150.00, "monthly": 4500.00},
        "savings": {"daily": 16.67, "monthly": 500.00},
        "utilization": 90
    }
}
```

### 4. Check Availability

**Endpoint**: `GET /admin/hoardings/{hoarding}/dooh-slots/availability`

**Query Parameters**:
```
?start_date=2025-12-10
&end_date=2025-12-16
&start_time=08:00
&end_time=20:00
```

**Response**:
```json
{
    "success": true,
    "availability": {
        "total_available": 3,
        "total_booked": 1,
        "total_conflicting": 0,
        "available": [
            {"id": 1, "slot_name": "Morning Prime", "time_range": "8:00 AM - 12:00 PM"},
            {"id": 2, "slot_name": "Afternoon", "time_range": "12:00 PM - 6:00 PM"}
        ],
        "booked": [
            {"id": 3, "slot_name": "Evening Prime", "booking_id": 42}
        ]
    }
}
```

### 5. Get Daily Schedule

**Endpoint**: `GET /admin/dooh-slots/{slot}/schedule?date=2025-12-09`

**Response**:
```json
{
    "success": true,
    "schedule": {
        "date": "2025-12-09",
        "slot": {"id": 1, "name": "Morning Prime"},
        "total_displays": 72,
        "schedule": [
            {
                "display_number": 1,
                "time": "08:00:00",
                "formatted_time": "8:00:00 AM",
                "loop_cycle": 1,
                "position_in_loop": 1,
                "duration_seconds": 10
            }
            // ... more displays
        ]
    }
}
```

---

## Usage Examples

### Example 1: Create Morning Prime Slot

```php
use App\Models\Hoarding;
use App\Services\DOOHSlotService;

$hoarding = Hoarding::find(1);
$service = app(DOOHSlotService::class);

// Create morning prime time slot
$slot = $hoarding->createDOOHSlot([
    'slot_name' => 'Morning Prime',
    'start_time' => '08:00:00',
    'end_time' => '12:00:00',
    'duration_seconds' => 10,
    'frequency_per_hour' => 6,
    'price_per_display' => 3.50,
    'is_prime_time' => true,
    'ads_in_loop' => 6,
]);

echo "Created: {$slot->slot_name}\n";
echo "Daily displays: {$slot->total_daily_displays}\n";
echo "Daily cost: ₹{$slot->daily_cost}\n";
```

### Example 2: Check Slot Availability

```php
$availability = $hoarding->getSlotAvailability(
    Carbon::parse('2025-12-10'),
    Carbon::parse('2025-12-16'),
    '08:00:00',
    '20:00:00'
);

if ($availability['total_available'] > 0) {
    echo "Found {$availability['total_available']} available slots!\n";
    
    foreach ($availability['available'] as $slot) {
        echo "- {$slot->slot_name}: {$slot->time_range}\n";
        echo "  Cost: ₹{$slot->daily_cost}/day\n";
    }
}
```

### Example 3: Book Multiple Slots

```php
use App\Models\Booking;

$booking = Booking::find(42);
$slotIds = [1, 2, 3]; // Morning, Afternoon, Evening

$result = $service->bookSlots($slotIds, $booking);

echo "Booked {$result['total_booked']} slots\n";
echo "Total cost: ₹{$result['total_cost']}\n";

if ($result['total_failed'] > 0) {
    echo "Failed to book {$result['total_failed']} slots:\n";
    foreach ($result['failed'] as $failure) {
        echo "- Slot {$failure['slot_id']}: {$failure['reason']}\n";
    }
}
```

### Example 4: Budget Optimization

```php
// Customer has ₹5000/month budget
$optimization = $service->optimizeForBudget(
    monthlyBudget: 5000,
    startTime: '08:00:00',
    endTime: '20:00:00',
    pricePerDisplay: 2.50
);

echo "To stay within ₹5000/month budget:\n";
echo "- Use {$optimization['optimized_config']['frequency_per_hour']} displays/hour\n";
echo "- Daily displays: {$optimization['optimized_config']['daily_displays']}\n";
echo "- Actual cost: ₹{$optimization['actual_cost']['monthly']}/month\n";
echo "- You'll save: ₹{$optimization['savings']['monthly']}/month\n";
echo "- Budget utilization: {$optimization['utilization']}%\n";
```

### Example 5: Generate Daily Schedule

```php
$slot = DOOHSlot::find(1);
$schedule = $service->generateDailySchedule($slot, Carbon::today());

echo "Schedule for {$schedule['date']}:\n";
echo "Total displays: {$schedule['total_displays']}\n\n";

echo "First 10 display times:\n";
foreach (array_slice($schedule['schedule'], 0, 10) as $display) {
    echo sprintf(
        "#%d: %s (Loop %d, Position %d)\n",
        $display['display_number'],
        $display['formatted_time'],
        $display['loop_cycle'],
        $display['position_in_loop']
    );
}
```

### Example 6: Setup Default Slots for Hoarding

```php
$hoarding = Hoarding::find(1);

// Create 4 default time slots
$slots = $hoarding->setupDefaultSlots();

echo "Created {count($slots)} default slots:\n";
foreach ($slots as $slot) {
    echo "- {$slot->slot_name}: {$slot->time_range}\n";
    echo "  Daily displays: {$slot->total_daily_displays}\n";
    echo "  Daily cost: ₹{$slot->daily_cost}\n";
}
```

---

## Common Use Cases

### Use Case 1: Customer Wants to Know Display Count

**Scenario**: Customer asks "How many times will my ad show?"

**Solution**:
```php
$slot = DOOHSlot::find(1);

echo "Your ad will display:\n";
echo "- {$slot->total_hourly_displays} times per hour\n";
echo "- {$slot->total_daily_displays} times per day\n";
echo "- Every {$slot->interval_seconds / 60} minutes\n";

if ($slot->total_booking_days) {
    echo "- Total displays in your booking: {$slot->total_displays_in_period}\n";
}
```

### Use Case 2: Customer Has Fixed Budget

**Scenario**: "I have ₹10,000 budget. What can I get?"

**Solution**:
```php
$optimization = $service->optimizeForBudget(
    monthlyBudget: 10000,
    startTime: '08:00:00',
    endTime: '20:00:00',
    pricePerDisplay: 2.50
);

echo "With ₹10,000 budget:\n";
echo "- Frequency: {$optimization['optimized_config']['frequency_per_hour']} times/hour\n";
echo "- Daily displays: {$optimization['optimized_config']['daily_displays']}\n";
echo "- Monthly displays: " . ($optimization['optimized_config']['daily_displays'] * 30) . "\n";
echo "- Actual cost: ₹{$optimization['actual_cost']['monthly']}\n";
echo "- You save: ₹{$optimization['savings']['monthly']}\n";
```

### Use Case 3: Admin Needs Occupancy Report

**Scenario**: Check how many slots are utilized

**Solution**:
```php
$hoarding = Hoarding::find(1);
$stats = $hoarding->getDOOHStats();

echo "DOOH Statistics:\n";
echo "- Total slots: {$stats['total_slots']}\n";
echo "- Available: {$stats['available']}\n";
echo "- Booked: {$stats['booked']}\n";
echo "- Occupancy rate: {$stats['occupancy_rate']}%\n";
echo "- Daily display capacity: {$stats['total_daily_displays']}\n";
echo "- Monthly revenue potential: ₹{$stats['total_monthly_revenue_potential']}\n";
echo "- Current monthly revenue: ₹{$stats['total_monthly_revenue_actual']}\n";
```

### Use Case 4: Customer Wants ROI Analysis

**Scenario**: "What's my cost per impression?"

**Solution**:
```php
$slot = DOOHSlot::find(1);
$metrics = $service->calculateMetrics(
    $slot,
    Carbon::parse('2025-12-10'),
    Carbon::parse('2026-01-09')  // 30 days
);

echo "ROI Analysis (30 days):\n";
echo "\nDisplays:\n";
echo "- Total: {$metrics['displays']['total']}\n";
echo "- Per day: {$metrics['displays']['daily']}\n";
echo "- Per hour: {$metrics['displays']['per_hour']}\n";

echo "\nCosts:\n";
echo "- Per display: ₹{$metrics['costs']['per_display']}\n";
echo "- CPM (per 1000): ₹{$metrics['costs']['cpm']}\n";
echo "- Total: ₹{$metrics['costs']['total']}\n";

echo "\nReach:\n";
echo "- Estimated views: {$metrics['reach']['estimated_total_views']}\n";
echo "- Daily reach: {$metrics['reach']['estimated_daily_reach']}\n";
```

### Use Case 5: Prevent Double Booking

**Scenario**: Check if slot is available before booking

**Solution**:
```php
$slot = DOOHSlot::find(1);

if ($slot->is_available) {
    $booking = Booking::find(42);
    
    if ($slot->book($booking)) {
        echo "Slot booked successfully!\n";
        echo "Booking cost: ₹{$slot->total_booking_cost}\n";
    } else {
        echo "Failed to book slot.\n";
    }
} else {
    echo "Slot not available (Status: {$slot->status})\n";
    
    if ($slot->is_booked) {
        echo "Currently booked by Booking #{$slot->booking_id}\n";
        echo "Until: {$slot->end_date->format('M d, Y')}\n";
    }
}
```

---

## Troubleshooting

### Issue 1: Calculations Don't Match Expected

**Problem**: Display count or cost is incorrect

**Debug**:
```php
$slot = DOOHSlot::find(1);

echo "Configuration:\n";
echo "- Time: {$slot->start_time} to {$slot->end_time}\n";
echo "- Hours: {$slot->slot_duration_hours}\n";
echo "- Frequency: {$slot->frequency_per_hour}/hour\n";
echo "- Price: ₹{$slot->price_per_display}/display\n";

echo "\nCalculations:\n";
echo "- Hourly displays: {$slot->frequency_per_hour}\n";
echo "- Daily displays: {$slot->frequency_per_hour} × {$slot->slot_duration_hours} = {$slot->total_daily_displays}\n";
echo "- Daily cost: {$slot->total_daily_displays} × {$slot->price_per_display} = ₹{$slot->daily_cost}\n";

// Force recalculation
$slot->calculateDisplayMetrics();
$slot->calculateCosts();
$slot->save();
```

### Issue 2: Slot Crosses Midnight

**Problem**: End time is before start time (e.g., 22:00 to 02:00)

**Solution**: The model handles this automatically
```php
// This works correctly
$slot = DOOHSlot::create([
    'start_time' => '22:00:00',
    'end_time' => '02:00:00',  // Next day
    // ...
]);

// Hours calculated: 24 - 22 + 2 = 4 hours
echo $slot->slot_duration_hours;  // 4.0
```

### Issue 3: Can't Delete Booked Slot

**Problem**: Trying to delete slot with active booking

**Solution**:
```php
$slot = DOOHSlot::find(1);

if ($slot->status === 'booked') {
    // First release the booking
    $slot->release();
    echo "Booking released\n";
}

// Now can delete
$slot->delete();
```

### Issue 4: Interval Too Short/Long

**Problem**: Ads displaying too frequently or infrequently

**Solution**: Adjust frequency
```php
$slot = DOOHSlot::find(1);

// Current
echo "Current: {$slot->frequency_per_hour} times/hour\n";
echo "Interval: {$slot->interval_seconds} seconds\n";

// Want 12-minute intervals?
// 3600 / 12 minutes = 3600 / 720 = 5 times per hour
$slot->frequency_per_hour = 5;
$slot->save();

echo "\nUpdated: {$slot->frequency_per_hour} times/hour\n";
echo "Interval: {$slot->interval_seconds} seconds (". round($slot->interval_seconds / 60) ." minutes)\n";
```

### Issue 5: Loop Schedule Not Generated

**Problem**: `loop_schedule` is empty

**Solution**: Trigger regeneration
```php
$slot = DOOHSlot::find(1);

// Regenerate loop schedule
$slot->generateLoopSchedule();
$slot->save();

echo "Generated {count($slot->loop_schedule)} display times\n";
```

### Issue 6: Prime Time Not Applying

**Problem**: Prime time multiplier not affecting price

**Solution**: Use service's calculatePricing
```php
$service = app(DOOHSlotService::class);

$pricing = $service->calculatePricing([
    'pricing_model' => 'per_display',
    'base_price' => 2.50,
    'frequency_per_hour' => 6,
    'start_time' => $slot->start_time,
    'end_time' => $slot->end_time,
    'is_prime_time' => true,
    'prime_multiplier' => 1.5,
]);

$slot->update([
    'price_per_display' => $pricing['price_per_display'],
    'is_prime_time' => true,
]);
```

---

## Best Practices

### 1. Always Use Service for Complex Operations

```php
// ❌ Bad: Manual calculations
$slot->daily_cost = $slot->price_per_display * $slot->total_daily_displays;

// ✅ Good: Let service handle it
$service->updateSlot($slot, ['price_per_display' => 3.00]);
```

### 2. Check Availability Before Booking

```php
// ✅ Good
if ($slot->is_available && !$slot->booking_id) {
    $slot->book($booking);
}

// ❌ Bad: Force booking without checking
$slot->booking_id = $booking->id;
$slot->status = 'booked';
```

### 3. Use Scopes for Queries

```php
// ✅ Good: Use scopes
$availableSlots = $hoarding->doohSlots()
    ->available()
    ->primeTime()
    ->get();

// ❌ Bad: Manual filtering
$slots = $hoarding->doohSlots()
    ->where('status', 'available')
    ->where('is_prime_time', true)
    ->whereNull('booking_id')
    ->get();
```

### 4. Leverage Auto-Calculation

```php
// ✅ Good: Let model calculate
$slot = DOOHSlot::create([
    'frequency_per_hour' => 6,
    'start_time' => '08:00:00',
    'end_time' => '20:00:00',
    // daily_cost calculated automatically
]);

// ❌ Bad: Manual calculation
$slot->total_daily_displays = 72;
$slot->daily_cost = 180;
```

### 5. Use Transactions for Multiple Bookings

```php
use Illuminate\Support\Facades\DB;

// ✅ Good
DB::transaction(function() use ($slotIds, $booking) {
    $service->bookSlots($slotIds, $booking);
});

// ❌ Bad: No transaction
foreach ($slotIds as $slotId) {
    $slot = DOOHSlot::find($slotId);
    $slot->book($booking);
}
```

---

## Performance Tips

### 1. Eager Load Relationships

```php
// ✅ Good
$slots = DOOHSlot::with(['hoarding', 'booking'])->get();

foreach ($slots as $slot) {
    echo $slot->hoarding->title;  // No extra query
}

// ❌ Bad: N+1 problem
$slots = DOOHSlot::all();
foreach ($slots as $slot) {
    echo $slot->hoarding->title;  // Extra query per slot!
}
```

### 2. Cache Statistics

```php
use Illuminate\Support\Facades\Cache;

// Cache for 1 hour
$stats = Cache::remember("hoarding_{$hoardingId}_stats", 3600, function() use ($hoardingId) {
    return $service->getHoardingSlotStats($hoardingId);
});
```

### 3. Limit Schedule Generation

```php
// For large slots, limit schedule items
$schedule = $service->generateDailySchedule($slot, Carbon::today());
$previewSchedule = array_slice($schedule['schedule'], 0, 50);  // First 50 only
```

---

## Summary

**PROMPT 39: DOOH Slot Rendering Engine** provides:

✅ **Automatic Calculations**: Display frequency, intervals, costs  
✅ **Flexible Pricing**: Per display, hour, day, or month  
✅ **Budget Optimization**: Find best frequency within budget  
✅ **Loop Management**: Multi-ad rotation with cycle tracking  
✅ **Real-time Availability**: Prevent double bookings  
✅ **ROI Metrics**: CPM, reach, impressions  
✅ **Visual Booking**: Interactive cost calculator  

**Key Files**:
- `app/Models/DOOHSlot.php` - Model with auto-calculations
- `app/Services/DOOHSlotService.php` - Calculation engine
- `app/Traits/HasDOOHSlots.php` - Hoarding integration
- `app/Http/Controllers/Admin/DOOHSlotController.php` - API endpoints

**Next Steps**:
1. Create DOOH hoardings in admin panel
2. Setup default slots or create custom slots
3. Use booking interface for cost calculation
4. Book slots through API or UI
5. Monitor occupancy and revenue

For questions or issues, refer to the test file `test_dooh_slots.php` for working examples.
