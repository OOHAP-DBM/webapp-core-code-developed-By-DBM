@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Edit Template — {{ $emailTemplate->name }}</h1>
        <a href="{{ route('admin.mail.configuration.index') }}"
           class="text-gray-600 hover:underline text-sm">← Back to List</a>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form action="{{ route('admin.mail.configuration.update', $emailTemplate->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Layout --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Layout <span class="text-red-500">*</span></label>
                    <select name="layout_id"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Select Layout --</option>
                        @foreach($layouts as $layout)
                            <option value="{{ $layout->id }}"
                                {{ old('layout_id', $emailTemplate->layout_id) == $layout->id ? 'selected' : '' }}>
                                Layout #{{ $layout->id }} ({{ $layout->primary_color }})
                            </option>
                        @endforeach
                    </select>
                    @error('layout_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Template Key — readonly --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Key</label>
                    <input type="text" value="{{ $emailTemplate->template_key }}"
                           class="w-full border border-gray-200 bg-gray-50 rounded px-3 py-2 text-sm text-gray-500 cursor-not-allowed"
                           readonly>
                    <p class="text-gray-400 text-xs mt-1">Template key change nahi hoga</p>
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name"
                           value="{{ old('name', $emailTemplate->name) }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject"
                           value="{{ old('subject', $emailTemplate->subject) }}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('subject')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Variables --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Variables</label>
                    <input type="text" name="variables_schema_raw"
                           value="{{ old('variables_schema_raw', implode(', ', $emailTemplate->variables_schema ?? [])) }}"
                           placeholder="e.g. name, email, order_id"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-gray-400 text-xs mt-1">Comma separated</p>
                </div>

                {{-- Body HTML --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Body HTML <span class="text-red-500">*</span></label>
                    <textarea name="body_html" rows="12"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('body_html', $emailTemplate->body_html) }}</textarea>
                    @error('body_html')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300">
                        Active hai ye template
                    </label>
                </div>

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 text-sm">
                    Update Template
                </button>
                <a href="{{ route('mail.configuration.index') }}"
                   class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200 text-sm">
                    Cancel
                </a>
            </div>

        </form>
    </div>

</div>
@endsection