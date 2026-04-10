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
    <div class="bg-gray-50 " id="dashboardApp" x-data="{ openFilter: false, dateFilter: '{{ request('date_filter', 'all') }}' }">
            <!-- TITLE -->
            <h2 class="pl-5 md:pl-0 text-xl font-bold text-gray-700 mb-6">
                Dashboard
            </h2>
            <!-- STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- TOTAL HOARDINGS -->
                <a href="{{ route('customer.enquiries.index') }}" target="_blank" class="bg-[#F3F4F6] rounded ml-4 md:ml-0 p-4 flex items-start gap-3 w-full max-w-xs hover:shadow-lg transition cursor-pointer" title="View all enquiries">
                    <div class="w-10 h-10 rounded-full bg-[#E5E7EB] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#374151"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 leading-tight">Total Enquiry</p>
                        <p class="text-xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['total_enquiries'] ?? 0 }}</p>
                    </div>
                </a>
                <!-- CITIES -->
                <!-- <div class="bg-[#DCFCE7] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">
                    <div class="w-10 h-10 rounded-full bg-[#86EFAC] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#166534"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 leading-tight">Cities</p>
                        <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['cities'] ?? 0 }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">Cities with Hoardings</p>
                    </div>
                </div> -->
                <!-- ACTIVE VENDORS -->
                <!-- <div class="bg-[#DBEAFE] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">
                    <div class="w-10 h-10 rounded-full bg-[#93C5FD] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#374151"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 leading-tight">Active Vendors</p>
                        <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['active_vendors'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Vendors on Platform</p>
                    </div>
                </div> -->
                <!-- COMPLETED BOOKINGS -->
                <!-- <div class="bg-[#FECACA] rounded-xl p-4 flex items-start gap-3 w-full max-w-xs">
                    <div class="w-10 h-10 rounded-full bg-[#F87171] flex items-center justify-center flex-shrink-0">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 13V11H22V13H18ZM19.2 20L16 17.6L17.2 16L20.4 18.4L19.2 20ZM17.2 8L16 6.4L19.2 4L20.4 5.6L17.2 8ZM5 19V15H4C3.45 15 2.97933 14.8043 2.588 14.413C2.19667 14.0217 2.00067 13.5507 2 13V11C2 10.45 2.196 9.97933 2.588 9.588C2.98 9.19667 3.45067 9.00067 4 9H8L13 6V18L8 15H7V19H5ZM14 15.35V8.65C14.45 9.05 14.8127 9.53767 15.088 10.113C15.3633 10.6883 15.5007 11.3173 15.5 12C15.4993 12.6827 15.3617 13.312 15.087 13.888C14.8123 14.464 14.45 14.9513 14 15.35Z" fill="#374151"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 leading-tight">Completed Bookings</p>
                        <p class="text-2xl font-semibold text-gray-900 leading-snug mt-1">{{ $stats['bookings'] ?? 0 }}</p>
                        <p class="text-xs text-gray-700 mt-0.5">Total Completed</p>
                    </div>
                </div> -->
            </div>
            <!-- BOOKED STATISTICS -->
            <div class="bg-white rounded p-5 border border-gray-200 mb-6">

                {{-- Header Row: Title + Tabs + Navigator --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <h3 class="font-semibold text-gray-900">Booked Statistics</h3>

                    <div class="flex flex-wrap items-center gap-3">

                        {{-- TODAY / THIS WEEK / THIS MONTH Tabs --}}
                        @php $chartFilter = request('chart_filter', 'this_month'); @endphp

                        <div class="flex items-center gap-1 bg-gray-100 rounded-full p-1">
                            @foreach(['today' => 'Today', 'this_week' => 'This Week', 'this_month' => 'This Month'] as $val => $label)
                                <a href="{{ request()->fullUrlWithQuery(['chart_filter' => $val, 'chart_offset' => 0]) }}"
                                class="px-3 py-1 text-xs font-semibold rounded-full transition
                                    {{ $chartFilter === $val
                                        ? 'bg-white text-gray-900 shadow'
                                        : 'text-gray-500 hover:text-gray-700' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Date Range Navigator --}}
                        <div class="flex items-center gap-1">
                            <a href="{{ request()->fullUrlWithQuery(['chart_offset' => ($chartOffset ?? 0) - 1]) }}"
                            class="w-7 h-7 flex items-center justify-center rounded-full border border-green-300 bg-green-50 hover:bg-green-100 text-green-700 font-bold text-sm transition">
                                ‹
                            </a>
                            <span class="text-xs font-semibold text-green-700 border border-green-300 bg-green-50 px-3 py-1 rounded-full whitespace-nowrap">
                                {{ $chartRangeLabel ?? now()->format('M Y') }}
                            </span>
                            <a href="{{ request()->fullUrlWithQuery(['chart_offset' => ($chartOffset ?? 0) + 1]) }}"
                            class="w-7 h-7 flex items-center justify-center rounded-full border border-green-300 bg-green-50 hover:bg-green-100 text-green-700 font-bold text-sm transition">
                                ›
                            </a>
                        </div>

                    </div>
                </div>

                {{-- Chart or Empty State --}}
                @if($hasBookingStats ?? false)
                    <canvas id="bookingChart" height="90"></canvas>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg" class="mb-3 text-gray-400">
                            <path d="M3 3v18h18" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7 15v-4M11 15v-7M15 15v-2"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-600">No booking data available</p>
                        <p class="text-xs text-gray-400 mt-1">Your booking statistics will appear here</p>
                    </div>
                @endif
            </div>


            <!-- ENQUIRY TABLE (like Enquiry & Offers page) -->
            <div class="bg-white rounded p-5 border border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg text-gray-900">Recent Enquiries</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="GET" action="{{ route('customer.dashboard') }}" id="dashboard-enquiries-search-form" class="relative flex-1 md:w-72">
                            <input
                                type="text"
                                name="search"
                                id="dashboard-enquiries-search-input"
                                value="{{ request('search') }}"
                                placeholder="Search enquiry by enquiry ID..."
                                class="w-full px-4 py-2 pr-10 border border-gray-300 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-green-500"
                            >

                            @if(request()->filled('date_filter'))
                                <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                            @endif
                            @if(request()->filled('from_date'))
                                <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                            @endif
                            @if(request()->filled('to_date'))
                                <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                            @endif

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
                            class="px-4 py-2 border border-gray-300 bg-white text-gray-900 text-sm hover:bg-gray-100 font-medium cursor-pointer"
                            >
                            Filter
                        </button>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Sn</th>
                                <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Enquiry ID</th>
                                <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Requirement</th>
                                <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">No. of Locations</th>
                                <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Status</th>
                                <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($enquiries as $index => $enquiry)
                                <tr class="hover:bg-gray-50 transition-colors">
                                   <td class="px-4 py-4 text-gray-700">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <a href="{{ route('customer.enquiries.show', $enquiry->id) }}" class="text-green-600 font-semibold hover:text-green-700 hover:underline">
                                          {{ $enquiry->formatted_id }}
                                        </a>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $enquiry->created_at->format('d M, y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-gray-900 font-semibold">
                                            {{$enquiry->customer_note}}
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

                                            {{-- STATUS TEXT --}}
                                            @if($enquiry->status === 'submitted')

                                                <div class="flex flex-wrap items-center gap-x-1">
                                                    <span class="text-xs font-semibold text-gray-900 whitespace-nowrap">
                                                        Enquiry Sent:
                                                    </span>
                                                    <span class="text-xs font-semibold text-[var(--waiting)]">
                                                        Waiting for Vendor Response
                                                    </span>
                                                </div>

                                            @else

                                                {{-- OTHER STATUSES --}}
                                                <div class="text-xs font-semibold
                                                    @if($enquiry->status === 'responded')
                                                        text-orange-600
                                                    @elseif($enquiry->status === 'accepted')
                                                        text-green-600
                                                    @elseif($enquiry->status === 'rejected')
                                                        text-red-600
                                                    @else
                                                        text-gray-600
                                                    @endif
                                                ">
                                                    @if($enquiry->status === 'responded')
                                                        Offers Received
                                                    @elseif($enquiry->status === 'accepted')
                                                        Accepted
                                                    @elseif($enquiry->status === 'rejected')
                                                        Rejected
                                                    @else
                                                        {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                                                    @endif
                                                </div>

                                            @endif

                                            {{-- DATE --}}
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
                            @if($stats['total_enquiries'] > 5)
                            <tr>
                                <td colspan="6" class="py-4 text-center bg-gray-50">
                                    <a href="{{ route('customer.enquiries.index') }}"
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 hover:text-green-900 hover:underline transition">
                                        View More Enquiries
                                    </a>
                                </td>
                            </tr>
                            @endif

                        </tbody>
                    </table>
                </div>
            </div>
        <div
                x-show="openFilter"
                x-cloak
                x-transition
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 md:px-0"
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
                                class="text-gray-800 hover:text-black text-xl cursor-pointer"
                            >
                                ✕
                            </button>
                        </div>
                        <form method="GET" action="{{ route('customer.dashboard') }}" class="p-6 space-y-6">

                            @if(request()->filled('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

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
                                        value="{{ request('from_date') }}"
                                        class="px-3 py-2 border border-gray-300 text-sm w-full"
                                        placeholder="From"
                                    >
                                    <input
                                        type="date"
                                        name="to_date"
                                        value="{{ request('to_date') }}"
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
                                    class="px-6 py-2 bg-green-800 text-white text-sm font-semibold hover:bg-green-900 cursor-pointer"
                                >
                                    Apply Filter
                                </button>

                            </div>

                        </form>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Booking Chart - Line Style Modern
    @if($hasBookingStats ?? false)
        var ctx = document.getElementById('bookingChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($bookingStats['labels']),
                datasets: [{
                    label: 'Hoardings',
                    data: @json($bookingStats['data']),
                    borderColor: '#e11d48', // rose-600
                    backgroundColor: 'rgba(225,29,72,0.08)',
                    pointBackgroundColor: '#e11d48',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.4, // smooth curve
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return 'Hoardings: ' + context.parsed.y;
                            }
                        },
                        backgroundColor: '#111827',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#e11d48',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#334155', font: { weight: 'bold' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#64748b', stepSize: 1 }
                    }
                }
            }
        });
    @endif

    // Debounced search
    const form = document.getElementById('dashboard-enquiries-search-form');
    const input = document.getElementById('dashboard-enquiries-search-input');
    if (!form || !input) {
        return;
    }
    let debounceTimer;
    const ignoredKeys = ['Shift', 'Control', 'Alt', 'Meta', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Escape', 'Tab'];
    const submitSearch = function () {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    };
    input.addEventListener('keyup', function (event) {
        if (ignoredKeys.includes(event.key)) {
            return;
        }
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        if (event.key === 'Enter') {
            event.preventDefault();
            submitSearch();
            return;
        }
        debounceTimer = setTimeout(submitSearch, 450);
    });
});
</script>
@endpush
@endsection