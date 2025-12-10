@extends('layouts.vendor')

@section('page-title', 'New Bookings')

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">New Bookings</h2>
            <p class="text-muted mb-0">Pending bookings awaiting payment confirmation</p>
        </div>
        <a href="{{ route('vendor.bookings.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>All Bookings
        </a>
    </div>
</div>

<!-- Category Navigation -->
<div class="mb-4">
    <div class="btn-group w-100" role="group">
        <a href="{{ route('vendor.bookings.new') }}" class="btn btn-primary">
            <i class="bi bi-inbox me-2"></i>New
            <span class="badge bg-white text-primary ms-2">{{ $stats['total'] }}</span>
        </a>
        <a href="{{ route('vendor.bookings.ongoing') }}" class="btn btn-outline-primary">
            <i class="bi bi-play-circle me-2"></i>Ongoing
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
                        <div class="text-muted small mb-1">Total New</div>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #dbeafe; color: #3b82f6;">
                        <i class="bi bi-inbox"></i>
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
                        <div class="text-muted small mb-1">Pending Payment</div>
                        <h3 class="mb-0">{{ $stats['pending_payment'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-clock-history"></i>
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
                        <div class="text-muted small mb-1">Payment Hold</div>
                        <h3 class="mb-0">{{ $stats['payment_hold'] }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('vendor.bookings.new') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <input type="number" name="amount_min" class="form-control" placeholder="Min Amount" value="{{ request('amount_min') }}">
            </div>
            <div class="col-md-2">
                <input type="number" name="amount_max" class="form-control" placeholder="Max Amount" value="{{ request('amount_max') }}">
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
                        <th>Duration</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
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
                            <span class="fw-bold">₹{{ number_format($booking->total_amount, 2) }}</span>
                        </td>
                        <td>
                            @php
                                $statusClass = match($booking->status) {
                                    'pending_payment_hold' => 'warning',
                                    'payment_hold' => 'info',
                                    'confirmed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                        </td>
                        <td>
                            @if($booking->payment_status)
                                <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $booking->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="btn btn-sm btn-outline-primary">
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
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3">No New Bookings</h4>
            <p class="text-muted">No pending bookings at the moment.</p>
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
