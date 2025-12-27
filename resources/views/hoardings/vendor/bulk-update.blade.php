@extends('layouts.vendor')

@section('page-title', 'Bulk Update Listings')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Bulk Update Listings</h2>
    <p class="text-muted mb-0">Update multiple listings at once</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Selection Method -->
        <div class="vendor-card mb-4">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Step 1: Select Listings</h5>
            </div>
            <div class="vendor-card-body">
                <div class="btn-group w-100 mb-3" role="group">
                    <input type="radio" class="btn-check" name="selectionMethod" id="methodManual" checked>
                    <label class="btn btn-outline-primary" for="methodManual">Manual Selection</label>
                    
                    <input type="radio" class="btn-check" name="selectionMethod" id="methodFilter">
                    <label class="btn btn-outline-primary" for="methodFilter">By Filter</label>
                    
                    <input type="radio" class="btn-check" name="selectionMethod" id="methodAll">
                    <label class="btn btn-outline-primary" for="methodAll">All Listings</label>
                </div>

                <!-- Manual Selection -->
                <div id="manualSelection" class="selection-panel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" class="form-check-input" id="selectAllListings"></th>
                                    <th>Listing</th>
                                    <th>Location</th>
                                    <th>Current Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listings ?? [] as $listing)
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input listing-select" value="{{ $listing->id }}"></td>
                                        <td>{{ $listing->title }}</td>
                                        <td>{{ $listing->city }}</td>
                                        <td>₹{{ number_format($listing->price_per_month, 0) }}</td>
                                        <td><span class="badge bg-success">{{ ucfirst($listing->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No listings available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Filter Selection -->
                <div id="filterSelection" class="selection-panel" style="display: none;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <select class="form-select" id="filterCity">
                                <option value="">All Cities</option>
                                @foreach($cities ?? [] as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select class="form-select" id="filterType">
                                <option value="">All Types</option>
                                <option value="billboard">Billboard</option>
                                <option value="hoarding">Hoarding</option>
                                <option value="unipole">Unipole</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="approved">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- All Listings -->
                <div id="allSelection" class="selection-panel" style="display: none;">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This will update ALL your listings. Use with caution!
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Fields -->
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Step 2: Choose Update Type</h5>
            </div>
            <div class="vendor-card-body">
                <form id="bulkUpdateForm" action="{{ route('vendor.listings.bulk-update-submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="selected_ids" id="selectedIds">
                    <input type="hidden" name="selection_method" id="selectionMethod" value="manual">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Fields to Update</label>
                        
                        <!-- Price Update -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="updatePrice" name="update_fields[]" value="price">
                            <label class="form-check-label" for="updatePrice">
                                <strong>Update Price</strong>
                            </label>
                        </div>
                        <div id="priceFields" class="ms-4 mb-3" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Update Method</label>
                                    <select class="form-select" name="price_method">
                                        <option value="fixed">Set Fixed Price</option>
                                        <option value="increase_percent">Increase by %</option>
                                        <option value="decrease_percent">Decrease by %</option>
                                        <option value="increase_amount">Increase by Amount</option>
                                        <option value="decrease_amount">Decrease by Amount</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Value</label>
                                    <input type="number" class="form-control" name="price_value" placeholder="Enter value">
                                </div>
                            </div>
                        </div>

                        <!-- Status Update -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="updateStatus" name="update_fields[]" value="status">
                            <label class="form-check-label" for="updateStatus">
                                <strong>Update Status</strong>
                            </label>
                        </div>
                        <div id="statusFields" class="ms-4 mb-3" style="display: none;">
                            <select class="form-select" name="status_value">
                                <option value="approved">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Illumination Update -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="updateIllumination" name="update_fields[]" value="illumination">
                            <label class="form-check-label" for="updateIllumination">
                                <strong>Update Illumination</strong>
                            </label>
                        </div>
                        <div id="illuminationFields" class="ms-4 mb-3" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="illumination_value" id="illumYes" value="1">
                                <label class="form-check-label" for="illumYes">
                                    Illuminated
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="illumination_value" id="illumNo" value="0">
                                <label class="form-check-label" for="illumNo">
                                    Non-Illuminated
                                </label>
                            </div>
                        </div>

                        <!-- Featured Update -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="updateFeatured" name="update_fields[]" value="featured">
                            <label class="form-check-label" for="updateFeatured">
                                <strong>Update Featured Status</strong>
                            </label>
                        </div>
                        <div id="featuredFields" class="ms-4 mb-3" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="featured_value" id="featYes" value="1">
                                <label class="form-check-label" for="featYes">
                                    Mark as Featured
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="featured_value" id="featNo" value="0">
                                <label class="form-check-label" for="featNo">
                                    Remove from Featured
                                </label>
                            </div>
                        </div>

                        <!-- Availability -->
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="updateAvailability" name="update_fields[]" value="availability">
                            <label class="form-check-label" for="updateAvailability">
                                <strong>Update Availability</strong>
                            </label>
                        </div>
                        <div id="availabilityFields" class="ms-4 mb-3" style="display: none;">
                            <label class="form-label">Available From</label>
                            <input type="date" class="form-control" name="available_from">
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-vendor-primary btn-lg" id="submitBtn" disabled>
                            <i class="bi bi-arrow-repeat me-2"></i>Apply Bulk Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Panel -->
    <div class="col-lg-4">
        <div class="vendor-card sticky-top" style="top: 90px;">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Update Summary</h5>
            </div>
            <div class="vendor-card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Selected Listings:</span>
                        <strong id="selectedCount">0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Fields to Update:</span>
                        <strong id="fieldsCount">0</strong>
                    </div>
                </div>

                <hr>

                <div id="previewContent">
                    <p class="text-muted text-center">Select listings and fields to see preview</p>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Changes can be reverted from the listings page within 24 hours.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Selection Method Toggle
document.querySelectorAll('input[name="selectionMethod"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.selection-panel').forEach(panel => panel.style.display = 'none');
        
        if (this.id === 'methodManual') {
            document.getElementById('manualSelection').style.display = 'block';
            document.getElementById('selectionMethod').value = 'manual';
        } else if (this.id === 'methodFilter') {
            document.getElementById('filterSelection').style.display = 'block';
            document.getElementById('selectionMethod').value = 'filter';
        } else {
            document.getElementById('allSelection').style.display = 'block';
            document.getElementById('selectionMethod').value = 'all';
        }
        updateSummary();
    });
});

// Field Toggle
['Price', 'Status', 'Illumination', 'Featured', 'Availability'].forEach(field => {
    const checkbox = document.getElementById(`update${field}`);
    const fields = document.getElementById(`${field.toLowerCase()}Fields`);
    
    checkbox?.addEventListener('change', function() {
        fields.style.display = this.checked ? 'block' : 'none';
        updateSummary();
    });
});

// Select All
document.getElementById('selectAllListings')?.addEventListener('change', function() {
    document.querySelectorAll('.listing-select').forEach(cb => cb.checked = this.checked);
    updateSummary();
});

// Individual Selection
document.querySelectorAll('.listing-select').forEach(cb => {
    cb.addEventListener('change', updateSummary);
});

function updateSummary() {
    const selected = document.querySelectorAll('.listing-select:checked').length;
    const fields = document.querySelectorAll('input[name="update_fields[]"]:checked').length;
    
    document.getElementById('selectedCount').textContent = selected;
    document.getElementById('fieldsCount').textContent = fields;
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = selected === 0 || fields === 0;
    
    // Update hidden input
    const selectedIds = Array.from(document.querySelectorAll('.listing-select:checked')).map(cb => cb.value);
    document.getElementById('selectedIds').value = selectedIds.join(',');
}

// Form submission
document.getElementById('bulkUpdateForm')?.addEventListener('submit', function(e) {
    const selected = document.getElementById('selectedCount').textContent;
    const fields = document.getElementById('fieldsCount').textContent;
    
    if (!confirm(`Are you sure you want to update ${selected} listing(s) with ${fields} field(s)?`)) {
        e.preventDefault();
    }
});
</script>
@endpush
