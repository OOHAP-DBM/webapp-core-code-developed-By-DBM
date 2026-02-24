{{-- resources/views/vendor/commission/index.blade.php --}}
@extends('layouts.vendor')

@section('title', 'My Commission')
@section('page_title', 'My Commission')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('vendor.dashboard')],
    ['label' => ' Commission']
]" />
@endsection

@section('content')
<div class="p-6">

    {{-- ── Page header ── --}}
    <div class="mb-8">
        <!-- <h1 class="text-2xl font-bold text-gray-900">My Commission</h1> -->
        <p class="text-sm text-gray-500 mt-1">Your current commission structure and effective rates per hoarding</p>
    </div>

    {{-- ── Agreement banner (shown when commission just set and pending agreement) ── --}}
    @if($pendingAgreement)
    @php $notifData = $pendingAgreement->data; @endphp
    <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl p-5" id="agreement-banner">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-amber-800 text-sm">Commission Agreement Required</h3>
                <p class="text-amber-700 text-sm mt-1">
                    {{ $notifData['message'] ?? 'A new commission rate has been set for your account. Please review and agree to proceed.' }}
                </p>
                <p class="text-amber-600 text-xs mt-2">
                    Notified {{ $pendingAgreement->created_at->diffForHumans() }}
                </p>
                <div class="flex gap-3 mt-4">
                    <button
                        onclick="openAgreementConfirmModal('{{ $pendingAgreement->id }}')"
                        id="agree-btn"
                        class="px-5 py-2 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 transition">
                        I Agree to This Commission
                    </button>
                    <button
                       type="button"
                       onclick="openSupportModal()"
                       class="px-5 py-2 border border-amber-300 text-amber-700 rounded-xl text-sm font-semibold hover:bg-amber-100 transition">
                        Contact Support
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="agreement-confirm-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeAgreementConfirmModal()"></div>
        <div class="relative mx-auto mt-24 w-full max-w-lg px-4">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-5">
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-bold text-gray-900">Confirm Commission Agreement</h3>
                    <button type="button" onclick="closeAgreementConfirmModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <p>You are about to accept the currently applicable commission terms.</p>
                    <p>By continuing, this action will be recorded with timestamp and IP address for audit purposes.</p>
                    <label class="flex items-start gap-2 mt-2">
                        <input id="agreement-confirm-checkbox" type="checkbox" class="mt-0.5 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-gray-700">I confirm that I have reviewed and agree to the commission terms.</span>
                    </label>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" onclick="closeAgreementConfirmModal()" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" id="confirm-agree-btn" onclick="submitCommissionAgreement()" class="px-4 py-2 text-sm font-semibold rounded-lg bg-amber-600 text-white hover:bg-amber-700 disabled:opacity-50" disabled>
                        Confirm & Agree
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="support-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" onclick="closeSupportModal()"></div>
        <div class="relative mx-auto mt-24 w-full max-w-md px-4">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-5">
                <div class="flex items-start justify-between">
                    <h3 class="text-base font-bold text-gray-900">Please Contact Support</h3>
                    <button type="button" onclick="closeSupportModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>

                <div class="mt-4 space-y-3 text-sm">
                    @if(!empty($supportContact['email']))
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                            <span class="text-gray-500">Email</span>
                            <a href="mailto:{{ $supportContact['email'] }}" class="font-semibold text-[#009A5C] hover:underline">{{ $supportContact['email'] }}</a>
                        </div>
                    @endif

                    @if(!empty($supportContact['phone']))
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                            <span class="text-gray-500">Mobile</span>
                            <a href="tel:{{ $supportContact['phone'] }}" class="font-semibold text-[#009A5C] hover:underline">{{ $supportContact['phone'] }}</a>
                        </div>
                    @endif

                    @if(empty($supportContact['email']) && empty($supportContact['phone']))
                        <p class="text-gray-500">Support contact is not configured yet. Please try again later.</p>
                    @endif
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="button" onclick="closeSupportModal()" class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!$hasAnyRules)
    {{-- ── Empty state ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No commission rules yet</h3>
        <p class="text-sm text-gray-500 max-w-sm mx-auto">
            Your commission rates haven't been configured yet. Please contact the administrator.
        </p>
    </div>

    @else

    {{-- ── Priority legend ── --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm px-5 py-4 mb-6 flex flex-wrap items-center gap-4 text-xs text-gray-500">
        <span class="font-semibold text-gray-700 whitespace-nowrap">How rules work:</span>
        <span class="text-gray-400">A more specific rule always overrides a broader one.</span>
        <div class="flex flex-wrap gap-3 ml-auto">
            @foreach([['bg-[#009A5C]','Global'],['bg-amber-500','State'],['bg-[#F97316]','City'],['bg-red-500','Hoarding']] as [$color,$label])
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full {{ $color }}"></span>
                <span>{{ $label }}</span>
            </div>
            @endforeach
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

        {{-- ── LEVEL 1: Global rules ── --}}
        @if($globalRules->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-gradient-to-r from-[#E8F7F0] to-white border-b border-gray-100 flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-[#009A5C] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">1</span>
                <div>
                    <span class="font-bold text-gray-900 text-sm">Base Rates</span>
                    <span class="text-xs text-gray-400 ml-2">— applies across all your hoardings</span>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($globalRules as $rule)
                <div class="px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $typeBadge($rule->hoarding_type) }}">
                            {{ $typeLabel($rule->hoarding_type) }}
                        </span>
                        <span class="text-sm text-gray-500">All states and cities</span>
                    </div>
                    <span class="text-2xl font-black text-gray-900">{{ number_format($rule->commission_percent, 2) }}<span class="text-sm font-bold text-gray-500">%</span></span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── LEVEL 2: State rules ── --}}
        @if($stateRules->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-gradient-to-r from-amber-50 to-white border-b border-gray-100 flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">2</span>
                <div>
                    <span class="font-bold text-gray-900 text-sm">State-Specific Rates</span>
                    <span class="text-xs text-gray-400 ml-2">— override base rates for these states</span>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($stateRules as $stateName => $rules)
                <div class="px-5 py-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        {{ $stateName }}
                    </p>
                    <div class="space-y-2 pl-4">
                        @foreach($rules as $rule)
                        <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-2.5">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeBadge($rule->hoarding_type) }}">
                                {{ $typeLabel($rule->hoarding_type) }}
                            </span>
                            <span class="font-black text-gray-900 text-lg">{{ number_format($rule->commission_percent, 2) }}<span class="text-xs font-semibold text-gray-500">%</span></span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── LEVEL 3: City rules ── --}}
        @if($cityRules->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-gradient-to-r from-orange-50 to-white border-b border-gray-100 flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-[#F97316] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">3</span>
                <div>
                    <span class="font-bold text-gray-900 text-sm">City-Specific Rates</span>
                    <span class="text-xs text-gray-400 ml-2">— override state rates for these cities</span>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($cityRules as $stateCity => $rules)
                @php [$ruleState, $ruleCity] = explode('|||', $stateCity, 2); @endphp
                <div class="px-5 py-4">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                        <svg class="w-3 h-3 text-[#F97316]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        {{ $ruleCity }}
                        <span class="font-normal text-gray-400 normal-case">{{ $ruleState }}</span>
                    </p>
                    <div class="space-y-2 pl-4">
                        @foreach($rules as $rule)
                        <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-2.5">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $typeBadge($rule->hoarding_type) }}">
                                {{ $typeLabel($rule->hoarding_type) }}
                            </span>
                            <span class="font-black text-gray-900 text-lg">{{ number_format($rule->commission_percent, 2) }}<span class="text-xs font-semibold text-gray-500">%</span></span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── LEVEL 4: Hoarding overrides ── --}}
        @if($hoardingOverrides->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-gradient-to-r from-red-50 to-white border-b border-gray-100 flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">4</span>
                <div>
                    <span class="font-bold text-gray-900 text-sm">Individual Hoarding Rates</span>
                    <span class="text-xs text-gray-400 ml-2">— set specifically for these hoardings</span>
                </div>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($hoardingOverrides as $hoarding)
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 text-sm truncate">{{ $hoarding->title ?? $hoarding->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span class="font-bold uppercase {{ $hoarding->hoarding_type === 'ooh' ? 'text-blue-500' : 'text-purple-500' }}">
                                {{ strtoupper($hoarding->hoarding_type) }}
                            </span>
                            @if($hoarding->city) · {{ $hoarding->city }} @endif
                            @if($hoarding->state) · {{ $hoarding->state }} @endif
                        </p>
                    </div>
                    <span class="font-black text-red-600 text-xl">{{ number_format($hoarding->commission_percent, 2) }}<span class="text-xs font-semibold text-red-400">%</span></span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Effective rates table ── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-7 h-7 rounded-full bg-gray-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-gray-900 text-sm">Your Effective Rates</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Actual commission applied to each of your hoardings</p>
                </div>
            </div>

            <form method="GET" action="{{ route('vendor.commission.index') }}" class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <input
                        type="text"
                        name="effective_search"
                        value="{{ request('effective_search') }}"
                        placeholder="Search hoarding / city / state"
                        class="md:col-span-2 px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-[#009A5C]"
                    >

                    <select name="effective_type" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-[#009A5C] bg-white">
                        <option value="">All Types</option>
                        <option value="ooh" {{ request('effective_type') === 'ooh' ? 'selected' : '' }}>OOH</option>
                        <option value="dooh" {{ request('effective_type') === 'dooh' ? 'selected' : '' }}>DOOH</option>
                    </select>

                    <select name="effective_state" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-[#009A5C] bg-white">
                        <option value="">All States</option>
                        @foreach(($effectiveStates ?? collect()) as $state)
                            <option value="{{ $state }}" {{ request('effective_state') === $state ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>

                    <select name="effective_city" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-[#009A5C] bg-white">
                        <option value="">All Cities</option>
                        @foreach(($effectiveCities ?? collect()) as $city)
                            <option value="{{ $city }}" {{ request('effective_city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500">Per page</label>
                        <select name="effective_per_page" class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs bg-white">
                            @foreach([10,25,50] as $size)
                                <option value="{{ $size }}" {{ (int) request('effective_per_page', 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('vendor.commission.index') }}" class="px-3 py-2 text-xs font-semibold rounded-lg border border-gray-200 text-gray-600 hover:bg-white">Reset</a>
                        <button type="submit" class="px-3 py-2 text-xs font-semibold rounded-lg bg-[#009A5C] text-white hover:bg-[#007a49]">Apply</button>
                    </div>
                </div>
            </form>

            @if($allHoardings->isEmpty())
            <div class="px-5 py-5 text-sm text-gray-400 italic">No hoardings found.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Hoarding</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Location</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Your Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($allHoardings as $hoarding)
                        @php
                            $effective   = $resolvedCommissions[$hoarding->id] ?? null;
                            $hasOverride = $hoarding->commission_percent !== null && (float)$hoarding->commission_percent > 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 font-medium text-gray-900 max-w-[220px] truncate">
                                {{ $hoarding->title ?? $hoarding->name }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase
                                    {{ $hoarding->hoarding_type === 'ooh' ? 'text-blue-600 bg-blue-50' : 'text-purple-600 bg-purple-50' }}">
                                    {{ strtoupper($hoarding->hoarding_type) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">
                                {{ collect([$hoarding->city, $hoarding->state])->filter()->implode(', ') ?: '—' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($effective !== null)
                                    <div class="inline-flex flex-col items-end">
                                        <span class="font-black text-[#009A5C] text-base">{{ number_format($effective, 2) }}%</span>
                                        @if($hasOverride)
                                            <span class="text-xs text-red-400 font-medium">custom rate</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-300 font-bold">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    Showing {{ $allHoardings->firstItem() }} to {{ $allHoardings->lastItem() }} of {{ $allHoardings->total() }} hoardings
                </span>
                {{ $allHoardings->links() }}
            </div>
            @endif
        </div>

    </div>{{-- end space-y-5 --}}
    @endif

</div>
@endsection

@push('scripts')
<script>
const agreeCommissionUrlTemplate = @json(route('vendor.commission.agree', ['notification' => '__NOTIFICATION__']));
let pendingAgreementNotificationId = null;

function openAgreementConfirmModal(notificationId) {
    pendingAgreementNotificationId = notificationId;
    const modal = document.getElementById('agreement-confirm-modal');
    const checkbox = document.getElementById('agreement-confirm-checkbox');
    const confirmButton = document.getElementById('confirm-agree-btn');

    if (checkbox) {
        checkbox.checked = false;
    }
    if (confirmButton) {
        confirmButton.disabled = true;
    }
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeAgreementConfirmModal() {
    const modal = document.getElementById('agreement-confirm-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function submitCommissionAgreement() {
    if (!pendingAgreementNotificationId) {
        return;
    }
    closeAgreementConfirmModal();
    agreeToCommission(pendingAgreementNotificationId);
}

document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('agreement-confirm-checkbox');
    const confirmButton = document.getElementById('confirm-agree-btn');
    if (checkbox && confirmButton) {
        checkbox.addEventListener('change', function () {
            confirmButton.disabled = !checkbox.checked;
        });
    }
});

function openSupportModal() {
    const modal = document.getElementById('support-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeSupportModal() {
    const modal = document.getElementById('support-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

async function agreeToCommission(notificationId) {
    const btn = document.getElementById('agree-btn');
    btn.disabled    = true;
    btn.textContent = 'Saving...';

    try {
        const agreeUrl = agreeCommissionUrlTemplate.replace('__NOTIFICATION__', encodeURIComponent(notificationId));
        const res = await fetch(agreeUrl, {
            method:  'POST',
            headers: {
                'Content-Type':  'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        if (!res.ok) {
            throw new Error('Request failed');
        }

        const data = await res.json();

        if (data.success) {
            const banner = document.getElementById('agreement-banner');
            banner.style.transition = 'opacity 0.4s';
            banner.style.opacity    = '0';
            setTimeout(() => banner.remove(), 400);
            return;
        }

        throw new Error('Agreement not saved');
    } catch (e) {
        btn.disabled    = false;
        btn.textContent = 'I Agree to This Commission';
        alert('Something went wrong. Please try again.');
    }
}
</script>
@endpush