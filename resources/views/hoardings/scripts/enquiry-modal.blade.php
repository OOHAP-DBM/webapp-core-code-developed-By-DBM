<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js"></script>

<style>
    /* Custom Select2 badge styling */
    .select2-results__option {
        padding: 10px 12px;
    }
    
    .select2-results__option.select2-results__option--highlighted {
        background-color: #f5faf7 !important;  
        color: #111827 !important;
    }
    
    .select2-results__option.select2-results__option--selected {
        background-color: #f5faf7 !important;  
        color: #111827 !important;
    }
    
    .select2-results__option .badge {
        display: inline-block;
        background-color: #f15858; 
        color: #ffffff; 
        padding: 4px 10px; 
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        margin-left: 8px;
        vertical-align: middle;
    }
    
    /* Increase height of Select2 container */
    .select2-container--default .select2-selection--single {
        height: 48px !important;
        padding: 6px 12px !important;
        border: 1px solid #e5e7eb !important;  /* Light gray border */
        border-radius: 8px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding: 0 !important;
    }
</style>

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
        const basePrice = btn.dataset.basePrice; // monthly_price ya base_monthly_price
        const baseMonthlyPrice = btn.dataset.baseMonthlyPrice; // hamesha base_monthly_price
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        // Defensive: Only open modal if all required data is present
        const id = btn.dataset.hoardingId;
        const hoardingType = btn.dataset.hoardingType;
        if (!id || !basePrice || !hoardingType) {
            console.error('[ERROR] Missing required data attributes for enquiry modal:', { id, basePrice, hoardingType });
            return;
        }
        openEnquiryModal({
            id: id,
            basePrice: Number(basePrice),
            baseMonthlyPrice: Number(btn.dataset.baseMonthlyPrice || 0), // <-- yeh line honi chahiye
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
   SYNC HIDDEN FIELDS - SIMPLIFIED
============================================================ */
function syncEnquiryHiddenFields() {
    const startDate = document.getElementById('enquiryStartDate')?.value;
    if (!startDate || !window.selectedPackageState) return;

    const hoardingType = document.getElementById('hoardingType')?.value;
    
    // Use package's fixed months if package selected, else use month selector
    let months = 1;
    if (window.selectedPackageState.id !== 'base') {
        months = window.selectedPackageState.months || 1;
    } else {
        const monthSelect = document.getElementById('packageSelect');
        months = monthSelect ? parseInt(monthSelect.value || 1) : 1;
    }

    // Set start date and months
    document.getElementById('enquiryHoardingId').value = document.getElementById('enquiryHoardingId')?.value || '';
    document.getElementById('enquiryStartDate').value = startDate;
    document.getElementById('enquiryDurationType').value = 'months';
    document.getElementById('enquiryMonths').value = months;

    // Calculate end date
    const start = new Date(startDate);
    const end = new Date(start);
    end.setMonth(end.getMonth() + months);
    document.getElementById('enquiryEndDate').value = end.toISOString().slice(0, 10);

    if (hoardingType === 'dooh') {
        /* ======= DOOH ======= */
        // Sync DOOH fields
        const videoDuration = document.querySelector('select[name="video_duration"]')?.value;
        const slotsCount = document.querySelector('input[name="slots_count"]')?.value;
        
        document.getElementById('enquiryVideoDuration').value = videoDuration || '';
        document.getElementById('enquirySlotsCount').value = slotsCount || '';
        
        // If package selected, post package_id and discount_percent
        if (window.selectedPackageState.id !== 'base') {
            document.getElementById('enquiryPackageId').value = window.selectedPackageState.id;
            document.getElementById('enquiryAmount').value = window.selectedPackageState.discountPercent || 0;
        } else {
            // Base price selected - NO package_id (null)
            document.getElementById('enquiryPackageId').value = '';
            document.getElementById('enquiryAmount').value = '';
        }

    } else {
        /* ======= OOH ======= */
        // Clear DOOH fields for OOH
        document.getElementById('enquiryVideoDuration').value = '';
        document.getElementById('enquirySlotsCount').value = '';
        
        // If package selected, post package_id and discount_percent
        if (window.selectedPackageState.id !== 'base') {
            document.getElementById('enquiryPackageId').value = window.selectedPackageState.id;
            document.getElementById('enquiryAmount').value = window.selectedPackageState.discountPercent || 0;
        } else {
            // Base price selected - NO package_id (null)
            document.getElementById('enquiryPackageId').value = '';
            document.getElementById('enquiryAmount').value = '';
        }
    }
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

    console.log('[DEBUG] monthSelect element found:', monthSelect);
    console.log('[DEBUG] enquiryPackage element found:', enquiryPackage);

    /* SYNC ON CHANGE EVENTS */
    if (startDateInput) {
        startDateInput.addEventListener('change', syncEnquiryHiddenFields);
    }

    if (monthSelect) {
        monthSelect.addEventListener('change', syncEnquiryHiddenFields);
    }

    /* SYNC DOOH FIELDS */
    const videoDurationSelect = document.querySelector('select[name="video_duration"]');
    const slotsCountInput = document.querySelector('input[name="slots_count"]');
    
    if (videoDurationSelect) {
        videoDurationSelect.addEventListener('change', syncEnquiryHiddenFields);
    }
    if (slotsCountInput) {
        slotsCountInput.addEventListener('change', syncEnquiryHiddenFields);
        slotsCountInput.addEventListener('input', syncEnquiryHiddenFields);
    }

    /* HANDLE PACKAGE SELECTION */
    if (enquiryPackage) {
        // Use Select2 event instead of native change event
        $(enquiryPackage).on('select2:select', function() {
            const selectedValue = this.value;
            const selectedOption = this.querySelector(`option[value="${selectedValue}"]`);
            
            // Always get fresh reference to monthInput
            const monthInput = document.getElementById('packageSelect');
            
            console.log('[DEBUG] Package selected via Select2:', selectedValue, 'monthInput:', !!monthInput);

            if (selectedValue === 'base') {
                // Base price selected - ENABLE month selection
                window.selectedPackageState = {
                    id: 'base',
                    label: selectedOption.textContent.trim(),
                    price: parseFloat(selectedOption.dataset.base || 0),
                    discountPercent: 0,
                    months: 1
                };
                
                // ENABLE month selector for flexible duration
                if (monthInput) {
                    monthInput.disabled = false;
                    monthInput.value = 1;
                    console.log('[DEBUG] ✅ Months input ENABLED (Base Price selected)');
                    console.log('[DEBUG] monthInput.disabled:', monthInput.disabled);
                } else {
                    console.error('[ERROR] monthInput element not found when enabling');
                }
            } else if (selectedOption) {
                // Package selected from API - DISABLE month selection
                const price = parseFloat(selectedOption.dataset.price || 0);
                const months = parseInt(selectedOption.dataset.months || 1);
                const label = selectedOption.dataset.label || selectedOption.textContent.trim();
                const discountPercent = parseInt(selectedOption.dataset.discountPercent || 0);
                const hoardingType = document.getElementById('hoardingType')?.value;

                window.selectedPackageState = {
                    id: selectedValue,
                    label: label,
                    price: price,
                    discountPercent: discountPercent,
                    months: months,
                    hoardingType: hoardingType
                };
                console.log('[DEBUG] Package selected state:', window.selectedPackageState);

                // DISABLE month selection for fixed packages
                if (monthInput) {
                    monthInput.value = months;  // Show package duration
                    monthInput.disabled = true; // DISABLE the input
                    console.log('[DEBUG] ✅ Months input DISABLED (Package selected:', label, ')');
                    console.log('[DEBUG] monthInput.disabled:', monthInput.disabled);
                    console.log('[DEBUG] monthInput.value:', monthInput.value, '(Package duration)');
                } else {
                    console.error('[ERROR] monthInput element not found when disabling');
                }
            }

            syncEnquiryHiddenFields();
        });
    }

    /* SYNC BEFORE FORM SUBMIT */
    if (enquiryForm) {
        enquiryForm.addEventListener('submit', function(e) {
            syncEnquiryHiddenFields();
            
            const hoardingType = document.getElementById('hoardingType')?.value;
            console.log('[DEBUG] Form Submitting for:', hoardingType);
        });
    }
});
</script>
<script>


/* ============================================================
   POPULATE PACKAGE OPTIONS (FROM API) WITH SELECT2
============================================================ */
function populatePackageOptions(selectElement, packages, baseMonthlyPrice, hoardingType) {
    console.log('[DEBUG] populatePackageOptions called with packages:', packages);

    // Clear all options except the base price option
    const baseOption = selectElement.querySelector('option[value="base"]');
    selectElement.innerHTML = '';
    selectElement.appendChild(baseOption);

    // Add package options from API
    if (packages && packages.length > 0) {
        packages.forEach(pkg => {
            const option = document.createElement('option');
            option.value = pkg.id;
            const discountPercent = pkg.discount_percent || 0;
            const months = pkg.months || pkg.duration || 1;

            // Sahi calculation for OOH
            let total = baseMonthlyPrice * months;
            let discount = (discountPercent > 0) ? (total * discountPercent / 100) : 0;
            let finalPrice = total - discount;

            let optionText = `${pkg.name} for ${months} Month${months > 1 ? 's' : ''} – ₹${number_format(finalPrice)} <span class="badge">SAVE ${discountPercent}%</span>`;

            option.innerHTML = optionText;
            option.dataset.price = finalPrice;
            option.dataset.label = pkg.name;
            option.dataset.months = months;
            option.dataset.discountPercent = discountPercent;
            option.dataset.isActive = pkg.is_active ? '1' : '0';
            selectElement.appendChild(option);
        });
    } else {
        console.log('[DEBUG] No packages to display, only base price available');
    }
    
    // Initialize Select2 on this element
    if ($(selectElement).data('select2')) {
        $(selectElement).select2('destroy');
    }
    $(selectElement).select2({
        allowHtml: true,
        width: '100%',
        templateResult: function(data) {
            if (!data.id) return data.text;
            return $('<span>' + data.element.innerHTML + '</span>');
        },
        templateSelection: function(data) {
            if (!data.id) return data.text;
            return $('<span>' + (data.element.innerHTML || data.text) + '</span>');
        }
    });
}

/* ============================================================
   NUMBER FORMAT HELPER
============================================================ */
function number_format(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

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
    let hoardingType = payload.type || hoardingTypeInput?.value || 'ooh';

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

    /* ================= FETCH PACKAGES FROM API ================= */
    console.log('[DEBUG] Fetching packages for hoarding:', payload.id);
    fetch(`/api/hoardings/${payload.id}/packages`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.packages !== undefined) {
                console.log('[DEBUG] Packages fetched:', data.packages);
                // Update hoardingType from API response
                const apiHoardingType = data.hoarding_type || hoardingType;
                if (hoardingTypeInput) {
                    hoardingTypeInput.value = apiHoardingType;
                }
                console.log('[DEBUG] Hoarding Type from API:', apiHoardingType);
                
                // Show/hide DOOH fields based on hoarding type
                const doohFields = document.getElementById('doohFields');
                const doohNote = document.getElementById('doohNote');
                if (apiHoardingType === 'dooh') {
                    if (doohFields) doohFields.style.display = '';
                    if (doohNote) doohNote.style.display = '';
                } else {
                    if (doohFields) doohFields.style.display = 'none';
                    if (doohNote) doohNote.style.display = 'none';
                }
                
                populatePackageOptions(
                    enquiryPackage,
                    data.packages,
                    payload.baseMonthlyPrice || payload.basePrice,
                    apiHoardingType
                );
                
                // Wait for Select2 to be initialized before proceeding
                setTimeout(() => {
                    // After packages are populated, restore previously selected package if it exists
                    if (
                        window.selectedPackageState &&
                        window.selectedPackageState.id &&
                        window.selectedPackageState.id !== 'base'
                    ) {
                        const packageId = window.selectedPackageState.id;
                        const opt = enquiryPackage.querySelector(`option[value="${packageId}"]`);
                        
                        if (opt) {
                            console.log('[DEBUG] Restoring previously selected package:', packageId);
                            enquiryPackage.value = packageId;
                            $(enquiryPackage).trigger('change');
                            
                            // Disable month selection for this package
                            if (monthSelect) {
                                const months = parseInt(opt.dataset.months || 1);
                                monthSelect.value = months;
                                monthSelect.disabled = true;
                            }
                            
                            // Update state with correct data from option
                            const price = parseInt(opt.dataset.price || 0);
                            const months = parseInt(opt.dataset.months || 1);
                            const discountPercent = parseInt(opt.dataset.discountPercent || 0);
                            window.selectedPackageState = {
                                id: packageId,
                                label: opt.dataset.label || opt.textContent.trim(),
                                price: price,
                                discountPercent: discountPercent,
                                months: months,
                                type: apiHoardingType
                            };
                            
                            syncEnquiryHiddenFields();
                            return;
                        }
                    }
                    
                    // No previously selected package - use base price
                    setBaseMode(payload.basePrice, apiHoardingType);
                }, 100);
            } else {
                console.warn('[WARN] No packages in API response');
                setBaseMode(payload.basePrice, hoardingType);
            }
        })
        .catch(err => {
            console.error('[ERROR] Failed to fetch packages:', err);
        });

    /* ================= BASE PRICE INIT ================= */
    const base = Math.round(Number(payload.basePrice || 0));
    baseOption.dataset.base  = base;
    baseOption.dataset.price = base;
    baseOption.dataset.label = 'Base Price';
    baseOption.dataset.months = '1';
    baseOption.dataset.discountPercent = '0';

    /* ================= RESET MONTH ================= */
    if (monthSelect) {
        monthSelect.disabled = false;
        monthSelect.value = 1;
    }

    /* ==================================================
         CASE 1: PACKAGE SELECTED FROM DETAILS PAGE
     ================================================== */
    // REMOVED - Now handled asynchronously after API call
    // This prevents race conditions with Select2 initialization

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
        label: hoardingType === 'dooh' ? `Base Price – ₹${base} (Per Second)` : `Base Price – ₹${base} (1 Month)`,
        price: base,
        months: 1,
        type: hoardingType
    };
    baseOption.textContent = hoardingType === 'dooh'
        ? `Base Price – ₹${base} (Per Second)`
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
    setTimeout(() => {
        modal.dispatchEvent(new CustomEvent('modal:open'));
    }, 50);
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
            
            // Sync all hidden fields before submission
            syncEnquiryHiddenFields();
            
            console.log('[DEBUG] Submitting form with action:', form.action);
            console.log('[DEBUG] Form data:');
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                console.log(`  ${key}:`, value);
            }

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        .content
                }
            })
            .then(res => {
                console.log('[DEBUG] Response status:', res.status);
                return res.json().then(data => ({ status: res.status, data: data }));
            })
            .then(({ status, data }) => {
                console.log('[DEBUG] Response data:', data);
                
                if (!data.success) {
                    console.error('[ERROR] Validation failed:', data);
                    
                    // Show validation errors
                    if (data.errors) {
                        let errorMsg = 'Validation Errors:\n\n';
                        for (let field in data.errors) {
                            errorMsg += `${field}: ${data.errors[field].join(', ')}\n`;
                        }
                        alert(errorMsg);
                    } else if (data.message) {
                        alert('Error: ' + data.message);
                    } else {
                        alert('Submission failed. Please check your input.');
                    }
                    return;
                }

                enquiryToast.fire({
                    icon: 'success',
                    title: 'Enquiry Submit SuccessFully',
                });

                closeEnquiryModal();
                form.reset();
            })
            .catch((err) => {
                console.error('[ERROR] Fetch error:', err);
                enquiryToast.fire({
                    icon: 'error',
                    title: 'Network Error',
                    html: 'Failed to submit enquiry. Please try again.'
                });
            });
        });

    });
</script>
