@extends('layouts.vendor')

@section('title', 'POS Bookings Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Bookings</h6>
                    <h2 id="total-bookings">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Revenue</h6>
                    <h2 id="total-revenue">₹0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Pending Payments</h6>
                    <h2 id="pending-payments">₹0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Active Credit Notes</h6>
                    <h2 id="active-credit-notes">0</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('vendor.pos.create') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Create New POS Booking
            </a>
            <a href="{{ route('vendor.pos.list') }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-list"></i> View All Bookings
            </a>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recent POS Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="recent-bookings-table">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Hoarding</th>
                                    <th>Dates</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="recent-bookings-body">
                                <tr>
                                    <td colspan="8" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch dashboard statistics
    fetch('/api/v1/vendor/pos/dashboard', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('total-bookings').textContent = data.data.total_bookings;
            document.getElementById('total-revenue').textContent = '₹' + data.data.total_revenue.toLocaleString();
            document.getElementById('pending-payments').textContent = '₹' + data.data.pending_payments.toLocaleString();
            document.getElementById('active-credit-notes').textContent = data.data.active_credit_notes;
        }
    })
    .catch(error => console.error('Error:', error));

    // Fetch recent bookings
    fetch('/api/v1/vendor/pos/bookings?per_page=10', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.data.length > 0) {
            const tbody = document.getElementById('recent-bookings-body');
            tbody.innerHTML = '';
            data.data.data.forEach(booking => {
                const row = `
                    <tr>
                        <td>${booking.invoice_number || 'N/A'}</td>
                        <td>${booking.customer_name}</td>
                        <td>${booking.hoarding ? booking.hoarding.title : 'N/A'}</td>
                        <td>${new Date(booking.start_date).toLocaleDateString()} - ${new Date(booking.end_date).toLocaleDateString()}</td>
                        <td>₹${parseFloat(booking.total_amount).toLocaleString()}</td>
                        <td><span class="badge bg-${getPaymentStatusColor(booking.payment_status)}">${booking.payment_status}</span></td>
                        <td><span class="badge bg-${getStatusColor(booking.status)}">${booking.status}</span></td>
                        <td>
                            <a href="/vendor/pos/bookings/${booking.id}" class="btn btn-sm btn-info">View</a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        } else {
            document.getElementById('recent-bookings-body').innerHTML = '<tr><td colspan="8" class="text-center">No bookings found</td></tr>';
        }
    })
    .catch(error => console.error('Error:', error));
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
