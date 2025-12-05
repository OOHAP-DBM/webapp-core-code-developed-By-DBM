@extends('layouts.app')

@section('title', 'Booking Hold - Booking #' . $booking->id)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Hold Timer Card with Cancel Button -->
            <div class="card shadow-sm border-0 mb-4" id="timer-card">
                <div class="card-body text-center py-4">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-clock-history text-warning"></i>
                        Payment Hold Active
                    </h5>
                    <p class="text-muted mb-3">Your booking is on hold. Time remaining:</p>
                    
                    <!-- Countdown Timer -->
                    <div class="countdown-display mb-3">
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <div class="time-box">
                                <div class="time-value display-4 fw-bold text-primary" id="minutes">--</div>
                                <div class="time-label text-muted small">MINUTES</div>
                            </div>
                            <div class="time-separator display-4 text-primary">:</div>
                            <div class="time-box">
                                <div class="time-value display-4 fw-bold text-primary" id="seconds">--</div>
                                <div class="time-label text-muted small">SECONDS</div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" 
                             id="progress-bar" 
                             role="progressbar" 
                             style="width: 100%"
                             aria-valuenow="100" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    
                    <p class="text-muted small mb-3 mt-2">
                        Hold expires at: <strong id="expiry-time">{{ $booking->hold_expiry_at->format('h:i A, M d, Y') }}</strong>
                    </p>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 justify-content-center mt-4">
                        <a href="{{ route('customer.bookings.payment', $booking->id) }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-credit-card"></i> Proceed to Payment
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-lg" id="cancel-btn">
                            <i class="bi bi-x-circle"></i> Cancel Booking
                        </button>
                    </div>
                </div>
            </div>

            <!-- Expired Hold Message (Hidden by default) -->
            <div class="alert alert-danger d-none" id="expired-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Payment hold has expired!</strong> This booking has been released. Please create a new booking.
            </div>

            <!-- Cancelled Message (Hidden by default) -->
            <div class="alert alert-success d-none" id="cancelled-alert">
                <i class="bi bi-check-circle-fill"></i>
                <strong>Booking cancelled successfully!</strong> Your payment authorization has been voided.
            </div>

            <!-- Booking Details Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt text-primary"></i>
                        Booking Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="text-muted mb-1 small">Booking ID</p>
                            <p class="fw-semibold mb-0">#{{ $booking->id }}</p>
                        </div>
                        <div class="col-6 text-end">
                            <p class="text-muted mb-1 small">Status</p>
                            <span class="badge bg-warning text-dark">
                                Payment Hold
                            </span>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-badge-ad"></i> Hoarding
                            </p>
                            <p class="fw-semibold mb-0">{{ $booking->booking_snapshot['hoarding_title'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-geo-alt"></i> Location
                            </p>
                            <p class="mb-0">{{ $booking->booking_snapshot['hoarding_location'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-calendar-check"></i> Start Date
                            </p>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-calendar-x"></i> End Date
                            </p>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($booking->end_date)->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-clock"></i> Duration
                            </p>
                            <p class="mb-0">{{ $booking->duration_days }} {{ $booking->duration_type }}</p>
                        </div>
                    </div>

                    <hr>

                    <!-- Payment Details -->
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-semibold">₹{{ number_format($booking->booking_snapshot['subtotal'] ?? 0, 2) }}</span>
                        </div>
                        @if(isset($booking->booking_snapshot['tax']) && $booking->booking_snapshot['tax'] > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Tax</span>
                            <span>₹{{ number_format($booking->booking_snapshot['tax'], 2) }}</span>
                        </div>
                        @endif
                        @if(isset($booking->booking_snapshot['discount']) && $booking->booking_snapshot['discount'] > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Discount</span>
                            <span class="text-success">-₹{{ number_format($booking->booking_snapshot['discount'], 2) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">Total Amount</span>
                            <span class="fw-bold fs-4 text-primary">₹{{ number_format($booking->total_amount, 2) }}</span>
                        </div>
                    </div>

                    @if($booking->customer_notes)
                    <div class="alert alert-info">
                        <strong>Your Notes:</strong>
                        <p class="mb-0 mt-1">{{ $booking->customer_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Cancel Confirmation Modal -->
            <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cancelModalLabel">
                                <i class="bi bi-exclamation-triangle text-warning"></i>
                                Cancel Booking
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to cancel this booking?</p>
                            <p class="text-muted small mb-3">
                                Your payment authorization will be voided, and this booking will be cancelled immediately.
                            </p>
                            
                            <div class="mb-3">
                                <label for="cancel-reason" class="form-label">Reason for cancellation (optional)</label>
                                <textarea class="form-control" id="cancel-reason" rows="3" placeholder="Please tell us why you're cancelling..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                            <button type="button" class="btn btn-danger" id="confirm-cancel-btn">
                                <span class="spinner-border spinner-border-sm d-none" id="cancel-spinner"></span>
                                Yes, Cancel Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.time-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px 20px;
    min-width: 100px;
}

.time-value {
    line-height: 1;
    font-family: 'Courier New', monospace;
}

.time-separator {
    line-height: 1;
    padding: 0 10px;
}

.countdown-display {
    max-width: 400px;
    margin: 0 auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const holdExpiryAt = new Date("{{ $booking->hold_expiry_at->toIso8601String() }}");
    const bookingId = {{ $booking->id }};
    const timerCard = document.getElementById('timer-card');
    const expiredAlert = document.getElementById('expired-alert');
    const cancelledAlert = document.getElementById('cancelled-alert');
    const cancelBtn = document.getElementById('cancel-btn');
    const confirmCancelBtn = document.getElementById('confirm-cancel-btn');
    const cancelSpinner = document.getElementById('cancel-spinner');
    const cancelReasonInput = document.getElementById('cancel-reason');
    
    let countdownInterval;

    // Initialize Bootstrap modal
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));

    // Countdown timer function
    function updateCountdown() {
        const now = new Date();
        const timeRemaining = holdExpiryAt - now;

        if (timeRemaining <= 0) {
            clearInterval(countdownInterval);
            showExpired();
            return;
        }

        const totalSeconds = Math.floor(timeRemaining / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;

        // Update display
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

        // Update progress bar
        const totalHoldTime = 30 * 60; // 30 minutes in seconds
        const percentRemaining = (totalSeconds / totalHoldTime) * 100;
        const progressBar = document.getElementById('progress-bar');
        progressBar.style.width = percentRemaining + '%';
        progressBar.setAttribute('aria-valuenow', percentRemaining);

        // Change color based on time remaining
        if (minutes < 5) {
            progressBar.classList.remove('bg-warning', 'bg-success');
            progressBar.classList.add('bg-danger');
            document.getElementById('minutes').classList.remove('text-primary', 'text-warning');
            document.getElementById('minutes').classList.add('text-danger');
            document.getElementById('seconds').classList.remove('text-primary', 'text-warning');
            document.getElementById('seconds').classList.add('text-danger');
        } else if (minutes < 15) {
            progressBar.classList.remove('bg-success', 'bg-danger');
            progressBar.classList.add('bg-warning');
            document.getElementById('minutes').classList.remove('text-primary', 'text-danger');
            document.getElementById('minutes').classList.add('text-warning');
            document.getElementById('seconds').classList.remove('text-primary', 'text-danger');
            document.getElementById('seconds').classList.add('text-warning');
        }
    }

    function showExpired() {
        timerCard.classList.add('d-none');
        expiredAlert.classList.remove('d-none');
        cancelBtn.disabled = true;
    }

    function showCancelled() {
        timerCard.classList.add('d-none');
        expiredAlert.classList.add('d-none');
        cancelledAlert.classList.remove('d-none');
        clearInterval(countdownInterval);
    }

    // Show cancel modal
    cancelBtn.addEventListener('click', function() {
        cancelModal.show();
    });

    // Handle cancel confirmation
    confirmCancelBtn.addEventListener('click', function() {
        const reason = cancelReasonInput.value.trim();
        
        // Show spinner
        cancelSpinner.classList.remove('d-none');
        confirmCancelBtn.disabled = true;

        // Make API call
        fetch(`/api/v1/bookings-v2/${bookingId}/cancel-during-hold`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            },
            body: JSON.stringify({
                reason: reason || null
            })
        })
        .then(response => response.json())
        .then(data => {
            cancelSpinner.classList.add('d-none');
            confirmCancelBtn.disabled = false;
            cancelModal.hide();

            if (data.success) {
                showCancelled();
                
                // Redirect to bookings list after 3 seconds
                setTimeout(() => {
                    window.location.href = '/customer/bookings';
                }, 3000);
            } else {
                alert('Error: ' + (data.message || 'Failed to cancel booking'));
            }
        })
        .catch(error => {
            cancelSpinner.classList.add('d-none');
            confirmCancelBtn.disabled = false;
            console.error('Error:', error);
            alert('Failed to cancel booking. Please try again.');
        });
    });

    // Start countdown
    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 1000);
});
</script>
@endsection
