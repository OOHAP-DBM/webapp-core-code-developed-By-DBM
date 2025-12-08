@extends('layouts.vendor')

@section('title', 'Create POS Booking')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Create New POS Booking</h4>
                </div>
                <div class="card-body">
                    <form id="pos-booking-form">
                        @csrf
                        
                        <!-- Customer Details -->
                        <h5 class="mb-3">Customer Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone *</label>
                                <input type="tel" class="form-control" name="customer_phone" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="customer_email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GSTIN</label>
                                <input type="text" class="form-control" name="customer_gstin" maxlength="15">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="customer_address" rows="2"></textarea>
                        </div>

                        <hr>

                        <!-- Booking Details -->
                        <h5 class="mb-3">Booking Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Booking Type *</label>
                                <select class="form-select" name="booking_type" required>
                                    <option value="ooh">OOH (Hoarding)</option>
                                    <option value="dooh">DOOH (Digital)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Select Hoarding *</label>
                                <select class="form-select" name="hoarding_id" id="hoarding-select" required>
                                    <option value="">-- Search & Select --</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Start Date *</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date *</label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                        </div>

                        <hr>

                        <!-- Pricing -->
                        <h5 class="mb-3">Pricing</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Base Amount *</label>
                                <input type="number" class="form-control" name="base_amount" id="base-amount" step="0.01" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Discount Amount</label>
                                <input type="number" class="form-control" name="discount_amount" id="discount-amount" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <strong>Price Breakdown:</strong><br>
                            Base Amount: ₹<span id="display-base">0.00</span><br>
                            Discount: ₹<span id="display-discount">0.00</span><br>
                            After Discount: ₹<span id="display-after-discount">0.00</span><br>
                            GST (@<span id="gst-rate">18</span>%): ₹<span id="display-gst">0.00</span><br>
                            <strong>Total Amount: ₹<span id="display-total">0.00</span></strong>
                        </div>

                        <hr>

                        <!-- Payment Details -->
                        <h5 class="mb-3">Payment Details</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Mode *</label>
                                <select class="form-select" name="payment_mode" required>
                                    <option value="cash">Cash</option>
                                    <option value="credit_note">Credit Note</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Reference</label>
                                <input type="text" class="form-control" name="payment_reference">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Notes</label>
                            <textarea class="form-control" name="payment_notes" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('vendor.pos.dashboard') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Create Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">POS Settings</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Auto-Approval: <strong id="auto-approval-status">Loading...</strong></li>
                        <li class="list-group-item">Auto-Invoice: <strong id="auto-invoice-status">Loading...</strong></li>
                        <li class="list-group-item">GST Rate: <strong id="gst-rate-display">18%</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('pos-booking-form');
    const baseAmountInput = document.getElementById('base-amount');
    const discountAmountInput = document.getElementById('discount-amount');

    // Load hoardings
    loadHoardings();

    // Auto-calculate pricing
    baseAmountInput.addEventListener('input', calculatePrice);
    discountAmountInput.addEventListener('input', calculatePrice);

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        createBooking();
    });

    function loadHoardings() {
        fetch('/api/v1/vendor/pos/search-hoardings', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('hoarding-select');
                data.data.data.forEach(hoarding => {
                    const option = document.createElement('option');
                    option.value = hoarding.id;
                    option.textContent = `${hoarding.title} - ${hoarding.location_city}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function calculatePrice() {
        const baseAmount = parseFloat(baseAmountInput.value) || 0;
        const discountAmount = parseFloat(discountAmountInput.value) || 0;

        fetch('/api/v1/vendor/pos/calculate-price', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                base_amount: baseAmount,
                discount_amount: discountAmount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('display-base').textContent = data.data.base_amount.toFixed(2);
                document.getElementById('display-discount').textContent = data.data.discount_amount.toFixed(2);
                document.getElementById('display-after-discount').textContent = data.data.amount_after_discount.toFixed(2);
                document.getElementById('display-gst').textContent = data.data.tax_amount.toFixed(2);
                document.getElementById('display-total').textContent = data.data.total_amount.toFixed(2);
                document.getElementById('gst-rate').textContent = data.data.gst_rate;
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function createBooking() {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        fetch('/api/v1/vendor/pos/bookings', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Booking created successfully!');
                window.location.href = '/vendor/pos/bookings/' + data.data.id;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to create booking');
        });
    }
});
</script>
@endsection
