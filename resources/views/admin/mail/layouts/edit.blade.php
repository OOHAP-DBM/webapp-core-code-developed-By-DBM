@extends('layouts.admin')

@section('content')
<div class="w-full bg-white border border-gray-200 rounded px-6 py-6">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">📧 Edit Mail Layout</h1>
        <a href="{{ route('admin.mail.layouts.index') }}" 
           class="text-sm text-gray-600 hover:underline">
            ← Back
        </a>
    </div>

    <!-- CARD -->
    <div class="bg-white border border-gray-200 rounded p-6">

        <form action="{{ route('admin.mail.layouts.update', $layout->id) }}" 
              method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- LOGO -->
                <div>
                    <label class="block text-sm font-medium mb-2">Logo Image</label>

                    @if(!empty($layout->logo_url))
                        <div class="mb-3">
                            <img src="{{ asset($layout->logo_url) }}" 
                                 class="h-12 bg-gray-100 p-2 rounded shadow">
                        </div>
                    @endif

                    <input type="file" name="logo_url"
                        class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <!-- PRIMARY COLOR -->
                <div>
                    <label class="block text-sm font-medium mb-2">Primary Color</label>
                    <input type="color" 
                        name="primary_color"
                        value="{{ old('primary_color', $layout->primary_color ?? '#2563eb') }}"
                        class="w-20 h-10 border rounded cursor-pointer">
                </div>

                <!-- FONT -->
                <div>
                    <label class="block text-sm font-medium mb-2">Font Family</label>
                    <input type="text" name="font_family"
                        value="{{ old('font_family', $layout->font_family) }}"
                        placeholder="Arial, sans-serif"
                        class="w-full border rounded px-3 py-2">
                </div>

                <!-- STATUS -->
                <div class="flex items-center mt-6">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $layout->is_active) ? 'checked' : '' }}
                        class="mr-2">
                    <label class="text-sm font-medium">Active Layout</label>
                </div>

            </div>

            <!-- HEADER HTML -->
            <div class="mt-6">
                <label class="block text-sm font-medium mb-2">Header HTML</label>
                <textarea 
                    name="header_html"
                    rows="8"
                    class="w-full border rounded px-3 py-2 font-mono text-sm"
                >{{ old('header_html', $layout->header_html) }}</textarea>
            </div>

            <!-- FOOTER HTML -->
            <div class="mt-6">
                <label class="block text-sm font-medium mb-2">Footer HTML</label>
                <textarea 
                    name="footer_html"
                    rows="8"
                    class="w-full border rounded px-3 py-2 font-mono text-sm"
                >{{ old('footer_html', $layout->footer_html) }}</textarea>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('admin.mail.layouts.index') }}"
                   class="px-5 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Cancel
                </a>

                <button type="submit"
                    class="px-6 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 shadow">
                    Update Layout
                </button>
            </div>

        </form>

    </div>

</div>
@endsection