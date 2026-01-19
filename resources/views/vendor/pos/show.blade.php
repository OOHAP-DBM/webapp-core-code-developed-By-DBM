@extends('layouts.vendor')

@section('title', 'POS Booking Details')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-file-invoice"></i> POS Booking Details</h4>
        </div>
        <div class="card-body">
            <div id="booking-details">
                <div class="text-center text-muted">Loading booking details...</div>
            </div>
        </div>
    </div>
</div>
<script>
const bookingId = @json($bookingId);
document.addEventListener('DOMContentLoaded', function() {
    fetch(`/api/v1/vendor/pos/bookings/${bookingId}`, {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const b = data.data;
            let html = `<div class='row mb-3'>
                <div class='col-md-6'><strong>Invoice #:</strong> ${b.invoice_number || 'N/A'}</div>
                <div class='col-md-6'><strong>Status:</strong> <span class='badge bg-${getStatusColor(b.status)}'>${b.status}</span></div>
            </div>`;
            html += `<div class='row mb-3'>
                <div class='col-md-6'><strong>Customer:</strong> ${b.customer_name}</div>
                <div class='col-md-6'><strong>Phone:</strong> ${b.customer_phone}</div>
            </div>`;
            html += `<div class='row mb-3'>
                <div class='col-md-6'><strong>Hoarding:</strong> ${b.hoarding ? `<a href='/hoardings/${b.hoarding.id}' target='_blank'>${b.hoarding.title}</a>` : 'N/A'}</div>
                <div class='col-md-6'><strong>Dates:</strong> ${new Date(b.start_date).toLocaleDateString()} - ${new Date(b.end_date).toLocaleDateString()}</div>
            </div>`;
            html += `<div class='row mb-3'>
                <div class='col-md-6'><strong>Total Amount:</strong> ₹${parseFloat(b.total_amount).toLocaleString()}</div>
                <div class='col-md-6'><strong>Payment Status:</strong> <span class='badge bg-${getPaymentStatusColor(b.payment_status)}'>${b.payment_status}</span></div>
            </div>`;
            html += `<div class='row mb-3'>
                <div class='col-md-12'><strong>Notes:</strong> ${b.notes || '-'}</div>
            </div>`;
            document.getElementById('booking-details').innerHTML = html;
        } else {
            document.getElementById('booking-details').innerHTML = `<div class='text-danger'>Booking not found.</div>`;
        }
    })
    .catch(error => {
        document.getElementById('booking-details').innerHTML = `<div class='text-danger'>Error loading booking details.</div>`;
    });
});
function getStatusColor(status) {
    const colors = {
        'draft': 'secondary',
        'confirmed': 'success',
        'active': 'primary',
        'completed': 'info',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}
function getPaymentStatusColor(status) {
    const colors = {
        'paid': 'success',
        'unpaid': 'danger',
        'partial': 'warning',
        'credit': 'info'
    };
    return colors[status] || 'secondary';
}
</script>
@endsection
