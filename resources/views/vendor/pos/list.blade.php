@extends('layouts.vendor')

@section('title', 'POS Bookings List')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-list"></i> POS Bookings</h4>
            <a href="{{ route('vendor.pos.create') }}" class="btn btn-light">
                <i class="fas fa-plus"></i> New Booking
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-select" id="filter-status">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filter-payment-status">
                        <option value="">All Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="credit">Credit Note</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="search-box" placeholder="Search by name, phone, invoice...">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" onclick="loadBookings()">Filter</button>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="table-responsive">
                <table class="table table-hover">
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
                    <tbody id="bookings-table-body">
                        <tr>
                            <td colspan="8" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination-container"></div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadBookings();
});

function loadBookings(page = 1) {
    const status = document.getElementById('filter-status').value;
    const paymentStatus = document.getElementById('filter-payment-status').value;
    const search = document.getElementById('search-box').value;

    let url = `/api/v1/vendor/pos/bookings?page=${page}`;
    if (status) url += `&status=${status}`;
    if (paymentStatus) url += `&payment_status=${paymentStatus}`;
    if (search) url += `&search=${search}`;

    fetch(url, {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.data.length > 0) {
            const tbody = document.getElementById('bookings-table-body');
            tbody.innerHTML = '';
            
            data.data.data.forEach(booking => {
                const row = `
                    <tr>
                        <td>${booking.invoice_number || 'N/A'}</td>
                        <td>
                            <strong>${booking.customer_name}</strong><br>
                            <small>${booking.customer_phone}</small>
                        </td>
                        <td>${booking.hoarding ? `<a href="/hoardings/${booking.hoarding.id}" target="_blank">${booking.hoarding.title}</a>` : 'N/A'}</td>
                        <td>
                            ${new Date(booking.start_date).toLocaleDateString()}<br>
                            to ${new Date(booking.end_date).toLocaleDateString()}
                        </td>
                        <td>₹${parseFloat(booking.total_amount).toLocaleString()}</td>
                        <td><span class="badge bg-${getPaymentStatusColor(booking.payment_status)}">${booking.payment_status}</span></td>
                        <td><span class="badge bg-${getStatusColor(booking.status)}">${booking.status}</span></td>
                        <td>
                            <a href="/vendor/pos/bookings/${booking.id}" class="btn btn-sm btn-info">View</a>
                            <a href="/vendor/pos/bookings/${booking.id}/edit" class="btn btn-sm btn-warning">Edit</a>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            // Handle pagination
            renderPagination(data.data);
        } else {
            document.getElementById('bookings-table-body').innerHTML = 
                '<tr><td colspan="8" class="text-center">No bookings found</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('bookings-table-body').innerHTML = 
            '<tr><td colspan="8" class="text-center text-danger">Error loading bookings</td></tr>';
    });
}

function renderPagination(paginationData) {
    const container = document.getElementById('pagination-container');
    let html = '<nav><ul class="pagination justify-content-center">';

    for (let i = 1; i <= paginationData.last_page; i++) {
        html += `<li class="page-item ${i === paginationData.current_page ? 'active' : ''}">
            <a class="page-link" href="#" onclick="loadBookings(${i}); return false;">${i}</a>
        </li>`;
    }

    html += '</ul></nav>';
    container.innerHTML = html;
}

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
