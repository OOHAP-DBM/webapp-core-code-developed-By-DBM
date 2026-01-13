@extends('layouts.app')

@section('title', 'Shortlisted Hoardings')

@section('content')
@include('components.customer.navbar')

{{-- FULL HEIGHT LAYOUT --}}
<div class="max-w-7xl mx-auto px-4 p-6 h-[calc(100vh-60px)] flex flex-col border-b border-gray-200">

    {{-- Breadcrumb --}}
    <p class="text-xs text-gray-400 mb-3">
        Home / Shortlisted Hoardings
    </p>

    {{-- Heading --}}
    <h1 class="text-2xl font-semibold text-gray-900 mb-4">
        Shortlisted
        <span class="text-gray-500 font-medium">
            ({{ $items->count() }} Hoardings)
        </span>
    </h1>

    {{-- MAIN CONTENT (NO PAGE SCROLL) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1 overflow-hidden">
        @if($items->count() > 0)
            <div class="lg:col-span-8 h-full overflow-y-auto pr-2">
                @include('cart.partials.list', ['items' => $items])
            </div>
            <div class="lg:col-span-4 h-full">
                <div class="sticky top-6">
                    @include('cart.partials.summary')
                </div>
            </div>
        @else
<div class="col-span-full flex-1 flex items-center justify-center text-center px-6 -mt-16">
            <div class="max-w-md">

                <div class="text-6xl mb-4">🛒</div>

                <h2 class="text-xl font-semibold text-gray-900 mb-2">
                    Your shortlist is empty
                </h2>

                <p class="text-sm text-gray-500 mb-6">
                    You haven’t shortlisted any hoardings yet.<br>
                    Start exploring and add hoardings to compare & book.
                </p>

                <a href="{{ route('search') }}"
                   class="inline-flex items-center gap-2
                          bg-gray-900 text-white
                          px-6 py-3 rounded-lg
                          text-sm font-medium
                          hover:bg-gray-800 transition">
                    Browse Hoardings
                </a>

            </div>
        </div>
        @endif
    </div>
    <script>
(function () {

    /* =====================================================
       GLOBAL STATE (SINGLE SOURCE OF TRUTH)
    ===================================================== */
        window.cartState = {
        prices: {},
        basePrices: {}, 
        dates: {}
        };


    /* =====================================================
       INIT DEFAULT MONTHLY PRICES
    ===================================================== */
    function initPrices() {
        document.querySelectorAll('[id^="final-price-"]').forEach(el => {

            const hoardingId = el.id.replace('final-price-', '');
            const finalPrice = Number(el.dataset.defaultPrice || 0);

            const baseEl = document.getElementById(`base-price-${hoardingId}`);
            const basePrice = baseEl
                ? Number(baseEl.dataset.basePrice || finalPrice)
                : finalPrice;

            cartState.prices[hoardingId] = finalPrice;
            cartState.basePrices[hoardingId] = basePrice;
        });
        recalculateSummary();
    }


    /* =====================================================
       RESET PACKAGES OF ONE HOARDING
    ===================================================== */
    function resetPackages(hoardingId) {
        document.querySelectorAll(`.package-card-${hoardingId}`).forEach(card => {
            card.classList.remove('ring-2', 'ring-green-500', 'bg-white');
            const strip = card.querySelector('.selected-strip');
            if (strip) strip.classList.add('hidden');
        });
    }

    /* =====================================================
       HANDLE PACKAGE CLICK (SELECT / UNSELECT)
    ===================================================== */
    function handlePackageClick(card) {

    const hoardingId   = card.dataset.hoardingId;
    const finalPrice   = Number(card.dataset.finalPrice || 0);
    const basePrice    = Number(card.dataset.basePrice || finalPrice);

    const packageId    = card.dataset.packageId;
    const packageName  = card.dataset.packageName;

    const finalEl = document.getElementById(`final-price-${hoardingId}`);
    const baseEl  = document.getElementById(`base-price-${hoardingId}`);

    if (!finalEl) return;

    const defaultPrice = Number(finalEl.dataset.defaultPrice || 0);
    const defaultBase  = baseEl
        ? Number(baseEl.dataset.basePrice || defaultPrice)
        : defaultPrice;

    const alreadySelected = card.classList.contains('ring-green-500');

    resetPackages(hoardingId);

    if (alreadySelected) {
        /* UNSELECT → MONTHLY */

        finalEl.innerHTML = `₹${defaultPrice.toLocaleString()}
            <span class="text-sm text-gray-400">/ Month</span>`;

        if (baseEl && defaultBase > defaultPrice) {
            baseEl.innerText = `₹${defaultBase.toLocaleString()}`;
            baseEl.classList.remove('hidden');
        } else if (baseEl) {
            baseEl.classList.add('hidden');
        }

        cartState.prices[hoardingId] = defaultPrice;
        cartState.basePrices[hoardingId] = defaultBase;

        // 🔥 BACKEND SYNC (UNSELECT)
        syncPackageToBackend(hoardingId, null, null);

    } else {
        /* SELECT PACKAGE */

        card.classList.add('ring-2', 'ring-green-500', 'bg-white');
        card.querySelector('.selected-strip')?.classList.remove('hidden');

        finalEl.innerHTML = `₹${finalPrice.toLocaleString()}
            <span class="text-sm text-gray-400">/ Package</span>`;

        if (baseEl && basePrice > finalPrice) {
            baseEl.innerText = `₹${basePrice.toLocaleString()}`;
            baseEl.classList.remove('hidden');
        }

        cartState.prices[hoardingId] = finalPrice;
        cartState.basePrices[hoardingId] = basePrice;

        // 🔥 BACKEND SYNC (SELECT)
        syncPackageToBackend(hoardingId, packageId, packageName);
    }

    recalculateSummary();
}



    /* =====================================================
       EVENT DELEGATION (SAFE & FAST)
    ===================================================== */
    document.body.addEventListener('click', function (e) {
        const card = e.target.closest('[data-package-id]');
        if (card) {
            handlePackageClick(card);
        }
    });

    /* =====================================================
       CAMPAIGN DATE PICKER
    ===================================================== */
    function initDatePickers() {
        document.querySelectorAll('[data-campaign-trigger]').forEach(trigger => {

            const hoardingId = trigger.dataset.campaignTrigger;
            const graceDays  = parseInt(trigger.dataset.graceDays || 0);

            let blockedDates = [];
            try {
                blockedDates = JSON.parse(trigger.dataset.blockDates || '[]');
            } catch {}

            const input = document.getElementById(`campaign-input-${hoardingId}`);
            const text  = document.getElementById(`campaign-text-${hoardingId}`);

            if (!input || !text) return;

            const minDate = new Date();
            minDate.setDate(minDate.getDate() + graceDays);

            const picker = flatpickr(input, {
                mode: 'range',
                dateFormat: 'M d, Y',
                minDate,
                disable: blockedDates,
                appendTo: trigger,
                static: true,
                onChange(dates, str) {
                    if (dates.length === 2) {
                        text.innerText = str;
                        window.cartState.dates[hoardingId] = str;
                        console.log('📅 cartState.dates →', window.cartState.dates);
                    }
                }
            });

            trigger.addEventListener('click', () => picker.open());
        });
    }

    /* =====================================================
       BOOTSTRAP
    ===================================================== */
    document.addEventListener('DOMContentLoaded', function () {
        initPrices();
        initDatePickers();
        console.log('🚀 Cart JS initialized');
    });

})();
window.recalculateSummary = function () {

    let baseTotal  = 0;
    let finalTotal = 0;

    Object.keys(cartState.prices).forEach(id => {
        baseTotal  += Number(cartState.basePrices[id] || 0);
        finalTotal += Number(cartState.prices[id] || 0);
    });

    const discount = Math.max(baseTotal - finalTotal, 0);

    document.getElementById('summary-subtotal').innerText =
        `₹${baseTotal.toLocaleString()}`;

    document.getElementById('summary-discount').innerText =
        `-₹${discount.toLocaleString()}`;

    document.getElementById('summary-total').innerText =
        `₹${finalTotal.toLocaleString()}`;

    console.log('🧾 SUMMARY', { baseTotal, finalTotal, discount });
};

function syncPackageToBackend(hoardingId, packageId = null, label = null) {
    fetch('/cart/select-package', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            hoarding_id: hoardingId,
            package_id: packageId,
            package_label: label
        })
    });
}

</script>

</div>

@include('components.customer.footer')
@endsection
