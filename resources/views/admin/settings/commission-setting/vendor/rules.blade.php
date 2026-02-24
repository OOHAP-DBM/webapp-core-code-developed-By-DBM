{{-- resources/views/admin/settings/commission-setting/vendor/rules.blade.php --}}
@extends('layouts.admin')

@section('title', 'Commission Rules — ' . $vendor->name)
@section('page_title', 'Commission Rules')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',               'route' => route('admin.dashboard')],
    ['label' => 'Commission Settings','route' => route('admin.commission.index')],
    ['label' => $vendor->name,        'route' => route('admin.commission.vendor.hoardings', $vendor)],
    ['label' => 'Rules']
]" />
@endsection

@section('content')
<div class="p-6 max-w-7xl mx-auto">

    {{-- ── Page header ── --}}
    <div class="flex items-start justify-between mb-8">
        <div>
            <!-- <h1 class="text-xl font-bold text-gray-900">Commission Rules</h1> -->
            <p class="text-sm text-gray-500 mt-1">
                Full rule hierarchy for <span class="font-semibold text-gray-700">{{ $vendor->name }}</span>
                @if($vendor->business_name) — {{ $vendor->business_name }} @endif
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.commission.vendor.hoardings', $vendor) }}"
               class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-semibold text-gray-600 hover:bg-gray-50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Hoardings
            </a>
            <a href="{{ route('admin.commission.vendor.hoardings', $vendor) }}"
               onclick="event.preventDefault(); window.location.href=this.href+'#open-modal';"
               class="px-4 py-2.5 bg-[#009A5C] text-white rounded-xl text-sm font-semibold hover:bg-[#007a49] transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Rules
            </a>
        </div>
    </div>

    @if(!$hasAnyRules)
    {{-- Empty state --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No commission rules set</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
            No commission rules have been configured for this vendor yet.
        </p>
        <a href="{{ route('admin.commission.vendor.hoardings', $vendor) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#009A5C] text-white rounded-xl text-sm font-semibold hover:bg-[#007a49] transition">
            Set Commission Rules
        </a>
    </div>

    @else

    {{-- ── Priority legend bar ── --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm px-6 py-4 mb-6 flex items-center gap-6 text-xs text-gray-500 overflow-x-auto">
        <span class="font-semibold text-gray-700 whitespace-nowrap">Priority (highest → lowest):</span>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <span class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center text-white text-xs font-bold">5</span>
            <span class="font-medium text-gray-700">Hoarding Override</span>
        </div>
        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <span class="w-5 h-5 rounded-full bg-[#F97316] flex items-center justify-center text-white text-xs font-bold">4</span>
            <span class="font-medium text-gray-700">City Rule</span>
        </div>
        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <span class="w-5 h-5 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold">3</span>
            <span class="font-medium text-gray-700">State Rule</span>
        </div>
        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <span class="w-5 h-5 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">2</span>
            <span class="font-medium text-gray-700">Hoarding Type Rule</span>
        </div>
        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <span class="w-5 h-5 rounded-full bg-[#009A5C] flex items-center justify-center text-white text-xs font-bold">1</span>
            <span class="font-medium text-gray-700">Global Rule</span>
        </div>
    </div>

    @php
        $typeLabel = fn($t) => match($t) { 'all' => 'OOH & DOOH', 'ooh' => 'OOH', 'dooh' => 'DOOH', default => strtoupper($t) };
        $typeBadge = fn($t) => match($t) {
            'all'  => 'bg-[#E8F7F0] text-[#009A5C] border-[#009A5C]/20',
            'ooh'  => 'bg-blue-50 text-blue-600 border-blue-200',
            'dooh' => 'bg-purple-50 text-purple-600 border-purple-200',
            default => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    @endphp

    <div class="space-y-5">

        {{-- ════════════════════════════════════════════════
             LEVEL 1 — Global rules
        ════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="w-7 h-7 rounded-full bg-[#009A5C] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">1</span>
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">Global Rules</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Apply to all states and cities unless overridden</p>
                </div>
                @if($globalRules->isEmpty())
                    <span class="ml-auto text-xs text-gray-300 font-medium">Not configured</span>
                @else
                    <span class="ml-auto text-xs text-[#009A5C] font-semibold bg-[#E8F7F0] px-2.5 py-1 rounded-full">
                        {{ $globalRules->count() }} rule{{ $globalRules->count() !== 1 ? 's' : '' }}
                    </span>
                @endif
            </div>

            @if($globalRules->isEmpty())
            <div class="px-6 py-5 text-sm text-gray-400 italic">No global rules set.</div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach($globalRules as $rule)
                <div class="px-6 py-4 flex items-center gap-4">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-600">Applies to</span>
                        <span class="inline-flex items-center ml-2 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeBadge($rule->hoarding_type) }}">
                            {{ $typeLabel($rule->hoarding_type) }}
                        </span>
                        <span class="text-xs text-gray-400 ml-2">— All states, all cities</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black text-gray-900">{{ number_format($rule->commission_percent, 2) }}<span class="text-sm font-bold text-gray-500">%</span></span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════
             LEVEL 2 — Hoarding type rules
        ════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="w-7 h-7 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">2</span>
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">Hoarding Type Rules</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Apply to all OOH or all DOOH unless overridden by state/city/hoarding</p>
                </div>
                @if($hoardingTypeRules->isEmpty())
                    <span class="ml-auto text-xs text-gray-300 font-medium">Not configured</span>
                @else
                    <span class="ml-auto text-xs text-indigo-700 font-semibold bg-indigo-50 px-2.5 py-1 rounded-full">
                        {{ $hoardingTypeRules->count() }} rule{{ $hoardingTypeRules->count() !== 1 ? 's' : '' }}
                    </span>
                @endif
            </div>

            @if($hoardingTypeRules->isEmpty())
            <div class="px-6 py-5 text-sm text-gray-400 italic">No hoarding type rules set.</div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach($hoardingTypeRules as $rule)
                <div class="px-6 py-4 flex items-center gap-4">
                    <div class="flex-1">
                        <span class="text-sm font-medium text-gray-600">Applies to</span>
                        <span class="inline-flex items-center ml-2 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeBadge($rule->hoarding_type) }}">
                            {{ $typeLabel($rule->hoarding_type) }}
                        </span>
                        <span class="text-xs text-gray-400 ml-2">— All states, all cities for this hoarding type</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black text-gray-900">{{ number_format($rule->commission_percent, 2) }}<span class="text-sm font-bold text-gray-500">%</span></span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ════════════════════════════════════════════════
             LEVEL 3 — State rules
        ════════════════════════════════════════════════ --}}
        <div x-data="{ openStateRules: true }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="w-7 h-7 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">3</span>
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">State-Level Rules</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Override global for a specific state</p>
                </div>
                @if($stateRules->isEmpty())
                    <span class="ml-auto text-xs text-gray-300 font-medium">Not configured</span>
                @else
                    <span class="ml-auto text-xs text-amber-700 font-semibold bg-amber-50 px-2.5 py-1 rounded-full">
                        {{ $stateRules->count() }} state{{ $stateRules->count() !== 1 ? 's' : '' }}
                    </span>
                @endif
                <button type="button" @click="openStateRules = !openStateRules" class="ml-2 p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                    <svg class="w-4 h-4 transition-transform" :class="openStateRules ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div x-show="openStateRules" x-transition>
                @if($stateRules->isEmpty())
                <div class="px-6 py-5 text-sm text-gray-400 italic">No state-level rules set.</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($stateRules as $stateName => $rules)
                    <div class="px-6 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800 text-sm">{{ $stateName }}</span>
                        </div>
                        <div class="space-y-2 pl-5">
                            @foreach($rules as $rule)
                            <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeBadge($rule->hoarding_type) }}">
                                        {{ $typeLabel($rule->hoarding_type) }}
                                    </span>
                                    <span class="text-xs text-gray-500">— All cities in {{ $stateName }}</span>
                                </div>
                                <span class="font-black text-gray-900">{{ number_format($rule->commission_percent, 2) }}<span class="text-xs font-semibold text-gray-500">%</span></span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════════════════
             LEVEL 4 — City rules
        ════════════════════════════════════════════════ --}}
        <div x-data="{ openCityRules: true }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="w-7 h-7 rounded-full bg-[#F97316] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">4</span>
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">City-Level Rules</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Override state & global for a specific city</p>
                </div>
                @if($cityRules->isEmpty())
                    <span class="ml-auto text-xs text-gray-300 font-medium">Not configured</span>
                @else
                    <span class="ml-auto text-xs text-orange-700 font-semibold bg-orange-50 px-2.5 py-1 rounded-full">
                        {{ $cityRules->count() }} {{ Str::plural('city', $cityRules->count()) }}
                    </span>
                @endif
                <button type="button" @click="openCityRules = !openCityRules" class="ml-2 p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                    <svg class="w-4 h-4 transition-transform" :class="openCityRules ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div x-show="openCityRules" x-transition>
                @if($cityRules->isEmpty())
                <div class="px-6 py-5 text-sm text-gray-400 italic">No city-level rules set.</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($cityRules as $stateCity => $rules)
                    @php
                        [$ruleState, $ruleCity] = explode('|||', $stateCity, 2);
                    @endphp
                    <div class="px-6 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-3.5 h-3.5 text-[#F97316]" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-semibold text-gray-800 text-sm">{{ $ruleCity }}</span>
                            <span class="text-xs text-gray-400">{{ $ruleState }}</span>
                        </div>
                        <div class="space-y-2 pl-5">
                            @foreach($rules as $rule)
                            <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeBadge($rule->hoarding_type) }}">
                                        {{ $typeLabel($rule->hoarding_type) }}
                                    </span>
                                    <span class="text-xs text-gray-500">— {{ $ruleCity }} only</span>
                                </div>
                                <span class="font-black text-gray-900">{{ number_format($rule->commission_percent, 2) }}<span class="text-xs font-semibold text-gray-500">%</span></span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════════════════
             LEVEL 5 — Hoarding-level overrides
        ════════════════════════════════════════════════ --}}
        <div x-data="{ openHoardingOverrides: true }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <span class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">5</span>
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">Hoarding-Level Overrides</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Set directly on individual hoardings — highest priority</p>
                </div>
                @if($hoardingOverrides->isEmpty())
                    <span class="ml-auto text-xs text-gray-300 font-medium">Not configured</span>
                @else
                    <span class="ml-auto text-xs text-red-700 font-semibold bg-red-50 px-2.5 py-1 rounded-full">
                        {{ $hoardingOverrides->count() }} hoarding{{ $hoardingOverrides->count() !== 1 ? 's' : '' }}
                    </span>
                @endif
                <button type="button" @click="openHoardingOverrides = !openHoardingOverrides" class="ml-2 p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition">
                    <svg class="w-4 h-4 transition-transform" :class="openHoardingOverrides ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div x-show="openHoardingOverrides" x-transition>
                @if($hoardingOverrides->isEmpty())
                <div class="px-6 py-5 text-sm text-gray-400 italic">No individual hoarding overrides set.</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($hoardingOverrides as $hoarding)
                    <div class="px-6 py-4 flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate">
                                {{ $hoarding->title ?? $hoarding->name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-xs font-bold uppercase {{ $hoarding->hoarding_type === 'ooh' ? 'text-blue-600' : 'text-purple-600' }}">
                                    {{ strtoupper($hoarding->hoarding_type) }}
                                </span>
                                @if($hoarding->city) <span>{{ $hoarding->city }}</span> @endif
                                @if($hoarding->state) <span class="text-gray-300">·</span><span>{{ $hoarding->state }}</span> @endif
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-xl font-black text-red-600">{{ number_format($hoarding->commission_percent, 2) }}<span class="text-sm font-bold text-red-400">%</span></span>
                            <p class="text-xs text-gray-400 mt-0.5">hoarding override</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- ════════════════════════════════════════════════
             EFFECTIVE RATES — resolved per hoarding
        ════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">Effective Rates per Hoarding</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Final resolved commission after applying the full rule hierarchy</p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.commission.vendor.rules', $vendor) }}" class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input type="text" name="effective_search" value="{{ request('effective_search') }}"
                        placeholder="Search title, city, state..."
                        class="md:col-span-2 w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#009A5C]/30 focus:border-[#009A5C]">

                    <select name="effective_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#009A5C]/30 focus:border-[#009A5C]">
                        <option value="">All Types</option>
                        <option value="ooh" {{ request('effective_type') === 'ooh' ? 'selected' : '' }}>OOH</option>
                        <option value="dooh" {{ request('effective_type') === 'dooh' ? 'selected' : '' }}>DOOH</option>
                    </select>

                    <select name="effective_state" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#009A5C]/30 focus:border-[#009A5C]">
                        <option value="">All States</option>
                        @foreach($effectiveStates as $state)
                            <option value="{{ $state }}" {{ request('effective_state') === $state ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>

                    <select name="effective_city" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#009A5C]/30 focus:border-[#009A5C]">
                        <option value="">All Cities</option>
                        @foreach($effectiveCities as $city)
                            <option value="{{ $city }}" {{ request('effective_city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-3 mt-3">
                    <select name="effective_per_page" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#009A5C]/30 focus:border-[#009A5C]">
                        <option value="10" {{ (int)request('effective_per_page', 10) === 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="25" {{ (int)request('effective_per_page') === 25 ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ (int)request('effective_per_page') === 50 ? 'selected' : '' }}>50 / page</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-[#009A5C] text-white rounded-lg text-sm font-semibold hover:bg-[#007a49] transition">Apply</button>
                    <a href="{{ route('admin.commission.vendor.rules', $vendor) }}" class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">Reset</a>
                </div>
            </form>

            @if($effectiveHoardings->isEmpty())
            <div class="px-6 py-5 text-sm text-gray-400 italic">No hoardings found for this vendor.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hoarding</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Effective Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Source</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($effectiveHoardings as $hoarding)
                        @php
                            $effective   = $resolvedCommissions[$hoarding->id] ?? null;
                            $hasOverride = $hoarding->commission_percent !== null && (float)$hoarding->commission_percent > 0;

                            // Determine source label
                            $source = '—';
                            if ($effective !== null) {
                                if ($hasOverride) {
                                    $source = 'Hoarding override';
                                    $sourceClass = 'text-red-600 bg-red-50';
                                } else {
                                    // Check what level matched (city -> state -> global)
                                    $state = $hoarding->state ?? '';
                                    $city = $hoarding->city ?? '';
                                    $hasState = $state !== '';
                                    $hasCity = $city !== '';

                                    $key1 = "{$hoarding->hoarding_type}|{$state}|{$city}";
                                    $key2 = "all|{$state}|{$city}";
                                    $key3 = "{$hoarding->hoarding_type}|{$state}|";
                                    $key4 = "all|{$state}|";
                                    $key5 = "{$hoarding->hoarding_type}||";
                                    $key6 = "all||";

                                    if ($hasCity && (isset($flatRuleMap[$key1]) || isset($flatRuleMap[$key2]))) {
                                        $source = 'City rule';
                                        $sourceClass = 'text-orange-600 bg-orange-50';
                                    } elseif ($hasState && (isset($flatRuleMap[$key3]) || isset($flatRuleMap[$key4]))) {
                                        $source = 'State rule';
                                        $sourceClass = 'text-amber-600 bg-amber-50';
                                    } elseif (isset($flatRuleMap[$key5])) {
                                        $source = 'Hoarding type rule';
                                        $sourceClass = 'text-indigo-700 bg-indigo-50';
                                    } elseif (isset($flatRuleMap[$key6])) {
                                        $source = 'Global rule';
                                        $sourceClass = 'text-[#009A5C] bg-[#E8F7F0]';
                                    } else {
                                        $source = 'Resolved rule';
                                        $sourceClass = 'text-gray-600 bg-gray-100';
                                    }
                                }
                            } else {
                                $sourceClass = 'text-gray-400 bg-gray-100';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-3 font-medium text-gray-900 max-w-xs truncate">
                                {{ $hoarding->title ?? $hoarding->name }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase {{ $hoarding->hoarding_type === 'ooh' ? 'text-blue-600 bg-blue-50' : 'text-purple-600 bg-purple-50' }}">
                                    {{ strtoupper($hoarding->hoarding_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-500 text-xs">
                                {{ collect([$hoarding->city, $hoarding->state])->filter()->implode(', ') ?: '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @if($effective !== null)
                                    <span class="font-bold text-gray-900">{{ number_format($effective, 2) }}%</span>
                                @else
                                    <span class="text-gray-300 font-bold">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $sourceClass ?? 'text-gray-400 bg-gray-100' }}">
                                    {{ $source }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-xs sm:text-sm text-gray-500">
                    Showing {{ $effectiveHoardings->firstItem() }} to {{ $effectiveHoardings->lastItem() }} of {{ $effectiveHoardings->total() }} hoardings
                </p>
                {{ $effectiveHoardings->links() }}
            </div>
            @endif
        </div>

    </div>{{-- end space-y-5 --}}
    @endif

</div>
@endsection