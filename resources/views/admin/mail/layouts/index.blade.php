@extends('layouts.admin')

@section('content')
<div class="bg-white shadow-sm rounded w-full px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold text-gray-800">Mail Layouts</h1>
        <a href="#" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">+ Add Layout</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">ID</th>
                    <th class="px-4 py-2 text-left">Logo</th>
                    <th class="px-4 py-2 text-left">Header</th>
                    <th class="px-4 py-2 text-left">Footer</th>
                    <th class="px-4 py-2 text-left">Primary Color</th>
                    <th class="px-4 py-2 text-left">Font Family</th>
                    <th class="px-4 py-2 text-left">Active</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($layouts as $layout)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $layout->id }}</td>
                        <td class="px-4 py-2">
                            @if(!empty($layout->logo_url))
                                <div class="mb-3">
                                    <img src="{{ asset($layout->logo_url) }}" 
                                        class="h-12 bg-gray-100 p-2 rounded shadow">
                                </div>
                            @else
                                <span class="text-gray-400 italic">No logo</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 max-w-xs truncate">{!! Str::limit(strip_tags($layout->header_html), 40) !!}</td>
                        <td class="px-4 py-2 max-w-xs truncate">{!! Str::limit(strip_tags($layout->footer_html), 40) !!}</td>
                        <td class="px-4 py-2">{{ $layout->primary_color }}</td>
                        <td class="px-4 py-2">{{ $layout->font_family }}</td>
                        <td class="px-4 py-2">
                            @if($layout->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Active</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-500 rounded text-xs">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.mail.layouts.edit', $layout->id) }}" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-400">No layouts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

