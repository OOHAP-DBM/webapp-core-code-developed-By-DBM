# OOHApp Developer Guide - Prompt 22 & 23 Implementation

**Last Updated:** December 8, 2025  
**Version:** 3.0  
**Implemented Features:** Direct Booking Flow + Enquiry-Offer-Quotation Workflow

---

## 📚 Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Prompt 22: Direct Booking Flow](#prompt-22-direct-booking-flow)
4. [Prompt 23: Enquiry-Offer-Quotation Workflow](#prompt-23-enquiry-offer-quotation-workflow)
5. [Database Schema](#database-schema)
6. [API Reference](#api-reference)
7. [Service Layer](#service-layer)
8. [Frontend Integration](#frontend-integration)
9. [Configuration](#configuration)
10. [Testing Guide](#testing-guide)
11. [Troubleshooting](#troubleshooting)
12. [Extension Points](#extension-points)

---

## 📖 Overview

This guide covers two major features implemented in the OOHApp platform:

### **Prompt 22: Direct Booking Flow**
Customer can book hoardings directly without quotation process:
- **Flow:** Customer → Select Hoarding → Check Availability → Book → Pay → Campaign Start (POD)
- **Key Features:** 30-min auto-refund, Razorpay integration, POD approval workflow

### **Prompt 23: Enquiry-Offer-Quotation Workflow**
Complete lead lifecycle with thread-based communication:
- **Flow:** Enquiry → Vendor Offer (versions) → Customer Accept → Quotation → Customer Approve → Booking
- **Key Features:** Version management, immutable snapshots, inline thread actions

---

## 🏗️ Architecture

### Module Structure
```
Modules/
├── Bookings/
│   ├── Controllers/Api/DirectBookingController.php
│   ├── Services/DirectBookingService.php
│   └── Models/Booking.php (updated)
├── Campaigns/
│   ├── Controllers/Api/PODController.php
│   └── Services/CampaignService.php
├── POD/
│   └── Models/PODSubmission.php
├── Threads/
│   ├── Controllers/Api/ThreadController.php
│   ├── Services/ThreadService.php
│   ├── Models/Thread.php
│   └── Models/ThreadMessage.php
├── Enquiries/
│   ├── Controllers/Api/EnquiryWorkflowController.php
│   └── Models/Enquiry.php (updated)
├── Offers/
│   ├── Services/OfferWorkflowService.php
│   └── Models/Offer.php (updated)
└── Quotations/
    ├── Services/QuotationWorkflowService.php
    └── Models/Quotation.php

app/
└── Jobs/
    └── ProcessAutoRefundJob.php

resources/views/
├── customer/bookings/
│   ├── summary.blade.php
│   └── payment-success.blade.php
├── enquiries/
│   └── index.blade.php
└── threads/
    └── conversation.blade.php
```

### Design Patterns
- **Service Layer Pattern:** Business logic isolated in services
- **Repository Pattern:** Data access through Eloquent models
- **Observer Pattern:** Event-driven notifications (placeholder)
- **Strategy Pattern:** Payment gateway abstraction
- **Queue Pattern:** Async job processing (auto-refund)

---

## 🎯 Prompt 22: Direct Booking Flow

### Feature Overview
Allows customers to book hoardings instantly with Razorpay payment and automatic refund within 30 minutes.

### Components

#### 1. DirectBookingService
**Location:** `Modules/Bookings/Services/DirectBookingService.php`

**Purpose:** Handles booking validation, availability checks, and booking creation.

**Key Methods:**

```php
// Create a direct booking
public function createDirectBooking(array $data): Booking

// Validate availability with grace period
public function validateAvailability(int $hoardingId, Carbon $startDate, Carbon $endDate): bool

// Calculate pricing (base × days + tax)
public function calculatePricing(Hoarding $hoarding, Carbon $startDate, Carbon $endDate): array

// Get available hoardings with filters
public function getAvailableHoardings(array $filters)

// Check individual hoarding availability
public function checkHoardingAvailability(int $hoardingId, string $startDate, string $endDate): array
```

**Validation Rules:**
- ✅ Grace period between bookings (from settings)
- ✅ Min/max duration limits (from settings)
- ✅ Max future start date (from settings)
- ✅ Overlapping bookings check (regular + POS)
- ✅ Start date not in past
- ✅ End date after start date

**Admin Settings Used:**
- `grace_period_minutes` (default: 15)
- `booking_min_duration_days` (default: 7)
- `booking_max_duration_months` (default: 12)
- `max_future_booking_start_months` (default: 12)
- `booking_tax_rate` (default: 18)
- `booking_hold_minutes` (default: 30)

#### 2. CampaignService
**Location:** `Modules/Campaigns/Services/CampaignService.php`

**Purpose:** Manages POD (Proof of Delivery) submission and approval workflow.

**Key Methods:**

```php
// Mounter uploads POD files
public function submitPOD(int $bookingId, int $mounterId, array $files, ?string $notes): PODSubmission

// Vendor approves POD → starts campaign
public function approvePOD(int $podId, int $vendorId, ?string $approvalNotes): PODSubmission

// Vendor rejects POD → requires resubmission
public function rejectPOD(int $podId, int $vendorId, string $rejectionReason): PODSubmission

// Get pending PODs for vendor
public function getPendingPODsForVendor(int $vendorId, int $perPage = 15)

// Get campaign status
public function getCampaignStatus(Booking $booking): array
```

**POD Workflow:**
1. Booking confirmed with start_date
2. When start_date arrives → Mounter uploads photos/videos
3. Files stored in `storage/app/public/pod/{booking_id}/`
4. Vendor reviews and approves/rejects
5. On approval → `campaign_started_at` timestamp set
6. Customer notified (placeholder for email/push)

**File Validation:**
- Allowed: jpg, jpeg, png, mp4, mov, avi
- Max size: 50MB per file
- Max files: 10 per submission

#### 3. ProcessAutoRefundJob
**Location:** `app/Jobs/ProcessAutoRefundJob.php`

**Purpose:** Queue job to process automatic refunds within 30-minute window.

**Flow:**
```php
handle() {
    1. Check if booking is confirmed with captured payment
    2. Check if payment_captured_at is within 30 minutes
    3. If YES → Call RazorpayService::createRefund()
    4. Update booking status to STATUS_REFUNDED
    5. If NO → Cancel without refund
    6. On failure → Mark refund_pending for manual intervention
}
```

**Dispatch Example:**
```php
ProcessAutoRefundJob::dispatch($bookingId, $userId, $cancellationReason);
```

#### 4. DirectBookingController
**Location:** `Modules/Bookings/Controllers/Api/DirectBookingController.php`

**Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/customer/direct-bookings/available-hoardings` | Search available hoardings |
| POST | `/api/v1/customer/direct-bookings/check-availability` | Check specific hoarding availability |
| POST | `/api/v1/customer/direct-bookings` | Create booking |
| POST | `/api/v1/customer/direct-bookings/{id}/initiate-payment` | Create Razorpay order |
| POST | `/api/v1/customer/direct-bookings/{id}/confirm-payment` | Capture payment |
| POST | `/api/v1/customer/direct-bookings/{id}/cancel` | Cancel with auto-refund |
| GET | `/api/v1/customer/direct-bookings/{id}` | Get booking details |

#### 5. PODController
**Location:** `Modules/Campaigns/Controllers/Api/PODController.php`

**Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/mounter/bookings/{id}/pod/submit` | Mounter submits POD |
| GET | `/api/v1/customer/bookings/{id}/pod` | Customer views POD submissions |
| GET | `/api/v1/customer/bookings/{id}/pod/{id}` | Get specific POD |
| GET | `/api/v1/vendor/pod/pending` | Vendor's pending PODs |
| POST | `/api/v1/vendor/pod/{id}/approve` | Vendor approves POD |
| POST | `/api/v1/vendor/pod/{id}/reject` | Vendor rejects POD |

### Database Changes

#### PODSubmission Model
**Table:** `pod_submissions`

```sql
CREATE TABLE pod_submissions (
    id BIGINT PRIMARY KEY,
    booking_id BIGINT FK → bookings.id,
    submitted_by BIGINT FK → users.id (mounter),
    submission_date TIMESTAMP,
    files JSON, -- [{path, type, size, uploaded_at}, ...]
    notes TEXT,
    status ENUM('pending', 'approved', 'rejected'),
    approved_by BIGINT FK → users.id,
    approved_at TIMESTAMP,
    approval_notes TEXT,
    rejected_by BIGINT FK → users.id,
    rejected_at TIMESTAMP,
    rejection_reason TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Indexes:**
- `booking_id`, `submitted_by`, `status`, `submission_date`

#### Booking Model Updates
**Added Columns:**

```sql
ALTER TABLE bookings ADD COLUMN (
    refund_id VARCHAR(255), -- Razorpay refund ID
    refund_amount DECIMAL(12,2),
    refunded_at TIMESTAMP,
    refund_error TEXT,
    campaign_started_at TIMESTAMP -- Set when POD approved
);
```

**Added Relationships:**
```php
public function podSubmissions(): HasMany

**Added Casts:**
```php
'refund_amount' => 'decimal:2',
'refunded_at' => 'datetime',
'campaign_started_at' => 'datetime',
```

### Frontend Views

#### 1. Booking Summary (`resources/views/customer/bookings/summary.blade.php`)
**Features:**
- 📊 Hoarding details card
- 📅 Booking period card
- 💰 Price breakdown card
- ⏱️ 30-minute countdown timer with progress bar
- 🎨 Color transitions (green → yellow → red)
- ⚡ Blinking animation when < 5 minutes

**JavaScript Functions:**
- `loadBookingDetails()` - Fetch booking via AJAX
- `startCountdownTimer()` - Update timer every second
- `initiatePayment()` - Redirect to Razorpay

#### 2. Payment Success (`resources/views/customer/bookings/payment-success.blade.php`)
**Features:**
- ✅ Success checkmark animation (CSS keyframes)
- 📋 Booking confirmation details
- ⏱️ 30-minute refund countdown timer
- 📈 Timeline visualization (4 steps)
- ❌ "Cancel Booking" button (full refund)
- 📝 Cancel confirmation modal

**JavaScript Functions:**
- `loadBookingDetails()` - Fetch booking data
- `startRefundTimer()` - 30-min countdown from payment_captured_at
- `confirmCancellation()` - POST to cancel endpoint

---

## 🔄 Prompt 23: Enquiry-Offer-Quotation Workflow

### Feature Overview
Complete lead lifecycle with thread-based communication between customer and vendor.

### Components

#### 1. Thread Models

##### Thread Model
**Location:** `Modules/Threads/Models/Thread.php`

**Table:** `threads`

```sql
CREATE TABLE threads (
    id BIGINT PRIMARY KEY,
    enquiry_id BIGINT FK → enquiries.id,
    customer_id BIGINT FK → users.id,
    vendor_id BIGINT FK → users.id (nullable for multi-vendor),
    is_multi_vendor BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'closed', 'archived') DEFAULT 'active',
    last_message_at TIMESTAMP,
    unread_count_customer INT DEFAULT 0,
    unread_count_vendor INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Key Methods:**
```php
public function incrementUnread(string $userType): void
public function resetUnread(string $userType): void
public function hasUnreadForCustomer(): bool
public function hasUnreadForVendor(): bool
```

##### ThreadMessage Model
**Location:** `Modules/Threads/Models/ThreadMessage.php`

**Table:** `thread_messages`

```sql
CREATE TABLE thread_messages (
    id BIGINT PRIMARY KEY,
    thread_id BIGINT FK → threads.id,
    sender_id BIGINT FK → users.id,
    sender_type ENUM('customer', 'vendor', 'admin'),
    message_type ENUM('text', 'offer', 'quotation', 'system'),
    message TEXT,
    attachments JSON, -- [{path, original_name, mime_type, size, uploaded_at}, ...]
    offer_id BIGINT FK → offers.id (nullable),
    quotation_id BIGINT FK → quotations.id (nullable),
    is_read_customer BOOLEAN DEFAULT FALSE,
    is_read_vendor BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Key Methods:**
```php
public function markAsRead(string $userType): void
public function isOffer(): bool
public function isQuotation(): bool
public function hasAttachments(): bool
```

#### 2. OfferWorkflowService
**Location:** `Modules/Offers/Services/OfferWorkflowService.php`

**Purpose:** Manages offer versions with snapshot freezing.

**Key Methods:**

```php
// Create new offer version
public function createOfferVersion(array $data): Offer

// Send offer via thread
public function sendOfferViaThread(int $offerId, ?string $message = null): Offer

// Accept and freeze offer
public function acceptOfferAndFreeze(int $offerId, int $customerId): Offer

// Reject offer with reason
public function rejectOfferViaThread(int $offerId, int $customerId, ?string $reason): Offer

// Update draft offer
public function updateDraftOffer(int $offerId, array $data): Offer

// Get offer versions for enquiry
public function getOfferVersions(int $enquiryId, ?int $vendorId = null)
```

**Offer Snapshot Structure:**
```json
{
  "offer_details": {
    "price": 50000.00,
    "price_type": "monthly",
    "description": "Premium hoarding location"
  },
  "duration": {
    "start_date": "2025-12-15",
    "end_date": "2026-03-15",
    "days": 90,
    "weeks": 13,
    "months": 3,
    "type": "months"
  },
  "price_breakdown": {
    "base_price": 50000,
    "price_type": "monthly",
    "duration_days": 90,
    "months": 3,
    "total": 150000,
    "per_month": 50000,
    "calculation": "50000 × 3 months"
  },
  "hoarding_snapshot": { ... },
  "vendor_snapshot": { ... },
  "acceptance": { // Added on acceptance
    "accepted_at": "2025-12-08 10:30:00",
    "frozen": true,
    "offer_id": 123,
    "offer_version": 2,
    "status": "immutable"
  }
}
```

**Version Management:**
- Each vendor can create multiple versions (v1, v2, v3...)
- Version auto-increments per vendor per enquiry
- Only draft offers can be edited/deleted
- Accepted offers are frozen (immutable)

#### 3. QuotationWorkflowService
**Location:** `Modules/Quotations/Services/QuotationWorkflowService.php`

**Purpose:** Manages quotation versions with line items and auto-calculation.

**Key Methods:**

```php
// Create quotation version
public function createQuotationVersion(array $data): Quotation

// Send via thread
public function sendQuotationViaThread(int $quotationId, ?string $message): Quotation

// Approve and freeze
public function approveQuotationAndFreeze(int $quotationId, int $customerId): Quotation

// Reject with reason
public function rejectQuotationViaThread(int $quotationId, int $customerId, ?string $reason): Quotation

// Revise (create new version)
public function reviseQuotation(int $quotationId, array $data): Quotation

// Update draft
public function updateDraftQuotation(int $quotationId, array $data): Quotation
```

**Quotation Calculation:**
```php
// Line items
items: [
    {description: "Hoarding Installation", quantity: 1, rate: 50000},
    {description: "Mounting Service", quantity: 1, rate: 10000},
    {description: "Maintenance (3 months)", quantity: 3, rate: 5000}
]

// Calculation
Subtotal = Σ(quantity × rate) = 50000 + 10000 + 15000 = 75000
Tax (18%) = 75000 × 0.18 = 13500
Discount = 5000
Grand Total = 75000 + 13500 - 5000 = 83500
```

**Quotation Snapshot Structure:**
```json
{
  "quotation_details": {
    "quotation_id": 456,
    "version": 1,
    "items": [ ... ],
    "total_amount": 75000.00,
    "tax": 13500.00,
    "discount": 5000.00,
    "grand_total": 83500.00,
    "notes": "Payment terms: 30 days"
  },
  "offer_snapshot": { ... }, // Includes frozen offer details
  "approval": {
    "approved_at": "2025-12-08 11:00:00",
    "approved_by": 789,
    "frozen": true,
    "status": "immutable"
  }
}
```

#### 4. ThreadService
**Location:** `Modules/Threads/Services/ThreadService.php`

**Purpose:** Manages thread communication and file attachments.

**Key Methods:**

```php
// Get or create thread for enquiry
public function getOrCreateThread(int $enquiryId): Thread

// Send text message with attachments
public function sendMessage(int $threadId, array $data): ThreadMessage

// Get messages for thread
public function getThreadMessages(int $threadId, array $filters = [])

// Get customer/vendor threads
public function getCustomerThreads(int $customerId, array $filters = [])
public function getVendorThreads(int $vendorId, array $filters = [])

// Mark messages as read
public function markAsRead(int $threadId, int $userId, string $userType): void

// Close/reopen thread
public function closeThread(int $threadId, int $userId, string $userType): Thread
public function reopenThread(int $threadId): Thread

// Search threads
public function searchThreads(int $userId, string $userType, string $searchTerm)
```

**File Upload:**
- Stored in `storage/app/public/thread_attachments/{thread_id}/`
- Metadata stored in JSON: `[{path, original_name, mime_type, size, uploaded_at}, ...]`

#### 5. ThreadController
**Location:** `Modules/Threads/Controllers/Api/ThreadController.php`

**Purpose:** Single controller for all thread operations including inline offer/quotation actions.

**Endpoints:**

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/customer/threads` | Customer | List threads |
| GET | `/api/v1/vendor/threads` | Vendor | List threads |
| GET | `/api/v1/{role}/threads/{id}` | Both | Get thread with messages |
| POST | `/api/v1/{role}/threads/{id}/messages` | Both | Send text message |
| POST | `/api/v1/vendor/threads/{id}/offers/create` | Vendor | Create & send offer inline |
| POST | `/api/v1/customer/threads/{threadId}/offers/{offerId}/accept` | Customer | Accept offer inline |
| POST | `/api/v1/customer/threads/{threadId}/offers/{offerId}/reject` | Customer | Reject offer inline |
| POST | `/api/v1/vendor/threads/{id}/quotations/create` | Vendor | Create & send quotation inline |
| POST | `/api/v1/customer/threads/{threadId}/quotations/{quotationId}/approve` | Customer | Approve quotation inline |
| POST | `/api/v1/customer/threads/{threadId}/quotations/{quotationId}/reject` | Customer | Reject quotation inline |

**Authorization:**
- Customer: Can only access own threads (customer_id match)
- Vendor: Can only access own threads (vendor_id match)
- Admin: Can access all threads
- Inline actions validated against thread ownership

#### 6. EnquiryWorkflowController
**Location:** `Modules/Enquiries/Controllers/Api/EnquiryWorkflowController.php`

**Endpoints:**

| Method | Endpoint | Role | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/customer/enquiries` | Customer | Create enquiry |
| GET | `/api/v1/customer/enquiries` | Customer | List enquiries |
| GET | `/api/v1/vendor/enquiries` | Vendor | List enquiries |
| GET | `/api/v1/{role}/enquiries/{id}` | Both | Get enquiry details |
| POST | `/api/v1/customer/enquiries/{id}/cancel` | Customer | Cancel enquiry |

### Frontend Views

#### 1. Thread Conversation UI (`resources/views/threads/conversation.blade.php`)
**Layout:**
```
┌─────────────────────────────────────────────────────────┐
│ [Thread List Sidebar]  │  [Conversation Area]           │
│ ├─ Thread 1 (unread)   │  ┌─────────────────────────┐   │
│ ├─ Thread 2            │  │  Thread Header          │   │
│ └─ Thread 3 (active)   │  └─────────────────────────┘   │
│                        │  [Messages]                    │
│                        │  └─ Text message (sent)        │
│                        │  └─ Text message (received)    │
│                        │  └─ Offer Card [Accept|Reject] │
│                        │  └─ Quotation Card [Approve]   │
│                        │  └─ System message             │
│                        │  ┌─────────────────────────┐   │
│                        │  │  Message Input + Files  │   │
│                        │  └─────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

**Features:**
- 📱 3-column responsive layout
- 💬 Real-time message rendering
- 🔄 Auto-polling (5-second interval)
- 📎 File attachment support
- 🎨 Color-coded message bubbles
- 🔔 Unread badges
- 📊 Inline offer/quotation cards with action buttons

**Modals:**
1. **Offer Modal** - Create offer form (vendor)
2. **Quotation Modal** - Line items table with auto-calculation (vendor)

**JavaScript Functions:**
```javascript
loadThreads()                      // Load thread list with unread counts
loadThread(threadId)               // Load specific thread messages
sendMessage()                      // Send text message
createOffer()                      // Create and send offer
createQuotation()                  // Create and send quotation
acceptOffer(offerId)               // Accept offer inline
rejectOffer(offerId)               // Reject offer inline
approveQuotation(quotationId)      // Approve quotation inline
rejectQuotation(quotationId)       // Reject quotation inline
calculateQuotation()               // Auto-calculate totals
```

#### 2. Enquiry List (`resources/views/enquiries/index.blade.php`)
**Features:**
- 📋 Paginated list with filters
- 🔍 Status filter (pending, accepted, rejected, cancelled)
- 🔎 Search by hoarding name/location
- 🏷️ Status badges with color coding
- 💬 Quick link to thread
- ❌ Cancel button for pending enquiries

---

## 💾 Database Schema

### Complete Schema Overview

```
enquiries
├─ id
├─ customer_id → users.id
├─ hoarding_id → hoardings.id
├─ preferred_start_date
├─ preferred_end_date
├─ duration_type
├─ message
├─ status
├─ snapshot (JSON)
└─ created_at, updated_at

threads
├─ id
├─ enquiry_id → enquiries.id
├─ customer_id → users.id
├─ vendor_id → users.id (nullable)
├─ is_multi_vendor
├─ status
├─ last_message_at
├─ unread_count_customer
├─ unread_count_vendor
└─ created_at, updated_at

thread_messages
├─ id
├─ thread_id → threads.id
├─ sender_id → users.id
├─ sender_type
├─ message_type
├─ message
├─ attachments (JSON)
├─ offer_id → offers.id (nullable)
├─ quotation_id → quotations.id (nullable)
├─ is_read_customer
├─ is_read_vendor
├─ read_at
└─ created_at, updated_at

offers
├─ id
├─ enquiry_id → enquiries.id
├─ vendor_id → users.id
├─ price
├─ price_type
├─ price_snapshot (JSON)
├─ description
├─ valid_until
├─ status
├─ version
└─ created_at, updated_at

quotations
├─ id
├─ offer_id → offers.id
├─ customer_id → users.id
├─ vendor_id → users.id
├─ version
├─ items (JSON)
├─ total_amount
├─ tax
├─ discount
├─ grand_total
├─ approved_snapshot (JSON)
├─ status
├─ notes
├─ approved_at
└─ created_at, updated_at

bookings
├─ ... (existing columns)
├─ refund_id
├─ refund_amount
├─ refunded_at
├─ refund_error
└─ campaign_started_at

pod_submissions
├─ id
├─ booking_id → bookings.id
├─ submitted_by → users.id (mounter)
├─ submission_date
├─ files (JSON)
├─ notes
├─ status
├─ approved_by → users.id
├─ approved_at
├─ approval_notes
├─ rejected_by → users.id
├─ rejected_at
├─ rejection_reason
└─ created_at, updated_at
```

---

## 🔌 API Reference

### Authentication
All endpoints require authentication via Sanctum:
```
Authorization: Bearer {api_token}
```

### Response Format
```json
{
  "success": true|false,
  "message": "Success/Error message",
  "data": { ... }
}
```

### Error Codes
- `400` - Validation error
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not found
- `500` - Server error

### Rate Limiting
- Default: 60 requests/minute per user
- Configurable in `config/sanctum.php`

---

## 🛠️ Service Layer

### Service Instantiation

**Option 1: Dependency Injection (Recommended)**
```php
class YourController extends Controller
{
    protected DirectBookingService $directBookingService;
    
    public function __construct(DirectBookingService $directBookingService)
    {
        $this->directBookingService = $directBookingService;
    }
}
```

**Option 2: App Helper**
```php
$service = app(DirectBookingService::class);
```

**Option 3: Manual Instantiation**
```php
$service = new DirectBookingService(
    app(SettingsService::class),
    app(BookingService::class)
);
```

### Service Dependencies

```php
DirectBookingService
├─ SettingsService (for admin configs)
└─ BookingService (for booking creation)

CampaignService
└─ PODSubmission (model)

OfferWorkflowService
└─ (no dependencies)

QuotationWorkflowService
└─ (no dependencies)

ThreadService
└─ Storage (for file uploads)
```

---

## 🎨 Frontend Integration

### Loading API Token
```javascript
const apiToken = localStorage.getItem('api_token');

// Or from meta tag
const apiToken = document.querySelector('meta[name="api-token"]').getAttribute('content');
```

### AJAX Request Template
```javascript
$.ajax({
    url: '/api/v1/customer/enquiries',
    method: 'GET',
    headers: {
        'Authorization': 'Bearer ' + apiToken,
        'Content-Type': 'application/json'
    },
    success: function(response) {
        if (response.success) {
            console.log(response.data);
        }
    },
    error: function(xhr) {
        console.error(xhr.responseJSON.message);
    }
});
```

### File Upload (FormData)
```javascript
const formData = new FormData();
formData.append('message', 'Hello');
formData.append('files[]', file1);
formData.append('files[]', file2);

$.ajax({
    url: '/api/v1/customer/threads/123/messages',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    headers: {
        'Authorization': 'Bearer ' + apiToken
    }
});
```

### Real-time Updates (Polling)
```javascript
let pollingInterval = setInterval(() => {
    loadNewMessages();
}, 5000); // 5 seconds

// Clear on page unload
$(window).on('beforeunload', function() {
    clearInterval(pollingInterval);
});
```

---

## ⚙️ Configuration

### Admin Settings (via SettingsService)

**Booking Settings:**
```php
'booking_hold_minutes' => 30,              // Payment hold duration
'grace_period_minutes' => 15,              // Buffer between bookings
'booking_min_duration_days' => 7,          // Minimum booking duration
'booking_max_duration_months' => 12,       // Maximum booking duration
'max_future_booking_start_months' => 12,   // Max future start date
'booking_tax_rate' => 18,                  // GST/Tax rate (%)
```

**To Update Settings:**
```php
app(SettingsService::class)->updateSetting('grace_period_minutes', 20);
```

### Environment Variables

**Razorpay Configuration:**
```env
RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxx
```

**Queue Configuration:**
```env
QUEUE_CONNECTION=database # or redis
```

### File Upload Limits
**In `php.ini`:**
```ini
upload_max_filesize = 50M
post_max_size = 50M
max_file_uploads = 10
```

**In Controller Validation:**
```php
'files.*' => 'file|max:51200' // 50MB in KB
```

---

## 🧪 Testing Guide

### API Testing with Postman

#### 1. Setup Environment Variables
```
base_url: http://localhost:8000/api/v1
api_token: {your_token}
```

#### 2. Test Direct Booking Flow
```
1. GET {{base_url}}/customer/direct-bookings/available-hoardings
   Headers: Authorization: Bearer {{api_token}}
   
2. POST {{base_url}}/customer/direct-bookings/check-availability
   Body: {
     "hoarding_id": 1,
     "start_date": "2025-12-15",
     "end_date": "2026-03-15"
   }
   
3. POST {{base_url}}/customer/direct-bookings
   Body: {
     "hoarding_id": 1,
     "start_date": "2025-12-15",
     "end_date": "2026-03-15",
     "customer_notes": "Test booking"
   }
   
4. POST {{base_url}}/customer/direct-bookings/{id}/initiate-payment
   
5. POST {{base_url}}/customer/direct-bookings/{id}/confirm-payment
   Body: {
     "razorpay_payment_id": "pay_xxxxx",
     "razorpay_order_id": "order_xxxxx",
     "razorpay_signature": "signature_xxxxx"
   }
```

#### 3. Test Enquiry-Offer-Quotation Flow
```
1. POST {{base_url}}/customer/enquiries
   Body: {
     "hoarding_id": 1,
     "preferred_start_date": "2025-12-15",
     "preferred_end_date": "2026-03-15",
     "message": "Interested in this location"
   }
   
2. GET {{base_url}}/customer/threads
   
3. POST {{base_url}}/vendor/threads/{threadId}/offers/create
   Body: {
     "price": 50000,
     "price_type": "monthly",
     "valid_days": 30,
     "description": "Premium offer",
     "message": "Please review our offer"
   }
   
4. POST {{base_url}}/customer/threads/{threadId}/offers/{offerId}/accept
   
5. POST {{base_url}}/vendor/threads/{threadId}/quotations/create
   Body: {
     "offer_id": 1,
     "items": [
       {"description": "Installation", "quantity": 1, "rate": 50000},
       {"description": "Maintenance", "quantity": 3, "rate": 5000}
     ],
     "tax_rate": 18,
     "discount": 5000,
     "notes": "Payment in 30 days"
   }
   
6. POST {{base_url}}/customer/threads/{threadId}/quotations/{quotationId}/approve
```

### Database Testing

**Check Migrations:**
```bash
php artisan migrate:status
```

**Rollback Last Migration:**
```bash
php artisan migrate:rollback --step=1
```

**Re-run Migrations:**
```bash
php artisan migrate:fresh --seed
```

### Queue Testing

**Check Queue Jobs:**
```bash
php artisan queue:failed
```

**Process Queue Manually:**
```bash
php artisan queue:work --once
```

**Retry Failed Job:**
```bash
php artisan queue:retry {job_id}
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. "Hoarding not available" Error
**Cause:** Overlapping bookings or grace period violation

**Solution:**
```php
// Check overlapping bookings
$overlapping = Booking::where('hoarding_id', $hoardingId)
    ->where(function($q) use ($startDate, $endDate) {
        $q->whereBetween('start_date', [$startDate, $endDate])
          ->orWhereBetween('end_date', [$startDate, $endDate]);
    })
    ->exists();

// Adjust grace period in settings
app(SettingsService::class)->updateSetting('grace_period_minutes', 0);
```

#### 2. Refund Job Fails
**Cause:** Razorpay API error or invalid payment ID

**Solution:**
```bash
# Check queue logs
tail -f storage/logs/laravel.log

# Retry failed job
php artisan queue:retry {job_id}

# Manual refund via Razorpay dashboard if needed
```

#### 3. File Upload Fails
**Cause:** File size exceeds limit or storage permission

**Solution:**
```bash
# Check storage permissions
chmod -R 775 storage/app/public

# Create symbolic link
php artisan storage:link

# Check php.ini limits
php -i | grep upload_max_filesize
```

#### 4. Thread Messages Not Loading
**Cause:** Incorrect authorization or missing relationships

**Solution:**
```php
// Check user role
$user = Auth::user();
dd($user->role);

// Check thread ownership
$thread = Thread::with(['customer', 'vendor'])->find($threadId);
dd($thread->customer_id, $thread->vendor_id, $user->id);
```

#### 5. Offer/Quotation Version Conflicts
**Cause:** Race condition in version increment

**Solution:**
```php
// Use database transaction
DB::transaction(function() {
    $maxVersion = Offer::where('enquiry_id', $enquiryId)
        ->where('vendor_id', $vendorId)
        ->lockForUpdate()
        ->max('version');
    
    $newVersion = ($maxVersion ?? 0) + 1;
    // Create offer...
});
```

### Debug Mode

**Enable Query Logging:**
```php
DB::enableQueryLog();
// ... your code ...
dd(DB::getQueryLog());
```

**Log Custom Messages:**
```php
Log::channel('daily')->info('Offer created', [
    'offer_id' => $offer->id,
    'version' => $offer->version
]);
```

---

## 🔧 Extension Points

### Adding New Payment Gateway

**Step 1:** Create Payment Service Interface
```php
interface PaymentGatewayInterface
{
    public function createOrder(array $data): array;
    public function capturePayment(string $paymentId): array;
    public function createRefund(string $paymentId, float $amount): array;
}
```

**Step 2:** Implement for New Gateway
```php
class StripePaymentService implements PaymentGatewayInterface
{
    public function createOrder(array $data): array { ... }
    public function capturePayment(string $paymentId): array { ... }
    public function createRefund(string $paymentId, float $amount): array { ... }
}
```

**Step 3:** Update Controller
```php
$paymentGateway = app($request->gateway === 'stripe' 
    ? StripePaymentService::class 
    : RazorpayService::class);
```

### Adding Email Notifications

**Step 1:** Create Notification Class
```php
php artisan make:notification EnquiryCreatedNotification
```

**Step 2:** Implement Notification
```php
class EnquiryCreatedNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Enquiry Received')
            ->line('You have received a new enquiry.')
            ->action('View Enquiry', url('/enquiries/'.$this->enquiry->id));
    }
}
```

**Step 3:** Dispatch from Service
```php
$vendor->notify(new EnquiryCreatedNotification($enquiry));
```

### Adding Real-time WebSocket (Pusher)

**Step 1:** Install Pusher
```bash
composer require pusher/pusher-php-server
```

**Step 2:** Configure in `.env`
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=xxxxx
PUSHER_APP_KEY=xxxxx
PUSHER_APP_SECRET=xxxxx
```

**Step 3:** Broadcast Event
```php
event(new MessageSent($message));
```

**Step 4:** Listen in Frontend
```javascript
Echo.private('thread.'+threadId)
    .listen('MessageSent', (e) => {
        appendMessage(e.message);
    });
```

### Adding Admin Dashboard Analytics

**Step 1:** Create Analytics Service
```php
class AnalyticsService
{
    public function getBookingStats(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_bookings' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_revenue' => Booking::whereBetween('created_at', [$startDate, $endDate])->sum('total_amount'),
            'avg_booking_value' => Booking::whereBetween('created_at', [$startDate, $endDate])->avg('total_amount'),
            'refund_rate' => $this->calculateRefundRate($startDate, $endDate),
        ];
    }
}
```

**Step 2:** Create Dashboard Controller
```php
class DashboardController extends Controller
{
    public function index(AnalyticsService $analytics)
    {
        $stats = $analytics->getBookingStats(now()->subMonth(), now());
        return view('admin.dashboard', compact('stats'));
    }
}
```

### Custom Validation Rules

**Step 1:** Create Rule Class
```php
php artisan make:rule AvailableHoardingRule
```

**Step 2:** Implement Rule
```php
class AvailableHoardingRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return app(DirectBookingService::class)->checkHoardingAvailability(
            $value['hoarding_id'],
            $value['start_date'],
            $value['end_date']
        )['available'];
    }
    
    public function message(): string
    {
        return 'The selected hoarding is not available for the chosen dates.';
    }
}
```

**Step 3:** Use in Controller
```php
$request->validate([
    'booking' => ['required', new AvailableHoardingRule()],
]);
```

---

## 📝 Code Style Guide

### Naming Conventions

**Controllers:** `{Feature}Controller.php`
- Example: `DirectBookingController`, `ThreadController`

**Services:** `{Feature}Service.php` or `{Feature}WorkflowService.php`
- Example: `DirectBookingService`, `OfferWorkflowService`

**Models:** Singular, PascalCase
- Example: `Thread`, `ThreadMessage`, `PODSubmission`

**Methods:**
- Services: `createDirectBooking()`, `sendOfferViaThread()`
- Controllers: `store()`, `show()`, `update()`, `destroy()`

**Variables:** camelCase
- Example: `$threadId`, `$offerVersion`, `$isMultiVendor`

### Documentation

**Service Method Template:**
```php
/**
 * Create a new offer version with snapshot
 *
 * @param array $data Must contain: enquiry_id, vendor_id, price, price_type
 * @return Offer
 * @throws \Exception If enquiry not found or validation fails
 */
public function createOfferVersion(array $data): Offer
{
    // Implementation...
}
```

### Error Handling

**Service Layer:**
```php
try {
    DB::beginTransaction();
    // ... operations ...
    DB::commit();
    return $result;
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Failed to create offer', [
        'error' => $e->getMessage(),
        'data' => $data,
    ]);
    throw $e;
}
```

**Controller Layer:**
```php
try {
    $offer = $this->offerService->createOfferVersion($data);
    return response()->json([
        'success' => true,
        'data' => $offer,
    ]);
} catch (\Exception $e) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
    ], 500);
}
```

---

## 📚 Additional Resources

### Official Documentation
- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [Razorpay API](https://razorpay.com/docs/api/)
- [Sanctum Auth](https://laravel.com/docs/11.x/sanctum)
- [Laravel Queues](https://laravel.com/docs/11.x/queues)

### Related Files
- `config/settings.php` - Admin settings configuration
- `routes/api_v1/direct-bookings.php` - Direct booking routes
- `routes/api_v1/enquiry-workflow.php` - Thread workflow routes
- `app/Models/Booking.php` - Booking model with relationships

### Support
For questions or issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check queue logs: `php artisan queue:failed`
3. Review database migrations: `php artisan migrate:status`
4. Contact development team

---

**Document Version:** 1.0  
**Last Updated:** December 8, 2025  
**Maintained By:** Development Team
