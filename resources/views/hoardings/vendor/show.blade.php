@extends('layouts.vendor')
@section('title', 'Preview: ' . $hoarding->title)

@section('content')
<div class="hoarding-wrapper">
   <a href="{{ url()->previous() == url()->current() ? '#' : url()->previous() }}" 
       onclick="if(window.history.length > 1) { window.history.back(); return false; }"
       class="inline-flex items-center gap-2 mb-6 text-sm font-semibold text-gray-500 hover:text-blue-600 transition-colors group">
        <span class="transform group-hover:-translate-x-1 transition-transform">←</span>
        Back to Previous
    </a>
    {{-- Gallery/Media Preview --}}
    @include('hoardings.partials.gallery')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- LEFT --}}
        <div class="lg:col-span-8 space-y-6">
            @include('hoardings.partials.basic-info')
            @include('hoardings.partials.details')
            @include('hoardings.partials.gazeflow')
            @include('hoardings.partials.audience')
            @include('hoardings.partials.location')
            @include('hoardings.partials.attributes')
            @include('hoardings.partials.reviews')
        </div>
        {{-- RIGHT --}}
        <div class="lg:col-span-4">
            @include('hoardings.partials.price-box')
        </div>
    </div>
</div>
@endsection