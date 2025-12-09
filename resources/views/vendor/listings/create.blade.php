@extends('layouts.vendor')

@section('page-title', 'Add ' . strtoupper($type ?? 'OOH') . ' Listing')

@section('content')
<div class="mb-4">
    <a href="{{ route('vendor.listings.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-2"></i>Back to Listings
    </a>
</div>

<form action="{{ route('vendor.listings.store') }}" method="POST" enctype="multipart/form-data" id="listingForm">
    @csrf
    <input type="hidden" name="media_type" value="{{ $type ?? 'ooh' }}">
    
    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="vendor-card mb-4">
                <div class="vendor-card-header">
                    <h5 class="vendor-card-title">Basic Information</h5>
                </div>
                <div class="vendor-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Listing Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   placeholder="e.g., Premium Billboard - MG Road"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Describe the location, visibility, traffic, etc.">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type" class="form-label">Hoarding Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="billboard">Billboard</option>
                                <option value="hoarding">Hoarding</option>
                                <option value="unipole">Unipole</option>
                                <option value="gantry">Gantry</option>
                                <option value="bus_shelter">Bus Shelter</option>
                                <option value="kiosk">Kiosk</option>
                                <option value="pole">Pole</option>
                                <option value="wall">Wall Painting</option>
                                @if(($type ?? 'ooh') === 'dooh')
                                    <option value="led_screen">LED Screen</option>
                                    <option value="digital_billboard">Digital Billboard</option>
                                @endif
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="orientation" class="form-label">Orientation</label>
                            <select class="form-select" id="orientation" name="orientation">
                                <option value="horizontal">Horizontal</option>
                                <option value="vertical">Vertical</option>
                                <option value="square">Square</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="vendor-card mb-4">
                <div class="vendor-card-header">
                    <h5 class="vendor-card-title">Location Details</h5>
                </div>
                <div class="vendor-card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="2" 
                                      required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state') }}" required>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" id="pincode" name="pincode" value="{{ old('pincode') }}" pattern="[0-9]{6}" required>
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="latitude" class="form-label">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="e.g., 28.7041">
                            <small class="text-muted">Optional: For map display</small>
                        </div>

                        <div class="col-md-6">
                            <label for="longitude" class="form-label">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="e.g., 77.1025">
                            <small class="text-muted">Optional: For map display</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications -->
            <div class="vendor-card mb-4">
                <div class="vendor-card-header">
                    <h5 class="vendor-card-title">Specifications</h5>
                </div>
                <div class="vendor-card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="width" class="form-label">Width (feet) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('width') is-invalid @enderror" id="width" name="width" value="{{ old('width') }}" step="0.1" required>
                            @error('width')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="height" class="form-label">Height (feet) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('height') is-invalid @enderror" id="height" name="height" value="{{ old('height') }}" step="0.1" required>
                            @error('height')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Illumination</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="illumination" name="illumination" value="1" {{ old('illumination') ? 'checked' : '' }}>
                                <label class="form-check-label" for="illumination">
                                    Illuminated (Lit at night)
                                </label>
                            </div>
                        </div>

                        @if(($type ?? 'ooh') === 'dooh')
                            <div class="col-md-6">
                                <label for="resolution" class="form-label">Resolution</label>
                                <input type="text" class="form-control" id="resolution" name="resolution" value="{{ old('resolution') }}" placeholder="e.g., 1920x1080">
                            </div>

                            <div class="col-md-6">
                                <label for="slot_duration" class="form-label">Slot Duration (seconds)</label>
                                <input type="number" class="form-control" id="slot_duration" name="slot_duration" value="{{ old('slot_duration', 10) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="slots_per_hour" class="form-label">Slots Per Hour</label>
                                <input type="number" class="form-control" id="slots_per_hour" name="slots_per_hour" value="{{ old('slots_per_hour', 6) }}">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Images -->
            <div class="vendor-card mb-4">
                <div class="vendor-card-header">
                    <h5 class="vendor-card-title">Images</h5>
                </div>
                <div class="vendor-card-body">
                    <div class="mb-3">
                        <label for="image" class="form-label">Primary Image <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*" required>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="imagePreview" class="mt-2"></div>
                    </div>

                    <div>
                        <label for="gallery" class="form-label">Gallery Images (Optional)</label>
                        <input type="file" class="form-control" id="gallery" name="gallery[]" accept="image/*" multiple>
                        <small class="text-muted">You can select multiple images</small>
                        <div id="galleryPreview" class="row g-2 mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Pricing -->
            <div class="vendor-card mb-4">
                <div class="vendor-card-header">
                    <h5 class="vendor-card-title">Pricing</h5>
                </div>
                <div class="vendor-card-body">
                    <div class="mb-3">
                        <label for="price_per_month" class="form-label">Price Per Month (₹) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('price_per_month') is-invalid @enderror" id="price_per_month" name="price_per_month" value="{{ old('price_per_month') }}" required>
                        @error('price_per_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(($type ?? 'ooh') === 'dooh')
                        <div class="mb-3">
                            <label for="price_per_slot" class="form-label">Price Per Slot (₹)</label>
                            <input type="number" class="form-control" id="price_per_slot" name="price_per_slot" value="{{ old('price_per_slot') }}">
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="printing_cost" class="form-label">Printing Cost (₹)</label>
                        <input type="number" class="form-control" id="printing_cost" name="printing_cost" value="{{ old('printing_cost') }}">
                    </div>

                    <div class="mb-3">
                        <label for="installation_cost" class="form-label">Installation Cost (₹)</label>
                        <input type="number" class="form-control" id="installation_cost" name="installation_cost" value="{{ old('installation_cost') }}">
                    </div>

                    <div class="mb-3">
                        <label for="maintenance_cost" class="form-label">Monthly Maintenance (₹)</label>
                        <input type="number" class="form-control" id="maintenance_cost" name="maintenance_cost" value="{{ old('maintenance_cost') }}">
                    </div>
                </div>
            </div>

            <!-- Availability -->
            <div class="vendor-card mb-4">
                <div class="vendor-card-header">
                    <h5 class="vendor-card-title">Availability</h5>
                </div>
                <div class="vendor-card-body">
                    <div class="mb-3">
                        <label for="available_from" class="form-label">Available From</label>
                        <input type="date" class="form-control" id="available_from" name="available_from" value="{{ old('available_from', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-3">
                        <label for="minimum_booking_days" class="form-label">Minimum Booking (days)</label>
                        <input type="number" class="form-control" id="minimum_booking_days" name="minimum_booking_days" value="{{ old('minimum_booking_days', 30) }}">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            Mark as Featured
                        </label>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="vendor-card mb-4">
                <div class="vendor-card-header">
                    <h5 class="vendor-card-title">Additional Details</h5>
                </div>
                <div class="vendor-card-body">
                    <div class="mb-3">
                        <label for="traffic_type" class="form-label">Traffic Type</label>
                        <select class="form-select" id="traffic_type" name="traffic_type">
                            <option value="high">High Traffic</option>
                            <option value="medium">Medium Traffic</option>
                            <option value="low">Low Traffic</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="audience_type" class="form-label">Target Audience</label>
                        <input type="text" class="form-control" id="audience_type" name="audience_type" value="{{ old('audience_type') }}" placeholder="e.g., Corporate, Students">
                    </div>

                    <div class="mb-3">
                        <label for="landmark" class="form-label">Nearby Landmark</label>
                        <input type="text" class="form-control" id="landmark" name="landmark" value="{{ old('landmark') }}" placeholder="e.g., Near Mall">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-vendor-primary btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Create Listing
                </button>
                <a href="{{ route('vendor.listings.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
// Image Preview
document.getElementById('image')?.addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">`;
        };
        reader.readAsDataURL(file);
    }
});

// Gallery Preview
document.getElementById('gallery')?.addEventListener('change', function(e) {
    const preview = document.getElementById('galleryPreview');
    preview.innerHTML = '';
    
    Array.from(e.target.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const col = document.createElement('div');
            col.className = 'col-4';
            col.innerHTML = `<img src="${e.target.result}" class="img-thumbnail">`;
            preview.appendChild(col);
        };
        reader.readAsDataURL(file);
    });
});

// Calculate total area
const widthInput = document.getElementById('width');
const heightInput = document.getElementById('height');

function updateArea() {
    const width = parseFloat(widthInput.value) || 0;
    const height = parseFloat(heightInput.value) || 0;
    const area = (width * height).toFixed(2);
    console.log('Total area:', area, 'sq ft');
}

widthInput?.addEventListener('input', updateArea);
heightInput?.addEventListener('input', updateArea);
</script>
@endpush
