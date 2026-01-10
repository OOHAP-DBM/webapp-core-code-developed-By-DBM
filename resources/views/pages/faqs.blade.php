@extends('layouts.app')

@section('content')
@include('components.customer.navbar')

<div class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold mb-8 text-center">FAQ</h1>

    <div class="space-y-4">
        @foreach($faqs as $faq)
            <div 
                x-data="{ open: false }"
                class="border border-gray-300 rounded-lg"
            >
                <!-- QUESTION -->
                <button
                    type="button"
                    @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-gray-900"
                >
                    <span class="flex items-center gap-2">
                        <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.19337 8.43476C1.06653 9.1025 1 9.79309 1 10.5C1 16.299 5.47715 21 11 21C16.5228 21 21 16.299 21 10.5C21 4.70101 16.5228 0 11 0C10.0948 0 9.21772 0.126281 8.38383 0.362979V5.73332C9.15366 5.26649 10.0471 4.99936 11 4.99936C13.8933 4.99936 16.2387 7.46208 16.2387 10.5C16.2387 13.5379 13.8933 16.0006 11 16.0006C8.10674 16.0006 5.7613 13.5379 5.7613 10.5C5.7613 9.76959 5.89689 9.07243 6.14307 8.43476H1.19337Z" fill="black"/>
                        <path d="M0 0.7875L7.25 2.05941V7.35L0 6.07809V0.7875Z" fill="#00B711"/>
                        </svg>
                        {{ $loop->iteration }}. {{ $faq->question }}
                    </span>

                    <!-- Arrow -->
                    <svg
                        :class="open ? 'rotate-180' : ''"
                        class="w-5 h-5 transition-transform"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- ANSWER -->
                <div
                    x-show="open"
                    x-collapse
                    class="px-5 pb-5 text-gray-700 leading-relaxed"
                >
                    {!! $faq->answer !!}
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('components.customer.footer')
@endsection
