@extends('layouts.admin')

@section('content')
<div class="bg-white shadow-sm rounded w-full px-4 py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Email Templates</h1>
        <a href="{{ route('admin.mail.configuration.create') }}"
           class="btn-color text-white px-4 py-2 rounded">
            + New Template
        </a>
    </div>

    {{-- Success / Error Message --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Templates Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template Key</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Layout</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($templates as $template)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $template->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $template->template_key }}</code>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $template->subject }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $template->layout->font_family ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <form action="{{ route('admin.mail.configuration.toggle', $template->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="px-2 py-1 rounded text-xs font-semibold
                                {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-sm flex gap-2">
                        {{-- Preview --}}
                        <a href="{{ route('admin.mail.configuration.preview', $template->id) }}"
                           class="text-purple-600 hover:underline">Preview</a>

                        {{-- Edit --}}
                        <a href="{{ route('admin.mail.configuration.edit', $template->id) }}"
                           class="text-blue-600 hover:underline">Edit</a>

                        {{-- Delete --}}
                        <form action="{{ route('admin.mail.configuration.destroy', $template->id) }}" method="POST"
                              onsubmit="return confirm('Delete karna chahte ho?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                        Empty templates, create a first template!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $templates->links() }}
    </div>

</div>
@endsection