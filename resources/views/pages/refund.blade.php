@extends('layouts.app')

@section('content')
@include('components.customer.navbar')

<div class="bg-gray-100 py-12">
    <div class="max-w-4xl mx-auto bg-white p-10 rounded-lg shadow">

        <h1 class="text-2xl font-bold mb-6 text-center">
            {{ $data->title }}
        </h1>

        <div class="prose max-w-none text-gray-800">
            {!! $data->content !!}
        </div>

    </div>
</div>

@include('components.customer.footer')
@endsection
