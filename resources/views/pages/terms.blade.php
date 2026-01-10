@extends('layouts.app')

@section('content')
@include('components.customer.navbar')

<div class="max-w-5xl mx-auto py-14 px-4">
    <h1 class="text-3xl font-bold mb-8 text-center">
        {{ $sections->first()->section_title ?? 'Terms & Conditions' }}
    </h1>

    <div class="prose prose-gray max-w-none">
        {!! $sections->first()->content !!}
    </div>
</div>

@include('components.customer.footer')
@endsection
