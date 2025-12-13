# PROMPT 104: Hoarding Availability API for Frontend Calendar

**Implementation Date:** December 13, 2025  
**Status:** ✅ Complete  
**Related Features:** PROMPT 101 (Overlap Validator), PROMPT 102 (Maintenance Blocks)

---

## 📋 Overview

The Hoarding Availability API provides comprehensive calendar data for frontend visualization, returning date-by-date availability status for hoardings. Perfect for calendar heatmaps, booking interfaces, and availability displays.

### Status Types

- **available** - No conflicts, hoarding can be booked
- **booked** - Has confirmed booking or POS booking
- **blocked** - Maintenance block active (admin/vendor set)
- **hold** - Active payment hold exists
- **partial** - Multiple statuses on same date (e.g., both booking and hold)

### Key Features

✅ Day-by-day availability status  
✅ Calendar heatmap data with color codes  
✅ Month view optimization  
✅ Batch date checking  
✅ Next N available dates finder  
✅ Occupancy rate calculation  
✅ Integration with bookings, holds, maintenance blocks, POS  
✅ Detailed information on demand  
✅ Date range validation (max 2 years)

---

## 🏗️ Architecture

### Components

1. **HoardingAvailabilityService** - Business logic for availability aggregation
2. **HoardingAvailabilityController** - 7 API endpoints for calendar data
3. **FormRequests** - Validation for date ranges and batch checks
4. **Routes** - RESTful endpoints under `/api/v1/hoardings/{id}/availability`

### Data Sources

The service aggregates data from:
- **Booking** model (confirmed, payment_hold statuses)
- **MaintenanceBlock** model (active blocks only)
- **Payment Holds** (pending_payment_hold with valid expiry)
- **POS Bookings** (if module exists)

---

## 📡 API Endpoints

All endpoints require `auth:sanctum` authentication.

### 1. Get Availability Calendar

**Endpoint:** `GET /api/v1/hoardings/{hoarding}/availability/calendar`

Get day-by-day availability for a date range.

**Query Parameters:**
```
start_date (required)    - YYYY-MM-DD format
end_date (required)      - YYYY-MM-DD, must be >= start_date
include_details (optional) - true/false, include booking/block details
```

**Example Request:**
```bash
GET /api/v1/hoardings/123/availability/calendar?start_date=2025-12-20&end_date=2025-12-27&include_details=true
Authorization: Bearer {token}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Availability calendar retrieved successfully",
  "data": {
    "hoarding_id": 123,
    "start_date": "2025-12-20",
    "end_date": "2025-12-27",
    "total_days": 8,
    "summary": {
      "available_days": 4,
      "booked_days": 2,
      "blocked_days": 1,
      "hold_days": 1,
      "partial_days": 0,
      "occupancy_rate": 50.0
    },
    "calendar": [
      {
        "date": "2025-12-20",
        "day_of_week": "Friday",
        "status": "available"
      },
      {
        "date": "2025-12-21",
        "day_of_week": "Saturday",
        "status": "booked",
        "details": {
          "bookings": [
            {
              "id": 456,
              "start_date": "2025-12-21",
              "end_date": "2025-12-23",
              "status": "confirmed",
              "customer_name": "John Doe"
            }
          ],
          "holds": [],
          "blocks": [],
          "pos_bookings": []
        }
      },
      {
        "date": "2025-12-22",
        "day_of_week": "Sunday",
        "status": "booked"
      },
      {
        "date": "2025-12-23",
        "day_of_week": "Monday",
        "status": "blocked",
        "details": {
          "bookings": [],
          "holds": [],
          "blocks": [
            {
              "id": 789,
              "title": "Annual Maintenance",
              "start_date": "2025-12-23",
              "end_date": "2025-12-24",
              "block_type": "maintenance"
            }
          ],
          "pos_bookings": []
        }
      },
      {
        "date": "2025-12-24",
        "day_of_week": "Tuesday",
        "status": "hold",
        "details": {
          "bookings": [],
          "holds": [
            {
              "id": 101,
              "start_date": "2025-12-24",
              "end_date": "2025-12-24",
              "expires_at": "2025-12-20 18:00:00",
              "customer_name": "Jane Smith"
            }
          ],
          "blocks": [],
          "pos_bookings": []
        }
      },
      {
        "date": "2025-12-25",
        "day_of_week": "Wednesday",
        "status": "available"
      },
      {
        "date": "2025-12-26",
        "day_of_week": "Thursday",
        "status": "available"
      },
      {
        "date": "2025-12-27",
        "day_of_week": "Friday",
        "status": "available"
      }
    ]
  }
}
```

**Validation:**
- `start_date` required, YYYY-MM-DD format
- `end_date` required, YYYY-MM-DD, >= start_date
- Maximum range: 730 days (2 years)

---

### 2. Get Availability Summary

**Endpoint:** `GET /api/v1/hoardings/{hoarding}/availability/summary`

Get counts only without full calendar data.

**Query Parameters:**
```
start_date (required) - YYYY-MM-DD format
end_date (required)   - YYYY-MM-DD format
```

**Example Request:**
```bash
GET /api/v1/hoardings/123/availability/summary?start_date=2025-12-20&end_date=2025-12-27
Authorization: Bearer {token}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Availability summary retrieved successfully",
  "data": {
    "hoarding_id": 123,
    "start_date": "2025-12-20",
    "end_date": "2025-12-27",
    "summary": {
      "available_days": 4,
      "booked_days": 2,
      "blocked_days": 1,
      "hold_days": 1,
      "partial_days": 0,
      "occupancy_rate": 50.0
    }
  }
}
```

---

### 3. Get Month Calendar

**Endpoint:** `GET /api/v1/hoardings/{hoarding}/availability/month/{year}/{month}`

Optimized for monthly calendar views.

**Path Parameters:**
```
year  - 2020-2100
month - 1-12
```

**Example Request:**
```bash
GET /api/v1/hoardings/123/availability/month/2025/12
Authorization: Bearer {token}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Month calendar retrieved successfully",
  "data": {
    "hoarding_id": 123,
    "start_date": "2025-12-01",
    "end_date": "2025-12-31",
    "total_days": 31,
    "summary": {
      "available_days": 20,
      "booked_days": 8,
      "blocked_days": 2,
      "hold_days": 1,
      "partial_days": 0,
      "occupancy_rate": 35.48
    },
    "calendar": [
      // ... 31 days of availability data
    ]
  }
}
```

---

### 4. Check Multiple Dates (Batch)

**Endpoint:** `POST /api/v1/hoardings/{hoarding}/availability/check-dates`

Check availability for specific dates in one request.

**Request Body:**
```json
{
  "dates": [
    "2025-12-20",
    "2025-12-25",
    "2025-12-30"
  ]
}
```

**Validation:**
- `dates` array required
- Min: 1 date, Max: 100 dates
- Each date must be YYYY-MM-DD format

**Example Request:**
```bash
POST /api/v1/hoardings/123/availability/check-dates
Authorization: Bearer {token}
Content-Type: application/json

{
  "dates": ["2025-12-20", "2025-12-25", "2025-12-30"]
}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Date availability checked successfully",
  "data": {
    "hoarding_id": 123,
    "requested_dates": [
      "2025-12-20",
      "2025-12-25",
      "2025-12-30"
    ],
    "results": [
      {
        "date": "2025-12-20",
        "day_of_week": "Friday",
        "status": "available",
        "details": {
          "bookings": [],
          "holds": [],
          "blocks": [],
          "pos_bookings": []
        }
      },
      {
        "date": "2025-12-25",
        "day_of_week": "Wednesday",
        "status": "booked",
        "details": {
          "bookings": [
            {
              "id": 456,
              "start_date": "2025-12-25",
              "end_date": "2025-12-27",
              "status": "confirmed",
              "customer_name": "John Doe"
            }
          ],
          "holds": [],
          "blocks": [],
          "pos_bookings": []
        }
      },
      {
        "date": "2025-12-30",
        "day_of_week": "Monday",
        "status": "available",
        "details": {
          "bookings": [],
          "holds": [],
          "blocks": [],
          "pos_bookings": []
        }
      }
    ]
  }
}
```

---

### 5. Get Next Available Dates

**Endpoint:** `GET /api/v1/hoardings/{hoarding}/availability/next-available`

Find the next N available dates for booking.

**Query Parameters:**
```
count (optional)           - Number of dates to find (default: 10, max: 100)
start_from (optional)      - Start searching from this date (default: today)
max_search_days (optional) - Max days to search ahead (default: 365, max: 730)
```

**Example Request:**
```bash
GET /api/v1/hoardings/123/availability/next-available?count=5&start_from=2025-12-20
Authorization: Bearer {token}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Next available dates retrieved successfully",
  "data": {
    "hoarding_id": 123,
    "requested_count": 5,
    "found_count": 5,
    "searched_until": "2025-12-31",
    "dates": [
      {
        "date": "2025-12-20",
        "day_of_week": "Friday",
        "status": "available"
      },
      {
        "date": "2025-12-21",
        "day_of_week": "Saturday",
        "status": "available"
      },
      {
        "date": "2025-12-24",
        "day_of_week": "Tuesday",
        "status": "available"
      },
      {
        "date": "2025-12-27",
        "day_of_week": "Friday",
        "status": "available"
      },
      {
        "date": "2025-12-28",
        "day_of_week": "Saturday",
        "status": "available"
      }
    ]
  }
}
```

---

### 6. Get Heatmap Data

**Endpoint:** `GET /api/v1/hoardings/{hoarding}/availability/heatmap`

Get calendar data with color codes for heatmap visualization.

**Query Parameters:**
```
start_date (required) - YYYY-MM-DD format
end_date (required)   - YYYY-MM-DD format
```

**Color Codes:**
- `available` - #22c55e (green)
- `booked` - #ef4444 (red)
- `blocked` - #6b7280 (gray)
- `hold` - #eab308 (yellow)
- `partial` - #f97316 (orange)

**Example Request:**
```bash
GET /api/v1/hoardings/123/availability/heatmap?start_date=2025-12-20&end_date=2025-12-27
Authorization: Bearer {token}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Heatmap data retrieved successfully",
  "data": {
    "hoarding_id": 123,
    "start_date": "2025-12-20",
    "end_date": "2025-12-27",
    "summary": {
      "available_days": 4,
      "booked_days": 2,
      "blocked_days": 1,
      "hold_days": 1,
      "partial_days": 0,
      "occupancy_rate": 50.0
    },
    "heatmap": [
      {
        "date": "2025-12-20",
        "status": "available",
        "color": "#22c55e",
        "label": "Available"
      },
      {
        "date": "2025-12-21",
        "status": "booked",
        "color": "#ef4444",
        "label": "Booked"
      },
      {
        "date": "2025-12-22",
        "status": "booked",
        "color": "#ef4444",
        "label": "Booked"
      },
      {
        "date": "2025-12-23",
        "status": "blocked",
        "color": "#6b7280",
        "label": "Blocked (Maintenance)"
      },
      {
        "date": "2025-12-24",
        "status": "hold",
        "color": "#eab308",
        "label": "On Hold"
      },
      {
        "date": "2025-12-25",
        "status": "available",
        "color": "#22c55e",
        "label": "Available"
      },
      {
        "date": "2025-12-26",
        "status": "available",
        "color": "#22c55e",
        "label": "Available"
      },
      {
        "date": "2025-12-27",
        "status": "available",
        "color": "#22c55e",
        "label": "Available"
      }
    ]
  }
}
```

---

### 7. Quick Status Check

**Endpoint:** `GET /api/v1/hoardings/{hoarding}/availability/quick-check`

Lightweight check for a single date.

**Query Parameters:**
```
date (required) - YYYY-MM-DD format
```

**Example Request:**
```bash
GET /api/v1/hoardings/123/availability/quick-check?date=2025-12-25
Authorization: Bearer {token}
```

**Example Response:**
```json
{
  "success": true,
  "message": "Quick availability check completed",
  "data": {
    "hoarding_id": 123,
    "date": "2025-12-25",
    "status": "booked",
    "details": {
      "bookings": [
        {
          "id": 456,
          "start_date": "2025-12-25",
          "end_date": "2025-12-27",
          "status": "confirmed",
          "customer_name": "John Doe"
        }
      ],
      "holds": [],
      "blocks": [],
      "pos_bookings": []
    }
  }
}
```

---

## 🧪 Service API

### HoardingAvailabilityService Methods

#### `getAvailabilityCalendar($hoardingId, $startDate, $endDate, $includeDetails = false)`

Get full availability calendar.

**Parameters:**
- `$hoardingId` (int) - Hoarding ID
- `$startDate` (string|Carbon) - Start date
- `$endDate` (string|Carbon) - End date
- `$includeDetails` (bool) - Include booking/block details

**Returns:** Array with calendar data

**Example:**
```php
use App\Services\HoardingAvailabilityService;
use Carbon\Carbon;

$service = app(HoardingAvailabilityService::class);

$calendar = $service->getAvailabilityCalendar(
    123,
    Carbon::today(),
    Carbon::today()->addDays(30),
    true
);

echo "Available days: " . $calendar['summary']['available_days'];
```

---

#### `getAvailabilitySummary($hoardingId, $startDate, $endDate)`

Get summary counts only.

**Returns:** Array with counts and occupancy rate

**Example:**
```php
$summary = $service->getAvailabilitySummary(123, '2025-12-01', '2025-12-31');

// [
//   'available_days' => 20,
//   'booked_days' => 8,
//   'blocked_days' => 2,
//   'hold_days' => 1,
//   'partial_days' => 0,
//   'occupancy_rate' => 35.48
// ]
```

---

#### `getMonthCalendar($hoardingId, $year, $month)`

Get calendar for entire month.

**Parameters:**
- `$hoardingId` (int)
- `$year` (int) - 2020-2100
- `$month` (int) - 1-12

**Example:**
```php
$calendar = $service->getMonthCalendar(123, 2025, 12);
```

---

#### `checkMultipleDates($hoardingId, array $dates)`

Check availability for multiple specific dates.

**Parameters:**
- `$hoardingId` (int)
- `$dates` (array) - Array of date strings ['2025-12-20', '2025-12-25']

**Returns:** Array of date results

**Example:**
```php
$results = $service->checkMultipleDates(123, [
    '2025-12-20',
    '2025-12-25',
    '2025-12-30'
]);

foreach ($results as $day) {
    echo "{$day['date']}: {$day['status']}\n";
}
```

---

#### `getNextAvailableDates($hoardingId, $count = 10, $startFrom = null, $maxSearchDays = 365)`

Find next N available dates.

**Parameters:**
- `$hoardingId` (int)
- `$count` (int) - Number of dates to find (default: 10)
- `$startFrom` (string|Carbon|null) - Start date (default: today)
- `$maxSearchDays` (int) - Max search range (default: 365)

**Returns:** Array with available dates

**Example:**
```php
$nextDates = $service->getNextAvailableDates(
    123,
    5, // Find 5 dates
    '2025-12-20',
    90 // Search next 90 days
);

if ($nextDates['found_count'] > 0) {
    echo "Next available: " . $nextDates['dates'][0]['date'];
}
```

---

## 🎨 Frontend Integration

### React Calendar Heatmap Example

```jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';

function HoardingCalendar({ hoardingId }) {
  const [heatmap, setHeatmap] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadHeatmap();
  }, [hoardingId]);

  const loadHeatmap = async () => {
    try {
      const response = await axios.get(
        `/api/v1/hoardings/${hoardingId}/availability/heatmap`,
        {
          params: {
            start_date: '2025-12-01',
            end_date: '2025-12-31'
          },
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        }
      );
      
      setHeatmap(response.data.data.heatmap);
      setLoading(false);
    } catch (error) {
      console.error('Failed to load calendar:', error);
    }
  };

  if (loading) return <div>Loading calendar...</div>;

  return (
    <div className="calendar-grid">
      {heatmap.map(day => (
        <div
          key={day.date}
          className="calendar-day"
          style={{ backgroundColor: day.color }}
          title={`${day.date}: ${day.label}`}
        >
          {new Date(day.date).getDate()}
        </div>
      ))}
    </div>
  );
}

export default HoardingCalendar;
```

### CSS Styling

```css
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
  max-width: 500px;
}

.calendar-day {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.calendar-day:hover {
  transform: scale(1.1);
}
```

---

### Vue.js Month Calendar

```vue
<template>
  <div class="month-calendar">
    <h3>{{ monthName }} {{ year }}</h3>
    
    <div class="calendar-header">
      <div v-for="day in weekDays" :key="day" class="day-name">
        {{ day }}
      </div>
    </div>
    
    <div class="calendar-body">
      <div
        v-for="day in calendarDays"
        :key="day.date"
        :class="['calendar-cell', `status-${day.status}`]"
        @click="showDetails(day)"
      >
        <span class="date-number">{{ getDayNumber(day.date) }}</span>
        <span class="status-badge">{{ day.status }}</span>
      </div>
    </div>
    
    <div class="legend">
      <div class="legend-item available">Available</div>
      <div class="legend-item booked">Booked</div>
      <div class="legend-item blocked">Blocked</div>
      <div class="legend-item hold">On Hold</div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: ['hoardingId', 'year', 'month'],
  
  data() {
    return {
      calendarDays: [],
      weekDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
      monthName: ''
    };
  },
  
  async mounted() {
    await this.loadMonth();
  },
  
  methods: {
    async loadMonth() {
      try {
        const response = await axios.get(
          `/api/v1/hoardings/${this.hoardingId}/availability/month/${this.year}/${this.month}`,
          {
            headers: {
              Authorization: `Bearer ${this.$store.state.token}`
            }
          }
        );
        
        this.calendarDays = response.data.data.calendar;
        this.monthName = new Date(this.year, this.month - 1).toLocaleString('default', { month: 'long' });
      } catch (error) {
        console.error('Failed to load month:', error);
      }
    },
    
    getDayNumber(date) {
      return new Date(date).getDate();
    },
    
    showDetails(day) {
      this.$emit('day-selected', day);
    }
  }
};
</script>

<style scoped>
.calendar-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
  margin-bottom: 8px;
}

.calendar-body {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 4px;
}

.calendar-cell {
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  padding: 8px;
  min-height: 60px;
  cursor: pointer;
}

.status-available { background-color: #d1fae5; }
.status-booked { background-color: #fee2e2; }
.status-blocked { background-color: #e5e7eb; }
.status-hold { background-color: #fef3c7; }
.status-partial { background-color: #fed7aa; }

.legend {
  display: flex;
  gap: 12px;
  margin-top: 16px;
}

.legend-item {
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 14px;
}

.legend-item.available { background-color: #d1fae5; }
.legend-item.booked { background-color: #fee2e2; }
.legend-item.blocked { background-color: #e5e7eb; }
.legend-item.hold { background-color: #fef3c7; }
</style>
```

---

### Angular Next Available Dates

```typescript
import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';

@Component({
  selector: 'app-next-available',
  template: `
    <div class="next-available">
      <h4>Next Available Dates</h4>
      
      <div class="controls">
        <input
          type="number"
          [(ngModel)]="count"
          min="1"
          max="30"
          placeholder="Number of dates"
        />
        <button (click)="loadNextAvailable()">Find Dates</button>
      </div>
      
      <div *ngIf="loading" class="loading">Searching...</div>
      
      <ul class="date-list" *ngIf="!loading && dates.length > 0">
        <li *ngFor="let day of dates" class="date-item">
          <span class="date">{{ day.date }}</span>
          <span class="day-name">{{ day.day_of_week }}</span>
          <button (click)="selectDate(day)">Book Now</button>
        </li>
      </ul>
      
      <div *ngIf="!loading && dates.length === 0" class="no-dates">
        No available dates found in the next {{ searchedDays }} days
      </div>
    </div>
  `,
  styles: [`
    .date-list {
      list-style: none;
      padding: 0;
    }
    
    .date-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px;
      border: 1px solid #e5e7eb;
      border-radius: 4px;
      margin-bottom: 8px;
    }
    
    .date {
      font-weight: 600;
      color: #1f2937;
    }
    
    .day-name {
      color: #6b7280;
    }
  `]
})
export class NextAvailableComponent implements OnInit {
  hoardingId: number = 123;
  count: number = 10;
  dates: any[] = [];
  loading: boolean = false;
  searchedDays: number = 0;

  constructor(private http: HttpClient) {}

  ngOnInit() {
    this.loadNextAvailable();
  }

  loadNextAvailable() {
    this.loading = true;
    
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('token')}`
    });

    this.http.get(
      `/api/v1/hoardings/${this.hoardingId}/availability/next-available`,
      {
        headers,
        params: { count: this.count.toString() }
      }
    ).subscribe({
      next: (response: any) => {
        this.dates = response.data.dates;
        this.searchedDays = response.data.searched_until;
        this.loading = false;
      },
      error: (error) => {
        console.error('Failed to load dates:', error);
        this.loading = false;
      }
    });
  }

  selectDate(day: any) {
    // Navigate to booking page with selected date
    console.log('Selected date:', day.date);
  }
}
```

---

## 🔍 Status Determination Logic

### Priority System

When multiple conditions exist on the same date, the status is determined as:

1. **Multiple statuses** → `partial`
2. **Single blocking status** → Use that status
3. **No conflicts** → `available`

### Examples

```
Scenario 1: Booking + Hold on same date
Result: "partial"

Scenario 2: Maintenance block only
Result: "blocked"

Scenario 3: Confirmed booking only
Result: "booked"

Scenario 4: Expired hold
Result: "available" (holds ignored)

Scenario 5: Completed maintenance block
Result: "available" (completed blocks ignored)
```

---

## ⚙️ Configuration

### Maximum Range Limits

Edit in `GetAvailabilityCalendarRequest.php`:

```php
public function withValidator($validator): void
{
    $validator->after(function ($validator) {
        if ($this->has('start_date') && $this->has('end_date')) {
            $start = Carbon::parse($this->input('start_date'));
            $end = Carbon::parse($this->input('end_date'));

            // Change this value (currently 730 days = 2 years)
            if ($start->diffInDays($end) > 730) {
                $validator->errors()->add(
                    'end_date',
                    'The date range cannot exceed 2 years (730 days).'
                );
            }
        }
    });
}
```

### Custom Color Codes

Edit in `HoardingAvailabilityController.php`:

```php
$colors = [
    'available' => '#22c55e', // Green
    'booked' => '#ef4444',    // Red
    'blocked' => '#6b7280',   // Gray
    'hold' => '#eab308',      // Yellow
    'partial' => '#f97316',   // Orange
];
```

---

## 🧪 Testing

### Run All Tests

```bash
php artisan test --filter=HoardingAvailability
```

### Service Tests (17 tests)

```bash
php artisan test tests/Feature/HoardingAvailabilityServiceTest.php
```

**Tests:**
- ✅ Returns all available when no conflicts
- ✅ Marks dates as booked
- ✅ Marks dates as blocked
- ✅ Marks dates as hold
- ✅ Marks dates as partial (multiple statuses)
- ✅ Ignores completed/cancelled blocks
- ✅ Includes details when requested
- ✅ Gets month calendar correctly
- ✅ Checks multiple dates in batch
- ✅ Finds next available dates
- ✅ Calculates occupancy rate
- ✅ Ignores expired holds
- ✅ Gets summary without full calendar

### API Tests (20 tests)

```bash
php artisan test tests/Feature/Api/HoardingAvailabilityApiTest.php
```

**Tests:**
- ✅ Requires authentication
- ✅ Validates required fields
- ✅ Validates date format
- ✅ Validates end_date after start_date
- ✅ Validates maximum date range
- ✅ Gets calendar successfully
- ✅ Includes details when requested
- ✅ Gets summary
- ✅ Gets month calendar
- ✅ Validates year and month
- ✅ Checks multiple dates
- ✅ Validates dates array
- ✅ Limits batch check to 100 dates
- ✅ Gets next available dates
- ✅ Validates next available parameters
- ✅ Gets heatmap data
- ✅ Performs quick check
- ✅ Requires date for quick check
- ✅ Calendar reflects all status types

---

## 📊 Performance Optimization

### Database Indexes

Ensure these indexes exist:

```sql
-- Bookings table
CREATE INDEX idx_bookings_hoarding_status_dates 
ON bookings (hoarding_id, status, start_date, end_date);

-- Maintenance blocks table
CREATE INDEX idx_maintenance_blocks_hoarding_status_dates 
ON maintenance_blocks (hoarding_id, status, start_date, end_date);
```

### Caching Strategy

Consider caching calendar data for popular hoardings:

```php
use Illuminate\Support\Facades\Cache;

public function getAvailabilityCalendar($hoardingId, $startDate, $endDate, $includeDetails = false)
{
    if (!$includeDetails) {
        $cacheKey = "hoarding:{$hoardingId}:calendar:{$startDate}:{$endDate}";
        
        return Cache::remember($cacheKey, 300, function () use ($hoardingId, $startDate, $endDate) {
            // ... existing logic
        });
    }
    
    // ... existing logic
}
```

Clear cache when bookings/blocks change:

```php
// In Booking observer
public function created(Booking $booking)
{
    Cache::tags(["hoarding:{$booking->hoarding_id}"])->flush();
}

// In MaintenanceBlock observer
public function created(MaintenanceBlock $block)
{
    Cache::tags(["hoarding:{$block->hoarding_id}"])->flush();
}
```

---

## 🔗 Integration with Other Features

### PROMPT 101: Booking Overlap Validator

The availability API uses the same data sources as the overlap validator:

```php
use App\Services\BookingOverlapValidator;

$validator = app(BookingOverlapValidator::class);

// Check if dates are available before showing calendar
$conflicts = $validator->validateAvailability(
    $hoardingId,
    $startDate,
    $endDate
);

if (!$conflicts['is_available']) {
    // Show which dates have conflicts in calendar
}
```

### PROMPT 102: Maintenance Blocks

Active maintenance blocks automatically appear as `blocked` in the calendar:

```php
// Admin creates maintenance block
MaintenanceBlock::create([
    'hoarding_id' => 123,
    'start_date' => '2025-12-25',
    'end_date' => '2025-12-27',
    'status' => 'active'
]);

// Calendar automatically shows these dates as "blocked"
$calendar = $service->getAvailabilityCalendar(123, '2025-12-01', '2025-12-31');
// Dec 25-27 will have status: "blocked"
```

---

## 🎯 Use Cases

### 1. Booking Interface Calendar

Show customers available dates when booking:

```jsx
function BookingCalendar({ hoardingId, onDateSelect }) {
  const [month, setMonth] = useState(new Date().getMonth() + 1);
  const [year, setYear] = useState(new Date().getFullYear());
  const [calendar, setCalendar] = useState(null);

  useEffect(() => {
    loadMonth();
  }, [month, year]);

  const loadMonth = async () => {
    const response = await axios.get(
      `/api/v1/hoardings/${hoardingId}/availability/month/${year}/${month}`
    );
    setCalendar(response.data.data);
  };

  const handleDateClick = (day) => {
    if (day.status === 'available') {
      onDateSelect(day.date);
    } else {
      alert(`Cannot book: ${day.status}`);
    }
  };

  // Render calendar...
}
```

### 2. Vendor Dashboard Occupancy

Show vendors their hoarding occupancy:

```jsx
function VendorDashboard({ hoardingId }) {
  const [summary, setSummary] = useState(null);

  useEffect(() => {
    loadSummary();
  }, []);

  const loadSummary = async () => {
    const response = await axios.get(
      `/api/v1/hoardings/${hoardingId}/availability/summary`,
      {
        params: {
          start_date: '2025-01-01',
          end_date: '2025-12-31'
        }
      }
    );
    setSummary(response.data.data.summary);
  };

  return (
    <div className="occupancy-stats">
      <h3>2025 Occupancy</h3>
      <div className="stat-card">
        <span>Occupancy Rate:</span>
        <span className="value">{summary?.occupancy_rate}%</span>
      </div>
      <div className="stat-card">
        <span>Booked Days:</span>
        <span className="value">{summary?.booked_days}</span>
      </div>
      <div className="stat-card">
        <span>Available Days:</span>
        <span className="value">{summary?.available_days}</span>
      </div>
    </div>
  );
}
```

### 3. Quick Availability Check

Before starting booking flow:

```jsx
async function checkDateAvailability(hoardingId, date) {
  try {
    const response = await axios.get(
      `/api/v1/hoardings/${hoardingId}/availability/quick-check`,
      { params: { date } }
    );
    
    const { status } = response.data.data;
    
    if (status === 'available') {
      return true;
    } else {
      alert(`Date unavailable: ${status}`);
      return false;
    }
  } catch (error) {
    console.error('Availability check failed:', error);
    return false;
  }
}

// Usage
const canBook = await checkDateAvailability(123, '2025-12-25');
if (canBook) {
  // Proceed to booking
}
```

---

## 📁 File Structure

```
app/
├── Services/
│   └── HoardingAvailabilityService.php (570 lines)
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── HoardingAvailabilityController.php (290 lines)
│   └── Requests/
│       ├── GetAvailabilityCalendarRequest.php (70 lines)
│       └── CheckMultipleDatesRequest.php (50 lines)
routes/
└── api_v1/
    └── hoarding_availability.php (40 lines)
tests/
└── Feature/
    ├── HoardingAvailabilityServiceTest.php (17 tests, 450 lines)
    └── Api/
        └── HoardingAvailabilityApiTest.php (20 tests, 530 lines)
docs/
├── PROMPT_104_AVAILABILITY_CALENDAR_API.md
└── PROMPT_104_IMPLEMENTATION_SUMMARY.md
```

---

## 🚨 Error Handling

### Common Errors

**422 Validation Error:**
```json
{
  "message": "The end date must be after or equal to start date.",
  "errors": {
    "end_date": ["The end date must be after or equal to start date."]
  }
}
```

**404 Not Found:**
```json
{
  "message": "Hoarding not found"
}
```

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

---

## 🎓 Best Practices

### 1. Always Use Date Range Limits

```javascript
// Good: Reasonable range
const startDate = '2025-12-01';
const endDate = '2025-12-31';

// Bad: Too large range
const startDate = '2025-01-01';
const endDate = '2027-12-31'; // Will fail validation
```

### 2. Cache Frontend Results

```javascript
const [calendarCache, setCalendarCache] = useState({});

const loadCalendar = async (month, year) => {
  const cacheKey = `${month}-${year}`;
  
  if (calendarCache[cacheKey]) {
    return calendarCache[cacheKey];
  }
  
  const data = await fetchCalendar(month, year);
  setCalendarCache({ ...calendarCache, [cacheKey]: data });
  return data;
};
```

### 3. Show Loading States

```jsx
{loading ? (
  <Skeleton count={31} />
) : (
  <Calendar data={calendarData} />
)}
```

### 4. Handle Partial Status

```javascript
const getStatusMessage = (status) => {
  switch (status) {
    case 'available':
      return 'Available for booking';
    case 'booked':
      return 'Already booked';
    case 'blocked':
      return 'Maintenance scheduled';
    case 'hold':
      return 'Temporarily on hold';
    case 'partial':
      return 'Multiple bookings/blocks';
    default:
      return 'Status unknown';
  }
};
```

---

## 📈 Future Enhancements

- [ ] **Real-time Updates**: WebSocket integration for live calendar updates
- [ ] **Predictive Pricing**: Show price variations on calendar based on demand
- [ ] **Weather Integration**: Show weather forecast on calendar dates
- [ ] **Event Markers**: Highlight special events, holidays
- [ ] **Recurring Blocks**: Support for recurring maintenance schedules
- [ ] **Export Calendar**: Download as iCal, CSV, PDF
- [ ] **Multi-Hoarding View**: Compare availability across multiple hoardings

---

## 📞 Support

For issues or questions:
- Check the API test files for usage examples
- Review PROMPT_102_MAINTENANCE_BLOCKS.md for block functionality
- Review PROMPT_101_OVERLAP_VALIDATOR.md for availability logic

---

## ✅ Summary

The Hoarding Availability API provides:
- 7 comprehensive endpoints
- 5 status types (available, booked, blocked, hold, partial)
- Calendar heatmap data with color codes
- Batch date checking
- Next available dates finder
- Occupancy analytics
- Full integration with bookings, holds, maintenance blocks, POS
- 37 comprehensive tests
- Frontend integration examples (React, Vue, Angular)

Perfect for building intuitive calendar UIs for customers and vendors! 🎉
