<script>
// Centralized POS Timer Notification Component (managed)
(function() {
    let timerInterval = null;

    function clearTimerNotification() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        const notif = document.getElementById('pos-timer-notif');
        if (notif) notif.remove();
        localStorage.removeItem('activeBookingTimer');
    }

    window.renderPosTimerNotification = function(booking) {
        clearTimerNotification();
        const notif = document.createElement('div');
        notif.id = 'pos-timer-notif';
        notif.className = 'fixed z-50 right-6 bottom-6 bg-amber-100 border border-amber-300 rounded-xl shadow-lg p-4 flex items-center gap-4 animate-fade-in';
        notif.style.minWidth = '260px';
        notif.innerHTML = `
            <div class="flex-1">
                <div class="font-bold text-amber-700 text-xs mb-1">Payment Due In</div>
                <div class="font-mono text-lg font-black text-amber-700" id="notif-timer-${booking.id}">--:--:--</div>
                <div class="text-xs text-gray-500 mt-1">Invoice #${booking.invoice_number ?? booking.id}</div>
            </div>
            <button class="ml-2 text-xs text-gray-500 hover:text-red-600 font-bold" id="close-timer-notif">✕</button>
        `;
        document.body.appendChild(notif);
        document.getElementById('close-timer-notif').onclick = clearTimerNotification;
        function updateTimer() {
            const remaining = new Date(booking.hold_expiry_at) - new Date();
            if (remaining <= 0) {
                document.getElementById(`notif-timer-${booking.id}`).innerText = '00:00:00';
                clearTimerNotification();
                return;
            }
            const h = Math.floor(remaining / 3600000);
            const m = Math.floor((remaining % 3600000) / 60000);
            const s = Math.floor((remaining % 60000) / 1000);
            document.getElementById(`notif-timer-${booking.id}`).innerText = [h, m, s].map(n => String(n).padStart(2, '0')).join(':');
        }
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    };

    window.checkAndShowPosTimerNotification = function() {
        const timerData = localStorage.getItem('activeBookingTimer');
        if (timerData) {
            try {
                const booking = JSON.parse(timerData);
                if (booking && booking.hold_expiry_at && new Date(booking.hold_expiry_at) > new Date()) {
                    window.renderPosTimerNotification(booking);
                } else {
                    clearTimerNotification();
                }
            } catch { clearTimerNotification(); }
        } else {
            clearTimerNotification();
        }
    };

    document.addEventListener('DOMContentLoaded', window.checkAndShowPosTimerNotification);
})();
</script>
