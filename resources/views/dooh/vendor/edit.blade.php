@extends('layouts.vendor')

@section('title', 'Edit DOOH Hoarding')

@section('content')
<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">
    <div class="w-full max-w-full sm:max-w-3xl md:max-w-4xl lg:max-w-5xl mx-auto py-4 sm:py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-lg md:rounded-full overflow-hidden min-h-[56px] md:h-16 shadow-sm">
            <!-- ...stepper UI, same as create... -->
        </div>
    </div>
    <form action="{{ route('vendor.dooh.update', $screen->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 px-1 sm:px-0">
        @csrf
        @method('PUT')
        <input type="hidden" name="step" value="1">
        @include('dooh.vendor.partials.step1', ['draft' => $screen])
        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mb-8 w-full">
            <a href="{{ route('hoardings.index') }}" class="w-full sm:w-auto px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition text-center">Cancel</a>
            <button type="submit" class="w-full sm:w-auto px-7 py-2 rounded bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition text-center">
                Update
            </button>
        </div>
    </form>
</div>
@endsection
