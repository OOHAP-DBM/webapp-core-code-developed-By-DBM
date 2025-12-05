# Razorpay Webhook Testing Payloads

## Overview
Test payloads for simulating Razorpay webhook events during development and testing.

## Webhook Endpoint
```
POST /api/webhooks/razorpay
Content-Type: application/json
X-Razorpay-Signature: {HMAC SHA256 signature}
```

---

## 1. payment.authorized Event

**Description:** Triggered when customer completes payment authorization (manual capture mode). Payment is authorized but NOT yet captured.

**Payload:**
```json
{
  "entity": "event",
  "account_id": "acc_BFQ7uQEaa7j2z7",
  "event": "payment.authorized",
  "contains": ["payment"],
  "payload": {
    "payment": {
      "entity": {
        "id": "pay_OPmXKLGJYHQzqx",
        "entity": "payment",
        "amount": 50000,
        "currency": "INR",
        "status": "authorized",
        "order_id": "order_OPmWzSsjQG9M6E",
        "invoice_id": null,
        "international": false,
        "method": "card",
        "amount_refunded": 0,
        "refund_status": null,
        "captured": false,
        "description": "Booking #1 - Hoarding Advertisement",
        "card_id": "card_OPmXKLGJYHQzqy",
        "bank": null,
        "wallet": null,
        "vpa": null,
        "email": "customer@example.com",
        "contact": "+919876543210",
        "customer_id": "cust_OPmWzSsjQG9M6F",
        "token_id": null,
        "notes": [],
        "fee": 1000,
        "tax": 180,
        "error_code": null,
        "error_description": null,
        "error_source": null,
        "error_step": null,
        "error_reason": null,
        "acquirer_data": {
          "auth_code": "123456"
        },
        "created_at": 1733384786
      }
    }
  },
  "created_at": 1733384786
}
```

**Expected Behavior:**
1. Webhook signature validated
2. `PaymentAuthorized` event fired
3. `UpdateBookingOnPaymentAuthorized` listener triggered
4. Booking found by `order_id`
5. Booking updated:
   - `razorpay_payment_id` = "pay_OPmXKLGJYHQzqx"
   - `payment_status` = "authorized"
   - `payment_authorized_at` = now()
   - `status` = "payment_hold"
   - `hold_expiry_at` = now() + booking_hold_minutes (from Settings, default 30)
6. Status log created: "Payment authorized via webhook..."
7. Response: `{"success": true, "message": "Webhook processed successfully"}`

**Testing Command (cURL):**
```bash
curl -X POST http://localhost:8000/api/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: {generated_signature}" \
  -d @payment_authorized.json
```

---

## 2. payment.captured Event

**Description:** Triggered when payment is manually captured by admin/system. Funds are now transferred to merchant account.

**Payload:**
```json
{
  "entity": "event",
  "account_id": "acc_BFQ7uQEaa7j2z7",
  "event": "payment.captured",
  "contains": ["payment"],
  "payload": {
    "payment": {
      "entity": {
        "id": "pay_OPmXKLGJYHQzqx",
        "entity": "payment",
        "amount": 50000,
        "currency": "INR",
        "status": "captured",
        "order_id": "order_OPmWzSsjQG9M6E",
        "invoice_id": null,
        "international": false,
        "method": "card",
        "amount_refunded": 0,
        "refund_status": null,
        "captured": true,
        "description": "Booking #1 - Hoarding Advertisement",
        "card_id": "card_OPmXKLGJYHQzqy",
        "bank": null,
        "wallet": null,
        "vpa": null,
        "email": "customer@example.com",
        "contact": "+919876543210",
        "customer_id": "cust_OPmWzSsjQG9M6F",
        "token_id": null,
        "notes": [],
        "fee": 1000,
        "tax": 180,
        "error_code": null,
        "error_description": null,
        "error_source": null,
        "error_step": null,
        "error_reason": null,
        "acquirer_data": {
          "auth_code": "123456"
        },
        "created_at": 1733384786
      }
    }
  },
  "created_at": 1733386586
}
```

**Expected Behavior:**
1. Webhook signature validated
2. `PaymentCaptured` event fired
3. `OnPaymentCaptured` listener triggered
4. Booking found by `order_id`
5. `ScheduleBookingConfirmJob` dispatched to 'bookings' queue
6. Job executed (sync or queued):
   - Booking updated:
     - `payment_status` = "captured"
     - `payment_captured_at` = now()
     - `status` = "confirmed"
   - Status log created: "Payment captured successfully via webhook..."
   - `BookingStatusChanged` event fired
7. Response: `{"success": true, "message": "Webhook processed successfully"}`

**Testing Command (cURL):**
```bash
curl -X POST http://localhost:8000/api/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: {generated_signature}" \
  -d @payment_captured.json
```

---

## 3. payment.failed Event

**Description:** Triggered when payment authorization fails due to insufficient funds, card decline, network error, etc.

**Payload:**
```json
{
  "entity": "event",
  "account_id": "acc_BFQ7uQEaa7j2z7",
  "event": "payment.failed",
  "contains": ["payment"],
  "payload": {
    "payment": {
      "entity": {
        "id": "pay_OPmXKLGJYHQzqx",
        "entity": "payment",
        "amount": 50000,
        "currency": "INR",
        "status": "failed",
        "order_id": "order_OPmWzSsjQG9M6E",
        "invoice_id": null,
        "international": false,
        "method": "card",
        "amount_refunded": 0,
        "refund_status": null,
        "captured": false,
        "description": "Booking #1 - Hoarding Advertisement",
        "card_id": "card_OPmXKLGJYHQzqy",
        "bank": null,
        "wallet": null,
        "vpa": null,
        "email": "customer@example.com",
        "contact": "+919876543210",
        "customer_id": "cust_OPmWzSsjQG9M6F",
        "token_id": null,
        "notes": [],
        "fee": null,
        "tax": null,
        "error_code": "BAD_REQUEST_ERROR",
        "error_description": "Payment processing failed because of incorrect OTP",
        "error_source": "customer",
        "error_step": "payment_authentication",
        "error_reason": "authentication_failed",
        "acquirer_data": {},
        "created_at": 1733384786
      }
    }
  },
  "created_at": 1733384800
}
```

**Expected Behavior:**
1. Webhook signature validated
2. `PaymentFailed` event fired
3. `OnPaymentFailed` listener triggered
4. Booking found by `order_id`
5. Booking updated:
   - `payment_status` = "failed"
   - `payment_failed_at` = now()
   - `payment_error_code` = "BAD_REQUEST_ERROR"
   - `payment_error_description` = "Payment processing failed because of incorrect OTP"
   - `status` = "cancelled"
6. Status log created: "Payment failed via webhook. Error: BAD_REQUEST_ERROR..."
7. `BookingStatusChanged` event fired (payment_hold → cancelled)
8. Hold released (booking made available for others)
9. Response: `{"success": true, "message": "Webhook processed successfully"}`

**Testing Command (cURL):**
```bash
curl -X POST http://localhost:8000/api/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: {generated_signature}" \
  -d @payment_failed.json
```

---

## Common Error Codes in payment.failed

| Error Code | Description |
|------------|-------------|
| `BAD_REQUEST_ERROR` | Invalid request parameters or authentication failure |
| `GATEWAY_ERROR` | Bank/payment gateway is down |
| `SERVER_ERROR` | Razorpay server error |
| `NETWORK_ERROR` | Network connectivity issue |
| `AUTHENTICATION_ERROR` | 3D Secure/OTP authentication failed |
| `INSUFFICIENT_FUNDS` | Insufficient balance in account |
| `CARD_DECLINED` | Card declined by issuer |
| `INVALID_CARD_NUMBER` | Card number is invalid |
| `CARD_EXPIRED` | Card has expired |

---

## Signature Generation (for testing)

### PHP Example:
```php
<?php
$webhookSecret = env('RAZORPAY_WEBHOOK_SECRET'); // From .env
$payload = file_get_contents('payment_authorized.json');
$signature = hash_hmac('sha256', $payload, $webhookSecret);

echo "X-Razorpay-Signature: " . $signature;
```

### Node.js Example:
```javascript
const crypto = require('crypto');
const fs = require('fs');

const webhookSecret = 'your_webhook_secret_here';
const payload = fs.readFileSync('payment_authorized.json', 'utf8');
const signature = crypto.createHmac('sha256', webhookSecret)
                        .update(payload)
                        .digest('hex');

console.log('X-Razorpay-Signature:', signature);
```

### Python Example:
```python
import hmac
import hashlib
import json

webhook_secret = 'your_webhook_secret_here'
with open('payment_authorized.json', 'r') as f:
    payload = f.read()

signature = hmac.new(
    webhook_secret.encode('utf-8'),
    payload.encode('utf-8'),
    hashlib.sha256
).hexdigest()

print(f'X-Razorpay-Signature: {signature}')
```

---

## Testing Workflow

### 1. Setup Environment
```bash
# Add to .env
RAZORPAY_KEY_ID=rzp_test_xxxxxxxxxxxxx
RAZORPAY_KEY_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxx
```

### 2. Create Test Booking
```bash
# Create booking via API
POST /api/v1/bookings-v2/quotations/1/book

# Create Razorpay order
POST /api/v1/bookings-v2/1/create-order
# Response: order_id = "order_OPmWzSsjQG9M6E"
```

### 3. Simulate payment.authorized Webhook
```bash
# Update order_id in payload JSON to match created order
# Generate signature
php -r "echo hash_hmac('sha256', file_get_contents('payment_authorized.json'), 'whsec_xxxxxxxxxxxxxxxx');"

# Send webhook
curl -X POST http://localhost:8000/api/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: {generated_signature}" \
  -d @payment_authorized.json

# Check logs
tail -f storage/logs/laravel.log

# Verify booking
GET /api/v1/bookings-v2/1
# Expected: payment_status = "authorized", status = "payment_hold"
```

### 4. Simulate payment.captured Webhook
```bash
# Generate signature for captured payload
# Send webhook (same order_id and payment_id)
curl -X POST http://localhost:8000/api/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: {generated_signature}" \
  -d @payment_captured.json

# Verify booking
GET /api/v1/bookings-v2/1
# Expected: payment_status = "captured", status = "confirmed"
```

### 5. Simulate payment.failed Webhook (Alternative Flow)
```bash
# Create new booking with different order_id
# Send failed webhook
curl -X POST http://localhost:8000/api/webhooks/razorpay \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: {generated_signature}" \
  -d @payment_failed.json

# Verify booking
GET /api/v1/bookings-v2/2
# Expected: payment_status = "failed", status = "cancelled"
```

---

## Database Verification Queries

```sql
-- Check booking status
SELECT id, status, payment_status, payment_authorized_at, payment_captured_at, payment_failed_at
FROM bookings
WHERE id = 1;

-- Check status logs
SELECT id, booking_id, old_status, new_status, notes, created_at
FROM booking_status_logs
WHERE booking_id = 1
ORDER BY created_at DESC;

-- Check Razorpay logs
SELECT id, action, status_code, metadata, created_at
FROM razorpay_logs
WHERE action LIKE 'payment.%'
ORDER BY created_at DESC
LIMIT 10;
```

---

## Troubleshooting

### Issue: "Invalid webhook signature"
**Solution:**
- Verify `RAZORPAY_WEBHOOK_SECRET` in `.env` matches Razorpay dashboard setting
- Ensure payload JSON is exactly as sent (no whitespace changes)
- Generate signature using raw JSON string (not parsed object)

### Issue: "Booking not found"
**Solution:**
- Verify `order_id` in webhook payload matches `razorpay_order_id` in bookings table
- Check if order was created via `/api/v1/bookings-v2/{id}/create-order` endpoint

### Issue: "Job not executing"
**Solution:**
- Check queue configuration: `QUEUE_CONNECTION=database`
- Run queue worker: `php artisan queue:work --queue=bookings`
- Check failed_jobs table: `SELECT * FROM failed_jobs;`

### Issue: Listener not triggered
**Solution:**
- Verify events registered in `AppServiceProvider::boot()`
- Clear config cache: `php artisan config:clear`
- Check Laravel log for errors: `storage/logs/laravel.log`

---

## Postman Collection

Import this JSON into Postman for quick testing:

```json
{
  "info": {
    "name": "Razorpay Webhooks",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "payment.authorized",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "X-Razorpay-Signature",
            "value": "{{webhook_signature}}"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{{payment_authorized_payload}}"
        },
        "url": {
          "raw": "{{base_url}}/api/webhooks/razorpay",
          "host": ["{{base_url}}"],
          "path": ["api", "webhooks", "razorpay"]
        }
      }
    },
    {
      "name": "payment.captured",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "X-Razorpay-Signature",
            "value": "{{webhook_signature}}"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{{payment_captured_payload}}"
        },
        "url": {
          "raw": "{{base_url}}/api/webhooks/razorpay",
          "host": ["{{base_url}}"],
          "path": ["api", "webhooks", "razorpay"]
        }
      }
    },
    {
      "name": "payment.failed",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          },
          {
            "key": "X-Razorpay-Signature",
            "value": "{{webhook_signature}}"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{{payment_failed_payload}}"
        },
        "url": {
          "raw": "{{base_url}}/api/webhooks/razorpay",
          "host": ["{{base_url}}"],
          "path": ["api", "webhooks", "razorpay"]
        }
      }
    }
  ]
}
```

---

## Production Checklist

- [ ] Update `RAZORPAY_KEY_ID` to production key
- [ ] Update `RAZORPAY_KEY_SECRET` to production secret
- [ ] Generate new `RAZORPAY_WEBHOOK_SECRET` in Razorpay dashboard
- [ ] Configure webhook URL in Razorpay dashboard: `https://yourdomain.com/api/webhooks/razorpay`
- [ ] Enable events: `payment.authorized`, `payment.captured`, `payment.failed`
- [ ] Set up queue worker as systemd service
- [ ] Configure monitoring/alerting for failed webhooks
- [ ] Test signature validation with production secret
- [ ] Set up webhook retry logic (Razorpay retries automatically)
- [ ] Monitor `razorpay_logs` table for failed webhooks

---

## References

- [Razorpay Webhooks Documentation](https://razorpay.com/docs/webhooks/)
- [Razorpay Signature Validation](https://razorpay.com/docs/webhooks/validate-test/#signature-validation)
- [Razorpay Payment States](https://razorpay.com/docs/payments/payments/payment-methods/cards/#payment-flow)
- [Laravel Events Documentation](https://laravel.com/docs/10.x/events)
- [Laravel Queues Documentation](https://laravel.com/docs/10.x/queues)
