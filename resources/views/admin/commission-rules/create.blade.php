@extends('layouts.admin')

@section('title', isset($commissionRule) ? 'Edit Commission Rule' : 'Create Commission Rule')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="bi bi-{{ isset($commissionRule) ? 'pencil' : 'plus-circle' }} me-2"></i>
                {{ isset($commissionRule) ? 'Edit' : 'Create' }} Commission Rule
            </h4>
            <a href="{{ route('admin.commission-rules.index') }}" class="btn btn-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ isset($commissionRule) ? route('admin.commission-rules.update', $commissionRule) : route('admin.commission-rules.store') }}" method="POST">
                @csrf
                @if(isset($commissionRule))
                @method('PUT')
                @endif

                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label class="form-label">Rule Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $commissionRule->name ?? '') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Priority *</label>
                        <input type="number" name="priority" class="form-control" value="{{ old('priority', $commissionRule->priority ?? 0) }}" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" {{ old('is_active', $commissionRule->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $commissionRule->description ?? '') }}</textarea>
                </div>

                <!-- Rule Type -->
                <div class="mb-4">
                    <label class="form-label">Rule Type *</label>
                    <select name="rule_type" id="ruleType" class="form-select" required>
                        <option value="flat" {{ old('rule_type', $commissionRule->rule_type ?? 'flat') === 'flat' ? 'selected' : '' }}>Flat Commission (All Bookings)</option>
                        <option value="vendor" {{ old('rule_type', $commissionRule->rule_type ?? '') === 'vendor' ? 'selected' : '' }}>Per Vendor</option>
                        <option value="hoarding" {{ old('rule_type', $commissionRule->rule_type ?? '') === 'hoarding' ? 'selected' : '' }}>Per Hoarding</option>
                        <option value="location" {{ old('rule_type', $commissionRule->rule_type ?? '') === 'location' ? 'selected' : '' }}>Per Location</option>
                        <option value="time_based" {{ old('rule_type', $commissionRule->rule_type ?? '') === 'time_based' ? 'selected' : '' }}>Time-Based</option>
                        <option value="seasonal" {{ old('rule_type', $commissionRule->rule_type ?? '') === 'seasonal' ? 'selected' : '' }}>Seasonal Offer</option>
                    </select>
                </div>

                <!-- Filters (show/hide based on rule type) -->
                <div class="row mb-4">
                    <div class="col-md-6" id="vendorFilter">
                        <label class="form-label">Specific Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ old('vendor_id', $commissionRule->vendor_id ?? '') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6" id="hoardingFilter">
                        <label class="form-label">Specific Hoarding</label>
                        <select name="hoarding_id" class="form-select">
                            <option value="">All Hoardings</option>
                            @foreach($hoardings as $hoarding)
                            <option value="{{ $hoarding->id }}" {{ old('hoarding_id', $commissionRule->hoarding_id ?? '') == $hoarding->id ? 'selected' : '' }}>
                                {{ $hoarding->title }} ({{ $hoarding->vendor->name }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $commissionRule->city ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Area</label>
                        <input type="text" name="area" class="form-control" value="{{ old('area', $commissionRule->area ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hoarding Type</label>
                        <select name="hoarding_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="billboard" {{ old('hoarding_type', $commissionRule->hoarding_type ?? '') === 'billboard' ? 'selected' : '' }}>Billboard</option>
                            <option value="digital" {{ old('hoarding_type', $commissionRule->hoarding_type ?? '') === 'digital' ? 'selected' : '' }}>Digital</option>
                            <option value="transit" {{ old('hoarding_type', $commissionRule->hoarding_type ?? '') === 'transit' ? 'selected' : '' }}>Transit</option>
                            <option value="street_furniture" {{ old('hoarding_type', $commissionRule->hoarding_type ?? '') === 'street_furniture' ? 'selected' : '' }}>Street Furniture</option>
                            <option value="wallscape" {{ old('hoarding_type', $commissionRule->hoarding_type ?? '') === 'wallscape' ? 'selected' : '' }}>Wallscape</option>
                            <option value="mobile" {{ old('hoarding_type', $commissionRule->hoarding_type ?? '') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                        </select>
                    </div>
                </div>

                <!-- Validity Period -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Valid From</label>
                        <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from', $commissionRule->valid_from?->format('Y-m-d') ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid To</label>
                        <input type="date" name="valid_to" class="form-control" value="{{ old('valid_to', $commissionRule->valid_to?->format('Y-m-d') ?? '') }}">
                    </div>
                </div>

                <!-- Seasonal -->
                <div class="mb-4" id="seasonalSection">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_seasonal" id="isSeasonal" {{ old('is_seasonal', $commissionRule->is_seasonal ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isSeasonal">Seasonal Offer</label>
                    </div>
                    <input type="text" name="season_name" class="form-control mt-2" placeholder="Season Name (e.g., Summer Sale, Diwali Offer)" value="{{ old('season_name', $commissionRule->season_name ?? '') }}">
                </div>

                <!-- Commission Configuration -->
                <hr>
                <h5 class="mb-3">Commission Configuration</h5>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Commission Type *</label>
                        <select name="commission_type" id="commissionType" class="form-select" required>
                            <option value="percentage" {{ old('commission_type', $commissionRule->commission_type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                            <option value="fixed" {{ old('commission_type', $commissionRule->commission_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="tiered" {{ old('commission_type', $commissionRule->commission_type ?? '') === 'tiered' ? 'selected' : '' }}>Tiered (Advanced)</option>
                        </select>
                    </div>
                    <div class="col-md-6" id="commissionValueField">
                        <label class="form-label">Commission Value *</label>
                        <input type="number" name="commission_value" step="0.01" min="0" class="form-control" value="{{ old('commission_value', $commissionRule->commission_value ?? '') }}" required>
                        <small class="text-muted" id="commissionHelp">Enter percentage (e.g., 15 for 15%) or fixed amount</small>
                    </div>
                </div>

                <!-- Booking Constraints -->
                <hr>
                <h5 class="mb-3">Booking Constraints (Optional)</h5>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Min Booking Amount</label>
                        <input type="number" name="min_booking_amount" step="0.01" min="0" class="form-control" value="{{ old('min_booking_amount', $commissionRule->min_booking_amount ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Booking Amount</label>
                        <input type="number" name="max_booking_amount" step="0.01" min="0" class="form-control" value="{{ old('max_booking_amount', $commissionRule->max_booking_amount ?? '') }}">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Min Duration (Days)</label>
                        <input type="number" name="min_duration_days" min="1" class="form-control" value="{{ old('min_duration_days', $commissionRule->min_duration_days ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Max Duration (Days)</label>
                        <input type="number" name="max_duration_days" min="1" class="form-control" value="{{ old('max_duration_days', $commissionRule->max_duration_days ?? '') }}">
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.commission-rules.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>{{ isset($commissionRule) ? 'Update' : 'Create' }} Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Dynamic help text for commission value
document.getElementById('commissionType').addEventListener('change', function() {
    const helpText = document.getElementById('commissionHelp');
    if (this.value === 'percentage') {
        helpText.textContent = 'Enter percentage (e.g., 15 for 15%)';
    } else if (this.value === 'fixed') {
        helpText.textContent = 'Enter fixed amount in ₹';
    } else {
        helpText.textContent = 'Tiered configuration required (see documentation)';
    }
});
</script>
@endsection
