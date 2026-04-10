@php
    $user = auth()->user();
    $layout = ($user && $user->hasRole('admin')) ? 'layouts.admin' : 'layouts.vendor';
@endphp
@extends($layout)
@section('title', 'All Enquiries')
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
                <div class="flex items-center gap-2">
                    <!-- Mobile Back Button -->
                    <button onclick="window.history.back()" type="button" class="md:hidden inline-flex items-center justify-center rounded-full text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 ml-[-5px]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 mb-0">Enquiry & Offers</h4>
                </div>
                <p class="text-sm text-gray-700 mt-2">Check all your sent offers to customers, track and manage them here.</p>
            </div>
            <div class="flex items-center gap-2 w-full lg:w-auto">
                <form method="GET" action="{{ route('vendor.enquiries.index') }}" class="relative flex-1 sm:w-80 lg:w-96">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search enquiry by enquiry ID..."
                        class="w-full border border-gray-300  px-3 py-2 pr-9 text-sm focus:ring-1 focus:ring-primary focus:outline-none sm:w-80 lg:w-96"
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
                    class="border border-gray-300 bg-white text-gray-800 px-4 py-2  text-sm font-medium hover:bg-gray-50 transition whitespace-nowrap"
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
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Offer ID</th>

                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Customer Name</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap text-center">No. of Hoardings</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">No. of Locations</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Offer Valid Till</th>

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

                                <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                    <a href="{{ route('vendor.enquiries.show', $enquiry->id) }}" class="text-green-600 font-semibold hover:text-green-700 hover:underline">
                                        {{ $enquiry->formatted_id }}
                                    </a>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $enquiry->created_at->format('d M, y') }}
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
                                            <div class="relative flex items-center gap-2" x-data="{ openMenu: false }">
                                                <a href="{{ route('vendor.enquiries.show', $enquiry->id) }}"
                                                   class="inline-flex items-center justify-center px-3 py-1.5 rounded text-xs font-semibold text-white hover:bg-blue-600 transition whitespace-nowrap"
                                                   style="background-color: var(--booking-btn-color);">
                                                    View Enquiry
                                                </a>
                                                <!-- Three Dots Button -->
                                                <button type="button" @click="openMenu = true" class="ml-1 p-1 rounded-full hover:bg-gray-200 focus:outline-none">
                                                    <!-- Vertical three dots icon -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                                                        <circle cx="12" cy="5" r="1.5"/>
                                                        <circle cx="12" cy="12" r="1.5"/>
                                                        <circle cx="12" cy="19" r="1.5"/>
                                                    </svg>
                                                </button>
                                                <!-- Modal -->
                                                <div x-show="openMenu" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                                                    <div @click.away="openMenu = false" class="bg-white rounded shadow-lg p-6 min-w-[260px] flex flex-col gap-4 relative">
                                                        <button @click="openMenu = false" class="absolute top-2 right-2 text-gray-400 hover:text-black">✕</button>
                                                        <a href="{{ route('vendor.offers.create', ['enquiry_id' => $enquiry->id]) }}"
                                                           class="flex items-center gap-2 text-gray-800 hover:text-green-700 font-medium px-2 py-1"
                                                        >
                                                           <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                             <path d="M8.25521 11.9193H13.7552C14.0149 11.9193 14.2328 11.8313 14.4088 11.6553C14.5848 11.4793 14.6725 11.2617 14.6719 11.0026C14.6713 10.7435 14.5833 10.5259 14.4079 10.3499C14.2325 10.1739 14.0149 10.0859 13.7552 10.0859H8.25521C7.99549 10.0859 7.77793 10.1739 7.60254 10.3499C7.42715 10.5259 7.33915 10.7435 7.33854 11.0026C7.33793 11.2617 7.42593 11.4796 7.60254 11.6562C7.77915 11.8328 7.99671 11.9205 8.25521 11.9193ZM8.25521 14.6693H13.7552C14.0149 14.6693 14.2328 14.5813 14.4088 14.4053C14.5848 14.2293 14.6725 14.0117 14.6719 13.7526C14.6713 13.4935 14.5833 13.2759 14.4079 13.0999C14.2325 12.9239 14.0149 12.8359 13.7552 12.8359H8.25521C7.99549 12.8359 7.77793 12.9239 7.60254 13.0999C7.42715 13.2759 7.33915 13.4935 7.33854 13.7526C7.33793 14.0117 7.42593 14.2296 7.60254 14.4062C7.77915 14.5828 7.99671 14.6705 8.25521 14.6693ZM8.25521 17.4193H11.0052C11.2649 17.4193 11.4828 17.3313 11.6588 17.1553C11.8348 16.9793 11.9225 16.7617 11.9219 16.5026C11.9213 16.2435 11.8333 16.0259 11.6579 15.8499C11.4825 15.6739 11.2649 15.5859 11.0052 15.5859H8.25521C7.99549 15.5859 7.77793 15.6739 7.60254 15.8499C7.42715 16.0259 7.33915 16.2435 7.33854 16.5026C7.33793 16.7617 7.42593 16.9796 7.60254 17.1562C7.77915 17.3328 7.99671 17.4205 8.25521 17.4193ZM5.50521 20.1693C5.00104 20.1693 4.5696 19.9899 4.21087 19.6312C3.85215 19.2725 3.67249 18.8407 3.67188 18.3359V3.66927C3.67188 3.1651 3.85154 2.73366 4.21087 2.37494C4.57021 2.01622 5.00165 1.83655 5.50521 1.83594H12.0823C12.3267 1.83594 12.5599 1.88177 12.7817 1.97344C13.0035 2.0651 13.1982 2.19497 13.3656 2.36302L17.8115 6.80885C17.9795 6.97691 18.1094 7.17185 18.201 7.39369C18.2927 7.61552 18.3385 7.84835 18.3385 8.09219V18.3359C18.3385 18.8401 18.1592 19.2719 17.8005 19.6312C17.4417 19.9905 17.01 20.1699 16.5052 20.1693H5.50521ZM16.5052 8.2526H13.2969C12.9149 8.2526 12.5904 8.11908 12.3234 7.85202C12.0563 7.58496 11.9225 7.26016 11.9219 6.8776V3.66927H5.50521V18.3359H16.5052V8.2526Z" fill="#727272"/>
                                                            </svg>

                                                            Create Offer
                                                        </a>
                                                        <button class="flex items-center gap-2 text-gray-800 hover:text-blue-700 font-medium px-2 py-1" disabled>
                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                  <path d="M13 12H10C9.86739 12 9.74021 12.0527 9.64645 12.1464C9.55268 12.2402 9.5 12.3674 9.5 12.5C9.5 12.6326 9.55268 12.7598 9.64645 12.8536C9.74021 12.9473 9.86739 13 10 13H13C13.1326 13 13.2598 12.9473 13.3536 12.8536C13.4473 12.7598 13.5 12.6326 13.5 12.5C13.5 12.3674 13.4473 12.2402 13.3536 12.1464C13.2598 12.0527 13.1326 12 13 12ZM20 5H3C2.73478 5 2.48043 5.10536 2.29289 5.29289C2.10536 5.48043 2 5.73478 2 6C2 6.26522 2.10536 6.51957 2.29289 6.70711C2.48043 6.89464 2.73478 7 3 7H20C20.2652 7 20.5196 6.89464 20.7071 6.70711C20.8946 6.51957 21 6.26522 21 6C21 5.73478 20.8946 5.48043 20.7071 5.29289C20.5196 5.10536 20.2652 5 20 5ZM18 8H5C4.73478 8 4.48043 8.10536 4.29289 8.29289C4.10536 8.48043 4 8.73478 4 9V17C4 18.654 5.346 20 7 20H16C17.654 20 19 18.654 19 17V9C19 8.73478 18.8946 8.48043 18.7071 8.29289C18.5196 8.10536 18.2652 8 18 8ZM16 18H7C6.448 18 6 17.551 6 17V10H17V17C17 17.551 16.552 18 16 18Z" fill="#727272"/>
                                                            </svg>


                                                            Archive this Offer
                                                        </button>
                                                        <button class="flex items-center gap-2 text-red-600 hover:text-red-800 font-medium px-2 py-1" disabled>
                                                           <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8.4 17L12 13.4L15.6 17L17 15.6L13.4 12L17 8.4L15.6 7L12 10.6L8.4 7L7 8.4L10.6 12L7 15.6L8.4 17ZM12 22C10.6167 22 9.31667 21.7373 8.1 21.212C6.88334 20.6867 5.825 19.9743 4.925 19.075C4.025 18.1757 3.31267 17.1173 2.788 15.9C2.26333 14.6827 2.00067 13.3827 2 12C1.99933 10.6173 2.262 9.31733 2.788 8.1C3.314 6.88267 4.02633 5.82433 4.925 4.925C5.82367 4.02567 6.882 3.31333 8.1 2.788C9.318 2.26267 10.618 2 12 2C13.382 2 14.682 2.26267 15.9 2.788C17.118 3.31333 18.1763 4.02567 19.075 4.925C19.9737 5.82433 20.6863 6.88267 21.213 8.1C21.7397 9.31733 22.002 10.6173 22 12C21.998 13.3827 21.7353 14.6827 21.212 15.9C20.6887 17.1173 19.9763 18.1757 19.075 19.075C18.1737 19.9743 17.1153 20.687 15.9 21.213C14.6847 21.739 13.3847 22.0013 12 22ZM12 20C14.2333 20 16.125 19.225 17.675 17.675C19.225 16.125 20 14.2333 20 12C20 9.76667 19.225 7.875 17.675 6.325C16.125 4.775 14.2333 4 12 4C9.76667 4 7.875 4.775 6.325 6.325C4.775 7.875 4 9.76667 4 12C4 14.2333 4.775 16.125 6.325 17.675C7.875 19.225 9.76667 20 12 20Z" fill="#E75858"/>
                                                            </svg>

                                                            Reject Enquiry
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
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
                    {{ $enquiries->links('pagination.vendor-compact') }}
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER MODAL --}}
    <div x-show="openFilter === true" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-5">
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
