@extends('layouts.vendor')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh] px-4">
    
    <div class="w-full max-w-2xl p-8 md:p-16 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col items-center justify-center">
        
        <div class="mb-6">
            <img src="{{ asset('images/hoarding-empty.svg') }}" 
                 alt="No Hoarding" 
                 class="w-24 h-24 md:w-32 md:h-32 object-contain opacity-80">
        </div>

        <h2 class="text-xl md:text-2xl font-semibold text-gray-800 mb-6 text-center">
            No Hoarding Added
        </h2>

        <a href="{{ route('vendor.hoardings.add') }}" 
           class="inline-flex items-center justify-center px-8 py-3 bg-[#38B2AC] hover:bg-[#319795] text-white font-medium rounded-lg transition-all duration-200 transform active:scale-95 shadow-md">
            <span class="mr-2 text-lg">+</span> 
            Add a Hoarding
        </a>
        
    </div>
</div>
@endsection