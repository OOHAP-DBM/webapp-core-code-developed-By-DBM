@extends('layouts.vendor')

@section('title', 'Create Offer')
@section('content')
<div class=" bg-gray-50">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            @include('offers::components.vendor.offer-form')
        </div>
        <div class="lg:col-span-5">
            @include('offers::components.vendor.offer-inventory')
        </div>
    </div>
    <div id="preview-screen" class="hidden animate-fade-in">
        @include('offers::components.vendor.offer-preview')
    </div>
</div>
@endsection
