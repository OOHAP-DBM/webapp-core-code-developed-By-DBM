@extends('layouts.customer')

@section('title', 'Booking Summary')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">Booking Summary</h3>
                            <p class="text-muted mb-0">Review your booking details before payment</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning fs-6">Pending Payment</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column: Booking Details -->
                <div class="col-lg-8">
                    <!-- Hoarding Details -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-billboard me-2"></i>Hoarding Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Hoarding Name</label>
                                    <h6 id="hoarding-name">-</h6>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Location</label>
                                    <h6 id="hoarding-location">-</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Size</label>
                                    <h6 id="hoarding-size">-</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Type</label>
                                    <h6 id="hoarding-type">-</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Price/Day</label>
                                    <h6 id="hoarding-price">-</h6>
                                </div>
                            </div>

                            <!-- Hoarding Image -->
                            <div class="mt-3" id="hoarding-image-container">
                                <!-- Image will be loaded dynamically -->
                            </div>
                        </div>
                    </div>

                    <!-- Booking Period -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Booking Period</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Start Date</label>
                                    <h6 id="start-date">-</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">End Date</label>
                                    <h6 id="end-date">-</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Duration</label>
                                    <h6 id="duration">-</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Notes -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-note-sticky me-2"></i>Notes</h6>
                        </div>
                        <div class="card-body">
                            <p id="customer-notes" class="mb-0 text-muted">No additional notes</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Payment Summary -->
                <div class="col-lg-4">
                    <!-- Price Breakdown -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Price Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Base Amount:</span>
                                <strong id="base-amount">₹0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Duration:</span>
                                <strong id="price-duration">0 days</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong id="subtotal">₹0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax (GST):</span>
                                <strong id="tax-amount">₹0.00</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-0">Total Amount:</h5>
                                <h5 class="mb-0 text-success" id="total-amount">₹0.00</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Timer -->
                    <div class="card shadow-sm border-warning mb-4" id="timer-card">
                        <div class="card-body text-center">
                            <h6 class="text-warning mb-3">
                                <i class="fas fa-clock me-2"></i>Complete Payment Within
                            </h6>
                            <div id="countdown-timer" class="display-4 text-danger mb-3">
                                30:00
                            </div>
                            <small class="text-muted">Booking will expire at: <span id="expiry-time">-</span></small>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button 
                            id="proceed-payment-btn" 
                            class="btn btn-primary btn-lg"
                            onclick="initiatePayment()"
                        >
                            <i class="fas fa-lock me-2"></i>Proceed to Payment
                        </button>
                        <a href="{{ route('customer.bookings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                        </a>
                    </div>

                    <!-- Important Info -->
                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Important</h6>
                        <ul class="mb-0 small">
                            <li>Payment must be completed within 30 minutes</li>
                            <li>You can cancel within 30 minutes of payment for full refund</li>
                            <li>Campaign starts when mounter uploads POD and vendor approves</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const bookingId = {{ $booking->id }};
let holdExpiryAt = '{{ $booking->hold_expiry_at }}';
let countdownInterval;

// Load booking details on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBookingDetails();
    startCountdownTimer();
});

function loadBookingDetails() {
    fetch(`/api/v1/customer/direct-bookings/${bookingId}`, {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const booking = data.data.booking;
            const snapshot = JSON.parse(booking.booking_snapshot || '{}');
            
            // Hoarding details
            document.getElementById('hoarding-name').textContent = snapshot.hoarding?.name || '-';
            document.getElementById('hoarding-location').textContent = snapshot.hoarding?.location || '-';
            document.getElementById('hoarding-size').textContent = 
                `${snapshot.hoarding?.width || 0}ft × ${snapshot.hoarding?.height || 0}ft`;
            document.getElementById('hoarding-type').textContent = 
                booking.hoarding?.type?.toUpperCase() || '-';
            document.getElementById('hoarding-price').textContent = 
                `₹${parseFloat(snapshot.hoarding?.price_per_day || 0).toFixed(2)}`;
            
            // Booking period
            document.getElementById('start-date').textContent = 
                new Date(booking.start_date).toLocaleDateString('en-IN');
            document.getElementById('end-date').textContent = 
                new Date(booking.end_date).toLocaleDateString('en-IN');
            document.getElementById('duration').textContent = 
                `${booking.duration_days} days`;
            
            // Notes
            if (booking.customer_notes) {
                document.getElementById('customer-notes').textContent = booking.customer_notes;
                document.getElementById('customer-notes').classList.remove('text-muted');
            }
            
            // Price breakdown
            const pricing = snapshot.pricing || {};
            document.getElementById('base-amount').textContent = 
                `₹${parseFloat(pricing.price_per_day || 0).toFixed(2)}`;
            document.getElementById('price-duration').textContent = 
                `${pricing.duration_days || 0} days`;
            document.getElementById('subtotal').textContent = 
                `₹${parseFloat(pricing.subtotal || 0).toFixed(2)}`;
            document.getElementById('tax-amount').textContent = 
                `₹${parseFloat(pricing.tax_amount || 0).toFixed(2)} (${pricing.tax_rate || 18}%)`;
            document.getElementById('total-amount').textContent = 
                `₹${parseFloat(booking.total_amount).toFixed(2)}`;
            
            // Check if hold expired
            if (data.data.hold_expired) {
                document.getElementById('proceed-payment-btn').disabled = true;
                document.getElementById('proceed-payment-btn').textContent = 'Booking Expired';
                document.getElementById('timer-card').classList.add('border-danger');
                clearInterval(countdownInterval);
            }
        }
    })
    .catch(error => {
        console.error('Error loading booking:', error);
        alert('Failed to load booking details');
    });
}

function startCountdownTimer() {
    const expiryTime = new Date(holdExpiryAt);
    document.getElementById('expiry-time').textContent = expiryTime.toLocaleTimeString('en-IN');
    
    countdownInterval = setInterval(function() {
        const now = new Date();
        const diff = expiryTime - now;
        
        if (diff <= 0) {
            clearInterval(countdownInterval);
            document.getElementById('countdown-timer').textContent = '00:00';
            document.getElementById('proceed-payment-btn').disabled = true;
            document.getElementById('proceed-payment-btn').textContent = 'Booking Expired';
            document.getElementById('timer-card').classList.remove('border-warning');
            document.getElementById('timer-card').classList.add('border-danger');
            return;
        }
        
        const minutes = Math.floor(diff / 1000 / 60);
        const seconds = Math.floor((diff / 1000) % 60);
        
        document.getElementById('countdown-timer').textContent = 
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        // Change color based on time remaining
        if (minutes < 5) {
            document.getElementById('countdown-timer').classList.remove('text-danger');
            document.getElementById('countdown-timer').classList.add('text-danger', 'blink');
        } else if (minutes < 10) {
            document.getElementById('countdown-timer').classList.remove('text-danger');
            document.getElementById('countdown-timer').classList.add('text-warning');
        }
    }, 1000);
}

function initiatePayment() {
    const btn = document.getElementById('proceed-payment-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Initiating Payment...';
    
    fetch(`/api/v1/customer/direct-bookings/${bookingId}/initiate-payment`, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to payment page with Razorpay order details
            window.location.href = `/customer/bookings/${bookingId}/payment?order_id=${data.data.razorpay_order_id}`;
        } else {
            alert(data.message || 'Failed to initiate payment');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to initiate payment');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-2"></i>Proceed to Payment';
    });
}
</script>

<style>
@keyframes blink {
    0%, 50%, 100% { opacity: 1; }
    25%, 75% { opacity: 0.5; }
}

.blink {
    animation: blink 1s ease-in-out infinite;
}
</style>
@endpush
@endsection
