@extends('layouts.admin')

@section('title', 'KYC Review Detail #' . $kyc->id)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ url('/admin/vendor/kyc-reviews') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <h2 class="mb-1">KYC Review Detail #{{ $kyc->id }}</h2>
                    <p class="text-muted mb-0">{{ $kyc->business_name }}</p>
                </div>
                <div>
                    @if($kyc->razorpay_subaccount_id)
                        <button type="button" class="btn btn-outline-info" onclick="syncRazorpayStatus()">
                            <i class="fas fa-sync-alt"></i> Sync Razorpay Status
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-file-alt"></i> KYC Status</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%"><strong>Verification Status:</strong></td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'secondary',
                                        'under_review' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'resubmission_required' => 'warning',
                                    ];
                                    $color = $statusColors[$kyc->verification_status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} px-3 py-2">
                                    {{ str_replace('_', ' ', strtoupper($kyc->verification_status)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Submitted At:</strong></td>
                            <td>{{ $kyc->submitted_at ? $kyc->submitted_at->format('M d, Y H:i A') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Verified At:</strong></td>
                            <td>{{ $kyc->verified_at ? $kyc->verified_at->format('M d, Y H:i A') : '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Verified By:</strong></td>
                            <td>{{ $kyc->verifier ? $kyc->verifier->name : '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-wallet"></i> Payout Status</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td width="40%"><strong>Payout Status:</strong></td>
                            <td>
                                @php
                                    $payoutColors = [
                                        'pending_verification' => 'warning',
                                        'verified' => 'success',
                                        'rejected' => 'danger',
                                        'failed' => 'secondary',
                                    ];
                                    $payoutColor = $payoutColors[$kyc->payout_status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $payoutColor }} px-3 py-2">
                                    {{ str_replace('_', ' ', strtoupper($kyc->payout_status)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Razorpay Account ID:</strong></td>
                            <td>
                                @if($kyc->razorpay_subaccount_id)
                                    <span class="font-monospace">{{ $kyc->razorpay_subaccount_id }}</span>
                                    <button class="btn btn-sm btn-link p-0 ms-2" onclick="copyToClipboard('{{ $kyc->razorpay_subaccount_id }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                @else
                                    <span class="text-muted">Not created</span>
                                @endif
                            </td>
                        </tr>
                        @if(isset($kyc->verification_details['razorpay_account_status']))
                        <tr>
                            <td><strong>Razorpay Status:</strong></td>
                            <td>{{ $kyc->verification_details['razorpay_account_status'] }}</td>
                        </tr>
                        @endif
                        @if(isset($kyc->verification_details['razorpay_verified_at']))
                        <tr>
                            <td><strong>Verified At:</strong></td>
                            <td>{{ \Carbon\Carbon::parse($kyc->verification_details['razorpay_verified_at'])->format('M d, Y H:i A') }}</td>
                        </tr>
                        @endif
                        @if($kyc->payout_status === 'rejected' && isset($kyc->verification_details['razorpay_rejection_reason']))
                        <tr>
                            <td><strong>Rejection Reason:</strong></td>
                            <td class="text-danger">{{ $kyc->verification_details['razorpay_rejection_reason'] }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Trail -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-history"></i> Audit Trail</h6>
        </div>
        <div class="card-body">
            <div class="timeline">
                @if($kyc->created_at)
                <div class="timeline-item">
                    <div class="timeline-marker bg-secondary"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">KYC Created</h6>
                        <p class="text-muted small mb-0">{{ $kyc->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                </div>
                @endif

                @if($kyc->submitted_at)
                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">KYC Submitted</h6>
                        <p class="text-muted small mb-0">{{ $kyc->submitted_at->format('M d, Y H:i A') }}</p>
                        <p class="small mb-0">By: {{ $kyc->vendor->name }}</p>
                    </div>
                </div>
                @endif

                @if(isset($kyc->verification_details['razorpay_subaccount_created_at']))
                <div class="timeline-item">
                    <div class="timeline-marker bg-primary"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Razorpay Sub-Account Created</h6>
                        <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($kyc->verification_details['razorpay_subaccount_created_at'])->format('M d, Y H:i A') }}</p>
                        <p class="small mb-0">Account ID: {{ $kyc->razorpay_subaccount_id }}</p>
                    </div>
                </div>
                @endif

                @if($kyc->verification_status === 'approved' && $kyc->verified_at)
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">KYC Approved (Manual)</h6>
                        <p class="text-muted small mb-0">{{ $kyc->verified_at->format('M d, Y H:i A') }}</p>
                        <p class="small mb-0">By: {{ $kyc->verifier ? $kyc->verifier->name : 'N/A' }}</p>
                    </div>
                </div>
                @endif

                @if($kyc->payout_status === 'verified' && isset($kyc->verification_details['razorpay_verified_at']))
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Razorpay Account Verified (Webhook)</h6>
                        <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($kyc->verification_details['razorpay_verified_at'])->format('M d, Y H:i A') }}</p>
                        <p class="small mb-0">Vendor Status: kyc_verified</p>
                    </div>
                </div>
                @endif

                @if($kyc->payout_status === 'rejected' && isset($kyc->verification_details['razorpay_rejected_at']))
                <div class="timeline-item">
                    <div class="timeline-marker bg-danger"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Razorpay Account Rejected (Webhook)</h6>
                        <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($kyc->verification_details['razorpay_rejected_at'])->format('M d, Y H:i A') }}</p>
                        @if(isset($kyc->verification_details['razorpay_rejection_reason']))
                        <p class="small text-danger mb-0">Reason: {{ $kyc->verification_details['razorpay_rejection_reason'] }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($kyc->payout_status === 'failed' && isset($kyc->verification_details['razorpay_error_at']))
                <div class="timeline-item">
                    <div class="timeline-marker bg-secondary"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Sub-Account Creation Failed</h6>
                        <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($kyc->verification_details['razorpay_error_at'])->format('M d, Y H:i A') }}</p>
                        @if(isset($kyc->verification_details['razorpay_error']))
                        <p class="small text-danger mb-0">Error: {{ $kyc->verification_details['razorpay_error'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Manual Override Actions -->
    @if($kyc->payout_status === 'rejected' || $kyc->payout_status === 'failed')
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-user-shield"></i> Manual Override</h6>
        </div>
        <div class="card-body">
            <p class="mb-3">This KYC has issues with Razorpay verification. You can manually override the payout status:</p>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <button type="button" class="btn btn-success w-100" onclick="manualOverride('verified')">
                        <i class="fas fa-check-circle"></i> Mark as Verified
                    </button>
                    <small class="text-muted d-block mt-1">Use this if documents are verified manually</small>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-primary w-100" onclick="retryRazorpayCreation()">
                        <i class="fas fa-redo"></i> Retry Razorpay Sub-Account
                    </button>
                    <small class="text-muted d-block mt-1">Attempt to create Razorpay account again</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Vendor and Business Information -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Vendor Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Name:</strong></td>
                            <td>{{ $kyc->vendor->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $kyc->vendor->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Vendor Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $kyc->vendor->status === 'active' ? 'success' : 'warning' }}">
                                    {{ strtoupper($kyc->vendor->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-building"></i> Business Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Business Name:</strong></td>
                            <td>{{ $kyc->business_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Legal Name:</strong></td>
                            <td>{{ $kyc->legal_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Business Type:</strong></td>
                            <td>{{ strtoupper($kyc->business_type) }}</td>
                        </tr>
                        <tr>
                            <td><strong>PAN:</strong></td>
                            <td class="font-monospace">{{ $kyc->pan_number }}</td>
                        </tr>
                        <tr>
                            <td><strong>GST:</strong></td>
                            <td class="font-monospace">{{ $kyc->gst_number ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-marker {
    position: absolute;
    left: -26px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid #fff;
}
.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
}
</style>

<script>
const apiToken = '{{ session("api_token") ?? "" }}';

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}

function syncRazorpayStatus() {
    if (!confirm('Fetch the latest status from Razorpay?')) return;

    fetch(`/api/v1/admin/kyc/{{ $kyc->id }}/sync-razorpay`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${apiToken}`,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Razorpay status synced successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to sync'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to sync Razorpay status');
    });
}

function manualOverride(status) {
    const reason = prompt('Enter reason for manual override:');
    if (!reason || reason.trim().length < 10) {
        alert('Please provide a reason (minimum 10 characters)');
        return;
    }

    fetch(`/api/v1/admin/kyc/{{ $kyc->id }}/manual-override`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${apiToken}`,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            payout_status: status,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Manual override applied successfully!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to override'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to apply manual override');
    });
}

function retryRazorpayCreation() {
    if (!confirm('Retry creating Razorpay sub-account?')) return;

    fetch(`/api/v1/admin/kyc/{{ $kyc->id }}/retry-razorpay`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${apiToken}`,
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Razorpay sub-account creation job dispatched!');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to retry'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to retry Razorpay creation');
    });
}
</script>
@endsection
