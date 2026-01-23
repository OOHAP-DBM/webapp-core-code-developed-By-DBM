@extends('layouts.vendor')

@section('title', 'POS Booking Details')

@section('content')
<div class="px-6 py-6">
    <div class="bg-white rounded-xl shadow border">

        {{-- Header --}}
        <div class="px-6 py-4 bg-primary text-white rounded-t-xl">
            <h4 class="text-lg font-semibold flex items-center gap-2">
                📄 POS Booking Details
            </h4>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <div id="booking-details" class="text-center text-gray-500">
                Loading booking details...
            </div>
        </div>

    </div>
</div>

<script>
const bookingId = @json($bookingId);

document.addEventListener('DOMContentLoaded', () => {
    fetch(`/api/v1/vendor/pos/bookings/${bookingId}`, {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById('booking-details');

        if (!data.success) {
            container.innerHTML = `
                <div class="text-red-500 font-medium">
                    Booking not found.
                </div>`;
            return;
        }

        const b = data.data;

        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Invoice #:</span>
                    ${b.invoice_number || 'N/A'}
                </div>

                <div>
                    <span class="font-semibold">Status:</span>
                    <span class="ml-2 px-2 py-1 rounded text-xs font-semibold ${getStatusColor(b.status)}">
                        ${b.status}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Customer:</span>
                    ${b.customer_name}
                </div>

                <div>
                    <span class="font-semibold">Phone:</span>
                    ${b.customer_phone}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Hoarding:</span>
                    ${
                        b.hoarding
                        ? `<a href="/hoardings/${b.hoarding.id}" target="_blank"
                             class="text-primary underline">
                             ${b.hoarding.title}
                           </a>`
                        : 'N/A'
                    }
                </div>

                <div>
                    <span class="font-semibold">Dates:</span>
                    ${new Date(b.start_date).toLocaleDateString()}
                    -
                    ${new Date(b.end_date).toLocaleDateString()}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="font-semibold">Total Amount:</span>
                    ₹${parseFloat(b.total_amount).toLocaleString()}
                </div>

                <div>
                    <span class="font-semibold">Payment Status:</span>
                    <span class="ml-2 px-2 py-1 rounded text-xs font-semibold ${getPaymentStatusColor(b.payment_status)}">
                        ${b.payment_status}
                    </span>
                </div>
            </div>

            <div class="mt-4">
                <span class="font-semibold">Notes:</span>
                <div class="mt-1 text-gray-700">
                    ${b.notes || '-'}
                </div>
            </div>
        `;
    })
    .catch(() => {
        document.getElementById('booking-details').innerHTML = `
            <div class="text-red-500 font-medium">
                Error loading booking details.
            </div>`;
    });
});

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
