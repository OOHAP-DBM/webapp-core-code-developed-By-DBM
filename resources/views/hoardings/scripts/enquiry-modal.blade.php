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

    // Always get hoardingType from input if present, else fallback to JS payload
    let hoardingType = null;
    const hoardingTypeInput = document.getElementById('hoardingType');
    if (hoardingTypeInput) {
        hoardingType = hoardingTypeInput.value;
    }
    // Defensive: If not set, fallback to window.selectedPackageState.type or 'ooh'
    if (!hoardingType) {
        hoardingType = window.selectedPackageState?.type || 'ooh';
    }
    if (!modal || !enquiryPackage || !baseOption) {
        console.error('[ERROR] Modal, enquiryPackage, or baseOption missing.');
        return;
    }

   

    /* ---------- CLOSE MODAL ---------- */
    window.closeEnquiryModal = function () {
        modal.classList.add('hidden');
    };

    /* ---------- BASE MODE HELPER ---------- */
    function setBaseMode(base) {
        enquiryPackage.value = 'base';

        if (hoardingType === 'dooh') {
            // ...existing code...
        }
        // ...existing code...
    }

    /* ---------- GLOBAL CLICK HANDLER (CARDS + LINKS) ---------- */

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.enquiry-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        // Defensive: Only open modal if all required data is present
        const id = btn.dataset.hoardingId;
        const basePrice = btn.dataset.basePrice;
        const hoardingType = btn.dataset.hoardingType;
        if (!id || !basePrice || !hoardingType) {
            console.error('[ERROR] Missing required data attributes for enquiry modal:', { id, basePrice, hoardingType });
            return;
        }
        openEnquiryModal({
            id: id,
            basePrice: Number(basePrice),
            graceDays: Number(btn.dataset.graceDays || 0),
            type: hoardingType,
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
        document.getElementById('hoardingType')?.value;

    const start = new Date(startDate);
    const end   = new Date(start);

    // 🔒 END DATE ALWAYS BASED ON STATE MONTHS
    end.setMonth(end.getMonth() + (window.selectedPackageState.months || 1));

    document.getElementById('enquiryEndDate').value =
        end.toISOString().slice(0, 10);

    document.getElementById('enquiryDurationType').value = 'months';

    /* =======================
       🔒 IMMUTABLE PACKAGE
    ======================= */
    if (window.selectedPackageState.id !== 'base') {
        // For packages, do not mutate label or price, always use original offer data
        document.getElementById('enquiryAmount').value = window.selectedPackageState.price;
        document.getElementById('enquiryPackageId').value = window.selectedPackageState.id;
        document.getElementById('enquiryPackageLabel').value = `${window.selectedPackageState.label} – ₹${window.selectedPackageState.price}`;
        return; // 🔥 ABSOLUTE STOP
    }

    /* =======================
       BASE PRICE ONLY
    ======================= */
    let priceLabel;
console.log('[DEBUG] Hoarding Type:', hoardingType);
    if (hoardingType === 'dooh') {
        priceLabel = `Base Price – ₹${window.selectedPackageState.price} (Per 10s Slot)`;
    } else {
        priceLabel =
            `Base Price – ₹${window.selectedPackageState.price} `
            + `(${window.selectedPackageState.months} Month`
            + `${window.selectedPackageState.months > 1 ? 's' : ''})`;
    }

    document.getElementById('enquiryAmount').value =
        window.selectedPackageState.price;

    document.getElementById('enquiryPackageId').value = '';

    document.getElementById('enquiryPackageLabel').value =
        priceLabel;
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

    // if (monthSelect) {
    //     monthSelect.addEventListener('change', syncEnquiryHiddenFields);
    // }



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
    console.log('[DEBUG] openEnquiryModal called with payload:', payload);

    const modal          = document.getElementById('enquiryModal');
    const hoardingInput  = document.getElementById('enquiryHoardingId');
    const countInput     = document.getElementById('enquiryCount');
    const enquiryPackage = document.getElementById('enquiryPackage');
    const baseOption     = document.getElementById('basePriceOption');
    const monthSelect    = document.getElementById('packageSelect');
    const hoardingTypeInput = document.getElementById('hoardingType');
    // Always set hoardingType from payload for correct modal context
    if (hoardingTypeInput && payload.type) {
        hoardingTypeInput.value = payload.type;
    }
    let hoardingType = hoardingTypeInput?.value || payload.type || 'ooh';

    if (!modal || !enquiryPackage || !baseOption) {
        console.error('[ERROR] Modal, enquiryPackage, or baseOption missing.');
        return;
    }

    /* ================= OPEN MODAL ================= */
    modal.classList.remove('hidden');
    if (!payload) {
        // Defensive: No payload, do not touch modal fields
        return;
    }
    hoardingInput.value = payload.id;
    countInput.value    = payload.count ?? 1;

    /* ================= BASE PRICE INIT ================= */
    const base = Math.round(Number(payload.basePrice || 0));
    baseOption.dataset.base  = base;

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
            // Always use the package's own months, label, and price
            const months = parseInt(opt.dataset.months || 1);
            if (monthSelect) {
                monthSelect.value = months;
                monthSelect.disabled = true;
            }
            window.selectedPackageState = {
                id: opt.value,
                label: opt.textContent.trim(),
                price: opt.getAttribute('data-price') ? parseInt(opt.getAttribute('data-price'), 10) : 0,
                months: months,
                type: hoardingType
            };
            syncEnquiryHiddenFields();
            modal.dispatchEvent(new CustomEvent('modal:open'));
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
        months: 1,
        type: hoardingType
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
    modal.dispatchEvent(new CustomEvent('modal:open'));
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
