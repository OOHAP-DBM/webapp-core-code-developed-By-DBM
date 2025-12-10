@extends('layouts.vendor')

@section('page-title', 'Ongoing Campaigns')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">Ongoing Campaigns</h2>
            <p class="text-muted mb-0">Active campaigns currently running</p>
        </div>
        <a href="{{ route('vendor.bookings.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>All Bookings
        </a>
    </div>
</div>

<!-- Category Navigation -->
<div class="mb-4">
    <div class="btn-group w-100" role="group">
        <a href="{{ route('vendor.bookings.new') }}" class="btn btn-outline-primary">
            <i class="bi bi-inbox me-2"></i>New
        </a>
        <a href="{{ route('vendor.bookings.ongoing') }}" class="btn btn-primary">
            <i class="bi bi-play-circle me-2"></i>Ongoing
            <span class="badge bg-white text-primary ms-2">{{ $stats['total'] }}</span>
        </a>
        <a href="{{ route('vendor.bookings.completed') }}" class="btn btn-outline-primary">
            <i class="bi bi-check-circle me-2"></i>Completed
        </a>
        <a href="{{ route('vendor.bookings.cancelled') }}" class="btn btn-outline-primary">
            <i class="bi bi-x-circle me-2"></i>Cancelled
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Ongoing</div>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                        <i class="bi bi-play-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Just Started</div>
                        <h3 class="mb-0">{{ $stats['just_started'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">
                        <i class="bi bi-sunrise"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Ending Soon</div>
                        <h3 class="mb-0">{{ $stats['ending_soon'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #fed7aa; color: #ea580c;">
                        <i class="bi bi-hourglass-bottom"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('vendor.bookings.ongoing') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="progress" class="form-select">
                    <option value="">All Progress</option>
                    <option value="just_started" {{ request('progress') === 'just_started' ? 'selected' : '' }}>Just Started</option>
                    <option value="mid_campaign" {{ request('progress') === 'mid_campaign' ? 'selected' : '' }}>Mid Campaign</option>
                    <option value="ending_soon" {{ request('progress') === 'ending_soon' ? 'selected' : '' }}>Ending Soon</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <select name="sort_by" class="form-select">
                    <option value="start_date_desc" {{ request('sort_by') === 'start_date_desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="start_date_asc" {{ request('sort_by') === 'start_date_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="end_date_asc" {{ request('sort_by') === 'end_date_asc' ? 'selected' : '' }}>Ending Soonest</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($bookings->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Hoarding</th>
                        <th>Campaign Period</th>
                        <th>Progress</th>
                        <th>Current Stage</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                    <tr>
                        <td>
                            <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="fw-bold text-decoration-none">
                                #{{ $booking->id }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                        {{ substr($booking->customer->name, 0, 1) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $booking->customer->name }}</div>
                                    <small class="text-muted">{{ $booking->customer->phone }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div class="fw-medium">{{ $booking->hoarding->name }}</div>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> {{ $booking->hoarding->location }}
                                </small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <div><i class="bi bi-calendar-check text-success"></i> {{ $start->format('d M Y') }}</div>
                                <div><i class="bi bi-calendar-x text-danger"></i> {{ $end->format('d M Y') }}</div>
                            </div>
                            <small class="badge bg-light text-dark mt-1">{{ $remainingDays }} days left</small>
                        </td>
                        <td>
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
                        <td>
                            @if($latestEvent)
                                <div>
                                    <span class="badge bg-{{ $latestEvent->stage === 15 ? 'success' : 'primary' }}">
                                        Stage {{ $latestEvent->stage }}
                                    </span>
                                    <div class="small text-muted mt-1">{{ $latestEvent->stage_name }}</div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold">₹{{ number_format($booking->total_amount, 2) }}</span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-info" title="View Timeline" data-bs-toggle="modal" data-bs-target="#timelineModal{{ $booking->id }}">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-3 border-top">
            {{ $bookings->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-play-circle display-1 text-muted"></i>
            <h4 class="mt-3">No Ongoing Campaigns</h4>
            <p class="text-muted">No active campaigns at the moment.</p>
        </div>
        @endif
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
