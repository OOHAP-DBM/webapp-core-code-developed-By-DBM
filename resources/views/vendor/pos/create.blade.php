@extends('layouts.vendor')

@section('title', 'Create Pos Booking')

@section('content')
<div class="px-6 py-6 bg-gray-50 min-h-screen">
    <div id="selection-screen" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <div class="lg:col-span-7">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h2 class="text-xl font-bold text-gray-800">Create POS Booking</h2>
                    <span id="booking-date" class="text-xs text-gray-400 font-medium"></span>
                </div>

                <div class="p-6">
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Customer Details</label>
                        
                        <div id="search-container" class="flex gap-2">
                            <div class="relative flex-1 border border-gray-300">
                                <input type="text" id="customer-search" autocomplete="off" 
                                    placeholder="Search by name, email, or mobile..." 
                                    class="w-full  border-gray-300 focus:ring-green-500 text-sm py-2.5 px-2">
                                <div id="customer-suggestions" class="absolute z-50 w-full bg-white border rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto"></div>
                            </div>
                            <button type="button" onclick="openCustomerModal()" class="bg-green-600 text-white px-4  hover:bg-green-700 transition flex items-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span class="ml-1 text-sm font-semibold">New</span>
                            </button>
                        </div>

                        <div id="customer-selected-card" class="hidden flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-4 animate-fade-in">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-[#2D5A43] rounded-full flex items-center justify-center text-white font-bold text-sm" id="cust-initials">--</div>
                                <div>
                                    <h4 id="cust-name" class="font-bold text-gray-800 text-sm leading-tight">Customer Name</h4>
                                    <p id="cust-details" class="text-xs text-gray-500 mt-0.5">Contact Details</p>
                                </div>
                            </div>
                            <button onclick="clearSelectedCustomer()" class="text-xs font-bold text-red-500 hover:text-red-700 px-3 py-1 border border-red-200 rounded-md bg-white">Change</button>
                        </div>
                    </div>

                    {{-- Availability Issues Alert (shown when conflicts detected) --}}
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
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span> OOH (Static)
                            </h4>
                            <div class="overflow-x-auto border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Hoarding</th>
                                            <th class="px-4 py-3 font-semibold">Rental/Mo</th>
                                            <th class="px-4 py-3 font-semibold text-center">Duration</th>
                                            <th class="px-4 py-3 font-semibold">Final Amount</th>
                                            <th class="px-4 py-3 font-semibold text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No static hoardings selected</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="selection-group">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> Digital (DOOH)
                            </h4>
                            <div class="overflow-x-auto border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Hoarding</th>
                                            <th class="px-4 py-3 font-semibold">Rental/Mo</th>
                                            <th class="px-4 py-3 font-semibold text-center">Slots/Day</th>
                                            <th class="px-4 py-3 font-semibold text-center">Duration</th>
                                            <th class="px-4 py-3 font-semibold">Final Amount</th>
                                            <th class="px-4 py-3 font-semibold text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-12 pt-6 border-t border-gray-100">
                        <button type="button" onclick="location.reload()" class="flex-1 py-3 bg-[#7A9C89] border border-gray-200 font-bold text-white transition cursor-pointer">Cancel</button>
                        <button id="submit-btn" class="flex-1 py-3 bg-[#2E5B42] text-white font-bold shadow-lg shadow-green-900/20 hover:bg-opacity-90 active:scale-[0.98] transition cursor-pointer">
                            Preview & Create Booking (<span id="btn-count">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-6">
                <div class="px-5 pt-5 gap-3 flex">
                    <h3 class="font-bold text-gray-800">Available Hoardings</h3>
                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full text-xs font-bold" id="available-count">0</span>
                </div>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="relative flex-1">
                            <input type="text" id="hoarding-search" placeholder="Search for available hoardings..." 
                                class="w-full pl-10  border border-gray-300 text-sm focus:ring-green-500" style="height:40px;">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="pointer-events:none;">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/></svg>
                            </span>
                        </div>
                        <button type="button" class="border border-gray-300 bg-white  px-5 py-2 text-gray-700 text-sm font-medium hover:bg-gray-100 transition" style="height:40px;" onclick="openFilterModal()">Filter</button>
                    @include('vendor.pos.filter_modal')
                    </div>
                    <div id="hoardings-grid" class="grid grid-cols-2 gap-4 max-h-[calc(100vh-250px)] overflow-y-auto pr-2 custom-scrollbar">
                        </div>
                </div>
            </div>
        </div>
    </div>

    <div id="preview-screen" class="hidden animate-fade-in">
        @include('vendor.pos.preview-screen')
    </div>
</div>

@include('vendor.pos.customer-modal')

<!-- Date picker modal (single calendar range picker, shows availability heatmap) -->
<div id="datePickerModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-black/50 absolute inset-0" onclick="closeDatePickerModal()"></div>

    <div class="relative bg-white rounded-lg p-4 w-full max-w-[700px] z-50 flex flex-col" style="min-width: 600px; min-height: 420px;">
        <div class="flex justify-between items-center mb-4">
            <h3 id="datePickerTitle" class="font-bold text-gray-800">Select Dates</h3>
            <button class="text-gray-500" onclick="closeDatePickerModal()">✕</button>
        </div>

        <input id="date-picker-input" type="text" class="hidden">

        <div id="date-picker-inline" class="mx-auto"></div>

        <div class="mt-4 flex justify-end gap-5">
            <button class="px-4 py-2 border rounded" onclick="closeDatePickerModal()">Cancel</button>
            <button class="px-4 py-2  bg-[#2D5A43] text-white rounded" onclick="confirmDateSelection()">Confirm</button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* Flatpickr availability styles */
    .flatpickr-day.booked { background: #ef4444 !important; color: #fff !important; border-color: #ef4444 !important; }
    .flatpickr-day.blocked { background: #6b7280 !important; color: #fff !important; border-color: #6b7280 !important; }
    .flatpickr-day.hold { background: #f59e0b !important; color: #fff !important; border-color: #f59e0b !important; }
    .flatpickr-day.partial { background: #f97316 !important; color: #fff !important; border-color: #f97316 !important; }

    /* Availability conflict row highlight */
    .availability-conflict td { background-color: #fff5f5 !important; }
    .availability-conflict td:first-child { border-left: 3px solid #ef4444; }
</style>

<script>
/* --- CONFIG & STATE --- */
function showToast(message, type = 'info') {
    if (window.Swal) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            icon: type,
            title: message
        });
    } else {
        alert(message);
    }
}
const API_URL = '/vendor/pos/api';
let hoardings = [];
let selectedHoardings = new Map();
let selectedCustomer = null;
let availabilityIssues = {}; // { hoardingId: { status, message } }

const formatINR = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val);

function getTieredMonths(startDate, endDate) {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return Math.ceil(diffDays / 30);
}

function getTieredDurationLabel(startDate, endDate) {
    const months = getTieredMonths(startDate, endDate);
    if (months <= 0) return '0 Months';
    return months === 1 ? '1 Month' : `${months} Months`;
}

function calculateTieredPrice(pricePerMonth, startDate, endDate) {
    const monthsToCharge = getTieredMonths(startDate, endDate);
    return pricePerMonth * monthsToCharge;
}

const fetchJSON = async (url, options = {}) => {
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}',  'X-Requested-With': 'XMLHttpRequest' },
        ...options
    });
    return await res.json();
};

/* --- INITIALIZATION --- */
document.addEventListener('DOMContentLoaded', async () => {
    document.getElementById('booking-date').innerText = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    await loadHoardings();

    const urlParams = new URLSearchParams(window.location.search);
    const customerId = urlParams.get('customer_id');
    if (customerId) {
        try {
            const res = await fetchJSON(`${API_URL}/customers?search=${customerId}`);
            const list = res.data?.data || res.data || [];
            let found = list.find(c => c.id == customerId);
            if (!found) {
                const allRes = await fetchJSON(`${API_URL}/customers`);
                const allList = allRes.data?.data || allRes.data || [];
                found = allList.find(c => c.id == customerId);
            }
            if (found) selectCustomer(found);
        } catch (e) { console.error(e); }
    }

    document.getElementById('customer-search').addEventListener('input', debounce(handleCustomerSearch, 300));
    document.getElementById('hoarding-search').addEventListener('input', debounce(filterInventory, 200));
});

/* --- CUSTOMER LOGIC --- */
function openCustomerModal() { document.getElementById('customerModal').classList.remove('hidden'); }
function clearAddressFields() {
    const city = document.getElementById('city');
    const state = document.getElementById('state');
    if (city) city.value = '';
    if (state) state.value = '';
}

function closeCustomerModal() { document.getElementById('customerModal').classList.add('hidden'); }

async function handleCustomerSearch(e) {
    const q = e.target.value.trim();
    const box = document.getElementById('customer-suggestions');
    if (q.length < 2) { box.classList.add('hidden'); return; }

    try {
        const res = await fetchJSON(`${API_URL}/customers?search=${encodeURIComponent(q)}`);
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
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function selectCustomer(c) {
    if (!c || !c.name) { console.warn('Invalid customer object', c); return; }
    selectedCustomer = c;

    document.getElementById('search-container')?.classList.add('hidden');
    document.getElementById('customer-selected-card')?.classList.remove('hidden');
    document.getElementById('customer-suggestions')?.classList.add('hidden');

    if (document.getElementById('cust-name')) document.getElementById('cust-name').innerText = c.name;
    if (document.getElementById('cust-details')) document.getElementById('cust-details').innerText = `${c.email || ''} | ${c.phone || ''}`;
    if (document.getElementById('cust-initials')) document.getElementById('cust-initials').innerText = c.name.slice(0, 2).toUpperCase();
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    document.getElementById('search-container').classList.remove('hidden');
    document.getElementById('customer-selected-card').classList.add('hidden');
}

/* --- INVENTORY LOGIC --- */
async function loadHoardings(filters = {}) {
    const query = new URLSearchParams(filters).toString();
    const url   = `${API_URL}/hoardings${query ? '?' + query : ''}`;
    const res   = await fetchJSON(url);
    hoardings   = res.data?.data || res.data || [];
    renderHoardings(hoardings);
}

window.loadHoardings = loadHoardings;

function renderHoardings(list) {
    const grid = document.getElementById('hoardings-grid');
    grid.innerHTML = list.map(h => {
        const isSelected = selectedHoardings.has(h.id);
        const isDooh = h.type?.toUpperCase() === 'DOOH';
        return `
            <div class="relative bg-white border ${isSelected ? 'border-green-500 ring-1 ring-green-500' : 'border-gray-200'} overflow-hidden cursor-pointer" onclick="toggleHoarding(${h.id})">
                <img src="${h.image_url || '/placeholder.png'}" class="w-full h-20 object-cover">
                ${isDooh ? `<span class="absolute top-1 right-1 bg-purple-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">DOOH</span>` : ''}
                <div class="p-2">
                    <h4 class="text-[10px] font-bold text-gray-800 truncate">${h.title}</h4>
                    <span class="text-[10px] font-bold">${formatINR(h.price_per_month)}/M</span>
                    ${isDooh ? `<span class="block text-[9px] text-purple-600 font-medium">${h.total_slots_per_day ?? 300} slots/day</span>` : ''}
                </div>
            </div>`;
    }).join('');
    document.getElementById('available-count').innerText = list.length;
}

function filterInventory() {
    const q = document.getElementById('hoarding-search').value.toLowerCase();
    renderHoardings(hoardings.filter(h => h.title.toLowerCase().includes(q) || (h.location_address || '').toLowerCase().includes(q)));
}

function toggleHoarding(id) {
    if (selectedHoardings.has(id)) {
        selectedHoardings.delete(id);
        delete availabilityIssues[id];
    } else {
        const h = hoardings.find(i => i.id === id);
        const today = toLocalYMD(new Date());
        let end = new Date(); end.setDate(end.getDate() + 29);
        selectedHoardings.set(id, { ...h, startDate: today, endDate: toLocalYMD(end) });
    }
    updateSummary();
}

function updateSummary() {
    renderHoardings(hoardings);
    const oohTbody = document.getElementById('ooh-selected-list');
    const doohTbody = document.getElementById('dooh-selected-list');
    oohTbody.innerHTML = ''; doohTbody.innerHTML = '';

    if (selectedHoardings.size === 0) {
        oohTbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No static hoardings selected</td></tr>`;
        doohTbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>`;
    } else {
        let hasOoh = false, hasDooh = false;

        selectedHoardings.forEach((h, id) => {
            const totalPrice = calculateTieredPrice(h.price_per_month, h.startDate, h.endDate);
            const issue = availabilityIssues[id];
            const conflictClass = issue ? 'availability-conflict' : '';
            const conflictBadge = issue ? `<span class="inline-block mt-1 text-[9px] font-bold text-red-600 bg-red-100 px-1.5 py-0.5 rounded">${issue.label}</span>` : '';

            const isDooh = h.type?.toUpperCase() === 'DOOH';

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
                        <td class="px-4 py-3 text-xs text-gray-500">${formatINR(h.price_per_month)}</td>
                        <td class="px-4 py-3 text-center">
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
                        <td class="px-4 py-3 font-bold text-xs text-green-700">${formatINR(totalPrice)}</td>
                        <td class="px-4 py-3 text-right">
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
                        <td class="px-4 py-3 text-xs text-gray-500">${formatINR(h.price_per_month)}</td>
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
                        <td class="px-4 py-3 font-bold text-xs text-green-700">${formatINR(totalPrice)}</td>
                        <td class="px-4 py-3 text-right">
                            <button onclick="toggleHoarding(${h.id})" class="text-red-500 cursor-pointer">
                                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>`;
            }
        });

        if (!hasOoh) oohTbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No static hoardings selected</td></tr>`;
        if (!hasDooh) doohTbody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>`;
    }
    document.getElementById('btn-count').innerText = selectedHoardings.size;
}

function updateDate(id, field, value) {
    let h = selectedHoardings.get(id);
    if(h) {
        h[field] = value;
        if(field === 'startDate' && h.endDate < value) h.endDate = value;
        selectedHoardings.set(id, h);
        updateSummary();
    }
}

/* --- Availability check helpers --- */
function statusLabel(status) {
    const map = { booked: 'Already Booked', blocked: 'Blocked/Maintenance', hold: 'On Hold', partial: 'Partially Unavailable' };
    return map[status] || status;
}

/**
 * Run availability checks for ALL selected hoardings.
 * Populates `availabilityIssues` and returns true if ALL are available.
 */
async function checkAllAvailability() {
    availabilityIssues = {};
    let allClear = true;

    const checks = Array.from(selectedHoardings.entries()).map(async ([id, h]) => {
        const allDates = enumerateDatesBetween(h.startDate, h.endDate);
        try {
            const res = await fetch(`/api/v1/hoardings/${id}/availability/check-dates`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ dates: allDates })
            });
            if (!res.ok) return;
            const result = await res.json();
            const conflicts = (result.data?.results || []).filter(r => r.status !== 'available');
            if (conflicts.length > 0) {
                allClear = false;
                const statuses = [...new Set(conflicts.map(c => c.status))];
                availabilityIssues[id] = {
                    title: h.title,
                    label: statuses.map(statusLabel).join(', '),
                    conflicts
                };
            }
        } catch (e) {
            console.error('Availability check error for hoarding', id, e);
        }
    });

    await Promise.all(checks);
    return allClear;
}

function showAvailabilityAlert(issues) {
    const alert = document.getElementById('availability-alert');
    const body = document.getElementById('availability-alert-body');
    const entries = Object.values(issues);

    if (entries.length === 0) { alert.classList.add('hidden'); return; }

    body.innerHTML = entries.map(issue => `
        <div class="flex items-start gap-2 py-1">
            <span class="font-bold text-red-700">${issue.title}:</span>
            <span>${issue.label} — please adjust the booking dates.</span>
        </div>
    `).join('');

    alert.classList.remove('hidden');
    alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/* --- Calendar modal & availability integration --- */
let currentFlatpickr = null;
let currentHeatmapMap = {};
let currentEditingHoardingId = null;

function toYMD(d) { return toLocalYMD(d); }

function enumerateDatesBetween(start, end) {
    const dates = [];
    let cur = new Date(start);
    const last = new Date(end);
    while (cur <= last) {
        dates.push(toYMD(cur));
        cur.setDate(cur.getDate() + 1);
    }
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
    const future = new Date(); future.setDate(future.getDate() + 365);
    const endStr = toLocalYMD(future);

    try {
        const res = await fetch(`/api/v1/hoardings/${id}/availability/heatmap?start_date=${startStr}&end_date=${endStr}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
            showToast(`Could not load availability (HTTP ${res.status}). Please try again.`);
            return;
        }
        const payload = await res.json();
        const heatmap = payload.data?.heatmap || [];

        const disabledDates = heatmap.filter(d => d.status !== 'available').map(d => d.date);
        currentHeatmapMap = {};
        heatmap.forEach(d => currentHeatmapMap[d.date] = d.status);

        if (currentFlatpickr) currentFlatpickr.destroy();

        currentFlatpickr = flatpickr('#date-picker-input', {
            mode: 'range',
            inline: true,
            appendTo: document.getElementById('date-picker-inline'),
            minDate: startStr,
            disable: disabledDates,
            defaultDate: [h.startDate, h.endDate],
            showMonths: 2,
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const date = toLocalYMD(dayElem.dateObj);
                const status = currentHeatmapMap[date];
                if (status && status !== 'available') {
                    dayElem.classList.add(status);
                    dayElem.title = status.charAt(0).toUpperCase() + status.slice(1);
                }
            }
        });
    } catch (e) {
        console.error(e);
        showToast('Could not load availability. Please try again.');
    }
}

function closeDatePickerModal() {
    document.getElementById('datePickerModal').classList.add('hidden');
}

async function confirmDateSelection() {
    if (!currentFlatpickr || !currentEditingHoardingId) return closeDatePickerModal();
    const dates = currentFlatpickr.selectedDates;
    if (!dates || dates.length === 0) { showToast('Please select a start and end date'); return; }

    const start = toYMD(dates[0]);
    const end = toYMD(dates.length === 1 ? dates[0] : dates[1]);
    const allDates = enumerateDatesBetween(start, end);

    try {
        const res = await fetch(`/api/v1/hoardings/${currentEditingHoardingId}/availability/check-dates`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ dates: allDates })
        });
        if (!res.ok) {
            showToast('Could not verify availability. Please try again.');
            openDatePickerForHoarding(currentEditingHoardingId);
            return;
        }
        const result = await res.json();
        const conflicts = (result.data?.results || []).filter(r => r.status !== 'available');
        if (conflicts.length > 0) {
            showToast('Selected range includes unavailable dates. Please choose a different range.', 'warning');
            openDatePickerForHoarding(currentEditingHoardingId);
            return;
        }

        let h = selectedHoardings.get(currentEditingHoardingId);
        if (h) {
            h.startDate = start;
            h.endDate = end;
            selectedHoardings.set(currentEditingHoardingId, h);
            // Clear any existing conflict for this hoarding since dates changed
            delete availabilityIssues[currentEditingHoardingId];
            updateSummary();
        }

        closeDatePickerModal();
    } catch (e) {
        console.error(e);
        showToast('Error checking availability.');
    }
}

// Expose helpers globally
window.enumerateDatesBetween = enumerateDatesBetween;
window.toYMD = toYMD;

window.finalCheckAvailability = async function() {
    return await checkAllAvailability();
};

/* --- SUBMIT BUTTON: Check availability first, then show preview --- */
document.getElementById('submit-btn').addEventListener('click', async () => {
    if (!selectedCustomer) { showToast('Select a customer.', 'warning'); return; }
    if (selectedHoardings.size === 0) { showToast('Select at least one hoarding.', 'warning'); return; }

    const btn = document.getElementById('submit-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-4 h-4 inline animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Checking Availability...`;

    const allClear = await checkAllAvailability();

    btn.disabled = false;
    btn.innerHTML = originalText;

    if (!allClear) {
        updateSummary(); // Re-render rows with conflict highlights
        showAvailabilityAlert(availabilityIssues);
        showToast('Some hoardings have availability conflicts. Please review and adjust dates.', 'error');
        return;
    }

    // All clear — hide any existing alert and proceed to preview
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

/* --- POPULATE PREVIEW --- */
let globalBaseAmount = 0;

function populatePreview() {
    if (!selectedCustomer) return;

    const previewCustName = document.getElementById('preview-cust-name');
    const previewCustPhone = document.getElementById('preview-cust-phone');
    const previewTotalCount = document.getElementById('preview-total-count');
    const oohTbody = document.getElementById('preview-ooh-list');
    const doohTbody = document.getElementById('preview-dooh-list');

    if (!previewCustName || !previewCustPhone || !previewTotalCount || !oohTbody || !doohTbody) {
        console.error('Preview DOM elements missing!');
        return;
    }

    previewCustName.innerText = selectedCustomer.name;
    previewCustPhone.innerText = selectedCustomer.phone;
    previewTotalCount.innerText = selectedHoardings.size;

    oohTbody.innerHTML = '';
    doohTbody.innerHTML = '';

    globalBaseAmount = 0;

    selectedHoardings.forEach((h) => {
        const itemTotal = calculateTieredPrice(h.price_per_month, h.startDate, h.endDate);
        globalBaseAmount += itemTotal;
        const isDooh = h.type?.toUpperCase() === 'DOOH';
        const slotsCell = isDooh ? `<div class="text-[10px] text-purple-600 font-medium mt-0.5">${h.total_slots_per_day ?? 300} slots/day</div>` : '';

        const row = `
            <tr class="border-b border-gray-50">
                <td class="px-8 py-4">
                    <div class="flex items-center gap-4">
                        <img src="${h.image_url || '/placeholder.png'}" class="w-12 h-12 rounded-lg object-cover border border-gray-100">
                        <div>
                            <p class="font-bold text-gray-800 text-[11px] uppercase">${h.title}</p>
                            <p class="text-[9px] text-gray-400 mt-0.5">${h.location_address || ''}</p>
                            ${slotsCell}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 text-xs font-medium text-gray-600">
                    ${h.startDate} - ${h.endDate}
                    <div class="text-[10px] text-gray-400 mt-1">${getTieredDurationLabel(h.startDate, h.endDate)}</div>
                </td>
                <td class="px-8 py-4 text-right font-bold text-gray-700 text-xs">${formatINR(itemTotal)}</td>
            </tr>`;

        if (isDooh) doohTbody.innerHTML += row;
        else oohTbody.innerHTML += row;
    });

    const sideSubTotal = document.getElementById('side-sub-total');
    if (sideSubTotal) sideSubTotal.innerText = formatINR(globalBaseAmount);
    calculateFinalTotals();
}
</script>
<script>
    function openFilterModal() {
        document.getElementById('filterModal').classList.remove('hidden');
    }
    function closeFilterModal() {
        document.getElementById('filterModal').classList.add('hidden');
    }
</script>

@endsection