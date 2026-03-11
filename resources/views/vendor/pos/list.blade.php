@extends($posLayout ?? 'layouts.vendor')

@section('title', 'POS Bookings List')
@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    @include('vendor.pos.components.admin-vendor-switcher')

    <div class="bg-white rounded-md shadow ">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                 POS Bookings
            </h4>

            <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.create') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center btn-color text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg text-sm font-medium transition">
                + New Booking
            </a>

        </div>

        <div class="p-4 sm:p-6 space-y-6">

            {{-- Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                <div class="sm:col-span-1 lg:col-span-3 relative">
                    <select id="filter-status"
                        class="appearance-none w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-primary focus:outline-none pr-8">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </div>

                <div class="sm:col-span-1 lg:col-span-3 relative">
                    <select id="filter-payment-status"
                        class="appearance-none w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-primary focus:outline-none pr-8">
                        <option value="">All Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="credit">Credit Note</option>
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <input id="search-box" type="text"
                        placeholder="Search by name, phone, invoice..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-primary focus:outline-none">
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <button onclick="loadPosBookings()"
                        class="w-full bg-primary text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg hover:bg-primary/90 transition">
                        Filter
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto  rounded-lg shadow">
                <table class="min-w-[880px] sm:min-w-full text-xs sm:text-sm">
                    <thead class="bg-gray-100 text-left">
                        <tr>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Invoice #</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Customer</th>
                            <th class="text-center px-2 py-2 sm:px-3 sm:py-2">Total Hoardings</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Booking Date</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Amount</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Payment</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Status</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-table-body">
                        <tr>
                            <td colspan="8" class="text-center pt-5 text-gray-500">
                                Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div id="pagination-container" class="mt-6 overflow-x-auto"></div>

        </div>
    </div>
</div>

<script>
/**
 * POS Bookings List - Web Session Auth
 * Uses role-scoped POS bookings endpoint with session auth
 */

const POS_BASE_PATH = @json($posBasePath ?? '/vendor/pos');

let currentPage = 1;

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

// Helper: Format date to DD/MM/YYYY HH:mm
function formatDateTime(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    if (isNaN(date.getTime())) return dateStr;
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hour = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');
    
    return `${day}/${month}/${year} ${hour}:${minute}`;
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-box');
    const statusSelect = document.getElementById('filter-status');
    const paymentStatusSelect = document.getElementById('filter-payment-status');

    if (statusSelect) {
        statusSelect.addEventListener('change', () => loadPosBookings(1));
    }

    if (paymentStatusSelect) {
        paymentStatusSelect.addEventListener('change', () => loadPosBookings(1));
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                loadPosBookings(1);
            }
        });
    }

    loadPosBookings();
});

function loadPosBookings(page = 1) {
    currentPage = page;
    const status = document.getElementById('filter-status').value;
    const paymentStatus = document.getElementById('filter-payment-status').value;
    const search = (document.getElementById('search-box').value || '').trim();

    let url = `${POS_BASE_PATH}/api/bookings?page=${page}&per_page=10`;
    if (status) url += `&status=${status}`;
    if (paymentStatus) url += `&payment_status=${paymentStatus}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    fetchJSON(url)
        .then(data => {
            const tbody = document.getElementById('bookings-table-body');

            if (data.success && data.data.data.length) {
                tbody.innerHTML = '';

                data.data.data.forEach(booking => {
                tbody.innerHTML += `
                <tr class="hidden sm:table-row hover:bg-gray-50">
                    <td class="px-2 py-2 sm:px-3 sm:py-2">${booking.invoice_number || 'N/A'}</td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2">
                        <strong>${booking.customer_name}</strong><br>
                        <span class="text-xs text-gray-500">${booking.customer_phone ?? '-'}</span>
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center font-bold">
                        ${Array.isArray(booking.booking_hoardings) ? booking.booking_hoardings.length : (Array.isArray(booking.bookingHoardings) ? booking.bookingHoardings.length : 0)}
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2">
                        ${formatDateTime(booking.created_at)}
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 font-medium">
                        ₹${parseFloat(booking.total_amount).toLocaleString()}
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${getPaymentStatusColor(booking.payment_status)}">
                            ${booking.payment_status}
                        </span>
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${getStatusColor(booking.status)}">
                            ${booking.status}
                        </span>
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2">
                    <div class="flex flex-wrap gap-1">
                        <a href="${POS_BASE_PATH}/bookings/${booking.id}"
                           class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">
                            View
                        </a>
                        <!-- BACKEND RULE: Only show Edit if status = draft -->
                        ${booking.status === 'draft' ? `
                            <a href="${POS_BASE_PATH}/bookings/${booking.id}/edit"
                               class="text-xs bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition">
                                Edit
                            </a>
                        ` : `
                            <button disabled 
                                class="text-xs bg-gray-300 text-gray-500 px-2 py-1 rounded cursor-not-allowed"
                                title="Can only edit draft bookings">
                                Edit
                            </button>
                        `}
                        <!-- BACKEND RULE: Show Mark Paid if payment_status in [unpaid, partial] AND status != cancelled -->
                        ${['unpaid', 'partial'].includes(booking.payment_status) && booking.status !== 'cancelled' ? `
                            <a href="${POS_BASE_PATH}/bookings/${booking.id}"
                               class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 transition">
                                Mark Paid
                            </a>
                        ` : ``}
                    </div>
                    </td>
                </tr>
                <tr class="sm:hidden border-b border-gray-100">
                    <td colspan="8" class="px-3 py-3">
                        <div class="rounded-lg border border-gray-200 p-3 space-y-2 bg-white">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-gray-900">${booking.invoice_number || 'N/A'}</p>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold ${getStatusColor(booking.status)}">${booking.status}</span>
                            </div>
                            <p class="text-xs text-gray-700 font-medium">${booking.customer_name}</p>
                            <p class="text-[11px] text-gray-500">${booking.customer_phone ?? '-'}</p>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold">₹${parseFloat(booking.total_amount).toLocaleString()}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold ${getPaymentStatusColor(booking.payment_status)}">${booking.payment_status}</span>
                            </div>
                            <p class="text-[11px] text-gray-500">${formatDateTime(booking.created_at)}</p>
                            <div class="flex flex-wrap gap-1 pt-1">
                                <a href="${POS_BASE_PATH}/bookings/${booking.id}" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">View</a>
                                ${booking.status === 'draft' ? `
                                    <a href="${POS_BASE_PATH}/bookings/${booking.id}/edit" class="text-xs bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition">Edit</a>
                                ` : `
                                    <button disabled class="text-xs bg-gray-300 text-gray-500 px-2 py-1 rounded cursor-not-allowed" title="Can only edit draft bookings">Edit</button>
                                `}
                                ${['unpaid', 'partial'].includes(booking.payment_status) && booking.status !== 'cancelled' ? `
                                    <a href="${POS_BASE_PATH}/bookings/${booking.id}" class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 transition">Mark Paid</a>
                                ` : ``}
                            </div>
                        </div>
                    </td>
                </tr>`;
            });

            renderPosPagination(data.data);
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">
                        No bookings found
                    </td>
                </tr>`;
        }
        })
        .catch(err => {
            console.warn('Error loading bookings:', err);
            document.getElementById('bookings-table-body').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-6 text-red-500">
                        Error loading bookings
                    </td>
                </tr>`;
        });
}

function renderPosPagination(pagination) {
    let html = '<div class="flex flex-wrap justify-center gap-2">';

    for (let i = 1; i <= pagination.last_page; i++) {
        html += `
            <button onclick="loadPosBookings(${i})"
                class="px-3 py-1 rounded border text-sm
                ${i === pagination.current_page
                    ? 'bg-primary'
                    : 'bg-white hover:bg-gray-100'}">
                ${i}
            </button>`;
    }

    html += '</div>';
    document.getElementById('pagination-container').innerHTML = html;
}

window.loadPosBookings = loadPosBookings;
window.loadBookings = loadPosBookings;

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