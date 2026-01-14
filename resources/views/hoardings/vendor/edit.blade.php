@extends('layouts.vendor')

@section('title', 'Edit OOH Hoarding')

@section('content')
<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">
    <div class="w-full max-w-5xl mx-auto py-6">
        <div class="flex items-stretch w-full bg-white border border-gray-300 rounded-full overflow-hidden h-16 shadow-sm">
            <!-- ...stepper UI, same as create... -->
        </div>
    </div>
    <form action="{{ route('vendor.hoardings.update', $listing->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="step" value="1">
        @include('hoardings.vendor.partials.step1', ['draft' => $listing])
        <div class="flex justify-end gap-3 mb-8">
            <a href="{{ route('hoardings.index') }}" class="px-5 py-2 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition">Cancel</a>
            <button type="submit" class="px-7 py-2 rounded bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition">
                Update
            </button>
        </div>
    </form>
</div>
@endsection
