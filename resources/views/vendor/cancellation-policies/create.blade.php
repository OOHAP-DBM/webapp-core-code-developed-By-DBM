@extends('layouts.vendor')

@section('title', 'Create Cancellation Policy')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Create Cancellation Policy</h2>
            <p class="text-muted mb-0">Define custom refund rules for your bookings</p>
        </div>
        <a href="{{ route('vendor.cancellation-policies.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Policies
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <h6 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Validation Errors</h6>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('vendor.cancellation-policies.store') }}" method="POST" id="policyForm">
        @csrf

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Policy Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" 
                                   placeholder="e.g., Premium Hoarding Cancellation Policy" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe when this policy applies...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="booking_type" class="form-label">Applies to Booking Type</label>
                                <select class="form-select @error('booking_type') is-invalid @enderror" 
                                        id="booking_type" name="booking_type">
                                    <option value="">All Types (OOH, DOOH, POS)</option>
                                    <option value="ooh" {{ old('booking_type') == 'ooh' ? 'selected' : '' }}>OOH Only</option>
                                    <option value="dooh" {{ old('booking_type') == 'dooh' ? 'selected' : '' }}>DOOH Only</option>
                                    <option value="pos" {{ old('booking_type') == 'pos' ? 'selected' : '' }}>POS Only</option>
                                </select>
                                @error('booking_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" 
                                           name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active (policy will be used immediately)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Windows -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Refund Time Windows</h5>
                        <small>Define refund percentages based on cancellation timing</small>
                    </div>
                    <div class="card-body">
                        <div id="timeWindows">
                            <!-- Time windows will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm" id="addTimeWindow">
                            <i class="bi bi-plus-circle"></i> Add Time Window
                        </button>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Add multiple tiers (e.g., 7 days = 100%, 3 days = 50%, etc.)
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Customer Cancellation Fees -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Customer Cancellation Fees</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="customer_fee_type" class="form-label">Fee Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('customer_fee_type') is-invalid @enderror" 
                                        id="customer_fee_type" name="customer_fee_type" required>
                                    <option value="percentage" {{ old('customer_fee_type', 'percentage') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    <option value="fixed" {{ old('customer_fee_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                                @error('customer_fee_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="customer_fee_value" class="form-label">Fee Value <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('customer_fee_value') is-invalid @enderror" 
                                       id="customer_fee_value" name="customer_fee_value" 
                                       value="{{ old('customer_fee_value', 10) }}" 
                                       min="0" step="0.01" required>
                                <small class="text-muted" id="feeValueHint">% of booking amount</small>
                                @error('customer_fee_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_min_fee" class="form-label">Minimum Fee (₹)</label>
                                <input type="number" class="form-control @error('customer_min_fee') is-invalid @enderror" 
                                       id="customer_min_fee" name="customer_min_fee" 
                                       value="{{ old('customer_min_fee', 500) }}" 
                                       min="0" step="1" placeholder="500">
                                @error('customer_min_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="customer_max_fee" class="form-label">Maximum Fee (₹)</label>
                                <input type="number" class="form-control @error('customer_max_fee') is-invalid @enderror" 
                                       id="customer_max_fee" name="customer_max_fee" 
                                       value="{{ old('customer_max_fee', 10000) }}" 
                                       min="0" step="1" placeholder="10000">
                                @error('customer_max_fee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refund Settings -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Refund Processing Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="refund_processing_days" class="form-label">Processing Days <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('refund_processing_days') is-invalid @enderror" 
                                       id="refund_processing_days" name="refund_processing_days" 
                                       value="{{ old('refund_processing_days', 7) }}" 
                                       min="1" max="30" required>
                                <small class="text-muted">Business days for refund processing (1-30)</small>
                                @error('refund_processing_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_refund_enabled" 
                                   name="auto_refund_enabled" value="1" 
                                   {{ old('auto_refund_enabled', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_refund_enabled">
                                <strong>Enable Auto-Refund</strong>
                                <br><small class="text-muted">Automatically process refunds through payment gateway</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enforce_campaign_start" 
                                   name="enforce_campaign_start" value="1" 
                                   {{ old('enforce_campaign_start', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enforce_campaign_start">
                                <strong>Enforce Campaign Start Rule</strong>
                                <br><small class="text-muted">No refund allowed after campaign/booking starts</small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="allow_partial_refund" 
                                   name="allow_partial_refund" value="1" 
                                   {{ old('allow_partial_refund', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allow_partial_refund">
                                <strong>Allow Partial Refunds</strong>
                                <br><small class="text-muted">Enable tiered refund percentages based on time windows</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Preview -->
                <div class="card mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-eye"></i> Policy Preview</h6>
                    </div>
                    <div class="card-body">
                        <div id="policyPreview">
                            <p class="text-muted small">Configure time windows to see preview</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li class="mb-2">Add multiple time windows for flexible refunds</li>
                            <li class="mb-2">Higher hours = earlier cancellation = higher refund</li>
                            <li class="mb-2">Enable campaign enforcement to prevent refunds after start</li>
                            <li class="mb-2">Set min/max fees to control cancellation costs</li>
                            <li class="mb-2">Auto-refund saves manual processing time</li>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-check-circle"></i> Create Policy
                        </button>
                        <a href="{{ route('vendor.cancellation-policies.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Time Window Template -->
<template id="timeWindowTemplate">
    <div class="card mb-3 time-window-item">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Time Window <span class="window-number"></span></h6>
                <button type="button" class="btn btn-sm btn-outline-danger remove-time-window">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Hours Before Start <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="time_windows[INDEX][hours_before]" 
                           min="0" step="1" placeholder="168" required>
                    <small class="text-muted">168h = 7 days</small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Refund % <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="time_windows[INDEX][refund_percent]" 
                           min="0" max="100" step="1" placeholder="100" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fee % (Optional)</label>
                    <input type="number" class="form-control" name="time_windows[INDEX][customer_fee_percent]" 
                           min="0" max="100" step="1" placeholder="0">
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let windowIndex = 0;

    // Add initial time window
    addTimeWindow();

    // Add time window button
    document.getElementById('addTimeWindow').addEventListener('click', addTimeWindow);

    // Fee type change
    document.getElementById('customer_fee_type').addEventListener('change', function() {
        const hint = document.getElementById('feeValueHint');
        hint.textContent = this.value === 'percentage' ? '% of booking amount' : 'Fixed amount in ₹';
    });

    // Update preview when inputs change
    document.getElementById('policyForm').addEventListener('input', updatePreview);
    document.getElementById('policyForm').addEventListener('change', updatePreview);

    function addTimeWindow() {
        const template = document.getElementById('timeWindowTemplate');
        const clone = template.content.cloneNode(true);
        
        // Replace INDEX with actual index
        clone.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace('INDEX', windowIndex);
        });
        
        // Set window number
        clone.querySelector('.window-number').textContent = '#' + (windowIndex + 1);
        
        // Add remove handler
        const removeBtn = clone.querySelector('.remove-time-window');
        removeBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.time-window-item').length > 1) {
                this.closest('.time-window-item').remove();
                updateWindowNumbers();
                updatePreview();
            } else {
                alert('At least one time window is required');
            }
        });
        
        document.getElementById('timeWindows').appendChild(clone);
        windowIndex++;
        
        updatePreview();
    }

    function updateWindowNumbers() {
        document.querySelectorAll('.window-number').forEach((el, idx) => {
            el.textContent = '#' + (idx + 1);
        });
    }

    function updatePreview() {
        const windows = [];
        document.querySelectorAll('.time-window-item').forEach(item => {
            const hours = item.querySelector('input[name*="hours_before"]').value;
            const refund = item.querySelector('input[name*="refund_percent"]').value;
            const fee = item.querySelector('input[name*="customer_fee_percent"]').value;
            
            if (hours && refund) {
                windows.push({ hours, refund, fee });
            }
        });

        if (windows.length === 0) {
            document.getElementById('policyPreview').innerHTML = '<p class="text-muted small">Configure time windows to see preview</p>';
            return;
        }

        // Sort by hours descending
        windows.sort((a, b) => parseInt(b.hours) - parseInt(a.hours));

        let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead class="table-light"><tr><th>Window</th><th>Refund</th><th>Fee</th></tr></thead><tbody>';
        
        windows.forEach((w, idx) => {
            const days = Math.floor(w.hours / 24);
            const displayHours = days > 0 ? `${days}d` : `${w.hours}h`;
            const feeDisplay = w.fee ? `${w.fee}%` : 'Default';
            
            html += `<tr>
                <td>${displayHours}+</td>
                <td><span class="badge bg-success">${w.refund}%</span></td>
                <td><span class="badge bg-warning text-dark">${feeDisplay}</span></td>
            </tr>`;
        });
        
        html += '</tbody></table></div>';
        
        const campaignEnforced = document.getElementById('enforce_campaign_start').checked;
        if (campaignEnforced) {
            html += '<div class="alert alert-warning alert-sm mt-2 mb-0"><small><i class="bi bi-shield-check"></i> No refund after campaign starts</small></div>';
        }
        
        document.getElementById('policyPreview').innerHTML = html;
    }
});
</script>
@endpush
@endsection
