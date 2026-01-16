@extends('layouts.app')

@section('title', 'Shortlisted Hoardings')

@section('content')
@include('components.customer.navbar')

<!-- USER DATA META TAGS -->
@auth
<meta name="user-id" content="{{ auth()->id() }}">
<meta name="user-email" content="{{ auth()->user()->email }}">
<meta name="user-mobile" content="{{ auth()->user()->phone ?? auth()->user()->mobile ?? '' }}">
<meta name="user-name" content="{{ auth()->user()->name }}">
@endauth

{{-- FULL HEIGHT LAYOUT --}}
<div class="bg-white max-w-7xl mx-auto px-3 sm:px-4 md:px-6 p-4 sm:p-6 min-h-screen flex flex-col border-b border-gray-200">

    {{-- Breadcrumb --}}
    <p class="text-xs sm:text-sm text-gray-400 mb-3 sm:mb-4">
        Home / Shortlisted Hoardings
    </p>

    {{-- Heading --}}
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-4 sm:mb-6">
        Shortlisted
        <span class="text-gray-500 font-medium text-base sm:text-lg">
            ({{ $items->count() }} Hoardings)
        </span>
    </h1>

    {{-- MAIN CONTENT (NO PAGE SCROLL) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 flex-1">
        @if($items->count() > 0)
            <div class="lg:col-span-8 overflow-y-auto">
                @include('cart.partials.list', ['items' => $items])
            </div>
            <div class="lg:col-span-4">
                <div class="sticky top-4 sm:top-6">
                    @include('cart.partials.summary')
                </div>
            </div>
        @else
<div class="col-span-full flex items-center justify-center text-center px-4 sm:px-6 py-12">
            <div class="max-w-md">

                <div class="text-4xl sm:text-6xl mb-4">🛒</div>

                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">
                    Your shortlist is empty
                </h2>

                <p class="text-xs sm:text-sm text-gray-500 mb-6">
                    You haven’t shortlisted any hoardings yet.<br>
                    Start exploring and add hoardings to compare & book.
                </p>

                <a href="{{ route('search') }}"
                   class="inline-flex items-center gap-2
                          bg-gray-900 text-white
                          px-4 sm:px-6 py-2 sm:py-3 rounded-lg
                          text-xs sm:text-sm font-medium
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
       RECALCULATE SUMMARY
    ===================================================== */
    window.recalculateSummary = function () {
        let baseTotal  = 0;
        let finalTotal = 0;

        Object.keys(window.cartState.prices).forEach(id => {
            baseTotal  += Number(window.cartState.basePrices[id] || 0);
            finalTotal += Number(window.cartState.prices[id] || 0);
        });

        const discount = Math.max(baseTotal - finalTotal, 0);

        const summarySubtotal = document.getElementById('summary-subtotal');
        const summaryDiscount = document.getElementById('summary-discount');
        const summaryTotal = document.getElementById('summary-total');

        if (summarySubtotal) summarySubtotal.innerText = `₹${baseTotal.toLocaleString()}`;
        if (summaryDiscount) summaryDiscount.innerText = `-₹${discount.toLocaleString()}`;
        if (summaryTotal) summaryTotal.innerText = `₹${finalTotal.toLocaleString()}`;

        console.log('🧾 SUMMARY', { baseTotal, finalTotal, discount });
    };

    /* =====================================================
       SYNC PACKAGE TO BACKEND
    ===================================================== */
    window.syncPackageToBackend = function (hoardingId, packageId = null, label = null) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('❌ CSRF token not found');
            return;
        }

        fetch('/cart/select-package', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content
            },
            body: JSON.stringify({
                hoarding_id: hoardingId,
                package_id: packageId,
                package_label: label
            })
        }).then(res => res.json())
          .then(data => console.log('✅ Backend sync:', data))
          .catch(err => console.error('❌ Sync error:', err));
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

            window.cartState.prices[hoardingId] = finalPrice;
            window.cartState.basePrices[hoardingId] = basePrice;
        });
        window.recalculateSummary();
    }

    /* =====================================================
       RESET PACKAGES OF ONE HOARDING
    ===================================================== */
    window.resetPackages = function (hoardingId) {
        document.querySelectorAll(`.package-card-${hoardingId}`).forEach(card => {
            card.classList.remove('bg-white');
            const strip = card.querySelector('.selected-strip');
            if (strip) strip.classList.add('hidden');
        });
    };

    /* =====================================================
       HANDLE PACKAGE CLICK (SELECT / UNSELECT)
    ===================================================== */
    window.handlePackageClick = function (card) {
        const hoardingId   = card.dataset.hoardingId;
        const hoardingType = card.dataset.hoardingType || 'ooh';
        const finalPrice   = Number(card.dataset.finalPrice || 0);
        const basePrice    = Number(card.dataset.basePrice || finalPrice);

        const packageId    = card.dataset.packageId;
        const packageName  = card.dataset.packageName;

        const finalEl = document.getElementById(`final-price-${hoardingId}`);
        const baseEl  = document.getElementById(`base-price-${hoardingId}`);

        console.log(`🎯 handlePackageClick triggered:`, { 
            hoardingId, 
            packageId, 
            packageName, 
            hoardingType, 
            finalPrice, 
            basePrice,
            finalElExists: !!finalEl
        });

        if (!finalEl) {
            console.error(`❌ final-price-${hoardingId} not found`);
            return;
        }

        const defaultPrice = Number(finalEl.dataset.defaultPrice || 0);
        const defaultBase  = baseEl
            ? Number(baseEl.dataset.basePrice || defaultPrice)
            : defaultPrice;

        const alreadySelected = card.querySelector('.selected-strip:not(.hidden)') !== null;

        console.log(`📦 Package Click:`, { hoardingId, packageId, packageName, alreadySelected });

        window.resetPackages(hoardingId);

        if (alreadySelected) {
            /* UNSELECT → DEFAULT */
            const priceLabel = hoardingType === 'dooh' ? '/ Slot' : '/ Month';
            finalEl.innerHTML = `₹${defaultPrice.toLocaleString()}<span class="text-sm text-gray-400">${priceLabel}</span>`;

            if (baseEl && defaultBase > defaultPrice) {
                baseEl.innerText = `₹${defaultBase.toLocaleString()}`;
                baseEl.classList.remove('hidden');
            } else if (baseEl) {
                baseEl.classList.add('hidden');
            }

            window.cartState.prices[hoardingId] = defaultPrice;
            window.cartState.basePrices[hoardingId] = defaultBase;

            window.syncPackageToBackend(hoardingId, null, null);

        } else {
            /* SELECT PACKAGE */
            const strip = card.querySelector('.selected-strip');
            if (strip) strip.classList.remove('hidden');

            finalEl.innerHTML = `₹${finalPrice.toLocaleString()}<span class="text-sm text-gray-400">/ Package</span>`;

            if (baseEl && basePrice > finalPrice) {
                baseEl.innerText = `₹${basePrice.toLocaleString()}`;
                baseEl.classList.remove('hidden');
            }

            window.cartState.prices[hoardingId] = finalPrice;
            window.cartState.basePrices[hoardingId] = basePrice;

            window.syncPackageToBackend(hoardingId, packageId, packageName);
        }

        window.recalculateSummary();
    };

    /* =====================================================
       INIT DATE PICKERS
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
       BOOTSTRAP ON DOM READY
    ===================================================== */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            console.log('🚀 DOMContentLoaded - Initializing Cart JS');
            initPrices();
            initDatePickers();
        });
    } else {
        console.log('🚀 Document already loaded - Initializing Cart JS');
        initPrices();
        initDatePickers();
    }

})();
    </script>

@include('cart.partials.raise-enquiry-modal')

</div>

@include('components.customer.footer')
@endsection
