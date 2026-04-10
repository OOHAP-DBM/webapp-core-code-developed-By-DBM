@extends('layouts.admin')

@section('content')
<div class="bg-white border border-gray-200 rounded w-full px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">New Email Template</h1>
        <a href="{{ route('admin.mail.configuration.index') }}"
           class="text-gray-600 hover:underline text-sm">← Back to List</a>
    </div>

    {{-- Form --}}
    <div class="bg-white border border-gray-200 rounded p-6">
        <form action="{{ route('admin.mail.configuration.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Layout --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Layout <span class="text-red-500">*</span></label>
                    <input type="text"
                        value="Layout #{{ $layouts->first()->id ?? '' }} ({{ $layouts->first()->primary_color ?? '' }})"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed"
                        readonly>
                    <input type="hidden" name="layout_id" value="{{ old('layout_id', $layouts->first()->id ?? '') }}">
                    @error('layout_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Template Key --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Key <span class="text-red-500">*</span></label>
                    <input type="text" name="template_key" value="{{ old('template_key') }}"
                           placeholder="e.g. welcome_user"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-gray-400 text-xs mt-1">Unique key — lowercase, underscore only</p>
                    @error('template_key')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="e.g. Welcome Email"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}"
                           placeholder="e.g. Welcome @{{name}}"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('subject')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Variables Schema --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Variables</label>
                    <input type="text" name="variables_schema_raw" value="{{ old('variables_schema_raw') }}"
                           placeholder="e.g. name, email, order_id"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-gray-400 text-xs mt-1">Comma separated — jo variables body me use honge</p>
                </div>

                {{-- Body HTML --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Body HTML <span class="text-red-500">*</span></label>
                    <textarea name="body_html" rows="12"
                              placeholder="<p>Hello @{{name}}, ...</p>"
                              class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('body_html') }}</textarea>
                    @error('body_html')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', '1') ? 'checked' : '' }}
                               class="rounded border-gray-300">
                        Active karo is template ko
                    </label>
                </div>

            </div>

            {{-- Submit --}}
            <div class="mt-6 flex gap-3">
                <button type="submit"
                        class="btn-color text-white px-6 py-2 rounded text-sm">
                    Save Template
                </button>
                <a href="{{ route('admin.mail.configuration.index') }}"
                   class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200 text-sm">
                    Cancel
                </a>
            </div>

        </form>
    </div>

</div>
@endsection