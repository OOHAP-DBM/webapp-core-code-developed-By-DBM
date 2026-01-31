@extends('layouts.vendor')

@section('title', 'POS Booking Details')

@section('content')
<div class="px-6 py-6">
    <div class="bg-white rounded-xl shadow border">

        {{-- Header --}}
        <div class="px-6 py-4 bg-primary text-white rounded-t-xl">
            <h4 class="text-lg font-semibold flex items-center gap-2">
                📄 POS Booking Details
            </h4>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <div id="booking-details" class="text-center text-gray-500">
                Loading booking details...
            </div>

            <!-- Error/Success Messages -->
            <div id="action-message" class="mt-4 hidden rounded-lg p-4"></div>
        </div>

    </div>
</div>

<!-- Modals -->
<!-- Mark as Paid Modal -->
<div id="mark-paid-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm">
        <h3 class="text-lg font-semibold mb-4">Mark Payment as Received</h3>
        <p class="text-gray-600 mb-4">Are you sure you want to mark this payment as received?</p>
        <p class="text-sm text-gray-500 mb-4">This action cannot be undone.</p>
        
        <div class="grid grid-cols-1 gap-2 mb-4">
            <div>
                <label class="block text-sm font-medium mb-1">Payment Amount *</label>
                <input type="number" id="payment-amount" step="0.01" min="0" 
                    class="w-full rounded-lg border border-gray-300 p-2">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Reference (Optional)</label>
                <input type="text" id="payment-reference"
                    class="w-full rounded-lg border border-gray-300 p-2" placeholder="e.g., Transaction ID">
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <button onclick="closeMarkPaidModal()" 
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                Cancel
            </button>
            <button onclick="confirmMarkPaid()"
                class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center gap-2">
                <span id="confirm-btn-text">✅ Mark as Paid</span>
                <span id="confirm-spinner" class="hidden">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>

<!-- Release Booking Modal -->
<div id="release-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-sm">
        <h3 class="text-lg font-semibold mb-2 text-red-600">⚠️ Release Booking</h3>
        <p class="text-gray-600 mb-2">Are you sure you want to release this booking?</p>
        <p class="text-sm text-red-500 mb-4"><strong>⚠️ This will cancel the booking and is PERMANENT.</strong></p>
        
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Reason (Optional)</label>
            <textarea id="release-reason" rows="2" placeholder="Why are you releasing this booking?"
                class="w-full rounded-lg border border-gray-300 p-2"></textarea>
        </div>

        <div class="flex gap-2 justify-end">
            <button onclick="closeReleaseModal()" 
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                No, Keep It
            </button>
            <button onclick="confirmRelease()"
                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-2">
                <span id="release-btn-text">🗑️ Yes, Release It</span>
                <span id="release-spinner" class="hidden">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>
</div>

<script>
const bookingId = @json($bookingId);
const API_URL = '/vendor/pos/api';
// const TOKEN = localStorage.getItem('token');
let currentBooking = null;

document.addEventListener('DOMContentLoaded', () => {
    // if (!TOKEN) {
    //     showActionMessage('Session expired. Please log in again.', 'error');
    //     setTimeout(() => window.location.href = '/login', 2000);
    //     return;
    // }

    loadBookingDetails();
    // Refresh countdown every minute
    setInterval(loadBookingDetails, 60000);
});

/**
 * Load booking details and render with action buttons based on state
 */
async function loadBookingDetails() {
    try {
        const response = await fetch(`${API_URL}/bookings/${bookingId}`, {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        // if (response.status === 401) {
        //     showActionMessage('Session expired. Please log in again.', 'error');
        //     setTimeout(() => window.location.href = '/login', 2000);
        //     return;
        // }

        const data = await response.json();
        const container = document.getElementById('booking-details');

        if (!data.success) {
            container.innerHTML = `
                <div class="text-red-500 font-medium">
                    Booking not found.
                </div>`;
            return;
        }

        currentBooking = data.data;
        const b = currentBooking;

        // Build hold status display if applicable
        let holdStatusHtml = '';
        if (b.hold_expiry_at) {
            const holdExpiry = new Date(b.hold_expiry_at);
            const now = new Date();
            const diff = holdExpiry - now;

            if (diff > 0) {
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                holdStatusHtml = `
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                        <span class="font-semibold text-yellow-800">⏰ Payment Hold Active</span><br>
                        <span class="text-sm text-yellow-700">Expires in ${days}d ${hours}h ${minutes}m</span>
                    </div>`;
            } else {
                // Hold expired
                holdStatusHtml = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                        <span class="font-semibold text-red-800">🔴 PAYMENT HOLD EXPIRED</span><br>
                        <span class="text-sm text-red-700">Payment is now OVERDUE. Please mark as paid or release immediately.</span>
                    </div>`;
            }
        }

        // Build reminder status
        let reminderHtml = '';
        if (b.reminder_count !== undefined) {
            const remindersLeft = 3 - b.reminder_count;
            reminderHtml = `
                <div class="mt-2">
                    <span class="font-semibold">Reminders:</span>
                    <span class="ml-2 px-2 py-1 rounded text-xs font-semibold ${remindersLeft > 0 ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700'}">
                        ${b.reminder_count}/3 sent
                    </span>
                </div>`;
        }

        container.innerHTML = `
            ${holdStatusHtml}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Invoice #:</span>
                    ${b.invoice_number || 'N/A'}
                </div>

                <div>
                    <span class="font-semibold">Booking Status:</span>
                    <span class="ml-2 px-2 py-1 rounded text-xs font-semibold ${getStatusColor(b.status)}">
                        ${b.status}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Customer:</span>
                    ${b.customer_name}
                </div>

                <div>
                    <span class="font-semibold">Phone:</span>
                    ${b.customer_phone || '-'}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Hoarding:</span>
                    ${
                        b.hoarding
                        ? `<a href="/hoardings/${b.hoarding.id}" target="_blank"
                             class="text-primary underline">
                             ${b.hoarding.title}
                           </a>`
                        : 'N/A'
                    }
                </div>

                <div>
                    <span class="font-semibold">Dates:</span>
                    ${new Date(b.start_date).toLocaleDateString()}
                    -
                    ${new Date(b.end_date).toLocaleDateString()}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Total Amount:</span>
                    ₹${parseFloat(b.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </div>

                <div>
                    <span class="font-semibold">Payment Status:</span>
                    <span class="ml-2 px-2 py-1 rounded text-xs font-semibold ${getPaymentStatusColor(b.payment_status)}">
                        ${b.payment_status}
                    </span>
                    ${reminderHtml}
                </div>
            </div>

            <div class="mt-4">
                <span class="font-semibold">Notes:</span>
                <div class="mt-1 text-gray-700">
                    ${b.notes || '-'}
                </div>
            </div>

            <!-- Action Buttons (rendered based on backend state) -->
            <div class="mt-6 flex flex-wrap gap-2">
                ${renderActionButtons(b)}
            </div>
        `;

    } catch (error) {
        console.error('Error loading booking:', error);
        document.getElementById('booking-details').innerHTML = `
            <div class="text-red-500 font-medium">
                Error loading booking details.
            </div>`;
    }
}

/**
 * Render action buttons based on backend state rules
 * BACKEND RULES:
 * - Mark paid: Only if payment_status in [unpaid, partial] AND status != cancelled
 * - Release: Only if payment_status = unpaid AND status in [draft, confirmed]
 * - Send reminder: Only if reminder_count < 3
 */
function renderActionButtons(booking) {
    let html = '';

    // Mark as Paid button
    // BACKEND RULE: payment_status in [unpaid, partial] AND status != cancelled
    if (['unpaid', 'partial'].includes(booking.payment_status) && booking.status !== 'cancelled') {
        html += `
            <button onclick="openMarkPaidModal()"
                class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm font-medium">
                💰 Mark as Paid
            </button>`;
    } else if (booking.payment_status === 'paid') {
        html += `
            <button disabled 
                class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
                title="Payment already received">
                ✓ Already Paid
            </button>`;
    } else if (booking.status === 'cancelled') {
        html += `
            <button disabled 
                class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
                title="Cannot mark paid - booking cancelled">
                ✗ Booking Cancelled
            </button>`;
    }

    // Release button
    // BACKEND RULE: payment_status = unpaid AND status in [draft, confirmed]
    if (booking.payment_status === 'unpaid' && ['draft', 'confirmed'].includes(booking.status)) {
        html += `
            <button onclick="openReleaseModal()"
                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium">
                🗑️ Release Booking
            </button>`;
    } else if (booking.status === 'active') {
        html += `
            <button disabled 
                class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
                title="Cannot release - booking already started">
                🚫 Cannot Release (Active)
            </button>`;
    } else if (booking.status === 'completed') {
        html += `
            <button disabled 
                class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
                title="Booking completed">
                ✓ Completed
            </button>`;
    } else if (booking.status === 'cancelled') {
        html += `
            <button disabled 
                class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
                title="Booking cancelled">
                ✗ Cancelled
            </button>`;
    } else if (booking.payment_status !== 'unpaid') {
        html += `
            <button disabled 
                class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
                title="Can only release if payment is unpaid">
                🚫 Cannot Release
            </button>`;
    }

    // Send Reminder button
    // BACKEND RULE: reminder_count < 3
    if (booking.reminder_count !== undefined && booking.reminder_count < 3) {
        html += `
            <button onclick="sendReminder()"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium">
                📧 Send Reminder
            </button>`;
    } else if (booking.reminder_count === 3) {
        html += `
            <button disabled 
                class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
                title="Maximum 3 reminders sent">
                📧 Max Reminders Sent
            </button>`;
    }

    // Back button
    html += `<a href="{{ route('vendor.pos.dashboard') }}"
        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm">
        ← Back
    </a>`;

    return html;
}

// Modal functions
function openMarkPaidModal() {
    document.getElementById('mark-paid-modal').classList.remove('hidden');
    document.getElementById('payment-amount').value = currentBooking.total_amount;
    document.getElementById('payment-reference').value = '';
}

function closeMarkPaidModal() {
    document.getElementById('mark-paid-modal').classList.add('hidden');
}

function openReleaseModal() {
    document.getElementById('release-modal').classList.remove('hidden');
    document.getElementById('release-reason').value = '';
}

function closeReleaseModal() {
    document.getElementById('release-modal').classList.add('hidden');
}

/**
 * Confirm and submit mark as paid
 */
async function confirmMarkPaid() {
    const amount = parseFloat(document.getElementById('payment-amount').value);
    const reference = document.getElementById('payment-reference').value;

    if (!amount || amount <= 0) {
        showActionMessage('Please enter a valid amount', 'error');
        return;
    }

    // Show loading
    document.getElementById('confirm-btn-text').classList.add('hidden');
    document.getElementById('confirm-spinner').classList.remove('hidden');
    document.querySelector('[onclick="confirmMarkPaid()"]').disabled = true;

    try {
        const response = await fetch(`${API_URL}/bookings/${bookingId}/mark-paid`, {
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                amount: amount,
                payment_reference: reference
            })
        });

        if (response.ok) {
            showActionMessage('✅ Payment marked as received successfully!', 'success');
            closeMarkPaidModal();
            setTimeout(() => loadBookingDetails(), 1500);
        } else if (response.status === 400) {
            const error = await response.json();
            showActionMessage(error.message || 'Cannot mark as paid - invalid state', 'error');
        } else if (response.status === 404) {
            showActionMessage('Booking not found', 'error');
        } else {
            const error = await response.json();
            showActionMessage(error.message || 'Error marking as paid', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showActionMessage('Network error. Please try again.', 'error');
    } finally {
        // Reset loading
        document.getElementById('confirm-btn-text').classList.remove('hidden');
        document.getElementById('confirm-spinner').classList.add('hidden');
        document.querySelector('[onclick="confirmMarkPaid()"]').disabled = false;
    }
}

/**
 * Confirm and submit release booking
 */
async function confirmRelease() {
    const reason = document.getElementById('release-reason').value;

    // Show loading
    document.getElementById('release-btn-text').classList.add('hidden');
    document.getElementById('release-spinner').classList.remove('hidden');
    document.querySelector('[onclick="confirmRelease()"]').disabled = true;

    try {
        const response = await fetch(`${API_URL}/bookings/${bookingId}/release`, {
            method: 'POST',
            headers: {
                // 'Authorization': 'Bearer ' + TOKEN,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
              credentials: 'same-origin',
            body: JSON.stringify({
                reason: reason
            })
        });

        if (response.ok) {
            showActionMessage('✅ Booking released successfully!', 'success');
            closeReleaseModal();
            setTimeout(() => loadBookingDetails(), 1500);
        } else if (response.status === 400) {
            const error = await response.json();
            showActionMessage(error.message || 'Cannot release - invalid state', 'error');
        } else {
            const error = await response.json();
            showActionMessage(error.message || 'Error releasing booking', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showActionMessage('Network error. Please try again.', 'error');
    } finally {
        // Reset loading
        document.getElementById('release-btn-text').classList.remove('hidden');
        document.getElementById('release-spinner').classList.add('hidden');
        document.querySelector('[onclick="confirmRelease()"]').disabled = false;
    }
}

/**
 * Send reminder
 */
async function sendReminder() {
    if (!confirm('Send payment reminder to customer?')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}/bookings/${bookingId}/send-reminder`, {
            method: 'POST',
            headers: {
               'Accept': 'application/json',
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin'
        });

        if (response.ok) {
            showActionMessage('✅ Reminder sent successfully!', 'success');
            loadBookingDetails();
        } else if (response.status === 429) {
            showActionMessage('⏰ You have already sent a reminder recently. Please wait 12 hours before sending another.', 'error');
        } else if (response.status === 400) {
            const error = await response.json();
            showActionMessage(error.message || 'Cannot send reminder', 'error');
        } else {
            const error = await response.json();
            showActionMessage(error.message || 'Error sending reminder', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showActionMessage('Network error. Please try again.', 'error');
    }
}

function showActionMessage(message, type) {
    const msgDiv = document.getElementById('action-message');
    msgDiv.className = `mt-4 rounded-lg p-4 ${
        type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700'
    }`;
    msgDiv.textContent = message;
    msgDiv.classList.remove('hidden');
    
    // Auto-hide after 5 seconds
    if (type === 'success') {
        setTimeout(() => msgDiv.classList.add('hidden'), 5000);
    }
}

// Helper functions
function getStatusColor(status) {
    const colors = {
        draft: 'bg-gray-400 text-white',
        confirmed: 'bg-green-500 text-white',
        active: 'bg-blue-500 text-white',
        completed: 'bg-cyan-500 text-white',
        cancelled: 'bg-red-500 text-white'
    };
    return colors[status] || 'bg-gray-400 text-white';
}

function getPaymentStatusColor(status) {
    const colors = {
        paid: 'bg-green-500 text-white',
        unpaid: 'bg-red-500 text-white',
        partial: 'bg-yellow-500 text-white',
        credit: 'bg-cyan-500 text-white'
    };
    return colors[status] || 'bg-gray-400 text-white';
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const markPaidModal = document.getElementById('mark-paid-modal');
    const releaseModal = document.getElementById('release-modal');
    
    if (event.target === markPaidModal) {
        closeMarkPaidModal();
    }
    if (event.target === releaseModal) {
        closeReleaseModal();
    }
});
</script>
@endsection