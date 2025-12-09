@extends('layouts.vendor')

@section('page-title', 'Booking Management')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Booking Management</h2>
    <p class="text-muted mb-0">Manage all your bookings and reservations</p>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Pending</div>
                        <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Confirmed</div>
                        <h3 class="mb-0">{{ $stats['confirmed'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Active</div>
                        <h3 class="mb-0">{{ $stats['active'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                        <i class="bi bi-play-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Completed</div>
                        <h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3>
                    </div>
                    <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">
                        <i class="bi bi-check2-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="vendor-card mb-4">
    <div class="vendor-card-body">
        <form action="{{ route('vendor.bookings.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Booking ID, Customer..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('vendor.bookings.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="vendor-card">
    <div class="vendor-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Hoarding</th>
                        <th>Duration</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings ?? [] as $booking)
                        <tr>
                            <td>
                                <strong>#{{ $booking->id }}</strong>
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</small>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $booking->customer->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $booking->customer->phone ?? '' }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    {{ $booking->hoarding->title ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $booking->hoarding->city ?? '' }}</small>
                                </div>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($booking->start_date)->format('d M') }} - 
                                {{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(\Carbon\Carbon::parse($booking->end_date)) }} days</small>
                            </td>
                            <td>
                                <strong>₹{{ number_format($booking->total_amount ?? 0, 0) }}</strong>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($booking->status === 'pending') bg-warning text-dark
                                    @elseif($booking->status === 'confirmed') bg-success
                                    @elseif($booking->status === 'active') bg-primary
                                    @elseif($booking->status === 'completed') bg-info
                                    @elseif($booking->status === 'cancelled') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $booking->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($booking->payment_status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('vendor.bookings.show', $booking->id) }}" class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($booking->status === 'pending')
                                        <button type="button" class="btn btn-outline-success" onclick="confirmBooking({{ $booking->id }})" title="Confirm">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="cancelBooking({{ $booking->id }})" title="Cancel">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                                <p class="mt-3">No bookings found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(isset($bookings) && $bookings->hasPages())
        <div class="vendor-card-body border-top">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function confirmBooking(id) {
    if (confirm('Confirm this booking?')) {
        fetch(`/vendor/bookings/${id}/confirm`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to confirm booking');
            }
        });
    }
}

function cancelBooking(id) {
    const reason = prompt('Enter cancellation reason:');
    if (reason) {
        fetch(`/vendor/bookings/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to cancel booking');
            }
        });
    }
}
</script>
@endpush
