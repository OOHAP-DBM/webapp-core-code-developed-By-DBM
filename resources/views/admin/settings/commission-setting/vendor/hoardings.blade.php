{{-- resources/views/admin/settings/commission-setting/vendor/hoardings.blade.php --}}
@extends('layouts.admin')

@section('title', 'Vendor Hoardings Commission')
@section('page_title', 'Vendor Hoardings Commission')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'Commission Settings', 'route' => route('admin.commission.index')],
    ['label' => 'Vendor Hoardings']
]" />
@endsection

@section('content')
<div class="p-6">

<!-- @dump($vendor, $hoardings, $resolvedCommissions, $hasExistingCommission) -->
    {{-- Vendor Details --}}
   
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <h2 class="text-base font-bold text-gray-900 mb-3">Vendor Details</h2>
        <div class="grid grid-cols-2 gap-y-1 text-sm text-gray-600">
            <div><span class="font-medium text-gray-800">Name:</span> {{ $vendor->name }}</div>
            <div><span class="font-medium text-gray-800">Business Name:</span> {{ $vendor->vendorProfile->company_name ?? '—' }}</div>
            <div><span class="font-medium text-gray-800">GSTIN:</span> {{ $vendor->vendorProfile->gstin ?? $vendor->gstin ?? '—' }}</div>
            <div><span class="font-medium text-gray-800">Mobile Number:</span> {{ $vendor->phone ?? $vendor->vendorProfile->contact_person_phone ?? '—' }}</div>
            <div><span class="font-medium text-gray-800">Email:</span> {{ $vendor->email ?? $vendor->vendorProfile->contact_person_email ?? '—' }}</div>
            <div><span class="font-medium text-gray-800">Address:</span> {{ $vendor->address ?? $vendor->vendorProfile->registered_address ?? '—' }}</div>
        </div>
    </div>

    {{-- Header + Set Vendor Commission Button --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-900">All Hoardings ({{ $hoardings->total() }})</h2>
    
        <button onclick="openVendorCommissionModal()"
            class="px-5 py-2.5 {{ ($hasExistingCommission ?? false) ? 'bg-[#F97316] hover:bg-[#ea6c0a]' : 'bg-[#009A5C] hover:bg-[#007a49]' }} text-black rounded-xl font-semibold text-sm transition">
            {{ ($hasExistingCommission ?? false) ? 'Update Commission' : 'Set Vendor Commission' }}
        </button>

         <a href="{{ route('admin.commission.vendor.rules', $vendor) }}"
            class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-semibold text-sm hover:bg-gray-200 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            View Rules
        </a>
    </div>



    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('admin.commission.vendor.hoardings', $vendor) }}">
        <div class="flex gap-3 mb-6">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
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
                <svg class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <svg class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <svg class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <!-- <button type="submit"
                class="px-4 py-2.5 bg-[#E8F7F0] text-[#009A5C] rounded-xl border border-[#009A5C]/20 hover:bg-[#009A5C] hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
            </button> -->
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
                @php
                    // Use pre-resolved commission from controller (centralized, no extra queries)
                    $effectiveCommission = $resolvedCommissions[$hoarding->id] ?? null;
                    $hasOverride         = $hoarding->commission_percent !== null && (float)$hoarding->commission_percent > 0;
                @endphp
                <tr class="hover:bg-gray-50 transition" id="hoarding-row-{{ $hoarding->id }}">
                    <td class="px-6 py-4 text-gray-500">
                        {{ str_pad($hoardings->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            @php
                                $thumbUrl = $hoarding->heroImage()?? ($hoarding->image ?? null);
                            @endphp
                            <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                @if($thumbUrl)
                                    <img src="{{ $thumbUrl }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $hoarding->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span class="uppercase font-semibold text-[#F97316]">{{ $hoarding->hoarding_type }}</span>
                                    @if($hoarding->size) | {{ $hoarding->size }} @endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $hoarding->city }}</td>
                    <td class="px-6 py-4 text-gray-500 text-xs max-w-xs truncate">{{ $hoarding->display_location }}</td>

                    {{-- Commission display cell --}}
                    <td class="px-6 py-4" id="commission-cell-{{ $hoarding->id }}">
                        @if($effectiveCommission !== null)
                            <div class="flex flex-col gap-0.5">
                                <span class="font-bold text-[#009A5C] text-sm">{{ number_format($effectiveCommission, 2) }}%</span>
                                @if($hasOverride)
                                    <span class="text-xs text-[#F97316] font-medium">Hoarding override</span>
                                @else
                                    <span class="text-xs text-gray-400">From vendor rules</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-300 font-bold text-sm">—</span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <button
                            onclick="openHoardingCommissionModal(
                                {{ $hoarding->id }},
                                '{{ addslashes($hoarding->title ?? $hoarding->name) }}',
                                {{ $hasOverride ? $hoarding->commission_percent : 'null' }}
                            )"
                            class="w-36 text-center px-4 py-1.5 {{ $hasOverride ? 'bg-[#F97316]' : 'bg-[#009A5C]' }} text-black rounded-lg text-xs font-semibold hover:opacity-90 transition">
                            {{ $hasOverride ? 'Update Commission' : 'Set Commission' }}
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
            <span class="text-sm text-gray-500">
                Showing {{ $hoardings->firstItem() }} to {{ $hoardings->lastItem() }} of {{ $hoardings->total() }} records
            </span>
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
            <h2 class="text-lg font-bold text-gray-900" id="hoardingModalTitle">Set Commission</h2>
            <button onclick="closeAllModals()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <p id="hoardingModalName" class="text-sm text-gray-500 mb-5"></p>

        <label class="text-sm font-semibold text-gray-700 mb-2 block">Commission %</label>
        <div class="relative mb-6">
            <input type="number" id="hoardingCommissionInput" min="0.01" max="99" step="0.01"
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
     FLOW B: VENDOR commission modals
══════════════════════════════════════════════════════════════ --}}

{{-- Step 1: Base commission --}}
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
            <input type="number" id="vBaseCommission" min="0.01" max="99" step="0.01" placeholder="e.g. 15"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
            <span class="absolute right-4 mt-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
        </div>

        <div class="flex gap-3">
            <button onclick="closeAllModals()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Cancel</button>
            <button onclick="vStep1Next()" class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">Next</button>
        </div>
    </div>
</div>

{{-- Step 2: OOH + DOOH --}}
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

        <div id="vTypeInputs" class="hidden space-y-4 mb-6">
            <div>
                <label class="text-sm font-semibold text-gray-700 mb-2 block">OOH Commission %</label>
                <div class="relative">
                    <input type="number" id="vOohCommission" min="0.01" max="99" step="0.01" placeholder="e.g. 12"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
                    <span class="absolute right-4 mt-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700 mb-2 block">DOOH Commission %</label>
                <div class="relative">
                    <input type="number" id="vDoohCommission" min="0.01" max="99" step="0.01" placeholder="e.g. 18"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-10 outline-none focus:border-[#009A5C] text-sm">
                    <span class="absolute right-4 mt-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold">%</span>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="switchModal('vModal2','vModal1')" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Back</button>
            <button onclick="vStep2Next()" class="flex-1 py-2.5 rounded-xl bg-[#009A5C] text-white font-semibold text-sm hover:bg-[#007a49]">Next</button>
        </div>
    </div>
</div>

{{-- Step 3: States --}}
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

{{-- Step 4: Cities --}}
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

{{-- Success Modal --}}
<div id="modalSuccess" class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-sm text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-[#009A5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2" id="successTitle">Commission Saved!</h3>
        <p id="successMessage" class="text-sm text-gray-500 mb-2"></p>
        <p id="successCommissionDetail" class="text-sm font-semibold text-[#009A5C] mb-6"></p>
        <button onclick="closeAllModals()" class="w-full py-2.5 bg-[#009A5C] text-white rounded-xl font-semibold text-sm">Done</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ═══════════════════════════════════════════════════════
// CONFIG — injected from PHP
// ═══════════════════════════════════════════════════════
const vendorStates   = @json($states);
const vendorId       = {{ $vendor->id }};
const csrfToken      = '{{ csrf_token() }}';
const citiesUrl      = '{{ route('admin.commission.cities') }}';
const saveUrl        = '{{ route('admin.commission.save') }}';
const commissionMap  = @json($commissionMap ?? []);
const hasExisting    = {{ ($hasExistingCommission ?? false) ? 'true' : 'false' }};
const MIN_COMMISSION = 0.01;
const MAX_COMMISSION = 99;

// ═══════════════════════════════════════════════════════
// CENTRALIZED COMMISSION DISPLAY
// Updates a hoarding row's commission cell without reload
// ═══════════════════════════════════════════════════════
function updateCommissionDisplay(hoardingId, commission, isOverride) {
    const cell = document.getElementById(`commission-cell-${hoardingId}`);
    if (!cell) return;

    // Treat 0 or null as "no commission set"
    const hasValue = commission !== null && commission !== undefined && parseFloat(commission) > 0;

    if (hasValue) {
        const formatted  = parseFloat(commission).toFixed(2);
        const label      = isOverride ? 'Hoarding override' : 'From vendor rules';
        const labelColor = isOverride ? 'text-[#F97316]' : 'text-gray-400';

        cell.innerHTML = `
            <div class="flex flex-col gap-0.5">
                <span class="font-bold text-[#009A5C] text-sm">${formatted}%</span>
                <span class="text-xs ${labelColor} font-medium">${label}</span>
            </div>`;
    } else {
        cell.innerHTML = `<span class="text-gray-300 font-bold text-sm">—</span>`;
    }

    // Update action button
    const actionBtn = cell.closest('tr')?.querySelector('button[onclick^="openHoardingCommissionModal"]');
    if (actionBtn) {
        actionBtn.textContent = (hasValue && isOverride) ? 'Update' : 'Set Commission';
        actionBtn.className   = `w-36 text-center px-4 py-1.5 ${(hasValue && isOverride) ? 'bg-[#F97316]' : 'bg-[#009A5C]'} text-white rounded-lg text-xs font-semibold hover:opacity-90 transition`;
    }
}

// Bulk update all hoarding commission cells (used after vendor-level save)
// function updateAllCommissionDisplays(resolvedMap) {
//     Object.entries(resolvedMap).forEach(([hoardingId, commission]) => {
//         // vendor-level save never sets override flag
//         updateCommissionDisplay(parseInt(hoardingId), commission, false);
//     });
// }
function updateAllCommissionDisplays(resolvedMap) {
    Object.entries(resolvedMap).forEach(([hoardingId, meta]) => {
        // meta is now { commission, is_override } — respect existing hoarding overrides
        updateCommissionDisplay(parseInt(hoardingId), meta.commission, meta.is_override);
    });
}
// ═══════════════════════════════════════════════════════
// COMMISSION MAP HELPERS
// ═══════════════════════════════════════════════════════
function getExisting(type, state, city) {
    const key = `${type}|${state ?? ''}|${city ?? ''}`;
    return (commissionMap[key] !== undefined) ? commissionMap[key] : null;
}

// ═══════════════════════════════════════════════════════
// WIZARD STATE
// ═══════════════════════════════════════════════════════
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

let currentHoardingId      = null;
let currentHoardingName    = null;
let currentHoardingHasValue = false;

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
    [yesId, noId].forEach(id => {
        document.getElementById(id).classList.remove(...active, ...inactive);
    });
    document.getElementById(isYes ? yesId : noId).classList.add(...active);
    document.getElementById(isYes ? noId : yesId).classList.add(...inactive);
}

// ═══════════════════════════════════════════════════════
// SUCCESS MODAL HELPER
// ═══════════════════════════════════════════════════════
function showSuccess(title, message, detail = '') {
    document.getElementById('successTitle').textContent            = title;
    document.getElementById('successMessage').textContent          = message;
    document.getElementById('successCommissionDetail').textContent = detail;
    openModal('modalSuccess');
}

function isValidCommissionValue(value) {
    return !Number.isNaN(value) && value >= MIN_COMMISSION && value <= MAX_COMMISSION;
}

function validateDynamicCommissionInputs(selector) {
    const inputs = Array.from(document.querySelectorAll(selector));
    for (const input of inputs) {
        if (input.value === '') continue;
        const value = parseFloat(input.value);
        if (!isValidCommissionValue(value)) {
            alert(`Commission must be between ${MIN_COMMISSION}% and ${MAX_COMMISSION}%.`);
            input.focus();
            return false;
        }
    }
    return true;
}

function getRuleCoverageForTypes(types) {
    let hasStateRule = false;
    let hasCityRule = false;

    Object.keys(commissionMap || {}).forEach((key) => {
        const [type = '', state = '', city = ''] = key.split('|');
        if (!types.includes(type)) return;
        if (state && !city) hasStateRule = true;
        if (state && city) hasCityRule = true;
    });

    return { hasStateRule, hasCityRule };
}

// ═══════════════════════════════════════════════════════
// FLOW A: SINGLE HOARDING
// ═══════════════════════════════════════════════════════
function openHoardingCommissionModal(id, name, existing) {
    currentHoardingId       = id;
    currentHoardingName     = name;
    currentHoardingHasValue = (existing !== null && existing !== undefined);

    document.getElementById('hoardingModalTitle').textContent = currentHoardingHasValue ? 'Update Commission' : 'Set Commission';
    document.getElementById('hoardingModalName').textContent  = `Hoarding: ${name}`;
    document.getElementById('hoardingCommissionInput').value  = currentHoardingHasValue ? existing : '';
    openModal('hoardingModal');
}

async function saveHoardingCommission() {
    const val = parseFloat(document.getElementById('hoardingCommissionInput').value);
    if (!isValidCommissionValue(val)) {
        alert(`Please enter a valid commission between ${MIN_COMMISSION} and ${MAX_COMMISSION}.`);
        return;
    }

    const btn = document.getElementById('saveHoardingBtn');
    btn.disabled    = true;
    btn.textContent = 'Saving...';

    try {
        const url = `/admin/commission/hoarding/${currentHoardingId}/commission`;
        const res  = await fetch(url, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body:    JSON.stringify({ commission: val }),
        });
        const data = await res.json();

        if (data.success) {
            // Update the row display using centralized function
            updateCommissionDisplay(currentHoardingId, data.commission, true);

            closeModal('hoardingModal');

            const actionLabel = currentHoardingHasValue ? 'Updated' : 'Set';
            const msg         = currentHoardingHasValue
                ? `Commission for "${currentHoardingName}" has been updated.`
                : `Commission for "${currentHoardingName}" has been set. A notification has been sent to the vendor.`;

            showSuccess(
                `Commission ${actionLabel}!`,
                msg,
                `Commission Rate: ${val}%`
            );
        } else {
            alert(data.message || 'Failed to save.');
        }
    } catch (e) {
        console.error('Commission save error:', e);
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
    const existingAll  = getExisting('all',  null, null);
    const existingOoh  = getExisting('ooh',  null, null);
    const existingDooh = getExisting('dooh', null, null);

    const wasSameForBoth = existingAll !== null;
    const wasSeparate    = !wasSameForBoth && (existingOoh !== null || existingDooh !== null);

    Object.assign(vState, {
        baseCommission: wasSameForBoth ? existingAll : (existingOoh ?? existingDooh ?? null),
        applyAllTypes:  wasSameForBoth ? true : (wasSeparate ? false : null),
        oohCommission:  existingOoh  ?? null,
        doohCommission: existingDooh ?? null,
        applyAllStates: null, stateData: [],
        applyAllCities: null, cityData: [],
    });

    if (vState.applyAllTypes !== null) {
        const types = vState.applyAllTypes ? ['all'] : ['ooh', 'dooh'];
        const { hasStateRule, hasCityRule } = getRuleCoverageForTypes(types);
        vState.applyAllStates = !(hasStateRule || hasCityRule);
        vState.applyAllCities = !hasCityRule;
    }

    document.getElementById('vBaseCommission').value = vState.baseCommission ?? '';
    document.getElementById('vOohCommission').value  = existingOoh  ?? '';
    document.getElementById('vDoohCommission').value = existingDooh ?? '';
    document.getElementById('vTypeInputs').classList.toggle('hidden', !wasSeparate);
    document.getElementById('vStateInputsWrap').classList.add('hidden');
    document.getElementById('vCityInputsWrap').classList.add('hidden');

    ['vAllTypesYes','vAllTypesNo','vAllStatesYes','vAllStatesNo','vAllCitiesYes','vAllCitiesNo']
        .forEach(id => {
            const el = document.getElementById(id);
            el.classList.remove('border-[#009A5C]', 'bg-green-50', 'text-[#009A5C]');
            el.classList.add('border-gray-200', 'text-gray-700');
        });

    if (wasSameForBoth) highlightChoice('vAllTypesYes', 'vAllTypesNo', true);
    if (wasSeparate)    highlightChoice('vAllTypesYes', 'vAllTypesNo', false);

    const modal3Btn = document.getElementById('vModal3NextBtn');
    if (modal3Btn) modal3Btn.textContent = 'Next';

    openModal('vModal1');
}

// Step 1
function vStep1Next() {
    const val = parseFloat(document.getElementById('vBaseCommission').value);
    if (!isValidCommissionValue(val)) {
        alert(`Please enter a valid commission between ${MIN_COMMISSION} and ${MAX_COMMISSION}.`);
        return;
    }
    vState.baseCommission = val;
    switchModal('vModal1', 'vModal2');
}

// Step 2
function vSetAllTypes(yes) {
    vState.applyAllTypes = yes;
    document.getElementById('vTypeInputs').classList.toggle('hidden', yes);
    highlightChoice('vAllTypesYes', 'vAllTypesNo', yes);

    const types = yes ? ['all'] : ['ooh', 'dooh'];
    const { hasStateRule, hasCityRule } = getRuleCoverageForTypes(types);
    vState.applyAllStates = !(hasStateRule || hasCityRule);
    vState.applyAllCities = !hasCityRule;
}

function vStep2Next() {
    if (vState.applyAllTypes === null) { alert('Please select an option.'); return; }

    if (vState.applyAllTypes) {
        // YES — save immediately, skip all further modals
        vState.stateData = []; vState.applyAllStates = true; vState.applyAllCities = true;
        vSaveNow(true, []);
        return;
    }

    // NO — validate separate OOH/DOOH then go to states
    const ooh  = parseFloat(document.getElementById('vOohCommission').value);
    const dooh = parseFloat(document.getElementById('vDoohCommission').value);
    if (!isValidCommissionValue(ooh))  { alert(`Please enter valid OOH commission between ${MIN_COMMISSION} and ${MAX_COMMISSION}.`);  return; }
    if (!isValidCommissionValue(dooh)) { alert(`Please enter valid DOOH commission between ${MIN_COMMISSION} and ${MAX_COMMISSION}.`); return; }
    vState.oohCommission  = ooh;
    vState.doohCommission = dooh;

    buildStateInputs();
    if (vState.applyAllStates !== null) {
        vSetAllStates(vState.applyAllStates);
    }
    switchModal('vModal2', 'vModal3');
}

// Step 3
function vSetAllStates(yes) {
    vState.applyAllStates = yes;
    document.getElementById('vStateInputsWrap').classList.toggle('hidden', yes);
    highlightChoice('vAllStatesYes', 'vAllStatesNo', yes);
    document.getElementById('vModal3NextBtn').textContent = yes ? 'Save Commission' : 'Next';
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
                    <input type="number" placeholder="Commission %" min="0.01" max="99" step="0.01"
                        value="${existing !== null ? existing : ''}"
                        data-state="${s}" data-field="commission"
                        class="state-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                    <span class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                </div>`;
        } else {
            const eo = getExisting('ooh',  s, null);
            const ed = getExisting('dooh', s, null);
            div.innerHTML = `
                <p class="text-sm font-semibold text-gray-700 mb-2">${s}</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <input type="number" placeholder="OOH %" min="0.01" max="99" step="0.01"
                            value="${eo !== null ? eo : ''}"
                            data-state="${s}" data-field="ooh_commission"
                            class="state-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>
                    <div class="relative">
                        <input type="number" placeholder="DOOH %" min="0.01" max="99" step="0.01"
                            value="${ed !== null ? ed : ''}"
                            data-state="${s}" data-field="dooh_commission"
                            class="state-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                        <span class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                    </div>
                </div>`;
        }
        wrap.appendChild(div);
    });
}

async function vStep3Next() {
    if (vState.applyAllStates === null) { alert('Please select an option.'); return; }

    if (!validateDynamicCommissionInputs('.state-inp')) {
        return;
    }

    if (vState.applyAllStates) {
        vState.stateData = [];
        await vSaveNow(true, []);
        return;
    }

    const map = {};
    document.querySelectorAll('.state-inp').forEach(inp => {
        const s = inp.dataset.state, f = inp.dataset.field;
        if (!map[s]) map[s] = { name: s };
        if (inp.value !== '') map[s][f] = parseFloat(inp.value);
    });
    vState.stateData = Object.values(map).filter(s =>
        s.commission !== undefined || s.ooh_commission !== undefined || s.dooh_commission !== undefined
    );

    await buildCityInputs();
    if (vState.applyAllCities !== null) {
        vSetAllCities(vState.applyAllCities);
    }
    switchModal('vModal3', 'vModal4');
}

// Step 4
function vSetAllCities(yes) {
    vState.applyAllCities = yes;
    document.getElementById('vCityInputsWrap').classList.toggle('hidden', yes);
    highlightChoice('vAllCitiesYes', 'vAllCitiesNo', yes);
    if (!yes) buildCityInputs();
}

async function buildCityInputs() {
    const wrap = document.getElementById('vCityInputsWrap');
    wrap.innerHTML = '<p class="text-xs text-gray-400 py-2">Loading cities...</p>';

    const statesToLoad = vState.applyAllStates ? vendorStates : vState.stateData.map(s => s.name);
    if (!statesToLoad.length) {
        wrap.innerHTML = '<p class="text-xs text-gray-400 py-2">No states selected.</p>';
        return;
    }

    wrap.innerHTML = '';
    for (const stateName of statesToLoad) {
        try {
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
                            <input type="number" placeholder="Commission %" min="0.01" max="99" step="0.01"
                                value="${existing !== null ? existing : ''}"
                                data-state="${stateName}" data-city="${city}" data-field="commission"
                                class="city-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                        </div>`;
                } else {
                    const eo = getExisting('ooh',  stateName, city);
                    const ed = getExisting('dooh', stateName, city);
                    row.innerHTML = `
                        <p class="text-sm font-semibold text-gray-700 mb-2">${city}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative">
                                <input type="number" placeholder="OOH %" min="0.01" max="99" step="0.01"
                                    value="${eo !== null ? eo : ''}"
                                    data-state="${stateName}" data-city="${city}" data-field="ooh_commission"
                                    class="city-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                                <span class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                            </div>
                            <div class="relative">
                                <input type="number" placeholder="DOOH %" min="0.01" max="99" step="0.01"
                                    value="${ed !== null ? ed : ''}"
                                    data-state="${stateName}" data-city="${city}" data-field="dooh_commission"
                                    class="city-inp w-full border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#009A5C] pr-8">
                                <span class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">%</span>
                            </div>
                        </div>`;
                }
                section.appendChild(row);
            });
            wrap.appendChild(section);
        } catch (e) {
            console.error(`Failed to load cities for ${stateName}:`, e);
        }
    }
}

// ─── Shared save ───────────────────────────────────────
async function vSaveNow(applyAllCities, cityData) {
    const btn       = document.getElementById('vSaveBtn');
    const modal3Btn = document.getElementById('vModal3NextBtn');

    if (btn)                                  { btn.disabled = true;       btn.textContent = 'Saving...'; }
    if (modal3Btn && vState.applyAllStates)   { modal3Btn.disabled = true; modal3Btn.textContent = 'Saving...'; }

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
            // Bulk-refresh all commission displays using centralized function
            if (data.resolved_commissions) {
                updateAllCommissionDisplays(data.resolved_commissions);
            }

            closeAllModals();

            // Build a human-readable commission detail string
            let detail = '';
            if (vState.applyAllTypes) {
                detail = `Commission Rate: ${vState.baseCommission}% (OOH & DOOH)`;
            } else {
                const parts = [];
                if (vState.oohCommission  !== null) parts.push(`OOH: ${vState.oohCommission}%`);
                if (vState.doohCommission !== null) parts.push(`DOOH: ${vState.doohCommission}%`);
                detail = parts.join('  |  ');
            }

            const isUpdate = hasExisting;
            showSuccess(
                isUpdate ? 'Commission Updated!' : 'Commission Set!',
                isUpdate
                    ? 'Vendor commission has been updated. A notification has been sent to the vendor.'
                    : 'Vendor commission has been set. The vendor has been notified and asked to agree.',
                // detail
            );
        } else {
            alert(data.message || 'Something went wrong.');
        }
    } catch (e) {
        console.error('Commission save error:', e);
        alert('Network error. Please try again.');
    } finally {
        if (btn)       { btn.disabled = false;       btn.textContent = 'Save Commission'; }
        if (modal3Btn) { modal3Btn.disabled = false; modal3Btn.textContent = vState.applyAllStates ? 'Save Commission' : 'Next'; }
    }
}

async function vSubmit() {
    if (vState.applyAllCities === null) { alert('Please select an option.'); return; }

    if (!validateDynamicCommissionInputs('.city-inp')) {
        return;
    }

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