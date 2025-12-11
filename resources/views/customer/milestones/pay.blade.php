@extends('layouts.customer')

@section('title', 'Pay Milestone')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Milestone Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-credit-card"></i> Milestone Payment</h4>
                </div>
                <div class="card-body">
                    <!-- Status Alert -->
                    @if($milestone->status === 'overdue')
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Payment Overdue!</strong> This payment was due on {{ $milestone->due_date->format('M d, Y') }}
                        ({{ $milestone->due_date->diffForHumans() }}).
                    </div>
                    @elseif($milestone->status === 'due')
                    <div class="alert alert-warning">
                        <i class="bi bi-clock"></i> 
                        <strong>Payment Due:</strong> This payment is due on {{ $milestone->due_date->format('M d, Y') }}.
                    </div>
                    @endif

                    <!-- Milestone Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Milestone Details</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Title:</strong></td>
                                    <td>{{ $milestone->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Quotation ID:</strong></td>
                                    <td>#{{ $milestone->quotation_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Vendor:</strong></td>
                                    <td>{{ $milestone->quotation->enquiry->vendor->name ?? 'N/A' }}</td>
                                </tr>
                                @if($milestone->description)
                                <tr>
                                    <td><strong>Description:</strong></td>
                                    <td>{{ $milestone->description }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Payment Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Amount Type:</strong></td>
                                    <td>{{ $milestone->amount_type === 'percentage' ? 'Percentage' : 'Fixed Amount' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Base Amount:</strong></td>
                                    <td>
                                        @if($milestone->amount_type === 'percentage')
                                            {{ $milestone->amount }}% of ₹{{ number_format($milestone->quotation->grand_total, 2) }}
                                        @else
                                            ₹{{ number_format($milestone->amount, 2) }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Due Date:</strong></td>
                                    <td>{{ $milestone->due_date ? $milestone->due_date->format('M d, Y') : 'Not specified' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Amount Breakdown -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Payment Breakdown</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Milestone Amount:</span>
                                <strong>₹{{ number_format($milestone->calculated_amount, 2) }}</strong>
                            </div>
                            @php
                                $gst = $milestone->calculated_amount * 0.18; // Assuming 18% GST
                                $total = $milestone->calculated_amount + $gst;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <span>GST (18%):</span>
                                <strong>₹{{ number_format($gst, 2) }}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h5>Total Payable:</h5>
                                <h5 class="text-primary">₹{{ number_format($total, 2) }}</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form id="paymentForm" action="{{ route('customer.milestones.process', $milestone->id) }}" method="POST">
                        @csrf
                        
                        <h5 class="mb-3">Payment Method</h5>
                        
                        <!-- Payment Options -->
                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="razorpay" value="razorpay" checked>
                                <label class="form-check-label" for="razorpay">
                                    <i class="bi bi-credit-card text-primary"></i> 
                                    <strong>Razorpay</strong> (Card/UPI/Netbanking/Wallets)
                                </label>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" target="_blank">Terms & Conditions</a> and 
                                <a href="#" target="_blank">Payment Policy</a>
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="payButton">
                                <i class="bi bi-lock"></i> Proceed to Payment - ₹{{ number_format($total, 2) }}
                            </button>
                            <a href="{{ route('customer.milestones.progress', $milestone->quotation_id) }}" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Milestones
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Info -->
            <div class="card border-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="bi bi-shield-check text-success display-4"></i>
                        </div>
                        <div class="col">
                            <h6 class="mb-1">Secure Payment</h6>
                            <p class="text-muted small mb-0">
                                Your payment information is encrypted and secured. We use industry-standard 
                                SSL encryption to protect your data.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const button = document.getElementById('payButton');
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
    
    // Razorpay Integration
    fetch('{{ route('customer.milestones.create-order', $milestone->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const options = {
                key: data.razorpay_key,
                amount: data.amount, // Amount in paise
                currency: 'INR',
                name: 'OohApp',
                description: '{{ $milestone->title }}',
                order_id: data.order_id,
                handler: function(response) {
                    // Payment successful - verify
                    verifyPayment(response);
                },
                prefill: {
                    name: '{{ auth()->user()->name }}',
                    email: '{{ auth()->user()->email }}',
                    contact: '{{ auth()->user()->phone ?? '' }}'
                },
                theme: {
                    color: '#0d6efd'
                },
                modal: {
                    ondismiss: function() {
                        button.disabled = false;
                        button.innerHTML = '<i class="bi bi-lock"></i> Proceed to Payment - ₹{{ number_format($total, 2) }}';
                    }
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        } else {
            alert('Error creating payment order. Please try again.');
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-lock"></i> Proceed to Payment - ₹{{ number_format($total, 2) }}';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-lock"></i> Proceed to Payment - ₹{{ number_format($total, 2) }}';
    });
});

function verifyPayment(response) {
    fetch('{{ route('customer.milestones.verify', $milestone->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_order_id: response.razorpay_order_id,
            razorpay_signature: response.razorpay_signature
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route('customer.milestones.success', $milestone->id) }}';
        } else {
            alert('Payment verification failed. Please contact support.');
        }
    })
    .catch(error => {
        console.error('Verification error:', error);
        alert('An error occurred during verification. Please contact support.');
    });
}
</script>
@endsection
