@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Preview — {{ $emailTemplate->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.mail.configuration.edit', $emailTemplate->id) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Edit</a>
            <a href="{{ route('admin.mail.configuration.index') }}"
               class="text-gray-600 hover:underline text-sm mt-2">← Back</a>
        </div>
    </div>

    {{-- Subject --}}
    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <p class="text-sm text-gray-500 font-medium">Subject:</p>
        <p class="text-gray-800 mt-1">{{ $rendered['subject'] }}</p>
    </div>

    {{-- Rendered Email Body --}}
    <div class="bg-white shadow rounded-lg p-4">
        <p class="text-sm text-gray-500 font-medium mb-3">Email Body Preview:</p>
        <div class="border rounded p-4 bg-gray-50">
            {!! $rendered['body'] !!}
        </div>
    </div>

</div>
@endsection