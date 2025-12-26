<!-- Step 3: Pricing & Availability (Figma-accurate) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-primary">Pricing</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Display Price per 30 seconds*</label>
                <input type="text" name="display_price" class="form-control" value="{{ old('display_price', $draft->display_price ?? '') }}" placeholder="Enter Price" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Video Length</label>
                <select name="video_length" class="form-control">
                    <option value="">Select Video Length</option>
                    <option value="10" {{ old('video_length', $draft->video_length ?? '') == '10' ? 'selected' : '' }}>10 sec</option>
                    <option value="20" {{ old('video_length', $draft->video_length ?? '') == '20' ? 'selected' : '' }}>20 sec</option>
                    <option value="30" {{ old('video_length', $draft->video_length ?? '') == '30' ? 'selected' : '' }}>30 sec</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Available Slot</label>
            <div class="row g-2">
                @php $slots = ['Early Morning 4:00AM - 8:00AM','Morning 8:00AM - 12:00PM','Afternoon 12:00PM - 3:00PM','Evening 4:00PM - 8:00PM','Night 8:00PM - 12:00AM','Midnight 12:00AM - 4:00AM']; @endphp
                @foreach($slots as $i => $slot)
                    <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="available_slots[]" value="{{ $slot }}" id="slot{{ $i }}" {{ in_array($slot, old('available_slots', $draft->available_slots ?? [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="slot{{ $i }}">{{ $slot }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm mt-2">+ Add slot</button>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Rental Offering</label>
                <div class="d-flex gap-3 align-items-center">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rental_type" value="monthly" {{ old('rental_type', $draft->rental_type ?? '') == 'monthly' ? 'checked' : '' }}>
                        <label class="form-check-label">Monthly</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="rental_type" value="weekly" {{ old('rental_type', $draft->rental_type ?? '') == 'weekly' ? 'checked' : '' }}>
                        <label class="form-check-label">Weekly</label>
                    </div>
                </div>
                <input type="text" name="base_monthly_price" class="form-control mt-2" value="{{ old('base_monthly_price', $draft->base_monthly_price ?? '') }}" placeholder="Base Monthly Price">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="discount_on_base" value="1" {{ old('discount_on_base', $draft->discount_on_base ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label">Offering Discount on Base Monthly Price?</label>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Long Term Offers</label>
                <div class="d-flex gap-3 align-items-center mb-2">
                    <label class="me-2">Offering long term booking discount?</label>
                    <label class="me-2"><input type="radio" name="long_term_discount" value="yes" {{ old('long_term_discount', $draft->long_term_discount ?? '') == 'yes' ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="long_term_discount" value="no" {{ old('long_term_discount', $draft->long_term_discount ?? '') == 'no' ? 'checked' : '' }}> No</label>
                </div>
                <div class="mb-2">
                    <label class="form-label small">On which price will you offer long term discount?</label>
                    <div class="d-flex gap-2">
                        <label><input type="radio" name="long_term_discount_type" value="base" {{ old('long_term_discount_type', $draft->long_term_discount_type ?? '') == 'base' ? 'checked' : '' }}> Base Monthly Price</label>
                        <label><input type="radio" name="long_term_discount_type" value="offered" {{ old('long_term_discount_type', $draft->long_term_discount_type ?? '') == 'offered' ? 'checked' : '' }}> Offered Monthly Price</label>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm">+ Add an offer</button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Graphics Included?</label>
                <div class="d-flex gap-3">
                    <label><input type="radio" name="graphics_included" value="yes" {{ old('graphics_included', $draft->graphics_included ?? '') == 'yes' ? 'checked' : '' }}> Yes</label>
                    <label><input type="radio" name="graphics_included" value="no" {{ old('graphics_included', $draft->graphics_included ?? '') == 'no' ? 'checked' : '' }}> No</label>
                </div>
                <input type="text" name="graphics_price" class="form-control mt-2" value="{{ old('graphics_price', $draft->graphics_price ?? '') }}" placeholder="Enter Graphics Price">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Survey Charges</label>
                <input type="text" name="survey_charges" class="form-control" value="{{ old('survey_charges', $draft->survey_charges ?? '') }}" placeholder="Per survey charge">
            </div>
        </div>
    </div>
</div>
