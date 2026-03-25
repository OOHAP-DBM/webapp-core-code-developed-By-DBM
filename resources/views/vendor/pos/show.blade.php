@include('vendor.pos.components.pos-timer-notification')
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
            class="hidden lg:inline-block text-sm bg-white/20 hover:bg-white/30 px-3 py-2 rounded-lg">
                ← Back
            </a>

        </div>

        {{-- Body --}}
        <div class="py-2 md:py-4 px-4 sm:p-6 space-y-6">

            <!-- Booking Summary -->
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Invoice</p>
                    <h2 class="text-lg font-semibold"><a id="ui-invoice" href="#" target="_blank" class="pointer-events-none text-inherit">—</a></h2>
                    @if(isset($invoice) && $invoice)
                        <a id="ui-invoice-link" href="{{ route('invoice.download', ['invoice' => $invoice->id]) }}" target="_blank" class="text-xs text-blue-600 hover:underline">Download Invoice PDF</a>
                    @else
                        <a id="ui-invoice-link" href="#" onclick="downloadInvoice(event)" class="hidden text-xs text-blue-600 hover:underline">Download Invoice PDF</a>
                    @endif
                <script>
                // If invoice_url is set dynamically, enable download
                function downloadInvoice(event) {
                    event.preventDefault();
                    const btn = event.currentTarget;
                    const url = btn.getAttribute('data-invoice-url') || btn.href;
                    if (!url || url === '#') return;
                    const a = document.createElement('a');
                    a.href = url;
                    a.setAttribute('download', 'Invoice.pdf');
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
                </script>
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
                    <div class="mt-1">
                        <span id="ui-payment-status"
                              class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-300">
                            —
                        </span>
                    </div>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Milestone Status</p>
                    <div id="ui-milestone-wrap">
                        <div id="ui-milestone-timeline" class="space-y-0"></div>
                    </div>
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
        <div id="mark-paid-mobile-handle" class="hidden w-16 h-1.5 rounded-full bg-gray-200 mx-auto mb-4 sm:hidden"></div>
        <h3 class="text-lg sm:text-xl font-semibold mb-2">Mark Payment as Received</h3>
        <p class="text-gray-600 text-sm mb-4">
            Confirm payment details before marking as paid.
        </p>

        <div id="milestone-payment-summary" class="hidden mb-4 space-y-1 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-gray-700">Total Amount:</span>
                <span id="milestone-total-amount" class="font-semibold text-blue-600">₹0.00</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-700">Received Amount:</span>
                <span id="milestone-received-amount" class="font-semibold text-green-600">₹0.00</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-700">Balance Amount:</span>
                <span id="milestone-balance-amount" class="font-semibold text-orange-500">₹0.00</span>
            </div>
        </div>

        <div id="milestone-details-section" class="hidden border-t border-b border-dashed border-gray-300 py-4 mb-4">
            <p class="text-2xl font-semibold text-gray-800 mb-3">Milestones Details</p>
            <div id="milestone-checkbox-list" class="space-y-2"></div>
            <p id="milestone-selection-help" class="text-xs text-gray-500 mt-2"></p>
        </div>

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

        <div id="mark-paid-actions"
             class="flex flex-col gap-2">
            <button id="mark-paid-confirm-btn" onclick="confirmMarkPaid()"
                    class="w-full px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center justify-center gap-2 font-semibold">
                <span id="confirm-btn-text">Mark as Paid</span>
                <span id="confirm-spinner" class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
            <button id="mark-paid-cancel-btn" onclick="closeMarkPaidModal()"
                    class="w-full px-4 py-2 rounded-lg border hover:bg-gray-100">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- RELEASE MODAL -->
<div id="release-modal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-sm sm:max-w-md w-full mx-4 shadow-xl animate-fadeIn">
        <h3 class="text-lg sm:text-xl font-semibold text-red-600 mb-2">⚠️ Cancel Booking</h3>
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
                <span id="release-btn-text">Cancel Booking</span>
                <span id="release-spinner"
                      class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
        </div>
    </div>
</div>

<!-- SEPARATE CANCEL BOOKING MODAL -->
<div id="cancel-booking-modal"
     class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-sm sm:max-w-md w-full mx-4 shadow-xl animate-fadeIn">
        <h3 class="text-lg sm:text-xl font-semibold text-red-600 mb-2">⚠️ Cancel Booking</h3>
        <p class="text-sm text-gray-600 mb-3">
            This will cancel the booking permanently.
        </p>

        <textarea id="cancel-booking-reason" rows="3"
                  class="w-full rounded-lg border border-gray-300 p-2 mb-4"
                  placeholder="Reason (optional)"></textarea>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
            <button onclick="closeCancelBookingModal()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg border hover:bg-gray-100">
                Keep Booking
            </button>
            <button id="cancel-booking-confirm-btn" onclick="confirmCancelBooking()"
                    class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 flex items-center justify-center gap-2">
                <span id="cancel-booking-btn-text">Cancel Booking</span>
                <span id="cancel-booking-spinner"
                      class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
        </div>
    </div>
</div>

<!-- SEND REMINDER MODAL -->
<div id="send-reminder-modal"
     class="hidden fixed inset-0 bg-black/60 flex items-end sm:items-center justify-center z-50">
    <div class="bg-white rounded-t-3xl sm:rounded-3xl w-full max-w-md sm:mx-4 shadow-xl animate-fadeIn overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
            <div class="w-9 h-9" aria-hidden="true"></div>
            <h3 class="text-lg font-semibold text-gray-900">Send Reminder</h3>
            <button type="button" onclick="dismissReminderModal()"
                    class="w-9 h-9 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50"
                    aria-label="Close reminder modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="px-5 py-5 space-y-5 max-h-[72vh] overflow-y-auto">
            <div>
                <h4 class="text-xl font-semibold text-gray-900">Schedule Reminders</h4>
                <p class="text-sm text-gray-500 mt-1">Select multiple dates and times to automate your customer follow-ups.</p>
            </div>

            <div id="reminder-composer-panel" class="space-y-4 transition-opacity duration-150">
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">When?</p>
                    <div class="flex flex-wrap gap-2">
                        <button id="day-today" onclick="selectReminderDay('today')"
                                class="day-btn px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium cursor-pointer">
                            Today
                        </button>
                        <button id="day-tomorrow" onclick="selectReminderDay('tomorrow')"
                                class="day-btn px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium cursor-pointer">
                            Tomorrow
                        </button>
                        <button id="day-custom" onclick="selectReminderDay('custom')"
                                class="day-btn px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium flex items-center gap-2 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                            </svg>
                            Custom Date
                        </button>
                    </div>
                    <div id="custom-date-wrapper" class="hidden mt-3">
                        <input type="date" id="reminder-custom-date"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 cursor-pointer"
                               onchange="handleCustomReminderDateChange(this.value)">
                    </div>
                </div>

                <div id="reminder-time-section" class="hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">At What Time?</p>
                <div id="time-btn-group-wrapper" class="flex flex-wrap items-center gap-2">
                    <div id="time-btn-group" class="flex flex-wrap gap-2"></div>
                    <button id="custom-time-toggle-btn" onclick="toggleCustomTimeInput()"
                            class="shrink-0 px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium cursor-pointer">
                        Custom Time
                    </button>
                </div>
                    <div id="custom-time-wrapper" class="hidden mt-3">
                        <input type="time" id="reminder-custom-time"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 cursor-pointer"
                               onchange="applyCustomTime(this.value)">
                    </div>
                    <p id="selected-time-display" class="hidden mt-2 text-sm font-semibold text-orange-500"></p>
                </div>
            </div>

                <button id="save-reminder-draft-btn" onclick="saveReminderDraft()"
                    class="hidden w-full py-3 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 disabled:bg-blue-300 disabled:cursor-not-allowed disabled:opacity-100">
                <span id="save-reminder-draft-text">Save Reminder</span>
            </button>

            <div id="reminder-inline-message"
                 class="hidden rounded-lg border p-3 text-sm"
                 aria-live="polite"></div>

            <div id="reminder-list-section" class="hidden border-t border-gray-200 pt-4 space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-base font-semibold text-gray-900">Scheduled Reminder</p>
                    <button id="reminder-add-more-btn" onclick="startNewReminderDraft()"
                            class="text-sm font-medium text-green-500 hover:text-green-600">
                        {{-- Add more --}}
                    </button>
                </div>
                <div id="reminder-list" class="space-y-3"></div>
                <button id="reminder-view-more-sent-btn" type="button" onclick="showAllSentReminders()"
                        class="hidden text-sm font-medium text-blue-600 hover:text-blue-700">
                    View More Sent
                </button>
            </div>

            <p class="text-xs text-gray-500">Note: Reminder will automatically send to the customer as scheduled.</p>

            <button id="send-reminder-btn" onclick="confirmSendReminder()"
                    class="w-full py-3 rounded-xl bg-green-500 text-white font-semibold text-sm flex items-center justify-center gap-2 hover:bg-green-600 disabled:bg-green-300 disabled:cursor-not-allowed cursor-pointer"
                    disabled>
                <span id="reminder-btn-text">Schedule Reminder</span>
                <span id="reminder-spinner"
                      class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            </button>
        </div>
    </div>
</div>

@include('vendor.pos.components.cancel-debit-note-modal')
<div id="reminder-success-modal"
     class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[60] px-4">
    <div class="bg-white rounded-2xl max-w-xs w-full p-5 shadow-xl animate-fadeIn text-center">
        <button onclick="closeReminderSuccessModal()"
                class="ml-auto flex text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <div class="mx-auto -mt-2 mb-4 w-16 h-16 rounded-2xl bg-green-50 flex items-center justify-center text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4" />
            </svg>
        </div>
        <h4 class="text-lg font-semibold text-gray-900">Reminder scheduled successfully</h4>
        <p class="text-sm text-gray-500 mt-2">It will automatically send to the customer as scheduled.</p>
        <button id="success-add-more-btn" onclick="openReminderModalFromSuccess()"
                class="w-full mt-5 py-3 rounded-xl bg-green-500 text-white font-semibold text-sm hover:bg-green-600">
            Add more reminder
        </button>
    </div>
</div>

<div id="reminder-close-confirm-modal"
     class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[70] px-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-xl animate-fadeIn">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86l-7.5 13A1 1 0 003.66 18h16.68a1 1 0 00.87-1.5l-7.5-13a1 1 0 00-1.74 0z" />
                </svg>
            </div>
            <div>
                <h4 class="text-lg font-semibold text-gray-900">Close reminder popup?</h4>
                <p class="text-sm text-gray-500 mt-1">Are you sure you want to close this popup? Reminder data saved here but not scheduled yet will be discarded.</p>
            </div>
        </div>
        <div class="mt-5 flex items-center justify-end gap-3">
            <button type="button" onclick="closeReminderDismissConfirmModal()"
                    class="px-4 py-2 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" onclick="confirmDismissReminderModal()"
                    class="px-4 py-2 rounded-xl bg-red-500 text-sm font-semibold text-white hover:bg-red-600">
                Yes, Close
            </button>
        </div>
    </div>
</div>
<!-- DELETE REMINDER CONFIRMATION MODAL -->
<div id="delete-reminder-confirm-modal"
     class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-[100] px-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-5 shadow-xl animate-fadeIn">
        <div class="flex items-start gap-3 mb-5">
            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                </svg>
            </div>
            <div>
                <h4 class="text-base font-semibold text-gray-900">Delete reminder?</h4>
                <p id="delete-reminder-confirm-label" class="text-sm text-gray-500 mt-1"></p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2">
            <button type="button" onclick="closeDeleteReminderConfirmModal()"
                    class="px-4 py-2 rounded-xl border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Keep
            </button>
            <button type="button" id="delete-reminder-confirm-btn" onclick="executeDeleteReminderDraft()"
                    class="px-4 py-2 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm font-semibold hover:bg-red-100">
                Delete
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

@include('vendor.pos.components.status-formatters')

<script>


const bookingId = @json($bookingId);
const POS_BASE_PATH = @json($posBasePath ?? '/vendor/pos');
const HOARDING_SHOW_URL = "{{ url('hoardings') }}/__SLUG__";
window.POS_BASE_PATH = POS_BASE_PATH;
const API_URL = `${POS_BASE_PATH}/api`;
const New_API_URL = '/api/v1';
let currentBooking = null;

document.addEventListener('DOMContentLoaded', () => {
    loadBookingDetails();
    setInterval(loadBookingDetails, 60000);
    const deleteReminderConfirmModal = document.getElementById('delete-reminder-confirm-modal');
if (event.target === deleteReminderConfirmModal) {
    closeDeleteReminderConfirmModal();
}
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

        document.getElementById('ui-booking-status').textContent = getPosBookingStatusLabel(b.status);
        const bookingStatusColor = b.status === 'cancelled'
            ? 'bg-slate-600 text-white'
            : getPosBookingStatusColor(b.status);
        document.getElementById('ui-booking-status').className =
            'inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold ' + bookingStatusColor;

        document.getElementById('ui-payment-status').textContent = getPosPaymentStatusLabel(b.payment_status);
        document.getElementById('ui-payment-status').className =
            'inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold ' + getPosPaymentStatusColor(b.payment_status);

        const milestoneWrap = document.getElementById('ui-milestone-wrap');
        const milestoneTimelineEl = document.getElementById('ui-milestone-timeline');
        const isMilestoneBooking = Number(b.is_milestone || 0) === 1;
        const milestoneTotal = parseInt(b.milestone_total || 0, 10) || 0;
        const milestones = Array.isArray(b.milestones) ? b.milestones : [];

        const formatMilestoneDate = (value) => {
            if (!value) return '-';
            const d = new Date(value);
            if (Number.isNaN(d.getTime())) return '-';
            const day = String(d.getDate()).padStart(2, '0');
            const mon = d.toLocaleString('en-US', { month: 'short' });
            const yr = String(d.getFullYear()).slice(-2);
            return `${day} ${mon}, ${yr}`;
        };

        if (isMilestoneBooking && milestoneTotal > 0) {
            milestoneTimelineEl.innerHTML = milestones.length
                ? milestones.map((ms, idx) => {
                    const title = ms.title || `Milestone ${ms.sequence_no || (idx + 1)}`;
                    const amount = parseFloat(ms.calculated_amount ?? ms.amount ?? 0) || 0;
                    const dueDate = formatMilestoneDate(ms.due_date);
                    const status = (ms.status || 'pending').toString();
                    const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
                    return `
                        <div class="text-xs py-1.5 border-b border-gray-200 last:border-0">
                            <div class="font-semibold text-gray-700">${title}</div>
                            <div class="text-gray-500">
                                ₹${amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                &nbsp;<span class="text-red-500">Due ${dueDate}</span>
                                &nbsp;<span class="text-gray-400">| ${statusLabel}</span>
                            </div>
                        </div>
                    `;
                }).join('')
                : '<p class="text-xs text-gray-400 py-1">No milestones added yet.</p>';
            milestoneWrap.classList.remove('hidden');
        } else {
            milestoneTimelineEl.innerHTML = '';
            milestoneWrap.classList.add('hidden');
        }

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
                                            <a href="${(h.slug || h.id) ? HOARDING_SHOW_URL.replace('__SLUG__', h.slug || h.id) : '#'}"
                                                    target="_blank"
                                                    class="hover:underline">
                                                        ${h.title}
                                            </a>
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
                        <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
                            <h3 class="text-base sm:text-lg font-bold mb-2">Price Summary</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div><strong>Base Amount:</strong> ₹${totalBase.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                                <div><strong>Total Discount:</strong> ₹${totalDiscount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                                <div><strong>Taxes (18%):</strong> ₹${totalTax.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                                <div><strong>Total Payable:</strong> ₹${totalFinal.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</div>
                            </div>
                        </div>
                    `;
                }

                let hoardingsTableHtml = '';
                if (hoardingsTableRows) {
                    hoardingsTableHtml = `
                        <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
                            <h3 class="text-base sm:text-lg font-bold mb-2">Hoardings</h3>
                            <div class="overflow-x-auto">
                            <table class="min-w-full text-sm shadow-sm overflow-hidden">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 ">Sn.</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 ">Hoardings</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 ">Rental</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 ">Duration</th>
                                        <th class="px-2 py-1 sm:px-3 sm:py-2 ">Total Price</th>
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
                <div class="rounded-xl border border-gray-200 bg-white p-4 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
                        <div><strong>Customer:</strong> ${b.customer_name}</div>
                        <div><strong>Phone:</strong> ${b.customer_phone || '-'} </div>
                        <div><strong>Booking Date:</strong> ${new Date(b.created_at).toLocaleString()} </div>
                        <div><strong>Email:</strong> ${b.customer_email || '-'} </div>
                    </div>
                    <div id="booking-action-buttons" class="flex flex-col sm:flex-row sm:flex-wrap gap-2 pt-2 border-t border-gray-200">
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
 * - Cancel booking: Vendor can cancel at any time
 * - Send reminder: Only if pending reminders < 3
 */
function renderActionButtons(booking) {
    let html = '';
    const isActiveCreditNoteBooking = booking.payment_mode === 'credit_note'
        && booking.payment_status === 'credit'
        && booking.credit_note_status !== 'cancelled';

    // Mark as Paid button
    // BACKEND RULE: payment_status in [unpaid, partial] AND status != cancelled
    if (['unpaid', 'partial'].includes(booking.payment_status) && booking.status !== 'cancelled') {
        html += `
            <button onclick="openMarkPaidModal()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg btn-color text-sm font-medium text-center cursor-pointer">
                Mark as Paid
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
    


    // Cancel booking button: show only if booking is not cancelled.
    if (booking.status !== 'cancelled') {
        html += `
            <button onclick="openCancelBookingModal()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium text-center cursor-pointer">
                Cancel Booking
            </button>`;
    }

    // Cancel Debit/Credit Note button
    // RULE: Show only when payment mode is credit_note, booking is on credit, and booking is not cancelled
    if (booking.status !== 'cancelled' && isActiveCreditNoteBooking) {
        html += `
            <button onclick="cancelDebitNote()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm font-medium text-center cursor-pointer">
                Cancel Debit Note
            </button>`;
    }

    // Send Reminder button
    // RULE: Only if pending reminders < 3 and booking satisfies backend reminder rules
    const pendingReminderCount = Array.isArray(booking.scheduled_reminders)
        ? booking.scheduled_reminders.filter(reminder => String(reminder?.status || 'pending').toLowerCase() === 'pending').length
        : 0;
    const canSendReminder = booking.status !== 'cancelled' && pendingReminderCount < 3 && (
        ['unpaid', 'partial'].includes(String(booking.payment_status || '').toLowerCase())
        || isActiveCreditNoteBooking
    );

    if (canSendReminder) {
        html += `
            <button onclick="sendReminder()"
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium text-center cursor-pointer">
                 Send Reminder
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
    else if (pendingReminderCount >= 3) {
        html += `
            <button disabled 
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Maximum 3 pending reminders allowed">
                Send Reminder
            </button>`;
    }
    else if (String(booking.payment_mode || '').toLowerCase() === 'credit_note' && String(booking.credit_note_status || '').toLowerCase() === 'cancelled') {
        html += `
            <button disabled
                class="w-full sm:w-auto px-4 py-2 rounded-lg bg-gray-300 text-gray-500 text-sm font-medium cursor-not-allowed text-center"
                title="Cannot send reminder after debit note is cancelled">
                Send Reminder
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
let _markPaidMilestoneMode = false;
let _markPaidPayableAmount = 0;

function formatCurrencyINR(value) {
    return '₹' + Number(value || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function getMilestonesForMarkPaid() {
    const milestones = Array.isArray(currentBooking?.milestones) ? currentBooking.milestones : [];

    return milestones
        .filter(ms => {
            const status = String(ms?.status || '').toLowerCase();
            return status !== 'cancelled';
        })
        .map(ms => ({
            id: Number(ms?.id || 0),
            title: ms?.title || `Milestone ${ms?.sequence_no || ''}`.trim(),
            status: String(ms?.status || 'pending').toLowerCase(),
            amount: Number(ms?.calculated_amount ?? ms?.amount ?? 0),
            sequenceNo: Number(ms?.sequence_no || 0),
            isPaid: String(ms?.status || '').toLowerCase() === 'paid',
            isSelectable: ['due', 'overdue'].includes(String(ms?.status || '').toLowerCase()),
        }))
        .sort((a, b) => a.sequenceNo - b.sequenceNo);
}

function setMarkPaidActionLayout(isMilestoneMode) {
    const actionsWrap = document.getElementById('mark-paid-actions');
    const cancelBtn = document.getElementById('mark-paid-cancel-btn');
    const confirmBtn = document.getElementById('mark-paid-confirm-btn');
    const mobileHandle = document.getElementById('mark-paid-mobile-handle');
    const modalTitle = document.querySelector('#mark-paid-modal h3');
    const amountInput = document.getElementById('payment-amount');
    const referenceInput = document.getElementById('payment-reference');

    if (!actionsWrap || !cancelBtn || !confirmBtn) {
        return;
    }

    if (isMilestoneMode) {
        actionsWrap.className = 'flex flex-col gap-3';

        confirmBtn.className = 'w-full px-4 py-2.5 rounded-lg bg-green-500 text-white hover:bg-green-600 flex items-center justify-center gap-2 font-semibold text-lg';
        cancelBtn.className = 'w-full px-4 py-1.5 text-red-500 hover:text-red-600 text-base bg-transparent border-0';

        if (mobileHandle) {
            mobileHandle.classList.remove('hidden');
        }
        if (modalTitle) {
            modalTitle.className = 'text-2xl font-semibold mb-2';
        }
        if (amountInput) {
            amountInput.className = 'w-full rounded-lg border border-gray-300 p-3 text-lg';
        }
        if (referenceInput) {
            referenceInput.className = 'w-full rounded-lg border border-gray-300 p-3 text-lg';
        }
    } else {
        actionsWrap.className = 'flex flex-col gap-2';

        confirmBtn.className = 'w-full px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 flex items-center justify-center gap-2 font-semibold';
        cancelBtn.className = 'w-full px-4 py-2 rounded-lg border hover:bg-gray-100';

        if (mobileHandle) {
            mobileHandle.classList.add('hidden');
        }
        if (modalTitle) {
            modalTitle.className = 'text-lg sm:text-xl font-semibold mb-2';
        }
        if (amountInput) {
            amountInput.className = 'w-full rounded-lg border border-gray-300 p-2';
        }
        if (referenceInput) {
            referenceInput.className = 'w-full rounded-lg border border-gray-300 p-2';
        }
    }
}

function updateMilestoneSelectionForMarkPaid() {
    const amountInput = document.getElementById('payment-amount');
    const helpText = document.getElementById('payment-amount-help');
    const selectionHelp = document.getElementById('milestone-selection-help');
    const confirmBtn = document.getElementById('mark-paid-confirm-btn');
    const checkboxes = Array.from(document.querySelectorAll('.mark-paid-milestone-checkbox:not(:disabled)'));

    const selected = checkboxes.filter(cb => cb.checked);
    const selectedAmount = selected.reduce((sum, cb) => sum + Number(cb.dataset.amount || 0), 0);
    const selectedCount = selected.length;
    const finalAmount = selectedAmount;

    if (selectedCount > 0) {
        amountInput.value = finalAmount.toFixed(2);
    }

    if (_markPaidMilestoneMode && confirmBtn) {
        confirmBtn.disabled = selectedCount === 0;
    }

    if (selectionHelp) {
        if (selectedCount === 0) {
            selectionHelp.textContent = 'Only due milestone can be paid. Select the due milestone to continue.';
        } else {
            selectionHelp.textContent = `Selected ${selectedCount} milestone(s): ${formatCurrencyINR(selectedAmount)}` +
                '. Payment amount must match selected due milestone amount.';
        }
    }

    const currentAmount = Number(amountInput.value || 0);
    helpText.textContent = `Maximum payable amount: ${formatCurrencyINR(_markPaidPayableAmount)}` +
        (currentAmount > _markPaidPayableAmount ? ' (amount will be validated before submit)' : '');
}

function renderMilestoneMarkPaidSection(payableAmount) {
    const milestones = getMilestonesForMarkPaid();
    const selectableMilestones = milestones.filter(ms => ms.isSelectable);
    const listWrap = document.getElementById('milestone-checkbox-list');
    const summaryWrap = document.getElementById('milestone-payment-summary');
    const detailsWrap = document.getElementById('milestone-details-section');

    _markPaidMilestoneMode = Number(currentBooking?.is_milestone || 0) === 1 && milestones.length > 0;

    summaryWrap.classList.toggle('hidden', !_markPaidMilestoneMode);
    detailsWrap.classList.toggle('hidden', !_markPaidMilestoneMode);
    setMarkPaidActionLayout(_markPaidMilestoneMode);

    if (!_markPaidMilestoneMode) {
        listWrap.innerHTML = '';
        document.getElementById('milestone-selection-help').textContent = '';
        return;
    }

    const totalAmount = Number(currentBooking?.total_amount || 0);
    const paidAmount = Number(currentBooking?.paid_amount || 0);

    document.getElementById('milestone-total-amount').textContent = formatCurrencyINR(totalAmount);
    document.getElementById('milestone-received-amount').textContent = formatCurrencyINR(paidAmount);
    document.getElementById('milestone-balance-amount').textContent = formatCurrencyINR(payableAmount);

    const firstSelectableId = selectableMilestones.length ? selectableMilestones[0].id : null;

    listWrap.innerHTML = milestones.map((ms, idx) => {
        const isChecked = ms.id === firstSelectableId;
        const isDisabled = !ms.isSelectable;
        const label = ms.title || `Milestone ${idx + 1}`;
        const statusLabel = ms.status.charAt(0).toUpperCase() + ms.status.slice(1);
        const statusClass = ms.isSelectable
            ? 'text-orange-600'
            : (ms.isPaid ? 'text-green-600' : 'text-gray-400');

        return `
            <label class="flex items-center gap-3 text-sm ${isDisabled ? 'text-gray-400 cursor-not-allowed' : 'text-gray-700 cursor-pointer'}">
                <input type="checkbox"
                    class="mark-paid-milestone-checkbox h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
                    data-id="${ms.id}"
                    data-amount="${ms.amount}"
                    ${isChecked ? 'checked' : ''}
                    ${isDisabled ? 'disabled' : ''}>
                <span>
                    <span class="font-medium">${formatCurrencyINR(ms.amount)}</span>
                    <span class="${statusClass}">(${label} • ${statusLabel})</span>
                </span>
            </label>
        `;
    }).join('');

    if (selectableMilestones.length === 0) {
        document.getElementById('milestone-selection-help').textContent = 'No due milestone available right now.';
        document.getElementById('mark-paid-confirm-btn').disabled = true;
        return;
    }

    document.querySelectorAll('.mark-paid-milestone-checkbox').forEach(cb => {
        cb.addEventListener('change', updateMilestoneSelectionForMarkPaid);
    });

    updateMilestoneSelectionForMarkPaid();
}

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

    _markPaidPayableAmount = payableAmount;
    renderMilestoneMarkPaidSection(payableAmount);
}

function closeMarkPaidModal() {
    document.getElementById('mark-paid-modal').classList.add('hidden');
    _markPaidMilestoneMode = false;
    setMarkPaidActionLayout(false);

    const detailsWrap = document.getElementById('milestone-details-section');
    const summaryWrap = document.getElementById('milestone-payment-summary');
    const listWrap = document.getElementById('milestone-checkbox-list');
    const selectionHelp = document.getElementById('milestone-selection-help');

    if (detailsWrap) detailsWrap.classList.add('hidden');
    if (summaryWrap) summaryWrap.classList.add('hidden');
    if (listWrap) listWrap.innerHTML = '';
    if (selectionHelp) selectionHelp.textContent = '';
}

function openReleaseModal() {
    document.getElementById('release-modal').classList.remove('hidden');
    document.getElementById('release-reason').value = '';
}

function closeReleaseModal() {
    document.getElementById('release-modal').classList.add('hidden');
}

function openCancelBookingModal() {
    const modal = document.getElementById('cancel-booking-modal');
    const reasonInput = document.getElementById('cancel-booking-reason');
    if (!modal || !reasonInput) return;

    modal.classList.remove('hidden');
    reasonInput.value = '';
}

function closeCancelBookingModal() {
    const modal = document.getElementById('cancel-booking-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function wireSeparateCancelBookingButton() {
    const actionWrap = document.getElementById('booking-action-buttons');
    if (!actionWrap) return;

    const buttons = Array.from(actionWrap.querySelectorAll('button'));
    const enabledCancelBtn = buttons.find(btn => {
        const label = (btn.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
        return label === 'cancel booking' && !btn.disabled;
    });

    if (enabledCancelBtn) {
        enabledCancelBtn.onclick = openCancelBookingModal;
        enabledCancelBtn.removeAttribute('onclick');
        return;
    }

    if (document.getElementById('separate-cancel-booking-btn')) {
        return;
    }

    const cancelBtn = document.createElement('button');
    cancelBtn.id = 'separate-cancel-booking-btn';
    cancelBtn.type = 'button';
    cancelBtn.className = 'w-full sm:w-auto px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium text-center';
    cancelBtn.textContent = 'Cancel Booking';
    cancelBtn.onclick = openCancelBookingModal;

    const backLink = actionWrap.querySelector('a');
    if (backLink) {
        actionWrap.insertBefore(cancelBtn, backLink);
    } else {
        actionWrap.appendChild(cancelBtn);
    }
}

// async function confirmCancelBooking() {
//     const reasonInput = document.getElementById('cancel-booking-reason');
//     const reason = (reasonInput?.value || '').trim() || 'Cancelled by vendor';
//     const confirmBtn = document.getElementById('cancel-booking-confirm-btn');

//     document.getElementById('cancel-booking-btn-text').classList.add('hidden');
//     document.getElementById('cancel-booking-spinner').classList.remove('hidden');
//     if (confirmBtn) confirmBtn.disabled = true;

//     try {
//         const response = await fetch(`${API_URL}/bookings/${bookingId}/cancel`, {
//             method: 'POST',
//             headers: {
//                 'Accept': 'application/json',
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
//             },
//             credentials: 'same-origin',
//             body: JSON.stringify({ reason })
//         });

//         if (response.ok) {
//             showActionMessage('✅ Booking cancelled successfully!', 'success');
//             closeCancelBookingModal();
//             setTimeout(() => loadBookingDetails(), 1500);
//         } else if (response.status === 400 || response.status === 422) {
//             const error = await response.json();
//             showActionMessage(error.message || 'Cannot cancel booking', 'error');
//         } else if (response.status === 404) {
//             showActionMessage('Booking not found', 'error');
//         } else {
//             const error = await response.json();
//             showActionMessage(error.message || 'Error cancelling booking', 'error');
//         }
//     } catch (error) {
//         console.error('Error:', error);
//         showActionMessage('Network error. Please try again.', 'error');
//     } finally {
//         document.getElementById('cancel-booking-btn-text').classList.remove('hidden');
//         document.getElementById('cancel-booking-spinner').classList.add('hidden');
//         if (confirmBtn) confirmBtn.disabled = false;
//     }
// }


async function confirmCancelBooking() {
    const reason = document.getElementById('cancel-booking-reason').value;

    const btn = document.getElementById('cancel-booking-confirm-btn');
    const spinner = document.getElementById('cancel-booking-spinner');
    const text = document.getElementById('cancel-booking-btn-text');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    text.textContent = 'Cancelling...';

    try {
       const response = await fetch(`${API_URL}/bookings/${bookingId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason })
        });

        const data = await response.json();

        if (data.success) {

            // 🔥🔥 THIS IS THE MAIN FIX
            window.removePosTimerBooking(bookingId);

            closeCancelBookingModal();
            loadBookingDetails(); // refresh UI

        } else {
            alert(data.message || 'Cancel failed');
        }

    } catch (e) {
        alert('Something went wrong');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
        text.textContent = 'Cancel Booking';
    }
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
    const selectedMilestoneCheckboxes = Array.from(document.querySelectorAll('.mark-paid-milestone-checkbox:checked:not(:disabled)'));
    const selectedMilestoneIds = selectedMilestoneCheckboxes
        .map(cb => Number(cb.dataset.id || 0))
        .filter(id => id > 0);
    const selectedMilestoneAmount = selectedMilestoneCheckboxes
        .reduce((sum, cb) => sum + Number(cb.dataset.amount || 0), 0);

    if (_markPaidMilestoneMode && selectedMilestoneIds.length === 0) {
        showActionMessage('Please select at least one milestone to mark payment.', 'error');
        return;
    }

    if (_markPaidMilestoneMode && Math.abs(amount - selectedMilestoneAmount) > 0.01) {
        showActionMessage(`For milestone payment, amount must match selected due milestone amount (${formatCurrencyINR(selectedMilestoneAmount)}).`, 'error');
        amountInput.focus();
        return;
    }

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
    document.getElementById('mark-paid-confirm-btn').disabled = true;

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
                payment_reference: reference,
                notes: reference || null,
                milestone_ids: selectedMilestoneIds
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
        document.getElementById('mark-paid-confirm-btn').disabled = false;
    }
}

/**
 * Confirm and submit release booking
 */
async function confirmRelease() {
    const reason = (document.getElementById('release-reason').value || '').trim() || 'Cancelled by vendor';

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
            showActionMessage('✅ Booking cancelled successfully!', 'success');
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
 * Reminder scheduling state
 */
let _reminderDay = null;
let _reminderTime = null;
let _editingReminderKey = null;
let _reminderDrafts = [];
let _reminderBasePendingSignature = '';
let _reminderHasLocalDraftChanges = false;
let _reminderInlineMessageTimeout = null;
let _showAllSentReminders = false;
let _suppressReminderAutoSave = false;

function sendReminder() {
    openReminderModal();
}

function cancelDebitNote() {
    if (!currentBooking) {
        showActionMessage('Booking details are not loaded yet.', 'error');
        return;
    }
    openCancelDebitNoteModal();
}

function openCancelDebitNoteModal() {
    const modal = document.getElementById('cancel-debit-note-modal');
    const reasonInput = document.getElementById('cancel-debit-note-reason');
    if (!modal) return;

    if (reasonInput) reasonInput.value = '';
    modal.classList.remove('hidden');
    setTimeout(() => reasonInput?.focus(), 100);
}

async function confirmCancelDebitNote() {
    const reasonInput = document.getElementById('cancel-debit-note-reason');
    const confirmBtn = document.getElementById('cancel-debit-note-confirm-btn');
    const btnText = document.getElementById('cancel-debit-note-btn-text');
    const spinner = document.getElementById('cancel-debit-note-spinner');

    const trimmedReason = (reasonInput?.value || '').trim() || 'Cancelled by vendor';

    // Show loading
    if (btnText) btnText.classList.add('hidden');
    if (spinner) spinner.classList.remove('hidden');
    if (confirmBtn) confirmBtn.disabled = true;

    try {
        const response = await fetch(`${API_URL}/bookings/${bookingId}/cancel-credit-note`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify({ reason: trimmedReason })
        });

        if (response.ok) {
            closeCancelDebitNoteModal();
            showActionMessage('✅ Debit note cancelled successfully!', 'success');
            await loadBookingDetails();
            return;
        }

        const error = await response.json();
        showActionMessage(error.message || 'Failed to cancel debit note', 'error');
    } catch (error) {
        console.error('cancelDebitNote error:', error);
        showActionMessage('Network error. Please try again.', 'error');
    } finally {
        if (btnText) btnText.classList.remove('hidden');
        if (spinner) spinner.classList.add('hidden');
        if (confirmBtn) confirmBtn.disabled = false;
    }
}

function closeCancelDebitNoteModal() {
    const modal = document.getElementById('cancel-debit-note-modal');
    if (modal) modal.classList.add('hidden');
}



function openReminderModal() {
    hydrateReminderDraftsFromBooking();
    _showAllSentReminders = false;
    resetReminderComposer();
    clearReminderModalMessage();
    renderReminderDrafts();
    document.getElementById('send-reminder-modal').classList.remove('hidden');
}

function closeReminderModal(discardUnsavedDrafts = false) {
    clearReminderModalMessage();
    closeReminderDismissConfirmModal();

    if (discardUnsavedDrafts) {
        hydrateReminderDraftsFromBooking(true);
    }

    resetReminderComposer();
    document.getElementById('send-reminder-modal').classList.add('hidden');
}

function dismissReminderModal() {
    if (shouldConfirmReminderDismiss()) {
        openReminderDismissConfirmModal();
        return;
    }

    closeReminderModal(true);
}

function openReminderDismissConfirmModal() {
    document.getElementById('reminder-close-confirm-modal').classList.remove('hidden');
}

function closeReminderDismissConfirmModal() {
    document.getElementById('reminder-close-confirm-modal').classList.add('hidden');
}

function confirmDismissReminderModal() {
    closeReminderModal(true);
}

function hasReminderComposerState() {
    const customDateValue = document.getElementById('reminder-custom-date')?.value || '';
    const customTimeValue = document.getElementById('reminder-custom-time')?.value || '';

    return _editingReminderKey !== null
        || _reminderDay !== null
        || _reminderTime !== null
        || customDateValue !== ''
        || customTimeValue !== '';
}

function shouldConfirmReminderDismiss() {
    return _reminderHasLocalDraftChanges || hasReminderComposerState();
}

function closeReminderSuccessModal() {
    document.getElementById('reminder-success-modal').classList.add('hidden');
}

function openReminderModalFromSuccess() {
    closeReminderSuccessModal();
    openReminderModal();
}

function hydrateReminderDraftsFromBooking(forceRefresh = false) {
    if (_reminderHasLocalDraftChanges && !forceRefresh) {
        return;
    }

    const bookingReminders = Array.isArray(currentBooking?.scheduled_reminders) ? currentBooking.scheduled_reminders : [];

    _reminderDrafts = bookingReminders.map((reminder, index) => ({
        key: reminder.id ? `saved-${reminder.id}` : `draft-${Date.now()}-${index}`,
        id: reminder.id ?? null,
        scheduled_at: reminder.scheduled_at,
        status: reminder.status || 'pending',
        sent_at: reminder.sent_at || null,
    }));

    _reminderBasePendingSignature = getPendingReminderSignature(
        _reminderDrafts.filter(reminder => reminder.status === 'pending')
    );
    _reminderHasLocalDraftChanges = false;
}

function clearReminderModalMessage() {
    const msgDiv = document.getElementById('reminder-inline-message');

    if (!msgDiv) {
        return;
    }

    if (_reminderInlineMessageTimeout) {
        clearTimeout(_reminderInlineMessageTimeout);
        _reminderInlineMessageTimeout = null;
    }

    msgDiv.className = 'hidden rounded-lg border p-3 text-sm';
    msgDiv.textContent = '';
}

function showReminderModalMessage(message, type) {
    const msgDiv = document.getElementById('reminder-inline-message');

    if (!msgDiv) {
        showActionMessage(message, type);
        return;
    }

    if (_reminderInlineMessageTimeout) {
        clearTimeout(_reminderInlineMessageTimeout);
        _reminderInlineMessageTimeout = null;
    }

    msgDiv.className = `rounded-lg border p-3 text-sm ${
        type === 'error'
            ? 'border-red-200 bg-red-50 text-red-700'
            : 'border-green-200 bg-green-50 text-green-700'
    }`;
    msgDiv.textContent = message;

    if (type === 'success') {
        _reminderInlineMessageTimeout = setTimeout(() => {
            clearReminderModalMessage();
        }, 5000);
    }
}

function syncReminderDraftLocalState() {
    _reminderHasLocalDraftChanges = getPendingReminderSignature() !== _reminderBasePendingSignature;
}

function parseReminderDateTime(value) {
    if (!value) {
        return null;
    }

    if (value instanceof Date) {
        return new Date(value.getTime());
    }

    if (typeof value === 'string' && value.includes(' ') && !value.includes('T')) {
        return new Date(value.replace(' ', 'T'));
    }

    return new Date(value);
}

function formatReminderPayloadDate(dateObj) {
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    const hour = String(dateObj.getHours()).padStart(2, '0');
    const minute = String(dateObj.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day} ${hour}:${minute}:00`;
}

function resetReminderComposer() {
    _reminderDay = null;
    _reminderTime = null;
    _editingReminderKey = null;

    document.getElementById('custom-date-wrapper').classList.add('hidden');
    document.getElementById('reminder-time-section').classList.add('hidden');
    document.getElementById('custom-time-wrapper').classList.add('hidden');
    document.getElementById('selected-time-display').classList.add('hidden');
    document.getElementById('selected-time-display').textContent = '';
    document.getElementById('reminder-custom-date').value = '';
    document.getElementById('reminder-custom-time').value = '';

    document.querySelectorAll('.day-btn').forEach(b => {
        b.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        b.classList.add('border-gray-300');
    });
    document.querySelectorAll('.time-btn').forEach(b => {
        b.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        b.classList.add('border-gray-300');
    });
    document.getElementById('custom-time-toggle-btn').classList.remove('bg-green-600', 'text-white', 'border-green-600');
    document.getElementById('save-reminder-draft-text').textContent = 'Save Reminder';
    renderPresetTimes(false);
    updateReminderActionButtons();
}

function startNewReminderDraft() {
    if (getReminderAvailableSlots() <= 0) {
        showReminderModalMessage('You can schedule only 3 reminders for this booking.', 'error');
        return;
    }

    resetReminderComposer();
}

function canComposeReminder() {
    return _editingReminderKey !== null || getReminderAvailableSlots() > 0;
}

function selectReminderDay(day) {
    if (!canComposeReminder()) {
        return;
    }

    _reminderDay = day;

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('reminder-custom-date').min = today;

    document.querySelectorAll('.day-btn').forEach(b => {
        b.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        b.classList.add('border-gray-300');
    });

    const btn = document.getElementById('day-' + day);
    if (btn) {
        btn.classList.add('bg-green-600', 'text-white', 'border-green-600');
        btn.classList.remove('border-gray-300');
    }

    if (day === 'custom') {
        document.getElementById('custom-date-wrapper').classList.remove('hidden');
        if (!document.getElementById('reminder-custom-date').value) {
            document.getElementById('reminder-custom-date').value = today;
        }
    } else {
        document.getElementById('custom-date-wrapper').classList.add('hidden');
    }

    document.getElementById('reminder-time-section').classList.remove('hidden');

    renderPresetTimes(day === 'today');

    updateReminderActionButtons();
}

function formatTime24ToLabel(t) {
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${String(hour12).padStart(2, '0')}:${m} ${ampm}`;
}

function addMinutesToNow(deltaMinutes) {
    const d = new Date(Date.now() + deltaMinutes * 60000);
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
}

function renderPresetTimes(isToday) {
    const wrap = document.getElementById('time-btn-group');
    const customTimeInput = document.getElementById('reminder-custom-time');
    if (!wrap) return;

    let times;
    if (isToday) {
        customTimeInput.min = addMinutesToNow(5);
        times = [addMinutesToNow(5), addMinutesToNow(125), addMinutesToNow(245)];
    } else {
        customTimeInput.removeAttribute('min');
        times = ['08:00', '10:00', '12:00'];
    }

    // Clear selected time if it no longer matches a preset
    if (_reminderTime && !times.includes(_reminderTime)) {
        _reminderTime = null;
        updateSelectedTimeDisplay();
    }

    wrap.innerHTML = times.map(t => {
        const label = formatTime24ToLabel(t);
       return `<button id="time-${t.replace(':', '')}" onclick="selectReminderTime('${t}')"
        class="time-btn px-2 py-2 rounded-lg border border-gray-300 text-sm font-medium cursor-pointer hover:bg-gray-100">
    ${label}
</button>`;
    }).join('');

    // Re-highlight if selected time still valid
    if (_reminderTime) {
        const activeBtn = document.getElementById('time-' + _reminderTime.replace(':', ''));
        if (activeBtn) {
            activeBtn.classList.add('bg-green-600', 'text-white', 'border-green-600');
            activeBtn.classList.remove('border-gray-300');
        }
    }
}

function handleCustomReminderDateChange(value) {
    if (!canComposeReminder()) {
        return;
    }

    if (value) {
        _reminderDay = 'custom';
        const today = new Date().toISOString().split('T')[0];
        renderPresetTimes(value === today);
    }
    updateReminderActionButtons();
}

function selectReminderTime(time24) {
    if (!canComposeReminder()) {
        return;
    }

    _reminderTime = time24;
    document.querySelectorAll('.time-btn').forEach(b => {
        b.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        b.classList.add('border-gray-300');
    });
    const id = 'time-' + time24.replace(':', '');
    const btn = document.getElementById(id);
    if (btn) {
        btn.classList.add('bg-green-600', 'text-white', 'border-green-600');
        btn.classList.remove('border-gray-300');
    }

    document.getElementById('custom-time-wrapper').classList.add('hidden');
    document.getElementById('custom-time-toggle-btn').classList.remove('bg-green-600', 'text-white', 'border-green-600');

    updateSelectedTimeDisplay();
    updateReminderActionButtons();
    autoSaveReminderDraftIfReady();
}

function toggleCustomTimeInput() {
    if (!canComposeReminder()) {
        const wrapper = document.getElementById('custom-time-wrapper');
        const toggleBtn = document.getElementById('custom-time-toggle-btn');
        wrapper.classList.add('hidden');
        toggleBtn.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        return;
    }

    const wrapper = document.getElementById('custom-time-wrapper');
    const isHidden = wrapper.classList.contains('hidden');
    wrapper.classList.toggle('hidden');
    const toggleBtn = document.getElementById('custom-time-toggle-btn');
    if (isHidden) {
        toggleBtn.classList.add('bg-green-600', 'text-white', 'border-green-600');
        document.querySelectorAll('.time-btn').forEach(b => {
            b.classList.remove('bg-green-600', 'text-white', 'border-green-600');
            b.classList.add('border-gray-300');
        });
        document.getElementById('reminder-custom-time').focus();
    } else {
        toggleBtn.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        if (!document.getElementById('reminder-custom-time').value) {
            _reminderTime = null;
            updateSelectedTimeDisplay();
        }
    }

    updateReminderActionButtons();
}

function applyCustomTime(time24) {
    if (!canComposeReminder()) {
        return;
    }

    _reminderTime = time24 || null;
    updateSelectedTimeDisplay();
    updateReminderActionButtons();
    autoSaveReminderDraftIfReady();
}

function autoSaveReminderDraftIfReady() {
    if (_suppressReminderAutoSave) {
        return;
    }

    if (!buildReminderDateFromSelection()) {
        return;
    }

    saveReminderDraft();
}

function updateSelectedTimeDisplay() {
    const display = document.getElementById('selected-time-display');
    if (_reminderTime) {
        const [h, m] = _reminderTime.split(':');
        const hour = parseInt(h, 10);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        display.textContent = String(hour12).padStart(2, '0') + ':' + m + ' ' + ampm;
        display.classList.remove('hidden');
    } else {
        display.classList.add('hidden');
    }
}

function buildReminderDateFromSelection() {
    if (!_reminderDay || !_reminderTime) {
        return null;
    }

    let scheduledDate;
    if (_reminderDay === 'today') {
        scheduledDate = new Date();
    } else if (_reminderDay === 'tomorrow') {
        scheduledDate = new Date();
        scheduledDate.setDate(scheduledDate.getDate() + 1);
    } else {
        const customVal = document.getElementById('reminder-custom-date').value;
        if (!customVal) {
            return null;
        }
        scheduledDate = new Date(customVal + 'T00:00:00');
    }

    const [h, m] = _reminderTime.split(':');
    scheduledDate.setHours(parseInt(h, 10), parseInt(m, 10), 0, 0);

    return scheduledDate;
}

function getPendingReminderDrafts() {
    return _reminderDrafts.filter(reminder => reminder.status === 'pending');
}

function getPendingReminderSignature(reminders = null) {
    const source = Array.isArray(reminders) ? reminders : getPendingReminderDrafts();

    return source
        .map(reminder => {
            const reminderDate = parseReminderDateTime(reminder.scheduled_at);

            if (reminderDate && !Number.isNaN(reminderDate.getTime())) {
                return formatReminderPayloadDate(reminderDate);
            }

            return String(reminder.scheduled_at || '');
        })
        .sort()
        .join('|');
}

function getSentReminderDraftCount() {
    const sentInList = _reminderDrafts.filter(reminder => reminder.status === 'sent').length;
    const sentFromBooking = Number(currentBooking?.reminder_count ?? 0);

    return Math.max(sentInList, Number.isFinite(sentFromBooking) ? sentFromBooking : 0);
}

function getReminderAvailableSlots() {
    const usedSlots = getPendingReminderDrafts().length;

    return Math.max(0, 3 - usedSlots);
}

function updateReminderActionButtons() {
    const saveButton = document.getElementById('save-reminder-draft-btn');
    const scheduleButton = document.getElementById('send-reminder-btn');
    const addMoreButton = document.getElementById('reminder-add-more-btn');
    const hasDraftDate = !!buildReminderDateFromSelection();
    const canSave = hasDraftDate && (_editingReminderKey !== null || getReminderAvailableSlots() > 0);
    const hasPending = getPendingReminderDrafts().length > 0;
    const hasPendingChanges = getPendingReminderSignature() !== _reminderBasePendingSignature;
    const hasUnsavedComposerState = _editingReminderKey !== null || hasDraftDate;
    const canAddMore = getReminderAvailableSlots() > 0;
    const canCompose = canAddMore || _editingReminderKey !== null;

    saveButton.disabled = !canSave;
    scheduleButton.disabled = !hasPending || hasUnsavedComposerState || !hasPendingChanges;

    // document.querySelectorAll('.day-btn, .time-btn').forEach(btn => {
    //     btn.disabled = !canCompose;
    //     btn.classList.toggle('opacity-50', !canCompose);
    //     btn.classList.toggle('cursor-not-allowed', !canCompose);
    // });

    document.querySelectorAll('.day-btn').forEach(btn => {
    btn.disabled = !canCompose;
    btn.classList.toggle('opacity-50', !canCompose);
    btn.classList.toggle('cursor-not-allowed', !canCompose);
    if (!canCompose) {
        btn.setAttribute('title', 'Maximum 3 reminders already scheduled');
        btn.setAttribute('data-tooltip', 'Maximum 3 reminders already scheduled');
    } else {
        btn.removeAttribute('title');
        btn.removeAttribute('data-tooltip');
    }
});

document.querySelectorAll('.time-btn').forEach(btn => {
    btn.disabled = !canCompose;
    btn.classList.toggle('opacity-50', !canCompose);
    btn.classList.toggle('cursor-not-allowed', !canCompose);
});

    const customTimeToggleBtn = document.getElementById('custom-time-toggle-btn');
    const customDateInput = document.getElementById('reminder-custom-date');
    const customTimeInput = document.getElementById('reminder-custom-time');

    if (customTimeToggleBtn) {
        customTimeToggleBtn.disabled = !canCompose;
        customTimeToggleBtn.classList.toggle('opacity-50', !canCompose);
        customTimeToggleBtn.classList.toggle('cursor-not-allowed', !canCompose);
    }

    if (customDateInput) {
        customDateInput.disabled = !canCompose;
    }

    if (customTimeInput) {
        customTimeInput.disabled = !canCompose;
    }

    if (!canCompose && _editingReminderKey === null) {
        _reminderDay = null;
        _reminderTime = null;

        document.getElementById('custom-date-wrapper').classList.add('hidden');
        document.getElementById('reminder-time-section').classList.add('hidden');
        document.getElementById('custom-time-wrapper').classList.add('hidden');
        document.getElementById('selected-time-display').classList.add('hidden');
        document.getElementById('selected-time-display').textContent = '';
        document.getElementById('reminder-custom-date').value = '';
        document.getElementById('reminder-custom-time').value = '';

        document.querySelectorAll('.day-btn, .time-btn').forEach(btn => {
            btn.classList.remove('bg-green-600', 'text-white', 'border-green-600');
            btn.classList.add('border-gray-300');
        });

        if (customTimeToggleBtn) {
            customTimeToggleBtn.classList.remove('bg-green-600', 'text-white', 'border-green-600');
        }
    }

    if (addMoreButton) {
        addMoreButton.disabled = !canAddMore;
        addMoreButton.classList.toggle('text-green-500', canAddMore);
        addMoreButton.classList.toggle('hover:text-green-600', canAddMore);
        addMoreButton.classList.toggle('text-green-300', !canAddMore);
        addMoreButton.classList.toggle('pointer-events-none', !canAddMore);
    }
}

function sortReminderDrafts() {
    _reminderDrafts = _reminderDrafts
        .slice()
        .sort((first, second) => parseReminderDateTime(first.scheduled_at).getTime() - parseReminderDateTime(second.scheduled_at).getTime());
}

function saveReminderDraft() {
    const scheduledDate = buildReminderDateFromSelection();
    if (!scheduledDate) {
        showReminderModalMessage('Please choose both date and time before saving.', 'error');
        return;
    }

    if (scheduledDate.getTime() < Date.now() - 60000) {
        showReminderModalMessage('Reminder time must be now or in the future.', 'error');
        return;
    }

    const scheduledAtValue = formatReminderPayloadDate(scheduledDate);

    const hasDuplicate = _reminderDrafts.some(reminder => {
        if (reminder.key === _editingReminderKey || reminder.status !== 'pending') {
            return false;
        }

        const existingDate = parseReminderDateTime(reminder.scheduled_at);
        if (!existingDate || Number.isNaN(existingDate.getTime())) {
            return false;
        }

        return formatReminderPayloadDate(existingDate) === scheduledAtValue;
    });

    if (hasDuplicate) {
        showReminderModalMessage('This reminder time is already in the list. Choose a different time.', 'error');
        return;
    }

    if (_editingReminderKey) {
        _reminderDrafts = _reminderDrafts.map(reminder => {
            if (reminder.key !== _editingReminderKey) {
                return reminder;
            }

            return {
                ...reminder,
                scheduled_at: scheduledAtValue,
            };
        });

        showReminderModalMessage('Reminder updated in list. Click Schedule Reminder to save changes.', 'success');
    } else {
        if (getReminderAvailableSlots() <= 0) {
            showReminderModalMessage('You can schedule only 3 reminders for this booking.', 'error');
            return;
        }

        _reminderDrafts.push({
            key: `draft-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
            id: null,
            scheduled_at: scheduledAtValue,
            status: 'pending',
            sent_at: null,
        });

        clearReminderModalMessage();
    }

    sortReminderDrafts();
    renderReminderDrafts();
    resetReminderComposer();
}

function renderReminderDrafts() {
    const section = document.getElementById('reminder-list-section');
    const list = document.getElementById('reminder-list');
    const addMoreButton = document.getElementById('reminder-add-more-btn');
    const viewMoreSentButton = document.getElementById('reminder-view-more-sent-btn');

    if (_reminderDrafts.length === 0) {
        section.classList.add('hidden');
        list.innerHTML = '';
        if (viewMoreSentButton) {
            viewMoreSentButton.classList.add('hidden');
        }
        syncReminderDraftLocalState();
        updateReminderActionButtons();
        return;
    }

    section.classList.remove('hidden');

    const sentReminders = _reminderDrafts.filter(reminder => reminder.status === 'sent');
    let sentVisibleCount = 0;

    const visibleReminders = _reminderDrafts.filter(reminder => {
        if (reminder.status !== 'sent') {
            return true;
        }

        if (_showAllSentReminders) {
            sentVisibleCount += 1;
            return true;
        }

        if (sentVisibleCount < 3) {
            sentVisibleCount += 1;
            return true;
        }

        return false;
    });

    list.innerHTML = visibleReminders.map((reminder, index) => renderReminderDraftItem(reminder, index)).join('');

    if (viewMoreSentButton) {
        const shouldShowViewMore = sentReminders.length > 3 && !_showAllSentReminders;
        viewMoreSentButton.classList.toggle('hidden', !shouldShowViewMore);
    }

    const canAddMore = getReminderAvailableSlots() > 0;
    addMoreButton.disabled = !canAddMore;
    addMoreButton.classList.toggle('text-green-500', canAddMore);
    addMoreButton.classList.toggle('hover:text-green-600', canAddMore);
    addMoreButton.classList.toggle('text-green-300', !canAddMore);
    addMoreButton.classList.toggle('cursor-not-allowed', !canAddMore);
    addMoreButton.classList.toggle('pointer-events-none', !canAddMore);

    syncReminderDraftLocalState();
    updateReminderActionButtons();
}

function showAllSentReminders() {
    _showAllSentReminders = true;
    renderReminderDrafts();
}

function renderReminderDraftItem(reminder, index) {
    const reminderDate = parseReminderDateTime(reminder.scheduled_at);
    const dateLabel = getReminderDateLabel(reminderDate);
    const timeLabel = reminderDate.toLocaleTimeString('en-IN', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });

    let actions = '';
    if (reminder.status === 'pending') {
        actions = `
            <div class="flex items-center gap-2">
                <button type="button" onclick="editReminderDraft('${reminder.key}')" class="text-gray-400 hover:text-gray-700" aria-label="Edit reminder cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                </button>
                <button type="button" onclick="deleteReminderDraft('${reminder.key}')" class="text-red-400 hover:text-red-600" aria-label="Delete reminder">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                    </svg>
                </button>
            </div>`;
    }

    let statusLabel;
    if (reminder.status === 'sent') {
        statusLabel = '<span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600">Sent</span>';
    } else if (reminder.id === null) {
        // locally saved draft — not yet pushed to server via Schedule Reminder
        statusLabel = '<span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-600">Saved</span>';
    } else {
        // server-side pending — Schedule Reminder was clicked, waiting to be delivered
        statusLabel = '<span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-600">Scheduled</span>';
    }

    return `
        <div class="border-b border-gray-100 pb-3 last:border-b-0 last:pb-0">
            <div class="flex items-start justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-800 leading-6">
                    <span class="font-medium">${index + 1}.</span>
                    <span>${dateLabel}</span>
                    <span class="text-gray-400">|</span>
                    <span class="text-orange-500">${timeLabel}</span>
                    ${statusLabel}
                </div>
                ${actions}
            </div>
        </div>`;
}

function getReminderDateLabel(reminderDate) {
    if (!(reminderDate instanceof Date) || Number.isNaN(reminderDate.getTime())) {
        return '-';
    }

    const today = new Date();
    const todayOnly = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const tomorrowOnly = new Date(todayOnly);
    tomorrowOnly.setDate(tomorrowOnly.getDate() + 1);
    const reminderOnly = new Date(reminderDate.getFullYear(), reminderDate.getMonth(), reminderDate.getDate());

    if (reminderOnly.getTime() === todayOnly.getTime()) {
        return 'Today';
    }

    if (reminderOnly.getTime() === tomorrowOnly.getTime()) {
        return 'Tomorrow';
    }

    return reminderDate.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function editReminderDraft(reminderKey) {
    const reminder = _reminderDrafts.find(item => item.key === reminderKey && item.status === 'pending');
    if (!reminder) {
        return;
    }

    _suppressReminderAutoSave = true;

    resetReminderComposer();
    _editingReminderKey = reminderKey;
    document.getElementById('save-reminder-draft-text').textContent = 'Update Reminder';

    const scheduledDate = parseReminderDateTime(reminder.scheduled_at);
    if (!(scheduledDate instanceof Date) || Number.isNaN(scheduledDate.getTime())) {
        _suppressReminderAutoSave = false;
        showReminderModalMessage('Unable to edit this reminder because its date/time is invalid.', 'error');
        return;
    }
    const today = new Date();
    const todayOnly = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const tomorrowOnly = new Date(todayOnly);
    tomorrowOnly.setDate(tomorrowOnly.getDate() + 1);
    const scheduledOnly = new Date(scheduledDate.getFullYear(), scheduledDate.getMonth(), scheduledDate.getDate());

    if (scheduledOnly.getTime() === todayOnly.getTime()) {
        selectReminderDay('today');
    } else if (scheduledOnly.getTime() === tomorrowOnly.getTime()) {
        selectReminderDay('tomorrow');
    } else {
        selectReminderDay('custom');
        document.getElementById('reminder-custom-date').value = `${scheduledDate.getFullYear()}-${String(scheduledDate.getMonth() + 1).padStart(2, '0')}-${String(scheduledDate.getDate()).padStart(2, '0')}`;
    }

    const timeValue = `${String(scheduledDate.getHours()).padStart(2, '0')}:${String(scheduledDate.getMinutes()).padStart(2, '0')}`;
    if (['08:00', '10:00', '12:00'].includes(timeValue)) {
        selectReminderTime(timeValue);
    } else {
        document.getElementById('reminder-custom-time').value = timeValue;
        toggleCustomTimeInput();
        applyCustomTime(timeValue);
    }

    _suppressReminderAutoSave = false;

    updateReminderActionButtons();
}

// function deleteReminderDraft(reminderKey) {
//     const reminder = _reminderDrafts.find(item => item.key === reminderKey);
//     if (!reminder) {
//         return;
//     }

//     const reminderDate = parseReminderDateTime(reminder.scheduled_at);
//     const label = reminderDate && !Number.isNaN(reminderDate.getTime())
//         ? `${getReminderDateLabel(reminderDate)} ${reminderDate.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true })}`
//         : 'this reminder';

//     if (!window.confirm(`Are you sure you want to delete ${label}?`)) {
//         return;
//     }

//     _reminderDrafts = _reminderDrafts.filter(reminder => reminder.key !== reminderKey);
//     if (_editingReminderKey === reminderKey) {
//         resetReminderComposer();
//     }
//     renderReminderDrafts();
// }
// Replace the existing deleteReminderDraft function:
let _pendingDeleteReminderKey = null;

function deleteReminderDraft(reminderKey) {
    const reminder = _reminderDrafts.find(item => item.key === reminderKey);
    if (!reminder) {
        return;
    }

    const reminderDate = parseReminderDateTime(reminder.scheduled_at);
    const label = reminderDate && !Number.isNaN(reminderDate.getTime())
        ? `${getReminderDateLabel(reminderDate)} ${reminderDate.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true })}`
        : 'this reminder';

    _pendingDeleteReminderKey = reminderKey;
    document.getElementById('delete-reminder-confirm-label').textContent =
        `${label} will be removed from your scheduled reminders.`;
    document.getElementById('delete-reminder-confirm-modal').classList.remove('hidden');
}

function closeDeleteReminderConfirmModal() {
    _pendingDeleteReminderKey = null;
    document.getElementById('delete-reminder-confirm-modal').classList.add('hidden');
}

function executeDeleteReminderDraft() {
    const reminderKey = _pendingDeleteReminderKey;
    closeDeleteReminderConfirmModal();

    if (!reminderKey) return;

    _reminderDrafts = _reminderDrafts.filter(reminder => reminder.key !== reminderKey);
    if (_editingReminderKey === reminderKey) {
        resetReminderComposer();
    }
    renderReminderDrafts();
}

async function confirmSendReminder() {
    if (_editingReminderKey !== null) {
        showReminderModalMessage('Please save the edited reminder before scheduling.', 'error');
        return;
    }

    if (buildReminderDateFromSelection()) {
        showReminderModalMessage('Please save the current reminder before scheduling.', 'error');
        return;
    }

    const pendingReminders = getPendingReminderDrafts().map(reminder => {
        const reminderDate = parseReminderDateTime(reminder.scheduled_at);

        return {
            scheduled_at: reminderDate ? formatReminderPayloadDate(reminderDate) : reminder.scheduled_at,
        };
    });

    if (pendingReminders.length === 0) {
        showReminderModalMessage('Add at least one reminder before scheduling.', 'error');
        return;
    }

    document.getElementById('reminder-btn-text').classList.add('hidden');
    document.getElementById('reminder-spinner').classList.remove('hidden');
    document.getElementById('send-reminder-btn').disabled = true;

    try {
        const response = await fetch(`${API_URL}/bookings/${bookingId}/send-reminder`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                scheduled_reminders: pendingReminders
            })
        });

        if (response.ok) {
            const result = await response.json();
            if (currentBooking) {
                currentBooking.scheduled_reminders = result?.data?.scheduled_reminders || [];
                currentBooking.remaining_reminder_slots = Number(result?.data?.remaining_reminder_slots ?? 0);
            }
            hydrateReminderDraftsFromBooking(true);
            closeReminderModal();
            const slotsLeft = getReminderAvailableSlots();
            const successAddMoreBtn = document.getElementById('success-add-more-btn');
            if (successAddMoreBtn) {
                successAddMoreBtn.classList.toggle('hidden', slotsLeft <= 0);
            }
            document.getElementById('reminder-success-modal').classList.remove('hidden');
            await loadBookingDetails();
        } else if (response.status === 400 || response.status === 422 || response.status === 429) {
            const error = await response.json();
            showReminderModalMessage(error.message || 'Cannot schedule reminder', 'error');
            await loadBookingDetails();
        } else {
            const error = await response.json();
            showReminderModalMessage(error.message || 'Error scheduling reminder', 'error');
        }
    } catch (err) {
        console.error('scheduleReminder error:', err);
        showReminderModalMessage('Network error. Please try again.', 'error');
    } finally {
        document.getElementById('reminder-btn-text').classList.remove('hidden');
        document.getElementById('reminder-spinner').classList.add('hidden');
        document.getElementById('send-reminder-btn').disabled = false;
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
    return getPosBookingStatusColor(status);
}

function getPaymentStatusColor(status) {
    return getPosPaymentStatusColor(status);
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const markPaidModal = document.getElementById('mark-paid-modal');
    const releaseModal = document.getElementById('release-modal');
    const cancelBookingModal = document.getElementById('cancel-booking-modal');
    const reminderSuccessModal = document.getElementById('reminder-success-modal');

    if (event.target === markPaidModal) {
        closeMarkPaidModal();
    }
    if (event.target === releaseModal) {
        closeReleaseModal();
    }
    if (event.target === cancelBookingModal) {
        closeCancelBookingModal();
    }
    const cancelDebitNoteModal = document.getElementById('cancel-debit-note-modal');
    if (event.target === cancelDebitNoteModal) {
        closeCancelDebitNoteModal();
    }
    if (event.target === reminderSuccessModal) {
        closeReminderSuccessModal();
    }
});
</script>
@endsection