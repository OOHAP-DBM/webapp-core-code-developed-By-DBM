@php
    $user = auth()->user();
    $layout = ($user && $user->hasRole('admin')) ? 'layouts.admin' : 'layouts.vendor';
@endphp
@extends($layout)
@section('title', 'Enquiries')
@section('content')
<div x-data="{ openFilter: false, dateFilter: '{{ request('date_filter', 'all') }}',
    init() {
        this.openFilter = false;
        if (window.location.hash === '#filter') {
            window.location.hash = '';
        }
    }
}"
 x-init="init()"
 class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white rounded-md shadow">
        {{-- Header + Search --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <div>
                <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800">Enquiry & Offers</h4>
                <p class="text-sm text-gray-700 mt-1">Check all your sent offers to customers, track and manage them here.</p>
            </div>
            <div class="flex items-center gap-2 w-full lg:w-auto">
                <form method="GET" action="{{ route('vendor.enquiries.index') }}" class="relative flex-1 lg:w-80">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search enquiry by enquiry ID..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-9 text-sm focus:ring-1 focus:ring-primary focus:outline-none"
                    >
                    <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </form>
                <button
                    type="button"
                    @click="openFilter = true"
                    class="border border-gray-300 bg-white text-gray-800 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition whitespace-nowrap"
                >
                    Filter
                </button>
            </div>
        </div>

        <div class="p-4 sm:p-6 space-y-6">
            {{-- Table --}}
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Sn</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Enquiry ID</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Customer Name</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap text-center">No. of Hoardings</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">No. of Locations</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Status</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($enquiries as $index => $enquiry)
                            <tr class="hover:bg-gray-50">
                                {{-- SN --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-gray-700 whitespace-nowrap">
                                    {{ ($enquiries->currentPage() - 1) * $enquiries->perPage() + $index + 1 }}
                                </td>

                                {{-- ENQUIRY ID --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                    <a href="{{ route('vendor.enquiries.show', $enquiry->id) }}" class="text-green-600 font-semibold hover:text-green-700 hover:underline">
                                        {{ $enquiry->formatted_id }}
                                    </a>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $enquiry->created_at->format('d M, y') }}
                                    </div>
                                </td>

                                {{-- CUSTOMER --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3">
                                    <div class="font-semibold text-gray-900">
                                        {{ $enquiry->customer->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $enquiry->customer->email ?? '' }}
                                    </div>
                                </td>

                                {{-- HOARDINGS --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-center whitespace-nowrap">
                                    <span class="text-gray-900 font-semibold">
                                        {{ $enquiry->items()->count() }}
                                    </span>
                                </td>

                                {{-- LOCATIONS --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 max-w-xs">
                                    <div class="space-y-1">
                                        @forelse($enquiry->locations as $index => $loc)
                                            @if($index < 3)
                                                <div class="text-xs text-gray-600">{{ $index + 1 }}. {{ $loc }}</div>
                                            @elseif($index == 3)
                                                <div class="text-xs text-gray-500 italic">+{{ count($enquiry->locations) - 3 }} more</div>
                                                @break
                                            @endif
                                        @empty
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endforelse
                                    </div>
                                </td>

                                {{-- STATUS --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-xs font-semibold {{ $enquiry->status_color['text'] }}">
                                            @if($enquiry->status === 'submitted')
                                                <span class="text-green-600">Enquiry Received</span>
                                            @else
                                                {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $enquiry->updated_at->format('d M, y | H:i') }}
                                        </div>
                                    </div>
                                </td>

                                {{-- ACTION --}}
                                <td class="px-3 sm:px-4 py-2 sm:py-3 text-center whitespace-nowrap">
                                    <div class="flex gap-2 justify-center flex-wrap">
                                        @if($enquiry->status === 'submitted')
                                            <a href="{{ route('vendor.enquiries.show', $enquiry->id) }}"
                                               class="inline-flex items-center justify-center px-3 py-1.5 rounded text-xs font-semibold bg-blue-500 text-white hover:bg-blue-600 transition whitespace-nowrap">
                                                View Enquiry
                                            </a>
                                        @elseif($enquiry->status === 'accepted')
                                            <a href="{{ route('vendor.quotation.create', $enquiry->id) }}"
                                               class="inline-flex items-center justify-center px-3 py-1.5 rounded text-xs font-semibold bg-green-600 text-white hover:bg-green-700 transition whitespace-nowrap">
                                                Create Quotation
                                            </a>
                                        @elseif(in_array($enquiry->status, ['draft', 'responded', 'pending']))
                                            <a href="{{ route('vendor.enquiries.respond', $enquiry->id) }}"
                                               class="inline-flex items-center justify-center px-3 py-1.5 rounded text-xs font-semibold bg-green-600 text-white hover:bg-green-700 transition whitespace-nowrap">
                                                Send Counter Offer
                                            </a>
                                        @else
                                            <button class="inline-flex items-center justify-center px-3 py-1.5 rounded text-xs font-semibold bg-gray-300 text-gray-500 cursor-not-allowed whitespace-nowrap">
                                                Send Reminder
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                    <div class="space-y-2">
                                        <p class="font-medium">No enquiries found</p>
                                        <p class="text-xs">Start by waiting for customers to submit enquiries for your hoardings</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="pt-1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-gray-600">
                <div class="font-medium">
                    Showing {{ $enquiries->firstItem() ?? 0 }} - {{ $enquiries->lastItem() ?? 0 }} of {{ $enquiries->total() }}
                </div>
                <div>
                    {{ $enquiries->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER MODAL --}}
    <div x-show="openFilter === true" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div @click.away="openFilter = false" class="bg-white w-full max-w-2xl rounded shadow-lg relative">
            <div class="flex items-center justify-between h-10 bg-green-100 px-4 rounded-t">
                <span></span>
                <button @click="openFilter = false" class="text-gray-800 hover:text-black text-xl cursor-pointer">✕</button>
            </div>
            <form method="GET" class="p-6 space-y-6">
                <h2 class="inline-block text-lg font-semibold text-gray-900 border-b border-gray-700 pb-1">Filter</h2>
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Created Enquiry by date</h3>
                    <div class="flex flex-wrap items-center gap-6 text-sm text-gray-700">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="all" x-model="dateFilter"> All
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="last_week" x-model="dateFilter"> Last week
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="last_month" x-model="dateFilter"> Last month
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="last_year" x-model="dateFilter"> Last year
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="date_filter" value="custom" x-model="dateFilter"> Custom Date
                        </label>
                    </div>
                    <div x-show="dateFilter === 'custom'" x-transition class="mt-4 flex gap-4">
                        <input type="date" name="from_date" class="px-3 py-2 border border-gray-300 text-sm w-full" placeholder="From">
                        <input type="date" name="to_date" class="px-3 py-2 border border-gray-300 text-sm w-full" placeholder="To">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-6 pt-4">
                    <a href="{{ route('vendor.enquiries.index') }}" class="text-sm text-black font-semibold hover:underline cursor-pointer">Reset</a>
                    <button type="submit" class="px-6 py-2 bg-green-800 text-white text-sm font-semibold hover:bg-green-900 cursor-pointer">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
