@extends('layouts.app')

@section('title', 'Sortlisted Hoardings')

@section('content')
@include('components.customer.navbar')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Breadcrumb --}}
    <p class="text-xs text-gray-400 mb-3">
        Home / OOH / Sortlisted Hoardings
    </p>

    <h1 class="text-xl font-semibold text-gray-900 mb-6">
        Sortlisted ({{ $items->count() }} Hoardings)
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT LIST --}}
        <div class="lg:col-span-8">
            @include('cart.partials.list', ['items' => $items])
        </div>

        {{-- RIGHT SUMMARY --}}
        <div class="lg:col-span-4">
            @include('cart.partials.summary')
        </div>

    </div>
</div>
@include('components.customer.footer')
@endsection
