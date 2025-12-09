# OOH Booking Platform - Developer Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Getting Started](#getting-started)
3. [Architecture Guide](#architecture-guide)
4. [Feature Modules](#feature-modules)
5. [Database Schema](#database-schema)
6. [API Reference](#api-reference)
7. [Common Tasks](#common-tasks)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## System Overview

### What is this Platform?
This is an **Out-of-Home (OOH) Advertising Booking Platform** built with Laravel 10.x. It connects:
- **Customers** who want to advertise on hoardings/billboards
- **Vendors** who own and manage hoardings
- **Admin** who manages the entire platform

### Core Workflow
```
Customer Creates Enquiry 
    → Vendor Submits Offer 
        → Admin Generates Quotation 
            → Customer Makes Payment 
                → Production (Graphics → Printing → Mounting) 
                    → Campaign Goes Live 
                        → Proof of Display 
                            → Campaign Completes
```

### Technology Stack
- **Backend**: Laravel 10.x (PHP 8.1+)
- **Database**: SQLite (development), MySQL (production)
- **Frontend**: Blade Templates, Bootstrap 5, JavaScript
- **Authentication**: Laravel Breeze with Spatie Roles & Permissions
- **Payment**: Razorpay Integration
- **Maps**: Google Maps API for geolocation

---

## Getting Started

### Prerequisites
```bash
# Required software
- PHP 8.1 or higher
- Composer
- Node.js & NPM
- SQLite (for development)
```

### Installation Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd oohApp_Version3
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
```env
# For SQLite (recommended for development)
DB_CONNECTION=sqlite

# For MySQL (production)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oohapp_version3_db
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Seed database (optional)**
```bash
php artisan db:seed
```

7. **Start development server**
```bash
php artisan serve
# Visit: http://localhost:8000
```

### Project Structure
```
oohApp_Version3/
├── app/
│   ├── Http/Controllers/     # Request handlers
│   │   ├── Admin/           # Admin panel controllers
│   │   ├── Vendor/          # Vendor panel controllers
│   │   └── Customer/        # Customer panel controllers
│   ├── Models/              # Database models
│   ├── Services/            # Business logic services
│   └── Traits/              # Reusable model traits
├── database/
│   ├── migrations/          # Database schema definitions
│   ├── seeders/            # Sample data generators
│   └── factories/          # Model factories for testing
├── resources/
│   ├── views/              # Blade templates
│   │   ├── admin/         # Admin UI
│   │   ├── vendor/        # Vendor UI
│   │   └── customer/      # Customer UI
│   └── js/                # JavaScript files
├── routes/
│   └── web.php            # Application routes
├── public/                # Public assets
├── storage/              # File storage
└── tests/                # Automated tests
```

---

## Architecture Guide

### MVC Pattern in This Project

**Models** (`app/Models/`)
- Represent database tables
- Define relationships between entities
- Include business logic specific to that entity

**Controllers** (`app/Http/Controllers/`)
- Handle HTTP requests
- Validate input
- Call services for business logic
- Return views or JSON responses

**Views** (`resources/views/`)
- Blade templates for HTML
- Display data passed from controllers
- Include forms and user interactions

### Service Layer Pattern

**Why Services?** 
Services contain complex business logic that doesn't belong in controllers or models.

**Example**: `BookingTimelineService`
```php
// Good: Using a service
public function create(Request $request) {
    $booking = Booking::create($request->validated());
    
    // Service handles complex timeline generation
    $timelineService = app(BookingTimelineService::class);
    $timelineService->generateFullTimeline($booking);
    
    return redirect()->route('bookings.show', $booking);
}

// Bad: Putting complex logic in controller
public function create(Request $request) {
    $booking = Booking::create($request->validated());
    
    // Too much logic in controller! Move to service
    BookingTimelineEvent::create([...]);
    BookingTimelineEvent::create([...]);
    // ... 50 more lines
}
```

### Trait Pattern

**What are Traits?**
Traits are reusable code blocks that can be added to multiple models.

**Common Traits in This Project:**

1. **HasSnapshots** - Tracks all changes to a model
2. **Auditable** - Creates audit logs for who changed what
3. **HasTimeline** - Manages booking timeline events

**Example Usage:**
```php
use App\Traits\HasSnapshots;
use App\Traits\Auditable;

class Booking extends Model
{
    use HasSnapshots, Auditable;  // Just add traits!
    
    // Now this model automatically:
    // - Creates snapshots on every change
    // - Logs all actions in audit trail
}
```

---

## Feature Modules

### PROMPT 33: Payment Settlement Engine

**Purpose**: Manages vendor payouts and commission calculations

**Key Files:**
- `app/Models/SettlementBatch.php` - Batch payout records
- `app/Models/VendorLedger.php` - Individual vendor transactions
- `app/Services/SettlementService.php` - Settlement logic
- `routes/web.php` - Lines 270-290 (settlement routes)

**How to Use:**
```php
// Create a settlement batch
$settlementService = app(SettlementService::class);
$batch = $settlementService->createBatch([
    'vendor_ids' => [1, 2, 3],
    'from_date' => '2025-01-01',
    'to_date' => '2025-01-31',
]);

// Process payout
$settlementService->processPayout($batch);
```

**Common Tasks:**
- View pending settlements: `/admin/settlements`
- Create new batch: Click "Create Settlement Batch" button
- Process payment: Click "Mark as Paid" on batch detail page

---

### PROMPT 34: Notification Template System

**Purpose**: Manages email/SMS templates with variables

**Key Files:**
- `app/Models/NotificationTemplate.php` - Template storage
- `app/Models/NotificationLog.php` - Sent notification history
- `app/Services/NotificationService.php` - Sending logic
- `resources/views/admin/notification-templates/` - Template management UI

**How to Use:**
```php
// Send a notification using template
$notificationService = app(NotificationService::class);
$notificationService->sendFromTemplate(
    'booking_confirmed',  // template slug
    $user,               // recipient
    [                    // variables
        'booking_id' => $booking->id,
        'amount' => $booking->total_amount,
    ]
);
```

**Template Variables:**
Use these placeholders in templates:
- `{customer_name}` - Customer's name
- `{booking_id}` - Booking ID
- `{amount}` - Amount
- `{hoarding_title}` - Hoarding name
- `{start_date}` - Campaign start date
- `{end_date}` - Campaign end date

**Common Tasks:**
- Create template: `/admin/notification-templates/create`
- Edit template: `/admin/notification-templates/{id}/edit`
- View sent logs: `/admin/notification-logs`
- Retry failed: Click "Retry" on failed notification

---

### PROMPT 35: Geofencing + Map-Based Search

**Purpose**: Search hoardings by location and radius

**Key Files:**
- `app/Http/Controllers/Customer/HoardingSearchController.php`
- `app/Models/Hoarding.php` - Contains geolocation queries
- `resources/views/customer/hoardings/search.blade.php` - Map UI

**How to Use:**
```php
// Search hoardings by location
$hoardings = Hoarding::nearLocation(
    $latitude,
    $longitude,
    $radiusKm  // Default: 5km
)->available()->get();

// Search with filters
$hoardings = Hoarding::nearLocation(28.6139, 77.2090, 10)
    ->where('hoarding_type', 'billboard')
    ->where('status', 'active')
    ->whereBetween('weekly_price', [10000, 50000])
    ->get();
```

**Geofencing Logic:**
```sql
-- Haversine formula for distance calculation
SELECT *,
    (6371 * acos(
        cos(radians(?)) * cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) * sin(radians(latitude))
    )) AS distance
FROM hoardings
HAVING distance < ?
ORDER BY distance
```

**Common Tasks:**
- Add geolocation to hoarding: Edit hoarding, add lat/long
- Search by map: `/customer/hoardings/search`
- Adjust search radius: Use radius slider on search page

---

### PROMPT 36: Snapshot Engine

**Purpose**: Immutable versioning system - captures complete state of entities at points in time

**Key Files:**
- `database/migrations/2025_12_09_095330_create_snapshots_table.php`
- `app/Models/Snapshot.php` - Immutable snapshot records
- `app/Services/SnapshotService.php` - Snapshot creation logic
- `app/Traits/HasSnapshots.php` - Auto-snapshot trait

**How It Works:**

1. **Automatic Snapshots** (via trait):
```php
class Offer extends Model {
    use HasSnapshots;  // Automatically creates snapshots!
    
    protected $snapshotType = 'offer';
    protected $snapshotOnCreate = true;   // Snapshot on creation
    protected $snapshotOnUpdate = true;   // Snapshot on update
}

// Now whenever you do this:
$offer->update(['total_price' => 50000]);

// A snapshot is automatically created with:
// - Complete offer data (before state)
// - Changes (old price → new price)
// - Version number (incremented)
// - User who made change
// - Timestamp
```

2. **Manual Snapshots**:
```php
// Create snapshot manually
$snapshotService = app(SnapshotService::class);
$snapshot = $snapshotService->snapshotOffer(
    $offer,
    'price_updated',
    ['total_price' => ['old' => 40000, 'new' => 50000]]
);

// Or use trait method
$offer->createSnapshot('custom_event', $changes, $metadata);
```

3. **View History**:
```php
// Get all snapshots for an entity
$snapshots = $offer->snapshots;  // Returns collection ordered by version

// Get specific version
$snapshot = $offer->getSnapshotVersion(2);  // Version 2

// Compare versions
$differences = $offer->compareWithVersion(2);

// Restore to previous version
$snapshot->restore();  // Creates new snapshot with old data
```

**Snapshot Data Structure:**
```php
[
    'id' => 1,
    'snapshotable_type' => 'App\Models\Offer',
    'snapshotable_id' => 123,
    'snapshot_type' => 'offer',
    'event' => 'updated',
    'version' => 3,
    
    // Complete state at this point in time
    'data' => [
        'id' => 123,
        'total_price' => 50000,
        'status' => 'approved',
        // ... all fields
    ],
    
    // What changed
    'changes' => [
        'total_price' => ['old' => 40000, 'new' => 50000]
    ],
    
    // Additional context
    'metadata' => [
        'change_percentage' => 25.0,
        'reason' => 'Customer negotiation'
    ],
    
    'created_by' => 1,
    'ip_address' => '127.0.0.1',
    'created_at' => '2025-12-09 10:30:00'
]
```

**Special Features:**

- **Price Change Detection**: Automatically calculates amount and percentage
```php
// When price changes from 40000 to 50000:
$snapshot->metadata['change_amount'] = 10000;
$snapshot->metadata['change_percentage'] = 25.0;
```

- **Status Change Detection**: Special event type
```php
// When status changes:
$snapshot->event = 'status_changed';
$snapshot->metadata['old_status'] = 'pending';
$snapshot->metadata['new_status'] = 'approved';
```

- **Immutability**: Snapshots cannot be modified or deleted
```php
$snapshot->update(['data' => 'new data']);
// Throws: "Snapshots are immutable and cannot be updated."

$snapshot->delete();
// Throws: "Snapshots are immutable and cannot be deleted."
```

**Admin UI:**
```
/admin/snapshots              - List all snapshots
/admin/snapshots/{id}         - View specific snapshot with comparison
/admin/snapshots/statistics   - Stats (total, by type, by event)
/admin/snapshots/type/{type}  - Filter by type
/admin/snapshots/compare      - Compare two snapshots
```

**Common Tasks:**

1. **Add snapshots to new model**:
```php
use App\Traits\HasSnapshots;

class YourModel extends Model {
    use HasSnapshots;
    
    protected $snapshotType = 'your_model';
    protected $snapshotOnCreate = true;
    protected $snapshotOnUpdate = true;
}
```

2. **View model history**:
```php
$history = $model->getSnapshotHistory();
foreach ($history as $snapshot) {
    echo "Version {$snapshot->version} - {$snapshot->event}";
}
```

3. **Restore previous version**:
```php
$snapshot = $model->getSnapshotVersion(2);
$snapshot->restore();  // Model restored to version 2 state
```

**When to Use:**
- ✓ Critical data that needs full history (prices, terms, status)
- ✓ Compliance requirements (audit trail)
- ✓ Undo/restore functionality
- ✓ Version comparison
- ✗ High-frequency changes (use audit logs instead)
- ✗ Large binary data (too much storage)

---

### PROMPT 37: Audit Trail + Logs

**Purpose**: Complete activity logging with who, what, when, where

**Key Files:**
- `database/migrations/2025_12_09_101555_create_audit_logs_table.php`
- `app/Models/AuditLog.php` - Audit log records
- `app/Services/AuditService.php` - Logging service
- `app/Traits/Auditable.php` - Auto-logging trait

**How It Works:**

1. **Automatic Audit Logs** (via trait):
```php
class Booking extends Model {
    use Auditable;  // Automatically logs changes!
    
    protected $auditModule = 'booking';
    protected $priceFields = ['total_amount'];  // Special tracking
}

// Now whenever you do this:
$booking->update(['status' => 'confirmed']);

// An audit log is automatically created with:
// - Who: User name, email, type (admin/vendor/customer)
// - What: Action (updated), description, changed fields
// - When: Exact timestamp
// - Where: IP address, user agent, request URL
// - Before/After: Old and new values
```

2. **Audit Log Data Structure:**
```php
[
    'id' => 1,
    'auditable_type' => 'App\Models\Booking',
    'auditable_id' => 123,
    
    // Who made the change
    'user_id' => 5,
    'user_name' => 'John Admin',
    'user_email' => 'john@example.com',
    'user_type' => 'admin',
    
    // What changed
    'action' => 'updated',  // created, updated, deleted, restored, status_changed, price_changed
    'description' => 'Updated Booking: Status',
    'old_values' => ['status' => 'pending'],
    'new_values' => ['status' => 'confirmed'],
    'changed_fields' => ['status'],
    
    // When (timestamp)
    'created_at' => '2025-12-09 10:30:00',
    
    // Where (location/context)
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'request_method' => 'PUT',
    'request_url' => 'https://example.com/admin/bookings/123',
    
    // Categorization
    'module' => 'booking',
    'metadata' => ['additional' => 'context'],
]
```

3. **Special Tracking:**

**Price Changes:**
```php
// When price changes, automatically calculates:
$booking->update(['total_amount' => 50000]);

// Audit log includes:
[
    'action' => 'price_changed',
    'old_values' => ['price' => 40000],
    'new_values' => ['price' => 50000],
    'metadata' => [
        'change_amount' => 10000,
        'change_percentage' => 25.0
    ]
]
```

**Status Changes:**
```php
// Status changes get special handling:
$booking->update(['status' => 'confirmed']);

// Audit log:
[
    'action' => 'status_changed',
    'description' => 'Changed status from pending to confirmed',
    'metadata' => [
        'old_status' => 'pending',
        'new_status' => 'confirmed'
    ]
]
```

4. **Manual Audit Logging:**
```php
// Using service
$auditService = app(AuditService::class);
$auditService->log($model, 'custom_action', $oldValues, $newValues, [
    'description' => 'Custom event description',
    'module' => 'custom_module',
]);

// Using trait method
$booking->audit('custom_action', $oldValues, $newValues, [
    'description' => 'Custom event'
]);
```

5. **Querying Audit Logs:**
```php
// Get all logs for a model
$logs = $booking->auditLogs;

// Get latest log
$latestLog = $booking->getLatestAuditLog();

// Get logs by action
$createdLogs = $booking->getAuditLogsByAction('created');

// Search with filters
$logs = AuditLog::where('action', 'updated')
    ->where('module', 'booking')
    ->where('user_id', $userId)
    ->whereBetween('created_at', [$from, $to])
    ->get();
```

6. **Statistics:**
```php
$auditService = app(AuditService::class);
$stats = $auditService->getStatistics([
    'from' => '2025-01-01',
    'to' => '2025-12-31',
    'module' => 'booking'
]);

// Returns:
[
    'total' => 1500,
    'today' => 45,
    'this_week' => 320,
    'this_month' => 890,
    'by_action' => [
        'created' => 500,
        'updated' => 800,
        'deleted' => 200
    ],
    'by_module' => [
        'booking' => 600,
        'payment' => 400,
        'offer' => 500
    ],
    'top_users' => [...]
]
```

**Admin UI:**
```
/admin/audit-logs                 - List all logs with filters
/admin/audit-logs/{id}            - View log details
/admin/audit-logs/statistics      - Statistics dashboard
/admin/audit-logs/user/{id}/activity  - User activity
/admin/audit-logs/export          - Export to CSV
```

**Configuration per Model:**
```php
class YourModel extends Model {
    use Auditable;
    
    // Required: Module name for filtering
    protected $auditModule = 'your_module';
    
    // Optional: Which fields are prices (for special tracking)
    protected $priceFields = ['price', 'amount', 'total'];
    
    // Optional: Control what to audit
    protected static $auditOnCreate = true;   // Audit creates
    protected static $auditOnUpdate = true;   // Audit updates
    protected static $auditOnDelete = true;   // Audit deletes
    protected static $auditOnRestore = true;  // Audit restores (soft deletes)
    
    // Optional: Audit timestamp changes
    protected $auditTimestamps = false;  // Skip created_at/updated_at
    
    // Optional: Special status change handling
    protected $auditStatusChanges = true;  // Detect status field changes
}
```

**Difference from Snapshots:**

| Feature | Audit Logs | Snapshots |
|---------|-----------|-----------|
| **Purpose** | Track who changed what | Version complete entity state |
| **Storage** | Only changed fields | Complete entity data |
| **Use Case** | Activity monitoring | Restore/compare versions |
| **Frequency** | Every action | Major changes |
| **Size** | Lightweight | Heavy |

**Best Practices:**

1. **Use Audit Logs for:**
   - Activity monitoring
   - Security tracking
   - User action history
   - Compliance reporting

2. **Don't Audit:**
   - High-frequency automated changes
   - Temporary session data
   - Cache updates

3. **Performance:**
```php
// Good: Use eager loading
$bookings = Booking::with(['auditLogs' => function($q) {
    $q->latest()->limit(10);
}])->get();

// Bad: N+1 query problem
foreach ($bookings as $booking) {
    $logs = $booking->auditLogs;  // Extra query per booking!
}
```

**Common Tasks:**

1. **Add audit logging to new model:**
```php
use App\Traits\Auditable;

class YourModel extends Model {
    use Auditable;
    
    protected $auditModule = 'your_module';
    protected $priceFields = ['price'];
}
```

2. **View entity history:**
```php
$logs = $model->getAuditHistory(10);  // Last 10 logs
```

3. **Export logs:**
```php
// Visit: /admin/audit-logs/export?module=booking&from=2025-01-01
// Downloads CSV file
```

---

### PROMPT 38: Booking Timeline Engine

**Purpose**: Visual timeline tracking booking lifecycle from enquiry to completion

**Key Files:**
- `database/migrations/2025_12_09_102847_create_booking_timeline_events_table.php`
- `app/Models/BookingTimelineEvent.php` - Timeline event records
- `app/Services/BookingTimelineService.php` - Timeline orchestration
- `app/Traits/HasTimeline.php` - Auto-timeline trait
- `resources/views/admin/bookings/timeline.blade.php` - Timeline UI

**How It Works:**

1. **Automatic Timeline Generation:**
```php
class Booking extends Model {
    use HasTimeline;  // Automatically generates timeline!
}

// When booking is created:
$booking = Booking::create([...]);

// Timeline is automatically generated with 12 events:
// 1. Enquiry received
// 2. Offer created
// 3. Quotation generated
// 4. Payment hold
// 5. Payment settled
// 6. Graphics design (scheduled)
// 7. Printing (scheduled)
// 8. Mounting (scheduled)
// 9. Proof of display (scheduled)
// 10. Campaign start (scheduled)
// 11. Campaign running
// 12. Campaign completed (scheduled)
```

2. **Timeline Event Structure:**
```php
[
    'id' => 1,
    'booking_id' => 123,
    
    // Event classification
    'event_type' => 'graphics',  // enquiry, offer, quotation, payment_hold, graphics, etc.
    'event_category' => 'production',  // booking, payment, production, campaign
    'title' => 'Graphics Design',
    'description' => 'Creative design and artwork preparation',
    'status' => 'in_progress',  // pending, in_progress, completed, failed, cancelled
    
    // Version tracking (for offers/quotations)
    'version' => 1,
    
    // References to related entities
    'reference_id' => 45,  // ID of offer, quotation, payment, etc.
    'reference_type' => 'App\Models\Offer',
    
    // Timing
    'scheduled_at' => '2025-12-20 09:00:00',  // When it should happen
    'started_at' => '2025-12-20 09:15:00',    // When it actually started
    'completed_at' => '2025-12-20 17:30:00',  // When it finished
    'duration_minutes' => 495,  // Auto-calculated
    
    // Display
    'order' => 6,  // Display order (0-11)
    'icon' => 'fa-paint-brush',
    'color' => 'info',
    
    // User tracking
    'user_id' => 5,
    'user_name' => 'John Designer',
    
    'metadata' => ['additional' => 'data'],
]
```

3. **Managing Production Stages:**
```php
// Start a production stage
$booking->startProductionStage('graphics');
// Event status: pending → in_progress
// started_at: set to now()

// Complete a production stage
$booking->completeProductionStage('graphics');
// Event status: in_progress → completed
// completed_at: set to now()
// duration_minutes: auto-calculated

// Or use service directly
$timelineService = app(BookingTimelineService::class);
$timelineService->startProductionEvent($booking, 'printing');
$timelineService->completeProductionEvent($booking, 'printing');
```

4. **Custom Events:**
```php
// Add custom event to timeline
$booking->addTimelineEvent(
    'custom_event',
    'Custom Event Title',
    [
        'description' => 'Something special happened',
        'status' => 'completed',
        'icon' => 'fa-star',
        'color' => 'warning',
        'metadata' => ['key' => 'value']
    ]
);
```

5. **Timeline Queries:**
```php
// Get complete timeline
$timeline = $booking->getTimeline();

// Get progress percentage
$progress = $booking->getTimelineProgress();  // Returns 58.33 (7 of 12 completed)

// Get current active stage
$currentStage = $booking->getCurrentStage();
// Returns: BookingTimelineEvent with status 'in_progress' or next pending

// Get next upcoming event
$nextEvent = $booking->getNextEvent();
// Returns: Next event with scheduled_at > now()

// Filter events
$productionEvents = $booking->timelineEvents()
    ->where('event_category', 'production')
    ->get();

$completedEvents = $booking->timelineEvents()
    ->where('status', 'completed')
    ->count();
```

6. **Rebuild Timeline:**
```php
// If you need to regenerate timeline (e.g., after booking updates)
$booking->rebuildTimeline();
// Deletes all existing events and recreates them
```

**Timeline Event Types:**

| Event Type | Category | Auto-Status | Description |
|-----------|----------|-------------|-------------|
| `enquiry` | booking | completed | Customer enquiry received |
| `offer` | booking | completed | Vendor offer submission |
| `quotation` | booking | completed | Admin quotation generated |
| `payment_hold` | payment | completed | Payment authorized |
| `payment_settled` | payment | completed | Payment captured |
| `graphics` | production | scheduled | Graphics design stage |
| `printing` | production | scheduled | Printing stage |
| `mounting` | production | scheduled | Installation stage |
| `proof` | production | pending | Proof of display |
| `campaign_start` | campaign | scheduled | Campaign goes live |
| `campaign_running` | campaign | auto | Campaign active |
| `campaign_completed` | campaign | scheduled | Campaign ends |

**Status Lifecycle:**
```
pending → in_progress → completed
                    ↓
                  failed
                    ↓
                cancelled
```

**Admin UI Features:**

**Timeline Page** (`/admin/bookings/{id}/timeline`):
- Overall progress bar (% completion)
- Current stage highlight
- Next upcoming event
- Booking summary cards
- Vertical timeline visualization
- Event cards with:
  - Icon and color-coded status
  - Title and description
  - Scheduled/started/completed timestamps
  - Duration display
  - User attribution
  - Action buttons (Start/Complete for production stages)
  - Category and status badges

**Timeline Widget** (for dashboard):
```blade
<x-booking-timeline-widget :booking="$booking" />
```
Shows:
- Mini progress bar
- Current stage alert
- Last 5 events
- Link to full timeline

**API Endpoints:**
```php
// Get timeline data (JSON)
GET /admin/bookings/{id}/timeline/api

// Start production stage
POST /admin/bookings/{id}/timeline/start-stage
{ "stage": "graphics" }

// Complete production stage
POST /admin/bookings/{id}/timeline/complete-stage
{ "stage": "printing" }

// Add custom event
POST /admin/bookings/{id}/timeline/add-event
{
    "event_type": "custom",
    "title": "Special Event",
    "description": "Details",
    "status": "completed"
}

// Get progress
GET /admin/bookings/{id}/timeline/progress
// Returns: { "progress": 58.33 }

// Get current stage
GET /admin/bookings/{id}/timeline/current-stage

// Rebuild timeline
POST /admin/bookings/{id}/timeline/rebuild
```

**Common Tasks:**

1. **Add timeline to new booking:**
```php
// Already done automatically via HasTimeline trait!
// Just ensure Booking model uses the trait
```

2. **Track production progress:**
```php
// Graphics team starts work
$booking->startProductionStage('graphics');

// Graphics team completes
$booking->completeProductionStage('graphics');

// Move to printing
$booking->startProductionStage('printing');
```

3. **Check booking progress:**
```php
$progress = $booking->getTimelineProgress();
echo "Booking is {$progress}% complete";

$current = $booking->getCurrentStage();
if ($current) {
    echo "Currently in: {$current->title}";
}
```

4. **Customize timeline for special bookings:**
```php
// Add extra event
$booking->addTimelineEvent('inspection', 'Site Inspection', [
    'description' => 'Pre-campaign site inspection',
    'scheduled_at' => $booking->start_date->subDays(3),
    'order' => 8,  // Between mounting and proof
]);
```

**Integration with Other Systems:**

**With Audit Logs:**
```php
// Timeline events are separate from audit logs
// Use audit logs for: who changed timeline events
// Use timeline for: workflow progress tracking
```

**With Snapshots:**
```php
// Snapshots track data changes
// Timeline tracks workflow progress
// Both are complementary
```

**With Notifications:**
```php
// Auto-notify on timeline events
if ($event->notify_customer) {
    // Send notification to customer
}
if ($event->notify_vendor) {
    // Send notification to vendor
}
```

**Best Practices:**

1. **Don't modify existing events** - Create new ones for changes
2. **Use scheduled_at for planning** - Helps predict timeline issues
3. **Track duration** - Helps improve time estimates
4. **Use metadata** - Store additional context for events
5. **Rebuild sparingly** - Only when booking fundamentals change

**Troubleshooting:**

**Timeline not generating?**
```php
// Check if trait is added
if (!in_array(HasTimeline::class, class_uses(Booking::class))) {
    // Add trait to Booking model
}

// Manually generate
$timelineService = app(BookingTimelineService::class);
$timelineService->generateFullTimeline($booking);
```

**Progress stuck?**
```php
// Check for pending events
$pending = $booking->timelineEvents()
    ->where('status', 'pending')
    ->where('scheduled_at', '<', now())
    ->get();

// Mark overdue events as in_progress or completed
```

---

## Database Schema

### Core Tables

**users** - All platform users
```sql
id, name, email, password, role (admin/vendor/customer), 
phone, status, created_at, updated_at
```

**hoardings** - Billboard/hoarding inventory
```sql
id, vendor_id, title, description, address, city, area, 
hoarding_type, width, height, weekly_price, monthly_price,
latitude, longitude, status, created_at, updated_at
```

**enquiries** - Customer enquiry requests
```sql
id, customer_id, hoarding_id, start_date, end_date, 
duration_type, message, status, created_at, updated_at
```

**offers** - Vendor offers for enquiries
```sql
id, enquiry_id, vendor_id, hoarding_id, total_price, 
valid_until, status, created_at, updated_at
```

**quotations** - Admin-generated final quotes
```sql
id, offer_id, customer_id, vendor_id, total_amount, 
tax, discount, final_amount, status, created_at, updated_at
```

**bookings** - Confirmed bookings
```sql
id, quotation_id, customer_id, vendor_id, hoarding_id,
start_date, end_date, total_amount, status, payment_status,
created_at, updated_at
```

### System Tables

**snapshots** - Version history (PROMPT 36)
```sql
id, snapshotable_type, snapshotable_id, snapshot_type, event,
version, data (JSON), changes (JSON), metadata (JSON),
created_by, ip_address, created_at
```

**audit_logs** - Activity logs (PROMPT 37)
```sql
id, auditable_type, auditable_id, user_id, user_name,
action, description, old_values (JSON), new_values (JSON),
changed_fields (JSON), ip_address, user_agent, 
request_method, request_url, module, created_at
```

**booking_timeline_events** - Timeline tracking (PROMPT 38)
```sql
id, booking_id, event_type, event_category, title, description,
status, reference_id, reference_type, version, user_id, user_name,
scheduled_at, started_at, completed_at, duration_minutes,
order, icon, color, metadata (JSON), created_at, updated_at
```

### Relationships

```
User
  ↓ has many
Hoardings
  ↓ receives
Enquiries
  ↓ generates
Offers
  ↓ converts to
Quotations
  ↓ confirms to
Bookings
  ↓ tracks with
Timeline Events
  ↓ logs in
Audit Logs
  ↓ versions in
Snapshots
```

---

## API Reference

### Authentication
All API routes require authentication via Laravel Sanctum or session.

### Booking Timeline API

**Get Timeline**
```http
GET /admin/bookings/{id}/timeline/api
Response: {
    "success": true,
    "timeline": [...events],
    "progress": 58.33,
    "current_stage": {...}
}
```

**Start Stage**
```http
POST /admin/bookings/{id}/timeline/start-stage
Body: { "stage": "graphics" }
Response: {
    "success": true,
    "message": "Graphics stage started",
    "event": {...}
}
```

**Complete Stage**
```http
POST /admin/bookings/{id}/timeline/complete-stage
Body: { "stage": "printing" }
Response: {
    "success": true,
    "message": "Printing stage completed",
    "event": {...}
}
```

### Audit Logs API

**Get Logs for Model**
```http
GET /admin/audit-logs/for-model?model_type=App\Models\Booking&model_id=123
Response: {
    "success": true,
    "logs": [...]
}
```

**Get User Activity**
```http
GET /admin/audit-logs/user/{userId}/activity?limit=100
Response: {
    "success": true,
    "logs": [...]
}
```

**Statistics**
```http
GET /admin/audit-logs/statistics?from=2025-01-01&to=2025-12-31
Response: {
    "success": true,
    "statistics": {
        "total": 1500,
        "today": 45,
        "by_action": {...},
        "by_module": {...}
    }
}
```

### Snapshots API

**Get Snapshots for Model**
```http
GET /admin/snapshots/for-model?model_type=App\Models\Offer&model_id=45
Response: {
    "success": true,
    "snapshots": [...]
}
```

**Compare Versions**
```http
POST /admin/snapshots/compare
Body: { "snapshot1_id": 1, "snapshot2_id": 3 }
Response: {
    "success": true,
    "snapshot1": {...},
    "snapshot2": {...},
    "differences": [...]
}
```

---

## Common Tasks

### 1. Add a New Model with Full Tracking

```php
<?php

namespace App\Models;

use App\Traits\HasSnapshots;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    // Add all tracking traits
    use HasSnapshots, Auditable;
    
    // Configure snapshots
    protected $snapshotType = 'campaign';
    protected $snapshotOnCreate = true;
    protected $snapshotOnUpdate = true;
    
    // Configure audit logs
    protected $auditModule = 'campaign';
    protected $priceFields = ['budget', 'cost'];
    
    protected $fillable = [
        'name',
        'budget',
        'cost',
        'status',
        // ... other fields
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
```

Now this model automatically:
- ✓ Creates snapshots on every change
- ✓ Logs all actions in audit trail
- ✓ Tracks who, what, when, where
- ✓ Provides version history
- ✓ Supports restore functionality

### 2. Create a New Service

```php
<?php

namespace App\Services;

class CampaignService
{
    /**
     * Create a campaign with all related data
     */
    public function createCampaign(array $data): Campaign
    {
        // Validate data
        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            // ... validation rules
        ])->validate();
        
        // Create campaign
        $campaign = Campaign::create($validated);
        
        // Additional business logic
        $this->setupCampaignDefaults($campaign);
        
        // Trigger notifications
        $this->notifyStakeholders($campaign);
        
        return $campaign;
    }
    
    /**
     * Update campaign status
     */
    public function updateStatus(Campaign $campaign, string $newStatus): bool
    {
        $oldStatus = $campaign->status;
        $campaign->update(['status' => $newStatus]);
        
        // Audit log is automatic via Auditable trait
        
        // Additional actions based on status
        if ($newStatus === 'active') {
            $this->activateCampaign($campaign);
        }
        
        return true;
    }
    
    protected function setupCampaignDefaults(Campaign $campaign): void
    {
        // Setup logic
    }
    
    protected function notifyStakeholders(Campaign $campaign): void
    {
        // Notification logic
    }
    
    protected function activateCampaign(Campaign $campaign): void
    {
        // Activation logic
    }
}
```

### 3. Add a New Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    protected $campaignService;
    
    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }
    
    /**
     * List all campaigns
     */
    public function index()
    {
        $campaigns = Campaign::latest()->paginate(20);
        return view('admin.campaigns.index', compact('campaigns'));
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.campaigns.create');
    }
    
    /**
     * Store new campaign
     */
    public function store(Request $request)
    {
        $campaign = $this->campaignService->createCampaign(
            $request->all()
        );
        
        return redirect()
            ->route('admin.campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully!');
    }
    
    /**
     * Show campaign details
     */
    public function show(Campaign $campaign)
    {
        // Load relationships
        $campaign->load(['bookings', 'auditLogs']);
        
        return view('admin.campaigns.show', compact('campaign'));
    }
    
    /**
     * Update campaign
     */
    public function update(Request $request, Campaign $campaign)
    {
        $campaign->update($request->validated());
        
        return back()->with('success', 'Campaign updated!');
    }
    
    /**
     * Delete campaign
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        // Audit log automatically created via Auditable trait
        
        return redirect()
            ->route('admin.campaigns.index')
            ->with('success', 'Campaign deleted!');
    }
}
```

### 4. Create a Custom Timeline Event

```php
// When something special happens in your workflow
public function handleSpecialEvent(Booking $booking)
{
    // Add custom event to timeline
    $booking->addTimelineEvent(
        'site_inspection',  // event_type
        'Site Inspection Completed',  // title
        [
            'description' => 'Pre-campaign site inspection by surveyor',
            'status' => 'completed',
            'completed_at' => now(),
            'icon' => 'fa-clipboard-check',
            'color' => 'success',
            'metadata' => [
                'inspector_name' => 'John Doe',
                'findings' => 'Site ready for installation',
                'photos_uploaded' => true,
            ],
            'notify_customer' => true,  // Send notification
            'notify_vendor' => true,
        ]
    );
}
```

### 5. Query with Relationships

```php
// Efficient querying with eager loading
$bookings = Booking::with([
    'customer',
    'vendor',
    'hoarding',
    'timelineEvents' => function($q) {
        $q->latest()->limit(5);  // Only last 5 events
    },
    'auditLogs' => function($q) {
        $q->where('action', 'updated')->latest();
    },
    'snapshots' => function($q) {
        $q->orderBy('version', 'desc')->limit(3);
    }
])->get();

// Now you can access without additional queries
foreach ($bookings as $booking) {
    echo $booking->customer->name;  // No extra query
    echo $booking->getTimelineProgress();  // No extra query
    $logs = $booking->auditLogs;  // No extra query
}
```

---

## Best Practices

### 1. Always Use Services for Business Logic
```php
// ❌ Bad: Logic in controller
public function store(Request $request) {
    $booking = Booking::create($request->all());
    $booking->timelineEvents()->create([...]);
    // Send email
    // Update inventory
    // 50 more lines...
}

// ✅ Good: Logic in service
public function store(Request $request) {
    $booking = $this->bookingService->create($request->validated());
    return redirect()->route('bookings.show', $booking);
}
```

### 2. Use Traits for Reusable Functionality
```php
// ✅ Good: Share functionality via traits
class Offer extends Model {
    use HasSnapshots, Auditable, HasTimeline;
}

class Booking extends Model {
    use HasSnapshots, Auditable, HasTimeline;
}
```

### 3. Eager Load Relationships
```php
// ❌ Bad: N+1 query problem
$bookings = Booking::all();
foreach ($bookings as $booking) {
    echo $booking->customer->name;  // 1 query per booking!
}

// ✅ Good: Eager loading
$bookings = Booking::with('customer')->all();
foreach ($bookings as $booking) {
    echo $booking->customer->name;  // Single query!
}
```

### 4. Validate Input
```php
// ✅ Always validate user input
public function store(Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'amount' => 'required|numeric|min:0',
    ]);
    
    return YourModel::create($validated);
}
```

### 5. Use Transactions for Multiple Operations
```php
use Illuminate\Support\Facades\DB;

// ✅ Use transactions for related operations
public function processBooking(Booking $booking) {
    DB::transaction(function() use ($booking) {
        $booking->update(['status' => 'confirmed']);
        $booking->payments()->create([...]);
        $booking->startProductionStage('graphics');
    });
}
```

### 6. Return Consistent API Responses
```php
// ✅ Consistent structure
return response()->json([
    'success' => true,
    'message' => 'Operation completed',
    'data' => $result
]);

// On error
return response()->json([
    'success' => false,
    'message' => 'Something went wrong',
    'errors' => $validator->errors()
], 422);
```

---

## Troubleshooting

### Issue: Trait not working

**Problem**: Model changes not creating snapshots/audit logs

**Solution**:
```php
// 1. Check if trait is imported
use App\Traits\HasSnapshots;
use App\Traits\Auditable;

class YourModel extends Model {
    use HasSnapshots, Auditable;  // ← Make sure it's here
}

// 2. Check if protected properties are set
protected $snapshotType = 'your_model';
protected $auditModule = 'your_module';

// 3. Clear cache
php artisan cache:clear
php artisan config:clear
```

### Issue: Timeline not generating

**Problem**: Booking created but no timeline events

**Solution**:
```php
// 1. Check if HasTimeline trait is added
use App\Traits\HasTimeline;

class Booking extends Model {
    use HasTimeline;  // ← Required
}

// 2. Manually generate timeline
$booking = Booking::find($id);
$booking->rebuildTimeline();

// 3. Check if booking has required relationships
$booking->load(['quotation.offer.enquiry']);
```

### Issue: N+1 Query Problem

**Problem**: Slow page loads, many database queries

**Solution**:
```php
// Install debugbar to see queries
composer require barryvdh/laravel-debugbar --dev

// Use eager loading
$bookings = Booking::with(['customer', 'vendor', 'hoarding'])->get();

// For nested relationships
$bookings = Booking::with(['quotation.offer.enquiry'])->get();
```

### Issue: Migration Errors

**Problem**: Migration fails with foreign key errors

**Solution**:
```bash
# 1. Drop all tables and re-migrate
php artisan migrate:fresh

# 2. If specific table issue, rollback and retry
php artisan migrate:rollback --step=1
php artisan migrate

# 3. Check table order in migrations
# Parent tables must be created before child tables
# Example: users → hoardings → enquiries → offers
```

### Issue: Snapshots taking too much space

**Problem**: Database growing too large

**Solution**:
```php
// 1. Don't snapshot high-frequency changes
protected $snapshotOnUpdate = false;  // Disable auto-snapshots

// 2. Clean old snapshots (be careful!)
Snapshot::where('created_at', '<', now()->subMonths(6))
    ->where('snapshot_type', 'non_critical_type')
    ->delete();

// 3. Use audit logs for frequent changes instead
use Auditable;  // Lighter than snapshots
```

---

## Getting Help

### Useful Commands
```bash
# View routes
php artisan route:list

# View models
php artisan model:show Booking

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Run tests
php artisan test
```

### Debugging Tools
```php
// 1. dd() - Dump and die
dd($variable);

// 2. dump() - Dump and continue
dump($variable);

// 3. Log debugging info
\Log::info('Debug message', ['data' => $variable]);

// 4. Query log
\DB::enableQueryLog();
// ... your queries
dd(\DB::getQueryLog());
```

### Code Comments
Write clear comments for complex logic:
```php
/**
 * Calculate vendor payout after deducting platform commission
 * 
 * Formula: payout = (booking_amount * (1 - commission_rate)) - pg_fees
 * 
 * @param Booking $booking
 * @return float Payout amount
 */
public function calculateVendorPayout(Booking $booking): float
{
    // Get commission rate from active rule
    $commissionRate = $this->getApplicableCommissionRate($booking);
    
    // Calculate base payout
    $basePayout = $booking->total_amount * (1 - $commissionRate);
    
    // Deduct payment gateway fees
    $pgFees = $booking->payments->sum('pg_fee');
    
    return max(0, $basePayout - $pgFees);
}
```

---

## Quick Reference

### Important Files Cheat Sheet

| Feature | Model | Service | Controller | Routes | View |
|---------|-------|---------|------------|--------|------|
| Snapshots | `Models/Snapshot.php` | `Services/SnapshotService.php` | `Admin/SnapshotController.php` | Line 385 | `admin/snapshots/` |
| Audit Logs | `Models/AuditLog.php` | `Services/AuditService.php` | `Admin/AuditLogController.php` | Line 397 | `admin/audit-logs/` |
| Timeline | `Models/BookingTimelineEvent.php` | `Services/BookingTimelineService.php` | `Admin/BookingTimelineController.php` | Line 411 | `admin/bookings/timeline.blade.php` |
| Bookings | `Models/Booking.php` | - | `Admin/BookingController.php` | - | `admin/bookings/` |
| Payments | `Models/BookingPayment.php` | `Services/PaymentService.php` | - | - | - |

### Useful Code Snippets

**Check if user has permission:**
```php
if (auth()->user()->can('manage_bookings')) {
    // User has permission
}
```

**Format currency:**
```php
₹{{ number_format($amount, 2) }}
```

**Format date:**
```php
{{ $date->format('M d, Y') }}
{{ $date->diffForHumans() }}  // "2 hours ago"
```

**Flash messages:**
```php
return redirect()->back()->with('success', 'Action completed!');
return redirect()->back()->with('error', 'Something went wrong!');
```

---

**Document Version**: 1.0  
**Last Updated**: December 9, 2025  
**For**: OOH Booking Platform v3
