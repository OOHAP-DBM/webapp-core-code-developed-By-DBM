
<!-- Step 1: DOOH Details (Figma-accurate, improved spacing, grouping, and UI) -->
<div class="wizard-section mb-4">
    <div class="wizard-card mb-4">
        <div class="wizard-card-title">Hoarding Details</div>
        <div class="row g-4 align-items-end">
            <div class="col-md-6">
                <label class="wizard-label">Hoarding Type <span class="text-danger">*</span></label>
                <input type="text" class="form-control wizard-input bg-light" value="DOOH (Digital Out-of-Home)" readonly>
            </div>
            <div class="col-md-6">
                <label class="wizard-label">Category <span class="text-danger">*</span></label>
                <select name="category" class="form-control wizard-input" required>
                    <option value="">Select Category</option>
                    <option value="Unipole" {{ old('category', $draft->category ?? '') == 'Unipole' ? 'selected' : '' }}>Unipole</option>
                    <option value="Billboard" {{ old('category', $draft->category ?? '') == 'Billboard' ? 'selected' : '' }}>Billboard</option>
                    <option value="Digital Kiosk" {{ old('category', $draft->category ?? '') == 'Digital Kiosk' ? 'selected' : '' }}>Digital Kiosk</option>
                </select>
            </div>
        </div>
        <div class="row g-4 mt-2 align-items-end">
            <div class="col-md-6">
                <label class="wizard-label">Screen Type <span class="text-danger">*</span></label>
                <select name="screen_type" class="form-control wizard-input" required>
                    <option value="">Choose Screen Type</option>
                    <option value="LED" {{ old('screen_type', $draft->screen_type ?? '') == 'LED' ? 'selected' : '' }}>LED</option>
                    <option value="LCD" {{ old('screen_type', $draft->screen_type ?? '') == 'LCD' ? 'selected' : '' }}>LCD</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="wizard-label">Screen Size <span class="text-danger">*</span></label>
                <div class="row g-2">
                    <div class="col-4">
                        <label class="wizard-label small">Measurement in</label>
                        <select name="size_unit" class="form-control wizard-input">
                            <option value="sqft" {{ old('size_unit', $draft->size_unit ?? '') == 'sqft' ? 'selected' : '' }}>Sqft</option>
                            <option value="sqm" {{ old('size_unit', $draft->size_unit ?? '') == 'sqm' ? 'selected' : '' }}>Sqm</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="wizard-label small">Screen width <span class="text-danger">*</span></label>
                        <input type="text" name="width" class="form-control wizard-input" value="{{ old('width', $draft->width ?? '') }}" placeholder="Enter width" required>
                    </div>
                    <div class="col-4">
                        <label class="wizard-label small">Screen height <span class="text-danger">*</span></label>
                        <input type="text" name="height" class="form-control wizard-input" value="{{ old('height', $draft->height ?? '') }}" placeholder="Enter height" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <label class="wizard-label">Valid till</label>
                <input type="date" name="valid_till" class="form-control wizard-input" value="{{ old('valid_till', $draft->valid_till ?? '') }}">
            </div>
        </div>
    </div>

    <div class="wizard-card mb-4">
        <div class="wizard-card-title">Upload Hoarding Media <span class="text-danger">*</span></div>
        <div class="wizard-upload-box">
            <input type="file" name="media" class="form-control wizard-upload-input">
            <div class="wizard-upload-helper">Upload up to 5 files. Includes image, video.</div>
        </div>
    </div>

    <div class="wizard-card mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-md-4">
                <label class="wizard-label">Nagar Nigam Approved?</label>
                <div class="wizard-radio-group">
                    <input type="radio" name="nagar_nigam_approved" id="nagar_yes" value="yes" {{ old('nagar_nigam_approved', $draft->nagar_nigam_approved ?? '') == 'yes' ? 'checked' : '' }}>
                    <label for="nagar_yes" class="wizard-radio-pill">Yes</label>
                    <input type="radio" name="nagar_nigam_approved" id="nagar_no" value="no" {{ old('nagar_nigam_approved', $draft->nagar_nigam_approved ?? '') == 'no' ? 'checked' : '' }}>
                    <label for="nagar_no" class="wizard-radio-pill">No</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="wizard-label">Block any certain dates?</label>
                <div class="wizard-radio-group">
                    <input type="radio" name="block_dates" id="block_yes" value="yes" {{ old('block_dates', $draft->block_dates ?? '') == 'yes' ? 'checked' : '' }}>
                    <label for="block_yes" class="wizard-radio-pill">Yes</label>
                    <input type="radio" name="block_dates" id="block_no" value="no" {{ old('block_dates', $draft->block_dates ?? '') == 'no' ? 'checked' : '' }}>
                    <label for="block_no" class="wizard-radio-pill">No</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="wizard-label">Grace period after booking?</label>
                <div class="wizard-radio-group">
                    <input type="radio" name="grace_period" id="grace_yes" value="yes" {{ old('grace_period', $draft->grace_period ?? '') == 'yes' ? 'checked' : '' }}>
                    <label for="grace_yes" class="wizard-radio-pill">Yes</label>
                    <input type="radio" name="grace_period" id="grace_no" value="no" {{ old('grace_period', $draft->grace_period ?? '') == 'no' ? 'checked' : '' }}>
                    <label for="grace_no" class="wizard-radio-pill">No</label>
                </div>
            </div>
        </div>
    </div>

    <div class="wizard-card mb-4">
        <div class="row g-4">
            <div class="col-md-6">
                <label class="wizard-label">Expected Footfall</label>
                <input type="text" name="expected_footfall" class="form-control wizard-input" value="{{ old('expected_footfall', $draft->expected_footfall ?? '') }}" placeholder="Enter number e.g. 50,000">
            </div>
            <div class="col-md-6">
                <label class="wizard-label">Expected Eyeball</label>
                <input type="text" name="expected_eyeball" class="form-control wizard-input" value="{{ old('expected_eyeball', $draft->expected_eyeball ?? '') }}" placeholder="Enter number e.g. 50,000">
            </div>
        </div>
    </div>

    <div class="wizard-card mb-4">
        <label class="wizard-label">Select Audience Type</label>
        <div class="wizard-checkbox-group">
            @php $audTypes = ['Political activities','Students','Luxury consumers','Enthusiasts/treks','Average Class','Public','Tourism','Foodies']; @endphp
            @foreach($audTypes as $type)
                <input type="checkbox" name="audience_type[]" id="aud_{{ $loop->index }}" value="{{ $type }}" {{ in_array($type, old('audience_type', $draft->audience_type ?? [])) ? 'checked' : '' }}>
                <label for="aud_{{ $loop->index }}" class="wizard-checkbox-pill">{{ $type }}</label>
            @endforeach
        </div>
    </div>

    <div class="wizard-card mb-4">
        <label class="wizard-label">Recently Booked by</label>
        <input type="file" name="recently_booked_by" class="form-control wizard-input">
        <div class="wizard-upload-helper">Upload up to 10 brand logos.</div>
    </div>
</div>

<style>
.wizard-section { padding: 0 0 24px 0; }
.wizard-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 32px 32px 24px 32px; margin-bottom: 24px; }
.wizard-card-title { font-size: 1.1rem; font-weight: 600; color: #009A5C; margin-bottom: 18px; }
.wizard-label { font-weight: 500; color: #222; font-size: 1rem; margin-bottom: 6px; display: block; }
.wizard-input { border-radius: 8px; font-size: 1rem; }
.wizard-upload-box { border: 2px dashed #C8C8C8; border-radius: 10px; padding: 32px 16px; text-align: center; background: #F8F8F8; }
.wizard-upload-helper { font-size: 0.92rem; color: #888; margin-top: 8px; }
.wizard-radio-group { display: flex; gap: 10px; align-items: center; margin-top: 4px; }
.wizard-radio-pill { display: inline-block; padding: 6px 18px; border-radius: 20px; border: 1.5px solid #C8C8C8; background: #fff; color: #222; font-weight: 500; cursor: pointer; margin-right: 6px; transition: all 0.15s; }
.wizard-radio-group input[type="radio"]:checked + .wizard-radio-pill { background: #009A5C; color: #fff; border-color: #009A5C; }
.wizard-radio-group input[type="radio"] { display: none; }
.wizard-checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 4px; }
.wizard-checkbox-pill { display: inline-block; padding: 6px 18px; border-radius: 20px; border: 1.5px solid #C8C8C8; background: #fff; color: #222; font-weight: 500; cursor: pointer; margin-right: 6px; transition: all 0.15s; }
.wizard-checkbox-group input[type="checkbox"]:checked + .wizard-checkbox-pill { background: #009A5C; color: #fff; border-color: #009A5C; }
.wizard-checkbox-group input[type="checkbox"] { display: none; }
.text-danger { color: #E74C3C !important; }
.form-control:focus { border-color: #009A5C; box-shadow: 0 0 0 0.15rem rgba(0,154,92,0.08); }
.is-invalid, .form-control.is-invalid { border-color: #E74C3C; }
.invalid-feedback { color: #E74C3C; font-size: 0.97rem; margin-top: 4px; }
.alert-danger { background: #FFF3F3; border: 1.5px solid #E74C3C; color: #E74C3C; border-radius: 8px; padding: 14px 18px; font-size: 1.05rem; margin-bottom: 24px; }
</style>
