# Hoarding-First Booking Flow - API Documentation

## Overview

The Hoarding-First Booking Flow implements a customer-centric booking process that starts from a hoarding card and progresses through package selection, date selection, review, and payment. This flow is designed according to the Customer Web Figma design (PROMPT 43).

## Architecture

### Key Components

1. **BookingDraft Model**: Temporary storage for in-progress bookings
2. **HoardingBookingService**: Business logic orchestration service
3. **BookingFlowController**: API endpoints for frontend integration
4. **RazorpayService**: Payment processing integration
5. **DynamicPriceCalculator**: Price calculation and freezing

### Flow Steps

```
Step 1: Hoarding Details → Step 2: Package Selection → Step 3: Date Selection 
    → Step 4: Draft Creation → Step 5: Review Summary 
    → Step 6: Booking Confirmation & Lock → Step 7: Payment Session 
    → Step 8: Payment Callback & Finalization
```

## API Endpoints

All endpoints require authentication with `auth:sanctum` middleware and `role:customer` guard.

Base URL: `/api/v1`

---

### Step 1: Get Hoarding Details

Fetch complete hoarding information including vendor details, availability calendar, and booking rules.

**Endpoint:** `GET /booking/hoarding/{id}`

**Parameters:**
- `id` (path): Hoarding ID

**Response:**
```json
{
    "success": true,
    "data": {
        "hoarding": {
            "id": 1,
            "title": "MG Road Premium Billboard",
            "description": "High-visibility location...",
            "type": "billboard",
            "width": 20,
            "height": 10,
            "dimensions_unit": "feet",
            "monthly_price": 50000,
            "weekly_price": 15000,
            "location": {
                "address": "MG Road, Bangalore",
                "city": "Bangalore",
                "state": "Karnataka",
                "pincode": "560001",
                "latitude": 12.9716,
                "longitude": 77.5946
            },
            "images": ["url1", "url2"],
            "rating": 4.5,
            "is_active": true
        },
        "vendor": {
            "id": 5,
            "name": "ABC Outdoors",
            "email": "vendor@example.com",
            "phone": "9876543210",
            "rating": 4.7
        },
        "availability": {
            "booked_periods": [
                {
                    "start_date": "2025-01-15",
                    "end_date": "2025-02-14",
                    "booking_id": 123
                }
            ],
            "maintenance_periods": [],
            "hold_periods": [
                {
                    "start_date": "2025-03-01",
                    "end_date": "2025-03-31",
                    "expires_at": "2025-12-10T14:30:00Z"
                }
            ],
            "available_from": "2025-12-10",
            "available_until": "2026-12-10"
        },
        "booking_rules": {
            "min_booking_duration_days": 7,
            "max_booking_duration_days": 365,
            "min_advance_booking_days": 2,
            "max_advance_booking_days": 365,
            "weekly_booking_enabled": true,
            "hold_duration_minutes": 30
        }
    }
}
```

**Error Responses:**
- `400`: Hoarding not found or not active

---

### Step 2: Get Available Packages

Fetch DOOH packages and standard booking packages for the hoarding.

**Endpoint:** `GET /booking/hoarding/{id}/packages`

**Parameters:**
- `id` (path): Hoarding ID

**Response:**
```json
{
    "success": true,
    "data": {
        "dooh_packages": [
            {
                "id": 10,
                "name": "Prime Time Package",
                "description": "Best visibility hours",
                "slot_count": 10,
                "duration_seconds": 10,
                "price_per_slot": 500,
                "discount_percentage": 15,
                "min_booking_months": 1,
                "max_booking_months": 12,
                "features": ["HD Display", "Analytics", "Remote Update"],
                "offer_tag": "15% OFF"
            }
        ],
        "standard_packages": [
            {
                "type": "monthly",
                "name": "Monthly Booking",
                "price": 50000,
                "description": "Full month booking"
            },
            {
                "type": "weekly",
                "name": "Weekly Booking",
                "price": 15000,
                "description": "7-day booking"
            }
        ]
    }
}
```

**Error Responses:**
- `400`: Hoarding not found

---

### Step 3: Validate Date Selection

Validate selected dates against availability, booking rules, and package constraints.

**Endpoint:** `POST /booking/validate-dates`

**Request Body:**
```json
{
    "hoarding_id": 1,
    "start_date": "2025-12-15",
    "end_date": "2026-01-14",
    "package_id": 10  // Optional for DOOH bookings
}
```

**Validation Rules:**
- `hoarding_id`: required, exists in hoardings
- `start_date`: required, date, after_or_equal:today
- `end_date`: required, date, after:start_date
- `package_id`: nullable, exists in dooh_packages

**Response:**
```json
{
    "success": true,
    "data": {
        "valid": true,
        "start_date": "2025-12-15",
        "end_date": "2026-01-14",
        "duration_days": 31,
        "duration_type": "months",
        "message": "Dates are available for booking"
    }
}
```

**Error Responses:**
```json
{
    "success": false,
    "valid": false,
    "message": "Selected dates overlap with existing booking from 2025-12-20 to 2025-12-30"
}
```

**Validation Failures:**
- Start date in the past
- Duration below minimum or above maximum
- Advance booking outside allowed window
- Dates overlap with existing bookings
- Package constraints violated (min/max months)

---

### Step 4: Create or Update Draft

Create a new booking draft or update existing one. Drafts auto-expire after 30 minutes (configurable).

**Endpoint:** `POST /booking/draft`

**Request Body:**
```json
{
    "hoarding_id": 1,
    "package_id": 10,  // Optional
    "start_date": "2025-12-15",
    "end_date": "2026-01-14",
    "coupon_code": "WELCOME10"  // Optional
}
```

**Response:**
```json
{
    "success": true,
    "message": "Draft booking created/updated successfully",
    "data": {
        "draft_id": 456,
        "step": "dates_selected",
        "hoarding": {
            "id": 1,
            "title": "MG Road Premium Billboard"
        },
        "package": {
            "id": 10,
            "name": "Prime Time Package"
        },
        "dates": {
            "start_date": "2025-12-15",
            "end_date": "2026-01-14",
            "duration_days": 31,
            "duration_type": "months"
        },
        "pricing": {
            "base_price": 50000,
            "discount_amount": 7500,
            "gst_amount": 7650,
            "total_amount": 50150,
            "price_snapshot": {
                "base_price": 50000,
                "discount_applied": 7500,
                "vendor_offer_applied": 0,
                "gst": 7650,
                "final_price": 50150
            },
            "applied_offers": [
                {
                    "coupon_code": "WELCOME10",
                    "discount_type": "percentage",
                    "discount_value": 10,
                    "discount_amount": 5000
                }
            ]
        },
        "expires_at": "2025-12-10T14:30:00Z"
    }
}
```

**Notes:**
- Price is frozen at draft creation using DynamicPriceCalculator
- Draft automatically expires after 30 minutes (refreshed on each update)
- System finds existing active draft for same customer+hoarding or creates new
- Price snapshot ensures customer pays the quoted amount even if prices change

---

### Step 5: Get Draft Details

Retrieve full details of a specific draft (for resuming sessions).

**Endpoint:** `GET /booking/draft/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 456,
        "step": "dates_selected",
        "hoarding": { /* full hoarding details */ },
        "package": { /* package details if selected */ },
        "dates": { /* booking dates */ },
        "pricing": { /* complete pricing breakdown */ },
        "expires_at": "2025-12-10T14:30:00Z",
        "created_at": "2025-12-10T13:00:00Z"
    }
}
```

**Error Responses:**
- `404`: Draft not found or doesn't belong to customer
- `410 Gone`: Draft has expired

---

### Step 6: Get Review Summary

Get complete summary for review screen before payment.

**Endpoint:** `GET /booking/draft/{id}/review`

**Response:**
```json
{
    "success": true,
    "data": {
        "draft_id": 456,
        "hoarding": {
            "id": 1,
            "title": "MG Road Premium Billboard",
            "location": "MG Road, Bangalore",
            "type": "billboard",
            "image": "url"
        },
        "package": {
            "id": 10,
            "name": "Prime Time Package",
            "features": ["HD Display", "Analytics"]
        },
        "booking_period": {
            "start_date": "15 Dec 2025",
            "end_date": "14 Jan 2026",
            "duration_days": 31,
            "duration_display": "1 month and 1 day"
        },
        "pricing": {
            "base": 50000,
            "discount": 7500,
            "gst": 7650,
            "total": 50150,
            "snapshot": { /* immutable price record */ },
            "applied_offers": [ /* list of offers */ ]
        },
        "expires_at": "2025-12-10T14:30:00Z",
        "hold_duration_minutes": 30
    }
}
```

**Error Responses:**
- `400`: Draft incomplete (missing dates or pricing)
- `410 Gone`: Draft expired

---

### Step 7: Confirm Booking & Lock Inventory

Convert draft to booking with temporary hold on inventory.

**Endpoint:** `POST /booking/draft/{id}/confirm`

**Response:**
```json
{
    "success": true,
    "message": "Booking confirmed and inventory locked",
    "data": {
        "booking_id": 789,
        "status": "pending_payment_hold",
        "hold_expires_at": "2025-12-10T14:30:00Z",
        "total_amount": 50150
    }
}
```

**Process:**
- Final availability check (prevents race conditions)
- Creates Booking with `STATUS_PENDING_PAYMENT_HOLD`
- Sets hold expiry (30 minutes default)
- Copies price snapshot to booking
- Marks draft as converted

**Error Responses:**
- `400`: Draft expired, already converted, or dates no longer available
- `410 Gone`: Draft expired

---

### Step 8: Create Payment Session

Create Razorpay order for payment processing.

**Endpoint:** `POST /booking/{id}/create-payment`

**Response:**
```json
{
    "success": true,
    "data": {
        "order_id": "order_XXXXXXXXXXXXX",
        "amount": 5015000,  // Paise (₹50,150)
        "currency": "INR",
        "key": "rzp_test_XXXXXXXXXXXX",
        "booking": {
            "id": 789,
            "hoarding_title": "MG Road Premium Billboard",
            "start_date": "2025-12-15",
            "end_date": "2026-01-14"
        },
        "customer": {
            "name": "John Doe",
            "email": "john@example.com",
            "phone": "9876543210"
        },
        "hold_expires_at": "2025-12-10T14:30:00Z",
        "hold_expires_in_seconds": 1800
    }
}
```

**Frontend Integration:**
```javascript
const options = {
    key: response.data.key,
    amount: response.data.amount,
    currency: response.data.currency,
    order_id: response.data.order_id,
    name: "OohApp",
    description: `Booking for ${response.data.booking.hoarding_title}`,
    prefill: {
        name: response.data.customer.name,
        email: response.data.customer.email,
        contact: response.data.customer.phone
    },
    handler: function(razorpayResponse) {
        // Call payment callback endpoint
        handlePaymentCallback(razorpayResponse);
    },
    modal: {
        ondismiss: function() {
            // Handle payment cancellation
        }
    }
};

const rzp = new Razorpay(options);
rzp.open();
```

**Error Responses:**
- `400`: Booking not in correct status or hold expired
- `404`: Booking not found

---

### Step 9: Payment Callback Handler

Process successful payment from Razorpay.

**Endpoint:** `POST /booking/payment/callback`

**Request Body:**
```json
{
    "razorpay_order_id": "order_XXXXXXXXXXXXX",
    "razorpay_payment_id": "pay_YYYYYYYYYYYYY",
    "razorpay_signature": "signature_hash"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Payment successful! Booking confirmed.",
    "data": {
        "booking_id": 789,
        "status": "confirmed",
        "payment_id": "pay_YYYYYYYYYYYYY",
        "confirmed_at": "2025-12-10T14:15:00Z"
    }
}
```

**Process:**
1. Verify signature with Razorpay
2. Update booking with payment authorization
3. Capture payment amount
4. On success: Mark booking as confirmed
5. Trigger notifications (customer, vendor, admin)
6. Create timeline event

**Error Responses:**
- `400`: Invalid signature or payment details
- `500`: Payment capture failed

---

### Step 10: Payment Failure Handler

Record payment failure (allows retry within hold window).

**Endpoint:** `POST /booking/payment/failed`

**Request Body:**
```json
{
    "razorpay_order_id": "order_XXXXXXXXXXXXX",
    "error_code": "BAU00",
    "error_description": "Insufficient funds"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Payment failed. You can retry.",
    "data": {
        "booking_id": 789,
        "can_retry": true,
        "hold_expires_at": "2025-12-10T14:30:00Z"
    }
}
```

**Notes:**
- Does NOT cancel booking immediately
- Customer can retry payment within hold window
- Booking auto-cancels when hold expires

---

### Additional Endpoints

#### Get My Drafts

List all active drafts for logged-in customer.

**Endpoint:** `GET /booking/my-drafts`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 456,
            "hoarding": {
                "id": 1,
                "title": "MG Road Premium Billboard",
                "image": "url"
            },
            "step": "dates_selected",
            "dates": {
                "start_date": "2025-12-15",
                "end_date": "2026-01-14"
            },
            "total_amount": 50150,
            "expires_at": "2025-12-10T14:30:00Z",
            "updated_at": "2025-12-10T13:00:00Z"
        }
    ]
}
```

#### Delete Draft

Abandon a draft booking.

**Endpoint:** `DELETE /booking/draft/{id}`

**Response:**
```json
{
    "success": true,
    "message": "Draft deleted successfully"
}
```

---

## Database Schema

### booking_drafts Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint unsigned | Primary key |
| customer_id | bigint unsigned | FK to users |
| hoarding_id | bigint unsigned | FK to hoardings |
| package_id | bigint unsigned | FK to dooh_packages (nullable) |
| start_date | date | Booking start date |
| end_date | date | Booking end date |
| duration_days | int | Calculated duration |
| duration_type | enum | days/weeks/months |
| price_snapshot | json | Immutable price record |
| base_price | decimal(12,2) | Base hoarding price |
| discount_amount | decimal(12,2) | Total discounts |
| gst_amount | decimal(12,2) | GST amount |
| total_amount | decimal(12,2) | Final payable amount |
| applied_offers | json | Offers and coupons |
| coupon_code | varchar | Applied coupon |
| step | enum | Current flow step |
| last_updated_step_at | timestamp | Step update time |
| session_id | varchar | Browser session ID |
| expires_at | timestamp | Auto-expiry time |
| is_converted | boolean | Converted to booking |
| booking_id | bigint unsigned | Created booking ID |
| converted_at | timestamp | Conversion time |
| timestamps | | created_at, updated_at |
| soft_deletes | | deleted_at |

**Indexes:**
- `(customer_id, is_converted)`
- `(hoarding_id, start_date, end_date)`
- `expires_at`
- `session_id`

---

## Business Logic

### Price Freezing

When dates are set in a draft, the system:
1. Calls `DynamicPriceCalculator->calculate()`
2. Stores complete result in `price_snapshot` (JSON)
3. Extracts key amounts to dedicated columns
4. Creates immutable record

**Benefits:**
- Customer sees consistent pricing
- Prevents price manipulation
- Audit trail for pricing disputes
- Handles temporary offers correctly

### Draft Expiry System

**Default Expiry:** 30 minutes (configurable in settings)

**Refresh Behavior:**
- Every draft update refreshes expiry
- Price recalculation does NOT change expiry
- Customer can safely review without time pressure

**Cleanup:**
- Scheduled job runs hourly: `cleanupExpiredDrafts()`
- Soft deletes expired drafts
- Maintains audit trail

### Hold Mechanism

**Purpose:** Reserve inventory during payment process

**Duration:** 30 minutes (configurable in settings)

**Process:**
1. Booking created with `STATUS_PENDING_PAYMENT_HOLD`
2. Hold expiry set to now + hold_duration
3. Customer has limited time to complete payment
4. On expiry: Booking auto-cancelled by scheduled job

**Hold Release:**
- Scheduled job runs every minute: `releaseExpiredHolds()`
- Cancels bookings with expired holds
- Frees inventory for other customers

### Availability Checking

**Checked Periods:**
1. **Confirmed Bookings:** Status = confirmed/payment_hold/pending
2. **Active Holds:** Temporary locks not yet expired
3. **Maintenance Periods:** (Future implementation)

**Overlap Detection:**
```sql
SELECT * FROM bookings 
WHERE hoarding_id = ? 
AND status IN ('confirmed', 'payment_hold', 'pending')
AND NOT (end_date < ? OR start_date > ?)
```

### Booking Rules Validation

**System Settings:**
- `min_booking_duration_days`: Minimum booking length
- `max_booking_duration_days`: Maximum booking length
- `min_advance_booking_days`: How far in advance required
- `max_advance_booking_days`: Maximum future booking
- `booking_hold_duration_minutes`: Payment time limit
- `draft_expiry_minutes`: Draft auto-expiry

**Hoarding-Specific:**
- `weekly_booking_enabled`: Allow 7-day bookings
- `weekly_price`: Price for weekly bookings

**Package Constraints:**
- `min_booking_months`: Minimum for DOOH packages
- `max_booking_months`: Maximum for DOOH packages

---

## Error Handling

### Common Error Codes

| Code | Message | Resolution |
|------|---------|------------|
| 400 | Validation failed | Check request parameters |
| 404 | Resource not found | Verify IDs are correct |
| 410 | Draft expired | Create new draft |
| 422 | Dates not available | Choose different dates |
| 500 | Server error | Contact support |

### Error Response Format

```json
{
    "success": false,
    "message": "Human-readable error message",
    "error": "ERROR_CODE",
    "details": {
        "field": "specific field error"
    }
}
```

---

## Scheduled Jobs

### Cleanup Expired Drafts

**Frequency:** Hourly  
**Command:** `php artisan schedule:work`  
**Logic:** `HoardingBookingService::cleanupExpiredDrafts()`

```php
$schedule->call(function () {
    app(HoardingBookingService::class)->cleanupExpiredDrafts();
})->hourly();
```

### Release Expired Holds

**Frequency:** Every minute  
**Command:** `php artisan schedule:work`  
**Logic:** `HoardingBookingService::releaseExpiredHolds()`

```php
$schedule->call(function () {
    app(HoardingBookingService::class)->releaseExpiredHolds();
})->everyMinute();
```

---

## Settings Configuration

### Required Settings

Add these to `settings` table:

```php
[
    'key' => 'min_booking_duration_days',
    'value' => '7',
    'type' => 'integer',
    'description' => 'Minimum booking duration in days'
],
[
    'key' => 'max_booking_duration_days',
    'value' => '365',
    'type' => 'integer',
    'description' => 'Maximum booking duration in days'
],
[
    'key' => 'min_advance_booking_days',
    'value' => '2',
    'type' => 'integer',
    'description' => 'Minimum days in advance for booking'
],
[
    'key' => 'max_advance_booking_days',
    'value' => '365',
    'type' => 'integer',
    'description' => 'Maximum days in advance for booking'
],
[
    'key' => 'booking_hold_duration_minutes',
    'value' => '30',
    'type' => 'integer',
    'description' => 'Payment hold duration in minutes'
],
[
    'key' => 'draft_expiry_minutes',
    'value' => '30',
    'type' => 'integer',
    'description' => 'Draft auto-expiry duration in minutes'
]
```

---

## Testing Checklist

### Unit Tests
- [ ] Draft model scopes and methods
- [ ] Price freezing calculation
- [ ] Expiry logic
- [ ] Date validation

### Integration Tests
- [ ] Complete booking flow (Steps 1-8)
- [ ] Draft creation and updates
- [ ] Price consistency across updates
- [ ] Availability checking accuracy
- [ ] Payment flow with Razorpay sandbox

### Edge Cases
- [ ] Concurrent booking attempts (race conditions)
- [ ] Draft expiry during checkout
- [ ] Hold expiry during payment
- [ ] Price changes after draft creation
- [ ] Multiple drafts per customer
- [ ] Payment retry scenarios

---

## Frontend Integration Guide

### State Management

```javascript
const bookingFlow = {
    draftId: null,
    currentStep: 'hoarding',
    hoarding: null,
    selectedPackage: null,
    selectedDates: null,
    pricing: null,
    expiresAt: null
};
```

### Step Progression

```javascript
// Step 1: Load hoarding
const hoarding = await api.get(`/booking/hoarding/${hoardingId}`);

// Step 2: Get packages
const packages = await api.get(`/booking/hoarding/${hoardingId}/packages`);

// Step 3: Validate dates
const validation = await api.post('/booking/validate-dates', {
    hoarding_id: hoardingId,
    start_date: startDate,
    end_date: endDate,
    package_id: selectedPackageId
});

// Step 4: Create/update draft
const draft = await api.post('/booking/draft', {
    hoarding_id: hoardingId,
    package_id: selectedPackageId,
    start_date: startDate,
    end_date: endDate
});

// Step 5: Review
const review = await api.get(`/booking/draft/${draftId}/review`);

// Step 6: Confirm
const booking = await api.post(`/booking/draft/${draftId}/confirm`);

// Step 7: Create payment
const payment = await api.post(`/booking/${bookingId}/create-payment`);

// Step 8: Initialize Razorpay
initializeRazorpay(payment.data);
```

### Expiry Timer

```javascript
function startExpiryCountdown(expiresAt) {
    const interval = setInterval(() => {
        const now = new Date();
        const expiry = new Date(expiresAt);
        const remaining = expiry - now;
        
        if (remaining <= 0) {
            clearInterval(interval);
            showExpiryMessage();
        } else {
            updateCountdownDisplay(remaining);
        }
    }, 1000);
}
```

---

## Security Considerations

### Authentication
- All endpoints require `auth:sanctum` middleware
- Customer role verification on each request
- Ownership validation for drafts and bookings

### Data Validation
- Server-side validation on all inputs
- Date range checks prevent invalid bookings
- Amount verification before payment

### Payment Security
- Razorpay signature verification
- Manual capture mode for better control
- Payment failure tracking

### Race Condition Prevention
- Final availability check before booking creation
- Database transactions for critical operations
- Unique constraints on date ranges

---

## Troubleshooting

### Draft Not Found (404)
**Cause:** Draft expired or deleted  
**Solution:** Create new draft from Step 1

### Dates Not Available (422)
**Cause:** Overlap with existing booking  
**Solution:** Choose different dates or hoarding

### Payment Failed
**Cause:** Various Razorpay errors  
**Solution:** Retry within hold window or contact support

### Hold Expired
**Cause:** Payment took longer than 30 minutes  
**Solution:** Start new booking from draft or Step 1

---

## Future Enhancements

- [ ] Maintenance periods blocking
- [ ] Email notifications on each step
- [ ] SMS notifications for important events
- [ ] Draft sharing via link
- [ ] Booking modifications (date changes)
- [ ] Partial refunds for cancellations
- [ ] Booking history timeline
- [ ] Analytics dashboard for customers

---

## Support

For technical issues or questions:
- Email: tech@oohapp.com
- Documentation: https://docs.oohapp.com
- API Status: https://status.oohapp.com

---

**Version:** 1.0  
**Last Updated:** December 9, 2025  
**Author:** OohApp Development Team
