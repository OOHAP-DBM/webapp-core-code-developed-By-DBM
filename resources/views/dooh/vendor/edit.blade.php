@extends('layouts.vendor')

@section('title', 'Edit DOOH Screen')

@section('content')
<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">

    <div class="w-full max-w-full sm:max-w-3xl md:max-w-4xl lg:max-w-5xl mx-auto py-4 sm:py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-lg md:rounded-full overflow-hidden min-h-[56px] md:h-16 shadow-sm">
            @php
                $steps = [1 => 'Details', 2 => 'Settings', 3 => 'Additional'];
            @endphp
            @foreach($steps as $num => $label)
            <div class="flex-1 flex items-center justify-center gap-2 px-3 sm:px-4 md:px-6 py-2 transition-all
                {{ $step == $num ? 'bg-[#009A5C] text-white' : ($step > $num ? 'bg-green-50 text-[#009A5C]' : 'bg-white text-gray-500') }}">
                <div class="flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full text-xs sm:text-sm font-bold
                    {{ $step == $num ? 'bg-white text-[#009A5C]' : ($step > $num ? 'bg-[#009A5C] text-white' : 'bg-gray-200 text-gray-600') }}">
                    @if($step > $num)
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span class="hidden sm:block text-xs md:text-sm font-semibold whitespace-nowrap">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <form id="edit-form" action="{{ route('vendor.dooh.update', $screen->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 px-1 sm:px-0">
        @csrf
        @method('PUT')
        <input type="hidden" name="step" value="{{ $step }}">

        @if($step == 1)
            @include('dooh.vendor.partials.step1', ['draft' => $screen, 'attributes' => $attributes, 'media' => $screen->media])
        @elseif($step == 2)
            @include('dooh.vendor.partials.step2', ['draft' => $screen, 'parentHoarding' => $screen->hoarding])
        @elseif($step == 3)
            @include('dooh.vendor.partials.step3', ['draft' => $screen, 'parentHoarding' => $screen->hoarding, 'slots' => $screen->slots])
        @endif

        <div class="flex flex-col sm:flex-row justify-between gap-2 sm:gap-3 mb-8 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Left: Cancel always visible, Previous on steps 2 & 3 --}}
            <div class="flex gap-2">
                @if($step == 1)
                <a href="{{ route('vendor.hoardings.myHoardings') }}"
                    class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition text-center">
                    Cancel
                </a>

                 @else
                    <a href="{{ route('vendor.dooh.edit', ['id' => $screen->id, 'step' => $step - 1]) }}"
                        id="prevLink"
                        class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Previous
                    </a>
                @endif
            </div>

            {{-- Right: Save & Continue / Update --}}
            @if($step < 3)
                <button type="submit" name="save_and_next" value="1" id="submitBtn"
                    class="w-full sm:w-auto bg-[#009A5C] hover:bg-[#008A52] text-white px-6 py-2.5 rounded-xl font-bold transition flex items-center justify-center gap-2">
                    <span id="submitBtnText">Save & Continue →</span>
                    <svg id="submitSpinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </button>
            @else
                <button type="submit" id="submitBtn"
                    class="w-full sm:w-auto bg-[#009A5C] hover:bg-[#008A52] text-white px-6 py-2.5 rounded-xl font-bold transition flex items-center justify-center gap-2">
                    <span id="submitBtnText">Update DOOH Screen</span>
                    <svg id="submitSpinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </button>
            @endif
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
        <p class="text-gray-800 font-bold text-base" id="overlayMessage">Updating details...</p>
        <p class="text-gray-400 text-xs">Please wait, do not close this page.</p>
    </div>
</div>

<script>
(function () {
    const form          = document.getElementById('edit-form');
    const submitBtn     = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    const btnText       = document.getElementById('submitBtnText');
    const overlay       = document.getElementById('loadingOverlay');
    const overlayMsg    = document.getElementById('overlayMessage');
    const prevLink      = document.getElementById('prevLink');
    const currentStep   = {{ $step }};

    function showOverlay(message) {
        overlayMsg.textContent = message;
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }

    // ── Forward submit ──
    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitSpinner.classList.remove('hidden');

        if (currentStep < 3) {
            btnText.textContent = 'Saving...';
            showOverlay('Saving Step ' + currentStep + '...');
        } else {
            btnText.textContent = 'Updating...';
            showOverlay('Updating your DOOH screen...');
        }
    });

    // ── Previous link: show overlay while navigating ──
    if (prevLink) {
        prevLink.addEventListener('click', function () {
            showOverlay('Going back...');
            // It's an <a> tag so navigation happens automatically
        });
    }

    // ── bfcache guard ──
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            submitBtn.disabled = false;
            submitSpinner.classList.add('hidden');
            btnText.textContent = currentStep < 3 ? 'Save & Continue →' : 'Update DOOH Screen';
        }
    });
})();
</script>

@endsection