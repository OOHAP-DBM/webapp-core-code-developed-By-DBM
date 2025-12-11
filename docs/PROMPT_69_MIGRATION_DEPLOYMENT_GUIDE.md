# Payment Gateway Integration - Migration & Deployment Guide
## PROMPT 69: Phase 2 Complete

## Overview

This guide explains how to deploy the new PaymentService integration to your production environment and migrate existing payment data.

---

## Phase 2: What Was Integrated

### Files Modified

1. **app/Services/Gateways/RazorpayGateway.php** (NEW)
   - Implements PaymentGatewayInterface
   - Wraps existing RazorpayService methods

2. **app/Http/Controllers/Customer/BookingFlowController.php**
   - Replaced `RazorpayService` with `PaymentService`
   - Creates `PaymentTransaction` records for all orders
   - Updated payment capture flow

3. **app/Services/BookingCancellationService.php**
   - Replaced `RazorpayService` with `PaymentService`
   - Refunds now tracked in `payment_transactions` table

4. **app/Http/Controllers/Api/RazorpayWebhookController.php**
   - Delegates payment webhooks to `PaymentService`
   - Kept account webhooks for vendor payouts

5. **app/Services/PaymentService.php**
   - Updated to only use RazorpayGateway (Stripe in Phase 3)

---

## Deployment Steps

### Step 1: Pull Latest Code

```bash
git pull origin master
```

**Commits Included:**
- `93cb09e`: Phase 1 (Core Architecture)
- `54fbc65`: Documentation
- `9f056cb`: Phase 2 (Integration)

---

### Step 2: Run Database Migration

```bash
php artisan migrate
```

**Migration Creates:**
- `payment_transactions` table with 50+ columns
- Indexes on `gateway`, `status`, `reference_type/reference_id`

**What This Does:**
- Creates new unified payment tracking table
- **Does NOT** modify existing tables (bookings, razorpay_logs, etc.)
- **Backward Compatible**: Old columns still work

---

### Step 3: Verify Configuration

**Check `.env` file:**

```env
# Payment Gateway (NEW)
PAYMENT_DEFAULT_GATEWAY=razorpay

# Razorpay (Existing - verify these are set)
RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
RAZORPAY_BASE_URL=https://api.razorpay.com/v1
```

**Add to `config/services.php`** (if not exists):

```php
'payment' => [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'razorpay'),
],
```

---

### Step 4: Clear Cache

```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

### Step 5: Test Payment Flow

**Test 1: Create New Booking with Payment**

```bash
# Create a booking via API/frontend
# Check payment_transactions table for new record
```

**Expected Behavior:**
- Order creation creates `PaymentTransaction` with `transaction_type = 'order'`
- Payment authorization updates transaction to `status = 'authorized'`
- Payment capture creates second transaction with `transaction_type = 'capture'`
- Booking.razorpay_payment_id still populated (backward compatible)

**Test 2: Process Refund**

```bash
# Cancel a paid booking
# Check payment_transactions table for refund transaction
```

**Expected Behavior:**
- Refund creates `PaymentTransaction` with `transaction_type = 'refund'`
- Original payment transaction updated with `refunded_amount`
- Booking.refund_id still populated (backward compatible)

**Test 3: Webhook Processing**

```bash
# Trigger test webhook from Razorpay dashboard
# OR use curl to send test webhook
```

**Expected Behavior:**
- Webhook creates/updates `PaymentTransaction` record
- `webhook_received = true`, `webhook_event_type` populated
- Booking status updated based on webhook event

---

## Data Migration (Optional)

### Migrating Existing Payments to PaymentTransaction

If you want to backfill existing payment data into the new `payment_transactions` table:

**Create Migration Command:**

```php
// app/Console/Commands/MigratePaymentTransactions.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;

class MigratePaymentTransactions extends Command
{
    protected $signature = 'payments:migrate-existing';
    protected $description = 'Migrate existing payment data to payment_transactions table';

    public function handle()
    {
        $this->info('Migrating existing payment data...');
        
        $bookings = Booking::whereNotNull('razorpay_payment_id')
            ->where('payment_status', 'paid')
            ->get();
        
        $count = 0;
        
        foreach ($bookings as $booking) {
            // Check if already migrated
            $exists = PaymentTransaction::where('gateway_payment_id', $booking->razorpay_payment_id)
                ->exists();
            
            if ($exists) {
                continue;
            }
            
            // Create payment transaction record
            PaymentTransaction::create([
                'gateway' => 'razorpay',
                'transaction_type' => 'payment',
                'gateway_order_id' => $booking->razorpay_order_id,
                'gateway_payment_id' => $booking->razorpay_payment_id,
                'reference_type' => 'Booking',
                'reference_id' => $booking->id,
                'user_id' => $booking->customer_id,
                'amount' => $booking->total_amount,
                'currency' => 'INR',
                'amount_in_smallest_unit' => $booking->total_amount * 100,
                'status' => 'captured',
                'payment_status' => 'paid',
                'authorized_at' => $booking->payment_authorized_at,
                'captured_at' => $booking->payment_captured_at,
                'customer_name' => $booking->customer->name ?? null,
                'customer_email' => $booking->customer->email ?? null,
                'customer_phone' => $booking->customer->phone ?? null,
                'created_at' => $booking->created_at,
                'updated_at' => $booking->updated_at,
            ]);
            
            $count++;
        }
        
        $this->info("Migrated {$count} payment transactions.");
        return 0;
    }
}
```

**Run Migration:**

```bash
php artisan payments:migrate-existing
```

**Note**: This is **optional**. New payments will automatically be tracked in `payment_transactions`.

---

## Backward Compatibility

### Existing Code Still Works

✅ **Bookings Table Columns:**
- `razorpay_order_id` - Still populated
- `razorpay_payment_id` - Still populated  
- `payment_authorized_at` - Still populated
- `payment_captured_at` - Still populated
- `payment_status` - Still updated

✅ **RazorpayService:**
- Still exists and functional
- Used internally by `RazorpayGateway`
- Can still be called directly if needed

✅ **RazorpayLog Table:**
- Still logs all Razorpay API calls
- Continues to work as before

### New Features

✅ **PaymentTransaction Table:**
- Comprehensive tracking across all gateways
- Polymorphic references (Booking, Invoice, etc.)
- Full webhook event logging
- Refund tracking with remaining refundable amount

✅ **PaymentService:**
- Unified API for all gateways
- Automatic transaction logging
- Gateway abstraction

---

## Monitoring & Logging

### Check Payment Transaction Logs

```sql
-- Recent payment transactions
SELECT * FROM payment_transactions 
ORDER BY created_at DESC 
LIMIT 50;

-- Failed transactions
SELECT * FROM payment_transactions 
WHERE status = 'failed' 
ORDER BY created_at DESC;

-- Pending captures (older than 25 minutes)
SELECT * FROM payment_transactions 
WHERE status = 'authorized' 
  AND capture_expires_at < NOW() + INTERVAL 5 MINUTE;

-- Refunded transactions
SELECT * FROM payment_transactions 
WHERE refunded_amount > 0 
ORDER BY refunded_at DESC;
```

### Application Logs

```bash
# Check Laravel logs for payment errors
tail -f storage/logs/laravel.log | grep -i payment

# Check webhook processing
tail -f storage/logs/laravel.log | grep -i webhook
```

---

## Rollback Plan

If issues arise, you can rollback:

### Step 1: Revert Code Changes

```bash
# Rollback to commit before Phase 2
git revert 9f056cb

# OR checkout previous commit
git checkout <commit-before-integration>
```

### Step 2: Rollback Database (Optional)

```bash
# Only if you want to remove payment_transactions table
php artisan migrate:rollback --step=1
```

**Note**: This will **NOT** affect existing data in `bookings`, `razorpay_logs`, etc.

---

## Common Issues & Solutions

### Issue 1: "Unsupported payment gateway" Error

**Cause**: Gateway not configured in PaymentService.

**Solution**:
```php
// Ensure default gateway is set in config/services.php
'payment' => [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'razorpay'),
],
```

---

### Issue 2: Webhook Signature Verification Fails

**Cause**: Webhook secret not set or incorrect.

**Solution**:
```bash
# Check .env
RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx

# Clear config cache
php artisan config:clear
```

---

### Issue 3: PaymentTransaction Not Created

**Cause**: Migration not run or database connection issue.

**Solution**:
```bash
# Run migration
php artisan migrate

# Check table exists
php artisan tinker
>>> Schema::hasTable('payment_transactions')
```

---

## Performance Considerations

### Database Indexes

The migration creates these indexes:
- `payment_transactions_gateway_status_index`
- `payment_transactions_reference_type_reference_id_index`
- `payment_transactions_gateway_transaction_type_index`

### Query Optimization

✅ **Good: Using Scopes**
```php
$transactions = PaymentTransaction::forReference('Booking', $bookingId)->get();
```

❌ **Bad: N+1 Queries**
```php
foreach ($bookings as $booking) {
    $transactions = PaymentTransaction::where('reference_id', $booking->id)->get();
}
```

✅ **Good: Eager Loading**
```php
$transactions = PaymentTransaction::with('user', 'reference')->get();
```

---

## Next Steps (Phase 3)

### Planned Features

1. **StripeGateway Implementation**
   - Full Stripe payment integration
   - Multi-currency support
   - Payment intents API

2. **Admin Dashboard**
   - Payment analytics
   - Transaction reports
   - Refund management

3. **Automated Testing**
   - Unit tests for PaymentService
   - Integration tests for payment flow
   - Webhook simulation tests

4. **Enhanced Monitoring**
   - Real-time payment alerts
   - Failed payment notifications
   - SLA tracking for payment processing

---

## Support & Troubleshooting

### Check System Status

```bash
# Verify payment service is working
php artisan tinker
>>> $payment = new \App\Services\PaymentService();
>>> $payment->getGatewayName()
=> "razorpay"
```

### Test Payment Creation

```bash
php artisan tinker
>>> $payment = new \App\Services\PaymentService();
>>> $result = $payment->createOrder(100.00, 'INR', ['receipt' => 'TEST_001']);
>>> $result['success']
=> true
```

### Enable Debug Mode (Development Only)

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

---

## Documentation References

- **Developer Guide**: `docs/PROMPT_69_PAYMENT_GATEWAY_DEVELOPER_GUIDE.md`
- **User Guide**: `docs/PROMPT_69_PAYMENT_GATEWAY_USER_GUIDE.md`
- **API Reference**: See Developer Guide Section 5

---

## Deployment Checklist

- [ ] Pull latest code (commits: 93cb09e, 54fbc65, 9f056cb)
- [ ] Run `php artisan migrate`
- [ ] Verify `.env` configuration (PAYMENT_DEFAULT_GATEWAY, RAZORPAY_*)
- [ ] Add `payment` config to `config/services.php`
- [ ] Clear all caches (`config`, `route`, `cache`)
- [ ] Test new booking creation
- [ ] Test payment capture
- [ ] Test refund processing
- [ ] Test webhook processing (send test webhook)
- [ ] Monitor `payment_transactions` table for new records
- [ ] Check logs for errors (`storage/logs/laravel.log`)
- [ ] Verify backward compatibility (existing columns populated)
- [ ] (Optional) Run `payments:migrate-existing` to backfill old data
- [ ] Update team documentation
- [ ] Schedule Phase 3 implementation (StripeGateway)

---

**Migration Guide Version**: 1.0  
**Created**: 2025-12-11  
**PROMPT**: 69 - Payment Gateway Integration Wrapper  
**Status**: Phase 2 Complete - Ready for Deployment
