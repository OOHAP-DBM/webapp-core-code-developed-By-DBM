{{--
╔══════════════════════════════════════════════════════════════════════════╗
║  HOARDING SELECTOR COMPONENT                                             ║
║                                                                          ║
║  Usage:                                                                  ║
║  <x-hoarding-selector                                                    ║
║      :api-url="'/vendor/pos/api'"                                        ║
║      :csrf-token="csrf_token()"                                          ║
║      title="Select Hoardings for Booking"                                ║
║      subtitle="Browse and select hoardings to add them to the booking."  ║
║      :show-filter-modal="true"                                           ║
║      :show-availability-check="true"                                     ║
║      on-change="myCallbackFunction"                                      ║
║  />                                                                      ║
╚══════════════════════════════════════════════════════════════════════════╝
--}}

@props([
    'apiUrl'               => '/vendor/pos/api',
    'csrfToken'            => '',
    'title'                => 'Select Hoardings',
    'subtitle'             => 'Browse and select hoardings.',
    'showFilterModal'      => true,
    'showAvailabilityCheck'=> true,
    'onChange'             => null,   {{-- JS callback name when selection changes --}}
    'componentId'          => 'hs',   {{-- unique prefix if used multiple times on same page --}}
])

<div class="bg-white rounded-lg shadow-sm border border-gray-200" id="{{ $componentId }}-panel">

    {{-- Header --}}
    <div class="px-3 sm:px-4 lg:px-5 pt-4 sm:pt-5 gap-3 flex items-center">
        <h3 class="font-bold text-gray-800">{{ $title }}</h3>
        <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full text-xs font-bold"
              id="{{ $componentId }}-available-count">0</span>
    </div>
    <p class="px-3 sm:px-4 lg:px-5 text-xs text-gray-400 mt-0.5 mb-3">{{ $subtitle }}</p>

    <div class="px-3 sm:px-4 lg:px-5 pb-4 space-y-3">

        {{-- Search + Filter row --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
            <div class="relative flex-1">
                <input type="text"
                       id="{{ $componentId }}-search"
                       placeholder="Search by location, size, or name"
                       class="w-full pl-10 border border-gray-300 text-sm focus:ring-green-500 min-h-[44px]">
                <span class="absolute left-3 top-0 bottom-0 my-auto text-gray-400 flex items-center pointer-events-none"
                      style="height:18px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/>
                    </svg>
                </span>
            </div>

            @if($showFilterModal)
            <button type="button"
                    class="w-full sm:w-auto min-h-[44px] border border-gray-300 bg-white px-4 py-2 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
                    onclick="{{ $componentId }}Panel.openFilterModal()">
                Advanced Filters
            </button>
            @endif
        </div>

        {{-- Grid/List Toggle + Unselect All --}}
        <div class="flex items-center justify-end gap-2">
            <button id="{{ $componentId }}-unselect-btn"
                    onclick="{{ $componentId }}Panel.unselectAll()"
                    class="hidden text-[11px] font-bold text-gray-700 border border-gray-300 bg-white hover:bg-gray-100 px-3 py-1 transition whitespace-nowrap">
                Unselect All
            </button>
            <div class="flex border border-gray-300">
                <button onclick="{{ $componentId }}Panel.setViewMode('grid')"
                        id="{{ $componentId }}-view-grid-btn"
                        class="px-2 py-1 bg-gray-800 text-white" title="Grid View">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1 2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V2zM1 7a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V7zM1 12a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"/>
                    </svg>
                </button>
                <button onclick="{{ $componentId }}Panel.setViewMode('list')"
                        id="{{ $componentId }}-view-list-btn"
                        class="px-2 py-1 bg-white text-gray-600 hover:bg-gray-100" title="List View">
                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Active Filter Tags --}}
        <div id="{{ $componentId }}-filter-tags" class="hidden">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[10px] text-gray-500 font-semibold mr-1">Results showing for:</span>
                <div id="{{ $componentId }}-filter-tags-list" class="flex flex-wrap gap-1.5 flex-1"></div>
                <button onclick="{{ $componentId }}Panel.clearAllFilters()"
                        class="text-[10px] text-red-500 font-semibold hover:underline whitespace-nowrap ml-1">
                    Clear all
                </button>
            </div>
        </div>

        {{-- Hoardings Grid --}}
        <div id="{{ $componentId }}-grid"
             class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[calc(100vh-250px)] overflow-y-auto pr-1 custom-scrollbar">
        </div>

        {{-- Pagination --}}
        <div id="{{ $componentId }}-pagination" class="flex justify-center items-center gap-2 mt-2"></div>

    </div>
</div>

{{-- Date Picker Modal --}}
<div id="{{ $componentId }}-date-modal"
     class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-black/50 absolute inset-0"
         onclick="{{ $componentId }}Panel.closeDatePicker()"></div>
    <div class="relative bg-white rounded-lg p-3 sm:p-4 w-[95vw] sm:w-full sm:max-w-[700px] z-50 flex flex-col min-h-[360px] sm:min-h-[420px]">
        <div class="flex justify-between items-center mb-4 gap-2">
            <h3 id="{{ $componentId }}-date-modal-title" class="font-bold text-gray-800">Select Dates</h3>
            <button class="text-gray-500" onclick="{{ $componentId }}Panel.closeDatePicker()">✕</button>
        </div>
        <input id="{{ $componentId }}-date-input" type="text" class="hidden">
        <div id="{{ $componentId }}-date-inline" class="mx-auto w-full overflow-x-auto"></div>
        <div class="mt-4 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
            <button class="w-full sm:w-auto min-h-[44px] px-4 py-2 border rounded"
                    onclick="{{ $componentId }}Panel.closeDatePicker()">Cancel</button>
            <button class="w-full sm:w-auto min-h-[44px] px-4 py-2 bg-[#2D5A43] text-white rounded"
                    onclick="{{ $componentId }}Panel.confirmDateSelection()">Confirm</button>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar       { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }

.flatpickr-day.booked  { background:#ef4444!important;color:#fff!important;border-color:#ef4444!important; }
.flatpickr-day.blocked { background:#6b7280!important;color:#fff!important;border-color:#6b7280!important; }
.flatpickr-day.hold    { background:#f59e0b!important;color:#fff!important;border-color:#f59e0b!important; }
.flatpickr-day.partial { background:#f97316!important;color:#fff!important;border-color:#f97316!important; }

#{{ $componentId }}-grid.list-view               { grid-template-columns:1fr!important; }
#{{ $componentId }}-grid.list-view .hs-card      { display:flex;flex-direction:row;align-items:center; }
#{{ $componentId }}-grid.list-view .hs-card img  { width:64px;height:64px;flex-shrink:0; }
#{{ $componentId }}-grid.list-view .hs-card-body { flex:1; }
</style>

<script>
(function () {
    /* ── CONFIG ─────────────────────────────────────────────────────── */
    const ID         = @json($componentId);
    const API_URL    = @json($apiUrl);
    const CSRF       = @json($csrfToken ?: csrf_token());
    const ON_CHANGE  = @json($onChange);   // string name of global JS callback
    const CHECK_AVAIL= @json($showAvailabilityCheck);

    /* ── STATE ──────────────────────────────────────────────────────── */
    let hoardings          = [];
    let selectedHoardings  = new Map();   // id → hoarding object with startDate/endDate
    let availabilityIssues = {};
    let activeFilters      = {};
    let currentPage        = 1;
    let totalPages         = 1;
    const perPage          = 10;
    let viewMode           = 'grid';
    let fpInstance         = null;
    let heatmapMap         = {};
    let editingId          = null;

    /* ── HELPERS ─────────────────────────────────────────────────────── */
    const $     = id  => document.getElementById(id);
    const el    = suf => $(ID + '-' + suf);
    const fmt   = val => new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:0 }).format(val);
    const ymd   = d   => { const x = new Date(d); return `${x.getFullYear()}-${String(x.getMonth()+1).padStart(2,'0')}-${String(x.getDate()).padStart(2,'0')}`; };
    const months= (s,e)=> { if(!s||!e) return 0; return Math.ceil((Math.ceil(Math.abs(new Date(e)-new Date(s))/(864e5))+1)/30); };
    const price = (ppm,s,e) => ppm * months(s,e);
    const label = (s,e) => { const m=months(s,e); return m<=0?'0 Months':m===1?'1 Month':`${m} Months`; };

    const fetchJSON = async (url, opts={}) => {
        const res = await fetch(url, {
            headers: { 'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'X-Requested-With':'XMLHttpRequest' },
            ...opts
        });
        return res.json();
    };

    const debounce = (fn, t) => { let timer; return (...a) => { clearTimeout(timer); timer=setTimeout(()=>fn(...a),t); }; };

    function fireChange() {
        if (ON_CHANGE && typeof window[ON_CHANGE] === 'function') {
            window[ON_CHANGE](selectedHoardings, availabilityIssues);
        }
    }

    /* ── LOAD HOARDINGS ─────────────────────────────────────────────── */
    async function load(filters = {}) {
        activeFilters = { ...filters };
        const q = new URLSearchParams({ ...filters, page: currentPage, per_page: perPage }).toString();
        const res = await fetchJSON(`${API_URL}/hoardings?${q}`);

        if ('last_page' in res) {
            hoardings  = res.data || [];
            totalPages = res.last_page || 1;
            currentPage= res.current_page || 1;
        } else {
            hoardings  = res.data?.data || res.data || [];
            totalPages = res.data?.last_page || 1;
        }

        render();
        renderPagination();
        renderFilterTags();
    }

    /* ── RENDER CARDS ───────────────────────────────────────────────── */
    function render() {
        const grid = el('grid');
        el('available-count').innerText = hoardings.length;

        if (!hoardings.length) {
            grid.innerHTML = `
                <div class="col-span-2 flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-14 h-14 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 3.5a7.5 7.5 0 0013.15 13.15z"/>
                    </svg>
                    <p class="text-sm font-bold text-gray-500">No hoardings found</p>
                    <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filters</p>
                </div>`;
            return;
        }

        grid.innerHTML = hoardings.map(h => {
            const sel    = selectedHoardings.has(h.id);
            const isDooh = h.type?.toUpperCase() === 'DOOH';
            const border = sel ? 'border-green-500 ring-1 ring-green-500' : 'border-gray-200';

            if (viewMode === 'list') {
                return `
                    <div class="hs-card flex items-center gap-3 bg-white border ${border} p-2 cursor-pointer"
                         onclick="${ID}Panel.toggle(${h.id})">
                        <img src="${h.image_url || '/placeholder.png'}"
                             class="w-16 h-16 object-cover flex-shrink-0"
                             onerror="this.src='/placeholder.png'">
                        <div class="hs-card-body flex-1 min-w-0">
                            <p class="text-[11px] font-bold text-gray-800 truncate">${h.title}</p>
                            <p class="text-[10px] font-bold text-gray-600">${fmt(h.price_per_month)}/M</p>
                            ${isDooh ? `<p class="text-[9px] text-purple-600">${h.total_slots_per_day ?? 300} slots/day</p>` : ''}
                        </div>
                        ${isDooh ? `<span class="text-[9px] font-bold bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded self-start">DOOH</span>` : ''}
                        ${sel ? `
                            <button onclick="event.stopPropagation();${ID}Panel.toggle(${h.id})"
                                class="text-[10px] font-bold text-red-500 border border-red-300 bg-red-50 hover:bg-red-100 px-2 py-1 rounded whitespace-nowrap">
                                Unselect
                            </button>` : ''}
                    </div>`;
            }

            // Grid view
            return `
                <div class="hs-card relative bg-white border ${border} overflow-hidden cursor-pointer"
                     onclick="${ID}Panel.toggle(${h.id})">
                    <img src="${h.image_url || '/placeholder.png'}"
                         class="w-full h-20 object-cover"
                         onerror="this.src='/placeholder.png'">
                    ${isDooh ? `<span class="absolute top-1 right-1 bg-purple-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">DOOH</span>` : ''}
                    ${sel ? `<span class="absolute top-1 left-1 bg-green-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">✓</span>` : ''}
                    <div class="p-2">
                        <p class="text-[10px] font-bold text-gray-800 truncate" title="${h.title}">${h.title}</p>
                        <p class="text-[10px] font-bold text-gray-700">${fmt(h.price_per_month)}/M</p>
                        ${isDooh ? `<p class="text-[9px] text-purple-600">${h.total_slots_per_day ?? 300} slots/day</p>` : ''}
                    </div>
                    ${sel ? `
                        <button onclick="event.stopPropagation();${ID}Panel.toggle(${h.id})"
                            class="w-full bg-red-50 hover:bg-red-100 text-red-500 text-[10px] font-bold py-1 border-t border-red-100">
                            Unselect
                        </button>` : ''}
                </div>`;
        }).join('');
    }

    /* ── PAGINATION ─────────────────────────────────────────────────── */
    function renderPagination() {
        const box = el('pagination');
        if (totalPages <= 1) { box.innerHTML = ''; return; }

        let html = `<button class="px-2 py-1 border rounded text-xs ${currentPage===1?'opacity-40 cursor-not-allowed':''}"
                        onclick="${ID}Panel.changePage(${currentPage-1})" ${currentPage===1?'disabled':''}>Prev</button>`;

        let start = Math.max(1, currentPage-2), end = Math.min(totalPages, start+4);
        if (end-start<4) start = Math.max(1, end-4);
        for (let i=start; i<=end; i++) {
            html += `<button class="px-2 py-1 border rounded text-xs ${i===currentPage?'bg-green-600 text-white':''}"
                         onclick="${ID}Panel.changePage(${i})">${i}</button>`;
        }

        html += `<button class="px-2 py-1 border rounded text-xs ${currentPage===totalPages?'opacity-40 cursor-not-allowed':''}"
                     onclick="${ID}Panel.changePage(${currentPage+1})" ${currentPage===totalPages?'disabled':''}>Next</button>`;
        box.innerHTML = html;
    }

    /* ── FILTER TAGS ─────────────────────────────────────────────────── */
    const filterLabelMap = {
        type:         v => v && v!=='ALL' ? v.toUpperCase() : null,
        category:     v => v ? v.split(',').map(c=>c.replace(/_/g,' ').replace(/\b\w/g,l=>l.toUpperCase())).join(', ') : null,
        availability: v => v ? v.split(',').map(a=>a==='available'?'Available':'Booked').join(', ') : null,
        city:         v => v || null,
    };

    function renderFilterTags() {
        const wrap = el('filter-tags');
        const list = el('filter-tags-list');
        list.innerHTML = '';
        const tags = [];

        Object.entries(activeFilters).forEach(([k,v]) => {
            if (!v || v==='' || v==='ALL' || k==='page' || k==='per_page') return;
            const fn = filterLabelMap[k];
            const lb = fn ? fn(v) : v;
            if (lb) tags.push({ k, lb });
        });

        if (activeFilters.price_min || activeFilters.price_max) {
            tags.push({ k:'price', lb:`₹${activeFilters.price_min??0} – ₹${activeFilters.price_max??'∞'}` });
        }

        tags.forEach(({ k, lb }) => {
            const span = document.createElement('span');
            span.className = 'inline-flex items-center gap-1 bg-gray-100 border border-gray-200 text-gray-700 text-[10px] font-medium px-2 py-0.5 rounded';
            span.innerHTML = `${lb} <button onclick="${ID}Panel.removeTag('${k}')" class="text-gray-400 hover:text-red-500 font-bold ml-0.5">✕</button>`;
            list.appendChild(span);
        });

        wrap.classList.toggle('hidden', tags.length === 0);
        updateUnselectBtn();
    }

    /* ── UNSELECT BTN ───────────────────────────────────────────────── */
    function updateUnselectBtn() {
        const btn = el('unselect-btn');
        if (!btn) return;
        btn.classList.toggle('hidden', selectedHoardings.size === 0);
    }

    /* ── TOGGLE HOARDING ────────────────────────────────────────────── */
    function toggle(id) {
        if (selectedHoardings.has(id)) {
            selectedHoardings.delete(id);
            delete availabilityIssues[id];
        } else {
            const h     = hoardings.find(x => x.id === id);
            if (!h) return;
            const today = ymd(new Date());
            const end   = new Date(); end.setDate(end.getDate()+29);
            selectedHoardings.set(id, { ...h, startDate: today, endDate: ymd(end) });
        }
        render();
        updateUnselectBtn();
        fireChange();
    }

    /* ── DATE PICKER ────────────────────────────────────────────────── */
    async function openDatePicker(id) {
        if (typeof flatpickr === 'undefined') { alert('Calendar not loaded'); return; }
        editingId = id;
        const h   = selectedHoardings.get(id);
        if (!h) return;

        el('date-modal-title').innerText = h.title;
        el('date-modal').classList.remove('hidden');

        const startStr = ymd(new Date());
        const future   = new Date(); future.setDate(future.getDate()+365);

        try {
            const res      = await fetch(`/api/v1/hoardings/${id}/availability/heatmap?start_date=${startStr}&end_date=${ymd(future)}`,
                                         { credentials:'same-origin', headers:{'Accept':'application/json'} });
            const payload  = await res.json();
            const heatmap  = payload.data?.heatmap || [];
            const disabled = heatmap.filter(d => d.status!=='available').map(d => d.date);
            heatmapMap     = {};
            heatmap.forEach(d => heatmapMap[d.date] = d.status);

            if (fpInstance) fpInstance.destroy();
            fpInstance = flatpickr(`#${ID}-date-input`, {
                mode:'range', inline:true,
                appendTo: el('date-inline'),
                minDate: startStr,
                disable: disabled,
                defaultDate: [h.startDate, h.endDate],
                showMonths: window.innerWidth < 640 ? 1 : 2,
                onDayCreate(_, __, ___, dayEl) {
                    const date   = ymd(dayEl.dateObj);
                    const status = heatmapMap[date];
                    if (status && status !== 'available') {
                        dayEl.classList.add(status);
                        dayEl.title = status.charAt(0).toUpperCase() + status.slice(1);
                    }
                }
            });
        } catch(e) { console.error(e); }
    }

    function closeDatePicker() {
        el('date-modal').classList.add('hidden');
    }

    async function confirmDateSelection() {
        if (!fpInstance || !editingId) { closeDatePicker(); return; }
        const dates = fpInstance.selectedDates;
        if (!dates || !dates.length) { alert('Please select a date range'); return; }

        const start = ymd(dates[0]);
        const end   = ymd(dates.length === 1 ? dates[0] : dates[1]);

        if (CHECK_AVAIL) {
            // enumerate dates and check
            const allDates = [];
            let cur = new Date(start); const last = new Date(end);
            while (cur <= last) { allDates.push(ymd(cur)); cur.setDate(cur.getDate()+1); }

            try {
                const res = await fetch(`/api/v1/hoardings/${editingId}/availability/check-dates`, {
                    method:'POST', credentials:'same-origin',
                    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
                    body: JSON.stringify({ dates: allDates })
                });
                const result    = await res.json();
                const conflicts = (result.data?.results || []).filter(r => r.status !== 'available');
                if (conflicts.length > 0) {
                    alert('Selected range includes unavailable dates. Please choose different dates.');
                    openDatePicker(editingId);
                    return;
                }
            } catch(e) { console.error(e); }
        }

        const h = selectedHoardings.get(editingId);
        if (h) {
            h.startDate = start; h.endDate = end;
            selectedHoardings.set(editingId, h);
            delete availabilityIssues[editingId];
        }

        closeDatePicker();
        render();
        fireChange();
    }

    /* ── VIEW MODE ──────────────────────────────────────────────────── */
    function setViewMode(mode) {
        viewMode = mode;
        const grid    = el('grid');
        const btnGrid = el('view-grid-btn');
        const btnList = el('view-list-btn');

        if (mode === 'grid') {
            grid.classList.remove('list-view');
            btnGrid.className = 'px-2 py-1 bg-gray-800 text-white';
            btnList.className = 'px-2 py-1 bg-white text-gray-600 hover:bg-gray-100';
        } else {
            grid.classList.add('list-view');
            btnList.className = 'px-2 py-1 bg-gray-800 text-white';
            btnGrid.className = 'px-2 py-1 bg-white text-gray-600 hover:bg-gray-100';
        }
        render();
    }

    /* ── PUBLIC API ─────────────────────────────────────────────────── */
    window[ID + 'Panel'] = {
        // Hoarding selection
        toggle,
        unselectAll() {
            selectedHoardings.clear();
            availabilityIssues = {};
            render();
            updateUnselectBtn();
            fireChange();
        },

        // Dates
        openDatePicker,
        closeDatePicker,
        confirmDateSelection,

        // Filters
        clearAllFilters() {
            activeFilters = {};
            currentPage   = 1;
            load({});
        },
        removeTag(key) {
            if (key === 'price') { delete activeFilters.price_min; delete activeFilters.price_max; }
            else { delete activeFilters[key]; }
            currentPage = 1;
            load(activeFilters);
        },
        applyFilters(filters) {
            currentPage = 1;
            load(filters);
        },
        openFilterModal() {
            // Hook: implement in parent page if needed
            if (typeof openFilterModal === 'function') openFilterModal();
        },

        // Pagination
        changePage(p) {
            if (p < 1 || p > totalPages || p === currentPage) return;
            currentPage = p;
            load({ ...activeFilters });
        },

        // View
        setViewMode,

        // Data access
        getSelected()          { return selectedHoardings; },
        getAvailabilityIssues(){ return availabilityIssues; },
        setAvailabilityIssue(id, issue) { availabilityIssues[id] = issue; render(); },
        clearAvailabilityIssue(id)      { delete availabilityIssues[id]; render(); },

        // Reload
        reload(filters = {}) { load(filters); },
    };

    /* ── INIT ───────────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', () => {
        load();
        el('search')?.addEventListener('input', debounce(e => {
            currentPage = 1;
            const q = e.target.value.trim();
            load(q ? { ...activeFilters, search: q } : { ...activeFilters });
        }, 250));
    });

}());
</script>