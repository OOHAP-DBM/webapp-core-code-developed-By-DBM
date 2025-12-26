<!-- Step 2: Location & Attributes (Figma-accurate) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-primary">Hoarding Location</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Hoarding Address*</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $draft->address ?? '') }}" placeholder="Enter Hoarding Address eg. Vikhroli Khind" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Pincode*</label>
                <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $draft->pincode ?? '') }}" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" value="{{ old('city', $draft->city ?? '') }}">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3 mb-3">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control" value="{{ old('state', $draft->state ?? '') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Nearby Landmark</label>
                <input type="text" name="landmark[]" class="form-control mb-2" value="{{ old('landmark.0', $draft->landmark[0] ?? '') }}" placeholder="Enter Landmark">
                <!-- Add JS for dynamic landmark fields if needed -->
                <button type="button" class="btn btn-outline-secondary btn-sm">+ Add another landmark</button>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <label class="form-label">Google Map Address</label>
                <input type="text" name="google_map_address" class="form-control" value="{{ old('google_map_address', $draft->google_map_address ?? '') }}">
                <a href="#" class="small text-success">Locate on Map</a>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Hoardings View for Visitors</label>
                <div class="d-flex gap-3">
                    <label><input type="radio" name="view_direction" value="one_way" {{ old('view_direction', $draft->view_direction ?? '') == 'one_way' ? 'checked' : '' }}> One way View</label>
                    <label><input type="radio" name="view_direction" value="two_way" {{ old('view_direction', $draft->view_direction ?? '') == 'two_way' ? 'checked' : '' }}> Two way View</label>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 mb-3">
                <label class="form-label">Hoardings Attributes</label>
                <div class="d-flex flex-wrap gap-2">
                    @php $attrs = ['Metro Ride','From Flyover','From the road','Roof top','Wall hanging','Road top']; @endphp
                    @foreach($attrs as $attr)
                        <label class="btn btn-outline-secondary btn-sm {{ in_array($attr, old('attributes', $draft->attributes ?? [])) ? 'active' : '' }}">
                            <input type="checkbox" name="attributes[]" value="{{ $attr }}" autocomplete="off" {{ in_array($attr, old('attributes', $draft->attributes ?? [])) ? 'checked' : '' }}> {{ $attr }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 mb-3">
                <label class="form-label">Located At</label>
                <div class="d-flex flex-wrap gap-2">
                    @php $located = ['Highway hoarding','At Square','Shopping Mall','Airport','Park','Main Road','Intercity Highway','Main Road','Pause Area']; @endphp
                    @foreach($located as $loc)
                        <label class="btn btn-outline-secondary btn-sm {{ in_array($loc, old('located_at', $draft->located_at ?? [])) ? 'active' : '' }}">
                            <input type="checkbox" name="located_at[]" value="{{ $loc }}" autocomplete="off" {{ in_array($loc, old('located_at', $draft->located_at ?? [])) ? 'checked' : '' }}> {{ $loc }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 mb-3">
                <label class="form-label">Hoardings Visibility</label>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label><input type="radio" name="visibility_type" value="one_side" {{ old('visibility_type', $draft->visibility_type ?? '') == 'one_side' ? 'checked' : '' }}> One Way Visibility</label>
                    </div>
                    <div class="col-md-4">
                        <label><input type="radio" name="visibility_type" value="both_side" {{ old('visibility_type', $draft->visibility_type ?? '') == 'both_side' ? 'checked' : '' }}> Both Side Visibility</label>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-md-4">
                        <label class="form-label small">To</label>
                        <input type="text" name="visibility_to[]" class="form-control" value="{{ old('visibility_to.0', $draft->visibility_to[0] ?? '') }}" placeholder="Eg Fun mall">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Going From</label>
                        <input type="text" name="visibility_from[]" class="form-control" value="{{ old('visibility_from.0', $draft->visibility_from[0] ?? '') }}" placeholder="Eg Santacruz mall">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">To</label>
                        <input type="text" name="visibility_to[]" class="form-control" value="{{ old('visibility_to.1', $draft->visibility_to[1] ?? '') }}" placeholder="Eg Fun mall">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
