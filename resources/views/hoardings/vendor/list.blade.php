@extends('layouts.vendor')

@section('title', 'My Hoardings')

@section('content')
<div class="bg-[#F8F9FA] ">
    <div class=" top-0 z-1 bg-white border-b border-gray-100 shadow-sm">
        <div class="max-w-[1600px] mx-auto px-6">
            <div class="flex gap-8">
                <a href="{{ route('vendor.hoardings.myHoardings', ['tab' => 'all']) }}" 
                   class="py-4 px-1 border-b-2 {{ $activeTab !== 'draft' ? 'border-[#00A86B] text-[#00A86B] font-semibold' : 'border-transparent text-gray-400' }} text-sm transition-all whitespace-nowrap">
                    My Hoardings
                </a>
                <a href="{{ route('vendor.hoardings.myHoardings', ['tab' => 'draft']) }}" 
                   class="py-4 px-1 border-b-2 {{ $activeTab === 'draft' ? 'border-[#00A86B] text-[#00A86B] font-semibold' : 'border-transparent text-gray-400' }} text-sm transition-all whitespace-nowrap">
                    Hoarding In Draft
                </a>
            </div>

            <div class="py-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                <form id="searchForm"
                    method="GET"
                    action="{{ route('vendor.hoardings.myHoardings', ['tab' => $activeTab]) }}"
                    class="relative flex-1 flex items-center w-full">

                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by hoarding title or location....."
                        onkeydown="autoSearchHoardings(event)"
                        class="block w-full pl-8 pr-2 py-2 bg-[#F3F4F6] border-none rounded-md focus:ring-1 focus:ring-emerald-500 text-[13px]"
                    >

                    @if(request()->filled('letter'))
                        <input type="hidden" name="letter" value="{{ request('letter') }}">
                    @endif
                    @if(request()->filled('per_page'))
                        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                    @if(request()->filled('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    @if(request()->filled('booked'))
                        <input type="hidden" name="booked" value="{{ request('booked') }}">
                    @endif
                    @if(request()->filled('hoarding_type'))
                        <input type="hidden" name="hoarding_type" value="{{ request('hoarding_type') }}">
                    @elseif(request()->filled('type'))
                        <input type="hidden" name="hoarding_type" value="{{ request('type') }}">
                    @endif


                    
                    @if(request()->filled('screen_size_min'))
                        <input type="hidden" name="screen_size_min" value="{{ request('screen_size_min') }}">
                    @endif
                    @if(request()->filled('screen_size_max'))
                        <input type="hidden" name="screen_size_max" value="{{ request('screen_size_max') }}">
                    @endif
                    @if(request()->filled('hoarding_size_min'))
                        <input type="hidden" name="hoarding_size_min" value="{{ request('hoarding_size_min') }}">
                    @endif
                    @if(request()->filled('hoarding_size_max'))
                        <input type="hidden" name="hoarding_size_max" value="{{ request('hoarding_size_max') }}">
                    @endif

                    @foreach((array) request('category', []) as $category)
                        <input type="hidden" name="category[]" value="{{ $category }}">
                    @endforeach
                    @foreach((array) request('availability', []) as $availability)
                        <input type="hidden" name="availability[]" value="{{ $availability }}">
                    @endforeach
                    @foreach((array) request('surroundings', []) as $surrounding)
                        <input type="hidden" name="surroundings[]" value="{{ $surrounding }}">
                    @endforeach
                    @foreach((array) request('resolution', []) as $resolution)
                        <input type="hidden" name="resolution[]" value="{{ $resolution }}">
                    @endforeach
                </form>

{{-- 
                <div class="flex items-center gap-2">
                    <!-- <button class="p-2 bg-[#E6F6F0] text-[#00A86B] rounded-md border border-emerald-50">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M18.9132 3.18537C14.3186 2.65548 9.68118 2.65548 5.08664 3.18537C5.0162 3.19371 4.94922 3.22139 4.89269 3.26555C4.83616 3.3097 4.79214 3.3687 4.76524 3.43639C4.73834 3.50408 4.72954 3.57798 4.73976 3.65038C4.74997 3.72278 4.77883 3.79102 4.82332 3.84799L8.88513 9.0297C10.34 10.8857 11.1338 13.2007 11.1337 15.5879V19.0308L12.8661 20.3418V15.5867C12.8663 13.1999 13.66 10.8854 15.1147 9.0297L19.1765 3.84799C19.221 3.79102 19.2498 3.72278 19.2601 3.65038C19.2703 3.57798 19.2615 3.50408 19.2346 3.43639C19.2077 3.3687 19.1637 3.3097 19.1071 3.26555C19.0506 3.22139 18.9836 3.19371 18.9132 3.18537ZM4.89492 1.40848C9.61726 0.86384 14.3837 0.86384 19.106 1.40848C20.776 1.60154 21.581 3.62275 20.5243 4.9718L16.4625 10.1535C15.2566 11.6914 14.5986 13.6096 14.5984 15.5879V22.1055C14.5986 22.2708 14.5543 22.4329 14.4705 22.5738C14.3867 22.7146 14.2667 22.8288 14.1238 22.9035C13.9809 22.9782 13.8207 23.0106 13.6611 22.997C13.5014 22.9834 13.3486 22.9244 13.2195 22.8265L9.75477 20.2047C9.64513 20.1216 9.55601 20.013 9.49461 19.8878C9.43321 19.7625 9.40127 19.6241 9.40137 19.4837V15.5867C9.40137 13.6084 8.74307 11.6909 7.53851 10.1523L3.4767 4.97299C2.41881 3.62394 3.22262 1.60154 4.89492 1.40848Z" fill="#009A5C"/>
                        </svg>
                    </button> -->
                    <button type="button" onclick="showExportFormatPrompt()" class="px-10 py-2 bg-[#00A86B] hover:bg-emerald-700 text-white font-medium rounded-md text-[13px] cursor-pointer">
                        Export
                    </button>
                    <a href="{{ route('vendor.hoardings.add') }}" 
                        class="px-5 py-2 bg-black text-white font-medium rounded-md text-[13px] flex items-center">
                        + Add New Hoarding
                    </a>
                </div> --}}
                <div class="flex items-center gap-2">
                    <button type="button"
                        onclick="openHoardingFilterModal()"
                        class="inline-flex items-center justify-center w-10 h-10 bg-[#E6F6F0] text-[#00A86B] rounded-md border border-emerald-100 hover:bg-emerald-100 transition"
                        title="Filter Hoardings">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6H20L14 13V19L10 21V13L4 6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <button type="button" onclick="showExportFormatPrompt()"
                        class="px-6 py-2 bg-[#00A86B] hover:bg-emerald-700 text-white font-medium rounded-md text-[13px] cursor-pointer whitespace-nowrap flex-shrink-0">
                        Export
                    </button>

                    <a href="{{ route('vendor.hoardings.add') }}"
                        class="px-4 py-2 bg-black text-white font-medium rounded-md text-[13px] flex items-center whitespace-nowrap flex-shrink-0">
                        + Add New Hoarding
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('hoardings.vendor.filter')

    <div class="max-w-[1600px] mx-auto py-6 space-y-5">
        {{-- <div class="flex flex-wrap gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
            @foreach(range('A', 'Z') as $char)
                <a href="#" class="hover:text-blue-500 {{ $char == 'H' ? 'text-blue-500 underline' : '' }}">{{ $char }}</a>
            @endforeach
        </div> --}}

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto" style="max-height: 597px; overflow-y: auto;">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="p-6">
                            @php
                                $selectedLetter = request('letter');
                            @endphp
                            <div class="flex flex-wrap gap-2 text-[11px] px-6 pt-6 font-bold text-gray-400 uppercase tracking-widest px-1">
                                @foreach(range('A', 'Z') as $char)
                                    <a
                                        href="{{ route('vendor.hoardings.myHoardings', array_merge(request()->except(['page', 'letter']), ['tab' => $activeTab, 'letter' => $char])) }}"
                                        class="
                                            {{ $selectedLetter === $char
                                                ? 'text-[#00A86B] underline'
                                                : 'hover:text-[#00A86B]' }}
                                        "
                                    >
                                        {{ $char }}
                                    </a>
                                @endforeach

                                @if($selectedLetter)
                                    <a
                                        href="{{ route('vendor.hoardings.myHoardings', array_merge(request()->except(['page', 'letter']), ['tab' => $activeTab])) }}"
                                        class="ml-3 text-red-500 hover:underline"
                                    >
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </tr>
                        <tr class="bg-white border-b border-gray-100 text-[12px] font-black text-gray-600  tracking-widest">
                            <th class="px-6 py-5 w-12">
                                <input type="checkbox" id="select-all" class="rounded-sm border-gray-300">
                            </th>
                            <th class="px-4 py-5">SN</th>
                            <th class="px-4 py-5">Hoarding Title</th>
                            <th class="px-4 py-5">Type</th>
                            <th class="px-4 py-5">Location</th>
                            @if($activeTab !== 'draft')
                            <th class="px-4 py-5">No of Bookings</th>
                            @endif
                            <th class="px-4 py-5">Status</th>
                            <th class="px-4 py-5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-[13px]">
                        @forelse($hoardings as $index => $hoarding)
                        @php 
                            $status = strtolower($hoarding['status']);
                            $isActive = in_array($status, ['active', 'published']);
                            $isPending = $status === 'pending_approval';
                            $isDraft   = $status === 'draft';
                            $canEdit   = $isDraft || $isPending;
                        @endphp
                        <tr class="hover:bg-[#F0F9FF] border-b border-gray-50 last:border-0 transition-colors">
                            <td class="px-6 py-4"><input type="checkbox" class="row-checkbox rounded-sm border-gray-300"></td>
                            <td class="px-4 py-4 text-gray-400 font-medium">{{ sprintf('%02d', $index + 1) }}</td>
                            <td class="px-4 py-4 text-gray-700">
                                <a href="{{ route('vendor.myHoardings.show', $hoarding['id']) }}"
                                target="_blank"
                                class="text-[#00A86B] font-medium hover:underline">
                                    {{ !empty($hoarding['title']) ? $hoarding['title'] : 'NA' }}
                                </a>
                            </td>
                            <td class="px-4 py-4 uppercase tracking-wide text-gray-500">
                                {{ !empty($hoarding['hoarding_type']) ? $hoarding['hoarding_type'] : 'NA' }}
                            </td>
                            <td class="px-4 py-4 text-gray-400 truncate max-w-[180px]">
                                {{ !empty($hoarding['location']) ? $hoarding['location'] : 'NA' }}
                            </td>
                            
                            @if($activeTab !== 'draft')
                            <td class="px-4 py-4 text-gray-500 underline decoration-gray-200">
                                {{ $hoarding['bookings_count'] ?? '0' }}
                            </td>
                            @endif

                            <td class="px-4 py-4">
                            @if(strtolower($hoarding['status']) === 'draft')
                                <span class="text-gray-500 text-[11px] font-bold">Draft</span>
                            @elseif($isPending)
                                <span class="text-orange-500 text-[11px] font-bold">Pending Approval</span>
                            @else
                                <form action="{{ route('vendor.hoardings.toggle', $hoarding['id']) }}"
                                    method="POST"
                                    class="inline-flex items-center gap-2">
                                    @csrf
                                    <!-- TOGGLE -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                                type="checkbox"
                                                class="sr-only peer"
                                                {{ $isActive ? 'checked' : '' }}
                                                onclick="return confirmToggle(event, this)"
                                            >
                                        <div class="
                                            w-9 h-5 bg-gray-300 rounded-full
                                            peer-checked:bg-emerald-500
                                            transition-colors
                                            after:content-['']
                                            after:absolute after:top-[2px] after:left-[2px]
                                            after:h-4 after:w-4
                                            after:bg-white after:rounded-full
                                            after:transition-all
                                            peer-checked:after:translate-x-4
                                        "></div>
                                    </label>
                                    <!-- STATUS TEXT -->
                                    <span class="
                                        text-[11px] font-bold uppercase tracking-wide
                                        {{ $isActive ? 'text-emerald-600' : 'text-gray-400' }}
                                    ">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </form>
                            @endif
                        </td>



                            <td class="px-4 py-4 text-center">
                                <div class="relative inline-block text-left">
                                    <button type="button" onclick="toggleActionMenu(event, 'menu-{{ $hoarding['id'] }}')" 
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded">
                                        <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                    </button>

                                    <div id="menu-{{ $hoarding['id'] }}" 
                                         class="hidden absolute right-0 -mt-[100px] w-44 bg-white rounded-lg shadow-xl border border-gray-100 z-[100] overflow-hidden">
                                        <div class="p-1 space-y-1 text-left">
                                            @if($canEdit)
                                                <a href="{{ route('vendor.hoardings.edit', $hoarding['id']) }}" 
                                                class="flex items-center gap-2 px-3 py-2 text-[12px] font-medium text-blue-600 hover:bg-blue-50 rounded">
                                                    <i class="fa-solid fa-pen-to-square opacity-60"></i> 
                                                    {{ $activeTab === 'draft' ? 'Edit Draft' : 'Edit Hoarding' }}
                                                </a>
                                            @endif

                                            {{-- Toggle Active/Inactive only for non-draft and non-pending hoardings --}}
                                            @if($activeTab !== 'draft' && !in_array($status, ['pending_approval']))
                                                <form action="{{ route('vendor.hoardings.toggle', $hoarding['id']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-[12px] font-medium {{ $isActive ? 'text-orange-500 hover:bg-orange-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded">
                                                        <i class="fa-solid {{ $isActive ? 'fa-pause-circle' : 'fa-play-circle' }} opacity-60"></i> 
                                                        {{ $isActive ? 'Make Inactive' : 'Make Active' }}
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Delete option with confirmation --}}
                                            <button
                                                type="button"
                                                onclick="deleteHoarding({{ $hoarding['id'] }})"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-[12px] font-medium text-red-500 hover:bg-red-50 rounded">
                                                <i class="fa-solid fa-trash-can opacity-60"></i> Delete
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-16 text-gray-400">
                                <div style="width: calc(100vw - 80px); display: flex; justify-content: center;" class="md:w-full">
                                    No records found.
                                </div>
                            </td>
                        </tr>                 
                       @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-50 text-[12px] text-gray-500">
                <div class="flex items-center gap-3 font-medium">
                    <form id="perPageForm" method="GET" action="" class="flex items-center gap-2">
                        @foreach(request()->except('page', 'per_page') as $key => $val)
                            @if(is_array($val))
                                @foreach($val as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endif
                        @endforeach
                        <label for="perPageSelect" class="mr-1 text-gray-500">Show</label>
                        <select id="perPageSelect" name="per_page" onchange="document.getElementById('perPageForm').submit()" class="bg-[#F3F4F6] border-none rounded-md px-3 py-1 text-gray-700 font-bold">
                            @foreach([5, 10, 20, 50, 100] as $size)
                                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        <span class="ml-2">per page</span>
                    </form>
                    <span>
                        Showing {{ ($hoardings->firstItem() ?? 0) }} to {{ ($hoardings->lastItem() ?? count($hoardings)) }} of {{ ($hoardings->total() ?? count($hoardings)) }} records
                    </span>
                </div>
                <div>
                    {{ $hoardings->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.swal-compact {
    border-radius: 12px !important;
}

@media (max-width: 640px) {
    .swal-compact {
        width: 90% !important;
        padding: 1rem !important;
    }
}
/* CONFIRM BUTTON */
.swal-btn-confirm {
    background-color: #00A86B !important;
    color: #ffffff !important;
    border-radius: 6px !important;
    padding: 10px 28px !important;
    font-weight: 500;
}

/* CANCEL BUTTON — FIRST IMAGE STYLE */
.swal-btn-cancel {
    background-color: #e5e7eb !important; /* light gray */
    color: #000000 !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 10px 28px !important;
    font-weight: 500;
}

/* hover effect (optional but image jaisa) */
.swal-btn-cancel:hover {
    background-color: #d1d5db !important;
}

#hoarding-filter-modal .hoarding-filter-panel {
    border: 1px solid #e5e7eb;
    box-shadow: 0 14px 38px rgba(15, 23, 42, 0.16);
}

#hoarding-filter-modal .hoarding-filter-title {
    font-size: clamp(28px, 1.9vw, 36px);
    line-height: 1;
    font-weight: 600;
    color: #101828;
}

#hoarding-filter-modal .hoarding-filter-body {
    color: #1f3152;
}

#hoarding-filter-modal .hoarding-filter-body input[type="checkbox"],
#hoarding-filter-modal .hoarding-filter-body input[type="radio"] {
    width: 15px;
    height: 15px;
    accent-color: #2f6d4f;
}

#hoarding-filter-modal .hoarding-filter-body input[type="number"] {
    height: 44px;
    border-color: #d9dee6;
    color: #334155;
}

#hoarding-filter-modal .hoarding-filter-body input[type="number"]::placeholder {
    color: #64748b;
}

.range-slider {
    position: relative;
    height: 20px;
}

.range-slider .range-track {
    position: absolute;
    top: 8px;
    left: 0;
    right: 0;
    height: 4px;
    border-radius: 9999px;
    background: #2f6d4f;
}

.range-slider .range-fill {
    position: absolute;
    top: 8px;
    height: 4px;
    border-radius: 9999px;
    background: #2f6d4f;
}

.range-slider input[type="range"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 20px;
    background: transparent;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
}

.range-slider input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    pointer-events: auto;
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    border: 3px solid #2f6d4f;
    background: #ffffff;
    cursor: pointer;
}

.range-slider input[type="range"]::-moz-range-thumb {
    pointer-events: auto;
    width: 18px;
    height: 18px;
    border-radius: 9999px;
    border: 3px solid #2f6d4f;
    background: #ffffff;
    cursor: pointer;
}

@media (max-width: 1024px) {
    #hoarding-filter-modal .hoarding-filter-panel {
        top: 16px;
        width: calc(100vw - 20px);
        max-width: 720px;
        max-height: calc(100vh - 32px);
    }

    #hoarding-filter-modal .hoarding-filter-title {
        font-size: 34px;
    }

    #hoarding-filter-modal .hoarding-filter-body {
        max-height: calc(100vh - 180px);
    }
}

@media (max-width: 640px) {
    #hoarding-filter-modal .hoarding-filter-panel {
        top: 8px;
        width: calc(100vw - 10px);
        max-height: calc(100vh - 16px);
    }

    #hoarding-filter-modal .hoarding-filter-header,
    #hoarding-filter-modal .hoarding-filter-body,
    #hoarding-filter-modal .hoarding-filter-footer {
        padding-left: 16px;
        padding-right: 16px;
    }

    #hoarding-filter-modal .hoarding-filter-title {
        font-size: 32px;
    }

    #hoarding-filter-modal .hoarding-filter-body {
        max-height: calc(100vh - 200px);
    }
}

</style>

<script>
    function setupDualRange(prefix) {
        const minInput = document.getElementById(prefix + '_min_input');
        const maxInput = document.getElementById(prefix + '_max_input');
        const minSlider = document.getElementById(prefix + '_min_slider');
        const maxSlider = document.getElementById(prefix + '_max_slider');
        const rangeRoot = document.querySelector('[data-range="' + prefix + '"]');
        if (!minInput || !maxInput || !minSlider || !maxSlider || !rangeRoot) return;

        const fill = rangeRoot.querySelector('.range-fill');
        const minBound = Number(minSlider.min || 0);
        const maxBound = Number(minSlider.max || 1000);

        const clamp = (value, low, high) => Math.min(high, Math.max(low, value));

        const paintFill = (minVal, maxVal) => {
            if (!fill) return;
            const left = ((minVal - minBound) / (maxBound - minBound)) * 100;
            const right = ((maxVal - minBound) / (maxBound - minBound)) * 100;
            fill.style.left = left + '%';
            fill.style.width = Math.max(0, right - left) + '%';
        };

        const syncFromInputs = () => {
            let minVal = Number(minInput.value || minBound);
            let maxVal = Number(maxInput.value || maxBound);

            minVal = clamp(minVal, minBound, maxBound);
            maxVal = clamp(maxVal, minBound, maxBound);
            if (minVal > maxVal) {
                minVal = maxVal;
            }

            minInput.value = minVal;
            maxInput.value = maxVal;
            minSlider.value = minVal;
            maxSlider.value = maxVal;
            paintFill(minVal, maxVal);
        };

        const syncFromSliders = (source) => {
            let minVal = Number(minSlider.value);
            let maxVal = Number(maxSlider.value);

            if (source === 'min' && minVal > maxVal) {
                minVal = maxVal;
                minSlider.value = minVal;
            }

            if (source === 'max' && maxVal < minVal) {
                maxVal = minVal;
                maxSlider.value = maxVal;
            }

            minInput.value = minVal;
            maxInput.value = maxVal;
            paintFill(minVal, maxVal);
        };

        minInput.addEventListener('input', syncFromInputs);
        maxInput.addEventListener('input', syncFromInputs);
        minSlider.addEventListener('input', () => syncFromSliders('min'));
        maxSlider.addEventListener('input', () => syncFromSliders('max'));

        syncFromInputs();
    }

    function openHoardingFilterModal() {
        const modal = document.getElementById('hoarding-filter-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeHoardingFilterModal() {
        const modal = document.getElementById('hoarding-filter-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeHoardingFilterModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        setupDualRange('screen_size');
        setupDualRange('hoarding_size');
    });
</script>

<script>
    // Select/Deselect all checkboxes
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }

        // Keep parent in sync if all/none are checked
        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                const noneChecked = Array.from(rowCheckboxes).every(c => !c.checked);
                if (allChecked) {
                    selectAll.checked = true;
                    selectAll.indeterminate = false;
                } else if (noneChecked) {
                    selectAll.checked = false;
                    selectAll.indeterminate = false;
                } else {
                    selectAll.checked = false;
                    selectAll.indeterminate = true;
                }
            });
        });
    });
</script>

<script>
    function closeAllMenus() {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
    function toggleActionMenu(event, menuId) {
        event.stopPropagation();
        const targetMenu = document.getElementById(menuId);
        closeAllMenus(); 
        targetMenu.classList.toggle('hidden');
    }
    document.addEventListener('click', closeAllMenus);
    document.addEventListener('scroll', closeAllMenus, {
        capture: true,
        passive: true
    });
</script>
<script>
    function deleteHoarding(id) {
        Swal.fire({
            title: 'Delete Hoarding?',
            html: '<p class="text-sm text-gray-600">This action cannot be undone.</p>',
            icon: 'warning',

            // 🎯 Size control
            width: '30rem',          // desktop
            padding: '1.25rem',      // compact height
            iconColor: '#ef4444',

            // 🎯 Buttons
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',

            // 🎯 Button styling
            buttonsStyling: true,
            reverseButtons: true,

            // 🎯 Mobile optimization
            customClass: {
                popup: 'swal-compact',
                title: 'text-base font-semibold',
                confirmButton: 'px-4 py-2 text-sm',
                cancelButton: 'px-4 py-2 text-sm'
            }
        }).then((result) => {

            if (!result.isConfirmed) return;

            // loader
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/vendor/hoardings/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Hoarding deleted successfully.',
                        timer: 1800,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });

                    setTimeout(() => location.reload(), 1800);
                }
            });
        });
    }
</script>
<script>
function confirmToggle(e, checkbox) {
    e.preventDefault();

    const form = checkbox.closest('form');
    const isActiveNow = checkbox.checked;

    Swal.fire({
        title: isActiveNow ? 'Active Hoarding?' : 'Inactive Hoarding?',
        html: isActiveNow
            ? '<p class="text-sm text-gray-600">Are you sure you want to Active this hoarding? It will be visible to customers.</p>'
            : '<p class="text-sm text-gray-600">Are you sure you want to Inactive this hoarding? It will not be visible to customers until re-activated.</p>',
        width:'25rem',
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: isActiveNow ? 'Active' : 'Inactive',
        cancelButtonText: 'Cancel',
        confirmButtonColor: isActiveNow ? '#00A86B' : '#ef4444',
        buttonsStyling: true,
        customClass: {
            popup: 'rounded-lg',
            title: 'text-base font-semibold text-gray-800',
            confirmButton: 'px-6 py-2 text-sm',
            cancelButton: 'px-6 py-2 text-sm swal-cancel-black'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Toggle the checkbox state and submit
            checkbox.checked = !isActiveNow;
            form.submit();
        }
        // If cancelled, do nothing: checkbox remains in its original state, form is not submitted
    });

    // Always return false to prevent the default checkbox toggle
    return false;
}
</script>
<script>
function autoSearchHoardings(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('searchForm').submit();
    }
}
</script>

@if(session('swal_success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: '{{ session('swal_type', 'success') }}',
            title: '{{ session('swal_type') === 'warning' ? 'Inactive!' : 'Success!' }}',
            text: '{{ session('swal_success') }}',
            showConfirmButton: false,
            timer: 1800,
            toast: true,
            position: 'top-end'
        });
    </script>
@endif
<script>
    function showExportFormatPrompt() {
        Swal.fire({
            title: 'Export Hoardings',
            text: 'Select the format you want to export:',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Export',
            confirmButtonColor: '#00A86B',
            cancelButtonText: 'Cancel',
            input: 'radio',
            inputOptions: {
                'csv': 'CSV',
                'excel': 'Excel',
                'pdf': 'PDF'
            },
            customClass: {
                input: 'cursor-pointer'
            },
            inputValidator: (value) => {
                if (!value) {
                    return 'Please select a format!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                let format = result.value;
                // Build export URL with current filters
                let url = new URL(window.location.href);
                url.searchParams.set('export', '1');
                url.searchParams.set('format', format);
                window.location.href = url.toString();
            }
        });
    }
</script>
@endsection