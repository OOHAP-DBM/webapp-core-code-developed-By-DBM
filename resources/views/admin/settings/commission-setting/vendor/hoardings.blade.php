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

    {{-- Header + Set Vendor Commission Button --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-900">All Hoardings ({{ $hoardings->total() }})</h2>
        <button onclick="openVendorCommissionModal()"
            class="px-5 py-2.5 {{ ($hasExistingCommission ?? false) ? 'bg-[#F97316] hover:bg-[#ea6c0a]' : 'bg-[#009A5C] hover:bg-[#007a49]' }} text-white rounded-xl font-semibold text-sm transition">
            {{ ($hasExistingCommission ?? false) ? 'Update Commission' : 'Set Vendor Commission' }}
        </button>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('admin.commission.vendor.hoardings', $vendor) }}">
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
                    <option value="ooh"  {{ request('type') == 'ooh'  ? 'selected' : '' }}>OOH</option>
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
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">HOARDING NAME</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">CITY</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">LOCATION</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">COMMISSION</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($hoardings as $i => $hoarding)
                <tr class="hover:bg-gray-50 transition" id="hoarding-row-{{ $hoarding->id }}">
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
                                <p class="font-medium text-gray-900">{{ $hoarding->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span class="uppercase font-semibold text-[#F97316]">{{ $hoarding->hoarding_type }}</span>
                                    @if($hoarding->size) | {{ $hoarding->size }} @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $hoarding->city }}</td>
                    <td class="px-6 py-4 text-gray-500 text-xs max-w-xs truncate">{{ $hoarding->location }}</td>
                    <td class="px-6 py-4">
                        <span id="commission-display-{{ $hoarding->id }}"
                            class="font-bold {{ $hoarding->commission ? 'text-[#009A5C]' : 'text-gray-400' }}">
                            {{ $hoarding->commission ? $hoarding->commission . '%' : '—' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="openHoardingCommissionModal({{ $hoarding->id }}, '{{ addslashes($hoarding->name) }}', {{ $hoarding->commission ?? 'null' }})"
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

        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">
                    Showing {{ $hoardings->firstItem() }} to {{ $hoardings->lastItem() }} of {{ $hoardings->total() }} records
                </span>
            </div>
            {{ $hoardings->links() }}
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     FLOW A: SINGLE HOARDING commission modal
══════════════════════════════════════════════════════════════ --}}
<div id="hoardingModal" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Set Commission</h2>
            <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p id="hoardingModalName" class="text-sm text-gray-500 mb-5"></p>

        <label class="text-sm font-semibold text-gray-700 mb-2 block">Commission %</label>
        <div class="relative mb-6">
            <input type="number" id="hoardingCommissionInput" min="0" max="100" step="0.01"
                placeholder="e.g. 15"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
        </div>

        <div class="flex gap-3">
            <button onclick="closeAllModals()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Cancel</button>
            <button onclick="saveHoardingCommission()" id="saveHoardingBtn"
                class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">
                Save
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     FLOW B: VENDOR commission modals (5 steps)
══════════════════════════════════════════════════════════════ --}}

{{-- Step 1: Enter base commission --}}
<div id="vModal1" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-lg font-bold text-gray-900">Set Vendor Commission</h2>
            <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">Enter the base commission percentage for <strong>{{ $vendor->name }}</strong>.</p>

        <label class="text-sm font-semibold text-gray-700 mb-2 block">Commission %</label>
        <div class="relative mb-6">
            <input type="number" id="vBaseCommission" min="0" max="100" step="0.01"
                placeholder="e.g. 15"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
        </div>

        <div class="flex gap-3">
            <button onclick="closeAllModals()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Cancel</button>
            <button onclick="vStep1Next()" class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">Next</button>
        </div>
    </div>
</div>

{{-- Step 2: OOH + DOOH same or separate? --}}
<div id="vModal2" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-lg font-bold text-gray-900">Hoarding Types</h2>
            <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">Is this applicable for both OOH and DOOH?</p>

        <div class="flex gap-3 mb-6">
            <button onclick="vSetAllTypes(true)" id="vAllTypesYes"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                Yes — Same for Both
            </button>
            <button onclick="vSetAllTypes(false)" id="vAllTypesNo"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                No — Set Separately
            </button>
        </div>

        {{-- Separate OOH/DOOH inputs --}}
        <div id="vTypeInputs" class="hidden space-y-4 mb-6">
            <div>
                <label class="text-sm font-semibold text-gray-700 mb-2 block">OOH Commission %</label>
                <div class="relative">
                    <input type="number" id="vOohCommission" min="0" max="100" step="0.01" placeholder="e.g. 12"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 mb-2 block">DOOH Commission %</label>
                <div class="relative">
                    <input type="number" id="vDoohCommission" min="0" max="100" step="0.01" placeholder="e.g. 18"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="switchModal('vModal2','vModal1')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Back</button>
            <button onclick="vStep2Next()" class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">Next</button>
        </div>
    </div>
</div>

{{-- Step 3: All states? --}}
<div id="vModal3" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-lg font-bold text-gray-900">State Coverage</h2>
            <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">Is this commission applicable to all states?</p>

        <div class="flex gap-3 mb-2">
            <button onclick="vSetAllStates(true)" id="vAllStatesYes"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                Yes — All States
            </button>
            <button onclick="vSetAllStates(false)" id="vAllStatesNo"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                No — By State
            </button>
        </div>

        <div id="vStateInputsWrap" class="hidden mt-4 mb-4 max-h-60 overflow-y-auto space-y-3 pr-1"></div>

        <div class="flex gap-3 mt-4">
            <button onclick="switchModal('vModal3','vModal2')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Back</button>
            <button onclick="vStep3Next()" id="vModal3NextBtn" class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">Next</button>
        </div>
    </div>
</div>

{{-- Step 4: All cities? --}}
<div id="vModal4" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-lg">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-lg font-bold text-gray-900">City Coverage</h2>
            <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">Is this commission applicable to all cities?</p>

        <div class="flex gap-3 mb-2">
            <button onclick="vSetAllCities(true)" id="vAllCitiesYes"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                Yes — All Cities
            </button>
            <button onclick="vSetAllCities(false)" id="vAllCitiesNo"
                class="choice-btn flex-1 py-3 rounded-xl border-2 border-gray-200 font-semibold text-sm transition">
                No — By City
            </button>
        </div>

        <div id="vCityInputsWrap" class="hidden mt-4 mb-4 max-h-64 overflow-y-auto space-y-3 pr-1"></div>

        <div class="flex gap-3 mt-4">
            <button onclick="switchModal('vModal4','vModal3')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Back</button>
            <button onclick="vSubmit()" id="vSaveBtn"
                class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">
                Save Commission
            </button>
        </div>
    </div>
</div>

{{-- Success Modal (shared) --}}
<div id="modalSuccess" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-sm text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-[#009A5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Commission Saved!</h3>
        <p id="successMessage" class="text-sm text-gray-500 mb-6">Commission settings have been applied successfully.</p>
        <button onclick="closeAllModals()" class="w-full py-2.5 bg-[#009A5C] text-white rounded-xl font-semibold text-sm">Done</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════
// SHARED STATE
// ═══════════════════════════════════════════════════════
const vendorStates  = @json($states);
const vendorId      = {{ $vendor->id }};
const csrfToken     = '{{ csrf_token() }}';
const citiesUrl     = '{{ route('admin.commission.cities') }}';
const saveUrl       = '{{ route('admin.commission.save') }}';
const commissionMap = @json($commissionMap ?? []);

// Helper: look up an existing commission value from the map
// key format: "type|state|city"  (empty string for null)
function getExisting(type, state, city) {
    const key = `${type}|${state ?? ''}|${city ?? ''}`;
    return (commissionMap[key] !== undefined) ? commissionMap[key] : null;
}

// Vendor commission wizard state
const vState = {
    baseCommission: null,
    applyAllTypes:  null,
    oohCommission:  null,
    doohCommission: null,
    applyAllStates: null,
    stateData:      [],
    applyAllCities: null,
    cityData:       [],
};

// Current hoarding being edited (Flow A)
let currentHoardingId   = null;
let currentHoardingName = null;

// ═══════════════════════════════════════════════════════
// MODAL HELPERS
// ═══════════════════════════════════════════════════════
function openModal(id)    { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id)   { document.getElementById(id).classList.add('hidden'); }
function closeAllModals() { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.add('hidden')); }
function switchModal(closeId, openId) { closeModal(closeId); openModal(openId); }

function highlightChoice(yesId, noId, isYes) {
    const active   = ['border-[#009A5C]', 'bg-green-50', 'text-[#009A5C]'];
    const inactive = ['border-gray-200', 'text-gray-700'];
    const yesEl = document.getElementById(yesId);
    const noEl  = document.getElementById(noId);
    yesEl.classList.remove(...active, ...inactive);
    noEl.classList.remove(...active, ...inactive);
    if (isYes) {
        yesEl.classList.add(...active);
        noEl.classList.add(...inactive);
    } else {
        noEl.classList.add(...active);
        yesEl.classList.add(...inactive);
    }
}

// ═══════════════════════════════════════════════════════
// FLOW A: SINGLE HOARDING
// ═══════════════════════════════════════════════════════
function openHoardingCommissionModal(id, name, existing) {
    currentHoardingId   = id;
    currentHoardingName = name;
    document.getElementById('hoardingModalName').textContent = 'Hoarding: ' + name;
    document.getElementById('hoardingCommissionInput').value = (existing !== null && existing !== undefined) ? existing : '';
    openModal('hoardingModal');
}

async function saveHoardingCommission() {
    const val = parseFloat(document.getElementById('hoardingCommissionInput').value);
    if (isNaN(val) || val < 0 || val > 100) {
        alert('Please enter a valid commission between 0 and 100.');
        return;
    }

    const btn = document.getElementById('saveHoardingBtn');
    btn.disabled    = true;
    btn.textContent = 'Saving...';

    try {
        const url = `/admin/commission/hoarding/${currentHoardingId}/commission`;
        const res = await fetch(url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body:    JSON.stringify({ commission: val }),
        });
        const data = await res.json();

        if (data.success) {
            const display = document.getElementById(`commission-display-${currentHoardingId}`);
            if (display) {
                display.textContent = data.commission + '%';
                display.className   = 'font-bold text-[#009A5C]';
            }
            closeModal('hoardingModal');
            document.getElementById('successMessage').textContent = `Commission of ${val}% set for "${currentHoardingName}".`;
            openModal('modalSuccess');
        } else {
            alert(data.message || 'Failed to save.');
        }
    } catch (e) {
        alert('Network error. Please try again.');
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Save';
    }
}

// ═══════════════════════════════════════════════════════
// FLOW B: VENDOR-LEVEL COMMISSION
// ═══════════════════════════════════════════════════════
function openVendorCommissionModal() {
    // ── Detect what was previously saved ──
    const existingAll  = getExisting('all',  null, null);
    const existingOoh  = getExisting('ooh',  null, null);
    const existingDooh = getExisting('dooh', null, null);

    // Was "same for both types" previously saved?
    const wasSameForBoth = existingAll !== null;
    // Were separate OOH/DOOH previously saved?
    const wasSeparate    = !wasSameForBoth && (existingOoh !== null || existingDooh !== null);

    // ── Reset wizard state, but pre-populate from existing ──
    Object.assign(vState, {
        baseCommission: wasSameForBoth ? existingAll : (existingOoh ?? existingDooh ?? null),
        applyAllTypes:  wasSameForBoth ? true : (wasSeparate ? false : null),
        oohCommission:  existingOoh  ?? null,
        doohCommission: existingDooh ?? null,
        applyAllStates: null,
        stateData:      [],
        applyAllCities: null,
        cityData:       [],
    });

    // ── Prefill commission inputs ──
    document.getElementById('vBaseCommission').value = vState.baseCommission ?? '';
    document.getElementById('vOohCommission').value  = existingOoh  ?? '';
    document.getElementById('vDoohCommission').value = existingDooh ?? '';

    // ── Show/hide separate OOH/DOOH inputs based on previous choice ──
    document.getElementById('vTypeInputs').classList.toggle('hidden', !wasSeparate);

    // ── Highlight previously chosen type button ──
    ['vAllTypesYes','vAllTypesNo','vAllStatesYes','vAllStatesNo','vAllCitiesYes','vAllCitiesNo']
        .forEach(id => {
            const el = document.getElementById(id);
            el.classList.remove('border-[#009A5C]', 'bg-green-50', 'text-[#009A5C]');
            el.classList.add('border-gray-200', 'text-gray-700');
        });

    if (wasSameForBoth) highlightChoice('vAllTypesYes', 'vAllTypesNo', true);
    if (wasSeparate)    highlightChoice('vAllTypesYes', 'vAllTypesNo', false);

    // ── Hide state/city wrap sections (always start from step 1) ──
    document.getElementById('vStateInputsWrap').classList.add('hidden');
    document.getElementById('vCityInputsWrap').classList.add('hidden');

    // Reset states modal button label
    const modal3Btn = document.getElementById('vModal3NextBtn');
    if (modal3Btn) modal3Btn.textContent = 'Next';

    openModal('vModal1');
}

// ── Step 1: Enter base commission ──
function vStep1Next() {
    const val = parseFloat(document.getElementById('vBaseCommission').value);
    if (isNaN(val) || val < 0 || val > 100) {
        alert('Please enter a valid commission between 0 and 100.');
        return;
    }
    vState.baseCommission = val;
    switchModal('vModal1', 'vModal2');
}

// ── Step 2: OOH / DOOH ──
function vSetAllTypes(yes) {
    vState.applyAllTypes = yes;
    document.getElementById('vTypeInputs').classList.toggle('hidden', yes);
    highlightChoice('vAllTypesYes', 'vAllTypesNo', yes);
}

function vStep2Next() {
    if (vState.applyAllTypes === null) { alert('Please select an option.'); return; }

    if (vState.applyAllTypes) {
        // YES — same commission for both OOH & DOOH → save immediately, close all modals
        vState.stateData      = [];
        vState.applyAllStates = true;
        vState.applyAllCities = true;
        vSaveNow(true, []);
        return;
    }

    // NO — separate OOH/DOOH rates → validate then go to states modal
    const ooh  = parseFloat(document.getElementById('vOohCommission').value);
    const dooh = parseFloat(document.getElementById('vDoohCommission').value);
    if (isNaN(ooh)  || ooh  < 0 || ooh  > 100) { alert('Please enter a valid OOH commission.');  return; }
    if (isNaN(dooh) || dooh < 0 || dooh > 100) { alert('Please enter a valid DOOH commission.'); return; }
    vState.oohCommission  = ooh;
    vState.doohCommission = dooh;

    buildStateInputs();
    switchModal('vModal2', 'vModal3');
}

// ── Step 3: States ──
function vSetAllStates(yes) {
    vState.applyAllStates = yes;
    document.getElementById('vStateInputsWrap').classList.toggle('hidden', yes);
    highlightChoice('vAllStatesYes', 'vAllStatesNo', yes);

    // Change the Next button to "Save Commission" when Yes is chosen
    const btn = document.getElementById('vModal3NextBtn');
    btn.textContent = yes ? 'Save Commission' : 'Next';
}

function buildStateInputs() {
    const wrap = document.getElementById('vStateInputsWrap');
    wrap.innerHTML = '';
    if (!vendorStates.length) {
        wrap.innerHTML = '<p class="text-xs text-gray-400">No states found for this vendor.</p>';
        return;
    }
    vendorStates.forEach(s => {
        const div = document.createElement('div');
        div.className = 'bg-gray-50 rounded-xl p-4';

        if (vState.applyAllTypes) {
            const existing = getExisting('all', s, null);
            div.innerHTML = `
                <p class="text-sm font-semibold text-gray-700 mb-2">${s}</p>
                <div class="relative">
                    <input type="number" placeholder="Commission %" min="0" max="100" step="0.01"
                        value="${existing !== null ? existing : ''}"
                        data-state="${s}" data-field="commission"
                        class="state-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                </div>`;
        } else {
            const existingOoh  = getExisting('ooh',  s, null);
            const existingDooh = getExisting('dooh', s, null);
            div.innerHTML = `
                <p class="text-sm font-semibold text-gray-700 mb-2">${s}</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <input type="number" placeholder="OOH %" min="0" max="100" step="0.01"
                            value="${existingOoh !== null ? existingOoh : ''}"
                            data-state="${s}" data-field="ooh_commission"
                            class="state-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>
                    <div class="relative">
                        <input type="number" placeholder="DOOH %" min="0" max="100" step="0.01"
                            value="${existingDooh !== null ? existingDooh : ''}"
                            data-state="${s}" data-field="dooh_commission"
                            class="state-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>
                </div>`;
        }
        wrap.appendChild(div);
    });
}

async function vStep3Next() {
    if (vState.applyAllStates === null) { alert('Please select an option.'); return; }

    if (vState.applyAllStates) {
        // YES — all states: save immediately, skip city modal entirely
        vState.stateData = [];
        await vSaveNow(true, []);
        return;
    }

    // NO — collect per-state values, then go to city modal
    const map = {};
    document.querySelectorAll('.state-inp').forEach(inp => {
        const s = inp.dataset.state;
        const f = inp.dataset.field;
        if (!map[s]) map[s] = { name: s };
        if (inp.value !== '') map[s][f] = parseFloat(inp.value);
    });
    vState.stateData = Object.values(map).filter(s =>
        s.commission !== undefined || s.ooh_commission !== undefined || s.dooh_commission !== undefined
    );

    await buildCityInputs();
    switchModal('vModal3', 'vModal4');
}

// ── Step 4: Cities ──
function vSetAllCities(yes) {
    vState.applyAllCities = yes;
    document.getElementById('vCityInputsWrap').classList.toggle('hidden', yes);
    highlightChoice('vAllCitiesYes', 'vAllCitiesNo', yes);
    if (!yes) buildCityInputs();
}

async function buildCityInputs() {
    const wrap = document.getElementById('vCityInputsWrap');
    wrap.innerHTML = '<p class="text-xs text-gray-400 py-2">Loading cities...</p>';

    // When applyAllStates was No, load cities only for selected states
    const statesToLoad = vState.applyAllStates
        ? vendorStates
        : vState.stateData.map(s => s.name);

    if (!statesToLoad.length) {
        wrap.innerHTML = '<p class="text-xs text-gray-400 py-2">No states selected.</p>';
        return;
    }

    wrap.innerHTML = '';
    for (const stateName of statesToLoad) {
        const res    = await fetch(`${citiesUrl}?state=${encodeURIComponent(stateName)}&vendor_id=${vendorId}`);
        const cities = await res.json();
        if (!cities.length) continue;

        const section = document.createElement('div');
        section.innerHTML = `<p class="text-xs font-bold text-gray-500 uppercase mb-2 mt-2">${stateName}</p>`;

        cities.forEach(city => {
            const row = document.createElement('div');
            row.className = 'bg-gray-50 rounded-xl p-3 mb-2';

            if (vState.applyAllTypes) {
                const existing = getExisting('all', stateName, city);
                row.innerHTML = `
                    <p class="text-sm font-semibold text-gray-700 mb-2">${city}</p>
                    <div class="relative">
                        <input type="number" placeholder="Commission %" min="0" max="100" step="0.01"
                            value="${existing !== null ? existing : ''}"
                            data-state="${stateName}" data-city="${city}" data-field="commission"
                            class="city-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>`;
            } else {
                const existingOoh  = getExisting('ooh',  stateName, city);
                const existingDooh = getExisting('dooh', stateName, city);
                row.innerHTML = `
                    <p class="text-sm font-semibold text-gray-700 mb-2">${city}</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="relative">
                            <input type="number" placeholder="OOH %" min="0" max="100" step="0.01"
                                value="${existingOoh !== null ? existingOoh : ''}"
                                data-state="${stateName}" data-city="${city}" data-field="ooh_commission"
                                class="city-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                        </div>
                        <div class="relative">
                            <input type="number" placeholder="DOOH %" min="0" max="100" step="0.01"
                                value="${existingDooh !== null ? existingDooh : ''}"
                                data-state="${stateName}" data-city="${city}" data-field="dooh_commission"
                                class="city-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                        </div>
                    </div>`;
            }
            section.appendChild(row);
        });
        wrap.appendChild(section);
    }
}

// ── Shared save function ──
// applyAllCities: boolean
// cityData: array of city objects (empty array if applyAllCities = true)
async function vSaveNow(applyAllCities, cityData) {
    const btn = document.getElementById('vSaveBtn');
    const modal3Btn = document.getElementById('vModal3NextBtn');

    // Disable whichever save button is visible
    if (btn) { btn.disabled = true; btn.textContent = 'Saving...'; }
    if (modal3Btn && vState.applyAllStates) { modal3Btn.disabled = true; modal3Btn.textContent = 'Saving...'; }

    try {
        const res = await fetch(saveUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                vendor_id:          vendorId,
                base_commission:    vState.baseCommission,
                apply_to_all_types: vState.applyAllTypes,
                ooh_commission:     vState.oohCommission,
                dooh_commission:    vState.doohCommission,
                apply_all_states:   vState.applyAllStates,
                states:             vState.stateData,
                apply_all_cities:   applyAllCities,
                cities:             cityData,
            }),
        });
        const data = await res.json();

        if (data.success) {
            closeAllModals();
            document.getElementById('successMessage').textContent = 'Vendor commission settings have been applied successfully.';
            openModal('modalSuccess');
        } else {
            alert(data.message || 'Something went wrong.');
        }
    } catch (e) {
        alert('Network error. Please try again.');
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Save Commission'; }
        if (modal3Btn) { modal3Btn.disabled = false; modal3Btn.textContent = vState.applyAllStates ? 'Save Commission' : 'Next'; }
    }
}

// ── Submit from city modal ──
async function vSubmit() {
    if (vState.applyAllCities === null) { alert('Please select an option.'); return; }

    let cityData = [];
    if (!vState.applyAllCities) {
        const map = {};
        document.querySelectorAll('.city-inp').forEach(inp => {
            const key = `${inp.dataset.state}__${inp.dataset.city}`;
            const f   = inp.dataset.field;
            if (!map[key]) map[key] = { state: inp.dataset.state, name: inp.dataset.city };
            if (inp.value !== '') map[key][f] = parseFloat(inp.value);
        });
        cityData = Object.values(map).filter(c =>
            c.commission !== undefined || c.ooh_commission !== undefined || c.dooh_commission !== undefined
        );
    }

    await vSaveNow(vState.applyAllCities, cityData);
}

// Close on backdrop click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeAllModals();
    });
});
</script>
@endpush