{{-- resources/views/vendor/offers/components/offer-form.blade.php --}}

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

    {{-- Header --}}
    @include('vendor.offers.components.offer-header')

    <div class="p-3 sm:p-4 lg:p-6">

        {{-- ── Customer Select ── --}}
        <div class="mb-8">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Select Customer</label>
            <p class="block text-xs text-gray-400 tracking-wider mb-2">Search an existing customer or add a new customer to proceed with offer.</p>

            <div id="offer-search-container" class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1 border border-gray-300">
                    <input type="text" id="offer-customer-search" autocomplete="off"
                        placeholder="Search customer by name, email, or mobile number"
                        class="w-full border-gray-300 focus:ring-green-500 text-sm py-2.5 px-2 min-h-[44px]">
                    <div id="offer-customer-suggestions"
                        class="absolute z-50 w-full bg-white border rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto"></div>
                </div>
                <button type="button" onclick="openCustomerModal()"
                    class="w-full sm:w-auto min-h-[44px] bg-green-600 text-white px-4 hover:bg-green-700 transition flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="ml-1 text-sm font-semibold">Add New Customer</span>
                </button>
                @include('vendor.offers.components.customer-modal')
            </div>

            {{-- Selected Customer Card --}}
            <div id="offer-customer-selected-card"
                class="hidden flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-green-50 border border-green-200 rounded-lg p-4 mt-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-[#2D5A43] rounded-full flex items-center justify-center text-white font-bold text-sm"
                        id="offer-cust-initials">--</div>
                    <div>
                        <h4 id="offer-cust-name" class="font-bold text-gray-800 text-sm leading-tight">Customer Name</h4>
                        <p id="offer-cust-details" class="text-xs text-gray-500 mt-0.5">Contact Details</p>
                    </div>
                </div>
                <button onclick="clearOfferCustomer()"
                    class="w-full sm:w-auto text-xs font-bold text-red-500 hover:text-red-700 px-3 py-2 border border-red-200 rounded-md bg-white">
                    Change
                </button>
            </div>

            {{-- Prefill from enquiry --}}
            @if($enquiry)
            <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                </svg>
                <p class="text-xs text-blue-700">
                    Prefilled from Enquiry <strong>#{{ $enquiry->id }}</strong> — {{ $enquiry->customer_name ?? '' }}
                </p>
            </div>
            @endif
        </div>

        {{-- ── OOH Table ── --}}
        <div class="space-y-6">
            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center mb-1">
                    <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span> OOH (Static)
                </h4>
                <p class="text-xs text-gray-400 mb-2 px-3.5">Select traditional billboard hoardings for long-term display.</p>
                <div class="overflow-x-auto border border-gray-100 rounded">
                    <table class="min-w-[700px] w-full divide-y divide-gray-200 text-left text-xs sm:text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-3 sm:px-4 py-3 font-semibold">Hoarding Name</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold hidden sm:table-cell">Monthly Rental</th>
                                <th class="px-4 py-3 font-semibold text-center">Booking Duration</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold">Total Cost</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-right">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="offer-ooh-selected-list" class="divide-y divide-gray-50 bg-white">
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">No static hoardings selected</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── DOOH Table ── --}}
            <div>
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center mb-1">
                    <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> Digital Hoardings (DOOH)
                </h4>
                <p class="text-xs text-gray-400 mb-2 px-3.5">Select digital screens and configure slot bookings.</p>
                <div class="overflow-x-auto border border-gray-100 rounded">
                    <table class="min-w-[700px] w-full divide-y divide-gray-200 text-left text-xs sm:text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-3 sm:px-4 py-3 font-semibold">Screen Location</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold hidden sm:table-cell">Slot Price</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-center hidden sm:table-cell">Slots/Day</th>
                                <th class="px-4 py-3 font-semibold text-center">Booking Duration</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold">Total Cost</th>
                                <th class="px-3 sm:px-4 py-3 font-semibold text-right">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="offer-dooh-selected-list" class="divide-y divide-gray-50 bg-white">
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">No digital slots selected</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Bottom Actions ── --}}
        @include('vendor.offers.components.offer-buttons')
    </div>
    <div id="hoardingDateModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/40" onclick="closeHoardingDateModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-fit max-w-[95vw] mx-4 p-4 sm:p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800">Select Hoarding Duration</h3>
                <button type="button" onclick="closeHoardingDateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <input type="text" id="hoardingDateModalPicker" class="hidden">

            <div id="hoardingDateModalCalendar" class="mb-4"></div>

            <div id="hoardingDateModalValue" class="text-xs text-gray-500 mb-4 text-center">
                No date range selected
            </div>

            <div class="flex gap-3">
                <button type="button"
                    onclick="closeHoardingDateModal()"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50">
                    Cancel
                </button>
                <button type="button"
                    onclick="applyHoardingDateModal()"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>
<script>
const OFFER_SUGGESTIONS_URL = "{{ route('vendor.offers.customer-suggestions') }}";

// Set current date
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('offer-date');
    if (el) {
        el.innerText = new Date().toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        el.classList.remove('hidden');
    }
});

// ── Customer search ──
let offerSearchTimer = null;
document.getElementById('offer-customer-search')?.addEventListener('input', function () {
    clearTimeout(offerSearchTimer);
    const q   = this.value.trim();
    const box = document.getElementById('offer-customer-suggestions');
    if (q.length < 2) { box.classList.add('hidden'); return; }

    offerSearchTimer = setTimeout(async () => {
        try {
            const res  = await fetch(`${OFFER_SUGGESTIONS_URL}?search=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            const list = data.data || [];
            box.innerHTML = list.length
                ? list.map(c => `
                    <div class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b last:border-0"
                        onclick='selectOfferCustomer(${JSON.stringify(c).replace(/'/g, "&apos;")})'>
                        <div class="text-sm font-bold text-gray-800">${c.name}</div>
                        <div class="text-[10px] text-gray-500">${[c.phone, c.email].filter(Boolean).join(' · ')}</div>
                    </div>`).join('')
                : '<div class="p-4 text-xs text-gray-400 text-center">No customers found</div>';
            box.classList.remove('hidden');
        } catch (err) { console.error('Customer search error:', err); }
    }, 300);
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('#offer-search-container')) {
        document.getElementById('offer-customer-suggestions')?.classList.add('hidden');
    }
});

function selectOfferCustomer(c) {
    if (!c?.name) return;
    window.offerSelectedCustomer = c;
    document.getElementById('offer-search-container')?.classList.add('hidden');
    document.getElementById('offer-customer-selected-card')?.classList.remove('hidden');
    document.getElementById('offer-customer-suggestions')?.classList.add('hidden');
    document.getElementById('offer-cust-name').innerText     = c.name;
    document.getElementById('offer-cust-details').innerText  = [c.email, c.phone].filter(Boolean).join(' · ');
    document.getElementById('offer-cust-initials').innerText = c.name.slice(0, 2).toUpperCase();
}

function clearOfferCustomer() {
    window.offerSelectedCustomer = null;
    document.getElementById('offer-search-container')?.classList.remove('hidden');
    document.getElementById('offer-customer-selected-card')?.classList.add('hidden');
    const si = document.getElementById('offer-customer-search');
    if (si) { si.value = ''; si.focus(); }
}

function openCustomerModal()  { document.getElementById('customerModal')?.classList.remove('hidden'); }
function closeCustomerModal() { document.getElementById('customerModal')?.classList.add('hidden'); }

// ── offerUpdateSummary — inventory se select hone par tables update hoti hain ──
function offerUpdateSummary() {
    const oohBody  = document.getElementById('offer-ooh-selected-list');
    const doohBody = document.getElementById('offer-dooh-selected-list');
    const btnCount = document.getElementById('offer-btn-count');

    if (!oohBody || !doohBody) return;

    if (!offerSelectedHoardings || offerSelectedHoardings.size === 0) {
        oohBody.innerHTML  = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">No static hoardings selected</td></tr>`;
        doohBody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">No digital slots selected</td></tr>`;
        if (btnCount) btnCount.innerText = 0;
        return;
    }

    let hasOoh = false, hasDooh = false;
    oohBody.innerHTML = '';
    doohBody.innerHTML = '';

    offerSelectedHoardings.forEach((h, id) => {
        const isDooh = (h.hoarding_type ?? '').toUpperCase() === 'DOOH';
        const price  = Number(h.price_per_month ?? 0);
        const loc    = h.display_location || '';

        const rmBtn = `
            <button onclick="offerToggleCard(document.querySelector('.offer-card[data-id=\\'${id}\\']'))"
                class="text-red-400 hover:text-red-600 transition ml-auto block" title="Remove">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>`;

        if (isDooh) {
            hasDooh = true;
            doohBody.innerHTML += `
            <tr class="hover:bg-gray-50 border-b border-gray-100">
                <td class="px-4 py-3">
                    <p class="text-xs font-bold text-gray-800">${h.title}</p>
                    ${loc ? `<p class="text-[9px] text-gray-400 truncate max-w-[150px]">${loc}</p>` : ''}
                </td>
                <td class="px-3 py-3 text-xs text-gray-500 hidden sm:table-cell">₹${price.toLocaleString('en-IN')}</td>
                <td class="px-3 py-3 text-center hidden sm:table-cell">
                    <span class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full">${h.total_slots_per_day ?? 300}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">
                    <input type="text"
                        class="booking-duration-input w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-green-500 cursor-pointer bg-white"
                        id="booking-duration-dooh-${id}"
                        readonly
                        placeholder="Select date range"
                        onclick="openBookingDateModal('booking-duration-dooh-${id}', '${id}')"
                        style="background:#fff;min-width:120px;" />
                </td>
                <td class="px-3 py-3 text-xs font-bold text-green-700">₹${price.toLocaleString('en-IN')}</td>
                <td class="px-3 py-3 text-right">${rmBtn}</td>
            </tr>`;
        } else {
            hasOoh = true;
            oohBody.innerHTML += `
            <tr class="hover:bg-gray-50 border-b border-gray-100">
                <td class="px-4 py-3">
                    <p class="text-xs font-bold text-gray-800">${h.title}</p>
                    ${loc ? `<p class="text-[9px] text-gray-400 truncate max-w-[150px]">${loc}</p>` : ''}
                </td>
                <td class="px-3 py-3 text-xs text-gray-500 hidden sm:table-cell">₹${price.toLocaleString('en-IN')}</td>
                <td class="px-4 py-3 text-xs text-gray-400">
                    <input type="text"
                        class="booking-duration-input w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-green-500 cursor-pointer bg-white"
                        id="booking-duration-ooh-${id}"
                        readonly
                        placeholder="Select date range"
                        onclick="openBookingDateModal('booking-duration-ooh-${id}', '${id}')"
                        style="background:#fff;min-width:120px;" />
                </td>
                <td class="px-3 py-3 text-xs font-bold text-green-700">₹${price.toLocaleString('en-IN')}</td>
                <td class="px-3 py-3 text-right">${rmBtn}</td>
            </tr>`;
        }
    });

    if (!hasOoh)  oohBody.innerHTML  = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">No static hoardings selected</td></tr>`;
    if (!hasDooh) doohBody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">No digital slots selected</td></tr>`;
    if (btnCount) btnCount.innerText = offerSelectedHoardings.size;
    hydrateSavedBookingRanges();
}
</script>
<script>
    let bookingDateModalFp = null;
    let activeBookingInputId = null;
    let activeBookingHoardingId = null;
    let activeBookingSelection = null;

    function openBookingDateModal(inputId, hoardingId) {
        activeBookingInputId = inputId;
        activeBookingHoardingId = hoardingId;
        activeBookingSelection = null;

        const modal = document.getElementById('hoardingDateModal');
        const picker = document.getElementById('hoardingDateModalPicker');
        const valueBox = document.getElementById('hoardingDateModalValue');

        modal.classList.remove('hidden');
        valueBox.textContent = 'No date range selected';

        if (bookingDateModalFp) {
            bookingDateModalFp.destroy();
            bookingDateModalFp = null;
        }

        // Default to today as start and end
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];

        bookingDateModalFp = flatpickr(picker, {
            inline: true,
            appendTo: document.getElementById('hoardingDateModalCalendar'),
            mode: 'range',
            dateFormat: 'Y-m-d',
            minDate: 'today',
            showMonths: window.innerWidth < 640 ? 1 : 2,
            monthSelectorType: 'static',
            defaultDate: [todayStr, todayStr],
            disable: [
                function(date) {
                    // Disable all dates before today
                    return date < new Date(todayStr);
                }
            ],
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    activeBookingSelection = {
                        start: flatpickr.formatDate(selectedDates[0], 'Y-m-d'),
                        end: flatpickr.formatDate(selectedDates[1], 'Y-m-d')
                    };
                    valueBox.textContent =
                        `${formatDisplayDate(activeBookingSelection.start)} to ${formatDisplayDate(activeBookingSelection.end)}`;
                }
            }
        });
    }

    function closeHoardingDateModal() {
        document.getElementById('hoardingDateModal').classList.add('hidden');
        document.getElementById('hoardingDateModalCalendar').innerHTML = '';
        activeBookingInputId = null;
        activeBookingHoardingId = null;
        activeBookingSelection = null;

        if (bookingDateModalFp) {
            bookingDateModalFp.destroy();
            bookingDateModalFp = null;
        }
    }

    function applyHoardingDateModal() {
        if (!activeBookingInputId || !activeBookingHoardingId || !activeBookingSelection) {
            closeHoardingDateModal();
            return;
        }

        const input = document.getElementById(activeBookingInputId);
        if (input) {
            input.value =
                `${formatDisplayDate(activeBookingSelection.start)} - ${formatDisplayDate(activeBookingSelection.end)}`;
        }

        closeHoardingDateModal();
    }

    function formatDisplayDate(dateStr) {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function hydrateSavedBookingRanges() {
        // Set all booking-duration-inputs to today by default
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];
        const todayDisplay = formatDisplayDate(todayStr);
        document.querySelectorAll('.booking-duration-input').forEach(input => {
            input.value = `${todayDisplay} - ${todayDisplay}`;
        });
    }
</script>