@extends('layouts.vendor')

@section('title', 'POS Bookings Dashboard')

@section('content')
<div class="px-6 py-6 space-y-6">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="rounded-xl bg-blue-600 text-white p-5">
            <h6 class="text-sm opacity-80">Total Bookings</h6>
            <h2 id="total-bookings" class="text-3xl font-semibold mt-2">0</h2>
        </div>

        <div class="rounded-xl bg-green-600 text-white p-5">
            <h6 class="text-sm opacity-80">Total Revenue</h6>
            <h2 id="total-revenue" class="text-3xl font-semibold mt-2">₹0</h2>
        </div>

        <div class="rounded-xl bg-yellow-400 text-gray-900 p-5">
            <h6 class="text-sm opacity-80">Pending Payments</h6>
            <h2 id="pending-payments" class="text-3xl font-semibold mt-2">₹0</h2>
        </div>

        <div class="rounded-xl bg-cyan-600 text-white p-5">
            <h6 class="text-sm opacity-80">Active Credit Notes</h6>
            <h2 id="active-credit-notes" class="text-3xl font-semibold mt-2">0</h2>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-wrap gap-4">
        <a
            href="{{ route('vendor.pos.create') }}"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition"
        >
            ➕ Create New POS Booking
        </a>

        <a
            href="{{ route('vendor.pos.list') }}"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
        >
            📋 View All Bookings
        </a>
    </div>

    <!-- Recent Bookings -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h5 class="text-lg font-semibold">Recent POS Bookings</h5>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice #</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Hoarding</th>
                        <th class="px-4 py-3 text-left">Dates</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Payment</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody id="recent-bookings-body" class="divide-y">
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    fetch('/api/v1/vendor/pos/dashboard', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            totalBookings.textContent = data.data.total_bookings;
            totalRevenue.textContent = '₹' + data.data.total_revenue.toLocaleString();
            pendingPayments.textContent = '₹' + data.data.pending_payments.toLocaleString();
            activeCreditNotes.textContent = data.data.active_credit_notes;
        }
    });

    fetch('/api/v1/vendor/pos/bookings?per_page=10', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const tbody = document.getElementById('recent-bookings-body');

        if (data.success && data.data.data.length) {
            tbody.innerHTML = '';
            data.data.data.forEach(b => {
                tbody.innerHTML += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">${b.invoice_number || 'N/A'}</td>
                    <td class="px-4 py-3">${b.customer_name}</td>
                    <td class="px-4 py-3">
                        ${b.hoarding ? `<a class="text-blue-600 hover:underline" target="_blank" href="/hoardings/${b.hoarding.id}">${b.hoarding.title}</a>` : 'N/A'}
                    </td>
                    <td class="px-4 py-3">
                        ${new Date(b.start_date).toLocaleDateString()} -
                        ${new Date(b.end_date).toLocaleDateString()}
                    </td>
                    <td class="px-4 py-3 font-medium">₹${parseFloat(b.total_amount).toLocaleString()}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${paymentBadge(b.payment_status)}">
                            ${b.payment_status}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full ${statusBadge(b.status)}">
                            ${b.status}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="/vendor/pos/bookings/${b.id}"
                           class="text-blue-600 hover:underline text-sm">
                            View
                        </a>
                    </td>
                </tr>`;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No bookings found</td></tr>`;
        }
    });
});

function statusBadge(status) {
    return {
        draft: 'bg-gray-200 text-gray-700',
        confirmed: 'bg-green-100 text-green-700',
        active: 'bg-blue-100 text-blue-700',
        completed: 'bg-cyan-100 text-cyan-700',
        cancelled: 'bg-red-100 text-red-700'
    }[status] || 'bg-gray-200 text-gray-700';
}

function paymentBadge(status) {
    return {
        paid: 'bg-green-100 text-green-700',
        unpaid: 'bg-red-100 text-red-700',
        partial: 'bg-yellow-100 text-yellow-700',
        credit: 'bg-cyan-100 text-cyan-700'
    }[status] || 'bg-gray-200 text-gray-700';
}
</script>
@endsection
