<script>
if (typeof window.humanizePosStatus !== 'function') {
    window.humanizePosStatus = function(status) {
        return String(status || 'N/A')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, function(char) { return char.toUpperCase(); });
    };
}

if (typeof window.getPosBookingStatusLabel !== 'function') {
    window.getPosBookingStatusLabel = function(status) {
        const labels = {
            pending_payment: 'HOLD'
        };

        return labels[status] || window.humanizePosStatus(status);
    };
}

if (typeof window.getPosPaymentStatusLabel !== 'function') {
    window.getPosPaymentStatusLabel = function(status) {
        const labels = {
            unpaid: 'Pending'
        };

        return labels[status] || window.humanizePosStatus(status);
    };
}

if (typeof window.getPosBookingStatusColor !== 'function') {
    window.getPosBookingStatusColor = function(status) {
        const colors = {
            draft: 'bg-gray-400 text-white',
            pending_payment: 'bg-yellow-500 text-white',
            confirmed: 'bg-green-500 text-white',
            active: 'bg-blue-500 text-white',
            completed: 'bg-cyan-500 text-white',
            cancelled: 'bg-red-500 text-white'
        };

        return colors[status] || 'bg-gray-400 text-white';
    };
}

if (typeof window.getPosPaymentStatusColor !== 'function') {
    window.getPosPaymentStatusColor = function(status) {
        const colors = {
            paid: 'bg-green-500 text-white',
            unpaid: 'bg-red-500 text-white',
            partial: 'bg-yellow-500 text-white',
            credit: 'bg-cyan-500 text-white'
        };

        return colors[status] || 'bg-gray-400 text-white';
    };
}

if (typeof window.getPosBookingStatusSoftColor !== 'function') {
    window.getPosBookingStatusSoftColor = function(status) {
        const colors = {
            draft: 'bg-gray-200 text-gray-700',
            pending_payment: 'bg-yellow-100 text-yellow-700',
            confirmed: 'bg-green-100 text-green-700',
            active: 'bg-blue-100 text-blue-700',
            completed: 'bg-cyan-100 text-cyan-700',
            cancelled: 'bg-red-100 text-red-700'
        };

        return colors[status] || 'bg-gray-200 text-gray-700';
    };
}

if (typeof window.getPosPaymentStatusSoftColor !== 'function') {
    window.getPosPaymentStatusSoftColor = function(status) {
        const colors = {
            paid: 'bg-green-100 text-green-700',
            unpaid: 'bg-red-100 text-red-700',
            partial: 'bg-yellow-100 text-yellow-700',
            credit: 'bg-cyan-100 text-cyan-700'
        };

        return colors[status] || 'bg-gray-200 text-gray-700';
    };
}
</script>