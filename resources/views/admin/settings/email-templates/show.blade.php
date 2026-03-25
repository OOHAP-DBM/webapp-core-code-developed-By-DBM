@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">{{ $emailTemplate->name }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.mail.configuration.preview', $emailTemplate->id) }}"
               class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 text-sm">Preview</a>
            <a href="{{ route('admin.mail.configuration.edit', $emailTemplate->id) }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">Edit</a>
            <a href="{{ route('admin.mail.configuration.index') }}"
               class="text-gray-600 hover:underline text-sm mt-2">← Back</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6 space-y-4">

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-600">Template Key:</span>
                <code class="ml-2 bg-gray-100 px-2 py-1 rounded">{{ $emailTemplate->template_key }}</code>
            </div>
            <div>
                <span class="font-medium text-gray-600">Status:</span>
                <span class="ml-2 px-2 py-1 rounded text-xs
                    {{ $emailTemplate->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $emailTemplate->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Subject:</span>
                <span class="ml-2 text-gray-800">{{ $emailTemplate->subject }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Variables:</span>
                <span class="ml-2 text-gray-800">
                    {{ implode(', ', $emailTemplate->variables_schema ?? []) ?: 'None' }}
                </span>
            </div>
        </div>

        <div>
            <p class="font-medium text-gray-600 text-sm mb-2">Body HTML:</p>
            <pre class="bg-gray-50 border rounded p-4 text-xs overflow-auto max-h-64 font-mono">{{ $emailTemplate->body_html }}</pre>
        </div>

    </div>

</div>
@endsection