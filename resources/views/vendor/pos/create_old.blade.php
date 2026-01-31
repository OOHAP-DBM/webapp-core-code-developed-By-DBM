
for customer create and select this code was working properly @extends('layouts.vendor')

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
                            <button onclick="clearSelectedCustomer()" class="text-xs font-bold text-red-500 hover:text-red-700 px-3 py-1 border border-red-200 rounded-md bg-white">
                                Change
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="selection-group">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2"></span> OOH (Static)
                            </h4>
                            <div class="overflow-hidden border border-gray-100 rounded-xl">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Hoarding</th>
                                            <th class="px-4 py-3 font-semibold">Rental/Mo</th>
                                            <th class="px-4 py-3 font-semibold">Total</th>
                                            <th class="px-4 py-3 font-semibold text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">No static hoardings selected</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="selection-group">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 flex items-center">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2"></span> Digital (DOOH)
                            </h4>
                            <div class="overflow-hidden border border-gray-100 rounded-xl">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Hoarding</th>
                                            <th class="px-4 py-3 font-semibold">Slot Info</th>
                                            <th class="px-4 py-3 font-semibold">Total</th>
                                            <th class="px-4 py-3 font-semibold text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">No digital slots selected</td></tr>
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
                        <span class="absolute left-3 top-2.5 text-gray-400 text-lg">🔍</span>
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
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }
    @keyframes slide-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .animate-slide-up { animation: slide-up 0.3s ease-out forwards; }
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

const fetchJSON = async (url, options = {}) => {
    const res = await fetch(url, {
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        ...options
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw { status: res.status, data };
    return data;
};

/* --- INITIALIZATION --- */
document.addEventListener('DOMContentLoaded', async () => {
    document.getElementById('booking-date').innerText = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    await loadHoardings();

    document.getElementById('hoarding-search').addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        renderHoardings(hoardings.filter(h => h.title.toLowerCase().includes(q) || (h.location_address || '').toLowerCase().includes(q)));
    });

    document.getElementById('customer-search').addEventListener('input', debounce(handleCustomerSearch, 300));
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
        
        box.innerHTML = list.length > 0 
            ? list.map(c => `
                <div class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b last:border-0" 
                     onclick='selectCustomer(${JSON.stringify(c).replace(/'/g, "&apos;")})'>
                    <div class="text-sm font-bold text-gray-800">${c.name}</div>
                    <div class="text-[10px] text-gray-500">${c.email || 'No Email'} | ${c.phone}</div>
                </div>`).join('')
            : `<div class="p-4 text-xs text-gray-400 italic">No customer found.</div>`;
        box.classList.remove('hidden');
    } catch (e) { console.error("Search failed", e); }
}

function selectCustomer(c) {
    selectedCustomer = c;
    document.getElementById('search-container').classList.add('hidden');
    document.getElementById('customer-selected-card').classList.remove('hidden');
    document.getElementById('cust-name').innerText = c.name;
    document.getElementById('cust-details').innerText = `${c.email || ''} | ${c.phone}`;
    document.getElementById('cust-initials').innerText = c.name.substring(0,2).toUpperCase();
    document.getElementById('customer-suggestions').classList.add('hidden');
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    document.getElementById('search-container').classList.remove('hidden');
    document.getElementById('customer-selected-card').classList.add('hidden');
    document.getElementById('customer-search').value = '';
}

/* --- INVENTORY LOGIC --- */
async function loadHoardings() {
    try {
        const res = await fetchJSON(`${API_URL}/hoardings`);
        hoardings = res.data?.data || res.data || [];
        renderHoardings(hoardings);
        document.getElementById('available-count').innerText = hoardings.length;
    } catch (e) { console.error(e); }
}

function renderHoardings(list) {
    const grid = document.getElementById('hoardings-grid');
    grid.innerHTML = list.map(h => {
        const sel = selectedHoardings.has(h.id);
        return `
            <div class="relative bg-white border rounded-xl overflow-hidden cursor-pointer hover:shadow-lg transition-all ${sel ? 'ring-2 ring-[#2D5A43] border-transparent' : 'border-gray-100'}" onclick="toggleHoarding(${h.id})">
                <img src="${h.image_url || '/placeholder.png'}" class="w-full h-24 object-cover">
                <div class="p-3">
                    <h4 class="text-[11px] font-bold text-gray-800 truncate">${h.title}</h4>
                    <p class="text-[10px] text-gray-400 truncate mb-2">${h.location_address || 'Unknown'}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded uppercase">${h.type}</span>
                        <span class="text-[11px] font-black text-gray-700">${formatINR(h.price_per_month)}</span>
                    </div>
                </div>
                ${sel ? `<div class="absolute top-2 right-2 bg-[#2D5A43] text-white p-1 rounded-full shadow-lg"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg></div>` : ''}
            </div>`;
    }).join('');
}

function toggleHoarding(id) {
    if (selectedHoardings.has(id)) selectedHoardings.delete(id);
    else selectedHoardings.set(id, hoardings.find(i => i.id === id));
    updateSummary();
}

/* --- INVENTORY LOGIC UPDATED --- */
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
            const isDooh = h.type.toUpperCase() === 'DOOH';
            // Default dates if not set: Start today, End in 1 month
            if(!h.startDate) h.startDate = new Date().toISOString().split('T')[0];
            if(!h.endDate) {
                let d = new Date();
                d.setMonth(d.getMonth() + 1);
                h.endDate = d.toISOString().split('T')[0];
            }

            const row = `
                <tr class="group hover:bg-gray-50 transition border-b">
                    <td class="px-4 py-3">
                        <div class="text-xs font-bold text-gray-800">${h.title}</div>
                        <div class="text-[10px] text-gray-400">${h.location_address}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1">
                            <input type="date" value="${h.startDate}" onchange="updateHoardingDate(${id}, 'startDate', this.value)" class="text-[10px] p-1 border rounded w-28">
                            <input type="date" value="${h.endDate}" onchange="updateHoardingDate(${id}, 'endDate', this.value)" class="text-[10px] p-1 border rounded w-28">
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 font-medium">${formatINR(h.price_per_month)}</td>
                    <td class="px-4 py-3 font-bold text-xs text-[#2D5A43]">${formatINR(h.price_per_month)}</td>
                    <td class="px-4 py-3 text-right">
                        <button onclick="toggleHoarding(${h.id})" class="text-gray-300 hover:text-red-500 transition">
                            <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>`;
            if (isDooh) doohTbody.innerHTML += row; else oohTbody.innerHTML += row;
        });
    }
    document.getElementById('btn-count').innerText = selectedHoardings.size;
}

function updateHoardingDate(id, field, value) {
    let h = selectedHoardings.get(id);
    if(h) {
        h[field] = value;
        selectedHoardings.set(id, h);
        
        // If we are on the preview screen, refresh the totals
        if (!document.getElementById('preview-screen').classList.contains('hidden')) {
            populatePreview();
        }
    }
}

/* --- ENHANCED FILTER LOGIC --- */
function filterInventory() {
    const query = document.getElementById('hoarding-search').value.toLowerCase();
    
    const filtered = hoardings.filter(h => {
        const matchesSearch = h.title.toLowerCase().includes(query) || 
                              (h.location_address || '').toLowerCase().includes(query);
        return matchesSearch;
    });

    renderHoardings(filtered);
    document.getElementById('available-count').innerText = filtered.length;
}

// Hook into existing search input
document.getElementById('hoarding-search').addEventListener('input', debounce(filterInventory, 200));
// function updateSummary() {
//     renderHoardings(hoardings);
//     const oohTbody = document.getElementById('ooh-selected-list');
//     const doohTbody = document.getElementById('dooh-selected-list');
//     oohTbody.innerHTML = ''; doohTbody.innerHTML = '';

//     if (selectedHoardings.size === 0) {
//         const empty = `<tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">No selections</td></tr>`;
//         oohTbody.innerHTML = empty; doohTbody.innerHTML = empty;
//     } else {
//         selectedHoardings.forEach(h => {
//             const isDooh = h.type.toUpperCase() === 'DOOH';
//             const row = `
//                 <tr class="group hover:bg-gray-50 transition">
//                     <td class="px-4 py-3">
//                         <div class="text-xs font-bold text-gray-800">${h.title}</div>
//                         <div class="text-[10px] text-gray-400">${h.location_address}</div>
//                     </td>
//                     <td class="px-4 py-3 text-xs text-gray-600">${isDooh ? 'Standard Slot' : formatINR(h.price_per_month)}</td>
//                     <td class="px-4 py-3 font-bold text-xs text-gray-800">${formatINR(h.price_per_month)}</td>
//                     <td class="px-4 py-3 text-right">
//                         <button onclick="toggleHoarding(${h.id})" class="text-gray-300 hover:text-red-500 transition"><svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
//                     </td>
//                 </tr>`;
//             if (isDooh) doohTbody.innerHTML += row; else oohTbody.innerHTML += row;
//         });
//     }
//     document.getElementById('btn-count').innerText = selectedHoardings.size;
// }

/* --- VIEW SWITCHING & PREVIEW --- */
document.getElementById('submit-btn').addEventListener('click', () => {
    if (!selectedCustomer) return alert("Please select a customer first.");
    if (selectedHoardings.size === 0) return alert("Please select at least one hoarding.");
    
    populatePreview();
    document.getElementById('selection-screen').classList.add('hidden');
    document.getElementById('preview-screen').classList.remove('hidden');
    window.scrollTo(0, 0);
});

function backToSelection() {
    document.getElementById('preview-screen').classList.add('hidden');
    document.getElementById('selection-screen').classList.remove('hidden');
}

function populatePreview() {
    document.getElementById('preview-cust-name').innerText = selectedCustomer.name;
    document.getElementById('preview-cust-business').innerText = selectedCustomer.business_name || 'N/A';
    document.getElementById('preview-cust-email').innerText = selectedCustomer.email || 'N/A';
    document.getElementById('preview-cust-phone').innerText = selectedCustomer.phone;

    const oohTbody = document.getElementById('preview-ooh-list');
    const doohTbody = document.getElementById('preview-dooh-list');
    oohTbody.innerHTML = ''; 
    doohTbody.innerHTML = '';

    let oohTotal = 0, doohTotal = 0, snOoh = 1, snDooh = 1;

    // Added 'id' to the forEach parameters
    selectedHoardings.forEach((h, id) => {
        const price = Number(h.price_per_month) || 0;
        const isDooh = h.type.toUpperCase() === 'DOOH';
        
        const row = `
            <tr class="border-b border-gray-50">
                <td class="px-4 py-4 text-gray-400 text-xs">${isDooh ? snDooh++ : snOoh++}</td>
                <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <img src="${h.image_url || '/placeholder.png'}" class="w-8 h-8 rounded object-cover border">
                        <div>
                            <p class="font-bold text-gray-800 text-xs">${h.title}</p>
                            <p class="text-[9px] text-gray-400">${h.location_address || ''}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-1">
                             <input type="date" value="${h.startDate}" onchange="updateHoardingDate(${id}, 'startDate', this.value)" class="text-[10px] border rounded px-1 py-0.5 focus:ring-1 focus:ring-green-500 bg-white font-medium">
                             <span class="text-gray-400">-</span>
                             <input type="date" value="${h.endDate}" onchange="updateHoardingDate(${id}, 'endDate', this.value)" class="text-[10px] border rounded px-1 py-0.5 focus:ring-1 focus:ring-green-500 bg-white font-medium">
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 text-xs font-semibold text-gray-700">${formatINR(price)}</td>
                <td class="px-4 py-4 text-right font-bold text-[#2D5A43] text-xs">${formatINR(price)}</td>
            </tr>`;

        if (isDooh) { 
            doohTbody.innerHTML += row; 
            doohTotal += price; 
        } else { 
            oohTbody.innerHTML += row; 
            oohTotal += price; 
        }
    });

    // Calculations
    const subTotal = oohTotal + doohTotal;
    const tax = subTotal * 0.18;
    
    // Update Sidebar
    document.getElementById('side-ooh-total').innerText = formatINR(oohTotal);
    document.getElementById('side-dooh-total').innerText = formatINR(doohTotal);
    document.getElementById('side-sub-total').innerText = formatINR(subTotal);
    document.getElementById('side-tax').innerText = formatINR(tax);
    document.getElementById('side-grand-total').innerText = formatINR(subTotal + tax);
    
    // Update Badge Counts
    document.querySelectorAll('.ooh-count').forEach(el => el.innerText = `${snOoh - 1} Items`);
    document.querySelectorAll('.dooh-count').forEach(el => el.innerText = `${snDooh - 1} Items`);
}

function debounce(fn, t) { let timer; return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), t); }; }
</script>
@endsection