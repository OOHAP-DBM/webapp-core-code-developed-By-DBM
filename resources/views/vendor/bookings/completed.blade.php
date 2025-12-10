@extends('layouts.vendor')

@section('page-title', 'Completed Campaigns')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">Completed Campaigns</h2>
            <p class="text-muted mb-0">Successfully completed campaigns and POD tracking</p>
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
        <a href="{{ route('vendor.bookings.ongoing') }}" class="btn btn-outline-primary">
            <i class="bi bi-play-circle me-2"></i>Ongoing
        </a>
        <a href="{{ route('vendor.bookings.completed') }}" class="btn btn-primary">
            <i class="bi bi-check-circle me-2"></i>Completed
            <span class="badge bg-white text-primary ms-2">{{ $stats['total'] }}</span>
        </a>
        <a href="{{ route('vendor.bookings.cancelled') }}" class="btn btn-outline-primary">
            <i class="bi bi-x-circle me-2"></i>Cancelled
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Completed</div>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">With POD</div>
                        <h3 class="mb-0">{{ $stats['with_pod'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Without POD</div>
                        <h3 class="mb-0">{{ $stats['without_pod'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-file-earmark-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Revenue</div>
                        <h3 class="mb-0">₹{{ number_format($stats['total_revenue'] / 100000, 2) }}L</h3>
                    </div>
                    <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('vendor.bookings.completed') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="pod_status" class="form-select">
                    <option value="">All POD Status</option>
                    <option value="submitted" {{ request('pod_status') === 'submitted' ? 'selected' : '' }}>POD Submitted</option>
                    <option value="approved" {{ request('pod_status') === 'approved' ? 'selected' : '' }}>POD Approved</option>
                    <option value="missing" {{ request('pod_status') === 'missing' ? 'selected' : '' }}>POD Missing</option>
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
                    <option value="end_date_desc" {{ request('sort_by') === 'end_date_desc' ? 'selected' : '' }}>Recently Completed</option>
                    <option value="end_date_asc" {{ request('sort_by') === 'end_date_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="amount_desc" {{ request('sort_by') === 'amount_desc' ? 'selected' : '' }}>Highest Revenue</option>
                    <option value="amount_asc" {{ request('sort_by') === 'amount_asc' ? 'selected' : '' }}>Lowest Revenue</option>
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
                        <th>Completed</th>
                        <th>Revenue</th>
                        <th>POD Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    @php
                        $endDate = \Carbon\Carbon::parse($booking->end_date);
                        $daysAgo = $endDate->diffInDays(now());
                        
                        // POD status logic
                        $hasPod = $booking->pod_submitted_at !== null;
                        $podApproved = $booking->pod_approved_at !== null;
                        $podStatus = 'missing';
                        $podBadgeClass = 'danger';
                        $podIcon = 'file-earmark-x';
                        
                        if ($podApproved) {
                            $podStatus = 'approved';
                            $podBadgeClass = 'success';
                            $podIcon = 'file-earmark-check';
                        } elseif ($hasPod) {
                            $podStatus = 'submitted';
                            $podBadgeClass = 'warning';
                            $podIcon = 'file-earmark-arrow-up';
                        }
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
                                <div>{{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</div>
                                <small class="text-muted">to {{ $endDate->format('d M Y') }}</small>
                            </div>
                            <small class="badge bg-light text-dark">{{ $booking->duration_days }} days</small>
                        </td>
                        <td>
                            <div>
                                <i class="bi bi-calendar-check text-success"></i>
                                {{ $endDate->format('d M Y') }}
                            </div>
                            <small class="text-muted">{{ $daysAgo }} days ago</small>
                        </td>
                        <td>
                            <span class="fw-bold text-success">₹{{ number_format($booking->total_amount, 2) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $podBadgeClass }}">
                                    <i class="bi bi-{{ $podIcon }} me-1"></i>
                                    {{ ucfirst($podStatus) }}
                                </span>
                                @if($podStatus === 'missing' && $daysAgo > 7)
                                    <i class="bi bi-exclamation-triangle text-danger" title="POD overdue"></i>
                                @endif
                            </div>
                            @if($hasPod && !$podApproved)
                                <small class="text-muted d-block mt-1">
                                    Submitted {{ \Carbon\Carbon::parse($booking->pod_submitted_at)->diffForHumans() }}
                                </small>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($hasPod)
                                    <a href="{{ route('vendor.bookings.pod_review', $booking->id) }}" class="btn btn-sm btn-outline-info" title="Review POD">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-warning" title="Submit POD" onclick="alert('POD submission feature coming soon')">
                                        <i class="bi bi-upload"></i>
                                    </button>
                                @endif
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
            <i class="bi bi-check-circle display-1 text-muted"></i>
            <h4 class="mt-3">No Completed Campaigns</h4>
            <p class="text-muted">No completed campaigns yet.</p>
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
</style>
@endpush
