@extends('layouts.vendor')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Direct Enquiries</h2>
    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
            @forelse($enquiries as $enquiry)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $enquiry->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">{{ $enquiry->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $enquiry->location_city }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $enquiry->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($enquiry->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($enquiry->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $enquiry->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('vendor.enquiries.show', $enquiry->id) }}" class="inline-block px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded hover:bg-emerald-700 transition">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">No direct enquiries found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $enquiries->links() }}
        </div>
    </div>
</div>
@endsection
