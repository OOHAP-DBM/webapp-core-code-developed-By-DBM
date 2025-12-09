@extends('layouts.customer')

@section('title', 'My Bookings')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Bookings</h1>
            <p class="text-muted">Manage and track all your bookings</p>
        </div>
        <div>
            <div class="btn-group">
                <a href="{{ route('customer.my.bookings.export', 'pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                   class="btn btn-outline-danger">
                    <i class="bi bi-file-pdf"></i> PDF
                </a>
                <a href="{{ route('customer.my.bookings.export', 'csv') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
                   class="btn btn-outline-success">
                    <i class="bi bi-file-excel"></i> CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Bookings</h6>
                    <h3 class="mb-0">{{ $summary['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Amount</h6>
                    <h3 class="mb-0">₹{{ number_format($summary['total_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Paid Amount</h6>
                    <h3 class="mb-0 text-success">₹{{ number_format($summary['paid_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending Amount</h6>
                    <h3 class="mb-0 text-warning">₹{{ number_format($summary['total_amount'] - $summary['paid_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.my.bookings') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Booking number or hoarding..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
            @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
            <div class="mt-2">
                <a href="{{ route('customer.my.bookings') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <a href="{{ route('customer.my.bookings', array_merge(request()->all(), ['sort_by' => 'booking_number', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Booking # <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th>Hoarding</th>
                            <th>Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>
                                <a href="{{ route('customer.my.bookings', array_merge(request()->all(), ['sort_by' => 'created_at', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc'])) }}" 
                                   class="text-decoration-none text-dark">
                                    Created <i class="bi bi-arrow-down-up"></i>
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td>
                                <strong>{{ $booking->booking_number }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $booking->hoarding->title ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $booking->hoarding->city ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>
                                    {{ $booking->start_date->format('M d, Y') }}<br>
                                    to {{ $booking->end_date->format('M d, Y') }}
                                </small>
                            </td>
                            <td>
                                <strong>₹{{ number_format($booking->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'confirmed' => 'info',
                                    'active' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                @php
                                $paymentColors = [
                                    'pending' => 'warning',
                                    'partial' => 'info',
                                    'paid' => 'success',
                                    'refunded' => 'secondary'
                                ];
                                @endphp
                                <span class="badge bg-{{ $paymentColors[$booking->payment_status] ?? 'secondary' }}">
                                    {{ ucfirst($booking->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $booking->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customer.bookings.show', $booking->id) }}" 
                                       class="btn btn-outline-primary btn-sm" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($booking->payment_status != 'paid')
                                    <button class="btn btn-outline-success btn-sm" title="Pay Now">
                                        <i class="bi bi-credit-card"></i>
                                    </button>
                                    @endif
                                    @if(in_array($booking->status, ['pending', 'confirmed']))
                                    <button class="btn btn-outline-danger btn-sm" title="Cancel">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted">No bookings found</p>
                                @if(request()->hasAny(['search', 'status', 'payment_status', 'date_from', 'date_to']))
                                <a href="{{ route('customer.my.bookings') }}" class="btn btn-sm btn-outline-primary">Clear Filters</a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bookings->hasPages())
        <div class="card-footer bg-white">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
