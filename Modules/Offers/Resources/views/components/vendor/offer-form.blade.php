{{--
    resources/views/vendor/offers/components/offer-form.blade.php

    Props expected from parent view:
      $enquiry      — Enquiry model | null
      $enquiryItems — Collection of EnquiryItem (vendor-filtered, with image_url) | empty collection
--}}

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="offer-form-root">

    {{-- ── Header ── --}}
    <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-white flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-800">
                {{ $enquiry ? 'Create Offer for Enquiry #' . ($enquiry->formatted_id ?? $enquiry->id) : 'Create New Offer' }}
            </h2>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $enquiry ? 'Review hoardings, confirm campaign dates and send offer to customer.' : 'Select hoardings and build an offer.' }}
            </p>
        </div>
        @if($enquiry)
        <a href="{{ url()->previous() }}"
           class="flex-shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg px-3 py-2 transition">
            ← Back
        </a>
        @endif
    </div>

    <div class="p-4 sm:p-6">

        {{-- ════════════════════════════════════════
             ENQUIRY CONTEXT BANNER
        ════════════════════════════════════════ --}}
        @if($enquiry)
        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <div class="flex flex-wrap gap-4 text-xs">
                <div>
                    <span class="text-blue-500 font-semibold uppercase tracking-wider">Customer</span>
                    <p class="font-bold text-gray-800 mt-0.5">{{ $enquiry->customer->name ?? '—' }}</p>
                    <p class="text-gray-500">{{ $enquiry->customer->email ?? '' }}{{ ($enquiry->customer->phone ?? null) ? ' · '.$enquiry->customer->phone : '' }}</p>
                </div>
                <div>
                    <span class="text-blue-500 font-semibold uppercase tracking-wider">Enquiry Status</span>
                    <p class="mt-0.5">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold
                            {{ $enquiry->status === 'submitted' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($enquiry->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-blue-500 font-semibold uppercase tracking-wider">Hoardings</span>
                    <p class="font-bold text-gray-800 mt-0.5">{{ $enquiryItems->count() }} item(s) for your inventory</p>
                </div>
                @if($enquiry->customer_note)
                <div class="w-full">
                    <span class="text-blue-500 font-semibold uppercase tracking-wider">Customer Note</span>
                    <p class="text-gray-700 mt-0.5 italic">"{{ $enquiry->customer_note }}"</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════════
             AVAILABILITY ALERT
        ════════════════════════════════════════ --}}
        <div id="offerAvailAlert" class="hidden mb-5 rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-bold text-red-700 mb-1">Availability Conflicts</p>
                    <div id="offerAvailAlertBody" class="text-xs text-red-600 space-y-1"></div>
                </div>
                <button onclick="document.getElementById('offerAvailAlert').classList.add('hidden')"
                    class="text-red-400 hover:text-red-600">✕</button>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             OFFER DESCRIPTION
        ════════════════════════════════════════ --}}
        <div class="mb-6">
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                Offer Note / Description <span class="font-normal text-gray-400">(optional)</span>
            </label>
            <textarea id="offerDescription" rows="2" placeholder="Add any note for the customer about this offer…"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500 outline-none resize-none"></textarea>
        </div>

        {{-- ════════════════════════════════════════
             OOH TABLE
        ════════════════════════════════════════ --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest">OOH — Static Hoardings</h4>
            </div>
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="min-w-[760px] w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Hoarding</th>
                            <th class="px-3 py-3 font-semibold">Monthly Rate</th>
                            <th class="px-3 py-3 font-semibold">Package</th>
                            <th class="px-4 py-3 font-semibold text-center">Campaign Duration</th>
                            <th class="px-3 py-3 font-semibold text-center">Availability</th>
                            <th class="px-3 py-3 font-semibold text-right">Total</th>
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="offerOohList" class="divide-y divide-gray-50 bg-white">
                        <tr id="offerOohEmpty">
                            <td colspan="7" class="px-4 py-10 text-center text-gray-300 italic">
                                No static hoardings added
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             DOOH TABLE
        ════════════════════════════════════════ --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest">DOOH — Digital Screens</h4>
            </div>
            <div class="overflow-x-auto border border-gray-100 rounded-lg">
                <table class="min-w-[760px] w-full text-left text-xs">
                    <thead class="bg-gray-50 text-gray-400 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Screen</th>
                            <th class="px-3 py-3 font-semibold">Slot Price</th>
                            <th class="px-3 py-3 font-semibold">Slots/Day</th>
                            <th class="px-4 py-3 font-semibold text-center">Campaign Duration</th>
                            <th class="px-3 py-3 font-semibold text-center">Availability</th>
                            <th class="px-3 py-3 font-semibold text-right">Total</th>
                            <th class="px-3 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="offerDoohList" class="divide-y divide-gray-50 bg-white">
                        <tr id="offerDoohEmpty">
                            <td colspan="7" class="px-4 py-10 text-center text-gray-300 italic">
                                No digital screens added
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             TOTAL SUMMARY
        ════════════════════════════════════════ --}}
        <div class="flex justify-end mb-6">
            <div class="text-right">
                <p class="text-xs text-gray-400">Total Offered Amount</p>
                <p class="text-2xl font-black text-[#2D5A43]" id="offerGrandTotal">₹0</p>
                <p class="text-[10px] text-gray-400" id="offerItemCount">0 items</p>
            </div>
        </div>

        {{-- ════════════════════════════════════════
             ACTIONS
        ════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row gap-3 pt-5 border-t border-gray-100">
            <button type="button" onclick="history.back()"
                class="w-full sm:w-auto px-6 py-3 border border-gray-200 rounded-lg text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="button" id="offerSaveDraftBtn" onclick="offerSubmit('draft')"
                class="w-full sm:flex-1 py-3 bg-gray-700 text-white rounded-lg text-sm font-bold hover:bg-gray-800 transition">
                Save as Draft
            </button>
            <button type="button" id="offerSendBtn" onclick="offerSubmit('send')"
                class="w-full sm:flex-1 py-3 bg-[#2D5A43] text-white rounded-lg text-sm font-bold hover:bg-opacity-90 transition">
                Save &amp; Send Offer
            </button>
        </div>

    </div>{{-- /p-4 --}}
</div>{{-- /offer-form-root --}}

{{-- ════════════════════════════════════════════════════════════
     DATE PICKER MODAL
════════════════════════════════════════════════════════════ --}}
<div id="offerDateModal" class="fixed inset-0 z-[9999] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="offerCloseDateModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-[95vw] sm:max-w-[780px] mx-auto p-5 z-10 max-h-[90vh] overflow-y-auto">

        <div class="flex items-start justify-between mb-3 gap-3">
            <div>
                <h3 id="offerDateModalTitle" class="font-bold text-gray-800 text-sm">Select Campaign Duration</h3>
                <p class="text-[10px] text-gray-400 mt-0.5">Duration rounds up to nearest 30-day block · minimum 1 month</p>
            </div>
            <button onclick="offerCloseDateModal()" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Summary bar --}}
        <div class="grid grid-cols-3 gap-2 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 mb-3">
            <div>
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider">Period</p>
                <p id="offerDpRange" class="text-[10px] font-black text-emerald-900 leading-snug">— select start</p>
            </div>
            <div class="text-center">
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider">Duration</p>
                <p id="offerDpMonths" class="text-[10px] font-black text-emerald-900">—</p>
            </div>
            <div class="text-right">
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider">Est. Total</p>
                <p id="offerDpCost" class="text-[10px] font-black text-emerald-900">—</p>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap gap-x-4 gap-y-1 mb-3 text-[10px] font-semibold text-gray-500">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-green-100 border border-green-300"></span>Available</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-100 border border-red-300"></span>Booked</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-gray-200 border border-gray-300"></span>Blocked</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-amber-100 border border-amber-300"></span>On Hold</span>
        </div>

        {{-- Calendar --}}
        <input type="text" id="offerFpInput" class="hidden">
        <div id="offerFpContainer" class="w-full overflow-x-auto mb-3"></div>

        {{-- Quick select --}}
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-[10px] text-gray-400 font-semibold">Quick:</span>
            @foreach([1,2,3,6,12] as $m)
            <button onclick="offerQuickMonths({{ $m }})" data-months="{{ $m }}"
                class="offer-quick-chip px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">
                {{ $m }} Mo
            </button>
            @endforeach
        </div>

        <div class="flex gap-3 pt-3 border-t border-gray-100">
            <button onclick="offerCloseDateModal()"
                class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50">
                Cancel
            </button>
            <button onclick="offerApplyDates()"
                class="flex-1 py-2.5 bg-[#2D5A43] text-white rounded-xl text-sm font-bold hover:bg-opacity-90">
                Confirm Dates
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════ --}}
<script>
/* ──────────────────────────────────────────────
   SERVER DATA (PHP → JS)
────────────────────────────────────────────── */
const OFFER_ENQUIRY_ID       = @json($enquiry->id ?? null);
const OFFER_ENQUIRY_ITEMS    = @json(
    ($enquiryItems ?? collect())->map(fn($item) => [
        'id'                   => $item->id,
        'hoarding_id'          => $item->hoarding_id,
        'hoarding_type'        => $item->hoarding_type,
        'package_id'           => $item->package_id,
        'package_type'         => $item->package_type,
        'package_label'        => $item->package_label,
        'preferred_start_date' => $item->preferred_start_date?->format('Y-m-d'),
        'preferred_end_date'   => $item->preferred_end_date?->format('Y-m-d'),
        'duration_months'      => $item->duration_months,
        'services'             => $item->services,
        'hoarding' => $item->hoarding ? [
            'id'                 => $item->hoarding->id,
            'title'              => $item->hoarding->title ?? $item->hoarding->name,
            'price_per_month'    => $item->hoarding->price_per_month ?? $item->hoarding->monthly_rental ?? 0,
            'display_location'   => $item->hoarding->display_location ?? $item->hoarding->city ?? '',
            'total_slots_per_day'=> $item->hoarding->doohScreen->total_slots_per_day ?? 300,
            'image_url'          => $item->image_url ?? null,
        ] : null,
    ])->values()
);
const OFFER_STORE_URL        = "{{ route('vendor.offers.store') }}";
const OFFER_CSRF             = "{{ csrf_token() }}";

/* ──────────────────────────────────────────────
   STATE
────────────────────────────────────────────── */
// Map<hoardingId, ItemState>
const offerItems = new Map();

let offerEditingId  = null;   // hoarding id currently being edited in date modal
let offerFp         = null;   // flatpickr instance
let offerHeatmap    = {};     // date → status for current hoarding
let offerDpStart    = null;   // currently selected start in modal

/* ──────────────────────────────────────────────
   HELPERS
────────────────────────────────────────────── */
const offerFmt = v =>
    new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:0 }).format(v ?? 0);

function offerYMD(date) {
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

function offerMonths(s, e) {
    if (!s || !e) return 1;
    return Math.max(1, Math.ceil((new Date(e) - new Date(s)) / 86400000 / 30 + 1 / 30));
}

function offerEndForMonths(startISO, n) {
    const d = new Date(startISO);
    d.setDate(d.getDate() + n * 30 - 1);
    return offerYMD(d);
}

function offerSnapEnd(startISO, rawEnd) {
    const months = offerMonths(startISO, rawEnd);
    return offerEndForMonths(startISO, months);
}

function offerRange(s, e) {
    const fmt = d => new Date(d).toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' });
    const m   = offerMonths(s, e);
    return { start: fmt(s), end: fmt(e), months: m, badge: m === 1 ? '1 Month' : `${m} Months`, full: `${fmt(s)} – ${fmt(e)}` };
}

function offerEnumDates(s, e) {
    const dates = [], cur = new Date(s), last = new Date(e);
    while (cur <= last) { dates.push(offerYMD(cur)); cur.setDate(cur.getDate()+1); }
    return dates;
}

function offerToast(msg, type = 'info') {
    if (window.Swal) Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:3500, icon:type, title:msg });
    else alert(msg);
}

function offerSetBtnLoading(btnId, loading, label) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    btn.disabled = loading;
    btn.innerHTML = loading
        ? `<svg class="w-4 h-4 inline animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>${label}`
        : label;
}

/* ──────────────────────────────────────────────
   INIT — pre-load enquiry items
────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    if (OFFER_ENQUIRY_ITEMS && OFFER_ENQUIRY_ITEMS.length) {
        OFFER_ENQUIRY_ITEMS.forEach(item => {
            if (!item.hoarding) return;
            offerItems.set(item.hoarding.id, {
                enquiryItemId:       item.id,
                hoardingId:          item.hoarding.id,
                hoardingType:        item.hoarding_type,
                title:               item.hoarding.title,
                pricePerMonth:       Number(item.hoarding.price_per_month ?? 0),
                displayLocation:     item.hoarding.display_location ?? '',
                slotsPerDay:         item.hoarding.total_slots_per_day ?? 300,
                imageUrl:            item.hoarding.image_url,
                packageId:           item.package_id,
                packageType:         item.package_type,
                packageLabel:        item.package_label,
                durationMonths:      item.duration_months,
                services:            item.services,
                startDate:           item.preferred_start_date,
                endDate:             item.preferred_end_date,
                availStatus:         'unchecked', // 'unchecked' | 'checking' | 'available' | 'conflict'
                conflictLabel:       null,
            });
        });
        offerRender();
        // Auto-check availability for all pre-loaded items
        setTimeout(offerCheckAll, 500);
    }
});

/* ──────────────────────────────────────────────
   ADD HOARDING (called from inventory panel)
────────────────────────────────────────────── */
window.offerAddHoarding = function(hoarding) {
    if (offerItems.has(hoarding.id)) {
        offerToast('Already added.', 'info');
        return;
    }
    offerItems.set(hoarding.id, {
        enquiryItemId:   null,
        hoardingId:      hoarding.id,
        hoardingType:    hoarding.type?.toLowerCase() ?? 'ooh',
        title:           hoarding.title ?? hoarding.name,
        pricePerMonth:   Number(hoarding.price_per_month ?? 0),
        displayLocation: hoarding.display_location ?? hoarding.city ?? '',
        slotsPerDay:     hoarding.total_slots_per_day ?? 300,
        imageUrl:        hoarding.image_url ?? null,
        packageId:       null,
        packageType:     null,
        packageLabel:    null,
        durationMonths:  null,
        services:        null,
        startDate:       null,
        endDate:         null,
        availStatus:     'unchecked',
        conflictLabel:   null,
    });
    offerRender();
    // Open date picker immediately
    setTimeout(() => offerOpenDateModal(hoarding.id), 150);
};

window.offerRemoveHoarding = function(hoardingId) {
    offerItems.delete(hoardingId);
    offerRender();
    // Deselect in inventory panel
    document.querySelector(`.offer-inv-card[data-id="${hoardingId}"]`)?.classList.remove('is-selected');
};

/* ──────────────────────────────────────────────
   RENDER TABLES
────────────────────────────────────────────── */
function offerRender() {
    const oohBody  = document.getElementById('offerOohList');
    const doohBody = document.getElementById('offerDoohList');
    let hasOoh = false, hasDooh = false, grandTotal = 0, itemCount = 0;

    oohBody.innerHTML  = '';
    doohBody.innerHTML = '';

    offerItems.forEach((item, id) => {
        const isDooh    = item.hoardingType === 'dooh';
        const hasDates  = Boolean(item.startDate && item.endDate);
        const months    = hasDates ? offerMonths(item.startDate, item.endDate) : (item.durationMonths ?? 0);
        const total     = hasDates ? item.pricePerMonth * months : 0;
        const rng       = hasDates ? offerRange(item.startDate, item.endDate) : null;

        if (hasDates) { grandTotal += total; itemCount++; }

        const availCell = _offerAvailCell(item);
        const durCell   = _offerDurCell(item, rng, id);
        const rmBtn     = `<button onclick="offerRemoveHoarding(${id})" title="Remove"
            class="text-red-400 hover:text-red-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg></button>`;

        const conflictRowCls = item.availStatus === 'conflict' ? 'bg-red-50' : '';

        if (!isDooh) {
            hasOoh = true;
            oohBody.insertAdjacentHTML('beforeend', `
            <tr class="hover:bg-gray-50 border-b border-gray-100 ${conflictRowCls}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        ${item.imageUrl ? `<img src="${item.imageUrl}" class="w-9 h-9 rounded object-cover border border-gray-100 flex-shrink-0" onerror="this.remove()">` : ''}
                        <div>
                            <p class="text-xs font-bold text-gray-800">${item.title}</p>
                            ${item.displayLocation ? `<p class="text-[9px] text-gray-400 truncate max-w-[160px]">${item.displayLocation}</p>` : ''}
                            ${item.packageLabel ? `<span class="text-[9px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-semibold">${item.packageLabel}</span>` : ''}
                        </div>
                    </div>
                </td>
                <td class="px-3 py-3 text-xs text-gray-600 font-semibold">${offerFmt(item.pricePerMonth)}</td>
                <td class="px-3 py-3 text-xs text-gray-500">${item.packageLabel ?? '—'}</td>
                <td class="px-4 py-3">${durCell}</td>
                <td class="px-3 py-3">${availCell}</td>
                <td class="px-3 py-3 text-xs font-bold text-right text-green-700">${hasDates ? offerFmt(total) : '—'}</td>
                <td class="px-3 py-3 text-right">${rmBtn}</td>
            </tr>`);
        } else {
            hasDooh = true;
            doohBody.insertAdjacentHTML('beforeend', `
            <tr class="hover:bg-gray-50 border-b border-gray-100 ${conflictRowCls}">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        ${item.imageUrl ? `<img src="${item.imageUrl}" class="w-9 h-9 rounded object-cover border border-gray-100 flex-shrink-0" onerror="this.remove()">` : ''}
                        <div>
                            <p class="text-xs font-bold text-gray-800">${item.title}</p>
                            ${item.displayLocation ? `<p class="text-[9px] text-gray-400 truncate max-w-[160px]">${item.displayLocation}</p>` : ''}
                        </div>
                    </div>
                </td>
                <td class="px-3 py-3 text-xs text-gray-600 font-semibold">${offerFmt(item.pricePerMonth)}</td>
                <td class="px-3 py-3 text-center">
                    <span class="bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full">${item.slotsPerDay}</span>
                </td>
                <td class="px-4 py-3">${durCell}</td>
                <td class="px-3 py-3">${availCell}</td>
                <td class="px-3 py-3 text-xs font-bold text-right text-green-700">${hasDates ? offerFmt(total) : '—'}</td>
                <td class="px-3 py-3 text-right">${rmBtn}</td>
            </tr>`);
        }
    });

    if (!hasOoh)  oohBody.innerHTML  = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-300 italic text-xs">No static hoardings added</td></tr>`;
    if (!hasDooh) doohBody.innerHTML = `<tr><td colspan="7" class="px-4 py-10 text-center text-gray-300 italic text-xs">No digital screens added</td></tr>`;

    document.getElementById('offerGrandTotal').innerText = offerFmt(grandTotal);
    document.getElementById('offerItemCount').innerText  = `${itemCount} item${itemCount !== 1 ? 's' : ''}`;
}

function _offerAvailCell(item) {
    switch (item.availStatus) {
        case 'available':
            return `<span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[9px] font-bold px-2 py-0.5 rounded-full">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Available</span>`;
        case 'conflict':
            return `<span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-[9px] font-bold px-2 py-0.5 rounded-full" title="${item.conflictLabel ?? ''}">
                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                Conflict</span>`;
        case 'checking':
            return `<span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-[9px] font-bold px-2 py-0.5 rounded-full">
                <svg class="w-2.5 h-2.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                Checking…</span>`;
        default:
            return `<span class="text-[9px] text-gray-400 font-semibold">—</span>`;
    }
}

function _offerDurCell(item, rng, id) {
    if (!item.startDate || !item.endDate) {
        return `<button onclick="offerOpenDateModal(${id})"
            class="text-xs font-semibold text-orange-600 hover:text-orange-800 whitespace-nowrap">
            Select dates →</button>`;
    }
    return `<button onclick="offerOpenDateModal(${id})" class="text-left hover:opacity-75 transition">
        <span class="block text-xs font-semibold text-gray-700">${rng.full}</span>
        <span class="block text-[9px] text-emerald-600 font-bold">${rng.badge} · tap to change</span>
    </button>`;
}

/* ──────────────────────────────────────────────
   AVAILABILITY CHECK
────────────────────────────────────────────── */
async function offerCheckAll() {
    // Mark all as checking
    offerItems.forEach((item) => {
        if (item.startDate && item.endDate) item.availStatus = 'checking';
    });
    offerRender();

    const results = await Promise.allSettled(
        Array.from(offerItems.entries())
            .filter(([, item]) => item.startDate && item.endDate)
            .map(([id, item]) => _offerCheckOne(id, item))
    );

    offerRender();

    // Show alert if any conflicts
    const conflicts = Array.from(offerItems.values()).filter(i => i.availStatus === 'conflict');
    if (conflicts.length) {
        _offerShowConflictAlert(conflicts);
    } else {
        document.getElementById('offerAvailAlert').classList.add('hidden');
    }
}

async function _offerCheckOne(hoardingId, item) {
    try {
        const allDates  = offerEnumDates(item.startDate, item.endDate);
        const chunkSize = 60;
        let   allConflicts = [];

        for (let i = 0; i < allDates.length; i += chunkSize) {
            const res = await fetch(`/api/v1/hoardings/${hoardingId}/availability/check-dates`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': OFFER_CSRF,
                },
                body: JSON.stringify({ dates: allDates.slice(i, i + chunkSize) }),
            });
            if (!res.ok) continue;
            const data = await res.json();
            const conflicts = (data.data?.results ?? []).filter(r =>
                r.status === 'booked' || r.status === 'hold' || r.status === 'partial'
            );
            allConflicts = allConflicts.concat(conflicts);
        }

        if (allConflicts.length) {
            const labels = { booked:'Already Booked', hold:'On Hold', partial:'Partially Unavailable' };
            const statuses = [...new Set(allConflicts.map(c => c.status))];
            item.availStatus   = 'conflict';
            item.conflictLabel = statuses.map(s => labels[s] ?? s).join(', ');
        } else {
            item.availStatus   = 'available';
            item.conflictLabel = null;
        }
    } catch (e) {
        console.error('Availability check error for hoarding', hoardingId, e);
        item.availStatus = 'unchecked';
    }
}

function _offerShowConflictAlert(conflicts) {
    const body = document.getElementById('offerAvailAlertBody');
    body.innerHTML = conflicts.map(item => `
        <div class="flex items-center gap-2">
            <span class="font-bold text-red-700">${item.title}:</span>
            <span>${item.conflictLabel ?? 'Conflict detected'}</span>
            <button onclick="offerOpenDateModal(${item.hoardingId})"
                class="ml-1 underline font-bold text-red-700 hover:text-red-900 whitespace-nowrap">
                Change dates →</button>
        </div>`).join('');
    document.getElementById('offerAvailAlert').classList.remove('hidden');
}

/* ──────────────────────────────────────────────
   DATE PICKER MODAL
────────────────────────────────────────────── */
async function offerOpenDateModal(hoardingId) {
    if (typeof flatpickr === 'undefined') {
        offerToast('Calendar library not loaded.', 'error');
        return;
    }

    offerEditingId = hoardingId;
    const item = offerItems.get(hoardingId);
    if (!item) return;

    offerDpStart = item.startDate ?? null;

    // Set title
    const title = item.title?.length > 50 ? item.title.slice(0, 50) + '…' : item.title;
    document.getElementById('offerDateModalTitle').innerText = title ?? 'Select Campaign Duration';

    // Show modal
    const modal = document.getElementById('offerDateModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.getElementById('offerFpContainer').innerHTML =
        '<div class="text-center py-8 text-sm text-gray-400 animate-pulse">Loading availability calendar…</div>';

    _offerUpdateDpBar(item.startDate, item.endDate, item.pricePerMonth);

    // Fetch heatmap
    const today  = offerYMD(new Date());
    const future = new Date(); future.setDate(future.getDate() + 730);

    try {
        const res = await fetch(
            `/api/v1/hoardings/${hoardingId}/availability/heatmap?start_date=${today}&end_date=${offerYMD(future)}`,
            { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        const payload = await res.json();
        const heatmap = payload.data?.heatmap ?? [];

        offerHeatmap = {};
        const disabledDates = [];
        heatmap.forEach(d => {
            offerHeatmap[d.date] = d.status;
            if (d.status && !['available', 'blocked'].includes(d.status)) {
                disabledDates.push(d.date);
            }
        });

        // Destroy existing
        if (offerFp) { offerFp.destroy(); offerFp = null; }
        document.getElementById('offerFpContainer').innerHTML = '';

        const defaultDate = item.startDate
            ? (item.endDate ? [item.startDate, item.endDate] : [item.startDate])
            : [];

        offerFp = flatpickr('#offerFpInput', {
            mode:       'range',
            inline:     true,
            appendTo:   document.getElementById('offerFpContainer'),
            minDate:    today,
            disable:    disabledDates,
            defaultDate,
            showMonths: window.innerWidth < 640 ? 1 : 2,

            onDayCreate(_, __, _fp, dayElem) {
                const dt     = offerYMD(dayElem.dateObj);
                const status = offerHeatmap[dt];
                dayElem.classList.remove('avail-day','day-booked','day-blocked','day-hold','day-partial');
                if (!status || status === 'available') { dayElem.classList.add('avail-day'); dayElem.title = 'Available'; }
                else if (status === 'booked')  { dayElem.classList.add('day-booked');  dayElem.title = 'Booked'; }
                else if (status === 'blocked') { dayElem.classList.add('day-blocked'); dayElem.title = 'Blocked'; }
                else if (status === 'hold')    { dayElem.classList.add('day-hold');    dayElem.title = 'On Hold'; }
                else if (status === 'partial') { dayElem.classList.add('day-partial'); dayElem.title = 'Partially booked'; }
            },

            onChange(selectedDates) {
                if (!selectedDates.length) return;
                const start = offerYMD(selectedDates[0]);
                offerDpStart = start;
                if (selectedDates.length < 2) { _offerUpdateDpBar(start, null, item.pricePerMonth); return; }
                const rawEnd    = offerYMD(selectedDates[1]);
                const snappedEnd = rawEnd === start ? offerEndForMonths(start, 1) : offerSnapEnd(start, rawEnd);
                _offerUpdateDpBar(start, snappedEnd, item.pricePerMonth);
                if (snappedEnd !== rawEnd) {
                    setTimeout(() => offerFp?.setDate([start, snappedEnd], false), 0);
                }
            },
        });

    } catch (e) {
        console.error(e);
        offerToast('Could not load availability. Try again.', 'error');
        offerCloseDateModal();
    }
}

function offerCloseDateModal() {
    document.getElementById('offerDateModal').classList.add('hidden');
    document.getElementById('offerDateModal').classList.remove('flex');
    document.getElementById('offerFpContainer').innerHTML = '';
    if (offerFp) { offerFp.destroy(); offerFp = null; }
    offerEditingId = null;
    offerDpStart   = null;
}

function offerApplyDates() {
    if (!offerFp || !offerEditingId) { offerCloseDateModal(); return; }

    const dates = offerFp.selectedDates;
    if (!dates?.length) { offerToast('Please pick a start date.', 'warning'); return; }

    const startISO = offerYMD(dates[0]);
    const rawEnd   = dates.length >= 2 ? offerYMD(dates[1]) : startISO;
    const endISO   = rawEnd === startISO ? offerEndForMonths(startISO, 1) : offerSnapEnd(startISO, rawEnd);

    // Validate against loaded heatmap (client-side fast check)
    const conflicts = offerEnumDates(startISO, endISO).filter(dt => {
        const s = offerHeatmap[dt];
        return s === 'booked' || s === 'hold' || s === 'partial';
    });

    if (conflicts.length) {
        offerToast('Selected range includes unavailable dates. Please pick a different period.', 'warning');
        return;
    }

    const item = offerItems.get(offerEditingId);
    if (item) {
        item.startDate     = startISO;
        item.endDate       = endISO;
        item.durationMonths = offerMonths(startISO, endISO);
        item.availStatus   = 'available';
        item.conflictLabel = null;
        document.getElementById('offerAvailAlert').classList.add('hidden');
    }

    offerCloseDateModal();
    offerRender();
    offerToast(`Dates confirmed: ${offerRange(startISO, endISO).full}`, 'success');
}

function offerQuickMonths(n) {
    if (!offerFp) return;
    const start = offerDpStart ?? offerYMD(new Date());
    const end   = offerEndForMonths(start, n);
    offerFp.setDate([start, end], false);
    const item = offerItems.get(offerEditingId);
    _offerUpdateDpBar(start, end, item?.pricePerMonth);
}

function _offerUpdateDpBar(s, e, ppm) {
    const rangeEl  = document.getElementById('offerDpRange');
    const monthsEl = document.getElementById('offerDpMonths');
    const costEl   = document.getElementById('offerDpCost');

    if (!s) {
        if (rangeEl)  rangeEl.innerText  = '— select start';
        if (monthsEl) monthsEl.innerText = '—';
        if (costEl)   costEl.innerText   = '—';
        _offerSetChip(null);
        return;
    }
    const r = offerRange(s, e ?? s);
    if (rangeEl)  rangeEl.innerHTML  = `${r.start}&nbsp;–&nbsp;${e ? r.end : '…'}`;
    if (monthsEl) monthsEl.innerText = e ? r.badge : '—';
    if (costEl)   costEl.innerText   = (e && ppm) ? offerFmt(ppm * r.months) : '—';
    _offerSetChip(e ? r.months : null);
}

function _offerSetChip(months) {
    document.querySelectorAll('.offer-quick-chip').forEach(btn => {
        const active = months !== null && parseInt(btn.dataset.months) === months;
        btn.classList.toggle('border-emerald-500', active);
        btn.classList.toggle('text-emerald-700',   active);
        btn.classList.toggle('bg-emerald-50',      active);
    });
}

/* ──────────────────────────────────────────────
   SUBMIT
────────────────────────────────────────────── */
async function offerSubmit(action) {
    // Validate: at least one item
    if (offerItems.size === 0) {
        offerToast('Add at least one hoarding to the offer.', 'warning');
        return;
    }

    // Validate: all items have dates
    const missingDates = Array.from(offerItems.values()).filter(i => !i.startDate || !i.endDate);
    if (missingDates.length) {
        offerToast(`Please select campaign dates for: ${missingDates[0].title}`, 'warning');
        offerOpenDateModal(missingDates[0].hoardingId);
        return;
    }

    // Validate: no conflicts
    const conflicts = Array.from(offerItems.values()).filter(i => i.availStatus === 'conflict');
    if (conflicts.length) {
        offerToast('Resolve availability conflicts before submitting.', 'error');
        _offerShowConflictAlert(conflicts);
        return;
    }

    // Check unchecked items first
    const unchecked = Array.from(offerItems.values()).filter(i => i.availStatus === 'unchecked');
    if (unchecked.length) {
        offerToast('Checking availability…', 'info');
        await offerCheckAll();
        const stillConflicts = Array.from(offerItems.values()).filter(i => i.availStatus === 'conflict');
        if (stillConflicts.length) {
            offerToast('Availability conflicts found. Please fix dates.', 'error');
            return;
        }
    }

    // Build payload
    const items = Array.from(offerItems.values()).map(item => ({
        enquiry_item_id:       item.enquiryItemId,
        hoarding_id:           item.hoardingId,
        hoarding_type:         item.hoardingType,
        package_id:            item.packageId,
        package_type:          item.packageType,
        package_label:         item.packageLabel,
        preferred_start_date:  item.startDate,
        preferred_end_date:    item.endDate,
        duration_months:       item.durationMonths ?? offerMonths(item.startDate, item.endDate),
        services:              item.services,
    }));

    const payload = {
        enquiry_id:  OFFER_ENQUIRY_ID,
        description: document.getElementById('offerDescription')?.value?.trim() || null,
        valid_days:  30,   // default validity; can be made configurable
        items,
    };

    const draftBtnLabel = 'Save as Draft';
    const sendBtnLabel  = 'Save &amp; Send Offer';

    offerSetBtnLoading('offerSaveDraftBtn', true, 'Saving…');
    offerSetBtnLoading('offerSendBtn',      true, 'Saving…');

    try {
        // Step 1: Create/store offer
        const storeRes = await fetch(OFFER_STORE_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': OFFER_CSRF,
            },
            body: JSON.stringify(payload),
        });

        const storeData = await storeRes.json();

        if (!storeRes.ok || !storeData.success) {
            throw new Error(storeData.message ?? 'Failed to save offer.');
        }

        // Step 2: If 'send', send the offer
        if (action === 'send') {
            const sendRes = await fetch(`/vendor/offers/${storeData.offer_id}/send`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': OFFER_CSRF,
                },
            });
            const sendData = await sendRes.json();
            if (!sendRes.ok || !sendData.success) {
                throw new Error(sendData.message ?? 'Offer saved but could not be sent.');
            }
            offerToast('Offer sent to customer!', 'success');
        } else {
            offerToast('Draft saved successfully.', 'success');
        }

        // Redirect to offer show page
        setTimeout(() => {
            window.location.href = storeData.redirect;
        }, 800);

    } catch (e) {
        offerToast(e.message, 'error');
        offerSetBtnLoading('offerSaveDraftBtn', false, draftBtnLabel);
        offerSetBtnLoading('offerSendBtn',      false, sendBtnLabel);
    }
}
</script>

{{-- ── Flatpickr day colour styles (same palette as POS) ── --}}
<style>
.flatpickr-day.avail-day       { background:#dcfce7!important; border-color:#86efac!important; color:#14532d!important; }
.flatpickr-day.day-booked,
.flatpickr-day.day-partial     { background:#fee2e2!important; color:#991b1b!important; border-color:#fca5a5!important; cursor:not-allowed!important; text-decoration:line-through; pointer-events:none; }
.flatpickr-day.day-blocked     { background:#f3f4f6!important; color:#9ca3af!important; border-color:#e5e7eb!important; cursor:not-allowed!important; pointer-events:none; }
.flatpickr-day.day-hold        { background:#fef9c3!important; color:#78350f!important; border-color:#fde047!important; cursor:not-allowed!important; pointer-events:none; }
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange        { background:#2D5A43!important; border-color:#2D5A43!important; color:#fff!important; }
.flatpickr-day.inRange         { background:#e5e7eb!important; border-color:#d1d5db!important; box-shadow:none!important; }
#offerFpContainer .flatpickr-calendar.inline { width:100%!important; max-width:100%!important; box-shadow:none!important; }
</style>