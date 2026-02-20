@extends('layouts.vendor')

@section('title', 'Edit OOH Hoarding')

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
    <!-- Stepper -->
    <div class="w-full max-w-5xl mx-auto py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-full overflow-hidden h-16 shadow-sm">
            <div class="relative flex-1 flex items-center justify-center gap-3 pr-6 
                {{ $step >= 1 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 95% 0%, 100% 50%, 95% 100%, 0% 100%);">
                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                    {{ $step >= 1 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    @if($hoarding->current_step > 1 || $step > 1)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <span>1</span>
                    @endif
                </div>
                <span class="font-semibold {{ $step >= 1 ? 'text-[#009A5C]' : 'text-gray-400' }}">Basic Info</span>
            </div>

            <div class="relative flex-1 flex items-center justify-center gap-3 px-6 border-l border-gray-300
                {{ $step >= 2 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 95% 0%, 100% 50%, 95% 100%, 0% 100%, 5% 50%);">
                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                    {{ $step >= 2 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    @if($hoarding->current_step > 2 || $step > 2)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <span>2</span>
                    @endif
                </div>
                <span class="font-semibold {{ $step >= 2 ? 'text-[#009A5C]' : 'text-gray-400' }}">Settings</span>
            </div>

            <div class="relative flex-1 flex items-center justify-center gap-3 pl-6 border-l border-gray-300
                {{ $step >= 3 ? 'bg-white' : 'bg-gray-50' }}"
                style="clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 5% 50%);">
                <div class="flex items-center justify-center w-8 h-8 rounded-full 
                    {{ $step >= 3 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                    <span>3</span>
                </div>
                <span class="font-semibold {{ $step >= 3 ? 'text-[#009A5C]' : 'text-gray-400' }}">Pricing</span>
            </div>
        </div>
    </div>

    <form action="{{ route('vendor.update', $listing->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="oohEditForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="step" value="{{ $step }}">
        <input type="hidden" name="id" value="{{ $listing->id }}">

        @if($step == 1)
            @include('hoardings.vendor.partials.step1', ['draft' => $listing, 'media' => $hoarding->oohMedia])
        @elseif($step == 2)
            @include('hoardings.vendor.partials.step2', ['draft' => $listing, 'parentHoarding' => $hoarding])
        @elseif($step == 3)
            @include('hoardings.vendor.partials.step3', ['draft' => $listing, 'parentHoarding' => $hoarding])
        @endif

        <div class="flex justify-between gap-3 mb-8 max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
             @if($step == 1)
                <a href="{{ route('vendor.hoardings.myHoardings') }}"
                    class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition text-center">
                    Cancel
                </a>

            @else
                {{-- Plain link — no form submit needed for edit since IDs are stable --}}
                <a href="{{ route('vendor.edit.ooh', ['id' => $listing->id, 'step' => $step - 1]) }}"
                    class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition">
                    ← Previous
                </a>
            @endif

                <button type="submit" id="submitBtn"
                        class="w-full sm:w-auto bg-[#009A5C] hover:bg-[#008A52] text-white px-6 py-3 rounded-xl font-bold transition-all">
                    @if($step < 3)
                        Save & Continue →
                    @else
                        Update & Submit 
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
    const form        = document.getElementById('oohEditForm');
    const submitBtn   = document.getElementById('submitBtn');
    const step        = {{ $step }};

    function showOverlay(msg) {
        loadingText.textContent = msg;
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            const msg = step === 3 ? 'Updating hoarding...' : `Saving Step ${step}...`;
            showOverlay(msg);
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<svg class="animate-spin h-4 w-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg> ${step === 3 ? 'Updating...' : 'Saving...'}`;
        });
    }

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            if (submitBtn) submitBtn.disabled = false;
        }
    });
})();
</script>

@endsection