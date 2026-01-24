@extends('layouts.app')

@section('content')
@include('components.customer.navbar')

<div class="max-w-7xl mx-auto py-14 px-4">

    {{-- WHITE CARD --}}
    <div class="bg-white  rounded-xl shadow p-8">

        <!-- <h1 class="text-3xl font-bold mb-8 text-center">
            {{ $data?->title ?? 'Disclaimer' }}
        </h1> -->

        @if($data && $data->content)
            <div class="prose prose-gray max-w-none mx-auto">
                {!! $data->content !!}
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">Disclaimer content is not available.</p>
            </div>
        @endif

    </div>

</div>

<style>
    hr {
        border: none;
        border-top: 1px solid #e5e7eb;
        margin: 24px 0;
    }

    .prose p:not(:first-of-type) {
        padding-left: 20px;
    }
</style>
@endsection