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

       const rawPrice = opt.getAttribute('data-price');

        window.selectedPackageState = {
            id: this.value,
            label: opt.dataset.label || '',
            price: rawPrice ? parseInt(rawPrice, 10) : 0,
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
    // 🔒 ONLY BASE PRICE
    if (enquiryPackage.value !== 'base') return;

    if (hoardingType === 'dooh' && monthSelect.value === '10_sec') return;

    const months = Math.max(1, parseInt(monthSelect.value || 1, 10));
    const base   = Number(baseOption.dataset.base || 0);
    const price  = Math.round(base * months);

    baseOption.textContent =
        `Base Price – ₹${price} (${months} Month${months > 1 ? 's' : ''})`;

    baseOption.dataset.price = price;

    window.selectedPackageState = {
        id: 'base',
        label: `Base Price (${months} Month${months > 1 ? 's' : ''})`,
        price: price,
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

    const hoardingType =
        document.getElementById('hoardingType')?.value; // ✅ FIX

    const start = new Date(startDate);
    let end = new Date(start);

    if (hoardingType === 'dooh') {
        // DOOH: always months selection, duration type is months
        end.setMonth(end.getMonth() + window.selectedPackageState.months);
        document.getElementById('enquiryDurationType').value = 'months';
    } else {
        // OOH: months selection
        end.setMonth(end.getMonth() + window.selectedPackageState.months);
        document.getElementById('enquiryDurationType').value = 'months';
    }

    document.getElementById('enquiryEndDate').value = end.toISOString().slice(0, 10);

    // Pricing: always show unit price, do not multiply by months
    let priceLabel = window.selectedPackageState.label || '';
    let priceValue = Number(window.selectedPackageState.price);
    if (hoardingType === 'dooh') {
        priceLabel += ' (Per 10s Slot)';
    }
    document.getElementById('enquiryAmount').value = priceValue;
    document.getElementById('enquiryPackageId').value = window.selectedPackageState.id === 'base' ? '' : window.selectedPackageState.id;
    document.getElementById('enquiryPackageLabel').value = priceLabel;
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
    if (!payload) {
        // Cart page apna data khud handle karta hai
        // Yahan kisi payload-based field ko touch mat karo
        return;
    }
    hoardingInput.value = payload.id;
    countInput.value    = payload.count ?? 1;

    /* ================= BASE PRICE INIT ================= */
    const base = Math.round(Number(payload.basePrice || 0));
    baseOption.dataset.base  = base;
    // baseOption.dataset.price = base;
    // baseOption.textContent  = `Base Price – ₹${base}`;

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

            const rawPrice = opt.getAttribute('data-price');

            window.selectedPackageState = {
                id: opt.value,
                label: opt.dataset.label || opt.text,
                price: rawPrice ? parseInt(rawPrice, 10) : 0,
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

    // Both OOH and DOOH: allow 1-12 months selection
    if (monthSelect) {
        monthSelect.disabled = false;
        monthSelect.value = 1;
    }
    window.selectedPackageState = {
        id: 'base',
        label: hoardingType === 'dooh' ? `Base Price – ₹${base}` : 'Base Price (1 Month)',
        price: base,
        months: 1
    };
    baseOption.textContent = hoardingType === 'dooh'
        ? `Base Price – ₹${base} (Per 10s Slot)`
        : `Base Price – ₹${base} (1 Month)`;
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const enquiryToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });

    document.addEventListener('DOMContentLoaded', function () {

        const form = document.getElementById('enquiryForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .content
                }
            })
            .then(res => res.json())
            .then(data => {

                if (!data.success) throw new Error();

                enquiryToast.fire({
                    icon: 'success',
                    title: 'Enquiry Submitted',
                    html: `<small>Enquiry ID: <b>#${data.enquiry_id}</b></small>`
                });

                closeEnquiryModal();
                form.reset();
            })
            .catch(() => {
                enquiryToast.fire({
                    icon: 'error',
                    title: 'Something went wrong'
                });
            });
        });

    });
</script>
