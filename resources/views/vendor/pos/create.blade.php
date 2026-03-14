@extends($posLayout ?? 'layouts.vendor')

@section('title', 'POS Customers')
@section('content')
<div class="sm:py-2 bg-gray-50">
    <div id="selection-screen" class="grid grid-cols-1 sm:grid-cols-5 lg:grid-cols-12 gap-4 sm:gap-5 lg:gap-6">
        
        <div class="order-2 sm:order-1 sm:col-span-3 lg:col-span-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-3 sm:px-4 lg:px-6 py-4 border-b border-gray-100 items-center gap-2 bg-white">
                    <h2 class="text-xl font-bold text-gray-800">Create New POS Booking</h2>
                    <p class="text-xs text-gray-400">Select a customer and choose hoardings to create a booking.</p>
                    <!-- <span id="booking-date" class="text-xs text-gray-400 font-medium"></span> -->
                </div>

                <div class="p-3 sm:p-4 lg:p-6">
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider ">Select Customer</label>
                        <p class="block text-xs text-gray-400 tracking-wider mb-2">Search an existing customer or add a new customer to proceed with booking.</p>
                        
                        <div id="search-container" class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1 border border-gray-300">
                                <input type="text" id="customer-search" autocomplete="off" 
                                    placeholder="Search customer by name, email, or mobile number" 
                                    class="w-full border-gray-300 focus:ring-green-500 text-sm py-2.5 px-2 min-h-[44px]">
                                <div id="customer-suggestions" class="absolute z-50 w-full bg-white border rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto"></div>
                            </div>
                            <button type="button" id="new-customer-btn" onclick="openCustomerModal()" class="w-full sm:w-auto min-h-[44px] bg-green-600 text-white px-4 hover:bg-green-700 transition flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span class="ml-1 text-sm font-semibold">Add New Customer</span>
                            </button>
                        </div>

                        <div id="customer-selected-card" class="hidden flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-green-50 border border-green-200 rounded-lg p-4 animate-fade-in">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#2D5A43] rounded-full flex items-center justify-center text-white font-bold text-sm" id="cust-initials">--</div>
                                <div>
                                    <h4 id="cust-name" class="font-bold text-gray-800 text-sm leading-tight">Customer Name</h4>
                                    <p id="cust-details" class="text-xs text-gray-500 mt-0.5">Contact Details</p>
                                </div>
                            </div>
                            <button id="change-customer-btn" onclick="clearSelectedCustomer()" class="w-full sm:w-auto text-xs font-bold text-red-500 hover:text-red-700 px-3 py-2 border border-red-200 rounded-md bg-white">Change</button>
                        </div>
                    </div>

                    {{-- Availability Issues Alert --}}
                    <div id="availability-alert" class="hidden mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-red-700 mb-2">Availability Conflicts Found</h4>
                                <div id="availability-alert-body" class="text-xs text-red-600 space-y-1"></div>
                            </div>
                            <button onclick="document.getElementById('availability-alert').classList.add('hidden')" class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="selection-group">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span> OOH (Static)
                            </h4>
                            <p class="text-xs text-gray-400 mb-2 px-3.5">Select traditional billboard hoardings for long-term display.</p>
                            <div class="overflow-x-auto border border-gray-100">
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
                                    <tbody id="ooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No static hoardings selected</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="selection-group">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> Digital Hoardings (DOOH)
                            </h4>
                            <p class="text-xs text-gray-400 mb-2 px-3.5">Select digital screens and configure slot bookings.</p>
                            <div class="overflow-x-auto border border-gray-100">
                                <table class="min-w-[700px] w-full divide-y divide-gray-200 text-left text-xs sm:text-sm">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th class="px-3 sm:px-4 py-3 font-semibold">Screen Location</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold hidden sm:table-cell">Slot Price</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold text-center hidden sm:table-cell">Slots Per Day</th>
                                            <th class="px-4 py-3 font-semibold text-center">Booking Duration</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold">Total Cost</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold text-right">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mt-12 pt-6 border-t border-gray-100">
                        <button type="button" onclick="location.reload()" class="w-full sm:flex-1 min-h-[44px] py-3 bg-[#7A9C89] border border-gray-200 font-bold text-white transition cursor-pointer">Cancel Booking</button>
                        <button id="submit-btn" class="w-full sm:flex-1 min-h-[44px] py-3 bg-[#2E5B42] text-white font-bold shadow-lg shadow-green-900/20 hover:bg-opacity-90 active:scale-[0.98] transition cursor-pointer">
                            Preview Booking & Confirm (<span id="btn-count">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             RIGHT PANEL — Available Hoardings
        ══════════════════════════════════════════ --}}
        <div class="order-1 sm:order-2 sm:col-span-2 lg:col-span-4">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:col-span-2 lg:sticky">
                <div class="px-3 sm:px-4 lg:px-5 pt-4 sm:pt-5 gap-3 flex">
                    <h3 class="font-bold text-gray-800">Select Hoardings for Booking</h3>
                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full text-xs font-bold" id="available-count">0</span>
                </div>
                <p class="px-3 sm:px-4 lg:px-5 text-xs text-gray-400 mt-0.5">Browse and select hoardings to add them to the booking.</p>

                <div class="p-3 sm:p-4 lg:p-5 ">
                    {{-- Search + Filter row --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                        <div class="relative flex-1">
                            <input type="text" id="hoarding-search" placeholder="Search hoardings by location, size, or name" 
                                class="w-full pl-10 border border-gray-300 text-sm focus:ring-green-500 min-h-[44px]" style="height:40px;">
                            <span class="absolute left-3 top-0 bottom-0 my-auto text-gray-400 flex items-center" style="pointer-events:none; height:18px;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/></svg>
                            </span>
                        </div>
                        <button type="button" class="w-full sm:w-auto min-h-[44px] border border-gray-300 bg-white px-5 py-2 text-gray-700 text-sm font-medium hover:bg-gray-100 transition" style="height:40px;" onclick="openFilterModal()">Advanced Filters</button>
                        @include('vendor.pos.filter_modal')
                    </div>

                    {{-- ── Grid/List Toggle + Unselect — ALWAYS VISIBLE ── --}}
                    <div class="flex items-center justify-end gap-2 mb-2">
                        <button
                            id="unselect-all-btn"
                            onclick="unselectAllHoardings()"
                            class="hidden text-[11px] font-bold text-gray-700 border border-gray-300 bg-white hover:bg-gray-100 px-3 py-1 transition whitespace-nowrap"
                        >
                            Unselect
                        </button>
                        <div class="flex border border-gray-300">
                            <button onclick="setViewMode('grid')" id="view-grid-btn" class="px-2 py-1 bg-gray-800 text-white" title="Grid View">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V2zM1 7a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V7zM1 12a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"/></svg>
                            </button>
                            <button onclick="setViewMode('list')" id="view-list-btn" class="px-2 py-1 bg-white text-gray-600 hover:bg-gray-100" title="List View">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5z"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- ── Active Filter Tags — only when filters applied ── --}}
                    <div id="active-filter-tags" class="hidden mb-3">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] text-gray-500 font-semibold mr-1">Results showing for:</span>
                            <div id="filter-tags-list" class="flex flex-wrap gap-1.5 flex-1"></div>
                            <button onclick="clearAllFilters()" class="text-[10px] text-red-500 font-semibold hover:underline whitespace-nowrap ml-1">Clear all</button>
                        </div>
                    </div>

                    {{-- Hoardings Grid --}}
                    <div id="hoardings-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 max-h-[calc(100vh-250px)] overflow-y-auto pr-1 sm:pr-2 custom-scrollbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="preview-screen" class="hidden animate-fade-in">
        @include('vendor.pos.preview-screen')
    </div>
</div>

@include('vendor.pos.customer-modal')

<!-- Date picker modal -->
<div id="datePickerModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-black/50 absolute inset-0" onclick="closeDatePickerModal()"></div>
    <div class="relative bg-white rounded-lg p-3 sm:p-4 w-[95vw] sm:w-full sm:max-w-[700px] z-50 flex flex-col min-h-[360px] sm:min-h-[420px]">
        <div class="flex justify-between items-center mb-4 gap-2">
            <h3 id="datePickerTitle" class="font-bold text-gray-800">Select Dates</h3>
            <button class="text-gray-500" onclick="closeDatePickerModal()">✕</button>
        </div>
        <input id="date-picker-input" type="text" class="hidden">
        <div id="date-picker-inline" class="mx-auto w-full overflow-x-auto"></div>
        <div class="mt-4 flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3">
            <button class="w-full sm:w-auto min-h-[44px] px-4 py-2 border rounded" onclick="closeDatePickerModal()">Cancel</button>
            <button class="w-full sm:w-auto min-h-[44px] px-4 py-2 bg-[#2D5A43] text-white rounded" onclick="confirmDateSelection()">Confirm</button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    .flatpickr-day.booked  { background: #ef4444 !important; color: #fff !important; border-color: #ef4444 !important; }
    .flatpickr-day.blocked { background: #6b7280 !important; color: #fff !important; border-color: #6b7280 !important; }
    .flatpickr-day.hold    { background: #f59e0b !important; color: #fff !important; border-color: #f59e0b !important; }
    .flatpickr-day.partial { background: #f97316 !important; color: #fff !important; border-color: #f97316 !important; }

    .availability-conflict td { background-color: #fff5f5 !important; }
    .availability-conflict td:first-child { border-left: 3px solid #ef4444; }

    /* List view styles */
    #hoardings-grid.list-view { grid-template-columns: 1fr !important; }
    #hoardings-grid.list-view .hoarding-card { display: flex; flex-direction: row; align-items: center; }
    #hoardings-grid.list-view .hoarding-card img { width: 64px; height: 64px; flex-shrink: 0; }
    #hoardings-grid.list-view .hoarding-card .card-body { flex: 1; }

    /* POS panel split: 55% left, 45% right on large screens */
    @media (min-width: 1024px) {
        #selection-screen:not(.hidden) {
            display: flex;
            flex-direction: row;
            gap: 1.5rem;
        }
        #selection-screen > div:first-child {
            width: 55% !important;
            min-width: 0;
        }
        #selection-screen > div:last-child {
            width: 45% !important;
            min-width: 0;
        }
    }
    @media (max-width: 1023.98px) {
        #selection-screen:not(.hidden) {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        #selection-screen > div:first-child,
        #selection-screen > div:last-child {
            width: 100% !important;
        }
    }
</style>

<script>
/* --- CONFIG & STATE --- */
function showToast(message, type = 'info') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: type, title: message });
    } else { alert(message); }
}

const API_URL = '/vendor/pos/api';
let hoardings        = [];
let selectedHoardings = new Map();
let selectedCustomer  = null;
let currentPage  = 1;
let totalPages   = 1;
let perPage      = 10;
let currentViewMode   = 'grid';
let activeFilters     = {};
let availabilityIssues = {};

const formatINR = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val);

function getTieredMonths(startDate, endDate) {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate), end = new Date(endDate);
    const diffDays = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1;
    return Math.ceil(diffDays / 30);
}
function getTieredDurationLabel(s, e) {
    const m = getTieredMonths(s, e);
    return m <= 0 ? '0 Months' : m === 1 ? '1 Month' : `${m} Months`;
}
function calculateTieredPrice(ppm, s, e) { return ppm * getTieredMonths(s, e); }

const fetchJSON = async (url, options = {}) => {
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        ...options
    });
    return await res.json();
};

/* --- INIT --- */
document.addEventListener('DOMContentLoaded', async () => {
    await loadHoardings();

    const urlParams  = new URLSearchParams(window.location.search);
    const customerId = urlParams.get('customer_id');

    if (customerId) {
        const newBtn = document.getElementById('new-customer-btn');
        if (newBtn) newBtn.style.display = 'none';
        try {
            let found = null;
            let res = await fetchJSON(`${API_URL}/customers/${customerId}`);
            if (res && (res.data || res.customer)) {
                found = res.data || res.customer;
            }
            if (!found || !found.id) {
                res  = await fetchJSON(`${API_URL}/customers?search=${customerId}`);
                const list = res.data?.data || res.data || [];
                found  = list.find(c => String(c.id) === String(customerId));
                if (!found) {
                    const allRes  = await fetchJSON(`${API_URL}/customers`);
                    const allList = allRes.data?.data || allRes.data || [];
                    found = allList.find(c => String(c.id) === String(customerId));
                }
            }
            if (found && found.id) {
                selectCustomer(found);
                const searchInput = document.getElementById('customer-search');
                if (searchInput) searchInput.value = found.name;
                const changeBtn = document.getElementById('change-customer-btn');
                if (changeBtn) changeBtn.style.display = 'none';
            } else {
                document.getElementById('search-container')?.classList.add('hidden');
                document.getElementById('customer-selected-card')?.classList.remove('hidden');
                if (document.getElementById('cust-name'))    document.getElementById('cust-name').innerText    = 'Customer not found';
                if (document.getElementById('cust-details')) document.getElementById('cust-details').innerText = `ID: ${customerId}`;
                if (document.getElementById('cust-initials'))document.getElementById('cust-initials').innerText = '?';
            }
        } catch (e) { 
            console.error(e);
            document.getElementById('search-container')?.classList.add('hidden');
            document.getElementById('customer-selected-card')?.classList.remove('hidden');
            if (document.getElementById('cust-name'))    document.getElementById('cust-name').innerText    = 'Customer not found';
            if (document.getElementById('cust-details')) document.getElementById('cust-details').innerText = `ID: ${customerId}`;
            if (document.getElementById('cust-initials'))document.getElementById('cust-initials').innerText = '?';
        }
    }

    document.getElementById('customer-search').addEventListener('input', debounce(handleCustomerSearch, 300));
    document.getElementById('hoarding-search').addEventListener('input', debounce(filterInventory, 200));
});

/* --- VIEW MODE --- */
function setViewMode(mode) {
    currentViewMode = mode;
    const grid = document.getElementById('hoardings-grid');
    const btnGrid = document.getElementById('view-grid-btn');
    const btnList = document.getElementById('view-list-btn');
    if (mode === 'grid') {
        grid.classList.remove('list-view');
        grid.classList.remove('grid-cols-2');
        grid.classList.add('grid-cols-1', 'sm:grid-cols-2');
        btnGrid.classList.add('bg-gray-800', 'text-white');
        btnGrid.classList.remove('bg-white', 'text-gray-600');
        btnList.classList.remove('bg-gray-800', 'text-white');
        btnList.classList.add('bg-white', 'text-gray-600');
    } else {
        grid.classList.add('list-view');
        grid.classList.remove('grid-cols-1', 'sm:grid-cols-2', 'grid-cols-2');
        btnList.classList.add('bg-gray-800', 'text-white');
        btnList.classList.remove('bg-white', 'text-gray-600');
        btnGrid.classList.remove('bg-gray-800', 'text-white');
        btnGrid.classList.add('bg-white', 'text-gray-600');
    }
    renderHoardings(hoardings);
}

/* --- ACTIVE FILTER TAGS --- */
const filterLabelMap = {
    type:              v => v && v !== '' && v !== 'ALL' ? v.toUpperCase() : null,
    category:          v => v ? v.split(',').map(c => c.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase())).join(', ') : null,
    availability:      v => v ? v.split(',').map(a => a === 'available' ? 'Available Hoardings' : 'Booked Hoardings').join(', ') : null,
    surroundings:      v => v ? v.split(',').map(s => s.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase())).join(', ') : null,
    hoarding_size_min: v => null,
    hoarding_size_max: v => null,
};

function renderFilterTags(params) {
    const tagsContainer = document.getElementById('active-filter-tags');
    const tagsList      = document.getElementById('filter-tags-list');
    tagsList.innerHTML  = '';

    let hasAny = false;
    const tags = [];

    Object.entries(params).forEach(([key, val]) => {
        if (!val || val === '' || val === 'ALL' || key === 'page' || key === 'per_page') return;
        if (key === 'screen_size_min' || key === 'screen_size_max') return;
        if (key === 'hoarding_size_min' || key === 'hoarding_size_max') return;

        const labelFn = filterLabelMap[key];
        const label   = labelFn ? labelFn(val) : val;
        if (label) tags.push({ key, label });
    });

    const sMin = params.screen_size_min, sMax = params.screen_size_max;
    if ((sMin && sMin !== '0') || (sMax && sMax !== '1000')) {
        tags.push({ key: 'screen_size', label: `${sMin ?? 0}Sq.ft - ${sMax ?? 1000}Sq.ft` });
    }
    const hMin = params.hoarding_size_min, hMax = params.hoarding_size_max;
    if ((hMin && hMin !== '0') || (hMax && hMax !== '1000')) {
        tags.push({ key: 'hoarding_size', label: `Hoarding: ${hMin ?? 0} - ${hMax ?? 1000}Sq.ft` });
    }

    if (params.price_min || params.price_max) {
        tags.push({ key: 'price', label: `₹${params.price_min ?? 0} - ₹${params.price_max ?? '∞'}` });
    }

    if (params.city) tags.push({ key: 'city', label: params.city });

    tags.forEach(({ key, label }) => {
        hasAny = true;
        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center gap-1 bg-gray-100 border border-gray-300 text-gray-700 text-[10px] font-medium px-2 py-0.5';
        tag.innerHTML = `${label} <button onclick="removeFilterTag('${key}')" class="text-gray-400 hover:text-red-500 font-bold leading-none ml-0.5">✕</button>`;
        tagsList.appendChild(tag);
    });

    tagsContainer.classList.toggle('hidden', !hasAny);
    updateUnselectBtn();
}

function removeFilterTag(key) {
    if (key === 'screen_size')        { delete activeFilters.screen_size_min; delete activeFilters.screen_size_max; }
    else if (key === 'hoarding_size') { delete activeFilters.hoarding_size_min; delete activeFilters.hoarding_size_max; }
    else if (key === 'price')         { delete activeFilters.price_min; delete activeFilters.price_max; }
    else                              { delete activeFilters[key]; }
    currentPage = 1;
    loadHoardings(activeFilters);
}

function clearAllFilters() {
    activeFilters = {};
    currentPage   = 1;
    loadHoardings({});
    if (typeof resetFilters === 'function') resetFilters(false);
}

function updateUnselectBtn() {
    const btn = document.getElementById('unselect-all-btn');
    if (!btn) return;
    if (selectedHoardings.size > 0) {
        btn.classList.remove('hidden');
    } else {
        btn.classList.add('hidden');
    }
}

function unselectAllHoardings() {
    selectedHoardings.clear();
    availabilityIssues = {};
    updateSummary();
    updateUnselectBtn();
}

/* --- CUSTOMER LOGIC --- */
function openCustomerModal()  { document.getElementById('customerModal').classList.remove('hidden'); }
function closeCustomerModal() { document.getElementById('customerModal').classList.add('hidden'); }
function clearAddressFields() {
    const city = document.getElementById('city'), state = document.getElementById('state');
    if (city) city.value = ''; if (state) state.value = '';
}

async function handleCustomerSearch(e) {
    const q   = e.target.value.trim();
    const box = document.getElementById('customer-suggestions');
    if (q.length < 2) { box.classList.add('hidden'); return; }
    try {
        const res  = await fetchJSON(`${API_URL}/customers?search=${encodeURIComponent(q)}`);
        const list = res.data?.data || res.data || [];
        box.innerHTML = list.map(c => `
            <div class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b" onclick='selectCustomer(${JSON.stringify(c).replace(/'/g, "&apos;")})'>
                <div class="text-sm font-bold text-gray-800">${c.name}</div>
                <div class="text-[10px] text-gray-500">${c.phone}</div>
            </div>`).join('') || '<div class="p-4 text-xs text-gray-400">No results</div>';
        box.classList.remove('hidden');
    } catch (e) { console.error(e); }
}

function toLocalYMD(date) {
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}

function selectCustomer(c) {
    if (!c || !c.name) { console.warn('Invalid customer', c); return; }
    selectedCustomer = c;
    document.getElementById('search-container')?.classList.add('hidden');
    document.getElementById('customer-selected-card')?.classList.remove('hidden');
    document.getElementById('customer-suggestions')?.classList.add('hidden');
    if (document.getElementById('cust-name'))    document.getElementById('cust-name').innerText    = c.name;
    if (document.getElementById('cust-details')) document.getElementById('cust-details').innerText = `${c.email || ''} | ${c.phone || ''}`;
    if (document.getElementById('cust-initials'))document.getElementById('cust-initials').innerText = c.name.slice(0,2).toUpperCase();
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    document.getElementById('search-container').classList.remove('hidden');
    document.getElementById('customer-selected-card').classList.add('hidden');
}

/* --- INVENTORY LOGIC --- */
async function loadHoardings(filters = {}) {
    activeFilters    = { ...filters };
    filters.page     = currentPage;
    filters.per_page = perPage;
    const query = new URLSearchParams(filters).toString();
    const url   = `${API_URL}/hoardings${query ? '?' + query : ''}`;
    const res   = await fetchJSON(url);

    if ('last_page' in res) {
        hoardings   = res.data || [];
        totalPages  = res.last_page  || 1;
        currentPage = res.current_page || 1;
    } else if (res.data && typeof res.data === 'object' && 'data' in res.data && 'last_page' in res.data) {
        hoardings  = res.data.data;
        totalPages = res.data.last_page || 1;
    } else if (Array.isArray(res.data)) {
        hoardings  = res.data;
        totalPages = 1;
    } else {
        hoardings  = res.data?.data || res.data || [];
        totalPages = 1;
    }

    renderHoardings(hoardings);
    renderPagination();
    renderFilterTags(activeFilters);
}
window.loadHoardings = loadHoardings;

function renderHoardings(list) {
    const grid = document.getElementById('hoardings-grid');
    if (list.length === 0) {
        grid.innerHTML = `
            <div class="col-span-1 sm:col-span-2 flex flex-col items-center justify-center py-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 3.5a7.5 7.5 0 0013.15 13.15z"/>
                </svg>
                <h4 class="text-sm font-bold text-gray-600 mb-1">No Data Found</h4>
                <p class="text-xs text-gray-400">Try adjusting your search or filter criteria</p>
            </div>`;
        document.getElementById('available-count').innerText = 0;
        return;
    }

    grid.innerHTML = list.map(h => {
        const isSelected = selectedHoardings.has(h.id);
        const isDooh     = h.type?.toUpperCase() === 'DOOH';

        if (currentViewMode === 'list') {
            return `
                <div class="hoarding-card flex items-center gap-3 bg-white border ${isSelected ? 'border-green-500 ring-1 ring-green-500' : 'border-gray-200'} p-2 cursor-pointer" onclick="toggleHoarding(${h.id})">
                    <img src="${h.image_url || '/placeholder.png'}" class="w-16 h-16 object-cover object-center flex-shrink-0">
                    <div class="card-body flex-1 min-w-0">
                        <h4 class="text-[11px] font-bold text-gray-800 truncate" title="${h.title}">${h.title}</h4>
                        <span class="text-[10px] font-bold text-gray-600">${formatINR(h.price_per_month)}/M</span>
                        ${isDooh ? `<span class="block text-[9px] text-purple-600 font-medium">${h.total_slots_per_day ?? 300} slots/day</span>` : ''}
                    </div>
                    ${isDooh ? `<span class="text-[9px] font-bold bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded self-start">DOOH</span>` : ''}
                    ${isSelected ? `
                        <button onclick="event.stopPropagation(); toggleHoarding(${h.id})"
                            class="text-[10px] font-bold text-red-500 border border-red-300 bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition self-center whitespace-nowrap">
                            Unselect
                        </button>` : ''}
                </div>`;
        }

        // Grid view — FIXED (backslash wali broken line hatayi, sahi wali lagayi)
        return `
            <div class="hoarding-card relative bg-white border ${isSelected ? 'border-green-500 ring-1 ring-green-500' : 'border-gray-200'} overflow-hidden cursor-pointer" onclick="toggleHoarding(${h.id})">
                <img src="${h.image_url || '/placeholder.png'}" class="w-full h-20 object-cover object-center">
                ${isDooh ? `<span class="absolute top-1 right-1 bg-purple-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">DOOH</span>` : ''}
                <div class="p-2">
                    <h4 class="text-[10px] font-bold text-gray-800 truncate" title="${h.title}">${h.title}</h4>
                    <span class="text-[10px] font-bold">${formatINR(h.price_per_month)}/M</span>
                    ${isDooh ? `<span class="block text-[9px] text-purple-600 font-medium">${h.total_slots_per_day ?? 300} slots/day</span>` : ''}
                </div>
            </div>`;
    }).join('');

    document.getElementById('available-count').innerText = list.length;
}

function renderPagination() {
    let container = document.getElementById('hoardings-pagination');
    if (!container) {
        const parent = document.querySelector('#hoardings-grid')?.parentElement;
        if (!parent) return;
        container = document.createElement('div');
        container.id = 'hoardings-pagination';
        container.className = 'flex justify-center items-center gap-2 mt-4';
        parent.appendChild(container);
    }
    if (totalPages <= 1) { container.innerHTML = ''; return; }

    let html = `<button class="px-2 py-1 border rounded text-xs ${currentPage===1?'bg-gray-200 text-gray-400 cursor-not-allowed':'bg-white'}" onclick="changePage(${currentPage-1})" ${currentPage===1?'disabled':''}>Prev</button>`;
    let start = Math.max(1, currentPage-2), end = Math.min(totalPages, start+4);
    if (end-start < 4) start = Math.max(1, end-4);
    for (let i = start; i <= end; i++) {
        html += `<button class="px-2 py-1 border rounded text-xs ${i===currentPage?'bg-green-600 text-white':'bg-white'}" onclick="changePage(${i})">${i}</button>`;
    }
    html += `<button class="px-2 py-1 border rounded text-xs ${currentPage===totalPages?'bg-gray-200 text-gray-400 cursor-not-allowed':'bg-white'}" onclick="changePage(${currentPage+1})" ${currentPage===totalPages?'disabled':''}>Next</button>`;
    container.innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;
    currentPage = page;
    loadHoardings({ ...activeFilters });
}

function filterInventory() {
    const q = document.getElementById('hoarding-search').value.toLowerCase();
    currentPage = 1;
    loadHoardings(q ? { ...activeFilters, search: q } : { ...activeFilters });
}

function toggleHoarding(id) {
    if (selectedHoardings.has(id)) {
        selectedHoardings.delete(id);
        delete availabilityIssues[id];
    } else {
        const h    = hoardings.find(i => i.id === id);
        const today = toLocalYMD(new Date());
        let end    = new Date(); end.setDate(end.getDate() + 29);
        selectedHoardings.set(id, { ...h, startDate: today, endDate: toLocalYMD(end) });
    }
    updateSummary();
    updateUnselectBtn();
}

function updateSummary() {
    renderHoardings(hoardings);
    updateUnselectBtn();

    const oohTbody  = document.getElementById('ooh-selected-list');
    const doohTbody = document.getElementById('dooh-selected-list');
    oohTbody.innerHTML = ''; doohTbody.innerHTML = '';

    if (selectedHoardings.size === 0) {
        oohTbody.innerHTML  = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No static hoardings selected</td></tr>`;
        doohTbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>`;
    } else {
        let hasOoh = false, hasDooh = false;

        selectedHoardings.forEach((h, id) => {
            const totalPrice    = calculateTieredPrice(h.price_per_month, h.startDate, h.endDate);
            const issue         = availabilityIssues[id];
            const conflictClass = issue ? 'availability-conflict' : '';
            const conflictBadge = issue ? `<span class="inline-block mt-1 text-[9px] font-bold text-red-600 bg-red-100 px-1.5 py-0.5 rounded">${issue.label}</span>` : '';
            const isDooh        = h.type?.toUpperCase() === 'DOOH';

            if (isDooh) {
                hasDooh = true;
                const slotsPerDay = h.total_slots_per_day ?? 300;
                doohTbody.innerHTML += `
                    <tr class="hover:bg-gray-50 border-b border-gray-200 ${conflictClass}">
                        <td class="px-4 py-3">
                            <div class="text-xs font-bold text-gray-800">${h.title}</div>
                            <div class="text-[9px] text-gray-400 truncate w-32">${h.location_address || ''}</div>
                            ${conflictBadge}
                        </td>
                        <td class="px-3 sm:px-4 py-3 text-xs text-gray-500 hidden sm:table-cell">${formatINR(h.price_per_month)}</td>
                        <td class="px-3 sm:px-4 py-3 text-center hidden sm:table-cell">
                            <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-[11px] font-bold px-2 py-1 rounded-full">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                                ${slotsPerDay}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div class="flex flex-col items-center justify-center">
                                <div class="flex items-center justify-center whitespace-nowrap">
                                    <button onclick="openDatePickerForHoarding(${h.id})" class="px-2 py-1 rounded bg-white text-[11px] font-semibold text-gray-700 hover:text-blue-600 transition leading-none">
                                        ${toLocalYMD(h.startDate)} - ${toLocalYMD(h.endDate)}
                                    </button>
                                    <button onclick="openDatePickerForHoarding(${h.id})" class="flex items-center justify-center w-6 h-6 rounded-full text-blue-500 hover:text-blue-700 transition" title="Edit Dates">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 13l6.536-6.536a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-2.828 0L9 13z"/></svg>
                                    </button>
                                </div>
                                <div class="text-[11px] text-gray-400 mt-1 text-center leading-none">${getTieredDurationLabel(h.startDate, h.endDate)}</div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-4 py-3 font-bold text-xs text-green-700">${formatINR(totalPrice)}</td>
                        <td class="px-3 sm:px-4 py-3 text-right">
                            <button onclick="toggleHoarding(${h.id})" class="text-red-500 cursor-pointer">
                                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>`;
            } else {
                hasOoh = true;
                oohTbody.innerHTML += `
                    <tr class="hover:bg-gray-50 border-b border-gray-200 ${conflictClass}">
                        <td class="px-4 py-3">
                            <div class="text-xs font-bold text-gray-800">${h.title}</div>
                            <div class="text-[9px] text-gray-400 truncate w-32">${h.location_address || ''}</div>
                            ${conflictBadge}
                        </td>
                        <td class="px-3 sm:px-4 py-3 text-xs text-gray-500 hidden sm:table-cell">${formatINR(h.price_per_month)}</td>
                        <td class="px-4 py-3 align-middle">
                            <div class="flex flex-col items-center justify-center">
                                <div class="flex items-center justify-center whitespace-nowrap">
                                    <button onclick="openDatePickerForHoarding(${h.id})" class="px-2 py-1 rounded bg-white text-[11px] font-semibold text-gray-700 hover:text-blue-600 transition leading-none">
                                        ${toLocalYMD(h.startDate)} - ${toLocalYMD(h.endDate)}
                                    </button>
                                    <button onclick="openDatePickerForHoarding(${h.id})" class="flex items-center justify-center w-6 h-6 rounded-full text-blue-500 hover:text-blue-700 transition" title="Edit Dates">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 13l6.536-6.536a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-2.828 0L9 13z"/></svg>
                                    </button>
                                </div>
                                <div class="text-[11px] text-gray-400 mt-1 text-center leading-none">${getTieredDurationLabel(h.startDate, h.endDate)}</div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-4 py-3 font-bold text-xs text-green-700">${formatINR(totalPrice)}</td>
                        <td class="px-3 sm:px-4 py-3 text-right">
                            <button onclick="toggleHoarding(${h.id})" class="text-red-500 cursor-pointer">
                                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>`;
            }
        });

        if (!hasOoh)  oohTbody.innerHTML  = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No static hoardings selected</td></tr>`;
        if (!hasDooh) doohTbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>`;
    }
    document.getElementById('btn-count').innerText = selectedHoardings.size;
}

function updateDate(id, field, value) {
    let h = selectedHoardings.get(id);
    if (h) {
        h[field] = value;
        if (field === 'startDate' && h.endDate < value) h.endDate = value;
        selectedHoardings.set(id, h);
        updateSummary();
    }
}

/* --- Availability --- */
function statusLabel(status) {
    const map = { booked: 'Already Booked', blocked: 'Blocked/Maintenance', hold: 'On Hold', partial: 'Partially Unavailable' };
    return map[status] || status;
}

async function checkAllAvailability() {
    availabilityIssues = {};
    let allClear = true;
    const checks = Array.from(selectedHoardings.entries()).map(async ([id, h]) => {
        const allDates = enumerateDatesBetween(h.startDate, h.endDate);
        try {
            const res = await fetch(`/api/v1/hoardings/${id}/availability/check-dates`, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ dates: allDates })
            });
            if (!res.ok) return;
            const result    = await res.json();
            const conflicts = (result.data?.results || []).filter(r => r.status !== 'available');
            if (conflicts.length > 0) {
                allClear = false;
                const statuses = [...new Set(conflicts.map(c => c.status))];
                availabilityIssues[id] = { title: h.title, label: statuses.map(statusLabel).join(', '), conflicts };
            }
        } catch (e) { console.error('Availability check error', id, e); }
    });
    await Promise.all(checks);
    return allClear;
}

function showAvailabilityAlert(issues) {
    const alert = document.getElementById('availability-alert');
    const body  = document.getElementById('availability-alert-body');
    const entries = Object.values(issues);
    if (entries.length === 0) { alert.classList.add('hidden'); return; }
    body.innerHTML = entries.map(i => `
        <div class="flex items-start gap-2 py-1">
            <span class="font-bold text-red-700">${i.title}:</span>
            <span>${i.label} — please adjust the booking dates.</span>
        </div>`).join('');
    alert.classList.remove('hidden');
    alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/* --- Calendar --- */
let currentFlatpickr = null, currentHeatmapMap = {}, currentEditingHoardingId = null;
function toYMD(d) { return toLocalYMD(d); }

function enumerateDatesBetween(start, end) {
    const dates = []; let cur = new Date(start); const last = new Date(end);
    while (cur <= last) { dates.push(toYMD(cur)); cur.setDate(cur.getDate()+1); }
    return dates;
}

async function openDatePickerForHoarding(id) {
    if (typeof flatpickr === 'undefined') { showToast('Calendar library not loaded.'); return; }
    currentEditingHoardingId = id;
    const h = selectedHoardings.get(id);
    if (!h) { showToast('Please select the hoarding first'); return; }
    document.getElementById('datePickerTitle').innerText = h.title;
    document.getElementById('datePickerModal').classList.remove('hidden');

    const startStr = toLocalYMD(new Date());
    const future   = new Date(); future.setDate(future.getDate() + 365);
    try {
        const res = await fetch(`/api/v1/hoardings/${id}/availability/heatmap?start_date=${startStr}&end_date=${toLocalYMD(future)}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!res.ok) { showToast(`Could not load availability (HTTP ${res.status}).`); return; }
        const payload = await res.json();
        const heatmap = payload.data?.heatmap || [];
        const disabledDates = heatmap.filter(d => d.status !== 'available').map(d => d.date);
        currentHeatmapMap = {};
        heatmap.forEach(d => currentHeatmapMap[d.date] = d.status);
        if (currentFlatpickr) currentFlatpickr.destroy();
        currentFlatpickr = flatpickr('#date-picker-input', {
            mode: 'range', inline: true, appendTo: document.getElementById('date-picker-inline'),
            minDate: startStr, disable: disabledDates, defaultDate: [h.startDate, h.endDate], showMonths: window.innerWidth < 640 ? 1 : 2,
            onDayCreate(dObj, dStr, fp, dayElem) {
                const date   = toLocalYMD(dayElem.dateObj);
                const status = currentHeatmapMap[date];
                if (status && status !== 'available') { dayElem.classList.add(status); dayElem.title = status.charAt(0).toUpperCase()+status.slice(1); }
            }
        });
    } catch (e) { console.error(e); showToast('Could not load availability. Please try again.'); }
}

function closeDatePickerModal() { document.getElementById('datePickerModal').classList.add('hidden'); }

async function confirmDateSelection() {
    if (!currentFlatpickr || !currentEditingHoardingId) return closeDatePickerModal();
    const dates = currentFlatpickr.selectedDates;
    if (!dates || dates.length === 0) { showToast('Please select a start and end date'); return; }
    const start = toYMD(dates[0]), end = toYMD(dates.length === 1 ? dates[0] : dates[1]);
    const allDates = enumerateDatesBetween(start, end);
    try {
        const res = await fetch(`/api/v1/hoardings/${currentEditingHoardingId}/availability/check-dates`, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ dates: allDates })
        });
        if (!res.ok) { showToast('Could not verify availability.'); openDatePickerForHoarding(currentEditingHoardingId); return; }
        const result    = await res.json();
        const conflicts = (result.data?.results || []).filter(r => r.status !== 'available');
        if (conflicts.length > 0) { showToast('Selected range includes unavailable dates.', 'warning'); openDatePickerForHoarding(currentEditingHoardingId); return; }
        let h = selectedHoardings.get(currentEditingHoardingId);
        if (h) {
            h.startDate = start; h.endDate = end;
            selectedHoardings.set(currentEditingHoardingId, h);
            delete availabilityIssues[currentEditingHoardingId];
            updateSummary();
        }
        closeDatePickerModal();
    } catch (e) { console.error(e); showToast('Error checking availability.'); }
}

window.enumerateDatesBetween = enumerateDatesBetween;
window.toYMD = toYMD;
window.finalCheckAvailability = async function() { return await checkAllAvailability(); };

/* --- SUBMIT --- */
document.getElementById('submit-btn').addEventListener('click', async () => {
    if (!selectedCustomer)            { showToast('Select a customer.', 'warning'); return; }
    if (selectedHoardings.size === 0) { showToast('Select at least one hoarding.', 'warning'); return; }

    const btn = document.getElementById('submit-btn');
    const originalText = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = `<svg class="w-4 h-4 inline animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Checking Availability...`;

    const allClear = await checkAllAvailability();
    btn.disabled   = false;
    btn.innerHTML  = originalText;

    if (!allClear) {
        updateSummary();
        showAvailabilityAlert(availabilityIssues);
        showToast('Some hoardings have availability conflicts. Please review and adjust dates.', 'error');
        return;
    }
    document.getElementById('availability-alert').classList.add('hidden');
    populatePreview();
    document.getElementById('selection-screen').classList.add('hidden');
    document.getElementById('preview-screen').classList.remove('hidden');
});

function backToSelection() {
    document.getElementById('preview-screen').classList.add('hidden');
    document.getElementById('selection-screen').classList.remove('hidden');
}

function debounce(fn, t) { let timer; return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), t); }; }

/* --- PREVIEW --- */
let globalBaseAmount = 0;
function safe(val, fallback = '---') {
    return (val !== undefined && val !== null && val !== '') ? val : fallback;
}

function populatePreview() {
    if (!selectedCustomer) return;
    const oohTbody  = document.getElementById('preview-ooh-list');
    const doohTbody = document.getElementById('preview-dooh-list');
    if (!oohTbody || !doohTbody) { console.error('Preview DOM elements missing!'); return; }

    const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = safe(val); };
    setEl('preview-cust-name',    selectedCustomer.name);
    setEl('preview-cust-phone',   selectedCustomer.phone);
    setEl('preview-cust-email',   selectedCustomer.email);
    setEl('preview-cust-gstin',   selectedCustomer.gstin);
    setEl('preview-cust-status',  selectedCustomer.status);
    setEl('preview-cust-role',    selectedCustomer.role);
    setEl('preview-cust-address',
        [selectedCustomer.billing_address, selectedCustomer.billing_city, selectedCustomer.billing_state, selectedCustomer.billing_pincode]
            .filter(Boolean).join(', ') || '---');
    setEl('preview-cust-country', selectedCustomer.country);
    setEl('preview-cust-created', selectedCustomer.created_at);
    setEl('preview-cust-updated', selectedCustomer.updated_at);
    setEl('preview-total-count',  selectedHoardings.size);

    oohTbody.innerHTML = ''; doohTbody.innerHTML = '';
    globalBaseAmount   = 0;

    const allRows = [];
    selectedHoardings.forEach((h) => {
        const itemTotal = calculateTieredPrice(h.price_per_month, h.startDate, h.endDate);
        globalBaseAmount += itemTotal;
        allRows.push({ h, itemTotal });
    });

    let snCounter = 1;
    allRows.forEach(({ h, itemTotal }) => {
        const isDooh    = h.type?.toUpperCase() === 'DOOH';
        const slotsCell = isDooh ? `<div class="text-[10px] text-purple-600 font-medium mt-0.5">${h.total_slots_per_day ?? 300} slots/day</div>` : '';
        const typeBadge = isDooh
            ? `<span class="inline-block text-[9px] font-bold bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">DOOH</span>`
            : `<span class="inline-block text-[9px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">OOH</span>`;

        const row = `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="px-4 py-2 text-xs text-gray-400 font-semibold w-8">${snCounter++}</td>
                <td class="px-4 py-2">
                    <div class="flex items-center gap-2">
                        <img src="${h.image_url || '/placeholder.png'}" class="w-8 h-8 rounded object-cover border border-gray-100 flex-shrink-0">
                        <div>
                            <p class="font-bold text-gray-800 text-xs leading-tight">${safe(h.title)}</p>
                            <p class="text-[9px] text-gray-400">${safe(h.location_address, '')}</p>
                            ${slotsCell}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-2 text-xs text-gray-500">${safe(h.location_address, safe(h.city, '---'))}</td>
                <td class="px-4 py-2">${typeBadge}</td>
                <td class="px-4 py-2 text-xs text-gray-600">
                    ${h.startDate} – ${h.endDate}
                    <div class="text-[10px] text-gray-400">${getTieredDurationLabel(h.startDate, h.endDate)}</div>
                </td>
                <td class="px-4 py-2 text-right font-bold text-gray-800 text-xs">${formatINR(itemTotal)}</td>
            </tr>`;
        oohTbody.innerHTML += row;
    });
    doohTbody.innerHTML = '';

    const sideSubTotal = document.getElementById('side-sub-total');
    if (sideSubTotal) sideSubTotal.innerText = formatINR(globalBaseAmount);
    calculateFinalTotals();
}
</script>
<script>
    function openFilterModal()  { document.getElementById('filterModal').classList.remove('hidden'); }
    function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); }
</script>

@endsection