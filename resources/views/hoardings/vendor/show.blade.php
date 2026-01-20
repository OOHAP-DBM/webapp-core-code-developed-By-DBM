@extends('layouts.vendor')
@section('title', 'Preview: ' . $hoarding->title)

@section('content')
<div class="hoarding-wrapper">
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