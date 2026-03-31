@include('vendor.pos.components.pos-timer-notification')
@extends($posLayout ?? 'layouts.vendor')

@section('title', 'Bookings Management')
@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    @include('vendor.pos.components.admin-vendor-switcher')

    <div class="bg-white rounded-md shadow ">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 pt-4 bg-primary rounded-t-xl">
            <div>
                <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    All Bookings
                </h4>
                <p class="text-xs">View, manage, and track all POS bookings and payment statuses.</p>
            </div>

            <a href="{{ route(($posRoutePrefix ?? 'vendor.pos') . '.create') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center btn-color text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg text-sm font-medium transition">
                + Create New Booking
            </a>

        </div>

        <div class="p-4 sm:p-6 space-y-6">

            {{-- Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4">
                <div class="sm:col-span-1 lg:col-span-3 relative">
                    <select id="filter-status"
                        class="appearance-none w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-1 focus:ring-primary focus:outline-none pr-8">
                        <option value="">Booking Status</option>
                        <option value="draft">Draft</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="active">Active</option>
                        <option value="pending_payment">Hold</option>
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
                        <option value="">Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Pending</option>
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
                        placeholder="Search bookings by customer name, phone, or invoice no."
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
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Invoice ID</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Customer Details</th>
                            <th class="text-center px-2 py-2 sm:px-3 sm:py-2">Hoardings Booked</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Booking Date & Time</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Total Amount</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Payment Status</th>
                            <th class="px-2 py-2 sm:px-3 sm:py-2">Booking Status</th>
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

@include('vendor.pos.components.status-formatters')

<script>
/**
 * POS Bookings List - Web Session Auth
 * Uses role-scoped POS bookings endpoint with session auth
 */

const POS_BASE_PATH = @json($posBasePath ?? '/vendor/pos');

let currentPage = 1;
let currentPerPage = 10;
let presetPaymentStatuses = '';

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
    const urlParams = new URLSearchParams(window.location.search);
    const searchInput = document.getElementById('search-box');
    const statusSelect = document.getElementById('filter-status');
    const paymentStatusSelect = document.getElementById('filter-payment-status');

    const initialStatus = (urlParams.get('status') || '').trim();
    const initialPaymentStatus = (urlParams.get('payment_status') || '').trim();
    const initialPaymentStatuses = (urlParams.get('payment_statuses') || '').trim();
    const initialSearch = (urlParams.get('search') || '').trim();
    const initialPerPage = parseInt(urlParams.get('per_page') || '10', 10);

    if (!isNaN(initialPerPage) && [5, 10, 20, 50, 100].includes(initialPerPage)) {
        currentPerPage = initialPerPage;
    }

    if (statusSelect && initialStatus) {
        statusSelect.value = initialStatus;
    }

    if (paymentStatusSelect && initialPaymentStatus) {
        paymentStatusSelect.value = initialPaymentStatus;
    }

    if (initialPaymentStatuses) {
        presetPaymentStatuses = initialPaymentStatuses;
    }

    if (searchInput && initialSearch) {
        searchInput.value = initialSearch;
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', () => loadPosBookings(1));
    }

    if (paymentStatusSelect) {
        paymentStatusSelect.addEventListener('change', () => {
            presetPaymentStatuses = '';
            loadPosBookings(1);
        });
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
    const perPage = Number.isInteger(currentPerPage) ? currentPerPage : 10;

    let url = `${POS_BASE_PATH}/api/bookings?page=${page}&per_page=${perPage}`;
    if (status) url += `&status=${status}`;
    if (paymentStatus) {
        url += `&payment_status=${paymentStatus}`;
    } else if (presetPaymentStatuses) {
        url += `&payment_statuses=${encodeURIComponent(presetPaymentStatuses)}`;
    }
    if (search) url += `&search=${encodeURIComponent(search)}`;

    fetchJSON(url)
        .then(data => {
            const tbody = document.getElementById('bookings-table-body');

            if (data.success && data.data.data.length) {
                tbody.innerHTML = '';

                data.data.data.forEach(booking => {
                tbody.innerHTML += `
                <tr class="hover:bg-gray-50">
                    <td class="px-2 py-2 sm:px-3 sm:py-2 whitespace-nowrap">${booking.invoice_number || 'N/A'}</td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 whitespace-nowrap">
                        <strong>${booking.customer_name}</strong><br>
                        <span class="text-xs text-gray-500">${booking.customer_phone ?? '-'}</span><br>
                        <span class="text-xs text-gray-500">${booking.customer_email ?? '-'}</span>
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 text-center font-bold whitespace-nowrap">
                        ${Array.isArray(booking.booking_hoardings) ? booking.booking_hoardings.length : (Array.isArray(booking.bookingHoardings) ? booking.bookingHoardings.length : 0)}
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 whitespace-nowrap">
                        ${formatDateTime(booking.created_at)}
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 font-medium whitespace-nowrap">
                        ₹${parseFloat(booking.total_amount).toLocaleString()}
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 whitespace-nowrap">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${getPaymentStatusColor(booking.payment_status)}">
                            ${getPaymentStatusLabel(booking.payment_status)}
                        </span>
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 whitespace-nowrap">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${getStatusColor(booking.status)}">
                            ${getBookingStatusLabel(booking.status)}
                        </span>
                    </td>
                    <td class="px-2 py-2 sm:px-3 sm:py-2 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1">
                        <a href="${POS_BASE_PATH}/bookings/${booking.id}"
                           class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition">
                            View Details
                        </a>
                        <!-- BACKEND RULE: Only show Edit if status = draft -->
                        ${booking.status === 'draft' ? `
                            <a href="${POS_BASE_PATH}/bookings/${booking.id}/edit"
                               class="text-xs bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition">
                                Edit
                            </a>
                        ` : ''}
                        <!-- BACKEND RULE: Show Mark Paid if payment_status in [unpaid, partial] AND status != cancelled -->
                        ${['unpaid', 'partial'].includes(booking.payment_status) && booking.status !== 'cancelled' ? `
                            <a href="${POS_BASE_PATH}/bookings/${booking.id}"
                               class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 transition">
                                Mark as Paid
                            </a>
                        ` : ``}
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
            renderPosPagination({
                current_page: 1,
                per_page: perPage,
                total: 0,
                last_page: 1,
            });
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
            document.getElementById('pagination-container').innerHTML = '';
        });
}

function renderPosPagination(pagination) {
    const current = Number(pagination.current_page || 1);
    const last = Number(pagination.last_page || 1);
    const total = Number(pagination.total || 0);
    const perPage = Number(pagination.per_page || currentPerPage || 10);

    const startRecord = total > 0 ? ((current - 1) * perPage) + 1 : 0;
    const endRecord = total > 0 ? Math.min(current * perPage, total) : 0;

    const pages = [];
    if (last <= 3) {
        for (let i = 1; i <= last; i++) {
            pages.push(i);
        }
    } else if (current <= 3) {
        for (let i = 1; i <= 3; i++) {
            pages.push(i);
        }
        pages.push(last);
    } else if (current >= last - 2) {
        pages.push(1);
        for (let i = last - 2; i <= last; i++) {
            pages.push(i);
        }
    } else {
        pages.push(1, current - 1, current, current + 1, last);
    }

    const normalizedPages = [...new Set(
        pages.filter((page) => page >= 1 && page <= last)
    )].sort((a, b) => a - b);

    let pageButtons = '';
    let previousPage = null;

    normalizedPages.forEach((page) => {
        if (previousPage !== null && page - previousPage > 1) {
            pageButtons += '<span class="text-gray-400">...</span>';
        }

        if (page === current) {
            pageButtons += `
                <button onclick="loadPosBookings(${page})"
                    class="h-6 min-w-[36px] px-2 inline-flex items-center justify-center rounded-md bg-[#00A86B] text-white"
                    aria-current="page">
                    ${page}
                </button>`;
        } else {
            pageButtons += `
                <button onclick="loadPosBookings(${page})"
                    class="h-6 min-w-[36px] px-2 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-gray-900"
                    aria-label="Go to page ${page}">
                    ${page}
                </button>`;
        }

        previousPage = page;
    });

    const previousButton = current <= 1
        ? `
            <span class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-400 cursor-not-allowed" aria-hidden="true">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        `
        : `
            <button onclick="loadPosBookings(${Math.max(1, current - 1)})"
                class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-gray-900"
                aria-label="Previous page">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        `;

    const nextButton = current >= last
        ? `
            <span class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-400 cursor-not-allowed" aria-hidden="true">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        `
        : `
            <button onclick="loadPosBookings(${Math.min(last, current + 1)})"
                class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-gray-900"
                aria-label="Next page">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        `;

    const pageNav = last > 1
        ? `
            <nav role="navigation" aria-label="Pagination Navigation" class="overflow-x-auto">
                <div class="inline-flex items-center gap-3 whitespace-nowrap text-sm font-medium select-none">
                    ${previousButton}
                    ${pageButtons}
                    ${nextButton}
                </div>
            </nav>
        `
        : '';

    const html = `
        <div class="bg-white px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-100 text-sm text-gray-600">
            <div class="font-medium">Showing ${startRecord} - ${endRecord} of ${total}</div>
            <div>${pageNav}</div>
        </div>
    `;

    document.getElementById('pagination-container').innerHTML = html;
}

function changePosPerPage(value) {
    const parsed = parseInt(value, 10);
    currentPerPage = [5, 10, 20, 50, 100].includes(parsed) ? parsed : 10;
    loadPosBookings(1);
}

window.loadPosBookings = loadPosBookings;
window.loadBookings = loadPosBookings;
window.changePosPerPage = changePosPerPage;

function getStatusColor(status) {
    return getPosBookingStatusColor(status);
}

function getPaymentStatusColor(status) {
    return getPosPaymentStatusColor(status);
}

function getBookingStatusLabel(status) {
    return getPosBookingStatusLabel(status);
}

function getPaymentStatusLabel(status) {
    return getPosPaymentStatusLabel(status);
}
</script>
@endsection