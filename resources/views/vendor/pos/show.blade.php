@extends('layouts.vendor')

@section('title', 'POS Booking Details')

@section('content')
<div class="px-6 py-6">
    <div class="bg-white rounded-xl shadow">

        {{-- Header --}}
        <div class="px-6 py-4 bg-primary text-white flex items-center justify-between">
            <h4 class="text-lg font-semibold flex items-center gap-2">
                📄 POS Booking Details
            </h4>
            <a href="{{ route('vendor.pos.dashboard') }}"
               class="text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg">
                ← Back
            </a>
        </div>

        {{-- Body --}}
        <div class="p-6 space-y-6">

            <!-- Booking Summary -->
            <div class="rounded-xl border bg-gray-50 p-5 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Invoice</p>
                    <h2 id="ui-invoice" class="text-lg font-semibold">—</h2>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Booking Status</p>
                    <span id="ui-booking-status"
                          class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-300">
                        —
                    </span>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Payment Status</p>
                    <span id="ui-payment-status"
                          class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-300">
                        —
                    </span>
                </div>

                <div class="text-right">
                    <p class="text-xs text-gray-500">Total Amount</p>
                    <p id="ui-total" class="text-2xl font-bold text-gray-900">₹0.00</p>
                </div>
            </div>

            <!-- Dynamic Content -->
            <div id="booking-details" class="space-y-6 text-sm text-gray-700">
                <div class="text-center text-gray-400 py-10">
                    Loading booking details…
                </div>
            </div>

            <!-- Action Message -->
            <div id="action-message" class="hidden rounded-lg p-4"></div>

        </div>
    </div>
</div>

<!-- MARK AS PAID MODAL -->
<div id="mark-paid-modal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl animate-fadeIn">
        <h3 class="text-lg font-semibold mb-2">💰 Mark Payment as Received</h3>
        <p class="text-gray-600 text-sm mb-4">
            Confirm payment details before marking as paid.
        </p>

        <div class="space-y-3 mb-5">
            <div>
                <label class="block text-sm font-medium mb-1">Payment Amount *</label>
                <input type="number" id="payment-amount"
                       class="w-full rounded-lg border border-gray-300 p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Reference (Optional)</label>
                <input type="text" id="payment-reference"
                       class="w-full rounded-lg border border-gray-300 p-2"
                       placeholder="Transaction ID / Cash Ref">
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <button onclick="closeMarkPaidModal()"
                    class="px-4 py-2 rounded-lg border hover:bg-gray-100">
                Cancel
            </button>
            <button onclick="confirmMarkPaid()"
                    class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center gap-2">
                <span id="confirm-btn-text">Mark as Paid</span>
                <span id="confirm-spinner" class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
        </div>
    </div>
</div>

<!-- RELEASE MODAL -->
<div id="release-modal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-xl animate-fadeIn">
        <h3 class="text-lg font-semibold text-red-600 mb-2">⚠️ Release Booking</h3>
        <p class="text-sm text-gray-600 mb-3">
            This will cancel the booking permanently.
        </p>

        <textarea id="release-reason" rows="3"
                  class="w-full rounded-lg border border-gray-300 p-2 mb-4"
                  placeholder="Reason (optional)"></textarea>

        <div class="flex justify-end gap-2">
            <button onclick="closeReleaseModal()"
                    class="px-4 py-2 rounded-lg border hover:bg-gray-100">
                Keep Booking
            </button>
            <button onclick="confirmRelease()"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center gap-2">
                <span id="release-btn-text">Release</span>
                <span id="release-spinner"
                      class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: scale(.96); }
    to { opacity: 1; transform: scale(1); }
}
.animate-fadeIn {
    animation: fadeIn .15s ease-out;
}
</style>

<script>


const bookingId = @json($bookingId);
const API_URL = '/vendor/pos/api';
let currentBooking = null;

document.addEventListener('DOMContentLoaded', () => {
    loadBookingDetails();
    setInterval(loadBookingDetails, 60000);
});

async function loadBookingDetails() {
    try {
        const response = await fetch(`${API_URL}/bookings/${bookingId}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });

        const data = await response.json();
        const container = document.getElementById('booking-details');

        if (!data.success) {
            container.innerHTML = `<div class="text-red-500">Booking not found</div>`;
            return;
        }

        currentBooking = data.data;
        const b = currentBooking;

        // 🔹 UI SUMMARY SYNC (NEW)
        document.getElementById('ui-invoice').textContent = b.invoice_number || '—';
        document.getElementById('ui-total').textContent =
            '₹' + parseFloat(b.total_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });

        document.getElementById('ui-booking-status').textContent = b.status;
        document.getElementById('ui-booking-status').className =
            'inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold ' + getStatusColor(b.status);

        document.getElementById('ui-payment-status').textContent = b.payment_status;
        document.getElementById('ui-payment-status').className =
            'inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold ' + getPaymentStatusColor(b.payment_status);

        /* ---- REST OF YOUR EXISTING HTML BUILD LOGIC ---- */
        container.innerHTML = `
            <div class="grid md:grid-cols-2 gap-4">
                <div><strong>Customer:</strong> ${b.customer_name}</div>
                <div><strong>Phone:</strong> ${b.customer_phone || '-'}</div>
                <div><strong>Dates:</strong>
                    ${new Date(b.start_date).toLocaleDateString()} -
                    ${new Date(b.end_date).toLocaleDateString()}
                </div>
                <div><strong>Notes:</strong> ${b.notes || '-'}</div>
            </div>

            <div class="flex flex-wrap gap-2 pt-4 border-t">
                ${renderActionButtons(b)}
            </div>
        `;

    } catch (e) {
        document.getElementById('booking-details').innerHTML =
            `<div class="text-red-500">Error loading booking</div>`;
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
                class="px-4 py-2 rounded-lg btn-color text-sm font-medium">
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