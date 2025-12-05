@extends('layouts.admin')

@section('title', 'KYC Details - ' . $kyc->business_name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.kyc.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <h2 class="mb-1">
                        <i class="bi bi-shield-check text-primary"></i>
                        KYC Details - {{ $kyc->business_name }}
                    </h2>
                    <p class="text-muted mb-0">ID: #{{ $kyc->id }}</p>
                </div>
                <div class="text-end">
                    <span class="badge {{ $kyc->status_badge_class }} fs-5 px-3 py-2">
                        {{ $kyc->status_label }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Alerts -->
    @if($kyc->verification_status === 'rejected' && isset($kyc->verification_details['rejection_reason']))
        <div class="alert alert-danger mb-4">
            <h5 class="alert-heading">
                <i class="bi bi-x-circle"></i> Rejection Reason
            </h5>
            <p class="mb-0">{{ $kyc->verification_details['rejection_reason'] }}</p>
            <hr>
            <small class="text-muted">Rejected on {{ $kyc->verified_at->format('d M Y, h:i A') }} by {{ $kyc->verifier->name }}</small>
        </div>
    @endif

    @if($kyc->verification_status === 'resubmission_required' && isset($kyc->verification_details['resubmission_remarks']))
        <div class="alert alert-warning mb-4">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle"></i> Resubmission Requested
            </h5>
            <p class="mb-2"><strong>Remarks:</strong></p>
            <ul class="mb-0">
                @foreach($kyc->verification_details['resubmission_remarks'] as $remark)
                    <li>{{ $remark }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Vendor Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-circle"></i> Vendor Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Vendor Name</label>
                            <div class="fw-bold">{{ $kyc->vendor->name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email</label>
                            <div class="fw-bold">{{ $kyc->vendor->email }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Phone</label>
                            <div class="fw-bold">{{ $kyc->vendor->phone }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Registered On</label>
                            <div class="fw-bold">{{ $kyc->vendor->created_at->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building"></i> Business Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Business Type</label>
                            <div class="fw-bold text-uppercase">{{ str_replace('_', ' ', $kyc->business_type) }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Business Name</label>
                            <div class="fw-bold">{{ $kyc->business_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Legal Name</label>
                            <div class="fw-bold">{{ $kyc->legal_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">PAN Number</label>
                            <div class="fw-bold">{{ $kyc->pan_number }}</div>
                        </div>
                        @if($kyc->gst_number)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">GST Number</label>
                                <div class="fw-bold">{{ $kyc->gst_number }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-lines-fill"></i> Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Contact Person</label>
                            <div class="fw-bold">{{ $kyc->contact_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Contact Email</label>
                            <div class="fw-bold">{{ $kyc->contact_email }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Contact Phone</label>
                            <div class="fw-bold">{{ $kyc->contact_phone }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-geo-alt-fill"></i> Business Address
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Complete Address</label>
                            <div class="fw-bold">{{ $kyc->address }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">City</label>
                            <div class="fw-bold">{{ $kyc->city ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">State</label>
                            <div class="fw-bold">{{ $kyc->state ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Pincode</label>
                            <div class="fw-bold">{{ $kyc->pincode ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bank"></i> Bank Account Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-shield-lock-fill"></i> Account number is encrypted and masked for security
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Account Holder Name</label>
                            <div class="fw-bold">{{ $kyc->account_holder_name }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Account Type</label>
                            <div class="fw-bold text-uppercase">{{ $kyc->account_type }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Account Number (Masked)</label>
                            <div class="fw-bold">{{ $kyc->masked_account_number }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">IFSC Code</label>
                            <div class="fw-bold">{{ $kyc->ifsc }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Bank Name</label>
                            <div class="fw-bold">{{ $kyc->bank_name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Documents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $documents = [
                                'pan_card' => ['label' => 'PAN Card', 'required' => true],
                                'aadhar_card' => ['label' => 'Aadhar Card', 'required' => false],
                                'gst_certificate' => ['label' => 'GST Certificate', 'required' => false],
                                'business_proof' => ['label' => 'Business Proof', 'required' => false],
                                'cancelled_cheque' => ['label' => 'Cancelled Cheque', 'required' => true],
                            ];
                        @endphp

                        @foreach($documents as $key => $doc)
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="mb-2">
                                            {{ $doc['label'] }}
                                            @if($doc['required'])
                                                <span class="badge bg-danger">Required</span>
                                            @endif
                                        </h6>
                                        @if($kyc->hasMedia($key))
                                            <a href="{{ $kyc->getFirstMediaUrl($key) }}" target="_blank" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> View Document
                                            </a>
                                            <span class="badge bg-success ms-2">
                                                <i class="bi bi-check-circle"></i> Uploaded
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Not Uploaded</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Actions -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item mb-3">
                            <div class="timeline-badge bg-primary">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <strong>KYC Created</strong>
                                <br>
                                <small class="text-muted">{{ $kyc->created_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>

                        @if($kyc->submitted_at)
                            <div class="timeline-item mb-3">
                                <div class="timeline-badge bg-info">
                                    <i class="bi bi-send"></i>
                                </div>
                                <div class="timeline-content">
                                    <strong>Submitted for Review</strong>
                                    <br>
                                    <small class="text-muted">{{ $kyc->submitted_at->format('d M Y, h:i A') }}</small>
                                </div>
                            </div>
                        @endif

                        @if($kyc->verified_at)
                            <div class="timeline-item mb-3">
                                <div class="timeline-badge {{ $kyc->isApproved() ? 'bg-success' : 'bg-danger' }}">
                                    <i class="bi {{ $kyc->isApproved() ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <strong>{{ $kyc->status_label }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $kyc->verified_at->format('d M Y, h:i A') }}
                                        @if($kyc->verifier)
                                            <br>by {{ $kyc->verifier->name }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                        @endif

                        @if($kyc->razorpay_subaccount_id)
                            <div class="timeline-item mb-3">
                                <div class="timeline-badge bg-success">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <div class="timeline-content">
                                    <strong>Razorpay Sub-account Created</strong>
                                    <br>
                                    <small class="text-muted">{{ $kyc->razorpay_subaccount_id }}</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            @if(!$kyc->isApproved())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-gear"></i> Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="approveKYC()">
                                <i class="bi bi-check-circle"></i> Approve KYC
                            </button>
                            
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resubmissionModal">
                                <i class="bi bi-arrow-clockwise"></i> Request Resubmission
                            </button>
                            
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle"></i> Reject KYC
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Additional Info -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Additional Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Last Updated</small>
                        <div class="fw-bold">{{ $kyc->updated_at->format('d M Y, h:i A') }}</div>
                    </div>
                    @if($kyc->razorpay_subaccount_id)
                        <div class="mb-2">
                            <small class="text-muted">Razorpay Sub-account</small>
                            <div class="fw-bold small">{{ $kyc->razorpay_subaccount_id }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-x-circle"></i> Reject KYC
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> This will reject the vendor's KYC and they will need to submit a new application.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" placeholder="Explain why the KYC is being rejected..." required></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <span class="spinner-border spinner-border-sm d-none" id="rejectSpinner"></span>
                        Reject KYC
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resubmission Modal -->
<div class="modal fade" id="resubmissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-clockwise"></i> Request Resubmission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resubmissionForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> The vendor will be asked to correct the issues you specify and resubmit.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <div id="remarksContainer">
                            <div class="input-group mb-2">
                                <input type="text" name="remarks[]" class="form-control" placeholder="e.g., GST certificate is not clear" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="removeRemark(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRemark()">
                            <i class="bi bi-plus-circle"></i> Add Another Remark
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <span class="spinner-border spinner-border-sm d-none" id="resubmissionSpinner"></span>
                        Request Resubmission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const kycId = {{ $kyc->id }};

// Approve KYC
async function approveKYC() {
    if (!confirm('Are you sure you want to approve this KYC? This will activate the vendor account.')) {
        return;
    }

    try {
        const response = await fetch(`/api/v1/admin/kyc/${kycId}/approve`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Failed to approve KYC');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Reject KYC
document.getElementById('rejectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const spinner = document.getElementById('rejectSpinner');
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');

    const formData = new FormData(this);
    const data = {
        reason: formData.get('reason')
    };

    try {
        const response = await fetch(`/api/v1/admin/kyc/${kycId}/reject`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            window.location.reload();
        } else {
            alert(result.message || 'Failed to reject KYC');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    }
});

// Request Resubmission
document.getElementById('resubmissionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const spinner = document.getElementById('resubmissionSpinner');
    submitBtn.disabled = true;
    spinner.classList.remove('d-none');

    const formData = new FormData(this);
    const remarks = formData.getAll('remarks[]').filter(r => r.trim());

    if (remarks.length === 0) {
        alert('Please add at least one remark');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
        return;
    }

    try {
        const response = await fetch(`/api/v1/admin/kyc/${kycId}/request-resubmission`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ remarks })
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.message);
            window.location.reload();
        } else {
            alert(result.message || 'Failed to request resubmission');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    }
});

// Add remark field
function addRemark() {
    const container = document.getElementById('remarksContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="remarks[]" class="form-control" placeholder="Enter another issue" required>
        <button type="button" class="btn btn-outline-secondary" onclick="removeRemark(this)">
            <i class="bi bi-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

// Remove remark field
function removeRemark(btn) {
    const container = document.getElementById('remarksContainer');
    if (container.children.length > 1) {
        btn.parentElement.remove();
    } else {
        alert('At least one remark is required');
    }
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -23px;
    top: 30px;
    width: 2px;
    height: calc(100% + 10px);
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-badge {
    position: absolute;
    left: -30px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.timeline-content {
    padding-left: 10px;
}
</style>
@endsection
