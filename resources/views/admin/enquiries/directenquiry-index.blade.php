@extends('layouts.admin')
@section('title', 'Direct Enquiries')

@section('content')
<div class="px-6 py-6 bg-gray-50 min-h-screen">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    
    <!-- LEFT : Heading -->
    <h1 class="text-xl font-bold text-gray-900">
        Direct Enquiries
    </h1>

    <!-- RIGHT : Search -->
    <form method="GET" class="w-full sm:w-80">
        <div class="relative">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search name, email, phone, city"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm
                       focus:ring-2 focus:ring-green-500 focus:border-green-500"
            >
            <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </form>

</div>


    <!-- TABLE -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-700  ">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" id="selectAll"
                                class="rounded border-gray-300 text-green-600"
                                onclick="toggleAll(this)">
                        </th>

                        <th class="px-4 py-3 w-12 text-center">SN</th>

                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Contact</th>
                        <th class="px-4 py-3 text-left">City</th>
                        <th class="px-4 py-3 text-left">Preferred_Locations</th>
                        <th class="px-4 py-3 text-center">Hoarding Type</th>
                        <th class="px-4 py-3 text-center">Preferred Modes</th>
                        <th class="px-4 py-3 text-center">Verification</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($enquiries as $index => $enquiry)
                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3 w-10 text-center">
                                <input type="checkbox"
                                    class="row-checkbox rounded border-gray-300 text-green-600"
                                    value="{{ $enquiry->id }}">
                            </td>

                            <td class="px-4 py-3 w-12 text-center text-gray-500 font-medium">
                                {{ $enquiries->firstItem() + $index }}
                            </td>

                            <td class="px-4 py-3 text-left font-semibold text-gray-900">
                                {{ $enquiry->name }}
                            </td>

                            <td class="px-4 py-3 text-left">
                                <div>{{ $enquiry->email }}</div>
                                <div class="text-xs text-gray-500">{{ $enquiry->phone }}</div>
                            </td>

                            <td class="px-4 py-3 text-left">
                                {{ $enquiry->location_city ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center space-x-1">
                                @foreach($enquiry->preferred_locations ?? [] as $location)
                                    <span class="px-2 py-1 bg-gray-100 text-xs rounded">
                                        {{ $location }}
                                    </span>
                                @endforeach
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">
                                    {{ $enquiry->hoarding_type }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center space-x-1">
                                @foreach($enquiry->preferred_modes ?? [] as $mode)
                                    <span class="px-2 py-1 bg-gray-100 text-xs rounded">
                                        {{ $mode }}
                                    </span>
                                @endforeach
                            </td>

                            <td class="px-4 py-3 text-center text-xs">
                                <span class="{{ $enquiry->is_email_verified ? 'text-green-600' : 'text-red-500' }}">
                                    Email
                                </span> /
                                <span class="{{ $enquiry->is_phone_verified ? 'text-green-600' : 'text-red-500' }}">
                                    Phone
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $enquiry->status === 'pending'
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : 'bg-green-100 text-green-700' }}">
                                    {{ strtoupper($enquiry->status) }}
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-gray-500">
                                No enquiries found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($enquiries->hasPages())
        <div class="px-4 py-3 border-t bg-gray-50">
            {{ $enquiries->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
<script>
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>
