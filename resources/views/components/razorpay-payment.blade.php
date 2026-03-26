<!-- Razorpay Payment Component -->
@props(['amount', 'orderId', 'userEmail', 'userName', 'userPhone', 'route'])

<button id="pay-btn-{{ $orderId }}" class="btn btn-primary">Pay with Razorpay</button>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('pay-btn-{{ $orderId }}').onclick = function(e){
    e.preventDefault();
    var options = {
        "key": "{{ config('services.razorpay.key') }}",
        "amount": "{{ $amount * 100 }}",
        "currency": "INR",
        "name": "{{ $userName }}",
        "description": "Payment",
        "order_id": "{{ $orderId }}",
        "handler": function (response){
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
            document.getElementById('razorpay_signature').value = response.razorpay_signature;
            document.getElementById('razorpay-payment-form-{{ $orderId }}').submit();
        },
        "prefill": {
            "name": "{{ $userName }}",
            "email": "{{ $userEmail }}",
            "contact": "{{ $userPhone }}"
        },
        "theme": {
            "color": "#3399cc"
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
};
</script>

<form id="razorpay-payment-form-{{ $orderId }}" action="{{ $route }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
</form>
