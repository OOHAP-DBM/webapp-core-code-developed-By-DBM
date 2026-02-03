
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="hidden sm:inline font-semibold text-xs md:text-base {{ $step >= 1 ? 'text-[#009A5C]' : 'text-gray-400' }}">
                            Step 1
                        </span>
                    </div>

                    <div class="relative flex-1 flex items-center justify-center gap-2 md:gap-3 px-4 md:px-6
                        {{ $step >= 2 ? 'bg-white' : 'bg-gray-50' }}"
                        style="clip-path: polygon(0% 0%, 92% 0%, 100% 50%, 92% 100%, 0% 100%, 8% 50%);">
                        
                        <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full 
                            {{ $step >= 2 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="hidden sm:inline font-semibold text-xs md:text-base {{ $step >= 2 ? 'text-[#009A5C]' : 'text-gray-400' }}">
                            Step 2
                        </span>
                    </div>

                    <div class="relative flex-1 flex items-center justify-center gap-2 md:gap-3 pl-4 md:pl-6
                        {{ $step >= 3 ? 'bg-white' : 'bg-gray-50' }}"
                        style="clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 8% 50%);">
                        
                        <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 md:w-8 md:h-8 rounded-full 
                            {{ $step >= 3 ? 'bg-[#009A5C]' : 'bg-gray-400' }} text-white">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="hidden sm:inline font-semibold text-xs md:text-base {{ $step >= 3 ? 'text-[#009A5C]' : 'text-gray-400' }}">
                            Step 3
                        </span>
                    </div>

                </div>
            </div>

            <form action="{{ route('vendor.dooh.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 px-1 sm:px-0">
                @csrf
                <input type="hidden" name="step" value="{{ $step }}">
                @if($step > 1 && $draft)
                    <input type="hidden" name="screen_id" value="{{ $draft->id }}">
                @endif
                @if($step == 1)
                    @include('dooh.vendor.partials.step1', ['draft' => $draft])
                @elseif($step == 2)
                    @include('dooh.vendor.partials.step2', ['draft' => $draft])
                @elseif($step == 3)
                    @include('dooh.vendor.partials.step3', ['draft' => $draft])
                @endif

                <div class="flex flex-col sm:flex-row justify-between gap-2 sm:gap-3 mb-8 w-full">
                    <a href="{{ route('vendor.hoardings.myHoardings') }}" class="w-full sm:w-auto px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition text-center">Cancel</a>
                    <button type="submit" class="w-full sm:w-auto px-7 py-2 rounded bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition text-center" style="cursor:pointer;">
                         @if($step < 3)
                            Next
                        @else
                            Publish
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- All styling handled by Tailwind CSS classes -->
@endsection