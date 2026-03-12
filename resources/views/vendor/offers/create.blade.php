@extends('layouts.vendor')

@section('title', 'Create Offer')
@section('content')
<div class="px-6 py-6 bg-gray-50">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
            @include('vendor.offers.components.offer-form')
        </div>
        <div class="lg:col-span-5">
            @include('vendor.offers.components.offer-inventory')
        </div>
    </div>
    <div id="preview-screen" class="hidden animate-fade-in">
        @include('vendor.offers.components.offer-preview')
    </div>
</div>
@endsection
