# Payment Gateway Integration - User Guide
## PROMPT 69: Complete Usage Instructions

## Table of Contents
1. [Introduction](#introduction)
2. [Quick Start](#quick-start)
3. [Payment Flow](#payment-flow)
4. [Order Creation](#order-creation)
5. [Payment Capture](#payment-capture)
6. [Refunds](#refunds)
7. [Transaction Tracking](#transaction-tracking)
8. [Multi-Gateway Support](#multi-gateway-support)
9. [Common Use Cases](#common-use-cases)
10. [Troubleshooting](#troubleshooting)

---

## Introduction

This guide explains how to use the Payment Gateway Integration Wrapper in your application. The wrapper provides a unified API for processing payments through multiple payment gateways (Razorpay, Stripe, etc.).

### What You Can Do

✅ Accept payments via Razorpay, Stripe, or other gateways  
✅ Hold payments for 30 minutes before capturing (manual capture)  
✅ Process full or partial refunds  
✅ Track all payment transactions automatically  
✅ Switch between payment gateways easily  
✅ Handle webhooks automatically  

### Prerequisites

- Laravel 10.x application
- Valid Razorpay/Stripe account
- Payment gateway credentials (API keys, webhook secrets)

---

## Quick Start

### Step 1: Basic Payment Flow

```php
use App\Services\PaymentService;

// Initialize payment service
$payment = new PaymentService();

// Create an order
$result = $payment->createOrder(500.00, 'INR', [
    'receipt' => 'ORDER_123',
    'description' => 'Product purchase',
]);

if ($result['success']) {
    $orderId = $result['order_data']['id'];
    
    // Use $orderId to open payment gateway checkout
    // (Customer completes payment)
    
    // Payment is automatically tracked in database
}
```

### Step 2: Check Payment Status

```php
use App\Models\PaymentTransaction;

$transaction = PaymentTransaction::where('gateway_order_id', $orderId)->first();

echo $transaction->status; // 'authorized', 'captured', 'failed'
```

### Step 3: Capture Payment

```php
$result = $payment->capturePayment($paymentId, $amount);

if ($result['success']) {
    echo "Payment captured successfully!";
}
```

---

## Payment Flow

### Standard Payment Flow (Auto-Capture)

```
1. Create Order
   ↓
2. Customer Pays
   ↓
3. Payment Authorized
   ↓
4. Payment Auto-Captured
   ↓
5. Payment Complete
```

### Manual Capture Flow (Hold & Capture)

```
1. Create Order (manual_capture = true)
   ↓
2. Customer Pays
   ↓
3. Payment Authorized (Held for 30 minutes)
   ↓
4. Verify Order
   ↓
5. Manually Capture Payment
   ↓
6. Payment Complete
```

### Refund Flow

```
1. Payment Captured
   ↓
2. Request Refund
   ↓
3. Refund Processed
   ↓
4. Money Returned to Customer (5-7 days)
```

---

## Order Creation

### Basic Order

```php
$payment = new PaymentService();

$result = $payment->createOrder(1000.00, 'INR', [
    'receipt' => 'INVOICE_456',
    'description' => 'Booking payment',
]);

if ($result['success']) {
    $orderId = $result['order_data']['id'];
    $transaction = $result['transaction']; // PaymentTransaction model
}
```

### Order with Customer Details

```php
$result = $payment->createOrder(1500.00, 'INR', [
    'receipt' => 'BOOKING_789',
    'description' => 'Hoarding booking payment',
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '9876543210',
]);
```

### Order with Reference (Link to Booking/Invoice)

```php
$result = $payment->createOrder($booking->total_amount, 'INR', [
    'reference_type' => 'Booking',
    'reference_id' => $booking->id,
    'user_id' => auth()->id(),
    'receipt' => "BOOKING_{$booking->id}_" . time(),
    'description' => "Booking #{$booking->id} payment",
]);

// Later, retrieve all transactions for this booking
$transactions = PaymentTransaction::forReference('Booking', $booking->id)->get();
```

### Order with Manual Capture (30-Minute Hold)

```php
$result = $payment->createOrder(2000.00, 'INR', [
    'receipt' => 'ORDER_999',
    'description' => 'High-value order',
    'manual_capture' => true,           // Enable manual capture
    'capture_expiry_minutes' => 30,     // Hold for 30 minutes
]);

// Payment will be authorized but NOT captured
// You must manually capture within 30 minutes
```

### Order with Metadata

```php
$result = $payment->createOrder(3000.00, 'INR', [
    'receipt' => 'CUSTOM_001',
    'metadata' => [
        'booking_number' => 'BK2024001',
        'hoarding_id' => 123,
        'campaign_name' => 'Summer Sale 2024',
        'agent_id' => 456,
    ]
]);

// Metadata is stored in transaction and sent to gateway
```

---

## Payment Capture

### When to Use Manual Capture

Use manual capture when:
- ✅ You need to verify stock availability
- ✅ You need to confirm booking slots
- ✅ You want to prevent fraud
- ✅ You need admin approval before charging

### Capturing Full Amount

```php
$payment = new PaymentService();

// Find transaction by payment ID
$transaction = PaymentTransaction::where('gateway_payment_id', $paymentId)->first();

// Capture full amount
$result = $payment->capturePayment(
    $transaction->gateway_payment_id,
    $transaction->amount
);

if ($result['success']) {
    echo "Payment captured: ₹" . $transaction->amount;
}
```

### Capturing Partial Amount

```php
// Order was for ₹1000, but capture only ₹800
$result = $payment->capturePayment($paymentId, 800.00);

if ($result['success']) {
    echo "Captured ₹800 out of ₹1000";
    // Remaining ₹200 is released back to customer
}
```

### Checking Capture Expiry

```php
$transaction = PaymentTransaction::find($id);

if ($transaction->isCaptureExpired()) {
    echo "Capture window expired!";
} else {
    $remainingTime = $transaction->getCaptureTimeRemaining();
    echo "Capture within {$remainingTime} minutes";
}
```

### Auto-Expiry Cleanup

Payments not captured within 30 minutes automatically expire:

```php
// Run scheduled command daily
// php artisan schedule:run

// Check expired captures
$expired = PaymentTransaction::expiredCaptures()->get();

foreach ($expired as $transaction) {
    echo "Transaction {$transaction->id} expired";
    // Release booking hold
    // Notify customer
}
```

---

## Refunds

### Full Refund

```php
$payment = new PaymentService();

$result = $payment->createRefund($paymentId, $transaction->amount, [
    'reason' => 'Customer requested cancellation',
    'notes' => 'Full refund for booking #123'
]);

if ($result['success']) {
    echo "Refund of ₹{$transaction->amount} initiated";
    echo "Refund ID: " . $result['refund_data']['id'];
}
```

### Partial Refund

```php
// Original payment: ₹1000
// Refund: ₹400 (partial)

$result = $payment->createRefund($paymentId, 400.00, [
    'reason' => 'Partial cancellation',
    'notes' => 'Cancelled 2 out of 5 items'
]);

// Transaction status: 'partially_refunded'
// Refunded amount: ₹400
// Remaining refundable: ₹600
```

### Multiple Partial Refunds

```php
// First refund: ₹300
$payment->createRefund($paymentId, 300.00, ['reason' => 'Item 1 cancelled']);

// Second refund: ₹200
$payment->createRefund($paymentId, 200.00, ['reason' => 'Item 2 cancelled']);

// Total refunded: ₹500
// Remaining refundable: ₹500
```

### Checking Refundable Amount

```php
$transaction = PaymentTransaction::find($id);

if ($transaction->isRefundable()) {
    $refundableAmount = $transaction->getRefundableAmount();
    echo "Can refund up to ₹{$refundableAmount}";
} else {
    echo "Already fully refunded";
}
```

### Refund Status

```php
$transaction = PaymentTransaction::find($id);

switch ($transaction->status) {
    case 'captured':
        echo "Not refunded";
        break;
    case 'partially_refunded':
        echo "Partially refunded: ₹" . $transaction->refunded_amount;
        break;
    case 'refunded':
        echo "Fully refunded";
        break;
}
```

---

## Transaction Tracking

### Finding Transactions

#### By Payment ID

```php
$transaction = PaymentTransaction::where('gateway_payment_id', $paymentId)->first();
```

#### By Order ID

```php
$transaction = PaymentTransaction::where('gateway_order_id', $orderId)->first();
```

#### By Reference (Booking/Invoice)

```php
$transactions = PaymentTransaction::forReference('Booking', $bookingId)->get();
```

#### By User

```php
$userTransactions = PaymentTransaction::where('user_id', $userId)
    ->recent(30) // Last 30 days
    ->get();
```

### Filtering Transactions

#### Successful Payments

```php
$successful = PaymentTransaction::successful()->get();
```

#### Failed Payments

```php
$failed = PaymentTransaction::failed()->get();
```

#### Refunded Payments

```php
$refunded = PaymentTransaction::refunded()->get();
```

#### Pending Capture

```php
$pending = PaymentTransaction::pendingCapture()->get();
```

#### By Gateway

```php
$razorpayTransactions = PaymentTransaction::forGateway('razorpay')->get();
$stripeTransactions = PaymentTransaction::forGateway('stripe')->get();
```

#### By Status

```php
$authorized = PaymentTransaction::withStatus('authorized')->get();
$captured = PaymentTransaction::withStatus('captured')->get();
```

### Transaction Details

```php
$transaction = PaymentTransaction::find($id);

// Basic info
echo "Amount: ₹" . $transaction->getFormattedAmount(); // ₹500.00
echo "Status: " . $transaction->status;
echo "Gateway: " . $transaction->getGatewayDisplayName(); // Razorpay

// Customer info
echo "Customer: {$transaction->customer_name}";
echo "Email: {$transaction->customer_email}";
echo "Phone: {$transaction->customer_phone}";

// Payment method
echo "Method: {$transaction->payment_method}"; // card, upi, netbanking

// Fees
echo "Fee: ₹{$transaction->fee}";
echo "Tax: ₹{$transaction->tax}";
echo "Net: ₹{$transaction->net_amount}";

// Timestamps
echo "Authorized: {$transaction->authorized_at}";
echo "Captured: {$transaction->captured_at}";
```

### Transaction History

```php
// Get all transactions for a booking (order, capture, refunds)
$transactions = PaymentTransaction::forReference('Booking', $bookingId)
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($transactions as $txn) {
    echo "{$txn->transaction_type}: ₹{$txn->amount} - {$txn->status}\n";
}

// Output:
// order: ₹1000 - captured
// refund: ₹200 - refunded
// refund: ₹100 - refunded
```

---

## Multi-Gateway Support

### Using Default Gateway

```php
// Uses default gateway from config (razorpay)
$payment = new PaymentService();
```

### Specifying Gateway

```php
// Use Razorpay
$payment = new PaymentService('razorpay');

// Use Stripe
$payment = new PaymentService('stripe');
```

### Switching Gateway Dynamically

```php
$payment = new PaymentService();

// Start with Razorpay
$payment->useGateway('razorpay');
$result1 = $payment->createOrder(500, 'INR');

// Switch to Stripe
$payment->useGateway('stripe');
$result2 = $payment->createOrder(500, 'USD');
```

### Gateway-Specific Logic

```php
// Use Razorpay for Indian customers
if ($customer->country === 'IN') {
    $payment = new PaymentService('razorpay');
    $currency = 'INR';
} else {
    // Use Stripe for international customers
    $payment = new PaymentService('stripe');
    $currency = 'USD';
}

$result = $payment->createOrder($amount, $currency, $options);
```

### Checking Gateway

```php
$payment = new PaymentService();

echo $payment->getGatewayName(); // 'razorpay' or 'stripe'
```

---

## Common Use Cases

### Use Case 1: Booking Payment with Hold

**Scenario**: Customer books a hoarding. Hold payment for 30 minutes while admin verifies availability.

```php
// 1. Customer creates booking
$booking = Booking::create([
    'hoarding_id' => $hoarding->id,
    'customer_id' => auth()->id(),
    'start_date' => $request->start_date,
    'end_date' => $request->end_date,
    'total_amount' => 5000.00,
    'status' => 'pending_payment',
]);

// 2. Create payment order with 30-minute hold
$payment = new PaymentService();

$orderResult = $payment->createOrder($booking->total_amount, 'INR', [
    'reference_type' => 'Booking',
    'reference_id' => $booking->id,
    'user_id' => auth()->id(),
    'receipt' => "BOOKING_{$booking->id}_" . time(),
    'description' => "Hoarding booking #{$booking->id}",
    'customer_name' => auth()->user()->name,
    'customer_email' => auth()->user()->email,
    'customer_phone' => auth()->user()->phone,
    'manual_capture' => true,
    'capture_expiry_minutes' => 30,
    'metadata' => [
        'booking_number' => $booking->booking_number,
        'hoarding_id' => $hoarding->id,
    ]
]);

$orderId = $orderResult['order_data']['id'];

// 3. Return order ID to frontend for checkout
return response()->json([
    'order_id' => $orderId,
    'amount' => $booking->total_amount,
    'currency' => 'INR',
]);

// 4. Frontend opens Razorpay checkout
// <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
// var options = { key: 'rzp_test_xxx', amount: 500000, currency: 'INR', order_id: orderId };
// var rzp = new Razorpay(options);
// rzp.open();

// 5. Payment authorized (webhook updates transaction)

// 6. Admin verifies booking availability
if ($booking->isAvailable()) {
    // 7. Capture payment
    $transaction = PaymentTransaction::forReference('Booking', $booking->id)->first();
    $captureResult = $payment->capturePayment($transaction->gateway_payment_id, $booking->total_amount);
    
    if ($captureResult['success']) {
        $booking->update(['status' => 'confirmed', 'payment_status' => 'paid']);
    }
} else {
    // Booking not available - payment auto-expires in 30 minutes
    $booking->update(['status' => 'cancelled']);
}
```

---

### Use Case 2: Partial Refund on Cancellation

**Scenario**: Customer cancels booking 3 days before start date. Refund 80% (20% cancellation fee).

```php
$booking = Booking::find($bookingId);
$transaction = PaymentTransaction::forReference('Booking', $booking->id)
    ->successful()
    ->first();

// Calculate refund (80% of total)
$refundAmount = $transaction->amount * 0.80;
$cancellationFee = $transaction->amount * 0.20;

// Create partial refund
$payment = new PaymentService();
$refundResult = $payment->createRefund(
    $transaction->gateway_payment_id,
    $refundAmount,
    [
        'reason' => 'Customer cancellation',
        'notes' => "Booking #{$booking->id} cancelled. Cancellation fee: ₹{$cancellationFee}"
    ]
);

if ($refundResult['success']) {
    $booking->update([
        'status' => 'cancelled',
        'cancellation_fee' => $cancellationFee,
        'refund_amount' => $refundAmount,
    ]);
    
    // Notify customer
    Mail::to($booking->customer)->send(new RefundProcessedMail($booking, $refundAmount));
}
```

---

### Use Case 3: Recurring Invoice Payments

**Scenario**: Monthly recurring payments for subscription.

```php
// Month 1: Create order
$payment = new PaymentService();

$result = $payment->createOrder(999.00, 'INR', [
    'reference_type' => 'Subscription',
    'reference_id' => $subscription->id,
    'user_id' => $subscription->user_id,
    'receipt' => "SUB_{$subscription->id}_" . now()->format('Y_m'),
    'description' => "Monthly subscription payment",
]);

// Month 2+: Charge saved payment method (if supported by gateway)
$gateway = $payment->getGateway();

if ($gateway->getSupportedFeatures()['recurring_payments']) {
    $result = $gateway->chargePaymentMethod(
        $subscription->payment_method_id,
        999.00,
        ['currency' => 'INR']
    );
}
```

---

### Use Case 4: Multi-Currency Support

**Scenario**: Support both Indian (INR) and international (USD) customers.

```php
// Determine currency based on customer location
if ($customer->country === 'IN') {
    $currency = 'INR';
    $amount = 5000.00; // ₹5000
    $gateway = 'razorpay';
} else {
    $currency = 'USD';
    $amount = 60.00; // $60 (approximate conversion)
    $gateway = 'stripe';
}

$payment = new PaymentService($gateway);

$result = $payment->createOrder($amount, $currency, [
    'reference_type' => 'Booking',
    'reference_id' => $booking->id,
    'customer_name' => $customer->name,
    'customer_email' => $customer->email,
]);
```

---

### Use Case 5: Batch Refund Processing

**Scenario**: Admin processes multiple refunds at once.

```php
$refundRequests = RefundRequest::where('status', 'approved')->get();

foreach ($refundRequests as $request) {
    $transaction = PaymentTransaction::find($request->transaction_id);
    
    if (!$transaction->isRefundable()) {
        $request->update(['status' => 'failed', 'reason' => 'Not refundable']);
        continue;
    }
    
    $payment = new PaymentService($transaction->gateway);
    $result = $payment->createRefund(
        $transaction->gateway_payment_id,
        $request->refund_amount,
        ['reason' => $request->reason]
    );
    
    if ($result['success']) {
        $request->update([
            'status' => 'processed',
            'refund_id' => $result['refund_data']['id'],
        ]);
    } else {
        $request->update([
            'status' => 'failed',
            'reason' => $result['error']
        ]);
    }
}
```

---

## Troubleshooting

### Issue 1: Payment Stuck in "Created" Status

**Symptoms**: Transaction status is `created` but customer completed payment.

**Possible Causes**:
- Webhook not received
- Webhook signature verification failed
- Customer closed checkout before completing

**Solutions**:

```php
// 1. Check webhook logs
$transaction = PaymentTransaction::find($id);
if (!$transaction->webhook_received) {
    echo "Webhook not received - check gateway dashboard";
}

// 2. Manually fetch payment status from gateway
$payment = new PaymentService($transaction->gateway);
$gateway = $payment->getGateway();
$paymentData = $gateway->getPayment($transaction->gateway_payment_id);

// 3. Update transaction manually if payment successful
if ($paymentData['status'] === 'captured') {
    $transaction->markCaptured($paymentData['amount'], $paymentData);
}
```

---

### Issue 2: Capture Window Expired

**Symptoms**: Cannot capture payment, error says "Capture window expired".

**Cause**: Payment was authorized more than 30 minutes ago.

**Solutions**:

```php
$transaction = PaymentTransaction::find($id);

if ($transaction->isCaptureExpired()) {
    echo "Payment expired - cannot capture";
    
    // Option 1: Request customer to pay again
    // Option 2: Contact payment gateway support
    
    // Mark transaction as expired
    $transaction->update(['status' => 'expired']);
    
    // Release booking hold
    $booking->update(['status' => 'cancelled']);
}
```

---

### Issue 3: Refund Amount Exceeds Refundable Amount

**Symptoms**: Refund fails with error "Amount exceeds refundable amount".

**Cause**: Trying to refund more than `amount - refunded_amount`.

**Solutions**:

```php
$transaction = PaymentTransaction::find($id);

// Check refundable amount
$refundable = $transaction->getRefundableAmount();
echo "Can refund up to: ₹{$refundable}";

// Already refunded
echo "Already refunded: ₹{$transaction->refunded_amount}";

// Request only refundable amount
if ($requestedRefund > $refundable) {
    $requestedRefund = $refundable;
}

$result = $payment->createRefund($transaction->gateway_payment_id, $requestedRefund);
```

---

### Issue 4: Webhook Signature Verification Fails

**Symptoms**: Webhooks return 400 error, signature verification fails.

**Cause**: Incorrect webhook secret in `.env`.

**Solutions**:

```bash
# 1. Check .env file
RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx

# 2. Verify secret matches gateway dashboard
# Razorpay Dashboard → Settings → Webhooks → Copy secret

# 3. Clear config cache
php artisan config:clear

# 4. Test webhook manually
curl -X POST https://yourdomain.com/api/webhooks/razorpay \
  -H "X-Razorpay-Signature: xxxxx" \
  -d @webhook_payload.json
```

---

### Issue 5: Multiple Transactions for Same Payment

**Symptoms**: Database has duplicate transactions.

**Cause**: Order creation called multiple times (e.g., page refresh).

**Prevention**:

```php
// Check if transaction already exists
$existing = PaymentTransaction::forReference('Booking', $booking->id)
    ->where('status', '!=', 'failed')
    ->first();

if ($existing) {
    // Use existing order
    return response()->json([
        'order_id' => $existing->gateway_order_id,
        'amount' => $existing->amount,
    ]);
}

// Create new order only if none exists
$result = $payment->createOrder($booking->total_amount, 'INR', [...]);
```

---

### Issue 6: Payment Succeeded but Booking Not Updated

**Symptoms**: Customer paid but booking status is still "pending".

**Cause**: Webhook processing failed or not handled.

**Solutions**:

```php
// 1. Check webhook was received
$transaction = PaymentTransaction::where('gateway_payment_id', $paymentId)->first();
if ($transaction->webhook_received) {
    echo "Webhook received at: {$transaction->webhook_received_at}";
} else {
    echo "Webhook not received";
}

// 2. Manually process webhook event
$payment = new PaymentService($transaction->gateway);
$result = $payment->handleWebhook($webhookPayload, $signature);

// 3. Update booking manually
$booking = Booking::find($transaction->reference_id);
$booking->update(['status' => 'confirmed', 'payment_status' => 'paid']);
```

---

## Best Practices

### 1. Always Use Receipts

Generate unique receipt numbers:
```php
$receipt = "{$type}_{$id}_" . time(); // "BOOKING_123_1234567890"
```

### 2. Store Metadata

Include useful metadata for debugging:
```php
'metadata' => [
    'booking_number' => $booking->booking_number,
    'hoarding_id' => $hoarding->id,
    'campaign_name' => $campaign->name,
    'agent_id' => auth()->id(),
]
```

### 3. Handle Errors Gracefully

```php
$result = $payment->createOrder($amount, 'INR', $options);

if (!$result['success']) {
    Log::error('Payment order creation failed', [
        'error' => $result['error'],
        'booking_id' => $booking->id,
    ]);
    
    return response()->json([
        'error' => 'Unable to process payment. Please try again.'
    ], 500);
}
```

### 4. Verify Webhook Signatures

Always verify signatures before processing webhooks - this is done automatically by `PaymentService::handleWebhook()`.

### 5. Use Transactions for Database Updates

```php
DB::transaction(function () use ($payment, $booking, $transaction) {
    // Capture payment
    $result = $payment->capturePayment($transaction->gateway_payment_id, $booking->total_amount);
    
    if ($result['success']) {
        // Update booking
        $booking->update(['status' => 'confirmed', 'payment_status' => 'paid']);
        
        // Send confirmation email
        Mail::to($booking->customer)->send(new BookingConfirmedMail($booking));
    }
});
```

### 6. Log All Payment Operations

Payment operations are automatically logged in `payment_transactions` table with full request/response payloads.

### 7. Test with Sandbox/Test Mode

Always use test API keys during development:
```env
# Development
RAZORPAY_KEY_ID=rzp_test_xxxxx

# Production
RAZORPAY_KEY_ID=rzp_live_xxxxx
```

---

## FAQ

**Q: How long does it take for refunds to reach customers?**  
A: 5-7 business days for most payment methods.

**Q: Can I capture less than the authorized amount?**  
A: Yes, partial capture is supported. Remaining amount is automatically released.

**Q: What happens if I don't capture within 30 minutes?**  
A: Payment automatically expires and is released back to customer.

**Q: Can I refund the same transaction multiple times?**  
A: Yes, multiple partial refunds are supported until full amount is refunded.

**Q: How do I switch from Razorpay to Stripe?**  
A: Just change the gateway parameter: `new PaymentService('stripe')`.

**Q: Are webhooks required?**  
A: Highly recommended. Webhooks automatically update transaction status in real-time.

**Q: Can I use this for subscription billing?**  
A: Yes, if your gateway supports recurring payments (check `getSupportedFeatures()`).

**Q: What currencies are supported?**  
A: Depends on gateway. Razorpay supports INR, USD, EUR, etc. Stripe supports 100+ currencies.

---

## Support

For technical issues:
- Check logs: `storage/logs/laravel.log`
- Review transaction: `payment_transactions` table
- Gateway dashboard: Check Razorpay/Stripe dashboard for payment status

For gateway-specific issues:
- **Razorpay Support**: https://razorpay.com/support/
- **Stripe Support**: https://support.stripe.com/

---

**Documentation Version**: 1.0  
**Created**: 2025-12-11  
**PROMPT**: 69 - Payment Gateway Integration Wrapper  
**Status**: Phase 1 Complete
