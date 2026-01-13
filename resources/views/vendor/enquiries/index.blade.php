@extends('layouts.vendor')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-xl font-semibold">Manage Enquiries</h2>
    <div class="flex justify-between items-center mb-4">
        <div>
            <p class="text-muted mb-0">Check all your received enquiries from customers, you can track and manage them here</p>
        </div>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer by name, email, mobile..." class="border rounded px-3 py-1 text-sm" />
            <button type="submit" class="px-4 py-1 bg-gray-200 rounded">Filter</button>
        </form>
    </div>
    @if($enquiries->count())
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">Sn #</th>
                        <th class="px-4 py-2 border">Enquiry ID</th>
                        <th class="px-4 py-2 border">Customer Name</th>
                        <th class="px-4 py-2 border">#of Hoardings</th>
                        <th class="px-4 py-2 border">#of Locations</th>
                        <th class="px-4 py-2 border">Enquiry Date</th>
                        <th class="px-4 py-2 border">Status</th>
                        <th class="px-4 py-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enquiries as $i => $enquiry)
                        <tr>
                            <td class="px-4 py-2 border">{{ ($enquiries->currentPage() - 1) * $enquiries->perPage() + $i + 1 }}</td>
                            <td class="px-4 py-2 border">#{{ $enquiry->id }}</td>
                            <td class="px-4 py-2 border">{{ $enquiry->customer->name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $enquiry->items->count() }}</td>
                            <td class="px-4 py-2 border">
                                @php
                                    $locations = $enquiry->items->pluck('hoarding.locality')->unique()->filter();
                                @endphp
                                @foreach($locations as $loc)
                                    <span class="inline-block bg-gray-100 px-2 py-1 rounded text-xs mr-1">{{ $loc }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-2 border">{{ $enquiry->created_at->format('d M, y') }}</td>
                            <td class="px-4 py-2 border">{{ ucfirst($enquiry->status) }}</td>
                            <td class="px-4 py-2 border">
                                <a href="#" class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-xs">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $enquiries->links() }}
        </div>
    @else
        <div class="alert alert-info">No enquiries to display.</div>
    @endif
</div>
@endsection
