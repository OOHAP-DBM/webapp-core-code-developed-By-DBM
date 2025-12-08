@extends('layouts.customer')

@section('title', 'Payment Successful')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Animation -->
            <div class="text-center mb-4">
                <div class="success-checkmark">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
                <h2 class="text-success mt-4">Payment Successful!</h2>
                <p class="text-muted">Your booking has been confirmed</p>
            </div>

            <!-- Booking Confirmation Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Booking Confirmed</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Booking ID</label>
                            <h6 id="booking-id">#{{ $booking->id }}</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <h6><span class="badge bg-success">Confirmed</span></h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Hoarding</label>
                            <h6 id="hoarding-name">-</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Location</label>
                            <h6 id="hoarding-location">-</h6>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Start Date</label>
                            <h6 id="start-date">-</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">End Date</label>
                            <h6 id="end-date">-</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Duration</label>
                            <h6 id="duration">-</h6>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Payment ID</label>
                            <h6 id="payment-id" class="font-monospace">-</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Amount Paid</label>
                            <h5 class="text-success" id="amount-paid">₹0.00</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancellation Info -->
            <div class="card shadow-sm border-warning mb-4" id="refund-info-card">
                <div class="card-body">
                    <h6 class="text-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i>Cancellation Policy
                    </h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0">You can cancel this booking with <strong>full refund</strong> within:</p>
                        </div>
                        <div>
                            <h4 class="text-warning mb-0" id="refund-timer">30:00</h4>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-warning" id="refund-progress" role="progressbar" style="width: 100%"></div>
                    </div>
                    <small class="text-muted">Refund window expires at: <span id="refund-expiry-time">-</span></small>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>What Happens Next?</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-marker">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Payment Received</h6>
                                <p class="text-muted small mb-0">Your payment has been successfully processed</p>
                            </div>
                        </div>

                        <div class="timeline-item pending">
                            <div class="timeline-marker">
                                <i class="fas fa-2"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Booking Period Starts</h6>
                                <p class="text-muted small mb-0">On <strong id="campaign-start-date">-</strong></p>
                            </div>
                        </div>

                        <div class="timeline-item pending">
                            <div class="timeline-marker">
                                <i class="fas fa-3"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Mounter Uploads POD</h6>
                                <p class="text-muted small mb-0">Proof of Display (photos/videos) will be uploaded</p>
                            </div>
                        </div>

                        <div class="timeline-item pending">
                            <div class="timeline-marker">
                                <i class="fas fa-4"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Vendor Approves</h6>
                                <p class="text-muted small mb-0">Campaign officially starts after vendor approval</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2 mb-4">
                <a href="/customer/bookings/{{ $booking->id }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-eye me-2"></i>View Booking Details
                </a>
                <button class="btn btn-outline-danger" id="cancel-btn" onclick="showCancelModal()">
                    <i class="fas fa-times me-2"></i>Cancel Booking (Full Refund)
                </button>
                <a href="{{ route('customer.bookings.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-list me-2"></i>View All Bookings
                </a>
            </div>

            <!-- Contact Info -->
            <div class="alert alert-info">
                <h6 class="alert-heading"><i class="fas fa-headset me-2"></i>Need Help?</h6>
                <p class="mb-0">Contact our support team at <strong>support@oohplatform.com</strong> or call <strong>1800-XXX-XXXX</strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Cancel Booking</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this booking?</p>
                <p class="text-success"><strong>Full refund will be processed automatically.</strong></p>
                
                <div class="mb-3">
                    <label for="cancellation-reason" class="form-label">Reason for cancellation (required)</label>
                    <textarea 
                        class="form-control" 
                        id="cancellation-reason" 
                        rows="3" 
                        placeholder="Please tell us why you're cancelling..."
                        required
                    ></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Booking</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancellation()">
                    Confirm Cancellation
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const bookingId = {{ $booking->id }};
let paymentCapturedAt = '{{ $booking->payment_captured_at }}';
let refundInterval;
let cancelModal;

document.addEventListener('DOMContentLoaded', function() {
    loadBookingDetails();
    startRefundTimer();
    
    // Initialize modal
    cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
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
            
            // Populate details
            document.getElementById('hoarding-name').textContent = snapshot.hoarding?.name || '-';
            document.getElementById('hoarding-location').textContent = snapshot.hoarding?.location || '-';
            document.getElementById('start-date').textContent = 
                new Date(booking.start_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
            document.getElementById('end-date').textContent = 
                new Date(booking.end_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
            document.getElementById('duration').textContent = `${booking.duration_days} days`;
            document.getElementById('payment-id').textContent = booking.razorpay_payment_id || '-';
            document.getElementById('amount-paid').textContent = `₹${parseFloat(booking.total_amount).toFixed(2)}`;
            document.getElementById('campaign-start-date').textContent = 
                new Date(booking.start_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' });
            
            // Check if can cancel with refund
            if (!data.data.can_cancel_with_refund) {
                document.getElementById('refund-info-card').style.display = 'none';
                document.getElementById('cancel-btn').disabled = true;
                document.getElementById('cancel-btn').textContent = 'Refund Window Expired';
            }
        }
    });
}

function startRefundTimer() {
    const capturedAt = new Date(paymentCapturedAt);
    const expiryTime = new Date(capturedAt.getTime() + 30 * 60 * 1000); // 30 minutes
    
    document.getElementById('refund-expiry-time').textContent = expiryTime.toLocaleTimeString('en-IN');
    
    refundInterval = setInterval(function() {
        const now = new Date();
        const diff = expiryTime - now;
        
        if (diff <= 0) {
            clearInterval(refundInterval);
            document.getElementById('refund-timer').textContent = '00:00';
            document.getElementById('refund-info-card').classList.remove('border-warning');
            document.getElementById('refund-info-card').classList.add('border-secondary');
            document.getElementById('cancel-btn').disabled = true;
            document.getElementById('cancel-btn').textContent = 'Refund Window Expired';
            return;
        }
        
        const minutes = Math.floor(diff / 1000 / 60);
        const seconds = Math.floor((diff / 1000) % 60);
        
        document.getElementById('refund-timer').textContent = 
            `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        
        // Update progress bar
        const percentage = (diff / (30 * 60 * 1000)) * 100;
        document.getElementById('refund-progress').style.width = percentage + '%';
        
        // Change color as time decreases
        if (percentage < 20) {
            document.getElementById('refund-progress').classList.remove('bg-warning');
            document.getElementById('refund-progress').classList.add('bg-danger');
        }
    }, 1000);
}

function showCancelModal() {
    cancelModal.show();
}

function confirmCancellation() {
    const reason = document.getElementById('cancellation-reason').value.trim();
    
    if (!reason) {
        alert('Please provide a reason for cancellation');
        return;
    }
    
    // Disable button
    event.target.disabled = true;
    event.target.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cancelling...';
    
    fetch(`/api/v1/customer/direct-bookings/${bookingId}/cancel`, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cancellation_reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cancelModal.hide();
            alert(data.message);
            window.location.href = '/customer/bookings';
        } else {
            alert(data.message || 'Failed to cancel booking');
            event.target.disabled = false;
            event.target.innerHTML = 'Confirm Cancellation';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to cancel booking');
        event.target.disabled = false;
        event.target.innerHTML = 'Confirm Cancellation';
    });
}
</script>

<style>
.success-checkmark {
    width: 120px;
    height: 120px;
    margin: 0 auto;
}

.check-icon {
    width: 120px;
    height: 120px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #28a745;
}

.check-icon::before {
    top: 3px;
    left: -2px;
    width: 50px;
    transform-origin: 100% 50%;
    border-radius: 100px 0 0 100px;
}

.check-icon::after {
    top: 0;
    left: 50px;
    width: 70px;
    transform-origin: 0 50%;
    border-radius: 0 100px 100px 0;
    animation: rotate-circle 4.25s ease-in;
}

.icon-line {
    height: 5px;
    background-color: #28a745;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.icon-line.line-tip {
    top: 56px;
    left: 25px;
    width: 32px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.icon-line.line-long {
    top: 50px;
    right: 18px;
    width: 60px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(40, 167, 69, .5);
}

.icon-fix {
    top: 12px;
    width: 10px;
    left: 32px;
    z-index: 1;
    height: 100px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: white;
}

@keyframes rotate-circle {
    0% { transform: rotate(-45deg); }
    5% { transform: rotate(-45deg); }
    12% { transform: rotate(-405deg); }
    100% { transform: rotate(-405deg); }
}

@keyframes icon-line-tip {
    0% { width: 0; left: 10px; top: 26px; }
    54% { width: 0; left: 10px; top: 26px; }
    70% { width: 62px; left: -14px; top: 56px; }
    84% { width: 30px; left: 26px; top: 56px; }
    100% { width: 32px; left: 25px; top: 56px; }
}

@keyframes icon-line-long {
    0% { width: 0; right: 56px; top: 60px; }
    65% { width: 0; right: 56px; top: 60px; }
    84% { width: 72px; right: 0px; top: 50px; }
    100% { width: 60px; right: 18px; top: 50px; }
}

.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -25px;
    top: 30px;
    width: 2px;
    height: 100%;
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -36px;
    top: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
    z-index: 1;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
    color: white;
}

.timeline-item.pending .timeline-marker {
    background: #6c757d;
    color: white;
}

.timeline-content h6 {
    font-weight: 600;
}
</style>
@endpush
@endsection
