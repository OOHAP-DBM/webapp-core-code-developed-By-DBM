@extends('layouts.app')

@section('content')
@include('components.customer.navbar')

<div class="max-w-5xl mx-auto py-14 px-4">
    <h1 class="text-3xl font-bold mb-8 text-center">
        {{ $data->title ?? 'Privacy Policy' }}
    </h1>

    <div class="prose prose-gray max-w-none">
        {!! $data->content !!}
    </div>
</div>

@include('components.customer.footer')
@endsection
