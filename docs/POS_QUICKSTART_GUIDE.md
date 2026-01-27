# POS System - Quick Start Integration Guide

**Status**: 🟢 Ready for Implementation  
**Time to Deploy**: 3-4 hours total  

---

## 🚀 Deployment Checklist

### Step 1: Database Migration (5 minutes)
```bash
# Run the new migration
php artisan migrate

# Verify new columns exist
php artisan tinker
>>> DB::table('pos_bookings')->getColumnListing()
# Should include: hold_expiry_at, payment_received_at, reminder_count, last_reminder_at
```

### Step 2: Test Backend API (15 minutes)

Use Postman/Insomnia to test these endpoints:

#### Test 2.1: Create Booking
```
POST http://localhost/api/v1/vendor/pos/bookings
Authorization: Bearer <vendor_token>
Content-Type: application/json

{
  "customer_name": "John Doe",
  "customer_phone": "9876543210",
  "customer_email": "john@example.com",
  "booking_type": "ooh",
  "hoarding_id": 1,
  "start_date": "2026-02-15",
  "end_date": "2026-03-15",
  "base_amount": 50000,
  "discount_amount": 5000,
  "payment_mode": "cash",
  "notes": "Test booking"
}

Expected Response (201):
{
  "success": true,
  "data": {
    "id": 123,
    "payment_status": "unpaid",
    "hold_expiry_at": "2026-02-03T10:30:45Z",  // 7 days from now
    "reminder_count": 0,
    ...
  }
}
```

#### Test 2.2: Mark Payment
```
POST http://localhost/api/v1/vendor/pos/bookings/123/mark-paid
Authorization: Bearer <vendor_token>
Content-Type: application/json

{
  "amount": 45000,
  "payment_date": "2026-01-27",
  "notes": "Cheque received"
}

Expected Response (200):
{
  "success": true,
  "data": {
    "id": 123,
    "payment_status": "paid",
    "paid_amount": 45000,
    "hold_expiry_at": null,  // CLEARED!
    "reminder_count": 0,  // RESET!
    ...
  }
}
```

#### Test 2.3: Get Pending Payments
```
GET http://localhost/api/v1/vendor/pos/pending-payments
Authorization: Bearer <vendor_token>

Expected Response (200):
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": 122,
      "customer_name": "Jane Doe",
      "total_amount": 30000,
      "hold_expiry_at": "2026-02-01T...",  // Expires soon
      "reminder_count": 1,
      ...
    },
    {
      "id": 124,
      "customer_name": "Bob Smith",
      "total_amount": 60000,
      "hold_expiry_at": "2026-02-10T...",  // Expires later
      "reminder_count": 0,
      ...
    }
  ]
}
```

#### Test 2.4: Send Reminder
```
POST http://localhost/api/v1/vendor/pos/bookings/122/send-reminder
Authorization: Bearer <vendor_token>

Expected Response (200):
{
  "success": true,
  "data": {
    "reminder_count": 2,
    "last_reminder_at": "2026-01-27T10:45:00Z"
  }
}

# Try again immediately:
Expected Response (429 - Rate Limited):
{
  "success": false,
  "message": "Please wait before sending another reminder"
}
```

#### Test 2.5: Release Booking
```
POST http://localhost/api/v1/vendor/pos/bookings/124/release
Authorization: Bearer <vendor_token>
Content-Type: application/json

{
  "reason": "Customer cancelled order"
}

Expected Response (200):
{
  "success": true,
  "data": {
    "id": 124,
    "status": "cancelled",
    "hold_expiry_at": null,
    "reminder_count": 0,
    "cancelled_at": "2026-01-27T10:50:00Z",
    "cancellation_reason": "Customer cancelled order"
  }
}
```

### Step 3: Implement Frontend (90 minutes)

#### File 1: `resources/views/vendor/pos/create.blade.php`

Find this section and add error handling:
```blade
<form id="pos-booking-form">
    @csrf
    <!-- ... form fields ... -->
    <button type="submit" class="px-6 py-3 rounded-lg bg-blue-600 text-white">
        💾 Create Booking
    </button>
</form>

<!-- ADD THIS SCRIPT BLOCK -->
<script>
document.getElementById('pos-booking-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const button = e.target.querySelector('button[type="submit"]');
    const originalText = button.innerText;
    
    try {
        button.disabled = true;
        button.innerText = 'Creating...';
        
        const response = await fetch('/api/v1/vendor/pos/bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            },
            body: JSON.stringify(Object.fromEntries(formData)),
        });
        
        const data = await response.json();
        
        if (response.status === 422) {
            // Show validation errors
            Object.entries(data.errors || {}).forEach(([field, messages]) => {
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('border-red-500');
                    const errorEl = document.createElement('small');
                    errorEl.className = 'text-red-500 block mt-1';
                    errorEl.textContent = messages[0];
                    input.parentElement.appendChild(errorEl);
                }
            });
            return;
        }
        
        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to create booking'));
            return;
        }
        
        alert('Booking created successfully!');
        window.location.href = '/vendor/pos';
        
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        button.disabled = false;
        button.innerText = originalText;
    }
});
</script>
```

#### File 2: `resources/views/vendor/pos/dashboard.blade.php`

Add pending payments section:
```blade
<!-- Pending Payments Widget -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 bg-yellow-500 text-white rounded-t-xl">
        <h3 class="font-semibold">⏰ Pending Payments</h3>
    </div>
    <div class="p-6">
        <div id="pending-payments-list">
            <p class="text-gray-500 text-center py-8">Loading...</p>
        </div>
    </div>
</div>

<script>
async function loadPendingPayments() {
    try {
        const response = await fetch('/api/v1/vendor/pos/pending-payments', {
            headers: {
                'Authorization': 'Bearer ' + document.querySelector('[data-token]')?.getAttribute('data-token')
            }
        });
        const { data } = await response.json();
        
        const container = document.getElementById('pending-payments-list');
        
        if (data.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center">No pending payments</p>';
            return;
        }
        
        container.innerHTML = data.map(booking => `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <strong>${booking.customer_name}</strong>
                        <p class="text-sm text-gray-600">${booking.customer_phone}</p>
                    </div>
                    <span class="bg-red-500 text-white px-2 py-1 rounded font-bold">
                        ₹${parseFloat(booking.total_amount).toFixed(2)}
                    </span>
                </div>
                
                <p class="text-sm mb-3">
                    <strong>Expires:</strong> <span id="timer-${booking.id}"></span>
                </p>
                
                <div class="flex gap-2 flex-wrap">
                    <button onclick="markAsPaid(${booking.id})" class="px-3 py-1 text-sm bg-green-500 text-white rounded hover:bg-green-600">
                        ✓ Mark Paid
                    </button>
                    <button onclick="releaseBooking(${booking.id})" class="px-3 py-1 text-sm bg-gray-500 text-white rounded hover:bg-gray-600">
                        × Release
                    </button>
                    ${booking.reminder_count < 3 ? `
                    <button onclick="sendReminder(${booking.id})" class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600">
                        📱 Remind (${booking.reminder_count}/3)
                    </button>
                    ` : ''}
                </div>
            </div>
        `).join('');
        
        // Start countdown timers
        data.forEach(booking => {
            if (booking.hold_expiry_at) {
                startCountdown(booking.id, new Date(booking.hold_expiry_at));
            }
        });
        
    } catch (error) {
        console.error('Failed to load pending payments:', error);
    }
}

function startCountdown(id, expiryDate) {
    const update = () => {
        const diff = expiryDate - new Date();
        if (diff <= 0) {
            document.getElementById(`timer-${id}`).innerHTML = '<span class="text-red-600 font-bold">EXPIRED</span>';
            return;
        }
        const days = Math.floor(diff / 86400000);
        const hours = Math.floor((diff % 86400000) / 3600000);
        document.getElementById(`timer-${id}`).innerText = `${days}d ${hours}h`;
    };
    update();
    setInterval(update, 60000);
}

async function markAsPaid(id) {
    const amount = prompt('Enter amount received (₹):');
    if (!amount) return;
    
    const response = await fetch(`/api/v1/vendor/pos/bookings/${id}/mark-paid`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
        },
        body: JSON.stringify({ amount: parseFloat(amount) })
    });
    
    if (response.ok) {
        alert('Payment marked successfully!');
        loadPendingPayments();
    } else {
        const data = await response.json();
        alert('Error: ' + data.message);
    }
}

async function releaseBooking(id) {
    const reason = prompt('Why are you releasing this booking?');
    if (!reason) return;
    
    const response = await fetch(`/api/v1/vendor/pos/bookings/${id}/release`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
        },
        body: JSON.stringify({ reason })
    });
    
    if (response.ok) {
        alert('Booking released successfully!');
        loadPendingPayments();
    }
}

async function sendReminder(id) {
    const response = await fetch(`/api/v1/vendor/pos/bookings/${id}/send-reminder`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
        }
    });
    
    if (response.ok) {
        alert('Reminder sent!');
        loadPendingPayments();
    } else {
        const data = await response.json();
        alert('Error: ' + data.message);
    }
}

// Load on page load and refresh every 5 minutes
document.addEventListener('DOMContentLoaded', () => {
    loadPendingPayments();
    setInterval(loadPendingPayments, 5 * 60 * 1000);
});
</script>
```

### Step 4: Verify All Workflows (30 minutes)

Run through the testing checklist:
```
✅ Create booking → verify hold_expiry_at set
✅ Mark payment → verify hold_expiry_at cleared
✅ Release booking → verify cancelled status
✅ Send reminder → verify count incremented
✅ Rate limit → verify can't send twice in 12 hours
✅ Pending list → verify sorted by urgency
```

### Step 5: Deploy (10 minutes)

```bash
# Commit changes
git add -A
git commit -m "FEATURE: POS payment workflow - mark paid, release, reminders"

# Push to staging
git push origin feature/pos-payment-workflow

# After approval, merge to main and deploy
git merge --squash feature/pos-payment-workflow
git push origin main

# On production:
php artisan migrate --force
php artisan config:cache
```

---

## 📞 Troubleshooting

### Issue: Migration fails
```
Solution: Check if columns already exist
php artisan tinker
>>> DB::table('pos_bookings')->getColumnListing()
If they exist, comment out the `up()` method and run
```

### Issue: API returns 404
```
Solution: Check routes are loaded
php artisan route:list | grep vendor/pos
Should see: POST /api/v1/vendor/pos/bookings/{id}/mark-paid
```

### Issue: Payment status not updating
```
Solution: Check model fillable array
grep "payment_status" Modules/POS/Models/POSBooking.php
Should be in $fillable array
```

### Issue: No countdown timer showing
```
Solution: Check JavaScript errors in browser console
Verify hold_expiry_at is being returned from API
Check CSS for #timer-{id} element visibility
```

---

## 📊 Quick Reference

### API Endpoints Summary
| Method | URL | Purpose |
|--------|-----|---------|
| POST | `/api/v1/vendor/pos/bookings` | Create booking |
| POST | `/api/v1/vendor/pos/bookings/{id}/mark-paid` | Mark payment received |
| POST | `/api/v1/vendor/pos/bookings/{id}/release` | Release hold |
| GET | `/api/v1/vendor/pos/pending-payments` | List unpaid bookings |
| POST | `/api/v1/vendor/pos/bookings/{id}/send-reminder` | Send reminder |

### Field Reference
| Field | Purpose | Set By |
|-------|---------|--------|
| `hold_expiry_at` | When booking auto-releases | createBooking() |
| `payment_status` | unpaid/paid/partial/credit | markPaymentReceived() |
| `payment_received_at` | When payment was recorded | markPaymentReceived() |
| `reminder_count` | Number of reminders sent | sendReminder() |
| `last_reminder_at` | When last reminder was sent | sendReminder() |

---

## ✅ You're Ready!

All backend code is complete, tested, and documented.  
Frontend implementation guide is provided.  
No architectural changes needed.  
Database migration is safe and reversible.

**Time estimate**: 3-4 hours total deployment  
**Risk level**: Low (isolated POS module)  
**Rollback**: Simple (revert migration if needed)

---

**Questions?** Check the full docs:
- Audit: `docs/POS_SYSTEM_AUDIT_AND_FIXES.md`
- Frontend: `docs/POS_FRONTEND_FIXES.md`
- Summary: `docs/POS_IMPLEMENTATION_COMPLETE.md`

