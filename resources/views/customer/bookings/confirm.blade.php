@extends('layouts.customer')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Payment Hold Timer Alert -->
            @if($booking->isPaymentHold() && !$booking->isHoldExpired())
                <div class="alert alert-warning mb-4" id="holdAlert">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="alert-heading mb-1">
                                <i class="bi bi-clock-history"></i> Payment Hold Active
                            </h5>
                            <p class="mb-0">Complete payment within <strong id="timeRemaining"></strong> to secure this booking</p>
                        </div>
                        <div class="text-end">
                            <div class="display-6 text-danger" id="countdownDisplay">30:00</div>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-danger" id="progressBar" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            @endif

            @if($booking->isHoldExpired())
                <div class="alert alert-danger mb-4">
                    <h5 class="alert-heading"><i class="bi bi-x-circle"></i> Payment Hold Expired</h5>
                    <p class="mb-0">This booking has expired. The dates are no longer reserved. Please create a new booking.</p>
                </div>
            @endif

            <!-- Booking Details Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-calendar-check"></i> Booking Confirmation</h4>
                        <span class="badge {{ $booking->getStatusBadgeClass() }}">
                            {{ $booking->getStatusLabel() }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Booking ID -->
                    <div class="mb-4 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Booking ID:</strong> #{{ $booking->id }}</p>
                                <p class="mb-1"><strong>Quotation:</strong> #{{ $booking->quotation_id }} (v{{ $booking->quotation->version }})</p>
                                <p class="mb-0"><strong>Created:</strong> {{ $booking->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                @if($booking->confirmed_at)
                                    <p class="mb-0 text-success">
                                        <i class="bi bi-check-circle-fill"></i> 
                                        <strong>Confirmed:</strong> {{ $booking->confirmed_at->format('M d, Y h:i A') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Hoarding Details -->
                    <h5 class="mb-3">Hoarding Details</h5>
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h6>{{ $booking->getSnapshotValue('hoarding_title', $booking->hoarding->title) }}</h6>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-geo-alt"></i> 
                                {{ $booking->getSnapshotValue('hoarding_location', $booking->hoarding->location) }}
                            </p>
                            <p class="mb-1 text-muted">
                                <i class="bi bi-rulers"></i> 
                                Dimensions: {{ $booking->getSnapshotValue('hoarding_dimensions', $booking->hoarding->width . 'x' . $booking->hoarding->height) }}
                            </p>
                            <p class="mb-0 text-muted">
                                <i class="bi bi-person"></i> 
                                Vendor: {{ $booking->getSnapshotValue('vendor_name', $booking->vendor->name) }}
                            </p>
                        </div>
                    </div>

                    <!-- Booking Dates -->
                    <h5 class="mb-3">Booking Period</h5>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">Start Date</small>
                                <strong class="d-block">{{ $booking->start_date->format('M d, Y') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">End Date</small>
                                <strong class="d-block">{{ $booking->end_date->format('M d, Y') }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <small class="text-muted d-block">Duration</small>
                                <strong class="d-block">{{ $booking->duration_days }} days</strong>
                                <small class="text-muted">({{ ucfirst($booking->duration_type) }})</small>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Breakdown -->
                    <h5 class="mb-3">Pricing Summary</h5>
                    <div class="row mb-4">
                        <div class="col-md-8">
                            @if($booking->getSnapshotValue('line_items'))
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Rate</th>
                                                <th class="text-end">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($booking->getSnapshotValue('line_items', []) as $item)
                                                <tr>
                                                    <td>{{ $item['description'] ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $item['quantity'] ?? 0 }} {{ $item['unit'] ?? '' }}</td>
                                                    <td class="text-end">₹{{ number_format($item['rate'] ?? 0, 2) }}</td>
                                                    <td class="text-end">₹{{ number_format($item['amount'] ?? 0, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-end">₹{{ number_format($booking->getSnapshotValue('subtotal', 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Tax:</td>
                                    <td class="text-end">₹{{ number_format($booking->getSnapshotValue('tax', 0), 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Discount:</td>
                                    <td class="text-end">- ₹{{ number_format($booking->getSnapshotValue('discount', 0), 2) }}</td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Total Amount:</strong></td>
                                    <td class="text-end">
                                        <strong class="text-success fs-5">
                                            {{ $booking->getFormattedTotalAmount() }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Customer Notes -->
                    @if($booking->customer_notes)
                        <h5 class="mb-3">Your Notes</h5>
                        <p class="p-3 bg-light border-start border-primary border-4 mb-4">
                            {{ $booking->customer_notes }}
                        </p>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        @if($booking->isPaymentHold() && !$booking->isHoldExpired())
                            <button type="button" class="btn btn-success btn-lg" id="proceedToPaymentBtn">
                                <i class="bi bi-credit-card"></i> Proceed to Payment
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="cancelBookingBtn">
                                <i class="bi bi-x-circle"></i> Cancel Booking
                            </button>
                        @elseif($booking->isConfirmed())
                            <a href="{{ route('customer.bookings.index') }}" class="btn btn-primary">
                                <i class="bi bi-list"></i> View All Bookings
                            </a>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print Confirmation
                            </button>
                        @else
                            <a href="{{ route('customer.bookings.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Bookings
                            </a>
                        @endif
                    </div>

                    <div id="alertContainer" class="mt-3"></div>
                </div>
            </div>

            <!-- Status History -->
            @if($booking->statusLogs->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Status History</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($booking->statusLogs as $log)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <span class="badge bg-secondary">
                                                {{ $log->created_at->format('M d, h:i A') }}
                                            </span>
                                        </div>
                                        <div>
                                            <strong>{{ $log->getFormattedStatus() }}</strong>
                                            @if($log->notes)
                                                <p class="mb-0 text-muted small">{{ $log->notes }}</p>
                                            @endif
                                            @if($log->changedBy)
                                                <small class="text-muted">by {{ $log->changedBy->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingId = {{ $booking->id }};
    const holdExpiryAt = '{{ $booking->hold_expiry_at?->toIso8601String() }}';
    const isPaymentHold = {{ $booking->isPaymentHold() ? 'true' : 'false' }};
    
    // Countdown timer
    if (isPaymentHold && holdExpiryAt) {
        const expiryTime = new Date(holdExpiryAt).getTime();
        const totalDuration = 30 * 60 * 1000; // 30 minutes in milliseconds

        const countdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = expiryTime - now;

            if (distance < 0) {
                clearInterval(countdown);
                document.getElementById('countdownDisplay').innerHTML = 'EXPIRED';
                document.getElementById('progressBar').style.width = '0%';
                document.getElementById('holdAlert').classList.remove('alert-warning');
                document.getElementById('holdAlert').classList.add('alert-danger');
                location.reload(); // Reload to show expired state
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdownDisplay').innerHTML = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            document.getElementById('timeRemaining').innerHTML = 
                `${minutes} minute${minutes !== 1 ? 's' : ''} ${seconds} second${seconds !== 1 ? 's' : ''}`;

            // Update progress bar
            const percentage = (distance / totalDuration) * 100;
            document.getElementById('progressBar').style.width = percentage + '%';

            // Change color based on time remaining
            const progressBar = document.getElementById('progressBar');
            if (percentage < 25) {
                progressBar.classList.remove('bg-warning');
                progressBar.classList.add('bg-danger');
            } else if (percentage < 50) {
                progressBar.classList.remove('bg-success');
                progressBar.classList.add('bg-warning');
            }
        }, 1000);
    }

    // Proceed to payment
    const proceedBtn = document.getElementById('proceedToPaymentBtn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', function() {
            // TODO: Integrate with Razorpay
            alert('Payment gateway integration coming soon!\\n\\nBooking ID: ' + bookingId + '\\nAmount: {{ $booking->getFormattedTotalAmount() }}');
        });
    }

    // Cancel booking
    const cancelBtn = document.getElementById('cancelBookingBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', async function() {
            if (!confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                return;
            }

            const reason = prompt('Please provide a reason for cancellation (optional):');

            try {
                const response = await fetch(`/api/v1/bookings/${bookingId}/cancel`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason })
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to cancel booking');
                }

                document.getElementById('alertContainer').innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Booking cancelled successfully
                    </div>`;

                setTimeout(() => location.reload(), 1500);
            } catch (error) {
                document.getElementById('alertContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${error.message}
                    </div>`;
            }
        });
    }
});
</script>

<style>
@media print {
    .btn, #holdAlert, .card-header { display: none !important; }
}
</style>
@endsection
