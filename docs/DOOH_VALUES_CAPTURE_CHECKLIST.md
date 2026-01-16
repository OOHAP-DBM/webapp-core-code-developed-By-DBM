# DOOH Enquiry Values Capture - Implementation Checklist

## ✅ IMPLEMENTATION STATUS

All code changes have been completed to ensure video_duration and slots_count values are properly captured from user input and saved to the database.

## 1. Frontend Form (enquiry-modal.blade.php)

### Video Duration Field
```html
<select name="video_duration" class="enquiry-input">
    <option value="15">15 Seconds</option>
    <option value="30">30 Seconds</option>
</select>
```
✅ **Status:** Field name is `video_duration`
✅ **Status:** Values are `15` or `30`
✅ **Status:** Properly inside the form

### Slots Count Field
```html
<input type="number" name="slots_count" class="enquiry-input" placeholder="e.g. 120" value="120" min="1">
```
✅ **Status:** Field name is `slots_count`
✅ **Status:** Default value is `120`
✅ **Status:** Min value is `1`
✅ **Status:** User can enter any number >= 1

### DOOH Fields Display
```javascript
function toggleDoohFields() {
    var hboardingType = document.getElementById('hoardingType')?.value;
    var doohFields = document.getElementById('doohFields');
    
    if (hboardingType === 'dooh') {
        if (doohFields) doohFields.style.display = '';  // Show
    } else {
        if (doohFields) doohFields.style.display = 'none';  // Hide
    }
}
```
✅ **Status:** Fields only visible for DOOH hoardings

## 2. Frontend Validation (enquiry-modal.blade.php)

### Validation Logic
```javascript
var hboardingType = document.getElementById('hoardingType')?.value || '';
var videoDuration = document.querySelector('select[name="video_duration"]')?.value;
var slotsCount = document.querySelector('input[name="slots_count"]')?.value;

// DOOH-specific validation
if (hboardingType.toLowerCase() === 'dooh') {
    if (!videoDuration) errorMsg += 'Video Duration is required.\n';
    if (!slotsCount) errorMsg += 'Slots Count is required.\n';
    console.log('[DEBUG] DOOH Validation - videoDuration:', videoDuration, 'slotsCount:', slotsCount);
}
```
✅ **Status:** Proper null/empty checks
✅ **Status:** Only validates for DOOH hoardings
✅ **Status:** Uses `.toLowerCase()` for case-insensitive comparison
✅ **Status:** Includes fallback for undefined hboardingType (`|| ''`)

### Form Submission Logging
```javascript
console.log('[DEBUG] Form submitting with:');
console.log('  hboardingType:', hboardingType);
console.log('  video_duration:', videoDuration);
console.log('  slots_count:', slotsCount);
console.log('  all form data:', new FormData(this));
```
✅ **Status:** Console logs form data before submission
✅ **Status:** Helps verify correct values being sent

### Conditional Field Posting
```javascript
if (hboardingType.toLowerCase() === 'dooh') {
    // Keep video_duration and slots_count fields
    console.log('[DEBUG] ✅ Submitting DOOH hoarding with video_duration:', videoDuration, 'and slots_count:', slotsCount);
} else {
    // Remove DOOH fields from form before submission for OOH hoardings
    var videoDurationField = document.querySelector('select[name="video_duration"]');
    var slotsCountField = document.querySelector('input[name="slots_count"]');
    if (videoDurationField) videoDurationField.removeAttribute('name');
    if (slotsCountField) slotsCountField.removeAttribute('name');
    console.log('[DEBUG] ✅ Submitting OOH hoarding without DOOH fields');
}
```
✅ **Status:** Only sends DOOH fields for DOOH hoardings
✅ **Status:** Removes fields for OOH hoardings to avoid confusion

## 3. Backend Validation (EnquiryService.php)

### Validation Rules
```php
'video_duration' => 'nullable|integer|in:15,30',
'slots_count' => 'nullable|integer|min:1',
```
✅ **Status:** video_duration must be 15 or 30
✅ **Status:** slots_count must be >= 1
✅ **Status:** Both are nullable (won't fail for OOH)
✅ **Status:** Both must be integers

## 4. Business Logic Processing (EnquiryItemService.php)

### Data Capture with Fallback
```php
$videoDuration = $data['video_duration'][$index] ?? $data['video_duration'] ?? 15;
$slotsCount = $data['slots_count'][$index] ?? $data['slots_count'] ?? 120;

// Convert to integers
$videoDuration = (int)$videoDuration;
$slotsCount = (int)$slotsCount;
```
✅ **Status:** Tries indexed access first: `$data['video_duration'][$index]`
✅ **Status:** Falls back to direct access: `$data['video_duration']`
✅ **Status:** Finally uses defaults: `15` and `120`
✅ **Status:** Explicit type conversion to integers

### Logging for Verification
```php
\Log::info('[DOOH ENQUIRY] Actual user values - video_duration: ' . $videoDuration . ', slots_count: ' . $slotsCount . ', data array:', [
    'video_duration_from_index' => $data['video_duration'][$index] ?? null,
    'video_duration_direct' => $data['video_duration'] ?? null,
    'video_duration_final' => $videoDuration,
    'slots_count_from_index' => $data['slots_count'][$index] ?? null,
    'slots_count_direct' => $data['slots_count'] ?? null,
    'slots_count_final' => $slotsCount,
]);
```
✅ **Status:** Logs where value came from (indexed, direct, or default)
✅ **Status:** Logs final converted integer value
✅ **Status:** Helps debug any conversion issues

### Meta Field Building
```php
$meta['dooh_specs'] = [
    'video_duration' => $videoDuration,      // Integer
    'slots_per_day'  => $slotsCount,        // Integer
    'loop_interval'  => $data['slot'] ?? 'Standard',
    'total_days'     => $startDate->diffInDays($endDate),
];
```
✅ **Status:** Saves video_duration to dooh_specs
✅ **Status:** Saves slots_count as slots_per_day to dooh_specs
✅ **Status:** All values are integers
✅ **Status:** Additional metadata for reference

## 5. Database Persistence (EnquiryItemRepository.php)

### Repository Logging
```php
\Log::info('[ENQUIRY ITEM REPO] Saving to database:', [
    'meta_dooh_specs' => $meta['dooh_specs'] ?? null,
    'meta_discount_percent' => $meta['discount_percent'] ?? null,
    'all_meta' => $meta,
]);
```
✅ **Status:** Logs meta before saving to database
✅ **Status:** Shows exact structure being persisted
✅ **Status:** Helps verify data integrity

### Database Insertion
```php
return EnquiryItem::create([
    'enquiry_id' => $enquiry->id,
    'hoarding_id' => $hoarding->id,
    'hoarding_type' => $type,
    'package_id' => $packageId,
    'package_type' => $packageType,
    'preferred_start_date' => $startDate,
    'preferred_end_date' => $endDate,
    'expected_duration' => $durationText,
    'services' => $services,
    'meta' => $meta,  // ✅ Contains dooh_specs with video_duration and slots_per_day
    'status' => EnquiryItem::STATUS_NEW,
]);
```
✅ **Status:** meta field saved to database
✅ **Status:** Meta contains dooh_specs with actual user values
✅ **Status:** Video_duration and slots_per_day are integers

## 6. Database Schema (enquiry_items table)

### Meta Field Definition
```php
$table->json('meta')->nullable();
```
✅ **Status:** Meta column is JSON type
✅ **Status:** Can store nested objects and arrays
✅ **Status:** Supports dooh_specs structure

## Testing Instructions

### Test 1: Basic DOOH Enquiry
1. Open a DOOH hoarding detail page
2. Click "Raise an Enquiry"
3. Fill form with:
   - Full Name: John Doe
   - Email: john@test.com
   - Mobile: 9876543210
   - Start Date: (any future date)
   - Video Duration: **30 Seconds** (select manually)
   - Slots per Day: **250** (change from default 120)
4. Submit form
5. **Check browser console:**
   ```
   [DEBUG] Form submitting with:
     hoardingType: dooh
     video_duration: 30
     slots_count: 250
   [DEBUG] ✅ Submitting DOOH hoarding with video_duration: 30 and slots_count: 250
   ```

6. **Check storage/logs/laravel.log:**
   ```
   ========== ENQUIRY MODAL POST DATA ==========
   [
     "video_duration" => "30"
     "slots_count" => "250"
     "hoarding_type" => "dooh"
   ]
   
   [DOOH ENQUIRY] Actual user values - video_duration: 30, slots_count: 250, data array:
   [
     "video_duration_from_index" => null
     "video_duration_direct" => "30"
     "video_duration_final" => 30
     "slots_count_from_index" => null
     "slots_count_direct" => "250"
     "slots_count_final" => 250
   ]
   
   [ENQUIRY ITEM REPO] Saving to database:
   [
     "meta_dooh_specs" => [
       "video_duration" => 30
       "slots_per_day" => 250
       "loop_interval" => "Standard"
       "total_days" => (calculated)
     ]
   ]
   ```

7. **Check database:**
   ```sql
   SELECT id, hoarding_id, hoarding_type, meta 
   FROM enquiry_items 
   WHERE hoarding_type = 'dooh' 
   ORDER BY created_at DESC 
   LIMIT 1;
   ```
   
   **Expected Result:**
   ```json
   {
     "id": 123,
     "hoarding_id": 456,
     "hoarding_type": "dooh",
     "meta": {
       "dooh_specs": {
         "video_duration": 30,
         "slots_per_day": 250,
         "loop_interval": "Standard",
         "total_days": 30
       },
       "customer_name": "John Doe",
       "customer_email": "john@test.com",
       ...
     }
   }
   ```

### Test 2: OOH Enquiry (No DOOH Fields)
1. Open an OOH hoarding detail page
2. Click "Raise an Enquiry"
3. Fill form without touching video_duration or slots_count fields
4. Submit form
5. **Check browser console:**
   ```
   [DEBUG] ✅ Submitting OOH hoarding without DOOH fields
   ```
6. **Check database:**
   ```json
   {
     "meta": {
       (NO dooh_specs key)
     }
   }
   ```

## Troubleshooting

### If values show as NULL in database:
1. Check browser console for `[DEBUG]` logs
2. Check `storage/logs/laravel.log` for `[DOOH ENQUIRY]` logs
3. Verify form submission included the fields
4. Check if field names match exactly (case-sensitive)

### If values show as 15 and 120 (defaults):
1. Check console log shows `video_duration_direct` and `slots_count_direct` are null
2. Check if form is actually sending the values
3. Verify field names are correct: `video_duration` and `slots_count`
4. Check if conditional POST logic is removing the fields by mistake

### If validation fails unnecessarily:
1. Check if hoarding_type is set correctly
2. Verify lowercase comparison: `.toLowerCase() === 'dooh'`
3. Check that hboardingType has fallback: `|| ''`

## Related Files
- `resources/views/hoardings/partials/enquiry-modal.blade.php` (Frontend)
- `resources/views/hoardings/scripts/enquiry-modal.blade.php` (Frontend Logic)
- `Modules/Enquiries/Services/EnquiryService.php` (Backend Validation)
- `Modules/Enquiries/Services/EnquiryItemService.php` (Business Logic)
- `Modules/Enquiries/Repositories/EnquiryItemRepository.php` (Database Persistence)
- `database/migrations/2026_01_05_090719_create_enquiry_items_table.php` (Database Schema)

## Summary

✅ **Video Duration:**
- User selects 15 or 30 seconds in form
- Validated as integer in range [15, 30]
- Converted to integer (int)
- Saved to `meta.dooh_specs.video_duration`
- Stored as integer in database JSON

✅ **Slots Count:**
- User enters number (default 120) in form
- Validated as integer >= 1
- Converted to integer (int)
- Saved to `meta.dooh_specs.slots_per_day`
- Stored as integer in database JSON

✅ **Verification:**
- Browser console logs show user-entered values
- laravel.log shows conversion and where value came from
- Database JSON contains actual user values (not defaults)
- Both values are proper integers (not strings)

---
**Last Updated:** January 15, 2026
**Status:** ✅ COMPLETE AND READY FOR TESTING
