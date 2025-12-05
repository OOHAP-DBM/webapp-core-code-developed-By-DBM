# Commission & Payout System - Implementation Guide

## Overview
Complete financial tracking system for OohApp platform commission calculations and vendor payout management.

## System Components

### 1. Database Tables

#### `booking_payments`
Tracks all financial transactions for bookings
- **gross_amount**: Total amount paid by customer
- **admin_commission_amount**: Platform commission (default 15%)
- **vendor_payout_amount**: Amount to be paid to vendor
- **pg_fee_amount**: Payment gateway fees (default 2%)
- **vendor_payout_status**: `pending` | `processing` | `completed` | `failed` | `on_hold`
- **payout_mode**: `bank_transfer` | `razorpay_transfer` | `upi` | `cheque` | `manual`
- **payout_reference**: Transaction reference for completed payouts
- **paid_at**: Timestamp when payout was completed
- **razorpay_payment_id**, **razorpay_order_id**: Razorpay tracking
- **metadata**: JSON field for additional details

#### `commission_logs`
Immutable audit trail for commission calculations (no `updated_at`)
- **gross_amount**: Base amount for calculation
- **admin_commission**: Calculated platform commission
- **vendor_payout**: Calculated vendor payout
- **pg_fee**: Payment gateway fees
- **tax**: Tax on commission (if applicable)
- **commission_rate**: Rate used (e.g., 15.00)
- **calculation_snapshot**: Complete JSON snapshot of calculation

### 2. Models

#### `BookingPayment`
```php
// Scopes
BookingPayment::pendingPayout()->get()
BookingPayment::completedPayout()->get()
BookingPayment::onHold()->get()
BookingPayment::captured()->get()

// Helper Methods
$payment->isPayoutPending()
$payment->isPayoutCompleted()
$payment->isPayoutOnHold()

// Calculated Attributes
$payment->net_platform_revenue  // commission - PG fees
$payment->commission_percentage
$payment->payment_summary

// Actions
$payment->markPayoutCompleted('bank_transfer', 'TXN123456', ['notes' => '...'])
$payment->markPayoutOnHold('Vendor verification pending')
```

#### `CommissionLog`
```php
// Calculated Attributes
$log->total_deductions  // commission + PG fee + tax
$log->net_vendor_payout
$log->summary

// Verification
$result = $log->verifyCalculation()
// Returns: ['valid' => true/false, 'expected' => ..., 'actual' => ..., 'difference' => ...]
```

#### `Booking`
New relationships added:
```php
$booking->bookingPayment  // hasOne
$booking->commissionLog   // hasOne
```

### 3. Service Layer

#### `CommissionService`
**Configuration (via Settings model):**
- `platform_commission_rate`: Default 15.00
- `payment_gateway_fee_rate`: Default 2.00
- `commission_tax_rate`: Default 0.00

**Main Methods:**

```php
// Calculate and record commission (called automatically on payment capture)
[$bookingPayment, $commissionLog] = $commissionService->calculateAndRecord(
    $booking, 
    $razorpayPaymentId, 
    $razorpayOrderId
);

// Get aggregate statistics for date range
$stats = $commissionService->getCommissionStats('2025-01-01', '2025-01-31');
// Returns: total_transactions, gross_amount, total_commission, total_payouts, 
//          total_pg_fees, total_tax, avg_commission_rate, net_platform_revenue

// Get all pending vendor payouts
$pendingPayouts = $commissionService->getPendingPayouts();

// Get vendor-specific payout summary
$summary = $commissionService->getVendorPayoutSummary($vendorId);
// Returns: vendor_id, pending_amount, completed_amount, total_amount, pending_count, completed_count
```

### 4. Event Integration

#### `OnPaymentCaptured` Listener
Automatically records commission when payment is captured:
```php
// Flow:
1. Payment captured via webhook
2. OnPaymentCaptured listener triggered
3. CommissionService->calculateAndRecord() called
4. BookingPayment + CommissionLog created
5. ScheduleBookingConfirmJob dispatched
```

### 5. Admin Interface

#### Web Route
```
GET /admin/finance/bookings-payments
```

**Features:**
- Summary cards: Total revenue, platform commission, pending payouts, completed payouts
- Filters: Payout status, date range
- Sortable payments ledger table
- Per-payment actions: View details, mark paid, put on hold

**Modals:**
1. **Payment Details**: Full breakdown with Razorpay IDs, calculated amounts, payout info
2. **Mark Paid**: Form to record completed payout (mode, reference, notes)
3. **Hold Payout**: Prompt for reason to put payout on hold

### 6. API Endpoints

All endpoints require `auth:sanctum` and `role:admin` middleware.

#### View Payment Details
```
GET /api/v1/admin/booking-payments/{id}
Response: {success, data: {BookingPayment with relations}}
```

#### Mark Payout as Paid
```
POST /api/v1/admin/booking-payments/{id}/mark-paid
Body: {
  payout_mode: "bank_transfer|razorpay_transfer|upi|cheque|manual",
  payout_reference: "TXN123456",
  notes: "Optional notes"
}
Response: {success, message, data: {updated BookingPayment}}
```

#### Hold Payout
```
POST /api/v1/admin/booking-payments/{id}/hold
Body: {reason: "Reason for hold"}
Response: {success, message, data: {updated BookingPayment}}
```

#### Commission Statistics
```
GET /api/v1/admin/commission-stats?start_date=2025-01-01&end_date=2025-01-31
Response: {success, data: {stats}, date_range}
```

#### Pending Payouts
```
GET /api/v1/admin/pending-payouts
Response: {success, data: [payments], total_pending_amount, count}
```

#### Vendor Payout Summary
```
GET /api/v1/admin/vendors/{vendorId}/payout-summary
Response: {success, data: {vendor summary}}
```

## Commission Calculation Flow

### Formula
```
Gross Amount = Customer Payment (booking.total_amount)
Admin Commission = Gross × (commission_rate / 100)  [default: 15%]
PG Fee = Gross × (pg_fee_rate / 100)  [default: 2%]
Vendor Payout = Gross - Admin Commission - PG Fee
```

### Example (₹10,000 booking):
- **Gross Amount**: ₹10,000
- **Admin Commission (15%)**: ₹1,500
- **PG Fee (2%)**: ₹200
- **Vendor Payout**: ₹8,300
- **Net Platform Revenue**: ₹1,300 (₹1,500 - ₹200)

### Configurable Rates
Update via Settings model:
```php
// In SettingsSeeder or admin panel
Setting::create([
    'key' => 'platform_commission_rate',
    'value' => '15.00',
    'type' => 'float',
    'description' => 'Platform commission rate in percentage',
    'group' => 'finance',
]);

Setting::create([
    'key' => 'payment_gateway_fee_rate',
    'value' => '2.00',
    'type' => 'float',
    'description' => 'Payment gateway fee rate in percentage',
    'group' => 'finance',
]);

Setting::create([
    'key' => 'commission_tax_rate',
    'value' => '0.00',
    'type' => 'float',
    'description' => 'Tax rate on commission in percentage',
    'group' => 'finance',
]);
```

## Payout Management Workflow

### 1. Automatic Recording
When Razorpay payment is captured:
```
Customer Payment → Razorpay Webhook → OnPaymentCaptured
    → CommissionService.calculateAndRecord()
    → BookingPayment (status: 'pending')
    → CommissionLog (immutable audit record)
```

### 2. Admin Review
Admin accesses finance dashboard:
```
/admin/finance/bookings-payments
```
- Views pending payouts
- Reviews commission breakdown
- Checks vendor details

### 3. Manual Payout Processing
Admin marks payout as paid:
```
1. Click "Mark Paid" button
2. Select payout mode (bank_transfer, UPI, etc.)
3. Enter payout reference (transaction ID)
4. Add optional notes
5. Confirm → API call → BookingPayment updated
   - vendor_payout_status: 'completed'
   - paid_at: current timestamp
   - payout_mode, payout_reference, metadata saved
```

### 4. Hold Processing
If verification needed:
```
1. Click "Hold Payout" button
2. Enter reason for hold
3. Confirm → API call → BookingPayment updated
   - vendor_payout_status: 'on_hold'
   - reason saved in metadata
```

## Testing Checklist

### ✅ Database Setup
- [x] Migrations executed successfully
- [x] Indexes created for performance
- [x] Foreign keys properly set up

### ✅ Models & Relationships
- [x] BookingPayment model with scopes and methods
- [x] CommissionLog model with verification
- [x] Booking relationships added

### ✅ Service Layer
- [x] CommissionService with configurable rates
- [x] Calculation logic with proper rounding
- [x] Transaction safety for data consistency

### ✅ Event Integration
- [x] OnPaymentCaptured calls commission service
- [x] Auto-creation on payment capture

### ✅ API Endpoints
- [x] FinanceController created with 6 methods
- [x] Routes added to api_v1/admin.php
- [x] Validation on inputs
- [x] Proper error handling

### ✅ Admin UI
- [x] Blade view with summary cards
- [x] Filters and sortable table
- [x] AJAX modals for actions
- [x] Responsive design with Bootstrap 5

### 🔄 Integration Testing (TODO)
- [ ] Create test booking
- [ ] Capture payment via webhook
- [ ] Verify BookingPayment and CommissionLog created
- [ ] Check calculations are correct
- [ ] Mark payout as paid via UI
- [ ] Verify status updated correctly

## Audit Trail & Compliance

### Immutable Records
`CommissionLog` has no `updated_at` - once created, records cannot be modified. This ensures:
- Complete audit trail for accounting
- Compliance with financial regulations
- Dispute resolution evidence
- Historical commission rate tracking

### Metadata Storage
Both tables use JSON `metadata` fields for:
- Calculation snapshots
- Configuration at time of transaction
- Admin actions (who marked paid, when, why)
- Any additional context needed for audits

### Verification Method
```php
$log->verifyCalculation()
```
Checks if stored vendor payout matches calculated value (allows 1 paisa tolerance for rounding).

## Troubleshooting

### Commission Not Recorded
1. Check if `OnPaymentCaptured` listener is registered
2. Verify webhook is hitting the endpoint
3. Check logs for `CommissionService` errors
4. Ensure Settings values are numeric

### Incorrect Calculations
1. Verify Settings rates are correct
2. Check `calculation_snapshot` in CommissionLog
3. Run `verifyCalculation()` on CommissionLog
4. Check for rounding issues (should be 2 decimals)

### Payout Status Not Updating
1. Check admin has proper role (`role:admin`)
2. Verify API endpoints are accessible
3. Check browser console for AJAX errors
4. Verify CSRF token if using web routes

## Future Enhancements

### Potential Features
- [ ] Bulk payout processing (select multiple, mark all paid)
- [ ] Razorpay Transfers API integration for automatic payouts
- [ ] Vendor payout notifications (email/SMS when marked paid)
- [ ] Commission reports export (CSV/PDF)
- [ ] Vendor-facing payout history page
- [ ] Automated reconciliation with bank statements
- [ ] Tiered commission rates based on vendor performance
- [ ] Refund handling with proportional commission adjustments

### Configuration Options
- [ ] Per-vendor custom commission rates
- [ ] Category-based commission (different rates for hoardings vs DOOH)
- [ ] Minimum payout threshold
- [ ] Scheduled automatic payout runs
- [ ] Multi-currency support

---

**Last Updated**: December 5, 2025  
**Version**: 1.0.0  
**Status**: Implementation Complete, Testing Pending
