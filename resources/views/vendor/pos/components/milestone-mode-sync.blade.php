{{--
    MILESTONE ↔ PAYMENT MODE SYNC — Final

    Milestone = WHEN (phases/timing) → stored as is_milestone = true
    Cash/Bank/UPI = HOW (method)     → stored as payment_mode = cash/bank_transfer/online

    Both are ALWAYS visible and fully independent.
    NO hiding. NO UI changes.

    Include once in preview-screen.blade.php:
    @include('vendor.pos.components.milestone-mode-sync')
--}}
<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // Poll until milestone fetch interceptor is ready
        var poll = setInterval(function () {
            if (typeof window.toggleMilestoneSection !== 'function') return;
            clearInterval(poll);

            // Patch window.fetch so milestone bookings send:
            //   payment_mode  = cash / bank_transfer / online  (unchanged, HOW)
            //   is_milestone  = true                           (new flag, WHEN)
            //   milestone_data = [...]
            var _origFetch = window.fetch;
            window.fetch = function (url, options) {
                options = options || {};

                if (
                    window._pendingMilestoneData &&
                    typeof url === 'string' &&
                    /\/api\/bookings\b/.test(url) &&
                    ((options.method || '').toUpperCase() === 'POST')
                ) {
                    try {
                        var body = JSON.parse(options.body || '{}');

                        // Keep payment_mode as whatever vendor selected (cash/bank/online)
                        // Just add the milestone flag and data
                        body.is_milestone  = true;
                        body.milestone_data = window._pendingMilestoneData;

                        // Do NOT overwrite body.payment_mode

                        options.body                 = JSON.stringify(body);
                        window._pendingMilestoneData = null;
                    } catch (e) {
                        console.error('[MilestoneSync] fetch patch error:', e);
                    }
                }
                return _origFetch.call(this, url, options);
            };

        }, 50);

    });

})();
</script>