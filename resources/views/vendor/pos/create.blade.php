@extends('layouts.vendor')

@section('title', 'Create Pos Booking')

@section('content')
<div class="px-6 py-6 bg-gray-50">
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
                                            <th class="px-4 py-3 font-semibold">Total</th>
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
                                            <th class="px-4 py-3 font-semibold">Duration</th>
                                            <th class="px-4 py-3 font-semibold">Total</th>
                                            <th class="px-4 py-3 font-semibold text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>
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
</style>

<script>
/* --- CONFIG & STATE --- */
// SweetAlert2 toast helper
function showToast(message) {
    if (window.Swal) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            icon: 'info',
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
// Pagination state
let currentPage = 1;
let totalPages = 1;
let perPage = 10;



const formatINR = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val);

/**
 * Tier Logic helpers
 * - getTieredMonths: returns integer number of months to charge (inclusive, rounds up by 30-day buckets)
 * - getTieredDurationLabel: returns "1 Month" or "N Months"
 * - calculateTieredPrice: uses the months to compute total
 */
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

    // Auto-select customer if passed in URL
    const urlParams = new URLSearchParams(window.location.search);
    const customerId = urlParams.get('customer_id');
    if (customerId) {
        try {
            // Try to fetch by ID directly
            const res = await fetchJSON(`${API_URL}/customers?search=${customerId}`);
            const list = res.data?.data || res.data || [];
            // Try to match by id or phone (in case search returns by phone)
            let found = list.find(c => c.id == customerId);
            // If not found, try to fetch all customers and match by id
            if (!found) {
                const allRes = await fetchJSON(`${API_URL}/customers`);
                const allList = allRes.data?.data || allRes.data || [];
                found = allList.find(c => c.id == customerId);
            }
            if (found) {
                selectCustomer(found);
            }
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

// function selectCustomer(c) {
// console.log('Customer object:', c);
//  if (!c || !c.name) {
//         console.warn('Invalid customer object passed to selectCustomer', c);
//         return;
//     }

//     selectedCustomer = c;
//     document.getElementById('search-container').classList.add('hidden');
//     document.getElementById('customer-selected-card').classList.remove('hidden');
//     document.getElementById('cust-name').innerText = c.name;
//     document.getElementById('cust-details').innerText = `${c.email || ''} | ${c.phone}`;
//     document.getElementById('cust-initials').innerText = c.name.substring(0,2).toUpperCase();
//     document.getElementById('customer-suggestions').classList.add('hidden');
//     document.getElementById('customer_gstin').classList.add('hidden');
// }

function toLocalYMD(date) {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}


function selectCustomer(c) {
    console.log('Customer object:', c);

    if (!c || !c.name) {
        console.warn('Invalid customer object passed to selectCustomer', c);
        return;
    }

    selectedCustomer = c;

    const searchContainer = document.getElementById('search-container');
    const selectedCard = document.getElementById('customer-selected-card');
    const suggestions = document.getElementById('customer-suggestions');
    const nameEl = document.getElementById('cust-name');
    const detailsEl = document.getElementById('cust-details');
    const initialsEl = document.getElementById('cust-initials');

    if (searchContainer) searchContainer.classList.add('hidden');
    if (selectedCard) selectedCard.classList.remove('hidden');
    if (suggestions) suggestions.classList.add('hidden');

    if (nameEl) nameEl.innerText = c.name;
    if (detailsEl) detailsEl.innerText = `${c.email || ''} | ${c.phone || ''}`;
    if (initialsEl) initialsEl.innerText = c.name.slice(0, 2).toUpperCase();
}


function clearSelectedCustomer() {
    selectedCustomer = null;
    document.getElementById('search-container').classList.remove('hidden');
    document.getElementById('customer-selected-card').classList.add('hidden');
}

/* --- INVENTORY LOGIC --- */


async function loadHoardings(filters = {}) {
    filters.page = currentPage;
    filters.per_page = perPage;
    const query = new URLSearchParams(filters).toString();
    const url   = `${API_URL}/hoardings${query ? '?' + query : ''}`;
    const res   = await fetchJSON(url);
    // Read pagination metadata from top-level API response
    if ('last_page' in res) {
        hoardings = res.data || [];
        totalPages = res.last_page || 1;
        currentPage = res.current_page || 1;
    } else if (res.data && typeof res.data === 'object' && 'data' in res.data && 'last_page' in res.data) {
        hoardings = res.data.data;
        totalPages = res.data.last_page || 1;
    } else if (Array.isArray(res.data)) {
        hoardings = res.data;
        totalPages = 1;
    } else {
        hoardings = res.data?.data || res.data || [];
        totalPages = 1;
    }
    renderHoardings(hoardings);
    renderPagination();
}

window.loadHoardings = loadHoardings;


function renderHoardings(list) {
    const grid = document.getElementById('hoardings-grid');
    if (!list.length) {
        grid.innerHTML = '<div class="col-span-2 text-center text-gray-400 italic py-8 w-full">No hoardings found</div>';
    } else {
        grid.innerHTML = list.map(h => {
            const isSelected = selectedHoardings.has(h.id);
            return `
                <div class="relative bg-white border ${isSelected ? 'border-green-500 ring-1 ring-green-500' : 'border-gray-200'} overflow-hidden cursor-pointer" onclick="toggleHoarding(${h.id})">
                    <img src="${h.image_url || '/placeholder.png'}" class="w-full h-20 object-cover">
                    <div class="p-2">
                        <h4 class="text-[10px] font-bold text-gray-800 truncate">${h.title}</h4>
                        <span class="text-[10px] font-bold">${formatINR(h.price_per_month)}/M</span>
                    </div>
                </div>`;
        }).join('');
    }
    document.getElementById('available-count').innerText = list.length;
}

function renderPagination() {
    let container = document.getElementById('hoardings-pagination');
    if (!container) {
        // Create container if not present
        const parent = document.querySelector('#hoardings-grid')?.parentElement;
        if (!parent) return;
        container = document.createElement('div');
        container.id = 'hoardings-pagination';
        container.className = 'flex justify-center items-center gap-2 mt-4';
        parent.appendChild(container);
    }
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }
    let html = '';
    html += `<button class="px-2 py-1 border rounded text-xs ${currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white'}" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>`;
    // Show up to 5 page numbers
    let start = Math.max(1, currentPage - 2);
    let end = Math.min(totalPages, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);
    for (let i = start; i <= end; i++) {
        html += `<button class="px-2 py-1 border rounded text-xs ${i === currentPage ? 'bg-green-600 text-white' : 'bg-white'}" onclick="changePage(${i})">${i}</button>`;
    }
    html += `<button class="px-2 py-1 border rounded text-xs ${currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white'}" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
    container.innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;
    currentPage = page;
    loadHoardings();
}


function filterInventory() {
    const q = document.getElementById('hoarding-search').value.toLowerCase();
    // Reset to first page on new search
    currentPage = 1;
    loadHoardings(q ? { search: q } : {});
}

function toggleHoarding(id) {
    if (selectedHoardings.has(id)) {
        selectedHoardings.delete(id);
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
        const empty = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">No selections</td></tr>`;
        oohTbody.innerHTML = empty; doohTbody.innerHTML = empty;
    } else {
        selectedHoardings.forEach((h, id) => {
            const totalPrice = calculateTieredPrice(h.price_per_month, h.startDate, h.endDate);
            const row = `
                <tr class="hover:bg-gray-50 border-b border-gray-200">
                    <td class="px-4 py-3">
                        <div class="text-xs font-bold text-gray-800">${h.title}</div>
                        <div class="text-[9px] text-gray-400 truncate w-32">${h.location_address || ''}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">${formatINR(h.price_per_month)}</td>
                  <td class="px-4 py-3 align-middle">
                        <div class="flex flex-col items-center justify-center">
                            <div class="flex items-center justify-center whitespace-nowrap">
                                <button 
                                    onclick="openDatePickerForHoarding(${h.id})"
                                    class="px-2 py-1 rounded bg-white text-[11px] font-semibold text-gray-700 hover:text-blue-600 transition leading-none">
                                    ${toLocalYMD(h.startDate)} - ${toLocalYMD(h.endDate)}
                                </button>
                                <button 
                                    onclick="openDatePickerForHoarding(${h.id})"
                                    class="flex items-center justify-center w-6 h-6 rounded-full text-blue-500 hover:text-blue-700 transition"
                                    title="Edit Dates">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M15.232 5.232l3.536 3.536M9 13l6.536-6.536a2 2 0 112.828 2.828L11.828 15.828a2 2 0 01-2.828 0L9 13z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-[11px] text-gray-400 mt-1 text-center leading-none">
                                ${getTieredDurationLabel(h.startDate, h.endDate)}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-bold text-xs text-green-700">${formatINR(totalPrice)}</td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="toggleHoarding(${h.id})" class="text-red-500 cursor-pointer">
                           <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>`;
            if (h.type?.toUpperCase() === 'DOOH') doohTbody.innerHTML += row; else oohTbody.innerHTML += row;
        });
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

// --- Calendar modal & availability integration ---
let currentFlatpickr = null;
let currentHeatmapMap = {};
let currentEditingHoardingId = null;

function toYMD(d) {
    return toLocalYMD(d);
}


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

    // set modal title
    document.getElementById('datePickerTitle').innerText = h.title;
    document.getElementById('datePickerModal').classList.remove('hidden');

    const today = toLocalYMD(new Date());
    const startStr = toLocalYMD(new Date());
    const future = new Date(); future.setDate(future.getDate() + 365);
    const endStr = toLocalYMD(future);

    try {
        const res = await fetch(`/api/v1/hoardings/${id}/availability/heatmap?start_date=${startStr}&end_date=${endStr}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
            const txt = await res.text().catch(() => '');
            console.error('Heatmap fetch failed', res.status, res.statusText, txt);
            if (res.status === 401 || res.status === 403) {
                showToast('Could not load availability (authentication required). Please login and try again.');
            } else {
                showToast(`Could not load availability (HTTP ${res.status}). Please try again.`);
            }
            return;
        }
        const payload = await res.json();
        const heatmap = payload.data?.heatmap || [];

        const disabledDates = heatmap.filter(d => d.status !== 'available').map(d => d.date);
        currentHeatmapMap = {};
        heatmap.forEach(d => currentHeatmapMap[d.date] = d.status);

        // Initialize or reinitialize flatpickr
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

    // Double-check availability via API
    const allDates = enumerateDatesBetween(start, end);
    try {
        const res = await fetch(`/api/v1/hoardings/${currentEditingHoardingId}/availability/check-dates`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ dates: allDates })
        });
        if (!res.ok) {
            const text = await res.text();
            console.error('Availability check failed', res.status, res.statusText, text);
            showToast('Could not verify availability (server error). Please try again.');
            openDatePickerForHoarding(currentEditingHoardingId);
            return;
        }
        const result = await res.json();
        const conflicts = (result.data?.results || []).filter(r => r.status !== 'available');
        console.log('response BEDORE booking for hoarding', currentEditingHoardingId, conflicts);
        if (conflicts.length > 0) {
            showToast('Selected range includes unavailable dates. Please choose a different range.');
            // refresh calendar highlights
            openDatePickerForHoarding(currentEditingHoardingId);
            return;
        }

        // Save dates
        let h = selectedHoardings.get(currentEditingHoardingId);
        if (h) {
            h.startDate = start;
            h.endDate = end;
            selectedHoardings.set(currentEditingHoardingId, h);
            updateSummary();
        }

        closeDatePickerModal();
    } catch (e) {
        console.error(e);
        showToast('Error checking availability.');
    }
}

// Expose helpers globally so preview script can reuse them
window.enumerateDatesBetween = enumerateDatesBetween;
window.toYMD = toYMD;

// Final availability check used by preview finalization
window.finalCheckAvailability = async function() {
    for (const [id, h] of selectedHoardings) {
        const dates = enumerateDatesBetween(h.startDate, h.endDate);
        try {
            const res = await fetch(`/api/v1/hoardings/${id}/availability/check-dates`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ dates })
            });
            if (!res.ok) {
                const txt = await res.text();
                console.error('Final availability check failed', res.status, res.statusText, txt);
                showToast('Error checking final availability (server error). Please try again.');
                return false;
            }
            const result = await res.json();
            const conflicts = (result.data?.results || []).filter(r => r.status !== 'available');
            console.log('response after booking for hoarding', id, conflicts);
            // if (conflicts.length > 0) {
            //     alert(` ${h.title} has unavailable dates in the selected range. Please adjust dates.`);
            //     return false;
            // }
        } catch (e) {
            console.error(e);
            showToast('Error checking final availability. Please try again.');
            return false;
        }
    }
    return true;
};

// Add a capturing click listener to ensure final availability check runs before booking submit
document.getElementById('create-booking-btn')?.addEventListener('click', async function(e) {
    if (!(await window.finalCheckAvailability())) {
        e.stopImmediatePropagation();
        e.preventDefault();
        return false;
    }
}, true);

document.getElementById('submit-btn').addEventListener('click', () => {
    if (!selectedCustomer) { showToast("Select a customer."); return; }
    if (selectedHoardings.size === 0) { showToast("Select inventory."); return; }
    
    populatePreview();
    document.getElementById('selection-screen').classList.add('hidden');
    document.getElementById('preview-screen').classList.remove('hidden');
});

function backToSelection() {
    document.getElementById('preview-screen').classList.add('hidden');
    document.getElementById('selection-screen').classList.remove('hidden');
}

function debounce(fn, t) { let timer; return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), t); }; }



// /* --- GLOBAL CALCULATOR FOR POS --- */
// let globalBaseAmount = 0; // The total before discount/tax

// function calculateFinalTotals() {
//     const discountInput = document.getElementById('pos-discount');
//     const discountVal = parseFloat(discountInput.value) || 0;
    
//     // 1. Logic: Subtotal - Discount = Taxable Amount
//     const taxableAmount = Math.max(0, globalBaseAmount - discountVal);
//     const tax = taxableAmount * 0.18;
//     const grandTotal = taxableAmount + tax;

//     // 2. Update UI
//     document.getElementById('side-discount-display').innerText = `- ${formatINR(discountVal)}`;
//     document.getElementById('side-taxable-amount').innerText = formatINR(taxableAmount);
//     document.getElementById('side-tax').innerText = formatINR(tax);
//     document.getElementById('side-grand-total').innerText = formatINR(grandTotal);
// }

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

    // Customer Info
    previewCustName.innerText = selectedCustomer.name;
    previewCustPhone.innerText = selectedCustomer.phone;
    previewTotalCount.innerText = selectedHoardings.size;

    // Clear tables
    oohTbody.innerHTML = '';
    doohTbody.innerHTML = '';

    globalBaseAmount = 0;

    selectedHoardings.forEach((h) => {
        const itemTotal = calculateTieredPrice(h.price_per_month, h.startDate, h.endDate);
        globalBaseAmount += itemTotal;

        const row = `
            <tr class="border-b border-gray-50">
                <td class="px-8 py-4">
                    <div class="flex items-center gap-4">
                        <img src="${h.image_url || '/placeholder.png'}" class="w-12 h-12 rounded-lg object-cover border border-gray-100">
                        <div>
                            <p class="font-bold text-gray-800 text-[11px] uppercase">${h.title}</p>
                            <p class="text-[9px] text-gray-400 mt-0.5">${h.location_address || ''}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 text-xs font-medium text-gray-600">
                    ${h.startDate} - ${h.endDate}
                    <div class="text-[10px] text-gray-400 mt-1">${getTieredDurationLabel(h.startDate, h.endDate)}</div>
                </td>
                <td class="px-8 py-4 text-right font-bold text-gray-700 text-xs">${formatINR(itemTotal)}</td>
            </tr>`;

        if (h.type?.toUpperCase() === 'DOOH') doohTbody.innerHTML += row;
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