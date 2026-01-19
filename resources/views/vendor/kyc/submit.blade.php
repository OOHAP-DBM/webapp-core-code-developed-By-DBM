@extends('layouts.vendor')

@section('title', 'KYC Verification')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-shield-check text-primary"></i>
                        KYC Verification
                    </h2>
                    <p class="text-muted mb-0">Complete your KYC to start receiving bookings and payouts</p>
                </div>
                @if($kyc && $kyc->verification_status)
                    <span class="badge {{ $kyc->status_badge_class }} px-3 py-2">
                        {{ $kyc->status_label }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if($kyc && in_array($kyc->verification_status, ['rejected', 'resubmission_required']))
        <div class="alert alert-warning mb-4">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle"></i> Action Required
            </h5>
            <p class="mb-2">{{ $kyc->verification_details['rejection_reason'] ?? $kyc->verification_details['resubmission_remarks'][0] ?? 'Please review and resubmit your KYC details.' }}</p>
            @if(isset($kyc->verification_details['resubmission_remarks']))
                <hr>
                <p class="mb-0 fw-semibold">Remarks:</p>
                <ul class="mb-0">
                    @foreach($kyc->verification_details['resubmission_remarks'] as $remark)
                        <li>{{ $remark }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if($kyc && $kyc->isApproved())
        <div class="alert alert-success mb-4">
            <h5 class="alert-heading">
                <i class="bi bi-check-circle"></i> KYC Approved!
            </h5>
            <p class="mb-0">Your KYC has been verified and approved. You can now receive bookings and payouts.</p>
        </div>
    @endif

    @if($kyc && $kyc->isUnderReview())
        <div class="alert alert-info mb-4">
            <h5 class="alert-heading">
                <i class="bi bi-hourglass-half"></i> Under Review
            </h5>
            <p class="mb-0">Your KYC is currently under review. You will be notified once it's verified.</p>
        </div>
    @endif

    <form id="kycForm" enctype="multipart/form-data">
        @csrf
        
        <!-- Business Information -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-building"></i> Business Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Business Type <span class="text-danger">*</span></label>
                        <select name="business_type" class="form-select" required {{ $kyc && $kyc->isApproved() ? 'disabled' : '' }}>
                            <option value="">Select Business Type</option>
                            <option value="individual" {{ old('business_type', $kyc->business_type ?? '') == 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="proprietorship" {{ old('business_type', $kyc->business_type ?? '') == 'proprietorship' ? 'selected' : '' }}>Sole Proprietorship</option>
                            <option value="partnership" {{ old('business_type', $kyc->business_type ?? '') == 'partnership' ? 'selected' : '' }}>Partnership</option>
                            <option value="pvt_ltd" {{ old('business_type', $kyc->business_type ?? '') == 'pvt_ltd' ? 'selected' : '' }}>Private Limited</option>
                            <option value="public_ltd" {{ old('business_type', $kyc->business_type ?? '') == 'public_ltd' ? 'selected' : '' }}>Public Limited</option>
                            <option value="llp" {{ old('business_type', $kyc->business_type ?? '') == 'llp' ? 'selected' : '' }}>LLP</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="business_name" class="form-control" placeholder="Enter business name" 
                               value="{{ old('business_name', $kyc->business_name ?? '') }}" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">PAN Number <span class="text-danger">*</span></label>
                        <input type="text" name="pan_number" class="form-control text-uppercase" placeholder="ABCDE1234F" 
                               maxlength="10" value="{{ old('pan_number', $kyc->pan_number ?? '') }}" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <small class="text-muted">Format: ABCDE1234F</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">GST Number <span class="text-muted">(Optional for non-GST businesses)</span></label>
                        <input type="text" name="gst_number" class="form-control text-uppercase" placeholder="22AAAAA0000A1Z5" 
                               maxlength="15" value="{{ old('gst_number', $kyc->gst_number ?? '') }}" {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <small class="text-muted">Format: 22AAAAA0000A1Z5</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label">Legal Name (As per PAN) <span class="text-danger">*</span></label>
                        <input type="text" name="legal_name" class="form-control" placeholder="Full legal entity name" 
                               value="{{ old('legal_name', $kyc->legal_name ?? '') }}" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person-lines-fill"></i> Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Contact Person Name <span class="text-danger">*</span></label>
                        <input type="text" name="contact_name" class="form-control" 
                               value="{{ old('contact_name', $kyc->contact_name ?? auth()->user()->name) }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                        <input type="email" name="contact_email" class="form-control" 
                               value="{{ old('contact_email', $kyc->contact_email ?? auth()->user()->email) }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                        <input type="tel" name="contact_phone" class="form-control" placeholder="+91XXXXXXXXXX" 
                               value="{{ old('contact_phone', $kyc->contact_phone ?? auth()->user()->phone) }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt-fill"></i> Business Address
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Complete Address <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Street, Building, Landmark" required>{{ old('address', $kyc->address ?? '') }}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $kyc->city ?? '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">State</label>
                        <input type="text" name="state" class="form-control" value="{{ old('state', $kyc->state ?? '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Pincode</label>
                        <input type="text" name="pincode" class="form-control" placeholder="XXXXXX" maxlength="6" value="{{ old('pincode', $kyc->pincode ?? '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-bank"></i> Bank Account Details (For Payouts)
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info small mb-3">
                    <i class="bi bi-info-circle"></i> Your bank details are encrypted and secure. This account will be used for vendor payouts.
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                        <input type="text" name="account_holder_name" class="form-control" 
                               value="{{ old('account_holder_name', $kyc->account_holder_name ?? '') }}" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Account Type <span class="text-danger">*</span></label>
                        <select name="account_type" class="form-select" required {{ $kyc && $kyc->isApproved() ? 'disabled' : '' }}>
                            <option value="">Select Account Type</option>
                            <option value="savings" {{ old('account_type', $kyc->account_type ?? '') == 'savings' ? 'selected' : '' }}>Savings</option>
                            <option value="current" {{ old('account_type', $kyc->account_type ?? 'current') == 'current' ? 'selected' : '' }}>Current</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number" class="form-control" placeholder="Enter account number" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Confirm Account Number <span class="text-danger">*</span></label>
                        <input type="text" name="account_number_confirmation" class="form-control" placeholder="Re-enter account number" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                        <input type="text" name="ifsc" class="form-control text-uppercase" placeholder="ABCD0123456" maxlength="11" 
                               value="{{ old('ifsc', $kyc->ifsc ?? '') }}" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <small class="text-muted">Format: ABCD0123456</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control" 
                               value="{{ old('bank_name', $kyc->bank_name ?? '') }}" required {{ $kyc && $kyc->isApproved() ? 'readonly' : '' }}>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Uploads -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-text"></i> Document Uploads
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning small mb-3">
                    <i class="bi bi-exclamation-triangle"></i> Upload clear, legible documents. Accepted formats: JPG, PNG, PDF (Max 5MB each)
                </div>
                
                <div class="row g-3">
                    <!-- PAN Card -->
                    <div class="col-md-6">
                        <label class="form-label">PAN Card <span class="text-danger">*</span></label>
                        <input type="file" name="pan_card" class="form-control" accept="image/*,application/pdf" {{ !$kyc || !$kyc->isApproved() ? 'required' : '' }}>
                        @if($kyc && $kyc->hasMedia('pan_card'))
                            <small class="text-success d-block mt-1">
                                <i class="bi bi-check-circle"></i> Uploaded
                                <a href="{{ $kyc->getFirstMediaUrl('pan_card') }}" target="_blank" class="ms-2">View</a>
                            </small>
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Aadhar Card -->
                    <div class="col-md-6">
                        <label class="form-label">Aadhar Card <span class="text-muted">(Optional)</span></label>
                        <input type="file" name="aadhar_card" class="form-control" accept="image/*,application/pdf">
                        @if($kyc && $kyc->hasMedia('aadhar_card'))
                            <small class="text-success d-block mt-1">
                                <i class="bi bi-check-circle"></i> Uploaded
                                <a href="{{ $kyc->getFirstMediaUrl('aadhar_card') }}" target="_blank" class="ms-2">View</a>
                            </small>
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- GST Certificate -->
                    <div class="col-md-6">
                        <label class="form-label">GST Certificate <span class="text-muted">(If applicable)</span></label>
                        <input type="file" name="gst_certificate" class="form-control" accept="image/*,application/pdf">
                        @if($kyc && $kyc->hasMedia('gst_certificate'))
                            <small class="text-success d-block mt-1">
                                <i class="bi bi-check-circle"></i> Uploaded
                                <a href="{{ $kyc->getFirstMediaUrl('gst_certificate') }}" target="_blank" class="ms-2">View</a>
                            </small>
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Business Proof -->
                    <div class="col-md-6">
                        <label class="form-label">Business Proof <span class="text-muted">(Optional)</span></label>
                        <input type="file" name="business_proof" class="form-control" accept="image/*,application/pdf">
                        <small class="text-muted">Registration certificate, incorporation doc, etc.</small>
                        @if($kyc && $kyc->hasMedia('business_proof'))
                            <small class="text-success d-block mt-1">
                                <i class="bi bi-check-circle"></i> Uploaded
                                <a href="{{ $kyc->getFirstMediaUrl('business_proof') }}" target="_blank" class="ms-2">View</a>
                            </small>
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <!-- Cancelled Cheque -->
                    <div class="col-md-6">
                        <label class="form-label">Cancelled Cheque <span class="text-danger">*</span></label>
                        <input type="file" name="cancelled_cheque" class="form-control" accept="image/*,application/pdf" {{ !$kyc || !$kyc->isApproved() ? 'required' : '' }}>
                        <small class="text-muted">For bank account verification</small>
                        @if($kyc && $kyc->hasMedia('cancelled_cheque'))
                            <small class="text-success d-block mt-1">
                                <i class="bi bi-check-circle"></i> Uploaded
                                <a href="{{ $kyc->getFirstMediaUrl('cancelled_cheque') }}" target="_blank" class="ms-2">View</a>
                            </small>
                        @endif
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        @if(!$kyc || !$kyc->isApproved())
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <div class="form-check d-inline-block mb-3">
                        <input class="form-check-input" type="checkbox" id="termsAgree" required>
                        <label class="form-check-label" for="termsAgree">
                            I declare that the information provided is accurate and complete. I understand that providing false information may lead to rejection.<br>
                            I agree to the <a href="{{ route('terms') }}" target="_blank">Terms & Conditions</a> and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>.
                        </label>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                            <i class="bi bi-send"></i> Submit KYC for Verification
                        </button>
                    </div>
                    
                    <p class="text-muted small mt-3 mb-0">
                        <i class="bi bi-clock-history"></i> Your KYC will be reviewed within 24-48 hours
                    </p>
                </div>
            </div>
        @endif
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kycForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        
        // Disable submit button
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('/api/v1/vendors/kyc', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok) {
                alert(data.message);
                window.location.reload();
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = data.errors[field][0];
                            }
                        }
                    });
                }
                alert(data.message || 'Failed to submit KYC. Please check the form and try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitSpinner.classList.add('d-none');
        }
    });
    
    // PAN validation
    const panInput = form.querySelector('[name="pan_number"]');
    if (panInput) {
        panInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // GST validation
    const gstInput = form.querySelector('[name="gst_number"]');
    if (gstInput) {
        gstInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // IFSC validation
    const ifscInput = form.querySelector('[name="ifsc"]');
    if (ifscInput) {
        ifscInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }
    
    // Account number confirmation
    const accountNumber = form.querySelector('[name="account_number"]');
    const accountConfirm = form.querySelector('[name="account_number_confirmation"]');
    
    if (accountConfirm) {
        accountConfirm.addEventListener('blur', function() {
            if (this.value !== accountNumber.value) {
                this.classList.add('is-invalid');
                const feedback = this.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.textContent = 'Account numbers do not match';
                }
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
});
</script>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.form-label {
    font-weight: 500;
}

.text-uppercase {
    text-transform: uppercase;
}

.invalid-feedback {
    display: block;
}
</style>
@endsection
