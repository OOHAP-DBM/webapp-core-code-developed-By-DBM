@extends('layouts.vendor')

@section('page-title', 'Completed Campaigns')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white rounded-md shadow">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                Completed Campaigns
            </h4>
            <a href="{{ route('vendor.bookings.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1 border border-gray-300 bg-white/80 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-white transition">
                <i class="bi bi-arrow-left"></i> All Bookings
            </a>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex border-b border-gray-200 overflow-x-auto">
            <a href="{{ route('vendor.bookings.new') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-inbox"></i> New
            </a>
            <a href="{{ route('vendor.bookings.ongoing') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-play-circle"></i> Ongoing
            </a>
            <a href="{{ route('vendor.bookings.completed') }}" class="px-4 sm:px-6 py-3 text-sm font-semibold border-b-2 border-[#00A86B] text-[#00A86B] whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-check-circle"></i> Completed
                <span class="ml-1 bg-[#00A86B] text-white text-xs px-1.5 py-0.5 rounded-full">{{ $stats['total'] }}</span>
            </a>
            <a href="{{ route('vendor.bookings.cancelled') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-x-circle"></i> Cancelled
            </a>
        </div>

        <div class="p-4 sm:p-6 space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#d1fae5;color:#10b981;"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Total Completed</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#dbeafe;color:#3b82f6;"><i class="bi bi-file-earmark-check"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">With POD</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['with_pod'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#fef3c7;color:#f59e0b;"><i class="bi bi-file-earmark-x"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Without POD</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['without_pod'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#d1fae5;color:#059669;"><i class="bi bi-currency-rupee"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Total Revenue</div>
                        <div class="text-xl font-bold text-gray-800">&#8377;{{ number_format($stats['total_revenue'] / 100000, 2) }}L</div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('vendor.bookings.completed') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                    <div class="lg:col-span-3">
                        <input type="text" name="search" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="lg:col-span-2 relative">
                        <select name="pod_status" class="appearance-none w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none pr-8">
                            <option value="">All POD Status</option>
                            <option value="submitted" {{ request('pod_status') === 'submitted' ? 'selected' : '' }}>POD Submitted</option>
                            <option value="approved" {{ request('pod_status') === 'approved' ? 'selected' : '' }}>POD Approved</option>
                            <option value="missing" {{ request('pod_status') === 'missing' ? 'selected' : '' }}>POD Missing</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                    </div>
                    <div class="lg:col-span-2">
                        <input type="date" name="date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('date_from') }}">
                    </div>
                    <div class="lg:col-span-2">
                        <input type="date" name="date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" value="{{ request('date_to') }}">
                    </div>
                    <div class="lg:col-span-2 relative">
                        <select name="sort_by" class="appearance-none w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none pr-8">
                            <option value="end_date_desc" {{ request('sort_by') === 'end_date_desc' ? 'selected' : '' }}>Recently Completed</option>
                            <option value="end_date_asc" {{ request('sort_by') === 'end_date_asc' ? 'selected' : '' }}>Oldest First</option>
                            <option value="amount_desc" {{ request('sort_by') === 'amount_desc' ? 'selected' : '' }}>Highest Revenue</option>
                            <option value="amount_asc" {{ request('sort_by') === 'amount_asc' ? 'selected' : '' }}>Lowest Revenue</option>
                        </select>
                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                    </div>
                    <div class="lg:col-span-1">
                        <button type="submit" class="w-full bg-primary text-white px-4 py-2 rounded-lg text-sm hover:bg-primary/90 transition"><i class="bi bi-funnel"></i></button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            @if($bookings->count() > 0)
            <div class="overflow-x-auto rounded-lg shadow">
                <table class="min-w-full text-xs sm:text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Booking ID</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Customer</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Hoarding</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Campaign Period</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Completed</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Revenue</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">POD Status</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($bookings as $booking)
                        @php
                            $endDate = \Carbon\Carbon::parse($booking->end_date);
                            $daysAgo = $endDate->diffInDays(now());
                            $hasPod = $booking->pod_submitted_at !== null;
                            $podApproved = $booking->pod_approved_at !== null;
                            $podStatus = 'missing';
                            $podBadgeClass = 'bg-red-100 text-red-700';
                            $podIcon = 'file-earmark-x';
                            if ($podApproved) {
                                $podStatus = 'approved';
                                $podBadgeClass = 'bg-green-100 text-green-700';
                                $podIcon = 'file-earmark-check';
                            } elseif ($hasPod) {
                                $podStatus = 'submitted';
                                $podBadgeClass = 'bg-yellow-100 text-yellow-700';
                                $podIcon = 'file-earmark-arrow-up';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="font-semibold text-blue-600 hover:underline">#{{ $booking->id }}</a>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="font-medium text-gray-800">{{ $booking->customer->name }}</span><br>
                                <span class="text-xs text-gray-500">{{ $booking->customer->phone }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <span class="font-medium text-gray-800">{{ $booking->hoarding->name }}</span><br>
                                <span class="text-xs text-gray-500"><i class="bi bi-geo-alt"></i> {{ $booking->hoarding->location }}</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}<br>
                                <span class="text-xs text-gray-500">to {{ $endDate->format('d M Y') }}</span><br>
                                <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{{ $booking->duration_days }} days</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <i class="bi bi-calendar-check text-green-600"></i> {{ $endDate->format('d M Y') }}<br>
                                <span class="text-xs text-gray-500">{{ $daysAgo }} days ago</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap font-semibold text-green-600">
                                &#8377;{{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $podBadgeClass }}">
                                        <i class="bi bi-{{ $podIcon }} me-1"></i>{{ ucfirst($podStatus) }}
                                    </span>
                                    @if($podStatus === 'missing' && $daysAgo > 7)
                                        <i class="bi bi-exclamation-triangle text-red-500" title="POD overdue"></i>
                                    @endif
                                </div>
                                @if($hasPod && !$podApproved)
                                    <span class="text-xs text-gray-400 mt-1 block">Submitted {{ \Carbon\Carbon::parse($booking->pod_submitted_at)->diffForHumans() }}</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">View</a>
                                    @if($hasPod)
                                        <a href="{{ route('vendor.bookings.pod_review', $booking->id) }}" class="text-xs bg-cyan-500 text-white px-2 py-1 rounded hover:bg-cyan-600 transition">POD</a>
                                    @else
                                        <button type="button" class="text-xs bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition" onclick="alert('POD submission feature coming soon')">Upload POD</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div>{{ $bookings->links() }}</div>
            @else
            <div class="text-center py-10 text-gray-400">
                <i class="bi bi-check-circle" style="font-size:2.5rem;"></i>
                <h4 class="mt-3 text-base font-semibold text-gray-500">No Completed Campaigns</h4>
                <p class="text-sm">No completed campaigns yet.</p>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
