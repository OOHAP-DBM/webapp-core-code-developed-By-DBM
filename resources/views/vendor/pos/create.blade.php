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
                            <div class="relative flex-1">
                                <input type="text" id="customer-search" autocomplete="off" 
                                    placeholder="Search by name, email, or mobile..." 
                                    class="w-full rounded-md border-gray-300 focus:ring-green-500 text-sm py-2.5">
                                <div id="customer-suggestions" class="absolute z-50 w-full bg-white border rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto"></div>
                            </div>
                            <button type="button" onclick="openCustomerModal()" class="bg-green-600 text-white px-4 rounded-md hover:bg-green-700 transition flex items-center">
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
                            <div class="overflow-x-auto border border-gray-100 rounded-xl">
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
                            <div class="overflow-x-auto border border-gray-100 rounded-xl">
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
                        <button type="button" onclick="location.reload()" class="px-6 py-3 border border-gray-200 rounded-lg font-bold text-gray-500 hover:bg-gray-50 transition">Reset</button>
                        <button id="submit-btn" class="flex-1 py-3 bg-[#2D5A43] text-white rounded-lg font-bold shadow-lg shadow-green-900/20 hover:bg-opacity-90 active:scale-[0.98] transition">
                            Preview & Create Booking (<span id="btn-count">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-6">
                <div class="p-5 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Inventory Available</h3>
                    <span class="bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full text-xs font-bold" id="available-count">0</span>
                </div>
                <div class="p-5">
                    <div class="relative mb-5">
                        <input type="text" id="hoarding-search" placeholder="Filter inventory..." 
                            class="w-full pl-10 rounded-lg border-gray-200 text-sm focus:ring-green-500">
                        <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
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

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<script>
/* --- CONFIG & STATE --- */
const API_URL = '/vendor/pos/api';
let hoardings = [];
let selectedHoardings = new Map();
let selectedCustomer = null;



const formatINR = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 0 }).format(val);

/**
 * Tier Logic: 1-30 days = 1 Month Rent, 31-60 = 2 Months Rent, etc.
 */
function calculateTieredPrice(pricePerMonth, startDate, endDate) {
    if (!startDate || !endDate) return 0;
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 

    const monthsToCharge = Math.ceil(diffDays / 30);
    return pricePerMonth * monthsToCharge;
}

const fetchJSON = async (url, options = {}) => {
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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

function selectCustomer(c) {
    selectedCustomer = c;
    document.getElementById('search-container').classList.add('hidden');
    document.getElementById('customer-selected-card').classList.remove('hidden');
    document.getElementById('cust-name').innerText = c.name;
    document.getElementById('cust-details').innerText = `${c.email || ''} | ${c.phone}`;
    document.getElementById('cust-initials').innerText = c.name.substring(0,2).toUpperCase();
    document.getElementById('customer-suggestions').classList.add('hidden');
    document.getElementById('customer_gstin').classList.add('hidden');
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    document.getElementById('search-container').classList.remove('hidden');
    document.getElementById('customer-selected-card').classList.add('hidden');
}

/* --- INVENTORY LOGIC --- */
async function loadHoardings() {
    const res = await fetchJSON(`${API_URL}/hoardings`);
    hoardings = res.data?.data || res.data || [];
    renderHoardings(hoardings);
}

function renderHoardings(list) {
    const grid = document.getElementById('hoardings-grid');
    grid.innerHTML = list.map(h => {
        const isSelected = selectedHoardings.has(h.id);
        return `
            <div class="relative bg-white border ${isSelected ? 'border-green-500 ring-1 ring-green-500' : 'border-gray-200'} rounded-xl overflow-hidden cursor-pointer" onclick="toggleHoarding(${h.id})">
                <img src="${h.image_url || '/placeholder.png'}" class="w-full h-20 object-cover">
                <div class="p-2">
                    <h4 class="text-[10px] font-bold text-gray-800 truncate">${h.title}</h4>
                    <span class="text-[10px] text-green-700 font-bold">${formatINR(h.price_per_month)}/mo</span>
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
    } else {
        const h = hoardings.find(i => i.id === id);
        const today = new Date().toISOString().split('T')[0];
        let end = new Date(); end.setDate(end.getDate() + 29);
        selectedHoardings.set(id, { ...h, startDate: today, endDate: end.toISOString().split('T')[0] });
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
                <tr class="hover:bg-gray-50 border-b">
                    <td class="px-4 py-3">
                        <div class="text-xs font-bold text-gray-800">${h.title}</div>
                        <div class="text-[9px] text-gray-400 truncate w-32">${h.location_address || ''}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">${formatINR(h.price_per_month)}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <input type="date" value="${h.startDate}" onchange="updateDate(${id}, 'startDate', this.value)" class="text-[10px] p-1 border rounded bg-white">
                            <span class="text-gray-400">-</span>
                            <input type="date" value="${h.endDate}" onchange="updateDate(${id}, 'endDate', this.value)" class="text-[10px] p-1 border rounded bg-white">
                        </div>
                    </td>
                    <td class="px-4 py-3 font-bold text-xs text-green-700">${formatINR(totalPrice)}</td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="toggleHoarding(${h.id})" class="text-gray-300 hover:text-red-500">
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

document.getElementById('submit-btn').addEventListener('click', () => {
    if (!selectedCustomer) return alert("Select a customer.");
    if (selectedHoardings.size === 0) return alert("Select inventory.");
    
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
                <td class="px-4 py-4 text-xs font-medium text-gray-600">${h.startDate} - ${h.endDate}</td>
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



@endsection