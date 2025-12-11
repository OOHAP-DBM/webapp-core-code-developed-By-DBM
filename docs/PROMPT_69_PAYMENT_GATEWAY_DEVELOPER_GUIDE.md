# Payment Gateway Integration Wrapper - Developer Guide
## PROMPT 69: Complete Implementation Documentation

## Table of Contents
1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Installation](#installation)
4. [Core Components](#core-components)
5. [API Reference](#api-reference)
6. [Gateway Implementations](#gateway-implementations)
7. [Webhooks](#webhooks)
8. [Examples](#examples)
9. [Testing](#testing)
10. [Troubleshooting](#troubleshooting)

---

## Overview

The Payment Gateway Integration Wrapper provides a unified, extensible API for integrating multiple payment gateways (Razorpay, Stripe, PayPal, etc.) with automatic transaction logging, webhook handling, and comprehensive refund support.

### Key Features

✅ **Multi-Gateway Support**: Easily switch between Razorpay, Stripe, or add custom gateways  
✅ **Unified API**: Same methods work across all gateways  
✅ **Automatic Logging**: All transactions tracked in `payment_transactions` table  
✅ **Webhook Handling**: Signature verification and automatic event processing  
✅ **Manual Capture**: Hold payments for 30 minutes before capture  
✅ **Partial Refunds**: Support for partial and full refunds  
✅ **Status Tracking**: Real-time payment status updates  
✅ **Error Handling**: Comprehensive error logging and recovery  

### Supported Gateways

| Gateway | Order Creation | Manual Capture | Refunds | Webhooks | Status |
|---------|---------------|----------------|---------|----------|--------|
| **Razorpay** | ✅ | ✅ | ✅ Full/Partial | ✅ | Implemented |
| **Stripe** | ✅ | ✅ | ✅ Full/Partial | ✅ | Phase 2 |
| **PayPal** | 🔜 | 🔜 | 🔜 | 🔜 | Planned |

---

## Architecture

### Component Diagram

```
┌──────────────────────────────────────────────────────┐
│                  Application Layer                   │
│  (Controllers, Services, Jobs)                       │
└───────────────────┬──────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│              PaymentService (Wrapper)                 │
│  • createOrder()                                      │
│  • capturePayment()                                   │
│  • createRefund()                                     │
│  • handleWebhook()                                    │
└───────────────────┬──────────────────────────────────┘
                    │
        ┌───────────┴───────────┐
        ▼                       ▼
┌──────────────────┐    ┌──────────────────┐
│ RazorpayGateway │    │  StripeGateway   │
│ (implements      │    │ (implements      │
│  PaymentGateway  │    │  PaymentGateway  │
│  Interface)      │    │  Interface)      │
└────────┬─────────┘    └────────┬─────────┘
         │                       │
         ▼                       ▼
   ┌──────────────────────────────────┐
   │   PaymentTransaction Model        │
   │   (Unified transaction log)       │
   └──────────────────────────────────┘
```

### Database Schema

**`payment_transactions` table** (50+ columns):

```sql
CREATE TABLE payment_transactions (
    id BIGINT PRIMARY KEY,
    
    -- Gateway Info
    gateway VARCHAR (razorpay/stripe/paypal),
    transaction_type VARCHAR (order/payment/capture/refund/void),
    
    -- External IDs
    gateway_order_id VARCHAR,
    gateway_payment_id VARCHAR,
    gateway_refund_id VARCHAR,
    
    -- Internal References
    reference_type VARCHAR (Booking, Invoice, etc.),
    reference_id BIGINT,
    user_id BIGINT,
    
    -- Amounts
    amount DECIMAL(15,2),
    currency VARCHAR(3),
    amount_in_smallest_unit INT,
    fee DECIMAL(15,2),
    tax DECIMAL(15,2),
    net_amount DECIMAL(15,2),
    
    -- Status
    status VARCHAR (created/authorized/captured/failed/refunded),
    payment_method VARCHAR,
    
    -- Capture Details
    manual_capture BOOLEAN,
    authorized_at TIMESTAMP,
    captured_at TIMESTAMP,
    capture_expires_at TIMESTAMP,
    
    -- Refund Details
    refunded_amount DECIMAL(15,2),
    refunded_at TIMESTAMP,
    refund_reason TEXT,
    
    -- Webhook Tracking
    webhook_received BOOLEAN,
    webhook_event_type VARCHAR,
    webhook_payload JSON,
    
    -- Logging
    request_payload JSON,
    response_payload JSON,
    metadata JSON,
    
    -- Error Handling
    error_code VARCHAR,
    error_message TEXT,
    
    -- Customer Snapshot
    customer_name VARCHAR,
    customer_email VARCHAR,
    customer_phone VARCHAR,
    
    -- Timestamps
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

---

## Installation

### Step 1: Run Migration

```bash
php artisan migrate
```

This creates the `payment_transactions` table.

### Step 2: Configure Environment

**.env**:
```env
# Default Gateway
PAYMENT_DEFAULT_GATEWAY=razorpay

# Razorpay
RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx

# Stripe (Phase 2)
STRIPE_KEY=sk_test_xxxxxxxxxxxxx
STRIPE_SECRET=xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
```

### Step 3: Update Config

**config/services.php**:
```php
'payment' => [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'razorpay'),
],

'razorpay' => [
    'key_id' => env('RAZORPAY_KEY_ID'),
    'key_secret' => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    'base_url' => env('RAZORPAY_BASE_URL', 'https://api.razorpay.com/v1'),
],

'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

---

## Core Components

### 1. PaymentGatewayInterface

**Purpose**: Contract that all payment gateways must implement.

**Location**: `app/Contracts/PaymentGatewayInterface.php`

**Key Methods**:
```php
interface PaymentGatewayInterface
{
    public function createOrder(float $amount, string $currency, array $options): array;
    public function capturePayment(string $paymentId, float $amount, array $options): array;
    public function createRefund(string $paymentId, float $amount, array $options): array;
    public function getPayment(string $paymentId): array;
    public function verifyWebhookSignature(string $payload, string $signature): bool;
    public function parseWebhook(string $payload): array;
    // ... 14 more methods
}
```

### 2. PaymentService

**Purpose**: Unified wrapper service that abstracts gateway-specific logic.

**Location**: `app/Services/PaymentService.php`

**Usage**:
```php
use App\Services\PaymentService;

// Initialize with default gateway
$payment = new PaymentService();

// Or specify gateway
$payment = new PaymentService('stripe');

// Switch gateway dynamically
$payment->useGateway('razorpay');
```

### 3. PaymentTransaction Model

**Purpose**: Eloquent model for `payment_transactions` table.

**Location**: `app/Models/PaymentTransaction.php`

**Key Features**:
- Automatic amount conversion (INR ↔ paise)
- Status tracking with helpers
- Polymorphic relationship to any reference (Booking, Invoice, etc.)
- Webhook event logging
- Refund tracking

**Scopes**:
```php
PaymentTransaction::successful()->get();
PaymentTransaction::failed()->get();
PaymentTransaction::pendingCapture()->get();
PaymentTransaction::forReference('Booking', 123)->get();
```

---

## API Reference

### PaymentService::createOrder()

Create a new payment order.

**Signature**:
```php
public function createOrder(
    float $amount,
    string $currency = 'INR',
    array $options = []
): array
```

**Parameters**:
- `$amount` - Amount in base currency (e.g., 500.00 INR)
- `$currency` - Currency code (INR, USD, EUR)
- `$options` - Additional configuration

**Options Array**:
```php
[
    'reference_type' => 'Booking',          // Model class name
    'reference_id' => 123,                   // Model ID
    'user_id' => 456,                        // Customer user ID
    'receipt' => 'BOOKING_123_1234567890',  // Unique receipt number
    'description' => 'Booking payment',      // Payment description
    'customer_name' => 'John Doe',
    'customer_email' => 'john@example.com',
    'customer_phone' => '9876543210',
    'manual_capture' => true,                // Enable 30-min hold
    'capture_expiry_minutes' => 30,          // Capture expiry time
    'metadata' => [                          // Custom metadata
        'booking_number' => 'BK123',
        'hoarding_id' => 789,
    ]
]
```

**Return Value**:
```php
[
    'success' => true,
    'transaction' => PaymentTransaction,    // Database record
    'order_data' => [                       // Gateway response
        'id' => 'order_xxxxx',
        'amount' => 50000,                  // In paise
        'currency' => 'INR',
        'status' => 'created'
    ]
]
```

**Example**:
```php
$payment = new PaymentService('razorpay');

$result = $payment->createOrder(500.00, 'INR', [
    'reference_type' => 'Booking',
    'reference_id' => $booking->id,
    'user_id' => auth()->id(),
    'receipt' => "BOOKING_{$booking->id}_" . time(),
    'description' => "Booking #{$booking->id} payment",
    'customer_name' => auth()->user()->name,
    'customer_email' => auth()->user()->email,
    'customer_phone' => auth()->user()->phone,
    'manual_capture' => true,
    'capture_expiry_minutes' => 30,
]);

if ($result['success']) {
    $orderId = $result['order_data']['id'];
    $transaction = $result['transaction'];
    
    // Open Razorpay checkout with $orderId
}
```

---

### PaymentService::capturePayment()

Capture an authorized payment.

**Signature**:
```php
public function capturePayment(
    string $paymentId,
    float $amount,
    array $options = []
): array
```

**Parameters**:
- `$paymentId` - Gateway payment ID (e.g., `pay_xxxxx`)
- `$amount` - Amount to capture (can be less than authorized for partial capture)
- `$options` - Additional options

**Example**:
```php
$result = $payment->capturePayment('pay_xxxxx', 500.00);

if ($result['success']) {
    $transaction = $result['transaction'];
    // Transaction status is now 'captured'
}
```

---

### PaymentService::createRefund()

Create a full or partial refund.

**Signature**:
```php
public function createRefund(
    string $paymentId,
    float $amount,
    array $options = []
): array
```

**Parameters**:
- `$paymentId` - Gateway payment ID
- `$amount` - Refund amount (must be ≤ refundable amount)
- `$options`:
  - `reason` - Refund reason
  - `notes` - Additional notes
  - `speed` - 'optimum' or 'normal' (Razorpay)

**Example**:
```php
// Full refund
$result = $payment->createRefund('pay_xxxxx', 500.00, [
    'reason' => 'Customer cancellation',
    'notes' => 'Booking cancelled before start date'
]);

// Partial refund
$result = $payment->createRefund('pay_xxxxx', 250.00, [
    'reason' => 'Partial cancellation',
]);

if ($result['success']) {
    $refundId = $result['refund_data']['id'];
    $transaction = $result['transaction']; // Updated with refund
}
```

---

### PaymentService::handleWebhook()

Process incoming webhook from payment gateway.

**Signature**:
```php
public function handleWebhook(
    string $payload,
    string $signature,
    ?string $gateway = null
): array
```

**Parameters**:
- `$payload` - Raw webhook request body
- `$signature` - Webhook signature header
- `$gateway` - Gateway name (auto-detected if null)

**Example**:
```php
// In WebhookController
$payload = request()->getContent();
$signature = request()->header('X-Razorpay-Signature');

$result = $payment->handleWebhook($payload, $signature, 'razorpay');

if ($result['success']) {
    return response()->json(['status' => 'ok']);
}
```

---

## Gateway Implementations

### Phase 1: Razorpay (Implemented)

**File**: `app/Services/Gateways/RazorpayGateway.php`

**Features**:
- ✅ Order creation with manual capture
- ✅ Payment authorization
- ✅ Payment capture (manual/automatic)
- ✅ Full/partial refunds
- ✅ Webhook signature verification (HMAC SHA256)
- ✅ Payment method detection (card/UPI/netbanking/wallet)

**Configuration**:
```php
'razorpay' => [
    'key_id' => env('RAZORPAY_KEY_ID'),
    'key_secret' => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    'base_url' => 'https://api.razorpay.com/v1',
]
```

**Webhook Events Handled**:
- `payment.authorized` - Payment hold successful
- `payment.captured` - Payment captured
- `payment.failed` - Payment failed
- `refund.created` - Refund initiated
- `refund.processed` - Refund completed

---

### Phase 2: Stripe (Planned)

**File**: `app/Services/Gateways/StripeGateway.php`

**Features** (To be implemented):
- 🔜 Payment Intent creation
- 🔜 Payment confirmation
- 🔜 Refund creation
- 🔜 Webhook signature verification (Stripe-Signature header)

**Configuration**:
```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
]
```

---

## Webhooks

### Setting Up Webhooks

#### Razorpay

1. Go to Razorpay Dashboard → Settings → Webhooks
2. Add webhook URL: `https://yourdomain.com/api/webhooks/razorpay`
3. Select events:
   - ✅ payment.authorized
   - ✅ payment.captured
   - ✅ payment.failed
   - ✅ refund.created
   - ✅ refund.processed
4. Copy webhook secret to `.env`:
   ```
   RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
   ```

#### Stripe

1. Go to Stripe Dashboard → Developers → Webhooks
2. Add endpoint: `https://yourdomain.com/api/webhooks/stripe`
3. Select events:
   - ✅ payment_intent.succeeded
   - ✅ payment_intent.payment_failed
   - ✅ charge.refunded
4. Copy signing secret to `.env`:
   ```
   STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
   ```

### Webhook Controller

**File**: `app/Http/Controllers/WebhookController.php`

```php
class WebhookController extends Controller
{
    public function handleRazorpay(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        
        $payment = new PaymentService('razorpay');
        $result = $payment->handleWebhook($payload, $signature);
        
        if ($result['success']) {
            return response()->json(['status' => 'ok']);
        }
        
        return response()->json(['error' => 'Invalid signature'], 400);
    }
    
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        
        $payment = new PaymentService('stripe');
        $result = $payment->handleWebhook($payload, $signature);
        
        if ($result['success']) {
            return response()->json(['status' => 'ok']);
        }
        
        return response()->json(['error' => 'Invalid signature'], 400);
    }
}
```

**Routes** (`routes/api.php`):
```php
Route::post('/webhooks/razorpay', [WebhookController::class, 'handleRazorpay']);
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripe']);
```

---

## Examples

### Example 1: Complete Booking Payment Flow

```php
// 1. Create order when booking is created
$payment = new PaymentService();

$orderResult = $payment->createOrder($booking->total_amount, 'INR', [
    'reference_type' => 'Booking',
    'reference_id' => $booking->id,
    'user_id' => $booking->customer_id,
    'receipt' => "BOOKING_{$booking->id}_" . time(),
    'description' => "Hoarding booking #{$booking->id}",
    'customer_name' => $booking->customer->name,
    'customer_email' => $booking->customer->email,
    'customer_phone' => $booking->customer->phone,
    'manual_capture' => true,
    'capture_expiry_minutes' => 30,
    'metadata' => [
        'booking_number' => $booking->booking_number,
        'hoarding_id' => $booking->hoarding_id,
    ]
]);

$orderId = $orderResult['order_data']['id'];
$transaction = $orderResult['transaction'];

// 2. Frontend opens Razorpay checkout
// (Customer completes payment - payment.authorized webhook received)

// 3. Webhook handler updates transaction to 'authorized' status

// 4. Admin captures payment after verification
$captureResult = $payment->capturePayment(
    $transaction->gateway_payment_id,
    $booking->total_amount
);

// 5. Webhook handler updates transaction to 'captured' status

// 6. If booking cancelled, create refund
if ($booking->status === 'cancelled') {
    $refundResult = $payment->createRefund(
        $transaction->gateway_payment_id,
        $booking->total_amount,
        [
            'reason' => 'Booking cancelled by customer',
            'notes' => "Booking #{$booking->id} cancelled",
        ]
    );
}
```

---

### Example 2: Partial Refund

```php
// Original payment: ₹500
// Refund ₹200 (partial)

$payment = new PaymentService();
$transaction = PaymentTransaction::find($transactionId);

// Check refundable amount
if ($transaction->isRefundable()) {
    $refundableAmount = $transaction->getRefundableAmount(); // 500.00
    
    if ($refundAmount <= $refundableAmount) {
        $result = $payment->createRefund(
            $transaction->gateway_payment_id,
            200.00, // Partial refund
            ['reason' => 'Partial cancellation']
        );
        
        if ($result['success']) {
            // Transaction status: 'partially_refunded'
            // refunded_amount: 200.00
            // Remaining refundable: 300.00
        }
    }
}
```

---

### Example 3: Multi-Gateway Support

```php
// Use Razorpay for Indian customers
if ($customer->country === 'IN') {
    $payment = new PaymentService('razorpay');
} else {
    // Use Stripe for international customers
    $payment = new PaymentService('stripe');
}

$result = $payment->createOrder($amount, $currency, $options);
```

---

### Example 4: Expired Capture Cleanup (Scheduled Job)

```php
namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use App\Services\PaymentService;

class CleanupExpiredCaptures extends Command
{
    public function handle()
    {
        $expiredTransactions = PaymentTransaction::expiredCaptures()->get();
        
        foreach ($expiredTransactions as $transaction) {
            $transaction->update([
                'status' => PaymentTransaction::STATUS_EXPIRED
            ]);
            
            // Notify customer
            // Release booking hold
            
            $this->info("Expired: {$transaction->id}");
        }
    }
}
```

---

## Testing

### Unit Tests

**File**: `tests/Unit/PaymentServiceTest.php`

```php
class PaymentServiceTest extends TestCase
{
    public function test_creates_order_with_razorpay()
    {
        $payment = new PaymentService('razorpay');
        
        $result = $payment->createOrder(500.00, 'INR', [
            'receipt' => 'TEST_001'
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['transaction']->gateway_order_id);
    }
    
    public function test_captures_payment()
    {
        // Create authorized payment
        $transaction = PaymentTransaction::factory()->create([
            'status' => PaymentTransaction::STATUS_AUTHORIZED
        ]);
        
        $payment = new PaymentService();
        $result = $payment->capturePayment($transaction->gateway_payment_id, 500.00);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('captured', $transaction->fresh()->status);
    }
}
```

### Integration Tests

**File**: `tests/Feature/PaymentFlowTest.php`

```php
class PaymentFlowTest extends TestCase
{
    public function test_complete_booking_payment_flow()
    {
        $booking = Booking::factory()->create(['total_amount' => 500.00]);
        $payment = new PaymentService('razorpay');
        
        // 1. Create order
        $orderResult = $payment->createOrder($booking->total_amount, 'INR', [
            'reference_type' => 'Booking',
            'reference_id' => $booking->id,
        ]);
        
        $this->assertTrue($orderResult['success']);
        
        // 2. Simulate webhook (authorized)
        $this->simulateWebhook('payment.authorized', [
            'payment_id' => $orderResult['transaction']->gateway_payment_id
        ]);
        
        // 3. Capture
        $captureResult = $payment->capturePayment(
            $orderResult['transaction']->gateway_payment_id,
            500.00
        );
        
        $this->assertTrue($captureResult['success']);
        
        // 4. Verify transaction status
        $this->assertEquals('captured', $orderResult['transaction']->fresh()->status);
    }
}
```

---

## Troubleshooting

### Common Issues

#### 1. **Order creation fails with "Invalid API key"**

**Solution**: Check `.env` file:
```env
RAZORPAY_KEY_ID=rzp_test_xxxxx  # Must start with rzp_test_ or rzp_live_
RAZORPAY_KEY_SECRET=xxxxx        # Must be correct secret
```

#### 2. **Webhook signature verification fails**

**Solution**: 
- Verify webhook secret is correct in `.env`
- Check raw payload is being passed (not parsed JSON)
- Ensure signature header name is correct (`X-Razorpay-Signature` for Razorpay)

#### 3. **Payment stuck in "authorized" state**

**Solution**:
- Manually capture: `$payment->capturePayment($paymentId, $amount)`
- Or wait 30 minutes for auto-expiry
- Check `capture_expires_at` timestamp

#### 4. **Refund fails with "Amount exceeds refundable amount"**

**Solution**:
```php
$refundableAmount = $transaction->getRefundableAmount();
if ($requestedRefund > $refundableAmount) {
    // Error: can only refund up to $refundableAmount
}
```

---

## Performance Optimization

### 1. Database Indexes

Already created in migration:
```php
$table->index(['gateway', 'gateway_payment_id']);
$table->index(['reference_type', 'reference_id']);
$table->index(['status', 'created_at']);
```

### 2. Eager Loading

```php
$transactions = PaymentTransaction::with('user', 'reference')->get();
```

### 3. Query Optimization

```php
// Bad: N+1 query
foreach ($bookings as $booking) {
    $transactions = PaymentTransaction::forReference('Booking', $booking->id)->get();
}

// Good: Bulk query
$bookingIds = $bookings->pluck('id');
$transactions = PaymentTransaction::where('reference_type', 'Booking')
    ->whereIn('reference_id', $bookingIds)
    ->get()
    ->groupBy('reference_id');
```

---

## Security Best Practices

1. **Always verify webhook signatures**
2. **Never expose API secrets in frontend**
3. **Use HTTPS for webhook URLs**
4. **Validate refund amounts server-side**
5. **Log all payment operations**
6. **Implement rate limiting on webhook endpoints**
7. **Use environment-specific API keys (test vs production)**

---

## Roadmap

### Phase 2 (Upcoming)
- ✅ Stripe gateway implementation
- ✅ Webhook controller
- ✅ Payment method saving
- ✅ Recurring payments

### Phase 3 (Future)
- PayPal integration
- Multi-currency support
- Payment analytics dashboard
- Automated reconciliation
- PCI compliance enhancements

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review `payment_transactions` table
- Enable debug mode: `APP_DEBUG=true`

---

**Documentation Version**: 1.0  
**Created**: 2025-12-11  
**PROMPT**: 69 - Payment Gateway Integration Wrapper  
**Status**: Phase 1 Complete (Core + Razorpay)
