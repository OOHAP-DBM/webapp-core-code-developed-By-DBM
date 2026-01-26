@extends('layouts.vendor')

@section('title', 'POS Bookings List')

@section('content')
<div class="px-6 py-6">

    <div class="bg-white rounded-xl shadow border">

        {{-- Header --}}
        <div class="flex justify-between items-center px-6 py-4 bg-primary text-white rounded-t-xl">
            <h4 class="text-lg font-semibold flex items-center gap-2">
                📋 POS Bookings
            </h4>

            <a href="{{ route('vendor.pos.create') }}"
                class="bg-yellow-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-yellow-600 transition">
                ➕ New Booking
            </a>

        </div>

        <div class="p-6 space-y-6">

            {{-- Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-3">
                    <select id="filter-status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-primary focus:outline-none">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <select id="filter-payment-status"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-primary focus:outline-none">
                        <option value="">All Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="credit">Credit Note</option>
                    </select>
                </div>

                <div class="md:col-span-4">
                    <input id="search-box" type="text"
                        placeholder="Search by name, phone, invoice..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-primary focus:outline-none">
                </div>

                <div class="md:col-span-2">
                    <button onclick="loadBookings()"
                        class="w-full bg-primary text-white py-2 rounded-lg hover:bg-primary/90 transition">
                        Filter
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto border rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="border px-3 py-2">Invoice #</th>
                            <th class="border px-3 py-2">Customer</th>
                            <th class="border px-3 py-2">Hoarding</th>
                            <th class="border px-3 py-2">Dates</th>
                            <th class="border px-3 py-2">Amount</th>
                            <th class="border px-3 py-2">Payment</th>
                            <th class="border px-3 py-2">Status</th>
                            <th class="border px-3 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-table-body">
                        <tr>
                            <td colspan="8" class="text-center py-6 text-gray-500">
                                Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div id="pagination-container" class="mt-6"></div>

        </div>
    </div>
</div>

<script>
let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => loadBookings());

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
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('bookings-table-body');

        if (data.success && data.data.data.length) {
            tbody.innerHTML = '';

            data.data.data.forEach(booking => {
                tbody.innerHTML += `
                <tr class="hover:bg-gray-50">
                    <td class="border px-3 py-2">${booking.invoice_number || 'N/A'}</td>
                    <td class="border px-3 py-2">
                        <strong>${booking.customer_name}</strong><br>
                        <span class="text-xs text-gray-500">${booking.customer_phone}</span>
                    </td>
                    <td class="border px-3 py-2">
                        ${booking.hoarding
                            ? `<a href="/hoardings/${booking.hoarding.id}" target="_blank"
                                 class="text-primary underline">${booking.hoarding.title}</a>`
                            : 'N/A'}
                    </td>
                    <td class="border px-3 py-2">
                        ${new Date(booking.start_date).toLocaleDateString()}<br>
                        <span class="text-xs text-gray-500">
                            to ${new Date(booking.end_date).toLocaleDateString()}
                        </span>
                    </td>
                    <td class="border px-3 py-2 font-medium">
                        ₹${parseFloat(booking.total_amount).toLocaleString()}
                    </td>
                    <td class="border px-3 py-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${getPaymentStatusColor(booking.payment_status)}">
                            ${booking.payment_status}
                        </span>
                    </td>
                    <td class="border px-3 py-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${getStatusColor(booking.status)}">
                            ${booking.status}
                        </span>
                    </td>
                    <td class="border px-3 py-2 flex gap-2">
                        <a href="/vendor/pos/bookings/${booking.id}"
                           class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">
                            View
                        </a>
                        <a href="/vendor/pos/bookings/${booking.id}/edit"
                           class="text-xs bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition">
                            Edit
                        </a>
                    </td>
                </tr>`;
            });

            renderPagination(data.data);
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">
                        No bookings found
                    </td>
                </tr>`;
        }
    })
    .catch(() => {
        document.getElementById('bookings-table-body').innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-6 text-red-500">
                    Error loading bookings
                </td>
            </tr>`;
    });
}

function renderPagination(pagination) {
    let html = '<div class="flex justify-center gap-2">';

    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
            <button onclick="loadBookings(${i})"
                class="px-3 py-1 rounded border text-sm
                ${i === pagination.current_page
                    ? 'bg-primary text-white'
                    : 'bg-white hover:bg-gray-100'}">
                ${i}
            </button>`;
    }

    html += '</div>';
    document.getElementById('pagination-container').innerHTML = html;
}

function getStatusColor(status) {
    return {
        draft: 'bg-gray-400 text-white',
        confirmed: 'bg-green-500 text-white',
        active: 'bg-blue-500 text-white',
        completed: 'bg-cyan-500 text-white',
        cancelled: 'bg-red-500 text-white'
    }[status] || 'bg-gray-400 text-white';
}

function getPaymentStatusColor(status) {
    return {
        paid: 'bg-green-500 text-white',
        unpaid: 'bg-red-500 text-white',
        partial: 'bg-yellow-500 text-white',
        credit: 'bg-cyan-500 text-white'
    }[status] || 'bg-gray-400 text-white';
}
</script>
@endsection
