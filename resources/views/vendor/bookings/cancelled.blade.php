@extends('layouts.vendor')

@section('page-title', 'Cancelled Bookings')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">Cancelled Bookings</h2>
            <p class="text-muted mb-0">Cancelled bookings and refund tracking</p>
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
        <a href="{{ route('vendor.bookings.completed') }}" class="btn btn-outline-primary">
            <i class="bi bi-check-circle me-2"></i>Completed
        </a>
        <a href="{{ route('vendor.bookings.cancelled') }}" class="btn btn-primary">
            <i class="bi bi-x-circle me-2"></i>Cancelled
            <span class="badge bg-white text-primary ms-2">{{ $stats['total'] }}</span>
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
                        <div class="text-muted small mb-1">Total Cancelled</div>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                        <i class="bi bi-x-circle"></i>
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
                        <div class="text-muted small mb-1">Cancelled Only</div>
                        <h3 class="mb-0">{{ $stats['cancelled_only'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-ban"></i>
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
                        <div class="text-muted small mb-1">Refunded</div>
                        <h3 class="mb-0">{{ $stats['refunded'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">
                        <i class="bi bi-arrow-counterclockwise"></i>
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
                        <div class="text-muted small mb-1">Lost Revenue</div>
                        <h3 class="mb-0">₹{{ number_format($stats['lost_revenue'] / 100000, 2) }}L</h3>
                    </div>
                    <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
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
        <form method="GET" action="{{ route('vendor.bookings.cancelled') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="cancellation_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="cancelled" {{ request('cancellation_type') === 'cancelled' ? 'selected' : '' }}>Cancelled Only</option>
                    <option value="refunded" {{ request('cancellation_type') === 'refunded' ? 'selected' : '' }}>Refunded</option>
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
                    <option value="cancelled_at_desc" {{ request('sort_by') === 'cancelled_at_desc' ? 'selected' : '' }}>Recently Cancelled</option>
                    <option value="cancelled_at_asc" {{ request('sort_by') === 'cancelled_at_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="amount_desc" {{ request('sort_by') === 'amount_desc' ? 'selected' : '' }}>Highest Amount</option>
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
                        <th>Booking Period</th>
                        <th>Cancelled Date</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    @php
                        $isRefunded = $booking->status === 'refunded';
                        $cancelledAt = $booking->cancelled_at ? \Carbon\Carbon::parse($booking->cancelled_at) : null;
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
                                <small class="text-muted">to {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</small>
                            </div>
                            <small class="badge bg-light text-dark">{{ $booking->duration_days }} days</small>
                        </td>
                        <td>
                            @if($cancelledAt)
                                <div>
                                    <i class="bi bi-calendar-x text-danger"></i>
                                    {{ $cancelledAt->format('d M Y') }}
                                </div>
                                <small class="text-muted">{{ $cancelledAt->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($isRefunded)
                                <span class="badge bg-info">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Refunded
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Cancelled
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-bold text-danger">₹{{ number_format($booking->total_amount, 2) }}</span>
                            @if($isRefunded)
                                <br><small class="text-muted">Refunded</small>
                            @endif
                        </td>
                        <td>
                            @if($booking->cancellation_reason)
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $booking->cancellation_reason }}">
                                    <i class="bi bi-chat-left-text text-muted me-1"></i>
                                    {{ Str::limit($booking->cancellation_reason, 50) }}
                                </div>
                            @else
                                <span class="text-muted">No reason provided</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
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
            <i class="bi bi-x-circle display-1 text-muted"></i>
            <h4 class="mt-3">No Cancelled Bookings</h4>
            <p class="text-muted">No cancelled bookings found.</p>
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
