@extends('layouts.vendor')

@section('title', 'Hoarding Completion Tracker')

@section('content')
<div class="py-4 px-2 sm:px-4 md:px-6 lg:px-8 w-full max-w-8xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Your Hoarding Listings Completion</h2>
    <div class="space-y-6">
        @foreach($hoardings as $hoarding)
            <div class="bg-white rounded-xl shadow p-6 flex items-center gap-6">
                <div class="flex-1">
                    <div class="font-semibold text-lg mb-2">{{ $hoarding['title'] ?? 'Untitled' }}</div>
                    <div class="w-full bg-gray-200 rounded-full h-4 relative">
                        <div class="bg-green-500 h-4 rounded-full transition-all duration-300" style="width: {{ $hoarding['completion'] }}%"></div>
                        <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-xs font-bold text-gray-800">{{ $hoarding['completion'] }}%</span>
                    </div>
                </div>
                <div class="ml-4">
                    <span class="text-gray-500 text-xs" title="Add missing fields to reach 100%">{{ $hoarding['completion'] < 100 ? 'Incomplete' : 'Complete' }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
