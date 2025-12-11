@extends('layouts.vendor')

@section('title', 'Cancellation Policies')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">Cancellation Policies</h2>
            <p class="text-muted mb-0">Manage your custom cancellation and refund rules</p>
        </div>
        <a href="{{ route('vendor.cancellation-policies.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Policy
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Your Custom Policies -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-gear"></i> Your Custom Policies</h5>
        </div>
        <div class="card-body">
            @if($vendorPolicies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Policy Name</th>
                                <th>Booking Type</th>
                                <th>Time Windows</th>
                                <th>Auto-Refund</th>
                                <th>Campaign Enforcement</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendorPolicies as $policy)
                            <tr>
                                <td>
                                    <strong>{{ $policy->name }}</strong>
                                    @if($policy->description)
                                        <br><small class="text-muted">{{ Str::limit($policy->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $policy->booking_type ? strtoupper($policy->booking_type) : 'ALL' }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ count($policy->time_windows ?? []) }} tier(s)</small>
                                    @if(isset($policy->time_windows[0]))
                                        <br><small class="text-muted">Max: {{ $policy->time_windows[0]['refund_percent'] }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @if($policy->auto_refund_enabled)
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Enabled</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Manual</span>
                                    @endif
                                </td>
                                <td>
                                    @if($policy->enforce_campaign_start)
                                        <span class="badge bg-warning"><i class="bi bi-shield-check"></i> Enforced</span>
                                    @else
                                        <span class="badge bg-light text-dark">Not Enforced</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-status" type="checkbox" 
                                               data-policy-id="{{ $policy->id }}"
                                               {{ $policy->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            {{ $policy->is_active ? 'Active' : 'Inactive' }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('vendor.cancellation-policies.edit', $policy->id) }}" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger delete-policy" 
                                                data-policy-id="{{ $policy->id }}"
                                                data-policy-name="{{ $policy->name }}"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">You haven't created any custom cancellation policies yet.</p>
                    <a href="{{ route('vendor.cancellation-policies.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create Your First Policy
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Global Default Policies (Reference) -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-globe"></i> Global Default Policies (Reference)</h5>
            <small class="text-muted">These system-wide policies apply when you don't have a custom policy</small>
        </div>
        <div class="card-body">
            @if($globalPolicies->count() > 0)
                @foreach($globalPolicies as $policy)
                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                {{ $policy->name }}
                                @if($policy->is_default)
                                    <span class="badge bg-primary">System Default</span>
                                @endif
                            </h6>
                            @if($policy->description)
                                <p class="text-muted small mb-2">{{ $policy->description }}</p>
                            @endif
                            
                            <div class="row g-2 mt-2">
                                <div class="col-auto">
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-tag"></i> 
                                        {{ $policy->booking_type ? strtoupper($policy->booking_type) : 'ALL TYPES' }}
                                    </span>
                                </div>
                                @if($policy->auto_refund_enabled)
                                <div class="col-auto">
                                    <span class="badge bg-success">
                                        <i class="bi bi-robot"></i> Auto-Refund
                                    </span>
                                </div>
                                @endif
                                @if($policy->enforce_campaign_start)
                                <div class="col-auto">
                                    <span class="badge bg-warning">
                                        <i class="bi bi-shield-check"></i> Campaign Enforcement
                                    </span>
                                </div>
                                @endif
                            </div>

                            <!-- Time Windows Preview -->
                            @if($policy->time_windows && count($policy->time_windows) > 0)
                            <div class="mt-3">
                                <small class="text-muted fw-bold">Refund Schedule:</small>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Cancellation Window</th>
                                                <th>Refund %</th>
                                                <th>Customer Fee</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($policy->time_windows as $window)
                                            <tr>
                                                <td>
                                                    @if($window['hours_before'] >= 168)
                                                        {{ round($window['hours_before'] / 24) }} days or more
                                                    @elseif($window['hours_before'] >= 24)
                                                        {{ round($window['hours_before'] / 24) }} - {{ round(($policy->time_windows[$loop->index - 1]['hours_before'] ?? 999) / 24) }} days
                                                    @else
                                                        Less than 24 hours
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $window['refund_percent'] >= 75 ? 'bg-success' : ($window['refund_percent'] >= 25 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $window['refund_percent'] }}%
                                                    </span>
                                                </td>
                                                <td>
                                                    @if(isset($window['customer_fee_percent']))
                                                        <span class="badge bg-danger">{{ $window['customer_fee_percent'] }}%</span>
                                                    @else
                                                        <span class="text-muted">Default: {{ $policy->customer_fee_value }}{{ $policy->customer_fee_type == 'percentage' ? '%' : '₹' }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-muted mb-0">No global default policies configured.</p>
            @endif
        </div>
    </div>

    <!-- Refund Calculator -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-calculator"></i> Refund Calculator</h5>
        </div>
        <div class="card-body">
            <form id="refundCalculatorForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Select Policy</label>
                        <select class="form-select" id="calculator_policy_id" required>
                            <option value="">Choose policy...</option>
                            @foreach($vendorPolicies as $policy)
                                <option value="{{ $policy->id }}">{{ $policy->name }} (Your Policy)</option>
                            @endforeach
                            @foreach($globalPolicies as $policy)
                                <option value="{{ $policy->id }}">{{ $policy->name }} (Global)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Booking Amount (₹)</label>
                        <input type="number" class="form-control" id="calculator_amount" 
                               placeholder="50000" min="0" step="100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hours Before Start</label>
                        <input type="number" class="form-control" id="calculator_hours" 
                               placeholder="168" min="0" step="1" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-info">
                        <i class="bi bi-calculator"></i> Calculate Refund
                    </button>
                </div>
            </form>

            <div id="calculatorResult" class="mt-4" style="display: none;">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Refund Calculation Result:</h6>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <small class="text-muted">Booking Amount</small>
                            <h5 id="result_booking_amount">-</h5>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Refund Percentage</small>
                            <h5 id="result_refund_percent">-</h5>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Customer Fee</small>
                            <h5 id="result_customer_fee">-</h5>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Final Refund Amount</small>
                            <h5 class="text-success" id="result_refund_amount">-</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePolicyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the policy "<strong id="deletePolicyName"></strong>"?</p>
                <p class="text-danger small"><i class="bi bi-exclamation-triangle"></i> This action cannot be undone. If this policy is being used in active bookings, deletion will be prevented.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deletePolicyForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Policy</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle policy status
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const policyId = this.dataset.policyId;
            const isActive = this.checked;
            
            fetch(`/vendor/cancellation-policies/${policyId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_active: isActive })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const label = this.nextElementSibling;
                    label.textContent = isActive ? 'Active' : 'Inactive';
                    
                    // Show toast notification
                    showToast('success', 'Policy status updated successfully');
                } else {
                    this.checked = !isActive;
                    showToast('error', data.message || 'Failed to update policy status');
                }
            })
            .catch(error => {
                this.checked = !isActive;
                showToast('error', 'An error occurred');
            });
        });
    });

    // Delete policy
    document.querySelectorAll('.delete-policy').forEach(btn => {
        btn.addEventListener('click', function() {
            const policyId = this.dataset.policyId;
            const policyName = this.dataset.policyName;
            
            document.getElementById('deletePolicyName').textContent = policyName;
            document.getElementById('deletePolicyForm').action = `/vendor/cancellation-policies/${policyId}`;
            
            new bootstrap.Modal(document.getElementById('deletePolicyModal')).show();
        });
    });

    // Refund calculator
    document.getElementById('refundCalculatorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const policyId = document.getElementById('calculator_policy_id').value;
        const amount = document.getElementById('calculator_amount').value;
        const hours = document.getElementById('calculator_hours').value;
        
        fetch('/vendor/cancellation-policies/preview-refund', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                policy_id: policyId,
                booking_amount: parseFloat(amount),
                hours_before_start: parseInt(hours)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const result = data.calculation;
                const formatted = data.formatted;
                
                document.getElementById('result_booking_amount').textContent = formatted.booking_amount;
                document.getElementById('result_refund_percent').textContent = formatted.refund_percent;
                document.getElementById('result_customer_fee').textContent = formatted.customer_fee;
                document.getElementById('result_refund_amount').textContent = formatted.refund_amount;
                
                document.getElementById('calculatorResult').style.display = 'block';
            } else {
                showToast('error', data.message || 'Calculation failed');
            }
        })
        .catch(error => {
            showToast('error', 'An error occurred during calculation');
        });
    });

    function showToast(type, message) {
        // Simple toast implementation - customize as needed
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const toast = document.createElement('div');
        toast.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }
});
</script>
@endpush
@endsection
