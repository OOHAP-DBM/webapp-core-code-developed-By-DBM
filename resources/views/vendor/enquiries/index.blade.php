@php
    $user = auth()->user();
    $layout = ($user && $user->hasRole('admin')) ? 'layouts.admin' : 'layouts.vendor';
@endphp
@extends($layout)
@section('title', 'Enquiries')
@section('content')

<div x-data="{ openFilter: false, dateFilter: '{{ request('date_filter', 'all') }}',
    init() {
        // Always ensure modal is closed on load, navigation, or back
        this.openFilter = false;
        // Remove any accidental hash or query param that could trigger modal
        if (window.location.hash === '#filter') {
            window.location.hash = '';
        }
    }
}"
 x-init="init()"
 class="px-6 py-6 bg-white">
    {{-- FILTER BAR --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div class="mb-6">
            <h1 class="text-lg font-bold text-gray-900">
                Enquiry & Manage Offers
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Check all your sent offers to customers, you can track and manage them here
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="relative flex-1 md:w-72">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search Enquiry By enquiry Id . "
                    class="w-full px-4 py-2 pr-10 border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
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
                class="px-4 py-2 border border-gray-300 bg-white text-gray-900 text-sm hover:bg-gray-100 font-medium cursor-pointer"
            >
                Filter
            </button>
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

    {{-- TABLE --}}
    <div class="bg-white border border-gray-200  overflow-x-auto shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Sn</th>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Enquiry ID</th>
                    <!-- <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Offer ID</th> -->
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Customer Name</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">No. of Hoardings</th>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">No. of Locations</th>
                    <!-- <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Offer Valid Till</th> -->
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Status</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($enquiries as $index => $enquiry)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- SN --}}
                        <td class="px-4 py-4 text-gray-700">
                            {{ ($enquiries->currentPage() - 1) * $enquiries->perPage() + $index + 1 }}
                        </td>

                        {{-- ENQUIRY ID --}}
                        <td class="px-4 py-4">
                            <a href="{{ route('vendor.enquiries.show', $enquiry->id) }}" class="text-green-600 font-semibold hover:text-green-700 hover:underline cursor-pointer bg-transparent border-0 p-0">
                                {{ $enquiry->formatted_id }}
                            </a>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $enquiry->created_at->format('d M, y') }}
                            </div>
                        </td>

                        {{-- OFFER ID --}}
                        <!-- <td class="px-4 py-4">
                            @php
                                $latestOffer = $enquiry->offers()->latest()->first();
                            @endphp
                            @if($latestOffer)
                                <span class="text-green-600 font-semibold">#{{ $latestOffer->id }}</span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td> -->

                        {{-- CUSTOMER --}}
                        <td class="px-4 py-4">
                            <div class="font-semibold text-gray-900">
                                {{ $enquiry->customer->name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $enquiry->customer->email ?? '' }}
                            </div>
                        </td>

                        {{-- HOARDINGS --}}
                        <td class="px-4 py-4 text-center">
                            <span class="text-gray-900 font-semibold">
                                {{ $enquiry->items()->count() }}
                            </span>
                        </td>

                        {{-- LOCATIONS --}}
                        <td class="px-4 py-4 max-w-xs">
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

                        {{-- VALID TILL --}}
                        <!-- <td class="px-4 py-4">
                            @if($enquiry->valid_till)
                                <div class="text-gray-900 font-semibold">{{ $enquiry->valid_till->format('d M, y') }}</div>
                                <div class="text-xs mt-1">
                                    @if($enquiry->validity_status === 'expired')
                                        <span class="text-red-600 font-medium">Expired</span>
                                    @elseif($enquiry->validity_status === 'expiring_soon')
                                        <span class="text-orange-600 font-medium">{{ $enquiry->days_left }} days left</span>
                                    @else
                                        <span class="text-green-600 font-medium">{{ $enquiry->days_left }} days left</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td> -->

                        {{-- STATUS --}}
                        <td class="px-4 py-4">
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
                        <td class="px-4 py-4 text-center">
                            <div class="flex gap-2 justify-center flex-wrap">
                                @if($enquiry->status === 'submitted')
                                    <a href="{{ route('vendor.enquiries.show', $enquiry->id) }}"
                                       class="px-4 py-2 bg-gray-900 text-white  text-xs hover:bg-gray-800 font-semibold cursor-pointer whitespace-nowrap">
                                        View Enquiry
                                    </a>
                                @elseif($enquiry->status === 'accepted')
                                    <a href="{{ route('vendor.quotation.create', $enquiry->id) }}"
                                       class="px-4 py-2 bg-green-600 text-white  text-xs hover:bg-green-700 font-semibold inline-block whitespace-nowrap">
                                        Create Quotation
                                    </a>
                                @elseif(in_array($enquiry->status, ['draft', 'responded', 'pending']))
                                    <a href="{{ route('vendor.enquiries.respond', $enquiry->id) }}"
                                       class="px-4 py-2 bg-green-600 text-white  text-xs hover:bg-green-700 font-semibold inline-block whitespace-nowrap">
                                        Send Counter Offer
                                    </a>
                                @else
                                    <button class="px-4 py-2 bg-gray-500 text-white  text-xs font-semibold cursor-not-allowed whitespace-nowrap">
                                        Send Reminder
                                    </button>
                                @endif
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-500">
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

    {{-- PAGINATION --}}
    <div class="mt-6 flex items-center justify-between text-sm text-gray-600">
        <div class="font-medium">
            Showing {{ $enquiries->firstItem() ?? 0 }} - {{ $enquiries->lastItem() ?? 0 }} of {{ $enquiries->total() }}
        </div>
        <div>
            {{ $enquiries->links() }}
        </div>
    </div>


</div>

@endsection
