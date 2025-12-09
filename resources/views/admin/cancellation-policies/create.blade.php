@extends('layouts.admin')

@section('title', 'Create Cancellation Policy')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('admin.cancellation-policies.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="fas fa-arrow-left"></i> Back to Policies
        </a>
        <h2 class="mb-1">Create Cancellation Policy</h2>
        <p class="text-muted mb-0">Configure refund rules and time windows</p>
    </div>

    <form method="POST" action="{{ route('admin.cancellation-policies.store') }}">
        @csrf
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Policy Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required placeholder="e.g., Standard Customer Cancellation Policy">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Describe when and how this policy applies...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Applies To <span class="text-danger">*</span></label>
                                <select name="applies_to" class="form-select @error('applies_to') is-invalid @enderror" required>
                                    <option value="">Select...</option>
                                    <option value="all" {{ old('applies_to') == 'all' ? 'selected' : '' }}>All Roles</option>
                                    <option value="customer" {{ old('applies_to') == 'customer' ? 'selected' : '' }}>Customers Only</option>
                                    <option value="vendor" {{ old('applies_to') == 'vendor' ? 'selected' : '' }}>Vendors Only</option>
                                    <option value="admin" {{ old('applies_to') == 'admin' ? 'selected' : '' }}>Admin Only</option>
                                </select>
                                @error('applies_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Booking Type <span class="text-danger">*</span></label>
                                <select name="booking_type" class="form-select @error('booking_type') is-invalid @enderror" required>
                                    <option value="">Select...</option>
                                    <option value="all" {{ old('booking_type') == 'all' ? 'selected' : '' }}>All Types</option>
                                    <option value="ooh" {{ old('booking_type') == 'ooh' ? 'selected' : '' }}>OOH</option>
                                    <option value="dooh" {{ old('booking_type') == 'dooh' ? 'selected' : '' }}>DOOH</option>
                                    <option value="pos" {{ old('booking_type') == 'pos' ? 'selected' : '' }}>POS</option>
                                </select>
                                @error('booking_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_default" value="1" class="form-check-input" 
                                           id="isDefault" {{ old('is_default') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isDefault">Set as Default Policy</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                           id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Windows -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Time Windows & Refund Rules</h5>
                        <small class="text-muted">Define refund percentages based on hours before booking start</small>
                    </div>
                    <div class="card-body">
                        <div id="timeWindowsContainer">
                            <!-- Time windows will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTimeWindow()">
                            <i class="fas fa-plus"></i> Add Time Window
                        </button>
                    </div>
                </div>

                <!-- Customer Fee Configuration -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Customer Cancellation Fee</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Fee Type</label>
                                <select name="customer_fee_type" class="form-select">
                                    <option value="percentage" {{ old('customer_fee_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed" {{ old('customer_fee_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Fee Value</label>
                                <input type="number" name="customer_fee_value" class="form-control" 
                                       value="{{ old('customer_fee_value', 0) }}" step="0.01" min="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Min Amount (₹)</label>
                                <input type="number" name="customer_fee_min" class="form-control" 
                                       value="{{ old('customer_fee_min') }}" step="0.01" min="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Max Amount (₹)</label>
                                <input type="number" name="customer_fee_max" class="form-control" 
                                       value="{{ old('customer_fee_max') }}" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Penalty Configuration -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Vendor Cancellation Penalty</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Penalty Type</label>
                                <select name="vendor_penalty_type" class="form-select">
                                    <option value="percentage" {{ old('vendor_penalty_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed" {{ old('vendor_penalty_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Penalty Value</label>
                                <input type="number" name="vendor_penalty_value" class="form-control" 
                                       value="{{ old('vendor_penalty_value', 0) }}" step="0.01" min="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Min Amount (₹)</label>
                                <input type="number" name="vendor_penalty_min" class="form-control" 
                                       value="{{ old('vendor_penalty_min') }}" step="0.01" min="0">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Max Amount (₹)</label>
                                <input type="number" name="vendor_penalty_max" class="form-control" 
                                       value="{{ old('vendor_penalty_max') }}" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Refund Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Refund Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="auto_refund_enabled" value="1" class="form-check-input" 
                                       id="autoRefund" {{ old('auto_refund_enabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="autoRefund">Enable Auto Refund</label>
                            </div>
                            <small class="text-muted">Automatically process refunds through payment gateway</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Refund Processing Days</label>
                            <input type="number" name="refund_processing_days" class="form-control" 
                                   value="{{ old('refund_processing_days', 7) }}" min="1">
                            <small class="text-muted">Expected days for refund to reach customer</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Refund Method</label>
                            <select name="refund_method" class="form-select">
                                <option value="original" {{ old('refund_method') == 'original' ? 'selected' : '' }}>Original Payment Method</option>
                                <option value="bank" {{ old('refund_method') == 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="wallet" {{ old('refund_method') == 'wallet' ? 'selected' : '' }}>Wallet Credit</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- POS-Specific Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">POS-Specific Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="pos_auto_refund_disabled" value="1" class="form-check-input" 
                                       id="posDisable" {{ old('pos_auto_refund_disabled', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="posDisable">Disable Auto-Refund for POS</label>
                            </div>
                            <small class="text-muted">POS bookings require manual refund processing</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">POS Refund Note</label>
                            <textarea name="pos_refund_note" class="form-control" rows="3" 
                                      placeholder="Instructions for POS refund handling...">{{ old('pos_refund_note') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Constraints -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Booking Constraints</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Min Hours Before Start</label>
                            <input type="number" name="min_hours_before_start" class="form-control" 
                                   value="{{ old('min_hours_before_start') }}" min="0">
                            <small class="text-muted">Minimum hours required for cancellation</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Hours Before Start</label>
                            <input type="number" name="max_hours_before_start" class="form-control" 
                                   value="{{ old('max_hours_before_start') }}" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Min Booking Amount (₹)</label>
                            <input type="number" name="min_booking_amount" class="form-control" 
                                   value="{{ old('min_booking_amount') }}" step="0.01" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Booking Amount (₹)</label>
                            <input type="number" name="max_booking_amount" class="form-control" 
                                   value="{{ old('max_booking_amount') }}" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Create Policy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
let windowCounter = 0;

function addTimeWindow() {
    windowCounter++;
    const container = document.getElementById('timeWindowsContainer');
    const windowHtml = `
        <div class="card mb-2 time-window" id="window${windowCounter}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Time Window #${windowCounter}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTimeWindow(${windowCounter})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label small">Hours Before Start</label>
                        <input type="number" name="time_windows[${windowCounter}][hours_before]" 
                               class="form-control form-control-sm" placeholder="e.g., 72" min="0" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label small">Refund Percentage</label>
                        <input type="number" name="time_windows[${windowCounter}][refund_percent]" 
                               class="form-control form-control-sm" placeholder="e.g., 100" min="0" max="100" required>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label small">Customer Fee % (Optional)</label>
                        <input type="number" name="time_windows[${windowCounter}][customer_fee_percent]" 
                               class="form-control form-control-sm" placeholder="Override" min="0" max="100">
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label small">Vendor Penalty % (Optional)</label>
                        <input type="number" name="time_windows[${windowCounter}][vendor_penalty_percent]" 
                               class="form-control form-control-sm" placeholder="Override" min="0" max="100">
                    </div>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', windowHtml);
}

function removeTimeWindow(id) {
    document.getElementById('window' + id).remove();
}

// Add initial time window
document.addEventListener('DOMContentLoaded', function() {
    addTimeWindow();
});
</script>
@endpush
@endsection
