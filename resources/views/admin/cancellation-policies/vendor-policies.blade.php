@extends('layouts.admin')

@section('title', 'Vendor Cancellation Policies')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Vendor Cancellation Policies</h2>
            <p class="text-muted mb-0">Monitor vendor-created custom cancellation policies</p>
        </div>
        <div>
            <a href="{{ route('admin.cancellation-policies.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left"></i> Global Policies
            </a>
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> View Refunds
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $policies->total() }}</h3>
                    <p class="text-muted mb-0 small">Total Vendor Policies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">{{ $policies->where('is_active', true)->count() }}</h3>
                    <p class="text-muted mb-0 small">Active Policies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-info">{{ $policies->unique('vendor_id')->count() }}</h3>
                    <p class="text-muted mb-0 small">Vendors with Policies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning">{{ $policies->where('enforce_campaign_start', true)->count() }}</h3>
                    <p class="text-muted mb-0 small">Campaign Enforcement</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Policies Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Vendor-Created Policies</h5>
        </div>
        <div class="card-body">
            @if($policies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Vendor</th>
                                <th>Policy Name</th>
                                <th>Booking Type</th>
                                <th>Time Windows</th>
                                <th>Settings</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $policy)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.vendors.show', $policy->vendor_id) }}" class="text-decoration-none">
                                        <strong>{{ $policy->vendor->name ?? 'N/A' }}</strong>
                                    </a>
                                    <br><small class="text-muted">ID: {{ $policy->vendor_id }}</small>
                                </td>
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
                                        <br><small class="text-muted">
                                            Max refund: {{ $policy->time_windows[0]['refund_percent'] }}%
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($policy->auto_refund_enabled)
                                        <span class="badge bg-success badge-sm">
                                            <i class="fas fa-robot"></i> Auto
                                        </span>
                                    @endif
                                    @if($policy->enforce_campaign_start)
                                        <span class="badge bg-warning badge-sm">
                                            <i class="fas fa-shield-check"></i> Campaign
                                        </span>
                                    @endif
                                    @if($policy->allow_partial_refund)
                                        <span class="badge bg-info badge-sm">
                                            <i class="fas fa-percentage"></i> Partial
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($policy->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $policy->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info view-policy" 
                                                data-policy="{{ json_encode($policy) }}"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$policy->is_active)
                                            <form action="{{ route('admin.cancellation-policies.toggle-status', $policy->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Activate">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.cancellation-policies.toggle-status', $policy->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="Deactivate">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="btn btn-outline-danger delete-policy" 
                                                data-policy-id="{{ $policy->id }}"
                                                data-policy-name="{{ $policy->name }}"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $policies->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-3">No vendor policies found</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Policy Modal -->
<div class="modal fade" id="viewPolicyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Policy Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="policyDetailsContent">
                <!-- Policy details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                <p class="text-danger small">
                    <i class="fas fa-exclamation-triangle"></i> This action cannot be undone. 
                    Policies that have been used in refunds cannot be deleted.
                </p>
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
    // View policy details
    document.querySelectorAll('.view-policy').forEach(btn => {
        btn.addEventListener('click', function() {
            const policy = JSON.parse(this.dataset.policy);
            
            let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Policy Name:</strong><br>${policy.name}
                    </div>
                    <div class="col-md-6">
                        <strong>Vendor:</strong><br>${policy.vendor?.name || 'N/A'}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Booking Type:</strong><br>
                        <span class="badge bg-info">${policy.booking_type ? policy.booking_type.toUpperCase() : 'ALL'}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <span class="badge bg-${policy.is_active ? 'success' : 'secondary'}">${policy.is_active ? 'Active' : 'Inactive'}</span>
                    </div>
                </div>
            `;

            if (policy.description) {
                html += `
                    <div class="mb-3">
                        <strong>Description:</strong><br>${policy.description}
                    </div>
                `;
            }

            html += `
                <div class="mb-3">
                    <strong>Settings:</strong><br>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-${policy.auto_refund_enabled ? 'check text-success' : 'times text-danger'}"></i> Auto-Refund</li>
                        <li><i class="fas fa-${policy.enforce_campaign_start ? 'check text-success' : 'times text-danger'}"></i> Campaign Start Enforcement</li>
                        <li><i class="fas fa-${policy.allow_partial_refund ? 'check text-success' : 'times text-danger'}"></i> Partial Refunds</li>
                        <li><i class="fas fa-clock"></i> Processing Days: ${policy.refund_processing_days}</li>
                    </ul>
                </div>
            `;

            if (policy.time_windows && policy.time_windows.length > 0) {
                html += `
                    <div class="mb-3">
                        <strong>Time Windows:</strong><br>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Hours Before</th>
                                    <th>Refund %</th>
                                    <th>Customer Fee %</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                policy.time_windows.forEach(window => {
                    const days = Math.floor(window.hours_before / 24);
                    const displayHours = days > 0 ? `${days} day(s)` : `${window.hours_before} hour(s)`;
                    const feeDisplay = window.customer_fee_percent !== null ? `${window.customer_fee_percent}%` : 'Default';
                    
                    html += `
                        <tr>
                            <td>${displayHours}+</td>
                            <td><span class="badge bg-success">${window.refund_percent}%</span></td>
                            <td>${feeDisplay}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            }

            html += `
                <div class="row">
                    <div class="col-md-6">
                        <strong>Customer Fee:</strong><br>
                        ${policy.customer_fee_type === 'percentage' ? policy.customer_fee_value + '%' : '₹' + policy.customer_fee_value}
                        ${policy.customer_min_fee ? `<br><small>Min: ₹${policy.customer_min_fee}</small>` : ''}
                        ${policy.customer_max_fee ? `<br><small>Max: ₹${policy.customer_max_fee}</small>` : ''}
                    </div>
                    <div class="col-md-6">
                        <strong>Created:</strong><br>
                        ${new Date(policy.created_at).toLocaleDateString()}
                    </div>
                </div>
            `;

            document.getElementById('policyDetailsContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewPolicyModal')).show();
        });
    });

    // Delete policy
    document.querySelectorAll('.delete-policy').forEach(btn => {
        btn.addEventListener('click', function() {
            const policyId = this.dataset.policyId;
            const policyName = this.dataset.policyName;
            
            document.getElementById('deletePolicyName').textContent = policyName;
            document.getElementById('deletePolicyForm').action = `/admin/cancellation-policies/${policyId}`;
            
            new bootstrap.Modal(document.getElementById('deletePolicyModal')).show();
        });
    });
});
</script>
@endpush
@endsection
