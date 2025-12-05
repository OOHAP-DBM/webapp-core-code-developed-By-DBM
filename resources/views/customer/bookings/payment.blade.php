@extends('layouts.app')

@section('title', 'Complete Payment - Booking #' . $booking->id)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Hold Timer Card -->
            <div class="card shadow-sm border-0 mb-4" id="timer-card">
                <div class="card-body text-center py-4">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-clock-history text-warning"></i>
                        Payment Hold Active
                    </h5>
                    <p class="text-muted mb-3">Complete payment within:</p>
                    
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
                    
                    <p class="text-muted small mb-0 mt-2">
                        Hold expires at: <strong id="expiry-time">{{ $booking->hold_expiry_at->format('h:i A, M d, Y') }}</strong>
                    </p>
                </div>
            </div>

            <!-- Expired Hold Message (Hidden by default) -->
            <div class="alert alert-danger d-none" id="expired-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Payment hold has expired!</strong> This booking has been released. Please create a new booking.
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
                            <span class="badge {{ $booking->getStatusBadgeClass() }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-badge-ad"></i> Hoarding
                            </p>
                            <p class="fw-semibold mb-0">{{ $booking->hoarding_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-geo-alt"></i> Location
                            </p>
                            <p class="mb-0">{{ $booking->hoarding_location }}</p>
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
                        <div class="col-md-6">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-clock"></i> Duration
                            </p>
                            <p class="mb-0">{{ $booking->duration_days }} days</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1 small">
                                <i class="bi bi-aspect-ratio"></i> Size
                            </p>
                            <p class="mb-0">{{ $booking->hoarding_width }}' × {{ $booking->hoarding_height }}'</p>
                        </div>
                    </div>

                    <hr>

                    <!-- Pricing Breakdown -->
                    <div class="pricing-breakdown">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Base Rate (per day)</span>
                            <span>₹{{ number_format($booking->rate_per_day, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Days × Rate</span>
                            <span>₹{{ number_format($booking->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax ({{ $booking->tax_percentage }}%)</span>
                            <span>₹{{ number_format($booking->tax_amount, 2) }}</span>
                        </div>
                        @if($booking->discount_amount > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span>- ₹{{ number_format($booking->discount_amount, 2) }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Total Amount</h5>
                            <h4 class="mb-0 text-primary fw-bold">₹{{ number_format($booking->total_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Button Card -->
            <div class="card shadow-sm border-0 mb-4" id="payment-card">
                <div class="card-body text-center py-4">
                    <h5 class="card-title mb-3">Complete Your Payment</h5>
                    <p class="text-muted mb-4">Click the button below to proceed with secure payment via Razorpay</p>
                    
                    <button type="button" 
                            class="btn btn-primary btn-lg px-5 py-3" 
                            id="pay-button"
                            onclick="initiatePayment()">
                        <i class="bi bi-credit-card me-2"></i>
                        Pay ₹{{ number_format($booking->total_amount, 2) }}
                    </button>

                    <div class="mt-4">
                        <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" height="24" class="me-2">
                        <small class="text-muted">Secured by Razorpay</small>
                    </div>
                </div>
            </div>

            <!-- Booking History -->
            @if($booking->statusLogs && $booking->statusLogs->count() > 0)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-primary"></i>
                        Status History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($booking->statusLogs->sortByDesc('created_at') as $log)
                        <div class="timeline-item d-flex align-items-start mb-3">
                            <div class="timeline-icon me-3">
                                <i class="bi bi-circle-fill text-primary" style="font-size: 10px;"></i>
                            </div>
                            <div class="timeline-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge {{ $log->getStatusBadgeClass() }}">
                                            {{ $log->getFormattedStatus() }}
                                        </span>
                                        @if($log->notes)
                                        <p class="text-muted small mb-0 mt-1">{{ $log->notes }}</p>
                                        @endif
                                    </div>
                                    <small class="text-muted">
                                        {{ $log->created_at->diffForHumans() }}
                                    </small>
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

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    // Booking data
    const bookingId = {{ $booking->id }};
    const holdExpiryAt = new Date('{{ $booking->hold_expiry_at->toIso8601String() }}');
    const totalMinutes = 30;

    // Timer variables
    let timerInterval;

    // Initialize countdown timer
    function initializeTimer() {
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    // Update countdown timer
    function updateTimer() {
        const now = new Date();
        const remaining = holdExpiryAt - now;

        if (remaining <= 0) {
            handleExpiry();
            return;
        }

        const minutes = Math.floor(remaining / 1000 / 60);
        const seconds = Math.floor((remaining / 1000) % 60);

        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

        // Update progress bar
        const totalSeconds = totalMinutes * 60;
        const remainingSeconds = minutes * 60 + seconds;
        const percentage = (remainingSeconds / totalSeconds) * 100;
        
        const progressBar = document.getElementById('progress-bar');
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);

        // Change color based on remaining time
        if (percentage <= 20) {
            progressBar.classList.remove('bg-warning', 'bg-success');
            progressBar.classList.add('bg-danger');
        } else if (percentage <= 50) {
            progressBar.classList.remove('bg-success', 'bg-danger');
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.remove('bg-warning', 'bg-danger');
            progressBar.classList.add('bg-success');
        }
    }

    // Handle expiry
    function handleExpiry() {
        clearInterval(timerInterval);
        
        document.getElementById('timer-card').classList.add('d-none');
        document.getElementById('payment-card').classList.add('d-none');
        document.getElementById('expired-alert').classList.remove('d-none');
        
        document.getElementById('pay-button').disabled = true;
    }

    // Create Razorpay order and initiate payment
    async function initiatePayment() {
        const payButton = document.getElementById('pay-button');
        payButton.disabled = true;
        payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

        try {
            // Create Razorpay order
            const response = await fetch(`/api/v1/bookings/${bookingId}/create-order`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + '{{ auth()->user()->api_token ?? "" }}'
                }
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Failed to create order');
            }

            // Open Razorpay checkout
            const options = {
                key: result.razorpay_key,
                amount: result.data.amount,
                currency: result.data.currency,
                name: 'OohApp Booking',
                description: `Booking #${bookingId} - Hoarding Advertisement`,
                order_id: result.data.order_id,
                handler: function(response) {
                    handlePaymentSuccess(response);
                },
                prefill: {
                    name: '{{ auth()->user()->name }}',
                    email: '{{ auth()->user()->email }}',
                    contact: '{{ auth()->user()->phone ?? "" }}'
                },
                theme: {
                    color: '#0d6efd'
                },
                modal: {
                    ondismiss: function() {
                        payButton.disabled = false;
                        payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i>Pay ₹{{ number_format($booking->total_amount, 2) }}';
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();

        } catch (error) {
            alert('Error: ' + error.message);
            payButton.disabled = false;
            payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i>Pay ₹{{ number_format($booking->total_amount, 2) }}';
        }
    }

    // Handle successful payment
    async function handlePaymentSuccess(response) {
        try {
            const confirmResponse = await fetch(`/api/v1/bookings/${bookingId}/confirm`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + '{{ auth()->user()->api_token ?? "" }}'
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                })
            });

            const result = await confirmResponse.json();

            if (result.success) {
                // Redirect to success page
                window.location.href = `/customer/bookings/${bookingId}?payment=success`;
            } else {
                alert('Payment verification failed: ' + result.message);
            }
        } catch (error) {
            alert('Error confirming payment: ' + error.message);
        }
    }

    // Initialize timer on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeTimer();
    });
</script>

<style>
    .countdown-display .time-box {
        min-width: 120px;
    }

    .countdown-display .time-value {
        font-family: 'Courier New', monospace;
        line-height: 1;
    }

    .countdown-display .time-label {
        letter-spacing: 2px;
        font-weight: 600;
    }

    .countdown-display .time-separator {
        line-height: 1;
        margin: 0 10px;
    }

    .progress-bar {
        transition: width 1s linear;
    }

    .timeline {
        position: relative;
        padding-left: 20px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 4px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item:last-child .timeline-content::after {
        display: none;
    }

    .pricing-breakdown {
        font-size: 0.95rem;
    }

    @media (max-width: 576px) {
        .countdown-display .time-box {
            min-width: 90px;
        }

        .countdown-display .time-value {
            font-size: 2rem;
        }
    }
</style>
@endsection
