<div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 mt-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-1.5 h-6 bg-[#009A5C] rounded-full"></div>
        <h3 class="text-xl font-bold text-gray-800">Long term Campaign Packages</h3>
    </div>

    <p class="text-xs text-gray-400 mb-8">
        Create specific inventory bundles (e.g.,Long term Booking)
    </p>

   <!-- @dump($draft->hoarding->packages) -->
    @php
        /**
         * ============================================================
         * SINGLE SOURCE OF TRUTH (EDIT + CREATE + OLD INPUT)
         * ============================================================
         */
        if (old('offers_json')) {
            $packagesJson = old('offers_json');
        } else {
            $packagesJson = collect($draft->hoarding->packages ?? $draft->hoarding->packages ?? [])
                ->map(function ($pkg) {
                    return [
                        'package_id' => $pkg->id ?? null,
                        'name'       => $pkg->package_name,
                        'duration'   => $pkg->min_booking_duration,
                        'unit'       => $pkg->duration_unit,
                        'discount'   => $pkg->discount_percent,
                        'end_date'   => $pkg->end_date
                            ? \Carbon\Carbon::parse($pkg->end_date)->format('Y-m-d')
                            : '',
                        'services' => $pkg->included_services ? json_decode($pkg->included_services, true) : []
                    ];
                })
                ->values()
                ->toJson();
        }
    @endphp

    <div class="space-y-6">
        <div id="offers-container" class="space-y-4"></div>

        <!-- SINGLE SOURCE OF TRUTH -->
        <input
            type="hidden"
            name="offers_json"
            id="offers_json"
            value='{{ $packagesJson }}'
        >
        <input
            type="hidden"
            id="inventory_type"
            value="{{ strtolower($draft->hoarding->hoarding_type) }}"
        >


        <button
            type="button"
            id="add-offer-btn"
            class="bg-[#1A1A1A] text-white px-8 py-3 rounded-xl text-sm font-bold hover:scale-[1.02] active:scale-95 transition-transform flex items-center gap-2 w-fit">
            <span>+</span> Add Campaign Package
        </button>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const offersContainer = document.getElementById('offers-container');
    const offersJsonInput = document.getElementById('offers_json');
    const addOfferBtn     = document.getElementById('add-offer-btn');
    const form            = offersJsonInput.closest('form');
    const today           = new Date().toISOString().split('T')[0];

    /* ======================================================
       ERROR HELPERS
    ====================================================== */
    function showError(input, message) {
        clearError(input);
        input.classList.add('border-red-500');
        const p = document.createElement('p');
        p.className = 'text-xs text-red-500 mt-1 error-msg';
        p.innerText = message;
        input.closest('div').appendChild(p);
    }

    function clearError(input) {
        input.classList.remove('border-red-500');
        input.closest('div')?.querySelector('.error-msg')?.remove();
    }

    /* ======================================================
       RENDER PACKAGE CARD
    ====================================================== */
    function renderOffer(data = {}) {
        const html = `
        <div class="group bg-gray-50/50 rounded-2xl border border-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">

                <input type="hidden" class="offer_package_id" value="${data.package_id ?? ''}">

                <div class="md:col-span-4">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Offer Label</label>
                    <input type="text"
                        class="offer_name w-full border rounded-xl px-4 py-3 text-sm"
                        value="${data.name ?? ''}">
                </div>

                <div class="md:col-span-3">
                    <label class="text-[10px] font-bold text-gray-400 uppercase"> Booking Duration</label>
                    <div class="flex">
                        <input type="number" min="1"
                            class="offer_duration w-20 border rounded-l-xl px-4 py-3 text-sm"
                            value="${data.duration ?? ''}">
                        <select class="offer_unit flex-1 border border-l-0 rounded-r-xl px-3 py-3 text-sm">
                            <option value="months" ${data.unit === 'months' ? 'selected' : ''}>Months</option>
                        </select>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Discount (%)</label>
                    <input type="number" min="0" max="100"
                        class="offer_discount w-full border rounded-xl px-4 py-3 text-sm"
                        value="${data.discount ?? ''}">
                </div>

                <div class="md:col-span-2">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">End Date</label>
                    <input type="date"
                        min="${today}"
                        class="offer_end_date w-full border rounded-xl px-4 py-3 text-sm"
                        value="${data.end_date ?? ''}">
                </div>

                <div class="md:col-span-1 flex justify-center items-start pb-4">
                    <button type="button"
                        class="remove-offer text-red-500 hover:text-red-700 text-2xl leading-none mt-1 transition-colors">×</button>
                </div>
            </div>  
        </div>`;

        offersContainer.insertAdjacentHTML('beforeend', html);

        offersContainer.lastElementChild
            .querySelector('.remove-offer')
            .onclick = e => e.target.closest('.group').remove();
    }

    /* ======================================================
       PREFILL EXISTING PACKAGES (EDIT MODE)
    ====================================================== */
    if (offersJsonInput.value) {
        try {
            const existing = JSON.parse(offersJsonInput.value);
            if (Array.isArray(existing)) {
                existing.forEach(pkg => renderOffer(pkg));
            }
        } catch (e) {
            console.warn('Invalid offers_json');
        }
    }

    /* ======================================================
       ADD NEW PACKAGE
    ====================================================== */
    addOfferBtn.onclick = () => renderOffer();

    /* ======================================================
       VALIDATE + SERIALIZE ON SUBMIT
    ====================================================== */
    form.addEventListener('submit', function (e) {
        let valid = true;
        const offers = [];


        offersContainer.querySelectorAll('.group').forEach(group => {
            const packageId = group.querySelector('.offer_package_id');
            const name     = group.querySelector('.offer_name');
            const duration = group.querySelector('.offer_duration');
            const unit     = group.querySelector('.offer_unit');
            const discount = group.querySelector('.offer_discount');
            const endDate  = group.querySelector('.offer_end_date');

            [name, duration, discount, endDate].forEach(clearError);

            // Skip empty rows
            if (!name.value && !duration.value && !discount.value && !endDate.value) {
                return;
            }

            if (!name.value.trim()) {
                showError(name, 'Offer label is required');
                valid = false;
            }

            if (duration.value < 1) {
                showError(duration, 'Booking Duration must be at least 1');
                valid = false;
            }


            if (!discount.value) {
                showError(discount, 'Discount is required');
                valid = false;
            } else if (discount.value < 0 || discount.value > 100) {
                showError(discount, 'Discount must be between 0 and 100');
                valid = false;
            }

            if (!endDate.value) {
                showError(endDate, 'End date is required');
                valid = false;
            } else if (endDate.value < today) {
                showError(endDate, 'End date cannot be in the past');
                valid = false;
            }

            offers.push({
                package_id: packageId.value || null,
                name     : name.value.trim(),
                duration : duration.value,
                unit     : unit.value,
                discount : discount.value,
                end_date : endDate.value,
            });
        });

        if (!valid) {
            e.preventDefault();
            return;
        }

        offersJsonInput.value = JSON.stringify(offers);
    });

});
</script>
