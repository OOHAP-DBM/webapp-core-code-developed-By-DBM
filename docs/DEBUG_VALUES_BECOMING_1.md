# DOOH Enquiry Values - Debugging Issue: 16 and 99 Becoming 1 and 1

## Problem Statement
**User enters:** video_duration = 16, slots_count = 99
**Database stores:** video_duration = 1, slots_per_day = 1

**This should NOT happen!**

## Root Cause Analysis

### Expected Behavior
1. **Form Selection (video_duration):**
   - HTML: `<select name="video_duration">` with only options 15 and 30
   - User CAN'T select 16 (not an option in dropdown)
   - If user somehow submits 16, validation should FAIL

2. **Form Input (slots_count):**
   - HTML: `<input type="number" name="slots_count" min="1">`
   - User can enter any number >= 1
   - 99 is valid

3. **Frontend Validation:**
   ```javascript
   if (![15, 30].includes(videoDuration)) {
       errorMsg += 'Video Duration must be 15 or 30 seconds.\n';
   }
   ```
   - If user somehow submits 16, this BLOCKS submission

4. **Backend Validation:**
   ```php
   'video_duration' => 'nullable|integer|in:15,30',
   ```
   - If somehow form field is manipulated and 16 reaches backend
   - Validation REJECTS it (400 error)

5. **Service Processing:**
   ```php
   // CRITICAL VALIDATION: video_duration MUST be 15 or 30
   if (!in_array($videoDuration, [15, 30])) {
       throw new \Exception('Invalid video_duration: ' . $videoDuration);
   }
   ```
   - Throws exception if invalid

**Expected Result for valid input (30, 99):**
```json
{
  "dooh_specs": {
    "video_duration": 30,
    "slots_per_day": 99
  }
}
```

**Expected Result for invalid input (16, 99):**
- Form submission blocked at frontend, OR
- 422 Validation error from backend, OR
- Exception thrown in service layer

### Actual Behavior
Database stores: video_duration = 1, slots_per_day = 1

This indicates either:
1. **Value conversion bug:** "16" and "99" somehow becoming "1"
2. **Data structure issue:** Wrong array access causing corruption
3. **Defaults being used:** Original values 15, 120 being replaced with 1, 1
4. **Validation bypassed:** Invalid values reaching database

## Investigation Steps

### Step 1: Check Form Submission in Browser
**What to look for in browser console:**

**If user enters 16 (invalid):**
```javascript
[DEBUG] DOOH Validation - videoDuration: 16 (must be 15 or 30), slotsCount: 99 (must be >= 1)
// Should show ERROR:
// "Video Duration must be 15 or 30 seconds."
// Form should NOT submit
```

**If user selects 30 (valid) and enters 99 (valid):**
```javascript
[DEBUG] DOOH Validation - videoDuration: 30 (must be 15 or 30), slotsCount: 99 (must be >= 1)
// No errors - form should submit
```

### Step 2: Check Network Request
**When form submits (if validation passes):**
```
POST /enquiries/store HTTP/1.1
Content-Type: application/x-www-form-urlencoded

video_duration=30
slots_count=99
hoarding_type=dooh
...
```

**What the server receives:**
```php
$_POST = [
    'video_duration' => '30',   // String!
    'slots_count' => '99',      // String!
    'hoarding_type' => 'dooh'
]
```

### Step 3: Check Laravel Validation Log
**In storage/logs/laravel.log:**

```
[ENQUIRY SERVICE] Validation result for DOOH fields:
[
  "video_duration" => 30      // After validation, should be integer
  "slots_count" => 99         // After validation, should be integer
]
```

**If validation FAILS (video_duration=16):**
```
[FAIL] Validation failed for video_duration
// Should show 422 error response
```

### Step 4: Check Service Processing Log
**In storage/logs/laravel.log:**

**If values are VALID (30, 99):**
```
[DOOH ENQUIRY] ✅ VALIDATED - video_duration: 30, slots_count: 99
```

**If values are INVALID (16, ...):**
```
[DOOH ENQUIRY] VALIDATION FAILED - Invalid video_duration received: 16 (must be 15 or 30)
// Should throw exception, no database save
```

### Step 5: Check Database Stored Value
**Query database:**
```sql
SELECT meta FROM enquiry_items 
WHERE hoarding_type = 'dooh' 
ORDER BY created_at DESC LIMIT 1;
```

**Expected (for 30, 99):**
```json
{
  "dooh_specs": {
    "video_duration": 30,
    "slots_per_day": 99
  }
}
```

**Actual (problem case):**
```json
{
  "dooh_specs": {
    "video_duration": 1,
    "slots_per_day": 1
  }
}
```

## What to Check

### Check 1: Is Validation Running?
If video_duration value is 16 (invalid), and database stores 1:
- ❌ Validation is NOT running (invalid value passed)
- ❌ Service is NOT validating (allows 16)
- ❌ Value corruption happening somewhere

### Check 2: Is Value Conversion Corrupting Data?
```php
$videoDuration = (int)"16";  // Should be 16, not 1
$slotsCount = (int)"99";     // Should be 99, not 1
```
This would only give 1 if the original value was "1" or similar.

### Check 3: Is Indexed Array Access Corrupting Data?
```php
// BAD: If $data['video_duration'] is an array:
$videoDuration = $data['video_duration'][$index];  // Could be wrong value
```

## Solution: Strict Value Validation

### Frontend (enquiry-modal.blade.php)
```javascript
// Before form submit:
if (![15, 30].includes(parseInt(videoDuration))) {
    alert('Video Duration must be 15 or 30 seconds.');
    return false;
}

if (parseInt(slotsCount) < 1) {
    alert('Slots Count must be at least 1.');
    return false;
}
```

### Backend (EnquiryService.php)
```php
'video_duration' => 'nullable|integer|in:15,30',
'slots_count' => 'nullable|integer|min:1',
```

### Service Layer (EnquiryItemService.php)
```php
if (!in_array($videoDuration, [15, 30])) {
    throw new \Exception('Invalid video_duration: ' . $videoDuration);
}

if ($slotsCount < 1) {
    throw new \Exception('Invalid slots_count: ' . $slotsCount);
}
```

## Testing the Fix

### Test 1: Valid Input (Should Save)
1. Select video_duration: **30 Seconds**
2. Enter slots_count: **99**
3. Submit form
4. Check database - should have: `"video_duration": 30, "slots_per_day": 99`

### Test 2: Invalid video_duration (Should Block)
1. Try to submit 16 seconds (not in dropdown - shouldn't be possible)
2. If somehow submitted, validation should block it
3. Check logs for: `VALIDATION FAILED - Invalid video_duration: 16`

### Test 3: Invalid slots_count (Should Block)
1. Try to enter 0 or negative number
2. Validation should block it (min:1)
3. Check logs for: `VALIDATION FAILED - Invalid slots_count`

## Critical Code Changes Made

### 1. Frontend Validation (enquiry-modal.blade.php)
```javascript
// NEW: Explicit range check for video_duration
if (![15, 30].includes(videoDuration)) {
    errorMsg += 'Video Duration must be 15 or 30 seconds.\n';
}

// NEW: Minimum check for slots_count
if (slotsCount < 1) {
    errorMsg += 'Slots Count must be at least 1.\n';
}
```

### 2. Service Layer Validation (EnquiryItemService.php)
```php
// NEW: Explicit validation after capturing values
if (!in_array($videoDuration, [15, 30])) {
    throw new \Exception('Invalid video_duration: ' . $videoDuration . '. Only 15 or 30 seconds allowed.');
}

if ($slotsCount < 1) {
    throw new \Exception('Invalid slots_count: ' . $slotsCount . '. Minimum 1 slot required.');
}
```

### 3. Direct Access Only (EnquiryItemService.php)
```php
// CHANGED: Removed indexed array access
// OLD: $videoDuration = $data['video_duration'][$index] ?? $data['video_duration'] ?? 15;
// NEW: $videoDuration = $data['video_duration'] ?? 15;
```

## Why Values Were Becoming 1

Hypothesis: If the indexed array access was trying to access a non-existent index and causing array parsing issues, it could result in `1` being returned (possibly from:
- `intval(null)` = 0
- Boolean conversion = 1
- Or other PHP quirks

**Solution: Use direct access only** - since video_duration and slots_count are single values, not arrays per hoarding.

## Logging to Monitor

After these changes, when submitting DOOH enquiry with valid values (30, 99):

✅ Browser console: `[DEBUG] DOOH Validation - videoDuration: 30 (must be 15 or 30), slotsCount: 99 (must be >= 1)`
✅ Laravel log: `[ENQUIRY SERVICE] Validation result for DOOH fields: "video_duration" => 30, "slots_count" => 99`
✅ Laravel log: `[DOOH ENQUIRY] ✅ VALIDATED - video_duration: 30, slots_count: 99`
✅ Database: `"video_duration": 30, "slots_per_day": 99`

If invalid (16, ...):
❌ Browser console: `Video Duration must be 15 or 30 seconds.` + Block form submission

## Key Changes Summary

| Issue | Before | After |
|-------|--------|-------|
| **Frontend Validation** | Only checks existence | Checks value range (15/30 for duration, >=1 for slots) |
| **Backend Validation** | Standard Laravel rules | + Service layer validation |
| **Data Access** | Indexed + direct (confusing) | Direct access only (clear) |
| **Logging** | Minimal | Comprehensive at each layer |
| **Error Handling** | Silent fail | Explicit exceptions + logging |

---

**Next Step:** Test with actual data and monitor logs to confirm values are saved correctly.
