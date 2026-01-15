@extends('layouts.vendor')

@section('title', 'Edit DOOH Screen')

@section('content')
<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">
    <div class="w-full max-w-full sm:max-w-3xl md:max-w-4xl lg:max-w-5xl mx-auto py-4 sm:py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-lg md:rounded-full overflow-hidden min-h-[56px] md:h-16 shadow-sm">
            @php
                $steps = [
                    1 => 'Details',
                    2 => 'Settings',
                    3 => 'Pricing'
                ];
            @endphp
            @foreach($steps as $num => $label)
            <div class="flex-1 flex items-center justify-center gap-2 px-3 sm:px-4 md:px-6 py-2 {{ $step == $num ? 'bg-[#009A5C] text-white' : 'bg-white text-gray-600' }} transition-all {{ $step > $num ? 'bg-green-100 text-[#009A5C]' : '' }}">
               
                <div class="flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full {{ $step == $num ? 'bg-white text-[#009A5C]' : 'bg-gray-200 text-gray-600' }} {{ $step > $num ? 'bg-[#009A5C] text-white' : '' }} text-xs sm:text-sm font-bold">
                    @if($step > $num)
                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    @else
                        {{ $num }}
                    @endif
                </div>
                <span class="hidden sm:block text-xs md:text-sm font-semibold whitespace-nowrap">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <form action="{{ route('vendor.dooh.update', $screen->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 px-1 sm:px-0">
        @csrf
        @method('PUT')
        <input type="hidden" name="step" value="{{ $step }}">

        @if($step == 1)
            @include('dooh.vendor.partials.step1', ['draft' => $screen, 'attributes' => $attributes])
        @elseif($step == 2)
            @include('dooh.vendor.partials.step2', ['draft' => $screen, 'parentHoarding' => $screen->hoarding])
        @elseif($step == 3)
            @include('dooh.vendor.partials.step3', ['draft' => $screen, 'parentHoarding' => $screen->hoarding])
        @endif

        <div class="flex flex-col sm:flex-row justify-between gap-2 sm:gap-3 mb-8 w-full ">
              <a href="{{ route('vendor.hoardings.myHoardings') }}" 
               class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition">
                Cancel
            </a>
            
            @if($step > 1)
            <a href="{{ route('vendor.dooh.edit', ['id' => $screen->id, 'step' => $step - 1]) }}" 
               class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-bold transition-all text-center">
                ← Previous
            </a>
            @else
            <div></div>
            @endif

            @if($step < 3)
            <button type="submit" name="save_and_next" value="1"
                class="w-full sm:w-auto bg-[#009A5C] hover:bg-[#008A52] text-white px-6 py-3 rounded-xl font-bold transition-all">
                Save & Continue →
            </button>
            @else
            <button type="submit"
                class="w-full sm:w-auto bg-[#009A5C] hover:bg-[#008A52] text-white px-6 py-3 rounded-xl font-bold transition-all">
                Update DOOH Screen
            </button>
            @endif
        </div>
    </form>
</div>
@endsection