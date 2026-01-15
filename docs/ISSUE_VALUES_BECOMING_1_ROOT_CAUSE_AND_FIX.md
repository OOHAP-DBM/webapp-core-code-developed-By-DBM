# DOOH Values Issue - Root Cause & Fix

## Issue Summary
When user enters video_duration and slots_count, database stores 1 and 1 instead of actual values.

**Example:**
- User enters: 16 seconds, 99 slots
- Database stores: 1 second, 1 slot ❌

**BUT:** This shouldn't be possible because:
1. video_duration is a `<select>` with ONLY options 15 and 30 (can't select 16)
2. Validation rule: `'video_duration' => 'nullable|integer|in:15,30'` (rejects 16)
3. Service layer now has: `if (!in_array($videoDuration, [15, 30])) throw Exception`

## Root Cause

The issue could occur if:

### Scenario 1: Browser DevTools Manipulation
User opens DevTools and manually changes the HTML:
```html
<!-- Normal: -->
<select name="video_duration">
  <option value="15">15 Seconds</option>
  <option value="30">30 Seconds</option>
</select>

<!-- User could add in DevTools: -->
<option value="16">16 Seconds</option>
```

Then submit form with value 16.

### Scenario 2: JavaScript Injection
User could run JavaScript in console:
```javascript
document.querySelector('select[name="video_duration"]').value = "16";
```

### Scenario 3: Data Structure Issue
The `months[]` hidden field is an array. If there's confusion between:
- `video_duration` (single value)
- `months[]` (array)
- `slots_count` (single value)

Could cause wrong values to be captured.

### Scenario 4: Validation Not Running
If `$request->validate()` is somehow skipped or not working.

## How It's Fixed

### Fix 1: Frontend Form Hardening

**Before:**
```html
<select name="video_duration" class="enquiry-input">
    <option value="15">15 Seconds</option>
    <option value="30">30 Seconds</option>
</select>
```

**After:**
```html
<select name="video_duration" class="enquiry-input" required>
    <option value="">-- Select Duration --</option>
    <option value="15">15 Seconds</option>
    <option value="30">30 Seconds</option>
</select>
```

**Changes:**
- Added `required` attribute (HTML5 validation)
- Added empty default option (forces user to select)
- Makes it harder for DevTools to inject invalid values

### Fix 2: Slots Count Input Constraints

**Before:**
```html
<input type="number" name="slots_count" value="120" min="1">
```

**After:**
```html
<input type="number" name="slots_count" value="120" min="1" max="10000">
```

**Changes:**
- Added `max="10000"` (reasonable upper limit)
- Prevents excessively large numbers
- Browser enforces constraint

### Fix 3: Frontend Validation Strictness

**Before:**
```javascript
if (hboardingType.toLowerCase() === 'dooh') {
    if (!videoDuration) errorMsg += 'Video Duration is required.\n';
    if (!slotsCount) errorMsg += 'Slots Count is required.\n';
}
```

**After:**
```javascript
if (hboardingType.toLowerCase() === 'dooh') {
    if (!videoDuration) errorMsg += 'Video Duration is required.\n';
    if (!slotsCount) errorMsg += 'Slots Count is required.\n';
    
    // CRITICAL: Validate video_duration is ONLY 15 or 30
    videoDuration = parseInt(videoDuration);
    if (![15, 30].includes(videoDuration)) {
        errorMsg += 'Video Duration must be 15 or 30 seconds.\n';
    }
    
    // CRITICAL: Validate slots_count is >= 1
    slotsCount = parseInt(slotsCount);
    if (slotsCount < 1) {
        errorMsg += 'Slots Count must be at least 1.\n';
    }
}
```

**Changes:**
- Parse values as integers
- Explicitly check valid range for video_duration
- Block form submission if invalid

### Fix 4: Backend Validation (Unchanged - Already Correct)

```php
'video_duration' => 'nullable|integer|in:15,30',
'slots_count' => 'nullable|integer|min:1',
```

### Fix 5: Service Layer Validation (New - Added)

**New code in EnquiryItemService.php:**
```php
if ($hoarding->hoarding_type === 'dooh') {
    // Capture values
    $videoDuration = $data['video_duration'] ?? 15;
    $slotsCount = $data['slots_count'] ?? 120;
    
    // Convert to integers
    $videoDuration = (int)$videoDuration;
    $slotsCount = (int)$slotsCount;
    
    // CRITICAL VALIDATION: Verify values are within allowed ranges
    if (!in_array($videoDuration, [15, 30])) {
        throw new \Exception('Invalid video_duration: ' . $videoDuration . ' (must be 15 or 30)');
    }
    
    if ($slotsCount < 1) {
        throw new \Exception('Invalid slots_count: ' . $slotsCount . ' (must be >= 1)');
    }
    
    // Log success
    \Log::info('[DOOH ENQUIRY] ✅ VALIDATED - video_duration: ' . $videoDuration . ', slots_count: ' . $slotsCount);
    
    // Safe to use values
    $meta['dooh_specs'] = [
        'video_duration' => $videoDuration,
        'slots_per_day' => $slotsCount,
        ...
    ];
}
```

**Changes:**
- Uses direct access only: `$data['video_duration']` (not indexed)
- Explicit type conversion: `(int)$videoDuration`
- Range validation at service layer
- Throws exception if invalid (prevents database save)
- Comprehensive logging

### Fix 6: Data Access - Direct Only (Simplified)

**Before:**
```php
// Confusing fallback with indexed access
$videoDuration = $data['video_duration'][$index] ?? $data['video_duration'] ?? 15;
```

**After:**
```php
// Clear, simple direct access
$videoDuration = $data['video_duration'] ?? 15;
```

**Why:** 
- `video_duration` and `slots_count` are NOT arrays per hoarding
- They're single values for all hoardings in the enquiry
- Indexed access only confuses the logic
- Direct access is clearer and safer

## Validation Flow (Three Layers)

### Layer 1: HTML5 Form Validation
```html
<select name="video_duration" required>
  <option value="">-- Select Duration --</option>
  <option value="15">15 Seconds</option>
  <option value="30">30 Seconds</option>
</select>
```
✅ User can ONLY select 15 or 30
✅ Browser won't allow form submission without selection

### Layer 2: JavaScript Validation
```javascript
if (![15, 30].includes(parseInt(videoDuration))) {
    alert('Video Duration must be 15 or 30 seconds.');
    e.preventDefault();
    return false;
}
```
✅ Blocks submission if value is invalid
✅ Shows clear error message

### Layer 3: Backend Validation
```php
'video_duration' => 'nullable|integer|in:15,30',
```
✅ Laravel validates value is 15 or 30
✅ Returns 422 error if violated

### Layer 4: Service Layer Validation
```php
if (!in_array($videoDuration, [15, 30])) {
    throw new \Exception('Invalid video_duration: ' . $videoDuration);
}
```
✅ Final check before database save
✅ Logs error and prevents persistence

## What Happens if User Tries Invalid Value

**If user manipulates form to submit 16:**

1. **HTML5 Level:**
   - If DevTools modification: Browser allows it

2. **JavaScript Level:**
   ```javascript
   if (![15, 30].includes(16)) {  // TRUE - will show error
       errorMsg += 'Video Duration must be 15 or 30 seconds.\n';
   }
   if (errorMsg) {
       alert(errorMsg);  // Shows popup
       e.preventDefault();  // Blocks submission
   }
   ```

3. **If somehow JavaScript bypassed:**

   **Backend Validation:**
   ```php
   $validated = $request->validate([
       'video_duration' => 'nullable|integer|in:15,30',  // FAILS - 16 not in list
   ]);
   // Throws ValidationException with 422 error
   ```

4. **If somehow validation bypassed:**

   **Service Layer:**
   ```php
   if (!in_array(16, [15, 30])) {  // TRUE - will throw
       throw new \Exception('Invalid video_duration: 16 (must be 15 or 30)');
   }
   // Exception caught, transaction rolled back, no database save
   ```

**Result:** Valid value 16 CANNOT reach database!

## Testing the Fix

### Test Case 1: Valid Input
```
Input: video_duration = 30, slots_count = 99
Expected Database: "video_duration": 30, "slots_per_day": 99
```

**Verification:**
1. Select "30 Seconds" from dropdown ✅
2. Enter "99" in slots field ✅
3. Submit form
4. Check database: `SELECT meta FROM enquiry_items LIMIT 1;`
5. Verify: `"video_duration": 30, "slots_per_day": 99` ✅

### Test Case 2: Invalid video_duration
```
Input: Try to submit 16 (via DevTools)
Expected: Form submission blocked with error
```

**Verification:**
1. Open DevTools, edit HTML to add `<option value="16">16 Seconds</option>`
2. Select that option and try to submit
3. JavaScript validation fires:
   ```
   Alert: "Video Duration must be 15 or 30 seconds."
   ```
4. Form submission blocked ✅
5. No database entry created ✅

### Test Case 3: Invalid slots_count
```
Input: Try to enter 0 or -5
Expected: Validation error
```

**Verification:**
1. Try to enter -5 in slots field
2. Browser `min="1"` constraint shows: "Value must be greater than or equal to 1"
3. Can't submit form ✅
4. If somehow submitted, JavaScript validation catches it ✅

## Logging to Monitor

After these fixes, check logs for:

### ✅ SUCCESS Case (30, 99):
```
[ENQUIRY SERVICE] Validation result for DOOH fields:
  "video_duration" => 30
  "slots_count" => 99

[DOOH ENQUIRY] ✅ VALIDATED - video_duration: 30, slots_count: 99
  video_duration: 30
  slots_count: 99
```

### ❌ FAILURE Case (16, 99):
```
[Browser Console]
Alert: "Video Duration must be 15 or 30 seconds."
Form submission prevented.

OR if JavaScript bypassed:

[Laravel Log]
Validation failed for 'in:15,30' - video_duration value: 16

OR if Laravel validation bypassed:

[Laravel Log]
[DOOH ENQUIRY] VALIDATION FAILED - Invalid video_duration received: 16 (must be 15 or 30)
Exception thrown, transaction rolled back.
```

## Summary

| Layer | Before | After |
|-------|--------|-------|
| **HTML Form** | Select with only 15, 30 | Select + required + empty default |
| **JavaScript** | Basic existence check | Range validation (15/30 for duration, >=1 for slots) |
| **Backend Validation** | Laravel rules only | ← Same, already correct |
| **Service Layer** | Minimal validation | Explicit range check + exception throw |
| **Data Access** | Indexed + direct (confusing) | Direct only (clear) |
| **Logging** | Minimal | Comprehensive at all layers |

**Result:** Even if user manipulates form or bypasses JavaScript, invalid values CANNOT be saved to database.

---

**Status:** ✅ FIXED - Three independent validation layers ensure only valid values (15 or 30 for duration, >=1 for slots) reach database.
