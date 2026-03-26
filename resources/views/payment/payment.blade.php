@extends('layouts.app')
@section('content')
<div class="container" style="max-width:1100px; margin:32px auto; display:flex; gap:32px; align-items:flex-start;">
    <div style="flex:2;">
        <h2>1. Billing Address</h2>
        <div class="card mb-4 p-3">
            <div><strong>Bill to – {{ $billing['full_name'] ?? $user->name }}</strong></div>
            <div>{{ $billing['billing_address'] ?? '' }}</div>
            <div>{{ $billing['city'] ?? '' }}, {{ $billing['state'] ?? '' }} {{ $billing['pincode'] ?? '' }}</div>
            <a href="{{ url()->previous() }}" style="font-size:13px;">edit address</a>
        </div>
        <h2>2. Payments</h2>
        <div class="card p-4">
            <div class="btn-group mb-3" role="group" aria-label="Payment Methods">
                <button type="button" class="btn btn-outline-primary active" id="tab-card">Credit / Debit</button>
                <button type="button" class="btn btn-outline-primary" id="tab-netbanking">Net Banking</button>
                <button type="button" class="btn btn-outline-primary" id="tab-upi">UPI (Pay via any App)</button>
            </div>
            <form id="payment-form">
                <input type="hidden" name="hoarding_id" value="{{ $hoarding->id }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="amount" value="{{ $total }}">
                <div id="card-fields">
                    <div class="mb-2">Enter Card Details</div>
                    <div class="row mb-2">
                        <div class="col"><input type="text" class="form-control" placeholder="Card Number" maxlength="19"></div>
                        <div class="col"><input type="text" class="form-control" placeholder="Name on Card"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col"><input type="text" class="form-control" placeholder="MM/YY"></div>
                        <div class="col"><input type="text" class="form-control" placeholder="CVV"></div>
                    </div>
                </div>
                <div id="netbanking-fields" style="display:none;">
                    <div class="mb-2">Select Bank</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="bank" value="AXIS"> Axis Bank</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="bank" value="HDFC"> HDFC Bank</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="bank" value="ICICI"> ICICI Bank</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="bank" value="SBI"> SBI</div>
                    <select class="form-select mt-2"><option>Other Banks</option></select>
                </div>
                <div id="upi-fields" style="display:none;">
                    <div class="mb-2">Select UPI App</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="upi" value="PhonePe"> PhonePe</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="upi" value="Paytm"> Paytm</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="upi" value="MobiKwik"> MobiKwik</div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="upi" value="other"> Enter UPI ID</div>
                    <input type="text" class="form-control mt-2" placeholder="Enter UPI ID here">
                </div>
                <button type="button" id="pay-btn" class="btn btn-success w-100 mt-3">Pay ₹{{ number_format($total) }}</button>
                <div class="text-muted mt-2" style="font-size:13px;">Encrypted and secure payments</div>
            </form>
        </div>
    </div>
    <div style="flex:1;">
        <div class="card p-4">
            <h5 class="mb-3">Booking Summary</h5>
            <div><strong>{{ $hoarding->title }}</strong></div>
            <div class="text-muted">{{ $hoarding->city ?? '' }}, {{ $hoarding->state ?? '' }}</div>
            <div class="mb-2">OOH | {{ $hoarding->width ?? '' }}*{{ $hoarding->height ?? '' }}sq.ft</div>
            <div>Duration – {{ $duration }} month(s)</div>
            <hr>
            <div class="d-flex justify-content-between"><span>Subtotal</span><span>₹{{ number_format($subtotal) }}</span></div>
            <div class="d-flex justify-content-between text-success"><span>Offer Discount -30%</span><span>-₹{{ number_format($offerDiscount) }}</span></div>
            <div class="d-flex justify-content-between text-success"><span>Coupon Discount</span><span>-₹{{ number_format($couponDiscount) }}</span></div>
            <div class="d-flex justify-content-between"><span>Printing Charge</span><span>₹{{ number_format($printingCharge) }}</span></div>
            <div class="d-flex justify-content-between"><span>Mounting Charge</span><span>₹{{ number_format($mountingCharge) }}</span></div>
            <div class="d-flex justify-content-between"><span>Taxes</span><span>₹{{ number_format($taxes) }}</span></div>
            <hr>
            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>₹{{ number_format($total) }}</span></div>
        </div>
    </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    // Tab switching logic
    document.getElementById('tab-card').onclick = function() {
        this.classList.add('active');
        document.getElementById('tab-netbanking').classList.remove('active');
        document.getElementById('tab-upi').classList.remove('active');
        document.getElementById('card-fields').style.display = '';
        document.getElementById('netbanking-fields').style.display = 'none';
        document.getElementById('upi-fields').style.display = 'none';
    };
    document.getElementById('tab-netbanking').onclick = function() {
        this.classList.add('active');
        document.getElementById('tab-card').classList.remove('active');
        document.getElementById('tab-upi').classList.remove('active');
        document.getElementById('card-fields').style.display = 'none';
        document.getElementById('netbanking-fields').style.display = '';
        document.getElementById('upi-fields').style.display = 'none';
    };
    document.getElementById('tab-upi').onclick = function() {
        this.classList.add('active');
        document.getElementById('tab-card').classList.remove('active');
        document.getElementById('tab-netbanking').classList.remove('active');
        document.getElementById('card-fields').style.display = 'none';
        document.getElementById('netbanking-fields').style.display = 'none';
        document.getElementById('upi-fields').style.display = '';
    };

    document.getElementById('pay-btn').onclick = function() {
        fetch("{{ route('payment.createOrder') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                hoarding_id: document.querySelector('[name=hoarding_id]').value,
                start_date: document.querySelector('[name=start_date]').value,
                end_date: document.querySelector('[name=end_date]').value,
                amount: document.querySelector('[name=amount]').value,
                source: 'draft',
                source_id: '{{ $draft->id ?? $draft_id ?? '' }}'
            })
        })
        .then(response => response.json())
        .then(data => {
            var options = {
                key: data.key,
                amount: data.amount * 100, // in paise
                currency: 'INR',
                name: data.name,
                description: data.description,
                order_id: data.order_id,
                handler: function (response){
                    fetch("{{ route('payment.verify') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            razorpay_order_id: data.order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                            hoarding_id: document.querySelector('[name=hoarding_id]').value,
                            start_date: document.querySelector('[name=start_date]').value,
                            end_date: document.querySelector('[name=end_date]').value
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        if(res.status === 'success') {
                            alert('Payment successful! Booking confirmed.');
                            window.location.href = '/';
                        } else {
                            alert('Payment failed: ' + (res.error || 'Unknown error'));
                        }
                    });
                },
                prefill: {
                    email: '{{ $billing['email'] ?? '' }}',
                    contact: '{{ $billing['mobile'] ?? '' }}',
                    name: '{{ $billing['full_name'] ?? '' }}'
                },
                theme: {
                    color: '#3399cc'
                },
                method: {
                    netbanking: true,
                    card: true,
                    upi: true
                }
            };
            var rzp = new Razorpay(options);
            rzp.open();
        });
    };
</script>
@endsection
