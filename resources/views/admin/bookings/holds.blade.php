@extends('layouts.app')

@section('title', 'Payment Holds Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-hourglass-split text-warning"></i>
                    Payment Holds Management
                </h2>
                <button type="button" class="btn btn-primary" onclick="refreshData()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Active Holds</p>
                            <h3 class="mb-0" id="active-holds-count">{{ $activeHolds->count() }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
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
                            <p class="text-muted mb-1 small">Expiring Soon (< 10 min)</p>
                            <h3 class="mb-0 text-danger" id="expiring-soon-count">{{ $expiringSoon->count() }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
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
                            <p class="text-muted mb-1 small">Total Hold Value</p>
                            <h3 class="mb-0 text-success">₹{{ number_format($totalHoldValue, 2) }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-currency-rupee" style="font-size: 2rem;"></i>
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
                            <p class="text-muted mb-1 small">Expired (Pending Capture)</p>
                            <h3 class="mb-0 text-warning">{{ $expired->count() }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-hourglass-bottom" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Soon Section -->
    @if($expiringSoon->count() > 0)
    <div class="card border-0 shadow-sm mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Urgent: Expiring in Next 10 Minutes
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Hoarding</th>
                            <th>Amount</th>
                            <th>Authorized At</th>
                            <th>Expires In</th>
                            <th>Payment ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiringSoon as $booking)
                        <tr id="booking-row-{{ $booking->id }}">
                            <td><strong>#{{ $booking->id }}</strong></td>
                            <td>
                                {{ $booking->customer->name }}<br>
                                <small class="text-muted">{{ $booking->customer->email }}</small>
                            </td>
                            <td>
                                {{ $booking->booking_snapshot['hoarding_name'] ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $booking->booking_snapshot['hoarding_location'] ?? '' }}</small>
                            </td>
                            <td><strong>₹{{ number_format($booking->total_amount, 2) }}</strong></td>
                            <td>
                                {{ $booking->payment_authorized_at?->format('M d, h:i A') }}<br>
                                <small class="text-muted">{{ $booking->payment_authorized_at?->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span class="badge bg-danger countdown" data-expiry="{{ $booking->hold_expiry_at->toIso8601String() }}">
                                    {{ $booking->getHoldMinutesRemaining() }} min
                                </span>
                            </td>
                            <td>
                                <code class="small">{{ $booking->razorpay_payment_id }}</code>
                            </td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-success manual-capture-btn"
                                        onclick="manualCapture({{ $booking->id }})"
                                        data-booking-id="{{ $booking->id }}">
                                    <i class="bi bi-check-circle"></i> Capture Now
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Active Holds Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-clock-history text-primary"></i>
                Active Payment Holds
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Hoarding</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                            <th>Authorized At</th>
                            <th>Hold Expires At</th>
                            <th>Time Remaining</th>
                            <th>Payment ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeHolds as $booking)
                        <tr id="booking-row-{{ $booking->id }}">
                            <td><strong>#{{ $booking->id }}</strong></td>
                            <td>
                                {{ $booking->customer->name }}<br>
                                <small class="text-muted">{{ $booking->customer->email }}</small>
                            </td>
                            <td>
                                {{ $booking->booking_snapshot['hoarding_name'] ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $booking->booking_snapshot['hoarding_location'] ?? '' }}</small>
                            </td>
                            <td><strong>₹{{ number_format($booking->total_amount, 2) }}</strong></td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($booking->payment_status) }}</span>
                            </td>
                            <td>
                                {{ $booking->payment_authorized_at?->format('M d, h:i A') }}<br>
                                <small class="text-muted">{{ $booking->payment_authorized_at?->diffForHumans() }}</small>
                            </td>
                            <td>
                                {{ $booking->hold_expiry_at->format('M d, h:i A') }}<br>
                                <small class="text-muted">{{ $booking->hold_expiry_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                @php
                                    $minutesRemaining = $booking->getHoldMinutesRemaining();
                                    $badgeClass = $minutesRemaining <= 5 ? 'bg-danger' : ($minutesRemaining <= 15 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <span class="badge {{ $badgeClass }} countdown" data-expiry="{{ $booking->hold_expiry_at->toIso8601String() }}">
                                    {{ max(0, $minutesRemaining) }} min
                                </span>
                            </td>
                            <td>
                                <code class="small">{{ $booking->razorpay_payment_id }}</code>
                            </td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-success manual-capture-btn"
                                        onclick="manualCapture({{ $booking->id }})"
                                        data-booking-id="{{ $booking->id }}">
                                    <i class="bi bi-check-circle"></i> Capture
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">No active payment holds</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Expired Holds Section -->
    @if($expired->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-hourglass-bottom text-warning"></i>
                Expired Holds (Pending Capture)
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Expired At</th>
                            <th>Time Since Expiry</th>
                            <th>Capture Attempted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expired as $booking)
                        <tr id="booking-row-{{ $booking->id }}">
                            <td><strong>#{{ $booking->id }}</strong></td>
                            <td>
                                {{ $booking->customer->name }}<br>
                                <small class="text-muted">{{ $booking->customer->email }}</small>
                            </td>
                            <td><strong>₹{{ number_format($booking->total_amount, 2) }}</strong></td>
                            <td>{{ $booking->hold_expiry_at->format('M d, h:i A') }}</td>
                            <td>
                                <span class="text-danger">
                                    {{ $booking->hold_expiry_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                @if($booking->capture_attempted_at)
                                    <span class="badge bg-secondary">
                                        Yes - {{ $booking->capture_attempted_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if(!$booking->capture_attempted_at)
                                <button type="button" 
                                        class="btn btn-sm btn-success manual-capture-btn"
                                        onclick="manualCapture({{ $booking->id }})"
                                        data-booking-id="{{ $booking->id }}">
                                    <i class="bi bi-check-circle"></i> Capture
                                </button>
                                @else
                                <span class="text-muted small">Processing...</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Auto-refresh countdown timers
setInterval(function() {
    document.querySelectorAll('.countdown').forEach(function(element) {
        const expiryDate = new Date(element.getAttribute('data-expiry'));
        const now = new Date();
        const diff = expiryDate - now;
        
        if (diff <= 0) {
            element.textContent = 'Expired';
            element.classList.remove('bg-success', 'bg-warning', 'bg-info');
            element.classList.add('bg-danger');
        } else {
            const minutes = Math.floor(diff / 60000);
            element.textContent = minutes + ' min';
            
            // Update badge color based on time remaining
            element.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'bg-info');
            if (minutes <= 5) {
                element.classList.add('bg-danger');
            } else if (minutes <= 15) {
                element.classList.add('bg-warning');
            } else {
                element.classList.add('bg-success');
            }
        }
    });
}, 10000); // Update every 10 seconds

// Manual capture function
async function manualCapture(bookingId) {
    const button = document.querySelector(`button[data-booking-id="${bookingId}"]`);
    
    if (!confirm('Are you sure you want to manually capture this payment?')) {
        return;
    }
    
    // Disable button and show loading
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Capturing...';
    
    try {
        const response = await fetch(`/api/v1/admin/bookings/${bookingId}/manual-capture`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + localStorage.getItem('api_token')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            alert('Payment captured successfully!');
            
            // Remove row from table
            document.getElementById(`booking-row-${bookingId}`).remove();
            
            // Update counts
            updateCounts();
        } else {
            alert('Error: ' + result.message);
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-check-circle"></i> Capture';
        }
    } catch (error) {
        alert('Error: ' + error.message);
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-check-circle"></i> Capture';
    }
}

// Refresh data
function refreshData() {
    window.location.reload();
}

// Update statistics counts
function updateCounts() {
    // Recalculate counts from visible rows
    const activeRows = document.querySelectorAll('#active-holds-section tbody tr:not(.d-none)').length;
    const expiringRows = document.querySelectorAll('#expiring-soon-section tbody tr:not(.d-none)').length;
    
    document.getElementById('active-holds-count').textContent = activeRows;
    document.getElementById('expiring-soon-count').textContent = expiringRows;
}
</script>

<style>
.countdown {
    min-width: 60px;
    display: inline-block;
    text-align: center;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

code {
    background-color: #f1f3f5;
    padding: 2px 6px;
    border-radius: 3px;
}
</style>
@endsection
