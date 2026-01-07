<script>
/* ============================================================
   GLOBAL STATE (DO NOT REMOVE)
============================================================ */
window.selectedPackageState = {
    id: 'base',
    label: 'Base Price',
    price: null
};

/* ============================================================
   MODAL CONTROLS
============================================================ */
(function () {
    const modal = document.getElementById('enquiryModal');
    const hoardingInput = document.getElementById('enquiryHoardingId');
    const countInput = document.getElementById('enquiryCount');
    const startDateInput = document.getElementById('enquiryStartDate');
    const enquiryPackage = document.getElementById('enquiryPackage');
    const baseOption = document.getElementById('basePriceOption');
    const monthWrapper = document.getElementById('monthWrapper');
    const monthSelect = document.getElementById('packageSelect');
    const hoardingType = document.getElementById('hoardingType')?.value;

    if (!modal || !enquiryPackage || !baseOption) return;

   

    /* ---------- CLOSE MODAL ---------- */
    window.closeEnquiryModal = function () {
        modal.classList.add('hidden');
    };

    /* ---------- BASE MODE HELPER ---------- */
    function setBaseMode(base) {
        enquiryPackage.value = 'base';

        if (hoardingType === 'dooh') {
            applyDoohBaseSlot();
            return;
        }

        window.selectedPackageState = {
            id: 'base',
            label: 'Base Price',
            price: base,
            months: 1
        };


        if (monthWrapper) monthWrapper.classList.remove('hidden');
        if (monthSelect) {
            monthSelect.disabled = false;
            monthSelect.value = 1;
        }

        recalcBasePrice();
    }

    /* ---------- PACKAGE DROPDOWN CHANGE ---------- */
    enquiryPackage.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];

        if (this.value === 'base') {
            if (hoardingType === 'dooh') {
                applyDoohBaseSlot();
            } else {
                if (monthSelect) {
                    monthSelect.disabled = false;
                    monthSelect.value = 1;
                }
                recalcBasePrice();
            }
            syncEnquiryHiddenFields();
            return;
        }

        /* PACKAGE SELECTED */
        const pkgMonths = parseInt(opt.dataset.months || 1, 10);
        if (monthSelect) {
            monthSelect.value = pkgMonths;
            monthSelect.disabled = true;
        }

        window.selectedPackageState = {
            id: this.value,
            label: opt.dataset.label || '',
            price: Math.round(Number(opt.dataset.price || 0)),
            months: pkgMonths
        };

        syncEnquiryHiddenFields();
    });

    /* ---------- MONTH CHANGE → ONLY FOR BASE PRICE ---------- */
    if (monthSelect) {
        monthSelect.addEventListener('change', function() {
            if (enquiryPackage.value !== 'base') return;
            if (hoardingType === 'dooh' && monthSelect.value === '10_sec') return;
            
            recalcBasePrice();
            syncEnquiryHiddenFields();
        });
    }

    function recalcBasePrice() {
        if (enquiryPackage.value !== 'base') return;
        if (hoardingType === 'dooh' && monthSelect.value === '10_sec') return;

        const months = Math.max(1, parseInt(monthSelect.value || 1, 10));
        const base = Number(baseOption.dataset.base || 0);
        const finalPrice = Math.round(base * months);

        baseOption.textContent = 
            `Base Price – ₹${finalPrice} (${months} Month${months > 1 ? 's' : ''})`;
        baseOption.dataset.price = finalPrice;

        window.selectedPackageState = {
            id: 'base',
            label: `Base Price (${months} Month${months > 1 ? 's' : ''})`,
            price: finalPrice,
            months: months
        };

    }

    /* ---------- GRACE PERIOD ---------- */
    function applyGracePeriod(graceDays = 0) {
        if (!startDateInput) return;

        const d = new Date();
        d.setDate(d.getDate() + Number(graceDays));

        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');

        const minDate = `${yyyy}-${mm}-${dd}`;
        startDateInput.min = minDate;

        if (!startDateInput.value || startDateInput.value < minDate) {
            startDateInput.value = minDate;
        }
    }

    /* ---------- DOOH BASE SLOT HANDLING ---------- */
    function applyDoohBaseSlot() {
        if (hoardingType !== 'dooh') return;

        if (enquiryPackage.value === 'base') {
            /* DOOH + BASE PRICE */
            let opt = monthSelect.querySelector('option[value="10_sec"]');
            if (!opt) {
                opt = document.createElement('option');
                opt.value = '10_sec';
                opt.textContent = '10 Seconds';
                monthSelect.prepend(opt);
            }

            monthSelect.value = '10_sec';
            monthSelect.disabled = true;

            const basePrice = Number(baseOption.dataset.base || baseOption.dataset.price || 0);
            baseOption.textContent = `Base Price – ₹${basePrice} (10 Seconds)`;

            window.selectedPackageState = {
                id: 'base',
                label: `Base Price – ₹${basePrice} (10 Seconds)`,
                price: basePrice
            };
        } else {
            /* REMOVE 10_SEC OPTION IF EXISTS */
            const opt = monthSelect.querySelector('option[value="10_sec"]');
            if (opt) opt.remove();

            const base = Number(baseOption.dataset.base || baseOption.dataset.price || 0);
            baseOption.textContent = `Base Price – ₹${base}`;
        }
        syncEnquiryHiddenFields();
    }

    /* ---------- GLOBAL CLICK HANDLER (CARDS + LINKS) ---------- */
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
        price: Math.round(Number(pkg.price || 0)),
        months: parseInt(pkg.months || 1)
    };

}

/* ============================================================
   SYNC HIDDEN FIELDS
============================================================ */
function syncEnquiryHiddenFields() {

    const startDate = document.getElementById('enquiryStartDate')?.value;
    if (!startDate || !window.selectedPackageState) return;

    const months = parseInt(window.selectedPackageState.months || 1);

    /* ===== END DATE ===== */
    const start = new Date(startDate);
    const end = new Date(start);
    end.setMonth(end.getMonth() + months);

    const yyyy = end.getFullYear();
    const mm = String(end.getMonth() + 1).padStart(2, '0');
    const dd = String(end.getDate()).padStart(2, '0');

    document.getElementById('enquiryEndDate').value = `${yyyy}-${mm}-${dd}`;

    /* ===== PACKAGE FIELDS ===== */
    const pkgIdInput    = document.getElementById('enquiryPackageId');
    const pkgLabelInput = document.getElementById('enquiryPackageLabel');
    const amountInput   = document.getElementById('enquiryAmount');

    if (window.selectedPackageState.id === 'base') {
        pkgIdInput.value = '';
    } else {
        pkgIdInput.value = window.selectedPackageState.id;
    }

    pkgLabelInput.value = window.selectedPackageState.label || null;
    amountInput.value   = window.selectedPackageState.price ?? 0;
}



/* ============================================================
   EVENT LISTENERS FOR SYNC
============================================================ */
document.addEventListener('DOMContentLoaded', function() {
    /* FORM ELEMENTS */
    const startDateInput = document.getElementById('enquiryStartDate');
    const monthSelect = document.getElementById('packageSelect');
    const enquiryPackage = document.getElementById('enquiryPackage');
    const enquiryForm = document.getElementById('enquiryForm');

    /* SYNC ON CHANGE EVENTS */
    if (startDateInput) {
        startDateInput.addEventListener('change', syncEnquiryHiddenFields);
    }

    if (monthSelect) {
        monthSelect.addEventListener('change', syncEnquiryHiddenFields);
    }

    if (enquiryPackage) {
        enquiryPackage.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (this.value === 'base') {
                /* Base price handled elsewhere */
            } else {
                window.selectedPackageState = {
                    id: this.value,
                    label: opt.text,
                    price: Number(opt.dataset.price || 0)
                };
            }
            syncEnquiryHiddenFields();
        });
    }

    /* SYNC BEFORE FORM SUBMIT */
    if (enquiryForm) {
        enquiryForm.addEventListener('submit', function() {
            syncEnquiryHiddenFields();
        });
    }
});
</script>
<script>
window.openEnquiryModal = function (payload) {

    const modal          = document.getElementById('enquiryModal');
    const hoardingInput  = document.getElementById('enquiryHoardingId');
    const countInput     = document.getElementById('enquiryCount');
    const enquiryPackage = document.getElementById('enquiryPackage');
    const baseOption     = document.getElementById('basePriceOption');
    const monthSelect    = document.getElementById('packageSelect');
    const hoardingType   = document.getElementById('hoardingType')?.value;

    if (!modal || !enquiryPackage || !baseOption) return;

    /* ================= OPEN MODAL ================= */
    modal.classList.remove('hidden');

    hoardingInput.value = payload.id;
    countInput.value    = payload.count ?? 1;

    /* ================= BASE PRICE INIT ================= */
    const base = Math.round(Number(payload.basePrice || 0));
    baseOption.dataset.base  = base;
    baseOption.dataset.price = base;
    baseOption.textContent  = `Base Price – ₹${base}`;

    /* ================= RESET MONTH ================= */
    if (monthSelect) {
        monthSelect.disabled = false;
        monthSelect.value = 1;
    }

    /* ==================================================
       CASE 1: PACKAGE SELECTED FROM DETAILS PAGE
    ================================================== */
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

            const months =
                window.selectedPackageState.months ??
                parseInt(opt.dataset.months || 1);

            if (monthSelect) {
                monthSelect.value = months;
                monthSelect.disabled = true;
            }

            window.selectedPackageState = {
                id: opt.value,
                label: opt.dataset.label || opt.text,
                price: Number(opt.dataset.price || 0),
                months: months
            };

            syncEnquiryHiddenFields();
            return;
        }
    }

    /* ==================================================
       CASE 2: BASE PRICE (DEFAULT)
    ================================================== */

    enquiryPackage.value = 'base';

    if (hoardingType === 'dooh') {

        /* ---------- DOOH BASE ---------- */
        let opt = monthSelect.querySelector('option[value="10_sec"]');
        if (!opt) {
            opt = document.createElement('option');
            opt.value = '10_sec';
            opt.textContent = '10 Seconds';
            monthSelect.prepend(opt);
        }

        monthSelect.value = '10_sec';
        monthSelect.disabled = true;

        window.selectedPackageState = {
            id: 'base',
            label: `Base Price – ₹${base} (10 Seconds)`,
            price: base,
            months: 1
        };

        baseOption.textContent = `Base Price – ₹${base} (10 Seconds)`;

    } else {

        /* ---------- OOH BASE ---------- */
        window.selectedPackageState = {
            id: 'base',
            label: 'Base Price (1 Month)',
            price: base,
            months: 1
        };

        baseOption.textContent = `Base Price – ₹${base} (1 Month)`;
    }
    /* ================= GRACE PERIOD ================= */
    if (payload.graceDays) {
        const startDateInput = document.getElementById('enquiryStartDate');

        if (startDateInput) {
            const d = new Date();
            d.setDate(d.getDate() + Number(payload.graceDays));

            const yyyy = d.getFullYear();
            const mm   = String(d.getMonth() + 1).padStart(2, '0');
            const dd   = String(d.getDate()).padStart(2, '0');

            const minDate = `${yyyy}-${mm}-${dd}`;

            startDateInput.min = minDate;

            if (!startDateInput.value || startDateInput.value < minDate) {
                startDateInput.value = minDate;
            }
        }
    }

    syncEnquiryHiddenFields();
};
</script>
