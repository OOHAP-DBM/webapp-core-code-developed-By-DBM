@extends('layouts.app')

@section('content')
@include('components.customer.navbar')

<div class="max-w-5xl mx-auto py-14 px-4">

    {{-- WHITE CARD --}}
    <div class="bg-white rounded-xl shadow p-8">

        <h1 class="text-3xl font-bold mb-8 text-center">
            {{ $sections?->first()?->section_title ?? 'Terms & Conditions' }}
        </h1>

        @if($sections && $sections->count() > 0 && $sections->first()->content)
            <div class="prose prose-gray max-w-none mx-auto">
                {!! $sections->first()->content !!}
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-500">Terms and conditions content is not available.</p>
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
    .section-content{
        padding-left:25px;
    }
    .tc-section h2 {
        margin-bottom: 6px;
    }
    .tc-section .section-content {
        margin-top: 8px;
    }
</style>
@endsection
