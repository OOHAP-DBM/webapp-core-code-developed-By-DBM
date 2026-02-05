@extends('layouts.customer')

@section('title', 'Dashboard')

@push('styles')
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            color: black;
            border-radius: 20px;
            margin-bottom: 40px;
        }
        
        .search-box-main {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .location-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 16px;
        }
        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .stats-card .label {
            font-size: 14px;
            color: #64748b;
        }
        
        .category-chip {
            display: inline-flex;
            align-items: center;
            padding: 12px 20px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-weight: 500;
            color: #334155;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .category-chip:hover {
            border-color: #667eea;
            background: #f8fafc;
            color: #667eea;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
        }
    </style>
@endpush
@section('content')
    <div class="p-6 bg-gray-50 " id="dashboardApp" x-data="{ openFilter: false, dateFilter: '{{ request('date_filter', 'all') }}' }">
            <!-- TITLE -->
            <h2 class="text-xl font-bold text-gray-700 mb-6">
                Dashboard
            </h2>
            <!-- STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- TOTAL HOARDINGS -->
                <div class="bg-[#F3F4F6] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">
                    <div class="w-10 h-10 rounded-full bg-[#E5E7EB] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#374151"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 leading-tight">Total Hoardings</p>
                        <p class="text-xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['total_hoardings'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Approved Hoardings</p>
                    </div>
                </div>
                <!-- CITIES -->
                <div class="bg-[#DCFCE7] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">
                    <div class="w-10 h-10 rounded-full bg-[#86EFAC] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#166534"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 leading-tight">Cities</p>
                        <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['cities'] ?? 0 }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">Cities with Hoardings</p>
                    </div>
                </div>
                <!-- ACTIVE VENDORS -->
                <div class="bg-[#DBEAFE] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">
                    <div class="w-10 h-10 rounded-full bg-[#93C5FD] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#374151"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 leading-tight">Active Vendors</p>
                        <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['active_vendors'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Vendors on Platform</p>
                    </div>
                </div>
                <!-- COMPLETED BOOKINGS -->
                <div class="bg-[#FECACA] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">
                    <div class="w-10 h-10 rounded-full bg-[#F87171] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#374151"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 leading-tight">Completed Bookings</p>
                        <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['bookings'] ?? 0 }}</p>
                        <p class="text-xs text-gray-700 mt-0.5">Total Completed</p>
                    </div>
                </div>
            </div>
            <!-- BOOKED STATISTICS -->
            <div class="bg-white rounded-xl p-5 shadow mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold">Booked Statistics</h3>
                    <!-- <span class="text-xs text-gray-500">9–15 Sep, 2024</span> -->
                </div>

                @if($hasBookingStats ?? false)
                    {{-- Chart --}}
                    <canvas id="bookingChart" height="90"></canvas>
                @else
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <!-- SVG -->
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            class="mb-3 text-gray-400">
                            <path d="M3 3v18h18" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7 15v-4M11 15v-7M15 15v-2"
                                stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round"/>
                        </svg>

                        <p class="text-sm font-medium text-gray-600">
                            No booking data available
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Your booking statistics will appear here
                        </p>
                    </div>
                @endif
            </div>


            <!-- ENQUIRY TABLE (like Enquiry & Offers page) -->
            <div class="bg-white rounded-xl p-5 shadow">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg text-gray-900">All Enquiries</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="GET" class="relative flex-1 md:w-72">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search enquiry by enquiry ID..."
                                class="w-full px-4 py-2 pr-10 border border-gray-300 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-green-500"
                            >

                            {{-- Search Icon --}}
                            <span class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                        </form>
                        <button
                            type="button"
                            @click="openFilter = true"
                            class="px-4 py-2 border border-gray-300 bg-white text-gray-900 text-sm hover:bg-gray-100 font-medium"
                            >
                            Filter
                        </button>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 overflow-x-auto shadow-sm">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Sn #</th>
                                <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Enquiry ID</th>
                                <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs"># of Vendors</th>
                                <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs"># of Locations</th>
                                <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Status</th>
                                <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($enquiries as $index => $enquiry)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-4 text-gray-700">
                                        {{ ($enquiries->currentPage() - 1) * $enquiries->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('customer.enquiries.show', $enquiry->id) }}" class="text-green-600 font-semibold hover:text-green-700 hover:underline">
                                            {{ 'ENQ' . str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $enquiry->created_at->format('d M, y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @php
                                            $vendorCount = $enquiry->items->map(function($item) {
                                                return optional($item->hoarding)->vendor_id;
                                            })->filter()->unique()->count();
                                        @endphp
                                        <span class="text-gray-900 font-semibold">
                                            {{ $vendorCount }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @php
                                            $locationCount = $enquiry->items->flatMap(function($item) {
                                                $hoarding = optional($item->hoarding);
                                                $locatedAt = $hoarding->located_at ?? [];
                                                return is_array($locatedAt) ? $locatedAt : [];
                                            })->unique()->count();
                                        @endphp
                                        <span class="text-gray-900 font-semibold">
                                            {{ $locationCount }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="space-y-1">
                                            <div class="text-xs font-semibold
                                                @if($enquiry->status === 'submitted')
                                                    text-blue-600
                                                @elseif($enquiry->status === 'responded')
                                                    text-orange-600
                                                @elseif($enquiry->status === 'accepted')
                                                    text-green-600
                                                @elseif($enquiry->status === 'rejected')
                                                    text-red-600
                                                @else
                                                    text-gray-600
                                                @endif
                                            ">
                                                @if($enquiry->status === 'submitted')
                                                    Waiting for Vendor Response
                                                @elseif($enquiry->status === 'responded')
                                                    Offers Received
                                                @elseif($enquiry->status === 'accepted')
                                                    Accepted
                                                @elseif($enquiry->status === 'rejected')
                                                    Rejected
                                                @else
                                                    {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $enquiry->updated_at->format('d M, y | H:i') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex gap-2 justify-center flex-wrap">
                                            <a href="{{ route('customer.enquiries.show', $enquiry->id) }}"
                                               class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white text-xs  font-semibold inline-block whitespace-nowrap transition-colors">
                                                View Details
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                        <div class="space-y-2">
                                            <p class="font-medium">No enquiries found</p>
                                            <p class="text-xs">You haven't made any enquiries yet</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6 flex items-center justify-between text-sm text-gray-600">
                    <div class="font-medium">
                        Showing {{ $enquiries->firstItem() ?? 0 }} - {{ $enquiries->lastItem() ?? 0 }} of {{ $enquiries->total() }}
                    </div>
                    <div>
                        {{ $enquiries->links() }}
                    </div>
                </div>
            </div>
        <div
                x-show="openFilter"
                x-cloak
                x-transition
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                >


                {{-- Modal Box --}}
                <div
                    @click.away="openFilter = false"
                    class="bg-white w-full max-w-2xl rounded shadow-lg relative"
                    >
                        <div class="flex items-center justify-between h-10 bg-green-100 px-4 rounded-t">
                            <span></span>
                            <button
                                @click="openFilter = false"
                                class="text-gray-800 hover:text-black text-xl"
                            >
                                ✕
                            </button>
                        </div>
                        <form method="GET" class="p-6 space-y-6">

                            <h2 class="inline-block text-lg font-semibold text-gray-900 border-b border-gray-700 pb-1">
                                Filter
                            </h2>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800 mb-3">
                                    Created Enquiry by date
                                </h3>

                                <div class="flex flex-wrap items-center gap-6 text-sm text-gray-700">

                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="date_filter" value="all" x-model="dateFilter">
                                        All
                                    </label>

                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="date_filter" value="last_week" x-model="dateFilter">
                                        Last week
                                    </label>

                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="date_filter" value="last_month"x-model="dateFilter">
                                        Last month
                                    </label>

                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="date_filter" value="last_year" x-model="dateFilter">
                                        Last year
                                    </label>

                                    <label class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="date_filter"
                                            value="custom"
                                            x-model="dateFilter"
                                        >
                                        Custom Date
                                    </label>

                                </div>

                                {{-- Custom Date --}}
                                <div
                                    x-show="dateFilter === 'custom'"
                                    x-transition
                                    class="mt-4 flex gap-4"
                                    >
                                    <input
                                        type="date"
                                        name="from_date"
                                        class="px-3 py-2 border border-gray-300 text-sm w-full"
                                        placeholder="From"
                                    >
                                    <input
                                        type="date"
                                        name="to_date"
                                        class="px-3 py-2 border border-gray-300 text-sm w-full"
                                        placeholder="To"
                                    >
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="flex items-center justify-end gap-6 pt-4">

                                <a href="{{route('customer.dashboard')}}"
                                class="text-sm text-black font-semibold hover:underline">
                                    Reset
                                </a>

                                <button
                                    type="submit"
                                    class="px-6 py-2 bg-green-800 text-white text-sm font-semibold hover:bg-green-900"
                                >
                                    Apply Filter
                                </button>

                            </div>

                        </form>
            </div>
        </div>
    </div>
@endsection
<!-- No scripts needed: all data is now rendered server-side. -->
