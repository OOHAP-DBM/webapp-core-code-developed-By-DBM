@extends('layouts.vendor')

@section('title', 'POS Dashboard')

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Poppins', sans-serif; }
</style>

@section('content')
<div class="px-6 py-6 space-y-8 bg-gray-50 min-h-screen">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-[#1D1D1D]">Welcome,Vendor</h2>
            <p class="text-sm text-gray-500 font-medium">POS Dashboard</p>
        </div>
        <button class="bg-[#1D1D1D] text-white px-6 py-2 rounded shadow-sm text-sm font-medium">POS</button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

       <div class="bg-[#4891EF] text-white rounded-xl p-5">
            <p class="text-sm opacity-80">Total Bookings</p>
            <h3 id="total-bookings" class="text-3xl font-semibold mt-2">0</h3>
        </div>
        <div class="bg-[#8153FF] text-white rounded-xl p-5">
            <p class="text-sm opacity-80">Total Revenue</p>
            <h3 id="total-revenue" class="text-3xl font-semibold mt-2">₹0</h3>
        </div>

        <div class="bg-[#2CB67D] text-white rounded-xl p-5">
            <p class="text-sm opacity-80">Pending Payments</p>
            <h3 id="pending-payments" class="text-3xl font-semibold mt-2">₹0</h3>
        </div>

        <div class="bg-slate-800 text-white rounded-xl p-5">
            <p class="text-sm opacity-80">Total Customers</p>
            <h3 id="total-customers" class="text-3xl font-semibold mt-2">0</h3>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('vendor.pos.create') }}"
           class="px-5 py-2.5 rounded-md bg-black text-white text-sm hover:bg-gray-800 transition">
            + Create POS Booking
        </a>

        <a href="{{ route('vendor.pos.list') }}"
           class="px-5 py-2.5 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100 transition">
            View All Bookings
        </a>
    </div>

    <!-- Recent POS Bookings -->
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b">
            <h4 class="font-semibold text-gray-800">Recent POS Bookings</h4>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Hoarding</th>
                        <th class="px-4 py-3 text-left">Dates</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Payment</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Hold</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody id="recent-bookings-body" class="divide-y bg-white">
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-gray-500">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pending Payments -->
    <div id="pending-payments-widget"
         class="bg-white rounded-xl border border-gray-200 hidden">
        <div class="px-6 py-4 border-b bg-yellow-50">
            <h4 class="font-semibold text-yellow-800">Pending Payments</h4>
            <p class="text-sm text-yellow-700">
                Bookings requiring payment confirmation
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left">Amount</th>
                        <th class="px-4 py-3 text-left">Pending Since</th>
                        <th class="px-4 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody id="pending-payments-body" class="divide-y bg-white">
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
/**
 * POS Dashboard - Web Session Auth
 * Uses new /vendor/pos/api/* endpoints with session auth
 * No tokens, credentials: 'same-origin'
 */

document.addEventListener('DOMContentLoaded', function () {

    // Helper: Fetch with session auth
    const fetchJSON = async (url) => {
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (!res.ok) {
            throw { status: res.status, message: 'Failed to fetch' };
        }

        return res.json();
    };

    // Helper: Format date to DD/MM/YYYY
    function formatDateDDMMYYYY(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date.getTime())) return dateStr;
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        
        return `${day}/${month}/${year}`;
    }

    // Load dashboard statistics
    fetchJSON('/vendor/pos/api/dashboard')
        .then(data => {
            if (data.success) {
                document.getElementById('total-bookings').textContent = data.data.total_bookings;
                document.getElementById('total-revenue').textContent = '₹' + data.data.total_revenue.toLocaleString();
                document.getElementById('pending-payments').textContent = '₹' + data.data.pending_payments.toLocaleString();
                document.getElementById('total-customers').textContent = data.data.total_customers;
            }
        })
        .catch(err => console.warn('Could not load dashboard stats:', err));

    // Load recent bookings
    fetchJSON('/vendor/pos/api/bookings?per_page=10')
        .then(data => {
            const tbody = document.getElementById('recent-bookings-body');

            if (data.success && data.data.data.length) {
                tbody.innerHTML = '';
                data.data.data.forEach(b => {
                    // Calculate hold expiry display
                    let holdExpiryDisplay = '-';
                    let holdExpiryClass = '';
                    
                    if (b.hold_expiry_at) {
                        const holdExpiry = new Date(b.hold_expiry_at);
                        const now = new Date();
                        const diff = holdExpiry - now;

                        if (diff > 0) {
                            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            holdExpiryDisplay = `In ${days}d ${hours}h`;
                            holdExpiryClass = diff < (12 * 60 * 60 * 1000) ? 'text-red-600 font-semibold' : 'text-yellow-600';
                        } else {
                            holdExpiryDisplay = 'EXPIRED!';
                            holdExpiryClass = 'text-red-600 font-semibold bg-red-50 px-2 py-1 rounded';
                        }
                    }

                    tbody.innerHTML += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">${b.invoice_number || 'N/A'}</td>
                        <td class="px-4 py-3">${b.customer_name}</td>
                    <td class="px-4 py-3">
                        ${b.hoarding ? `<a class="text-blue-600 hover:underline" target="_blank" href="/hoardings/${b.hoarding.id}">${b.hoarding.title}</a>` : 'N/A'}
                    </td>
                    <td class="px-4 py-3">
                        ${formatDateDDMMYYYY(b.start_date)} -
                        ${formatDateDDMMYYYY(b.end_date)}
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
                    <td class="px-4 py-3 ${holdExpiryClass}">
                        ${holdExpiryDisplay}
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
            tbody.innerHTML = `<tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">No bookings found</td></tr>`;
        }
    })
        .catch(err => console.warn('Could not load bookings:', err));

    // Load pending payments (bookings with active holds)
    fetchJSON('/vendor/pos/api/pending-payments')
        .then(data => {
            if (data.success && data.data.length > 0) {
                const widget = document.getElementById('pending-payments-widget');
                const tbody = document.getElementById('pending-payments-body');
                widget.classList.remove('hidden');

                tbody.innerHTML = '';
                data.data.forEach(b => {
                    // Calculate days pending
                    const createdDate = new Date(b.created_at);
                    const now = new Date();
                    const daysPending = Math.floor((now - createdDate) / (1000 * 60 * 60 * 24));
                    
                    let daysText = `${daysPending} day${daysPending !== 1 ? 's' : ''} pending`;
                    let rowClass = daysPending > 7 ? 'bg-red-100 border-l-4 border-red-600' : (daysPending > 3 ? 'bg-red-50' : 'bg-yellow-50');

                    tbody.innerHTML += `
                    <tr class="${rowClass}">
                        <td class="px-4 py-3 font-medium">${b.invoice_number || 'N/A'}</td>
                        <td class="px-4 py-3">${b.customer_name}</td>
                        <td class="px-4 py-3 font-semibold">₹${parseFloat(b.total_amount).toLocaleString()}</td>
                        <td class="px-4 py-3 font-semibold text-red-600">${daysText}</td>
                        <td class="px-4 py-3">
                            <a href="/vendor/pos/bookings/${b.id}"
                               class="text-blue-600 hover:underline text-sm font-medium">
                                View & Mark Paid
                            </a>
                        </td>
                    </tr>`;
                });
            }
        })
        .catch(err => console.warn('Could not load pending payments:', err));
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