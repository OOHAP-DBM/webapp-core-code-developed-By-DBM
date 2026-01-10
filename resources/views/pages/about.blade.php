@extends('layouts.app')

@section('content')
@include('components.customer.navbar')

<div class="max-w-6xl mx-auto py-12 px-4">

    {{-- TOP DIRECTOR SECTION --}}
    <div class="bg-white rounded-xl shadow p-6 mb-10 flex flex-col md:flex-row gap-6">
        <img
            src="{{ asset($about->hero_image) }}"
            class="w-full md:w-1/3 rounded-lg object-cover"
            alt="OOHAPP Office"
        >

        <div class="flex-1">
            <p class="text-sm text-gray-600 mb-1">
                {{ strip_tags($about->hero_description) }}
            </p>

            <h2 class="text-2xl font-bold mt-4 mb-3">
                {{ $about->section_title }}
            </h2>

            <div class="text-gray-700 leading-relaxed space-y-4">
                {!! $about->section_content !!}
            </div>
        </div>
    </div>

    {{-- PURPLE DIVIDER --}}
    <div class="h-3 bg-gradient-to-r from-purple-600 to-purple-900 rounded mb-10"></div>

    {{-- OUR LEADERS --}}
    <h3 class="text-xl font-bold mb-6">OUR LEADERS</h3>

    @foreach($leaders as $leader)
        <div class="bg-white rounded-xl shadow p-6 flex gap-6 items-start">
            <img
                src="{{ asset($leader->image) }}"
                class="w-32 h-32 object-cover rounded-lg grayscale"
                alt="{{ $leader->name }}"
            >

            <div>
                <h4 class="text-lg font-bold">
                    {{ $leader->name }}
                </h4>
                <p class="text-sm text-gray-500 mb-2">
                    {{ $leader->designation }}
                </p>

                <p class="text-gray-700 leading-relaxed">
                    {{ $leader->bio }}
                </p>
            </div>
        </div>
    @endforeach

</div>

@include('components.customer.footer')
@endsection
