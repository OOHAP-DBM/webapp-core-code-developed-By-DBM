@extends('layouts.vendor')

@section('title', 'Edit OOH Hoarding')

@section('content')
<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">
    <!-- Stepper -->
    <div class="w-full max-w-5xl mx-auto py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-full overflow-hidden h-16 shadow-sm">
            <!-- Step 1 -->
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

            <!-- Step 2 -->
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

            <!-- Step 3 -->
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

    <!-- Form -->
    <form action="{{ route('vendor.update', $listing->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="step" value="{{ $step }}">

        @if($step == 1)
            @include('hoardings.vendor.partials.step1', ['draft' => $listing])
        @elseif($step == 2)
            @include('hoardings.vendor.partials.step2', ['draft' => $listing, 'parentHoarding' => $hoarding])
        @elseif($step == 3)
            @include('hoardings.vendor.partials.step3', ['draft' => $listing, 'parentHoarding' => $hoarding])
        @endif

        <!-- Action Buttons -->
        <div class="flex justify-between gap-3 mb-8">
            <a href="{{ route('vendor.hoardings.myHoardings') }}" 
               class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition">
                Cancel
            </a>
            
            <div class="flex gap-3">
                @if($step > 1)
                    <a href="{{ route('vendor.edit.ooh', ['id' => $listing->id, 'step' => $step - 1]) }}" 
                       class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition">
                        ← Previous
                    </a>
                @endif

                @if($step < 3)
                    <button type="submit" name="save_and_next" value="1" 
                            class="px-7 py-2 rounded bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">
                        Save & Continue →
                    </button>
                @else
                    <button type="submit" 
                            class="px-7 py-2 rounded bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition">
                        Update & Submit for Review
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection