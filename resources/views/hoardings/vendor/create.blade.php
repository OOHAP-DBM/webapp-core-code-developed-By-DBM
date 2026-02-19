@extends('layouts.vendor')

@section('title', 'Add OOH Hoarding')

@section('content')

{{-- Loading Overlay --}}
<div id="loadingOverlay" class="fixed inset-0 z-[999] hidden items-center justify-center"
    style="background: rgba(255,255,255,0.7); backdrop-filter: blur(4px);">
    <div class="flex flex-col items-center gap-4">
        <svg class="animate-spin h-12 w-12 text-[#009A5C]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <p id="loadingText" class="text-[#009A5C] font-bold text-lg">Saving...</p>
    </div>
</div>

<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">

    <div class="w-full max-w-5xl mx-auto py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-full overflow-hidden h-16 shadow-sm">

            <div class="relative flex-1 flex items-center justify-center gap-3 pr-6 
                {{ $step >= 1 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 95% 0%, 100% 50%, 95% 100%, 0% 100%);">
                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                    {{ $step >= 1 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    @if($step > 1)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <span class="font-bold">1</span>
                    @endif
                </div>
                <span class="font-semibold {{ $step >= 1 ? 'text-[#009A5C]' : 'text-gray-400' }}">Basic Info</span>
            </div>

            <div class="relative flex-1 flex items-center justify-center gap-3 px-6 border-l border-gray-300
                {{ $step >= 2 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 95% 0%, 100% 50%, 95% 100%, 0% 100%, 5% 50%);">
                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                    {{ $step >= 2 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    @if($step > 2)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <span class="font-bold">2</span>
                    @endif
                </div>
                <span class="font-semibold {{ $step >= 2 ? 'text-[#009A5C]' : 'text-gray-400' }}">Settings</span>
            </div>

            <div class="relative flex-1 flex items-center justify-center gap-3 pl-6 border-l border-gray-300
                {{ $step >= 3 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 5% 50%);">
                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                    {{ $step >= 3 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    <span class="font-bold">3</span>
                </div>
                <span class="font-semibold {{ $step >= 3 ? 'text-[#009A5C]' : 'text-gray-400' }}">Pricing</span>
            </div>

        </div>
    </div>

    <form action="{{ route('vendor.hoarding.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="oohCreateForm">
        @csrf
        <input type="hidden" name="step" value="{{ $step }}">
        <input type="hidden" name="go_back" id="goBackInput" value="0">

        {{-- Always pass ooh_id if draft exists --}}
        @if($draft)
            <input type="hidden" name="ooh_id" value="{{ $draft->id }}">
        @endif

        @if($step == 1)
            @include('hoardings.vendor.partials.step1', ['draft' => $draft])
        @elseif($step == 2)
            @include('hoardings.vendor.partials.step2', ['draft' => $draft, 'parentHoarding' => $draft?->hoarding])
        @elseif($step == 3)
            @include('hoardings.vendor.partials.step3', ['draft' => $draft, 'parentHoarding' => $draft?->hoarding])
        @endif

        <div class="flex justify-between gap-3 mb-8 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
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
            <div class="flex gap-3">

                <button type="submit" id="submitBtn"
                        class="px-7 py-2 rounded bg-[#009A5C] text-white font-semibold shadow hover:bg-[#008A52] transition">
                    @if($step < 3)
                        Save & Continue →
                    @else
                        Publish
                    @endif
                </button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const overlay     = document.getElementById('loadingOverlay');
    const loadingText = document.getElementById('loadingText');
    const form        = document.getElementById('oohCreateForm');
    const submitBtn   = document.getElementById('submitBtn');
    const prevBtn     = document.getElementById('prevBtn');
    const goBackInput = document.getElementById('goBackInput');
    const step        = {{ $step }};

    function showOverlay(msg) {
        loadingText.textContent = msg;
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }

    // Submit button
    if (form && submitBtn) {
        form.addEventListener('submit', function (e) {
            if (goBackInput && goBackInput.value === '1') return; // going back, different message set below
            const msg = step === 3 ? 'Publishing your hoarding...' : `Saving Step ${step}...`;
            showOverlay(msg);
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<svg class="animate-spin h-4 w-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg> ${step === 3 ? 'Publishing...' : 'Saving...'}`;
        });
    }

    // Previous button — submit form with go_back flag
    if (prevBtn && goBackInput) {
        prevBtn.addEventListener('click', function () {
            goBackInput.value = '1';
            showOverlay('Going back...');
            prevBtn.disabled = true;
            prevBtn.textContent = 'Going back...';
            form.submit();
        });
    }

    // bfcache guard — reset UI if user hits browser back
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            if (submitBtn) submitBtn.disabled = false;
            if (goBackInput) goBackInput.value = '0';
        }
    });
})();
</script>

@endsection