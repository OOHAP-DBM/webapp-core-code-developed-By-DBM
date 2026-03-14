@extends('layouts.vendor')

@section('page-title', 'Ongoing Campaigns')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white rounded-md shadow">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                Ongoing Campaigns
            </h4>
            <a href="{{ route('vendor.bookings.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center border border-gray-300 bg-white/80 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-white transition">
                ← All Bookings
            </a>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex border-b border-gray-200 overflow-x-auto">
            <a href="{{ route('vendor.bookings.new') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-inbox"></i> New
            </a>
            <a href="{{ route('vendor.bookings.ongoing') }}" class="px-4 sm:px-6 py-3 text-sm font-semibold border-b-2 border-[#00A86B] text-[#00A86B] whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-play-circle"></i> Ongoing
                <span class="ml-1 bg-[#00A86B] text-white text-xs px-1.5 py-0.5 rounded-full">{{ $stats['total'] }}</span>
            </a>
            <a href="{{ route('vendor.bookings.completed') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-check-circle"></i> Completed
            </a>
            <a href="{{ route('vendor.bookings.cancelled') }}" class="px-4 sm:px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 whitespace-nowrap flex items-center gap-1">
                <i class="bi bi-x-circle"></i> Cancelled
            </a>
        </div>

        <div class="p-4 sm:p-6 space-y-6">

            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#d1fae5;color:#10b981;"><i class="bi bi-play-circle"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Total Ongoing</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#dbeafe;color:#3b82f6;"><i class="bi bi-sunrise"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Just Started</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['just_started'] }}</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#fed7aa;color:#ea580c;"><i class="bi bi-hourglass-bottom"></i></div>
                    <div>
                        <div class="text-xs text-gray-500">Ending Soon</div>
                        <div class="text-xl font-bold text-gray-800">{{ $stats['ending_soon'] }}</div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('vendor.bookings.ongoing') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                    <div class="lg:col-span-3">
                        <input type="text" name="search" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="lg:col-span-2 relative">
                        <select name="progress" class="appearance-none w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-primary focus:outline-none pr-8">
                            <option value="">All Progress</option>
                            <option value="just_started" {{ request('progress') === 'just_started' ? 'selected' : '' }}>Just Started</option>
                            <option value="mid_campaign" {{ request('progress') === 'mid_campaign' ? 'selected' : '' }}>Mid Campaign</option>
                            <option value="ending_soon" {{ request('progress') === 'ending_soon' ? 'selected' : '' }}>Ending Soon</option>
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
                            <option value="start_date_desc" {{ request('sort_by') === 'start_date_desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="start_date_asc" {{ request('sort_by') === 'start_date_asc' ? 'selected' : '' }}>Oldest First</option>
                            <option value="end_date_asc" {{ request('sort_by') === 'end_date_asc' ? 'selected' : '' }}>Ending Soonest</option>
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
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Progress</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Current Stage</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Amount</th>
                            <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($bookings as $booking)
                        @php
                            $now = now();
                            $start = \Carbon\Carbon::parse($booking->start_date);
                            $end = \Carbon\Carbon::parse($booking->end_date);
                            $totalDays = $start->diffInDays($end);
                            $elapsedDays = $start->diffInDays($now);
                            $progressPercent = $totalDays > 0 ? min(100, round(($elapsedDays / $totalDays) * 100)) : 0;
                            $remainingDays = $now->diffInDays($end);
                            
                            // Determine progress category
                            $progressCategory = 'mid_campaign';
                            $progressColor = 'primary';
                            if ($elapsedDays <= 7) {
                                $progressCategory = 'just_started';
                                $progressColor = 'info';
                            } elseif ($remainingDays <= 7) {
                                $progressCategory = 'ending_soon';
                                $progressColor = 'warning';
                            }
                            
                            // Get latest timeline event
                            $latestEvent = $booking->timelineEvents()->latest()->first();
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
                                <div class="text-xs"><i class="bi bi-calendar-check text-green-600"></i> {{ $start->format('d M Y') }}</div>
                                <div class="text-xs"><i class="bi bi-calendar-x text-red-500"></i> {{ $end->format('d M Y') }}</div>
                                <span class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded mt-1 inline-block">{{ $remainingDays }} days left</span>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div style="width: 120px;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="fw-bold text-{{ $progressColor }}">{{ $progressPercent }}%</small>
                                        <small class="text-muted">Day {{ $elapsedDays }}/{{ $totalDays }}</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <small class="badge bg-{{ $progressColor }}-subtle text-{{ $progressColor }} mt-1">
                                        {{ ucfirst(str_replace('_', ' ', $progressCategory)) }}
                                    </small>
                                </div>
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                @if($latestEvent)
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $latestEvent->stage === 15 ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        Stage {{ $latestEvent->stage }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">{{ $latestEvent->stage_name }}</div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap font-semibold text-gray-800">
                                ₹{{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">View</a>
                                    <button type="button" class="text-xs bg-cyan-500 text-white px-2 py-1 rounded hover:bg-cyan-600 transition" title="View Timeline" data-bs-toggle="modal" data-bs-target="#timelineModal{{ $booking->id }}">Timeline</button>
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
                <i class="bi bi-play-circle" style="font-size:2.5rem;"></i>
                <h4 class="mt-3 text-base font-semibold text-gray-500">No Ongoing Campaigns</h4>
                <p class="text-sm">No active campaigns at the moment.</p>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .avatar-sm {
        width: 32px;
        height: 32px;
    }
    .avatar-title {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .progress {
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-bar {
        transition: width 0.3s ease;
    }
</style>
@endpush
