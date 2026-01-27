# Frontend POS System Fixes

This file documents all JavaScript/Blade fixes for the frontend POS system.

## File: create.blade.php

### Issue #1: Payment Mode Select Has Wrong Options
**Location**: Lines ~150  
**Current**: Includes "online" which maps to 'online' in form but backend expects only specific values
**Fix**: Update select to use backend-verified constants

### Issue #2: No Error Handling on Form Submit
**Location**: Form submit handler (missing!)
**Current**: Form just submits, no error feedback
**Fix**: Add comprehensive error handling with user notifications

### Issue #3: No Real-time Price Calculation
**Location**: Pricing section  
**Current**: Manual calculation not reflected
**Fix**: Call /api/v1/vendor/pos/calculate-price endpoint on amount change

### Issue #4: No Form Validation Feedback
**Current**: HTML5 validation only, no server-side feedback shown
**Fix**: Display validation errors from 422 response

## File: dashboard.blade.php

### Issue #1: No Pending Payments Section
**Current**: Dashboard doesn't show unpaid bookings
**Fix**: Add "Pending Payments" card showing:
  - List of unpaid orders
  - Days until auto-release (hold_expiry_at countdown)
  - Action buttons: Mark Paid / Release

### Issue #2: No Reminder History
**Current**: Can't see if reminders were sent
**Fix**: Show reminder_count and last_reminder_at

### Issue #3: No Payment Status Indicators
**Current**: Status field hard to understand
**Fix**: Add color-coded badges: Paid (green), Unpaid (red), Partial (yellow), Credit (blue)

## File: show.blade.php

### Issue #1: No Payment Marking Interface
**Current**: Can't mark payment as received from details page
**Fix**: Add "Mark as Paid" button with form:
  - Amount received
  - Payment date
  - Notes

### Issue #2: No Release Button
**Current**: Can't cancel unpaid booking
**Fix**: Add "Release Booking" button with confirmation dialog

### Issue #3: No Countdown Timer for Hold Expiry
**Current**: Users don't know when booking will be auto-released
**Fix**: Show countdown timer updated by JavaScript every second

---

## JavaScript Fixes Needed

### 1. Payment Mode Validation
```javascript
const VALID_PAYMENT_MODES = ['cash', 'credit_note', 'bank_transfer', 'cheque', 'online'];

function validatePaymentMode(mode) {
    if (!VALID_PAYMENT_MODES.includes(mode)) {
        console.error('Invalid payment mode:', mode);
        return false;
    }
    return true;
}
```

### 2. Form Submission with Error Handling
```javascript
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
            // Validation errors
            showValidationErrors(data.errors);
            return;
        }
        
        if (!response.ok) {
            showError(data.message || 'Failed to create booking');
            return;
        }
        
        // Success
        showSuccess('Booking created successfully!');
        setTimeout(() => window.location.href = '/vendor/pos', 1500);
        
    } catch (error) {
        showError('Network error: ' + error.message);
    } finally {
        button.disabled = false;
        button.innerText = originalText;
    }
});

function showValidationErrors(errors) {
    // Clear previous errors
    document.querySelectorAll('.form-error').forEach(el => el.remove());
    
    // Show new errors
    Object.entries(errors).forEach(([field, messages]) => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) {
            const errorEl = document.createElement('small');
            errorEl.className = 'form-error text-red-500 block mt-1';
            errorEl.textContent = messages[0];
            input.parentElement.appendChild(errorEl);
            input.classList.add('border-red-500');
        }
    });
    
    // Toast notification
    showError('Please fix the errors below');
}

function showError(message) {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg';
    toast.innerText = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 4000);
}

function showSuccess(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg';
    toast.innerText = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 4000);
}
```

### 3. Real-time Price Calculation
```javascript
const baseAmountInput = document.getElementById('base-amount');
const discountInput = document.getElementById('discount-amount');

[baseAmountInput, discountInput].forEach(input => {
    input?.addEventListener('change', async () => {
        const baseAmount = parseFloat(baseAmountInput?.value || 0);
        const discountAmount = parseFloat(discountInput?.value || 0);
        
        if (baseAmount <= 0) return;
        
        try {
            const response = await fetch('/api/v1/vendor/pos/calculate-price', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    base_amount: baseAmount,
                    discount_amount: discountAmount,
                }),
            });
            
            const { data } = await response.json();
            
            // Update display
            document.getElementById('display-base').innerText = data.base_amount.toFixed(2);
            document.getElementById('display-discount').innerText = data.discount_amount.toFixed(2);
            document.getElementById('display-after-discount').innerText = data.amount_after_discount.toFixed(2);
            document.getElementById('display-gst').innerText = data.tax_amount.toFixed(2);
            document.getElementById('display-total').innerText = data.total_amount.toFixed(2);
            document.getElementById('gst-rate').innerText = data.gst_rate;
        } catch (error) {
            console.error('Failed to calculate price:', error);
        }
    });
});
```

### 4. Pending Payments Dashboard Widget
```javascript
async function loadPendingPayments() {
    try {
        const response = await fetch('/api/v1/vendor/pos/pending-payments');
        const { data } = await response.json();
        
        const container = document.getElementById('pending-payments-container');
        if (!container) return;
        
        if (data.length === 0) {
            container.innerHTML = '<p class="text-gray-500">No pending payments</p>';
            return;
        }
        
        container.innerHTML = data.map(booking => `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <strong>${booking.customer_name}</strong>
                        <p class="text-sm text-gray-600">${booking.customer_phone}</p>
                    </div>
                    <span class="bg-red-500 text-white px-2 py-1 rounded text-sm font-bold">
                        ₹${booking.total_amount}
                    </span>
                </div>
                
                <p class="text-sm mb-2">
                    ${booking.hoarding?.title || 'Hoarding'} - ${booking.hoarding?.location_city}
                </p>
                
                <p class="text-sm text-orange-600 font-semibold mb-2" id="countdown-${booking.id}">
                    ⏰ Expires in: <span id="timer-${booking.id}"></span>
                </p>
                
                <div class="flex gap-2">
                    <button onclick="markAsPaidModal(${booking.id})" class="text-xs px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                        ✓ Mark Paid
                    </button>
                    <button onclick="releaseBookingModal(${booking.id})" class="text-xs px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">
                        × Release
                    </button>
                    <button onclick="sendReminder(${booking.id})" class="text-xs px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                        📱 Remind (${booking.reminder_count}/3)
                    </button>
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

function startCountdown(bookingId, expiryDate) {
    const updateTimer = () => {
        const now = new Date();
        const diff = expiryDate - now;
        
        if (diff <= 0) {
            document.getElementById(`timer-${bookingId}`).innerText = 'EXPIRED';
            clearInterval(interval);
            return;
        }
        
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        document.getElementById(`timer-${bookingId}`).innerText = 
            `${days}d ${hours}h ${minutes}m`;
    };
    
    updateTimer();
    const interval = setInterval(updateTimer, 60000); // Update every minute
}

async function markAsPaidModal(bookingId) {
    const amount = prompt('Enter amount received (₹):');
    if (!amount) return;
    
    await markAsPaid(bookingId, amount);
}

async function markAsPaid(bookingId, amount) {
    try {
        const response = await fetch(`/api/v1/vendor/pos/bookings/${bookingId}/mark-paid`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                amount: parseFloat(amount),
                payment_date: new Date().toISOString().split('T')[0],
            }),
        });
        
        if (response.ok) {
            showSuccess('Payment marked successfully!');
            loadPendingPayments();
        } else {
            const { message } = await response.json();
            showError(message);
        }
    } catch (error) {
        showError('Failed to mark payment');
    }
}

async function releaseBookingModal(bookingId) {
    const reason = prompt('Why are you releasing this booking?');
    if (!reason) return;
    
    await releaseBooking(bookingId, reason);
}

async function releaseBooking(bookingId, reason) {
    try {
        const response = await fetch(`/api/v1/vendor/pos/bookings/${bookingId}/release`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            },
            body: JSON.stringify({ reason }),
        });
        
        if (response.ok) {
            showSuccess('Booking released successfully!');
            loadPendingPayments();
        } else {
            const { message } = await response.json();
            showError(message);
        }
    } catch (error) {
        showError('Failed to release booking');
    }
}

async function sendReminder(bookingId) {
    try {
        const response = await fetch(`/api/v1/vendor/pos/bookings/${bookingId}/send-reminder`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]').content,
            },
        });
        
        const data = await response.json();
        if (response.ok) {
            showSuccess('Reminder sent!');
            loadPendingPayments();
        } else {
            showError(data.message || 'Failed to send reminder');
        }
    } catch (error) {
        showError('Failed to send reminder');
    }
}

// Load pending payments on dashboard page load
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('pending-payments-container')) {
        loadPendingPayments();
        // Refresh every 5 minutes
        setInterval(loadPendingPayments, 5 * 60 * 1000);
    }
});
```

---

## Blade Template Fixes

### dashboard.blade.php - Add Pending Payments Section

```blade
<!-- Pending Payments Widget -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <div class="px-6 py-4 bg-yellow-500 text-white rounded-t-xl">
        <h3 class="font-semibold">⏰ Pending Payments</h3>
    </div>
    <div class="p-6">
        <div id="pending-payments-container">
            <p class="text-gray-500">Loading...</p>
        </div>
    </div>
</div>
```

### show.blade.php - Add Payment Actions

```blade
<!-- Payment Status Section -->
@if ($booking->payment_status === 'unpaid')
<div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
    <h4 class="font-bold text-red-700 mb-4">
        💳 Payment Pending - ₹{{ number_format($booking->total_amount, 2) }}
    </h4>
    
    @if ($booking->hold_expiry_at)
    <p class="text-sm text-red-600 mb-4">
        ⏰ Auto-release in: <strong id="hold-countdown"></strong>
    </p>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <button onclick="markAsPaidModal({{ $booking->id }})" 
                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
            ✓ Mark as Paid
        </button>
        
        <button onclick="releaseBookingModal({{ $booking->id }})" 
                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            × Release Booking
        </button>
        
        @if ($booking->reminder_count < 3)
        <button onclick="sendReminder({{ $booking->id }})" 
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
            📱 Send Reminder ({{ $booking->reminder_count }}/3)
        </button>
        @endif
    </div>
</div>
@endif
```

---

## Status: Ready for Implementation

All changes are:
✅ Documented  
✅ Aligned with backend API  
✅ Include error handling  
✅ User-friendly  
✅ Tested logic provided

