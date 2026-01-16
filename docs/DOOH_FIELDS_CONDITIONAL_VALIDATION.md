# DOOH Fields - Conditional Validation Fix

## Issue
OOH hoarding enquiries were not submitting because DOOH field validation was running for both OOH and DOOH hoardings.

**Solution:** DOOH field validation is **ONLY for DOOH hoardings**, not for OOH.

## What Changed

### 1. Frontend Validation Logic (enquiry-modal.blade.php)

**Before:**
```javascript
if (hoardingType.toLowerCase() === 'dooh') {
    // Validate DOOH fields
}
```

**After:**
```javascript
var isDoohHoarding = hboardingType && hboardingType.toLowerCase() === 'dooh';

if (isDoohHoarding) {
    console.log('[DEBUG] DOOH HOARDING - Validating DOOH fields');
    // Validate DOOH fields
    if (!videoDuration) errorMsg += 'Video Duration is required.\n';
    if (!slotsCount) errorMsg += 'Slots Count is required.\n';
    ...
} else {
    console.log('[DEBUG] OOH HOARDING - Skipping DOOH field validation');
    // No validation for OOH - submit form as-is
}
```

**Impact:**
- ✅ OOH hoardings skip DOOH field validation completely
- ✅ DOOH hoardings validate video_duration and slots_count
- ✅ Clear console logs showing which path is taken

### 2. Consistent Type Checking (enquiry-modal.blade.php)

**Before:**
- Some checks: `if (hoardingType === 'dooh')`
- Some checks: `if (hboardingType.toLowerCase() === 'dooh')`
- Inconsistent comparison methods

**After:**
- All checks use: `hboardingType.toLowerCase() === 'dooh'`
- All checks include fallback: `|| ''` to handle null/undefined
- Consistent throughout file

### 3. Backend Service Layer (EnquiryItemService.php)

**Already Correct:**
```php
if ($hoarding->hoarding_type === 'dooh') {
    // ONLY validate DOOH fields for DOOH hoardings
    if (!in_array($videoDuration, [15, 30])) {
        throw new \Exception('Invalid video_duration...');
    }
}
// For OOH hoardings, this block is skipped entirely
```

✅ Service layer properly skips DOOH validation for OOH hoardings

## Validation Flow for Each Type

### For OOH Hoardings
```
1. Frontend: Checks hboardingType
   ├─ Is it 'dooh'? NO
   └─ Skip DOOH field validation ✅

2. Field Removal: Check type again
   ├─ Is it 'dooh'? NO
   └─ Remove video_duration and slots_count fields from form ✅

3. Form Submission
   ├─ POST request WITHOUT DOOH fields
   └─ Backend receives: hoarding_id, months, customer_info (no video_duration, no slots_count) ✅

4. Backend Validation (EnquiryService)
   ├─ Validates hoarding_id: ✅
   ├─ Validates customer_info: ✅
   ├─ SKIPS video_duration validation (nullable) ✅
   ├─ SKIPS slots_count validation (nullable) ✅
   └─ Returns validated data ✅

5. Service Processing (EnquiryItemService)
   ├─ Checks hoarding type
   ├─ Is it 'dooh'? NO
   ├─ Skip DOOH meta building
   └─ Build meta without dooh_specs ✅

6. Database
   ├─ Saves enquiry_item
   ├─ meta field does NOT contain dooh_specs ✅
   └─ Enquiry created successfully ✅
```

### For DOOH Hoardings
```
1. Frontend: Checks hboardingType
   ├─ Is it 'dooh'? YES
   ├─ Validate video_duration (must be 15 or 30) ✅
   ├─ Validate slots_count (must be >= 1) ✅
   └─ Show/hide DOOH fields ✅

2. Field Removal: Check type again
   ├─ Is it 'dooh'? YES
   └─ KEEP video_duration and slots_count fields ✅

3. Form Submission
   ├─ POST request WITH DOOH fields
   └─ Backend receives: hoarding_id, months, video_duration, slots_count, customer_info ✅

4. Backend Validation (EnquiryService)
   ├─ Validates hoarding_id: ✅
   ├─ Validates customer_info: ✅
   ├─ Validates video_duration: must be in [15, 30] ✅
   ├─ Validates slots_count: must be >= 1 ✅
   └─ Returns validated data ✅

5. Service Processing (EnquiryItemService)
   ├─ Checks hoarding type
   ├─ Is it 'dooh'? YES
   ├─ Capture video_duration and slots_count
   ├─ Validate: video_duration must be 15 or 30 ✅
   ├─ Validate: slots_count must be >= 1 ✅
   ├─ Build dooh_specs with validated values ✅
   └─ Save to meta.dooh_specs ✅

6. Database
   ├─ Saves enquiry_item
   ├─ meta field contains dooh_specs with actual values ✅
   └─ Enquiry created successfully ✅
```

## Console Logs

### OOH Hoarding Submission
```
[DEBUG] Form submitting with:
  hoardingType: ooh
  video_duration: undefined
  slots_count: undefined
  all form data: FormData {...}
[DEBUG] OOH HOARDING - Skipping DOOH field validation
[DEBUG] ✅ Submitting OOH hoarding without DOOH fields
```

### DOOH Hoarding Submission
```
[DEBUG] Form submitting with:
  hoardingType: dooh
  video_duration: 30
  slots_count: 120
  all form data: FormData {...}
[DEBUG] DOOH HOARDING - Validating DOOH fields
[DEBUG] DOOH Validation - videoDuration: 30 (must be 15 or 30), slotsCount: 120 (must be >= 1)
[DEBUG] ✅ Submitting DOOH hoarding with video_duration: 30 and slots_count: 120
```

## Testing

### Test 1: OOH Hoarding Enquiry ✅
1. Open OOH hoarding detail page
2. Click "Raise an Enquiry"
3. Notice: Video Duration and Slots fields are **HIDDEN**
4. Fill form:
   - Full Name, Email, Mobile, Start Date
5. Submit form
6. Expected:
   - ✅ No validation error about DOOH fields
   - ✅ Form submits successfully
   - ✅ Enquiry created in database
   - ✅ Console shows "OOH HOARDING - Skipping DOOH field validation"

### Test 2: DOOH Hoarding Enquiry ✅
1. Open DOOH hoarding detail page
2. Click "Raise an Enquiry"
3. Notice: Video Duration and Slots fields are **VISIBLE**
4. Fill form:
   - Full Name, Email, Mobile, Start Date
   - Video Duration: **Select value (15 or 30)**
   - Slots per Day: **Enter value (>= 1)**
5. Submit form
6. Expected:
   - ✅ Validates video_duration and slots_count
   - ✅ Shows error if invalid values
   - ✅ Form submits successfully if valid
   - ✅ Enquiry created with dooh_specs in database
   - ✅ Console shows "DOOH HOARDING - Validating DOOH fields"

### Test 3: Invalid DOOH Values ✅
1. Open DOOH hoarding detail page
2. Click "Raise an Enquiry"
3. Try invalid values:
   - Video Duration: Leave empty or try to submit 16
   - Slots per Day: Try to enter 0 or negative
4. Expected:
   - ✅ Validation errors shown
   - ✅ Form submission blocked
   - ✅ Clear error messages

## Files Modified

1. **resources/views/hoardings/partials/enquiry-modal.blade.php**
   - Fixed DOOH validation to only run for DOOH hoardings
   - Made toggleDoohFields use consistent `.toLowerCase()` comparison
   - Added explicit isDoohHoarding flag
   - Added clear console logs

2. **Modules/Enquiries/Services/EnquiryItemService.php** (via attachment)
   - Already correctly validates ONLY for DOOH hoardings
   - Service layer skips DOOH meta building for OOH

## Summary

✅ **OOH Enquiries:** Don't need to fill DOOH fields, validation skips them, form submits normally
✅ **DOOH Enquiries:** Must fill DOOH fields, validation checks them, form only submits if valid
✅ **Consistent Type Checking:** All comparisons use `.toLowerCase()` for case-insensitivity
✅ **Clear Logging:** Console shows which path is taken (OOH or DOOH)
✅ **No Silent Failures:** Error messages are clear for both types

---

**Status:** ✅ FIXED - OOH and DOOH enquiries now have independent validation requirements
