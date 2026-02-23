@extends('layouts.admin')
@section('title', 'All Hoardings')
@section('page_title', 'All Hoardings')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'All Hoardings', 'route' => route('admin.my-hoardings')],
    ['label' => 'Vendor\'s Hoardings']
]" />
@endsection

@push('styles')
<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .action-dropdown {
        position: fixed;
        min-width: 240px;
        transform-origin: top right;
        z-index: 9999;
    }
    td.relative {
        overflow: visible !important;
    }
</style>
@endpush

@section('content')
<div class="px-6 py-6 bg-gray-50 min-h-screen">

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">
                Vendor's Hoardings
            </h1>
            <p class="text-sm text-gray-500">Manage and review all vendor hoarding submissions</p>
        </div>
        
        {{-- Bulk Actions --}}
        <div id="bulkActionsContainer" class="hidden items-center gap-3 animate-fade-in flex-wrap">
            <span class="text-sm font-medium text-gray-600 bg-gray-100 px-3 py-1.5 rounded-lg">
                <span id="selectedCount" class="font-bold text-green-600">0</span> selected
            </span>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg text-sm font-medium flex items-center gap-2 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    Bulk Actions
                    <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden">
                    <div class="py-1">
                        <button onclick="bulkApprove()" class="w-full text-left px-4 py-2.5 hover:bg-blue-50 text-blue-600 font-medium flex items-center gap-3 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Approve & Activate
                        </button>
                        <button onclick="bulkActivate()" class="w-full text-left px-4 py-2.5 hover:bg-green-50 text-green-600 font-medium flex items-center gap-3 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Activate Selected
                        </button>
                        <button onclick="bulkDeactivate()" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-gray-600 font-medium flex items-center gap-3 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Deactivate Selected
                        </button>
                        <div class="border-t border-gray-100 my-1"></div>
                        <button onclick="bulkDelete()" class="w-full text-left px-4 py-2.5 hover:bg-red-50 text-red-600 font-medium flex items-center gap-3 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Section --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 sm:p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filters
            </h2>
        </div>
        <form method="GET" action="{{ route('admin.vendor-hoardings.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}"
                            placeholder="Title, address, city..."
                            class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 hover:border-gray-400 shadow-sm transition-all"
                        >
                    </div>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="px-4 py-2.5 w-full border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all shadow-sm hover:border-gray-400">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                {{-- Type Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Type</label>
                    <select name="type" class="px-4 py-2.5 w-full border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all shadow-sm hover:border-gray-400">
                        <option value="">All Types</option>
                        <option value="ooh" {{ request('type') === 'ooh' ? 'selected' : '' }}>OOH</option>
                        <option value="dooh" {{ request('type') === 'dooh' ? 'selected' : '' }}>DOOH</option>
                    </select>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-3 mt-4">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-600 hover:bg-green-700 active:scale-95 text-white rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Apply Filters
                </button>
                <a href="{{ route('admin.vendor-hoardings.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-white hover:bg-gray-50 active:scale-95 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold shadow-sm hover:shadow transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
        <div class="overflow-x-auto -mx-4 sm:mx-0 relative">
            <table class="min-w-full w-full text-sm">

                {{-- ================= THEAD ================= --}}
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 text-[11px] uppercase text-gray-700 font-semibold border-b-2 border-gray-200">
                    <tr>
                        <th class="px-5 py-4 w-8">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-green-600 focus:ring-green-500 focus:ring-2 transition-all cursor-pointer" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="px-5 py-4 w-12 text-gray-500">SN</th>
                        <th class="px-5 py-4 text-left">Hoarding Title</th>
                        <th class="px-5 py-4">Type</th>
                        <th class="px-5 py-4">Published By</th>
                        <th class="px-5 py-4">Hoarding Commission</th>
                        <th class="px-5 py-4">Location</th>
                        <th class="px-5 py-4 text-left md:ml-2">Status</th>
                        <th class="px-5 py-4">Progress</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>
                {{-- ================= TBODY ================= --}}
                <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($hoardings as $index => $hoarding)

                    @php
                        $isActive = $hoarding->status === 'active';

                        $overallCommission = $hoarding->vendor_commission
                            ? number_format($hoarding->vendor_commission, 0) . '%'
                            : '—';

                        $hoardingCommission = $hoarding->hoarding_commission
                            ? number_format($hoarding->hoarding_commission, 0) . '%'
                            : '—';

                        $progressPercent = $hoarding->completion ?? 0;
                        $progress = $progressPercent . '% Complete';
                    @endphp

                    <tr class="hover:bg-blue-50/30 transition-colors duration-150 group"
                        data-id="{{ $hoarding->id }}"
                        data-source="{{ $hoarding->source }}"
                    >

                        {{-- Checkbox --}}
                        <td class="px-5 py-4">
                            <input type="checkbox" class="hoarding-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500 focus:ring-2 transition-all cursor-pointer" value="{{ $hoarding->id }}" onchange="updateBulkActions()">
                        </td>

                        {{-- SN --}}
                        <td class="px-5 py-4 text-gray-500 font-medium">
                            {{ $hoardings->firstItem() + $index }}
                        </td>

                        {{-- Title --}}
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.hoardings.show', $hoarding->id) }}" class="text-green-600 font-semibold hover:text-green-700 hover:underline flex items-center gap-2 group-hover:gap-3 transition-all">
                                <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ $hoarding->title }}
                            </a>
                        </td>

                        {{-- Type --}}
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $hoarding->type === 'OOH' ? 'bg-purple-100 text-purple-700' : 'bg-cyan-100 text-cyan-700' }}">
                                {{ $hoarding->type }}
                            </span>
                        </td>

                        {{-- Vendor --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr($hoarding->vendor?->name ?? 'V', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        {{ $hoarding->vendor?->name ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-400">Vendor</div>
                                </div>
                            </div>
                        </td>

                        {{-- Hoarding Commission --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-50 text-green-700 font-semibold text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $hoardingCommission }}
                            </span>
                        </td>

                        {{-- Location --}}
                        <td class="px-5 py-4">
                            <div class="flex items-start gap-2 text-gray-600">
                                <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-sm line-clamp-2">{{ $hoarding->address ?? '-' }}</span>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                    @if($hoarding->status === 'active') bg-green-50 text-green-700
                                    @elseif($hoarding->status === 'pending_approval') bg-red-50 text-red-600
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ $hoarding->status === 'pending_approval' ? 'UNAPPROVED' : strtoupper($hoarding->status) }}
                                </span>
                            </div>
                        </td>

                        {{-- Progress --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all {{ $isActive ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-red-400' }}" style="width: {{ $progressPercent }}%"></div>
                                </div>
                                <span class="text-xs font-semibold {{ $isActive ? 'text-green-600' : 'text-red-500' }} whitespace-nowrap">
                                    {{ $progressPercent }}%
                                </span>
                            </div>
                        </td>

                        {{-- Action --}}
                        <td class="px-5 py-4 text-right relative">
                            <div x-data="{ open: false }" class="inline-block">
                                <button
                                    @click.stop="
                                        open = !open;
                                        if (open) {
                                            $nextTick(() => {
                                                const btn = $el;
                                                const dropdown = $refs.dropdown;
                                                const btnRect = btn.getBoundingClientRect();
                                                const dropdownHeight = dropdown.offsetHeight || 200;
                                                const spaceBelow = window.innerHeight - btnRect.bottom;

                                                dropdown.style.left = (btnRect.right - 240) + 'px';

                                                if (spaceBelow < dropdownHeight && btnRect.top > dropdownHeight) {
                                                    dropdown.style.top = (btnRect.top - dropdownHeight) + 'px';
                                                } else {
                                                    dropdown.style.top = (btnRect.bottom + 4) + 'px';
                                                }
                                            });
                                        }
                                    "
                                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>

                                <div
                                    x-ref="dropdown"
                                    x-show="open"
                                    @click.outside="open = false"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="action-dropdown bg-white rounded-lg shadow-xl border border-gray-200"
                                    style="display: none;">

                                    <div class="py-1">

                                        @if($hoarding->status === 'pending_approval')
                                            <button
                                                @click="open = false"
                                                onclick="approveAndActivateSingle({{ $hoarding->id }})"
                                                class="w-full text-left px-4 py-2.5 hover:bg-blue-50 text-blue-600 font-medium flex items-center gap-3 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Approve & Activate
                                            </button>
                                        @elseif($hoarding->status === 'active')
                                            <button
                                                @click="open = false"
                                                onclick="deactivateSingle({{ $hoarding->id }})"
                                                class="w-full text-left px-4 py-2.5 hover:bg-gray-50 text-gray-600 font-medium flex items-center gap-3 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Deactivate
                                            </button>
                                        @elseif($hoarding->status === 'inactive' || $hoarding->status === 'suspended')
                                            <button
                                                @click="open = false"
                                                onclick="activateSingle({{ $hoarding->id }})"
                                                class="w-full text-left px-4 py-2.5 hover:bg-green-50 text-green-600 font-medium flex items-center gap-3 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Activate
                                            </button>
                                        @endif

                                        @if($hoarding->status !== 'suspended')
                                            <button
                                                @click="open = false"
                                                onclick="suspendSingle({{ $hoarding->id }})"
                                                class="w-full text-left px-4 py-2.5 hover:bg-orange-50 text-orange-600 font-medium flex items-center gap-3 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                                Suspend
                                            </button>
                                        @endif

                                        <button
                                            class="w-full text-left px-4 py-2.5 hover:bg-green-50 text-green-600 font-medium flex items-center gap-3 transition-colors"
                                            @click="
                                                open = false;
                                                window.dispatchEvent(
                                                    new CustomEvent('open-hoarding-commission', {
                                                        detail: {
                                                            id: {{ $hoarding->id }},
                                                            title: '{{ addslashes($hoarding->title) }}',
                                                            source: '{{ $hoarding->source }}'
                                                        }
                                                    })
                                                )
                                            ">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Update Commission
                                        </button>

                                        <div class="border-t border-gray-100 my-1"></div>

                                        <button
                                            @click="open = false"
                                            onclick="deleteSingle({{ $hoarding->id }})"
                                            class="w-full text-left px-4 py-2.5 hover:bg-red-50 text-red-600 font-medium flex items-center gap-3 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-20">
                            <div class="flex flex-col items-center justify-center text-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-gray-500 text-lg font-medium mb-2">No Hoardings Found</p>
                                <p class="text-gray-400 text-sm">Try adjusting your filters or search criteria</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        {{-- Pagination --}}
        @if($hoardings->hasPages())
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs sm:text-sm text-gray-600 order-2 sm:order-1">
                    Showing <span class="font-semibold text-gray-900">{{ $hoardings->firstItem() }}</span> 
                    to <span class="font-semibold text-gray-900">{{ $hoardings->lastItem() }}</span> 
                    of <span class="font-semibold text-gray-900">{{ $hoardings->total() }}</span> results
                </div>
                <div class="flex gap-2 order-1 sm:order-2">
                    {{ $hoardings->withQueryString()->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@include('hoardings.admin.modals.vendor-commission-modal')
@include('hoardings.admin.modals.hoarding-commission-modal')
@endsection

@push('scripts')
<script>
    // Close all dropdowns when clicking outside or scrolling
    document.addEventListener('click', function () {
        document.querySelectorAll('[x-data]').forEach(el => {
            if (el._x_dataStack) {
                // handled by alpine click.outside
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {

        document.querySelectorAll('.status-toggle').forEach(button => {

            button.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const btn = e.currentTarget;

                if (btn.dataset.loading === '1') return;
                btn.dataset.loading = '1';

                const id = btn.dataset.id;
                const source = btn.dataset.source;
                const hoardingCommission = btn.dataset.hoardingCommission;

                if (!hoardingCommission || hoardingCommission == 0) {
                    btn.dataset.loading = '0';

                    window.dispatchEvent(
                        new CustomEvent('open-hoarding-commission', {
                            detail: {
                                id: id,
                                title: 'Set Hoarding Commission',
                                source: source
                            }
                        })
                    );
                    return;
                }

                const toggleUrl = '/admin/vendor-hoardings/' + id + '/toggle-status';
                fetch(toggleUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error();
                    return res.json();
                })
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated',
                        text: data.status === 'active'
                            ? 'Hoarding has been published successfully.'
                            : 'Hoarding has been unpublished successfully.',
                        confirmButtonColor: '#16a34a',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 1800);
                })
                .catch(() => {
                    btn.dataset.loading = '0';

                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: 'Unable to update hoarding status'
                    });
                });
            });
        });
    });

    // Bulk Actions Functionality
    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.hoarding-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.hoarding-checkbox:checked');
        const count = checkboxes.length;
        const bulkContainer = document.getElementById('bulkActionsContainer');
        const selectedCount = document.getElementById('selectedCount');
        
        if (count > 0) {
            bulkContainer.classList.remove('hidden');
            bulkContainer.classList.add('flex');
            selectedCount.textContent = count;
        } else {
            bulkContainer.classList.add('hidden');
            bulkContainer.classList.remove('flex');
        }
        
        const allCheckboxes = document.querySelectorAll('.hoarding-checkbox');
        const selectAllCheckbox = document.getElementById('selectAll');
        selectAllCheckbox.checked = count === allCheckboxes.length && count > 0;
    }

    function getSelectedIds() {
        const checkboxes = document.querySelectorAll('.hoarding-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    function bulkApprove() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Approve & Activate Hoardings',
            text: `Are you sure you want to approve and activate ${ids.length} hoarding(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Approve & Activate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we approve the hoardings',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                fetch('/admin/vendor-hoardings/bulk-approve', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to approve hoardings' });
                });
            }
        });
    }

    function bulkDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Delete Hoardings?',
            text: `Are you sure you want to delete ${ids.length} hoarding(s)? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete them',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Deleting...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch('/admin/vendor-hoardings/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete hoardings' });
                });
            }
        });
    }

    function bulkActivate() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Activate Hoardings?',
            text: `Activate ${ids.length} hoarding(s)? Note: Commission must be set first.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, activate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Activating...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch('/admin/vendor-hoardings/bulk-activate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Activated!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message, confirmButtonColor: '#dc2626' });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to activate hoardings' });
                });
            }
        });
    }

    function bulkDeactivate() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        Swal.fire({
            title: 'Deactivate Hoardings?',
            text: `Deactivate ${ids.length} hoarding(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6b7280',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, deactivate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Deactivating...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch('/admin/vendor-hoardings/bulk-deactivate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deactivated!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to deactivate hoardings' });
                });
            }
        });
    }

    // Single hoarding actions
    function approveAndActivateSingle(id) {
        Swal.fire({
            title: 'Approve & Activate Hoarding',
            text: 'Are you sure you want to approve and activate this hoarding?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Approve & Activate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Processing...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch('/admin/vendor-hoardings/bulk-approve', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: [id] })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Success!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                });
            }
        });
    }

    function activateSingle(id) {
        Swal.fire({
            title: 'Activate Hoarding?',
            text: 'Commission must be set first.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, activate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Activating...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch('/admin/vendor-hoardings/bulk-activate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: [id] })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Activated!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message, confirmButtonColor: '#dc2626' });
                    }
                });
            }
        });
    }

    function deactivateSingle(id) {
        Swal.fire({
            title: 'Deactivate Hoarding?',
            text: 'This will unpublish the hoarding.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6b7280',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, deactivate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Deactivating...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch('/admin/vendor-hoardings/bulk-deactivate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: [id] })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deactivated!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                });
            }
        });
    }

    function deleteSingle(id) {
        Swal.fire({
            title: 'Delete Hoarding?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Deleting...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch('/admin/vendor-hoardings/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ ids: [id] })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                });
            }
        });
    }

    function suspendSingle(id) {
        Swal.fire({
            title: 'Suspend Hoarding?',
            text: 'This will temporarily suspend the hoarding.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea580c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, suspend it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Suspending...', text: 'Please wait', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                fetch(`/admin/vendor-hoardings/${id}/suspend`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: 'Suspended!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to suspend hoarding' });
                });
            }
        });
    }
</script>
@endpush