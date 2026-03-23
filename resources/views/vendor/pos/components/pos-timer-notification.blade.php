<script>
// Centralized POS Timer Notification Component (managed)
(function() {
    const STORAGE_KEY = 'activeBookingTimers';
    const LEGACY_STORAGE_KEY = 'activeBookingTimer';
    const timerMap = new Map();

    function getPosBasePath() {
        return window.POS_BASE_PATH || '/vendor/pos';
    }

    function parseStoredTimers() {
        const allTimersRaw = localStorage.getItem(STORAGE_KEY);
        if (allTimersRaw) {
            try {
                const parsed = JSON.parse(allTimersRaw);
                return Array.isArray(parsed) ? parsed : [];
            } catch {
                return [];
            }
        }

        const legacyRaw = localStorage.getItem(LEGACY_STORAGE_KEY);
        if (!legacyRaw) return [];

        try {
            const legacyBooking = JSON.parse(legacyRaw);
            return legacyBooking ? [legacyBooking] : [];
        } catch {
            return [];
        }
    }

    function uniqueActiveTimers(bookings) {
        const nowMs = Date.now();
        const uniqueById = new Map();

        (bookings || []).forEach((booking) => {
            if (!booking || !booking.id || !booking.hold_expiry_at) return;
            const expiryMs = new Date(booking.hold_expiry_at).getTime();
            if (!Number.isFinite(expiryMs) || expiryMs <= nowMs) return;
            uniqueById.set(String(booking.id), {
                id: booking.id,
                invoice_number: booking.invoice_number ?? booking.id,
                hold_expiry_at: booking.hold_expiry_at,
            });
        });

        return Array.from(uniqueById.values()).sort((a, b) => {
            return new Date(a.hold_expiry_at) - new Date(b.hold_expiry_at);
        });
    }

    function persistTimers(bookings) {
        const active = uniqueActiveTimers(bookings);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(active));
        localStorage.removeItem(LEGACY_STORAGE_KEY);
        return active;
    }

    function removeTimer(bookingId) {
        const bookings = parseStoredTimers().filter((booking) => String(booking.id) !== String(bookingId));
        persistTimers(bookings);

        const entry = timerMap.get(String(bookingId));
        if (entry) {
            clearInterval(entry.interval);
            entry.element.remove();
            timerMap.delete(String(bookingId));
        }

        reflowTimerCards();
    }

    function formatRemaining(remainingMs) {
        const h = Math.floor(remainingMs / 3600000);
        const m = Math.floor((remainingMs % 3600000) / 60000);
        const s = Math.floor((remainingMs % 60000) / 1000);
        return [h, m, s].map(n => String(n).padStart(2, '0')).join(':');
    }

    function reflowTimerCards() {
        Array.from(timerMap.values())
            .sort((a, b) => new Date(a.booking.hold_expiry_at) - new Date(b.booking.hold_expiry_at))
            .forEach((entry, index) => {
                entry.element.style.bottom = `${24 + (index * 114)}px`;
            });
    }

    function ensureTimerCard(booking) {
        const key = String(booking.id);
        const existing = timerMap.get(key);
        if (existing) {
            existing.booking = booking;
            existing.element.querySelector('.js-pos-invoice').textContent = `Invoice #${booking.invoice_number ?? booking.id}`;
            return;
        }

        const card = document.createElement('div');
        card.id = `pos-timer-notif-${booking.id}`;
        card.className = 'fixed z-50 right-6 bg-amber-100 border border-amber-300 rounded-xl shadow-lg p-4 flex items-center gap-4 animate-fade-in';
        card.style.minWidth = '260px';
        card.style.cursor = 'pointer';
        card.title = 'Open booking details';
        card.innerHTML = `
            <div class="flex-1">
                <div class="font-bold text-amber-700 text-xs mb-1">Payment Due In</div>
                <div class="font-mono text-lg font-black text-amber-700 js-pos-timer">--:--:--</div>
                <div class="text-xs text-gray-500 mt-1 js-pos-invoice">Invoice #${booking.invoice_number ?? booking.id}</div>
            </div>
            <button class="ml-2 text-xs text-gray-500 hover:text-red-600 font-bold js-pos-close">✕</button>
        `;

        card.addEventListener('click', () => {
            window.location.href = `${getPosBasePath()}/bookings/${booking.id}`;
        });

        card.querySelector('.js-pos-close').addEventListener('click', (event) => {
            event.stopPropagation();
            removeTimer(booking.id);
        });
        document.body.appendChild(card);

        const timerText = card.querySelector('.js-pos-timer');
        const interval = setInterval(() => {
            const remaining = new Date(booking.hold_expiry_at) - new Date();
            if (remaining <= 0) {
                timerText.innerText = '00:00:00';
                removeTimer(booking.id);
                // Ask backend to process expired holds immediately for email/in-app notifications.
                syncWithPendingPayments();
                return;
            }
            timerText.innerText = formatRemaining(remaining);
        }, 1000);

        timerMap.set(key, {
            booking,
            element: card,
            interval,
        });
    }

    function renderTimers(bookings) {
        const active = persistTimers(bookings);
        const activeIds = new Set(active.map((booking) => String(booking.id)));

        Array.from(timerMap.keys()).forEach((key) => {
            if (!activeIds.has(key)) {
                const entry = timerMap.get(key);
                if (entry) {
                    clearInterval(entry.interval);
                    entry.element.remove();
                }
                timerMap.delete(key);
            }
        });

        active.forEach((booking) => {
            ensureTimerCard(booking);
            const entry = timerMap.get(String(booking.id));
            if (entry) {
                const remaining = new Date(booking.hold_expiry_at) - new Date();
                entry.element.querySelector('.js-pos-timer').innerText = remaining > 0 ? formatRemaining(remaining) : '00:00:00';
            }
        });

        reflowTimerCards();
    }

    async function syncWithPendingPayments() {
        try {
            const response = await fetch(`${getPosBasePath()}/api/pending-payments`, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response.ok) return;

            const data = await response.json();
            if (!data?.success || !Array.isArray(data.data)) return;

            const validStatuses = ['unpaid', 'partial'];

            const pendingTimers = data.data
                .filter((booking) => {
                    // ❌ REMOVE cancelled / failed / completed bookings
                    if (['cancelled', 'expired', 'failed', 'paid'].includes(booking.status)) {
                        removeTimer(booking.id); // 🔥 IMPORTANT
                        return false;
                    }

                    return validStatuses.includes(booking.payment_status) && booking.hold_expiry_at;
                })
                .map((booking) => ({
                    id: booking.id,
                    invoice_number: booking.invoice_number ?? booking.id,
                    hold_expiry_at: booking.hold_expiry_at,
                }));

            const mergedTimers = [...parseStoredTimers(), ...pendingTimers];
            renderTimers(mergedTimers);
        } catch {
            // Silent fail: timer is a helper UI, page should continue to work
        }
    }

    window.upsertPosTimerBooking = function(booking) {
        if (!booking || !booking.id || !booking.hold_expiry_at) return;
        const current = parseStoredTimers();
        current.push({
            id: booking.id,
            invoice_number: booking.invoice_number ?? booking.id,
            hold_expiry_at: booking.hold_expiry_at,
        });
        renderTimers(current);
    };

    window.removePosTimerBooking = function(bookingId) {
        if (!bookingId) return;
        removeTimer(bookingId);
    };

    window.renderPosTimerNotification = function(booking) {
        window.upsertPosTimerBooking(booking);
    };

    window.checkAndShowPosTimerNotification = function() {
        renderTimers(parseStoredTimers());
        syncWithPendingPayments();
    };

    document.addEventListener('DOMContentLoaded', window.checkAndShowPosTimerNotification);
})();
</script>
