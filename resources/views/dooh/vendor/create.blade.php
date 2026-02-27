@extends('layouts.vendor')

@section('title', 'Add DOOH Hoarding')

@section('content')
<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">

    <div class="w-full max-w-full sm:max-w-3xl md:max-w-4xl lg:max-w-5xl mx-auto py-4 sm:py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-lg md:rounded-full overflow-hidden min-h-[56px] md:h-16 shadow-sm">

            <div class="relative flex-1 flex items-center justify-center gap-2 md:gap-3 pr-4 md:pr-6 
                {{ $step >= 1 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 92% 0%, 100% 50%, 92% 100%, 0% 100%);">
                <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full 
                    {{ $step >= 1 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="hidden sm:inline font-semibold text-xs md:text-base {{ $step >= 1 ? 'text-[#009A5C]' : 'text-gray-400' }}">Step 1</span>
            </div>

            <div class="relative flex-1 flex items-center justify-center gap-2 md:gap-3 px-4 md:px-6
                {{ $step >= 2 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 92% 0%, 100% 50%, 92% 100%, 0% 100%, 8% 50%);">
                <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full 
                    {{ $step >= 2 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="hidden sm:inline font-semibold text-xs md:text-base {{ $step >= 2 ? 'text-[#009A5C]' : 'text-gray-400' }}">Step 2</span>
            </div>

            <div class="relative flex-1 flex items-center justify-center gap-2 md:gap-3 pl-4 md:pl-6
                {{ $step >= 3 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 8% 50%);">
                <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full 
                    {{ $step >= 3 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="hidden sm:inline font-semibold text-xs md:text-base {{ $step >= 3 ? 'text-[#009A5C]' : 'text-gray-400' }}">Step 3</span>
            </div>

        </div>
    </div>

    <form id="hoarding-form" action="{{ route('vendor.dooh.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 px-1 sm:px-0">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">
        <input type="hidden" name="go_back" id="go_back" value="0">
         @if($draft)
            <input type="hidden" name="screen_id" value="{{ $draft->id }}">
        @endif
        <!-- @if($step > 1 && $draft)
            <input type="hidden" name="screen_id" value="{{ $draft->id }}">
        @endif -->

        @if($step == 1)
            @include('dooh.vendor.partials.step1', ['draft' => $draft])
        @elseif($step == 2)
            @include('dooh.vendor.partials.step2', ['draft' => $draft])
        @elseif($step == 3)
            @include('dooh.vendor.partials.step3', ['draft' => $draft])
        @endif

        <div class="flex flex-col sm:flex-row justify-between gap-2 sm:gap-3 mb-8 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Left: Cancel on step 1, Previous on steps 2 & 3 --}}
            @if($step == 1)
                <a href="{{ route('vendor.hoardings.myHoardings') }}"
                    class="w-full sm:w-auto px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition text-center">
                    Cancel
                </a>
            @else
                <button type="button" id="prevBtn"
                    class="w-full sm:w-auto px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition text-center flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                </button>
            @endif

            {{-- Right: Next / Publish --}}
            <button type="submit" id="submitBtn"
                class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition flex items-center justify-center gap-2">
                <span id="submitBtnText">@if($step < 3) Next @else Publish @endif</span>
                <svg id="submitSpinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
            </button>
        </div>
    </form>
</div>

{{-- ── Full-page Loading Overlay ── --}}
<div id="loadingOverlay"
    class="fixed inset-0 z-[999] bg-white/70 backdrop-blur-sm hidden items-center justify-center">
    <div class="flex flex-col items-center gap-4 bg-white rounded-2xl shadow-2xl px-10 py-8 border border-gray-100">
        <svg class="animate-spin w-12 h-12 text-[#009A5C]" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <p class="text-gray-800 font-bold text-base" id="overlayMessage">Saving your details...</p>
        <p class="text-gray-400 text-xs">Please wait, do not close this page.</p>
    </div>
</div>

<script>
(function () {
    const form        = document.getElementById('hoarding-form');
    const submitBtn   = document.getElementById('submitBtn');
    const prevBtn     = document.getElementById('prevBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    const btnText     = document.getElementById('submitBtnText');
    const overlay     = document.getElementById('loadingOverlay');
    const overlayMsg  = document.getElementById('overlayMessage');
    const goBackInput = document.getElementById('go_back');
    const currentStep = {{ $step }};

    function showOverlay(message) {
        overlayMsg.textContent = message;
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }

    function showBtnLoading(text) {
        submitBtn.disabled = true;
        btnText.textContent = text;
        submitSpinner.classList.remove('hidden');
    }

    // ── Forward submit (Next / Publish) ──
    form.addEventListener('submit', function (e) {
        if (goBackInput.value === '1') return;

        // ✅ Don't show overlay if media validation cancelled the submit
        if (e.defaultPrevented) return;

        if (currentStep < 3) {
            showOverlay('Saving Step ' + currentStep + '...');
            showBtnLoading('Saving...');
        } else {
            showOverlay('Publishing your hoarding...');
            showBtnLoading('Publishing...');
        }
    });

    // ── Previous button ──
    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            prevBtn.disabled = true;
            prevBtn.innerHTML = `
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Going back...
            `;
            showOverlay('Going back...');
            goBackInput.value = '1';
            form.submit();
        });
    }

    // ── bfcache guard: reset UI when user hits browser back ──
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            submitBtn.disabled = false;
            submitSpinner.classList.add('hidden');
            btnText.textContent = currentStep < 3 ? 'Next' : 'Publish';
            if (prevBtn) {
                prevBtn.disabled = false;
                prevBtn.innerHTML = `
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Previous
                `;
            }
        }
    });
})();
</script>

@endsection