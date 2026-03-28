@extends('layouts.admin')

@section('content')
<div class="bg-white border border-gray-200 rounded w-full px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">Mail Layouts</h1>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Logo</th>
                    <th class="px-4 py-2 text-left">Primary Color</th>
                    <th class="px-4 py-2 text-left">Font Family</th>
                    <th class="px-4 py-2 text-left">Header</th>
                    <th class="px-4 py-2 text-left">Footer</th>
                    <th class="px-4 py-2 text-left">Active</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($layouts as $layout)
                    <tr class="border-b">

                        {{-- ID --}}
                        <td class="px-4 py-2">{{ $layout->id }}</td>

                        {{-- LOGO --}}
                        <td class="px-4 py-2">
                            @if(!empty($layout->logo_url))
                                <img src="{{ asset($layout->logo_url) }}"
                                    class="h-10 bg-gray-100 p-1 rounded object-contain">
                            @else
                                <span class="text-gray-400 italic text-xs">No logo</span>
                            @endif
                        </td>

                        {{-- PRIMARY COLOR --}}
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-5 h-5 rounded border"
                                    style="background-color: {{ $layout->primary_color ?? '#ccc' }}"></span>
                                <span class="text-xs text-gray-600">{{ $layout->primary_color }}</span>
                            </div>
                        </td>

                        {{-- FONT FAMILY --}}
                        <td class="px-4 py-2 text-xs text-gray-600">{{ $layout->font_family }}</td>

                        {{-- HEADER PREVIEW BUTTON --}}
                        <td class="px-4 py-2">
                            <button
                                onclick="openPreview('header-{{ $layout->id }}')"
                                class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs hover:bg-indigo-200">
                                👁 View
                            </button>
                        </td>

                        {{-- FOOTER PREVIEW BUTTON --}}
                        <td class="px-4 py-2">
                            <button
                                onclick="openPreview('footer-{{ $layout->id }}')"
                                class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs hover:bg-indigo-200">
                                👁 View
                            </button>
                        </td>

                        {{-- ACTIVE STATUS --}}
                        <td class="px-4 py-2">
                            @if($layout->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded text-xs">Inactive</span>
                            @endif
                        </td>

                        {{-- ACTIONS --}}
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.mail.layouts.edit', $layout->id) }}"
                                class="px-3 py-1 btn-color text-white rounded text-xs">
                                Edit
                            </a>
                        </td>
                    </tr>

                    {{-- HEADER MODAL --}}
                    <div id="header-{{ $layout->id }}"
                        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b">
                                <h3 class="font-semibold text-gray-700 text-sm">Header Preview — Layout #{{ $layout->id }}</h3>
                                <button onclick="closePreview('header-{{ $layout->id }}')"
                                    class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                            </div>
                            <div class="p-4 bg-gray-50 max-h-[70vh] overflow-y-auto">
                                @php
                                    $headerPreview = str_replace(
                                        ['{{logo_url}}', '{{primary_color}}'],
                                        [asset($layout->logo_url), $layout->primary_color ?? '#22c55e'],
                                        $layout->header_html
                                    );
                                @endphp
                                {!! $headerPreview !!}
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER MODAL --}}
                    <div id="footer-{{ $layout->id }}"
                        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b">
                                <h3 class="font-semibold text-gray-700 text-sm">Footer Preview — Layout #{{ $layout->id }}</h3>
                                <button onclick="closePreview('footer-{{ $layout->id }}')"
                                    class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                            </div>
                            <div class="p-4 bg-gray-50 max-h-[70vh] overflow-y-auto">
                                {!! $layout->footer_html !!}
                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-400">No layouts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function openPreview(id) {
        const el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function closePreview(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    // Click outside modal to close
    document.querySelectorAll('[id^="header-"], [id^="footer-"]').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) closePreview(this.id);
        });
    });
</script>
@endsection