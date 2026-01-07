<script>
/* ============================================================
   GLOBAL STATE (DO NOT REMOVE)
============================================================ */
window.selectedPackageState = {
    id: 'base',
    label: 'Base Price',
    price: null
};

(function () {

    /* ============================================================
       ELEMENT REFERENCES
    ============================================================ */
    const modal          = document.getElementById('enquiryModal');
    const hoardingInput  = document.getElementById('enquiryHoardingId');
    const countInput     = document.getElementById('enquiryCount');
    const startDateInput = document.getElementById('enquiryStartDate');

    const enquiryPackage = document.getElementById('enquiryPackage');
    const baseOption     = document.getElementById('basePriceOption');

    const monthWrapper   = document.getElementById('monthWrapper'); // label + select wrapper
    const monthSelect    = document.getElementById('packageSelect');

    if (!modal || !enquiryPackage || !baseOption) return;

    /* ============================================================
       OPEN MODAL
    ============================================================ */
    window.openEnquiryModal = function (payload) {

        modal.classList.remove('hidden');

        hoardingInput.value = payload.id;
        countInput.value    = payload.count ?? 1;

        /* ---------- BASE PRICE SET ---------- */
        const base = Math.round(Number(payload.basePrice || 0));

        baseOption.dataset.base  = base;   // original base
        baseOption.dataset.price = base;   // current calculated price
        baseOption.textContent  = `Base Price – ₹${base}`;

        /* ---------- DEFAULT MONTH RESET ---------- */
        if (monthWrapper && monthSelect) {
            monthSelect.value = 1;
        }

        /* ========================================================
           PRESERVE PACKAGE (FROM DETAILS PAGE)
        ======================================================== */
        if (
            window.selectedPackageState &&
            window.selectedPackageState.id &&
            window.selectedPackageState.id !== 'base'
        ) {
            const opt = enquiryPackage.querySelector(
                `option[value="${window.selectedPackageState.id}"]`
            );

            if (opt) {
                enquiryPackage.value = window.selectedPackageState.id;

                if (monthWrapper) monthWrapper.classList.remove('hidden');

                const pkgMonths = parseInt(opt.dataset.months || 1, 10);

                if (monthSelect) {
                    monthSelect.value = pkgMonths;
                    monthSelect.disabled = true; // readonly
                }

            } else {
                setBaseMode(base);
            }

        } else {
            setBaseMode(base);
        }

        /* ---------- GRACE PERIOD ---------- */
        applyGracePeriod(payload.graceDays ?? 0);
    };

    /* ============================================================
       CLOSE MODAL
    ============================================================ */
    window.closeEnquiryModal = function () {
        modal.classList.add('hidden');
    };

    /* ============================================================
       BASE MODE (HELPER)
    ============================================================ */
    function setBaseMode(base) {

        enquiryPackage.value = 'base';

        window.selectedPackageState = {
            id: 'base',
            label: 'Base Price',
            price: base
        };

        if (monthWrapper) monthWrapper.classList.remove('hidden');
        if (monthSelect)  monthSelect.disabled = false;

        recalcBasePrice();
    }

    /* ============================================================
       PACKAGE DROPDOWN CHANGE
    ============================================================ */
    enquiryPackage.addEventListener('change', function () {

        const opt = this.options[this.selectedIndex];

        // ---------- BASE PRICE ----------
        if (this.value === 'base') {

            if (monthSelect) {
                monthSelect.disabled = false; // editable
                monthSelect.value = 1;        // 🔥 DEFAULT 1 MONTH
            }

            recalcBasePrice();
            syncEnquiryHiddenFields();
            return;
        }

        // ---------- PACKAGE SELECTED ----------
        const pkgMonths = parseInt(opt.dataset.months || 1, 10);

        if (monthSelect) {
            monthSelect.value = pkgMonths;
            monthSelect.disabled = true; // readonly
        }

        window.selectedPackageState = {
            id: this.value,
            label: opt.text,
            price: Math.round(Number(opt.dataset.price || 0))
        };

        syncEnquiryHiddenFields();
    });


    /* ============================================================
       MONTH CHANGE → ONLY FOR BASE PRICE
    ============================================================ */
    if (monthSelect) {
        monthSelect.addEventListener('change', recalcBasePrice);
    }

    function recalcBasePrice() {

        if (enquiryPackage.value !== 'base') return;

        const months = Math.max(1, parseInt(monthSelect.value || 1, 10));
        const base   = Number(baseOption.dataset.base || 0);

        const finalPrice = Math.round(base * months);

        baseOption.textContent =
            `Base Price – ₹${finalPrice} (${months} Month${months > 1 ? 's' : ''})`;

        baseOption.dataset.price = finalPrice;

        window.selectedPackageState = {
            id: 'base',
            label: `Base Price (${months} Month${months > 1 ? 's' : ''})`,
            price: finalPrice
        };
    }

    /* ============================================================
       GRACE PERIOD
    ============================================================ */
    function applyGracePeriod(graceDays = 0) {

        if (!startDateInput) return;

        const d = new Date();
        d.setDate(d.getDate() + Number(graceDays));

        const yyyy = d.getFullYear();
        const mm   = String(d.getMonth() + 1).padStart(2, '0');
        const dd   = String(d.getDate()).padStart(2, '0');

        const minDate = `${yyyy}-${mm}-${dd}`;
        startDateInput.min = minDate;

        if (!startDateInput.value || startDateInput.value < minDate) {
            startDateInput.value = minDate;
        }
    }

    /* ============================================================
       GLOBAL CLICK HANDLER (CARDS + LINKS)
    ============================================================ */
    document.addEventListener('click', function (e) {

        const btn = e.target.closest('.enquiry-btn');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        openEnquiryModal({
            id: btn.dataset.hoardingId,
            basePrice: Number(btn.dataset.basePrice || 0),
            graceDays: Number(btn.dataset.graceDays || 0),
            count: 1
        });
    });

})();

/* ============================================================
   DETAILS PAGE PACKAGE CARD CLICK (DO NOT REMOVE)
============================================================ */
function selectPackage(pkg, el) {

    document.querySelectorAll('.package-card')
        .forEach(c => c.classList.remove('active'));

    el.classList.add('active');

    window.selectedPackageState = {
        id: String(pkg.id),
        label: pkg.label,
        price: Math.round(Number(pkg.price || 0))
    };

    console.log('PACKAGE SELECTED FROM DETAILS:', window.selectedPackageState);
}
</script>
<script>
function syncEnquiryHiddenFields() {

    // hoarding id (already set when modal opens)
    // ensure exists
    const hoardingId = document.getElementById('enquiryHoardingId')?.value;
    if (!hoardingId) return;

    const startDate = document.getElementById('enquiryStartDate')?.value;
    const months = parseInt(document.getElementById('packageSelect')?.value || 1);

    if (!startDate) return;

    // ---------- END DATE ----------
    const start = new Date(startDate);
    const end = new Date(start);
    end.setMonth(end.getMonth() + months);

    const yyyy = end.getFullYear();
    const mm = String(end.getMonth() + 1).padStart(2, '0');
    const dd = String(end.getDate()).padStart(2, '0');

    document.getElementById('enquiryEndDate').value =
        `${yyyy}-${mm}-${dd}`;

    // ---------- PACKAGE / PRICE ----------
    if (window.selectedPackageState) {

        document.getElementById('enquiryPackageId').value =
            window.selectedPackageState.id;

        document.getElementById('enquiryPackageLabel').value =
            window.selectedPackageState.label;

        document.getElementById('enquiryAmount').value =
            window.selectedPackageState.price ?? 0;
    }
}

// 🔁 EVENTS
document.getElementById('enquiryStartDate')
    ?.addEventListener('change', syncEnquiryHiddenFields);

document.getElementById('packageSelect')
    ?.addEventListener('change', syncEnquiryHiddenFields);

document.getElementById('enquiryPackage')
    ?.addEventListener('change', function () {

        const opt = this.options[this.selectedIndex];

        if (this.value === 'base') {
            // base price already calculated elsewhere
        } else {
            window.selectedPackageState = {
                id: this.value,
                label: opt.text,
                price: Number(opt.dataset.price || 0)
            };
        }

        syncEnquiryHiddenFields();
    });

// 🧨 MOST IMPORTANT: submit se just pehle
document.getElementById('enquiryForm')
    ?.addEventListener('submit', function () {
        syncEnquiryHiddenFields();
    });
</script>
