{{-- offer-date-modal.blade.php --}}

<div id="offerDateModal" class="fixed inset-0 z-[9999] hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="offerCloseDateModal()"></div>
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
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-green-100 border border-green-300 inline-block"></span>Available</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-red-100 border border-red-300 inline-block"></span>Booked</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-gray-200 border border-gray-300 inline-block"></span>Blocked</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-sm bg-amber-100 border border-amber-300 inline-block"></span>On Hold</span>
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

<script>
/* ──────────────────────────────────────────────
   DATE PICKER LOGIC
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

    const title = item.title?.length > 50 ? item.title.slice(0, 50) + '…' : item.title;
    document.getElementById('offerDateModalTitle').innerText = title ?? 'Select Campaign Duration';

    const modal = document.getElementById('offerDateModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.getElementById('offerFpContainer').innerHTML =
        '<div class="text-center py-8 text-sm text-gray-400 animate-pulse">Loading availability calendar…</div>';

    _offerUpdateDpBar(item.startDate, item.endDate, item.pricePerMonth);

    const today  = offerYMD(new Date());
    const future = new Date(); future.setDate(future.getDate() + 730);

    try {
        const res = await fetch(
            `/api/v1/hoardings/${hoardingId}/availability/heatmap?start_date=${today}&end_date=${offerYMD(future)}`,
            { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }
        );

        let heatmap = [];
        if (res.ok) {
            const payload = await res.json();
            heatmap = payload.data?.heatmap ?? [];
        }

        offerHeatmap = {};
        const disabledDates = [];
        heatmap.forEach(d => {
            offerHeatmap[d.date] = d.status;
            if (d.status && !['available', 'blocked'].includes(d.status)) {
                disabledDates.push(d.date);
            }
        });

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
                const rawEnd     = offerYMD(selectedDates[1]);
                const snappedEnd = rawEnd === start ? offerEndForMonths(start, 1) : offerSnapEnd(start, rawEnd);
                _offerUpdateDpBar(start, snappedEnd, item.pricePerMonth);
                if (snappedEnd !== rawEnd) {
                    setTimeout(() => offerFp?.setDate([start, snappedEnd], false), 0);
                }
            },
        });

    } catch (e) {
        console.error(e);
        // Still show calendar without heatmap
        if (offerFp) { offerFp.destroy(); offerFp = null; }
        document.getElementById('offerFpContainer').innerHTML = '';
        offerFp = flatpickr('#offerFpInput', {
            mode: 'range', inline: true,
            appendTo: document.getElementById('offerFpContainer'),
            minDate: today,
            defaultDate: item.startDate ? (item.endDate ? [item.startDate, item.endDate] : [item.startDate]) : [],
            showMonths: window.innerWidth < 640 ? 1 : 2,
            onChange(selectedDates) {
                if (!selectedDates.length) return;
                const start = offerYMD(selectedDates[0]);
                offerDpStart = start;
                if (selectedDates.length < 2) { _offerUpdateDpBar(start, null, item.pricePerMonth); return; }
                const rawEnd = offerYMD(selectedDates[1]);
                const snappedEnd = rawEnd === start ? offerEndForMonths(start, 1) : offerSnapEnd(start, rawEnd);
                _offerUpdateDpBar(start, snappedEnd, item.pricePerMonth);
                if (snappedEnd !== rawEnd) setTimeout(() => offerFp?.setDate([start, snappedEnd], false), 0);
            },
        });
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

    // Client-side conflict check
    const conflicts = offerEnumDates(startISO, endISO).filter(dt => {
        const s = offerHeatmap[dt];
        return s === 'booked' || s === 'hold' || s === 'partial';
    });

    if (conflicts.length) {
        offerToast('Selected range includes unavailable dates.', 'warning');
        return;
    }

    const item = offerItems.get(offerEditingId);
    if (item) {
        item.startDate      = startISO;
        item.endDate        = endISO;
        item.durationMonths = offerMonths(startISO, endISO);
        item.availStatus    = 'available';
        item.conflictLabel  = null;
    }

    offerCloseDateModal();
    offerRender();
    offerToast(`Dates set: ${offerRange(startISO, endISO).full}`, 'success');
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
</script>