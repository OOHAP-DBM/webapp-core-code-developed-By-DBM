@extends($posLayout ?? 'layouts.vendor')

@section('title', 'POS Booking Details')
@section('content')
<div class="px-4 sm:px-6 py-4 sm:py-6">
    @include('vendor.pos.components.admin-vendor-switcher')
    <div class="bg-white rounded-xl shadow">

        {{-- Header --}}
        <div class="px-4 sm:px-6 py-4 bg-primary text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h4 class="text-lg sm:text-xl font-bold text-gray-800 flex items-center gap-2">
                 POS Booking Details
            </h4>
            <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.dashboard') }}"
               class="w-full sm:w-auto text-center text-sm bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg">
                ← Back
            </a>
        </div>

        {{-- Body --}}
        <div class="p-4 sm:p-6 space-y-6">

            <!-- Booking Summary -->
            <div class="rounded-xl border bg-gray-50 p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Invoice</p>
                    <h2 class="text-lg font-semibold"><a id="ui-invoice" href="#" class="pointer-events-none text-inherit">—</a></h2>
                    <a id="ui-invoice-link" href="#" target="_blank" class="hidden text-xs text-blue-600 hover:underline">View Invoice PDF</a>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Booking Status</p>
                    <span id="ui-booking-status"
                          class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-300">
                        —
                    </span>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Payment Status</p>
                    <span id="ui-payment-status"
                          class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold bg-gray-300">
                        —
                    </span>
                </div>

                <div class="text-left lg:text-right">
                    <p class="text-sm text-gray-500">Total Amount</p>
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
    <div class="bg-white rounded-2xl p-6 max-w-sm sm:max-w-md w-full mx-4 shadow-xl animate-fadeIn">
        <h3 class="text-lg sm:text-xl font-semibold mb-2">💰 Mark Payment as Received</h3>
        <p class="text-gray-600 text-sm mb-4">
            Confirm payment details before marking as paid.
        </p>

        <div class="space-y-3 mb-5">
            <div>
                <label class="block text-sm font-medium mb-1">Payment Amount *</label>
                <input type="number" id="payment-amount"
                       min="0.01" step="0.01"
                       class="w-full rounded-lg border border-gray-300 p-2">
                <p id="payment-amount-help" class="text-xs text-gray-500 mt-1"></p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Reference (Optional)</label>
                <input type="text" id="payment-reference"
                       class="w-full rounded-lg border border-gray-300 p-2"
                       placeholder="Transaction ID / Cash Ref">
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
            <button onclick="closeMarkPaidModal()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border hover:bg-gray-100">
                Cancel
            </button>
            <button onclick="confirmMarkPaid()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center justify-center gap-2">
                <span id="confirm-btn-text">Mark as Paid</span>
                <span id="confirm-spinner" class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
        </div>
    </div>
</div>

<!-- RELEASE MODAL -->
<div id="release-modal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-sm sm:max-w-md w-full mx-4 shadow-xl animate-fadeIn">
        <h3 class="text-lg sm:text-xl font-semibold text-red-600 mb-2">⚠️ Release Booking</h3>
        <p class="text-sm text-gray-600 mb-3">
            This will cancel the booking permanently.
        </p>

        <textarea id="release-reason" rows="3"
                  class="w-full rounded-lg border border-gray-300 p-2 mb-4"
                  placeholder="Reason (optional)"></textarea>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
            <button onclick="closeReleaseModal()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border hover:bg-gray-100">
                Keep Booking
            </button>
            <button onclick="confirmRelease()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center justify-center gap-2">
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
const POS_BASE_PATH = @json($posBasePath ?? '/vendor/pos');
window.POS_BASE_PATH = POS_BASE_PATH;
const API_URL = `${POS_BASE_PATH}/api`;
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
        const invoiceNumberEl = document.getElementById('ui-invoice');
        invoiceNumberEl.textContent = b.invoice_number || '—';
        const invoiceLink = document.getElementById('ui-invoice-link');
        if (b.invoice_url) {
            invoiceNumberEl.href = b.invoice_url;
            invoiceNumberEl.classList.remove('pointer-events-none');
            invoiceNumberEl.classList.add('text-blue-600', 'hover:underline');
            invoiceLink.href = b.invoice_url;
            invoiceLink.classList.remove('hidden');
        } else {
            invoiceNumberEl.href = '#';
            invoiceNumberEl.classList.add('pointer-events-none');
            invoiceNumberEl.classList.remove('text-blue-600', 'hover:underline');
            invoiceLink.href = '#';
            invoiceLink.classList.add('hidden');
        }
        document.getElementById('ui-total').textContent =
            '₹' + parseFloat(b.total_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });

        // If payment_status is unpaid, show 'Hold' as booking status
        let bookingStatusText = b.status;
        let bookingStatusColor = getStatusColor(b.status);
        if (b.payment_status === 'pending_payment') {
            bookingStatusText = 'Hold';
            bookingStatusColor = 'bg-yellow-500 text-white';
        }
        document.getElementById('ui-booking-status').textContent = bookingStatusText;
        document.getElementById('ui-booking-status').className =
            'inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold ' + bookingStatusColor;

        document.getElementById('ui-payment-status').textContent = b.payment_status;
        document.getElementById('ui-payment-status').className =
            'inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold ' + getPaymentStatusColor(b.payment_status);

        /* ---- REST OF YOUR EXISTING HTML BUILD LOGIC ---- */
            let hoardingsTableRows = '';
            let totalBase = 0, totalDiscount = 0, totalTax = 0, totalFinal = 0;
            if (Array.isArray(b.hoardings) && b.hoardings.length > 0) {
                hoardingsTableRows = b.hoardings.map((h, idx) => {
                    const base = parseFloat(h.hoarding_price || 0);
                    const discount = parseFloat(h.hoarding_discount || 0);
                    const tax = ((base - discount) * 0.18);
                    const final = base - discount + tax;
                    totalBase += base;
                    totalDiscount += discount;
                    totalTax += tax;
                    totalFinal += final;
                    return `
                        <tr>
                            <td class="px-2 py-1 sm:px-3 sm:py-2 text-center">${idx + 1}</td>
                            <td class="px-2 py-1 sm:px-3 sm:py-2">
                                <div class="flex items-center gap-2">
                                    <img src="${h.image_url}" alt="Hoarding" class="w-10 h-10 sm:w-12 sm:h-12 rounded object-cover border my-1" />
                                    <div>
                                        <div class="font-semibold ">
                                            <a href="${h.url || '#'}" target="_blank">${h.title}</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-1 sm:px-3 sm:py-2 text-center">₹${base.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                            <td class="px-2 py-1 sm:px-3 sm:py-2 text-center">${h.campaign_start_date || '-'} - ${h.campaign_end_date || '-'}<br><span class="text-xs">${h.campaign_duration_days ? h.campaign_duration_days + ' days' : '-'}</span></td>
                            <td class="px-2 py-1 sm:px-3 sm:py-2 text-center">₹${final.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                        </tr>
                    `;
                }).join('');
            }


                let priceSummaryHtml = '';
                if (hoardingsTableRows) {
                    priceSummaryHtml = `
                        <div class="rounded-xl border bg-white p-4 mb-4">
                            <h3 class="text-base sm:text-lg font-bold mb-2">Price Summary</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div><strong>Base Amount:</strong> ₹${totalBase.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                                <div><strong>Total Discount:</strong> ₹${totalDiscount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                                <div><strong>GST (18%):</strong> ₹${totalTax.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                                <div><strong>Total Payable:</strong> ₹${totalFinal.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                            </div>
                        </div>
                    `;
                }

                let hoardingsTableHtml = '';
                if (hoardingsTableRows) {
                    hoardingsTableHtml = `
                        <div class="rounded-xl border bg-white p-4 mb-4">
                            <h3 class="text-base sm:text-lg font-bold mb-2">Booked Hoardings</h3>
                            <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 border">Sn.</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 border">Hoardings</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 border">Rental</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 border">Duration</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 border">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${hoardingsTableRows}
                                </tbody>
                            </table>
                            </div>
                        </div>
                    `;
                }

            // Customer details and actions inside invoice box
            let customerDetailsHtml = `
                <div class="rounded-xl border bg-white p-4 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
                        <div><strong>Customer:</strong> ${b.customer_name}</div>
                        <div><strong>Phone:</strong> ${b.customer_phone || '-'} </div>
                        <div><strong>Booking Date:</strong> ${new Date(b.created_at).toLocaleString()} </div>
                        <div><strong>Email:</strong> ${b.notes || '-'} </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 pt-2 border-t">
                        ${renderActionButtons(b)}
                    </div>
                </div>
            `;

                container.innerHTML = `
                    ${customerDetailsHtml}
                    ${priceSummaryHtml}
                    ${hoardingsTableHtml}
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
                class="w-full sm:w-auto px-4 py-2 rounded-lg btn-color text-sm font-medium text-center">
                💰 Mark as Paid
            </button>`;
    } else if (booking.payment_status === 'paid') {
        html += `
            <button disabled 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Payment already received">
                ✓ Already Paid
            </button>`;
    } else if (booking.status === 'cancelled') {
        html += `
            <button disabled 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Cannot mark paid - booking cancelled">
                ✗ Booking Cancelled
            </button>`;
    }

    // Release button
    // BACKEND RULE: payment_status = unpaid AND status in [draft, confirmed]
    if (booking.payment_status === 'unpaid' && ['draft', 'confirmed'].includes(booking.status)) {
        html += `
            <button onclick="openReleaseModal()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium text-center">
                🗑️ Release Booking
            </button>`;
    } else if (booking.status === 'active') {
        html += `
            <button disabled 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Cannot release - booking already started">
                🚫 Cannot Release (Active)
            </button>`;
    } else if (booking.status === 'completed') {
        html += `
            <button disabled 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Booking completed">
                ✓ Completed
            </button>`;
    } else if (booking.status === 'cancelled') {
        html += `
            <button disabled 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Booking cancelled">
                ✗ Cancelled
            </button>`;
    } else if (booking.payment_status !== 'unpaid') {
        html += `
            <button disabled 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Can only release hoarding if payment is unpaid">
                🚫 Cannot Release
            </button>`;
    }

    // Send Reminder button
    // BACKEND RULE: reminder_count < 10
    // if (booking.reminder_count !== undefined && booking.reminder_count < 10) {
    //     html += `
    //         <button onclick="sendReminder()"
    //             class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium">
    //             📧 Send Reminder
    //         </button>`;
    // } else if (booking.reminder_count === 10) {
    //     html += `
    //         <button disabled 
    //             class="px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed"
    //             title="Maximum 10 reminders sent">
    //             📧 Max Reminders Sent
    //         </button>`;
    // }
    // Send Reminder button
// RULE: Only if reminder_count < 10 AND payment not paid
if (booking.payment_status !== 'paid' && booking.reminder_count !== undefined && booking.reminder_count < 10) {
    html += `
        <button onclick="sendReminder()"
            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium text-center">
            📧 Send Reminder
        </button>`;
} 
else if (booking.payment_status === 'paid') {
    html += `
        <button disabled 
            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
            title="Payment already completed">
            ✓ Payment Completed
        </button>`;
} 
else if (booking.reminder_count === 10) {
    html += `
        <button disabled 
            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
            title="Maximum 10 reminders sent">
            📧 Max Reminders Sent
        </button>`;
}

    // Back button
    html += `<a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.dashboard') }}"
        class="w-full sm:w-auto px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm text-center">
        ← Back
    </a>`;

    return html;
}

// Modal functions
function openMarkPaidModal() {
    const totalAmount = parseFloat(currentBooking?.total_amount || 0);
    const paidAmount = parseFloat(currentBooking?.paid_amount || 0);
    const payableAmount = Math.max(0, totalAmount - paidAmount);
    const amountInput = document.getElementById('payment-amount');
    const helpText = document.getElementById('payment-amount-help');

    document.getElementById('mark-paid-modal').classList.remove('hidden');
    amountInput.value = payableAmount.toFixed(2);
    amountInput.max = payableAmount.toFixed(2);
    helpText.textContent = `Maximum payable amount: ₹${payableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
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
    const amountInput = document.getElementById('payment-amount');
    const amount = parseFloat(amountInput.value);
    const totalAmount = parseFloat(currentBooking?.total_amount || 0);
    const paidAmount = parseFloat(currentBooking?.paid_amount || 0);
    const payableAmount = Math.max(0, totalAmount - paidAmount);
    const reference = document.getElementById('payment-reference').value;

    if (!amount || amount <= 0) {
        showActionMessage('Please enter a valid amount', 'error');
        return;
    }

    if (amount > payableAmount) {
        showActionMessage(`Amount cannot be greater than payable amount (₹${payableAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })})`, 'error');
        amountInput.focus();
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
            const result = await response.json();
            showActionMessage('✅ Payment marked as received successfully!', 'success');
            if (['partial', 'paid'].includes(result?.data?.payment_status)) {
                if (typeof window.removePosTimerBooking === 'function') {
                    window.removePosTimerBooking(bookingId);
                } else if (typeof window.checkAndShowPosTimerNotification === 'function') {
                    window.checkAndShowPosTimerNotification();
                }
            }

            closeMarkPaidModal();
            setTimeout(() => loadBookingDetails(), 1500);
        } else if (response.status === 400 || response.status === 422) {
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