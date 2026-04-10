@extends('layouts.admin')

@section('content')
<div class="mx-auto bg-white p-6 rounded border border-gray-200 w-full">

    <!-- HEADER -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">
            📧 Preview — {{ $emailTemplate->name }}
        </h1>

        <div class="flex gap-3">
            <a href="{{ route('admin.mail.configuration.edit', $emailTemplate->id) }}"
               class="btn-color text-white px-4 py-2 rounded text-sm">
               Edit
            </a>

            <a href="{{ route('admin.mail.configuration.index') }}"
               class="text-gray-600 hover:underline text-sm mt-2">
               ← Back
            </a>
        </div>
    </div>

    <!-- SUBJECT -->
    <div class="bg-white border border-gray-200 rounded p-4 mb-4">
        <p class="text-sm text-gray-500 font-medium">Subject:</p>
        <p class="text-gray-800 mt-1 font-semibold">
            {{ $emailTemplate->subject }}
        </p>
    </div>
    <div class="bg-gray-200 p-6 rounded-lg">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded overflow-hidden">

            @php
                $bodyContent = $rendered['body'] ?? $emailTemplate->body_html;
                $bodyContent = preg_replace('/<header\b[^>]*>.*?<\/header>/is', '', $bodyContent);
                $bodyContent = preg_replace('/<footer\b[^>]*>.*?<\/footer>/is', '', $bodyContent);
            @endphp
            <div class="p-6 text-gray-800 leading-relaxed">
                {!! $bodyContent !!}
            </div>
        </div>
    </div>

</div>
@endsection