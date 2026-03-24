{{-- resources/views/admin/settings/razorpay.blade.php --}}
@extends('layouts.admin')

@section('title', 'Razorpay Settings')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home',     'route' => route('admin.dashboard')],
    ['label' => 'Settings', 'route' => route('admin.settings.razorpay')],
    ['label' => 'Razorpay Settings']
]" />
@endsection

@section('content')
<div class="bg-[#F7F7F7] w-full min-h-screen">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Razorpay Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure your Razorpay payment gateway credentials</p>
        </div>

        {{-- Live/Test Badge --}}
        @if($settings)
            <span class="px-4 py-1.5 rounded-full text-sm font-semibold
                {{ $settings->mode === 'live' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ $settings->mode === 'live' ? '🟢 Live Mode' : '🟡 Test Mode' }}
            </span>
        @endif
    </div>

    {{-- Success / Error Messages --}}
    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT — Main Form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl p-6 shadow-sm">

                <h2 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#00995c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    API Credentials
                </h2>

                <form method="POST" action="{{ route('admin.settings.razorpay.update') }}">
                    @csrf

                    {{-- Mode --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Mode <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="mode" value="test"
                                    {{ ($settings?->mode ?? 'test') === 'test' ? 'checked' : '' }}
                                    class="text-[#00995c] focus:ring-[#00995c]">
                                <span class="text-sm font-medium text-gray-700">Test Mode</span>
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Sandbox</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="mode" value="live"
                                    {{ ($settings?->mode) === 'live' ? 'checked' : '' }}
                                    class="text-[#00995c] focus:ring-[#00995c]">
                                <span class="text-sm font-medium text-gray-700">Live Mode</span>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Production</span>
                            </label>
                        </div>
                    </div>

                    {{-- Key ID --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Key ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="key_id"
                            value="{{ old('key_id', $settings?->key_id) }}"
                            placeholder="rzp_test_xxxxxxxxxx or rzp_live_xxxxxxxxxx"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#00995c] focus:ring-0 outline-none
                                @error('key_id') border-red-400 bg-red-50 @enderror">
                        @error('key_id')
                            <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">
                            Find this in your Razorpay Dashboard → Settings → API Keys
                        </p>
                    </div>

                    {{-- Key Secret --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Key Secret <span class="text-red-500">*</span>
                            @if($settings && !empty($settings->attributes['key_secret']))
                                <span class="text-green-600 text-xs font-normal ml-2 inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Secret saved & encrypted
                                </span>
                            @endif
                        </label>
                        <div class="relative">
                            <input type="password" name="key_secret" id="key_secret"
                                placeholder="{{ ($settings && !empty($settings->attributes['key_secret'])) ? 'Leave blank to keep existing secret' : 'Enter your Key Secret' }}"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#00995c] outline-none pr-12">
                            <button type="button" onclick="toggleSecretVisibility()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg id="eye-show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eye-hide" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Stored encrypted in database. Leave blank to keep existing.</p>
                    </div>

                    {{-- Webhook Secret --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Webhook Secret
                            @if($settings && !empty($settings->attributes['webhook_secret']))
                                <span class="text-green-600 text-xs font-normal ml-2 inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Saved
                                </span>
                            @endif
                        </label>
                        <input type="password" name="webhook_secret"
                            placeholder="{{ ($settings && !empty($settings->attributes['webhook_secret'])) ? 'Leave blank to keep existing' : 'Optional — for webhook verification' }}"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#00995c] outline-none">
                        <p class="text-xs text-gray-400 mt-1">
                            Set in Razorpay Dashboard → Settings → Webhooks
                        </p>
                    </div>

                    {{-- Currency + Business Name --}}
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Currency <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="currency"
                                value="{{ old('currency', $settings?->currency ?? 'INR') }}"
                                maxlength="3"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#00995c] outline-none uppercase">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Business Name</label>
                            <input type="text" name="business_name"
                                value="{{ old('business_name', $settings?->business_name) }}"
                                placeholder="Shown on checkout popup"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#00995c] outline-none">
                        </div>
                    </div>

                    {{-- Theme Color --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Theme Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="theme_color"
                                value="{{ old('theme_color', $settings?->theme_color ?? '#009A5C') }}"
                                class="h-10 w-16 rounded-lg border border-gray-200 cursor-pointer p-1">
                            <span class="text-sm text-gray-500">Accent color shown on Razorpay checkout popup</span>
                        </div>
                    </div>

                    {{-- Enable Toggle --}}
                    <div class="mb-6 flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">Enable Razorpay Payments</p>
                            <p class="text-xs text-gray-400 mt-0.5">Allow customers to pay via Razorpay</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ ($settings?->is_active) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full
                                after:h-5 after:w-5 after:transition-all peer-checked:bg-[#00995c]">
                            </div>
                        </label>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-3">
                        <button type="button" id="test-btn"
                            class="flex items-center gap-2 px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Test Credentials
                        </button>

                        <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 bg-[#00995c] text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Settings
                        </button>
                    </div>

                    {{-- Test Result --}}
                    <div id="test-result" class="hidden mt-4 p-3 rounded-xl text-sm"></div>

                </form>
            </div>
        </div>

        {{-- RIGHT — Status Card --}}
        <div class="space-y-4">

            {{-- Status Card --}}
            <div class="bg-white rounded-xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Gateway Status</h3>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $settings?->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $settings?->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Mode</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ ($settings?->mode ?? 'test') === 'live' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($settings?->mode ?? 'Not Set') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Credentials</span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $settings?->isConfigured() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $settings?->isConfigured() ? '✓ Configured' : 'Not Set' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Currency</span>
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $settings?->currency ?? 'INR' }}
                        </span>
                    </div>
                    @if($settings?->updated_at)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Last Updated</span>
                            <span class="text-xs text-gray-400">
                                {{ $settings->updated_at->diffForHumans() }}
                            </span>
                        </div>
                    @endif
                </div>

                @if($settings)
                    <button type="button" id="toggle-btn"
                        class="mt-4 w-full py-2 rounded-xl text-sm font-semibold transition
                        {{ $settings->is_active
                            ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100'
                            : 'bg-green-50 text-green-600 border border-green-200 hover:bg-green-100' }}">
                        {{ $settings->is_active ? 'Deactivate Gateway' : 'Activate Gateway' }}
                    </button>
                @endif
            </div>

            {{-- Help Card --}}
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                <h3 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    How to get API Keys
                </h3>
                <ol class="text-xs text-blue-700 space-y-2 list-decimal list-inside">
                    <li>Login to <a href="https://dashboard.razorpay.com" target="_blank" class="underline font-medium">Razorpay Dashboard</a></li>
                    <li>Go to Settings → API Keys</li>
                    <li>Generate Test or Live key pair</li>
                    <li>Copy Key ID and Key Secret here</li>
                </ol>
                <div class="mt-3 p-2.5 bg-blue-100 rounded-lg">
                    <p class="text-xs text-blue-700 font-medium">⚠️ Never share your Key Secret</p>
                    <p class="text-xs text-blue-600 mt-0.5">It is stored encrypted in your database</p>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
// ✅ Show/hide secret
function toggleSecretVisibility() {
    const input  = document.getElementById('key_secret');
    const eyeShow = document.getElementById('eye-show');
    const eyeHide = document.getElementById('eye-hide');

    if (input.type === 'password') {
        input.type = 'text';
        eyeShow.classList.add('hidden');
        eyeHide.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeShow.classList.remove('hidden');
        eyeHide.classList.add('hidden');
    }
}

// ✅ Test credentials
document.getElementById('test-btn').addEventListener('click', async function () {
    const keyId     = document.querySelector('[name="key_id"]').value.trim();
    const keySecret = document.getElementById('key_secret').value.trim();
    const resultDiv = document.getElementById('test-result');

    if (!keyId) {
        showTestResult('Please enter Key ID first.', false);
        return;
    }
    if (!keySecret) {
        showTestResult('Please enter Key Secret to test credentials.', false);
        return;
    }

    this.disabled    = true;
    this.innerHTML   = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Testing...';

    try {
        const response = await fetch('{{ route("admin.settings.razorpay.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ key_id: keyId, key_secret: keySecret })
        });

        const result = await response.json();
        showTestResult(result.message, result.success);

    } catch (e) {
        showTestResult('Network error. Please try again.', false);
    } finally {
        this.disabled  = false;
        this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Test Credentials';
    }
});

function showTestResult(message, success) {
    const div = document.getElementById('test-result');
    div.className = `mt-4 p-3 rounded-xl text-sm flex items-center gap-2 ${
        success
            ? 'bg-green-50 border border-green-200 text-green-700'
            : 'bg-red-50 border border-red-200 text-red-700'
    }`;
    div.innerHTML = `<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="${success ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"/>
    </svg>${message}`;
    div.classList.remove('hidden');
}

// ✅ Toggle active via AJAX
@if($settings)
document.getElementById('toggle-btn')?.addEventListener('click', async function () {
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Updating...';

    try {
        const response = await fetch('{{ route("admin.settings.razorpay.toggle") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const result = await response.json();

        if (result.success) {
            // ✅ Reload to reflect all status changes
            window.location.reload();
        } else {
            alert(result.message);
            btn.disabled = false;
            btn.textContent = '{{ $settings->is_active ? "Deactivate Gateway" : "Activate Gateway" }}';
        }

    } catch (e) {
        alert('Network error. Please try again.');
        btn.disabled = false;
    }
});
@endif
</script>
@endsection