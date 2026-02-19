{{-- resources/views/admin/commission/vendor_hoardings.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="p-6">

    {{-- Vendor Details --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h2 class="text-base font-bold text-gray-900 mb-3">Vendor Details</h2>
        <div class="grid grid-cols-2 gap-y-1 text-sm text-gray-600">
            <div><span class="font-medium text-gray-800">Name:</span> {{ $vendor->name }}</div>
            <div><span class="font-medium text-gray-800">Business Name:</span> {{ $vendor->business_name ?? '—' }}</div>
            <div><span class="font-medium text-gray-800">GSTIN:</span> {{ $vendor->gstin ?? '—' }}</div>
            <div><span class="font-medium text-gray-800">Mobile Number:</span> {{ $vendor->phone ?? '—' }}</div>
            <div><span class="font-medium text-gray-800">Email:</span> {{ $vendor->email }}</div>
            <div><span class="font-medium text-gray-800">Address:</span> {{ $vendor->address ?? '—' }}</div>
        </div>
    </div>

    {{-- Header + Actions --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-900">All Hoardings ({{ $hoardings->total() }})</h2>
        <div class="flex gap-2">
            <button id="bulkSetCommissionBtn" onclick="openCommissionModal()"
                class="hidden px-5 py-2.5 bg-[#F97316] text-white rounded-xl font-semibold text-sm hover:bg-[#ea6c0a] transition">
                Set Commission
            </button>
            <button onclick="selectAllHoardings()"
                class="px-4 py-2.5 border border-gray-200 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-50 transition">
                Select All
            </button>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('admin.commission.vendor.hoardings', $vendor) }}" id="hoardingFilterForm">
        <div class="flex gap-3 mb-6">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by hoarding type, city, location..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-[#009A5C]">
            </div>

            <div class="relative">
                <select name="type" onchange="this.form.submit()"
                    class="appearance-none border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm outline-none focus:border-[#009A5C] bg-white">
                    <option value="">All Types</option>
                    <option value="ooh" {{ request('type') == 'ooh' ? 'selected' : '' }}>OOH</option>
                    <option value="dooh" {{ request('type') == 'dooh' ? 'selected' : '' }}>DOOH</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select name="state" onchange="this.form.submit()"
                    class="appearance-none border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm outline-none focus:border-[#009A5C] bg-white min-w-[130px]">
                    <option value="">All States</option>
                    @foreach($states as $s)
                        <option value="{{ $s }}" {{ request('state') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select name="city" onchange="this.form.submit()"
                    class="appearance-none border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm outline-none focus:border-[#009A5C] bg-white min-w-[130px]">
                    <option value="">All Cities</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <button type="submit"
                class="px-4 py-2.5 bg-[#E8F7F0] text-[#009A5C] rounded-xl border border-[#009A5C]/20 hover:bg-[#009A5C] hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
            </button>
        </div>
    </form>

    {{-- Hoardings Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <input type="checkbox" id="selectAllHoardings" class="rounded border-gray-300">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">HOARDING NAME</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">CITY</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">LOCATION</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($hoardings as $i => $hoarding)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <input type="checkbox" class="hoarding-checkbox rounded border-gray-300"
                            value="{{ $hoarding->id }}"
                            data-state="{{ $hoarding->state }}"
                            data-city="{{ $hoarding->city }}"
                            data-type="{{ $hoarding->hoarding_type }}">
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ str_pad($hoardings->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                @if($hoarding->image)
                                    <img src="{{ $hoarding->image }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 underline">{{ $hoarding->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span class="uppercase font-semibold text-[#F97316]">{{ $hoarding->hoarding_type }}</span>
                                    | {{ $hoarding->size ?? '' }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $hoarding->city }}</td>
                    <td class="px-6 py-4 text-gray-500 text-xs max-w-xs truncate">{{ $hoarding->display_location }}</td>
                    <td class="px-6 py-4">
                        <button onclick="openCommissionModalForHoarding({{ $hoarding->id }}, '{{ addslashes($hoarding->name) }}')"
                            class="px-4 py-1.5 bg-[#F97316] text-white rounded-lg text-xs font-semibold hover:bg-[#ea6c0a] transition">
                            Set Commission
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">No hoardings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <select class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
                <span class="text-sm text-gray-500">
                    Showing {{ $hoardings->firstItem() }} to {{ $hoardings->lastItem() }} of {{ $hoardings->total() }} records
                </span>
            </div>
            {{ $hoardings->links() }}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     COMMISSION MODAL FLOW (6 Steps)
══════════════════════════════════════════════════ --}}

{{-- MODAL 1: Apply to all hoarding types? --}}
<div id="commModal1" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <button onclick="closeAllModals()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <h2 class="text-lg font-bold text-gray-900 mb-1">Set Commission %</h2>
        <p class="text-sm text-gray-500 mb-6">Apply to all hoarding types (OOH + DOOH)?</p>

        {{-- Selected hoardings info --}}
        <div id="selectedInfo" class="mb-4 p-3 bg-blue-50 rounded-xl text-sm text-blue-700 hidden">
            <span id="selectedCount">0</span> hoarding(s) selected
        </div>

        <div class="flex gap-3 mb-6">
            <button onclick="setAllTypes(true)" id="allTypesYes"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                Yes — All Types
            </button>
            <button onclick="setAllTypes(false)" id="allTypesNo"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                No — Set Separately
            </button>
        </div>

        {{-- Global commission input (shown when Yes) --}}
        <div id="globalCommissionWrap" class="hidden mb-6">
            <label class="text-sm font-semibold text-gray-700 mb-2 block">Commission %</label>
            <div class="relative">
                <input type="number" id="globalCommission" min="0" max="100" step="0.01" placeholder="e.g. 15"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
            </div>
        </div>

        {{-- OOH + DOOH inputs (shown when No) --}}
        <div id="typeInputsWrap" class="hidden mb-6 space-y-4">
            <div>
                <label class="text-sm font-semibold text-gray-700 mb-2 block">OOH Commission %</label>
                <div class="relative">
                    <input type="number" id="oohCommission" min="0" max="100" step="0.01" placeholder="e.g. 12"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 mb-2 block">DOOH Commission %</label>
                <div class="relative">
                    <input type="number" id="doohCommission" min="0" max="100" step="0.01" placeholder="e.g. 18"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="closeAllModals()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Cancel</button>
            <button onclick="modal1Next()" class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">Next</button>
        </div>
    </div>
</div>

{{-- MODAL 2: Apply to all states? --}}
<div id="commModal2" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <h2 class="text-lg font-bold text-gray-900 mb-1">State Coverage</h2>
        <p class="text-sm text-gray-500 mb-6">Apply this commission to all states?</p>

        <div class="flex gap-3 mb-6">
            <button onclick="setAllStates(true)" id="allStatesYes"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                Yes — All States
            </button>
            <button onclick="setAllStates(false)" id="allStatesNo"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                No — By State
            </button>
        </div>

        {{-- Per-state inputs --}}
        <div id="stateInputsWrap" class="hidden mb-6 max-h-64 overflow-y-auto space-y-3"></div>

        <div class="flex gap-3">
            <button onclick="openModal('commModal1'); closeModal('commModal2')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Back</button>
            <button onclick="modal2Next()" class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">Next</button>
        </div>
    </div>
</div>

{{-- MODAL 3: Apply to all cities? --}}
<div id="commModal3" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg">
        <h2 class="text-lg font-bold text-gray-900 mb-1">City Coverage</h2>
        <p class="text-sm text-gray-500 mb-6">Apply this commission to all cities?</p>

        <div class="flex gap-3 mb-6">
            <button onclick="setAllCities(true)" id="allCitiesYes"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                Yes — All Cities
            </button>
            <button onclick="setAllCities(false)" id="allCitiesNo"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                No — By City
            </button>
        </div>

        <div id="cityInputsWrap" class="hidden mb-6 max-h-72 overflow-y-auto space-y-4"></div>

        <div class="flex gap-3">
            <button onclick="openModal('commModal2'); closeModal('commModal3')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Back</button>
            <button onclick="submitCommission()" id="finalSaveBtn"
                class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">
                Save Commission
            </button>
        </div>
    </div>
</div>

{{-- SUCCESS MODAL --}}
<div id="commModalSuccess" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-sm text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-[#009A5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Commission Saved!</h3>
        <p class="text-sm text-gray-500 mb-6">Commission settings have been applied successfully.</p>
        <button onclick="closeAllModals(); window.location.reload()"
            class="w-full py-2.5 bg-[#009A5C] text-white rounded-xl font-semibold text-sm">
            Done
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════
// STATE
// ═══════════════════════════════════════
const commState = {
    vendorId:        {{ $vendor->id }},
    hoardingIds:     [],
    applyAllTypes:   null,
    globalCommission: null,
    oohCommission:   null,
    doohCommission:  null,
    applyAllStates:  null,
    stateData:       [],
    applyAllCities:  null,
    cityData:        [],
};

const vendorStates = @json($states);

// ═══════════════════════════════════════
// CHECKBOX MANAGEMENT
// ═══════════════════════════════════════
document.getElementById('selectAllHoardings').addEventListener('change', function() {
    document.querySelectorAll('.hoarding-checkbox').forEach(cb => cb.checked = this.checked);
    updateBulkBtn();
});

document.querySelectorAll('.hoarding-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkBtn);
});

function updateBulkBtn() {
    const checked = document.querySelectorAll('.hoarding-checkbox:checked').length;
    const btn = document.getElementById('bulkSetCommissionBtn');
    if (checked > 0) {
        btn.classList.remove('hidden');
        btn.textContent = `Set Commission (${checked} selected)`;
    } else {
        btn.classList.add('hidden');
    }
}

function selectAllHoardings() {
    document.querySelectorAll('.hoarding-checkbox').forEach(cb => cb.checked = true);
    document.getElementById('selectAllHoardings').checked = true;
    updateBulkBtn();
}

// ═══════════════════════════════════════
// MODAL HELPERS
// ═══════════════════════════════════════
function openModal(id)      { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id)     { document.getElementById(id).classList.add('hidden'); }
function closeAllModals()   { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.add('hidden')); }

// ═══════════════════════════════════════
// OPEN COMMISSION MODAL
// ═══════════════════════════════════════
function openCommissionModal() {
    // Collect selected hoardings
    const checked = document.querySelectorAll('.hoarding-checkbox:checked');
    commState.hoardingIds = Array.from(checked).map(cb => parseInt(cb.value));

    if (commState.hoardingIds.length === 0) {
        alert('Please select at least one hoarding.');
        return;
    }

    // Reset state
    commState.applyAllTypes = null;
    commState.globalCommission = null;
    commState.oohCommission = null;
    commState.doohCommission = null;
    commState.applyAllStates = null;
    commState.stateData = [];
    commState.applyAllCities = null;
    commState.cityData = [];

    // Reset UI
    document.getElementById('allTypesYes').className = 'choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition';
    document.getElementById('allTypesNo').className = 'choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition';
    document.getElementById('globalCommissionWrap').classList.add('hidden');
    document.getElementById('typeInputsWrap').classList.add('hidden');
    document.getElementById('globalCommission').value = '';
    document.getElementById('oohCommission').value = '';
    document.getElementById('doohCommission').value = '';

    // Show info
    const info = document.getElementById('selectedInfo');
    document.getElementById('selectedCount').textContent = commState.hoardingIds.length;
    info.classList.remove('hidden');

    openModal('commModal1');
}

function openCommissionModalForHoarding(hoardingId, name) {
    // Uncheck all, check just this one
    document.querySelectorAll('.hoarding-checkbox').forEach(cb => {
        cb.checked = parseInt(cb.value) === hoardingId;
    });
    updateBulkBtn();
    openCommissionModal();
}

// ═══════════════════════════════════════
// MODAL 1: Hoarding Types
// ═══════════════════════════════════════
function setAllTypes(yes) {
    commState.applyAllTypes = yes;
    document.getElementById('globalCommissionWrap').classList.toggle('hidden', !yes);
    document.getElementById('typeInputsWrap').classList.toggle('hidden', yes);
    highlightChoice('allTypesYes', 'allTypesNo', yes);
}

function modal1Next() {
    if (commState.applyAllTypes === null) { alert('Please select an option.'); return; }

    if (commState.applyAllTypes) {
        const val = parseFloat(document.getElementById('globalCommission').value);
        if (isNaN(val) || val < 0 || val > 100) {
            alert('Please enter a valid commission between 0 and 100.');
            return;
        }
        commState.globalCommission = val;
    } else {
        const ooh  = parseFloat(document.getElementById('oohCommission').value);
        const dooh = parseFloat(document.getElementById('doohCommission').value);
        if (isNaN(ooh) || isNaN(dooh)) {
            alert('Please enter both OOH and DOOH commission rates.');
            return;
        }
        commState.oohCommission  = ooh;
        commState.doohCommission = dooh;
    }

    closeModal('commModal1');
    openModal('commModal2');
    buildStateInputs();

    // Reset state modal choices
    commState.applyAllStates = null;
    document.getElementById('allStatesYes').className = 'choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition';
    document.getElementById('allStatesNo').className = 'choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition';
    document.getElementById('stateInputsWrap').classList.add('hidden');
}

// ═══════════════════════════════════════
// MODAL 2: States
// ═══════════════════════════════════════
function setAllStates(yes) {
    commState.applyAllStates = yes;
    document.getElementById('stateInputsWrap').classList.toggle('hidden', yes);
    highlightChoice('allStatesYes', 'allStatesNo', yes);
}

function buildStateInputs() {
    const wrap = document.getElementById('stateInputsWrap');
    wrap.innerHTML = '';

    vendorStates.forEach(s => {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 rounded-xl p-4';

        if (commState.applyAllTypes) {
            div.innerHTML = `
                <p class="text-sm font-semibold text-gray-700 mb-2">${s}</p>
                <div class="relative">
                    <input type="number" placeholder="Commission %" min="0" max="100" step="0.01"
                        data-state="${s}" data-field="commission"
                        class="state-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                </div>`;
        } else {
            div.innerHTML = `
                <p class="text-sm font-semibold text-gray-700 mb-2">${s}</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <input type="number" placeholder="OOH %" min="0" max="100" step="0.01"
                            data-state="${s}" data-field="ooh_commission"
                            class="state-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>
                    <div class="relative">
                        <input type="number" placeholder="DOOH %" min="0" max="100" step="0.01"
                            data-state="${s}" data-field="dooh_commission"
                            class="state-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>
                </div>`;
        }
        wrap.appendChild(div);
    });
}

function modal2Next() {
    if (commState.applyAllStates === null) { alert('Please select an option.'); return; }

    if (!commState.applyAllStates) {
        const inputs = document.querySelectorAll('.state-input');
        const map = {};
        inputs.forEach(inp => {
            const s = inp.dataset.state;
            const f = inp.dataset.field;
            if (!map[s]) map[s] = { name: s };
            if (inp.value !== '') map[s][f] = parseFloat(inp.value);
        });
        commState.stateData = Object.values(map).filter(s =>
            s.commission !== undefined || s.ooh_commission !== undefined || s.dooh_commission !== undefined
        );
    }

    closeModal('commModal2');
    openModal('commModal3');
    buildCityInputs();

    // Reset city modal choices
    commState.applyAllCities = null;
    document.getElementById('allCitiesYes').className = 'choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition';
    document.getElementById('allCitiesNo').className = 'choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition';
    document.getElementById('cityInputsWrap').classList.add('hidden');
}

// ═══════════════════════════════════════
// MODAL 3: Cities
// ═══════════════════════════════════════
function setAllCities(yes) {
    commState.applyAllCities = yes;
    document.getElementById('cityInputsWrap').classList.toggle('hidden', yes);
    highlightChoice('allCitiesYes', 'allCitiesNo', yes);
    if (!yes) buildCityInputs();
}

async function buildCityInputs() {
    const wrap = document.getElementById('cityInputsWrap');
    wrap.innerHTML = '<p class="text-xs text-gray-400 py-2">Loading cities...</p>';

    const statesToLoad = commState.applyAllStates
        ? vendorStates
        : commState.stateData.map(s => s.name);

    if (!statesToLoad.length) {
        wrap.innerHTML = '<p class="text-xs text-gray-400 py-2">No states selected.</p>';
        return;
    }

    wrap.innerHTML = '';

    for (const stateName of statesToLoad) {
        const res = await fetch(`{{ route('admin.commission.cities') }}?state=${encodeURIComponent(stateName)}&vendor_id={{ $vendor->id }}`);
        const cities = await res.json();
        if (!cities.length) continue;

        const section = document.createElement('div');
        section.innerHTML = `<p class="text-xs font-bold text-gray-500 uppercase mb-2 mt-3 first:mt-0">${stateName}</p>`;

        cities.forEach(city => {
            const row = document.createElement('div');
            row.className = 'bg-gray-50 rounded-xl p-3 mb-2';

            if (commState.applyAllTypes) {
                row.innerHTML = `
                    <p class="text-sm font-semibold text-gray-700 mb-2">${city}</p>
                    <div class="relative">
                        <input type="number" placeholder="Commission %" min="0" max="100" step="0.01"
                            data-state="${stateName}" data-city="${city}" data-field="commission"
                            class="city-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>`;
            } else {
                row.innerHTML = `
                    <p class="text-sm font-semibold text-gray-700 mb-2">${city}</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="relative">
                            <input type="number" placeholder="OOH %" min="0" max="100" step="0.01"
                                data-state="${stateName}" data-city="${city}" data-field="ooh_commission"
                                class="city-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                        </div>
                        <div class="relative">
                            <input type="number" placeholder="DOOH %" min="0" max="100" step="0.01"
                                data-state="${stateName}" data-city="${city}" data-field="dooh_commission"
                                class="city-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                        </div>
                    </div>`;
            }
            section.appendChild(row);
        });

        wrap.appendChild(section);
    }
}

// ═══════════════════════════════════════
// SUBMIT
// ═══════════════════════════════════════
async function submitCommission() {
    if (commState.applyAllCities === null) { alert('Please select an option.'); return; }

    if (!commState.applyAllCities) {
        const inputs = document.querySelectorAll('.city-input');
        const map = {};
        inputs.forEach(inp => {
            const key = `${inp.dataset.state}__${inp.dataset.city}`;
            const f   = inp.dataset.field;
            if (!map[key]) map[key] = { state: inp.dataset.state, name: inp.dataset.city };
            if (inp.value !== '') map[key][f] = parseFloat(inp.value);
        });
        commState.cityData = Object.values(map).filter(c =>
            c.commission !== undefined || c.ooh_commission !== undefined || c.dooh_commission !== undefined
        );
    }

    const btn = document.getElementById('finalSaveBtn');
    btn.disabled    = true;
    btn.textContent = 'Saving...';

    try {
        const res = await fetch('{{ route('admin.commission.save') }}', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                vendor_id:         commState.vendorId,
                hoarding_ids:      commState.hoardingIds,
                apply_all_types:   commState.applyAllTypes,
                global_commission: commState.globalCommission,
                ooh_commission:    commState.oohCommission,
                dooh_commission:   commState.doohCommission,
                apply_all_states:  commState.applyAllStates,
                states:            commState.stateData,
                apply_all_cities:  commState.applyAllCities,
                cities:            commState.cityData,
            }),
        });

        const data = await res.json();
        if (data.success) {
            closeModal('commModal3');
            openModal('commModalSuccess');
        } else {
            alert(data.message || 'Something went wrong.');
        }
    } catch (e) {
        alert('Network error. Please try again.');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Save Commission';
    }
}

// ═══════════════════════════════════════
// UI HELPERS
// ═══════════════════════════════════════
function highlightChoice(yesId, noId, isYes) {
    const activeClass   = ['border-[#009A5C]', 'bg-green-50', 'text-[#009A5C]'];
    const inactiveClass = ['border-gray-200', 'text-gray-700'];

    const yesEl = document.getElementById(yesId);
    const noEl  = document.getElementById(noId);

    // Reset both
    yesEl.classList.remove(...activeClass);
    noEl.classList.remove(...activeClass);
    yesEl.classList.add(...inactiveClass);
    noEl.classList.add(...inactiveClass);

    if (isYes) {
        yesEl.classList.remove(...inactiveClass);
        yesEl.classList.add(...activeClass);
    } else {
        noEl.classList.remove(...inactiveClass);
        noEl.classList.add(...activeClass);
    }
}

// Close modals on backdrop click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeAllModals();
    });
});
</script>
@endpush