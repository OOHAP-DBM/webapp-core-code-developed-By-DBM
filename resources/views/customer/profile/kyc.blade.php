@extends('layouts.customer')

@section('title', 'KYC Verification')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('customer.profile.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-2"></i>Back to Profile
                </a>
            </div>

            <!-- KYC Status Alert -->
            @if(isset($kyc))
                @if($kyc->status === 'approved')
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-3" style="font-size: 2rem;"></i>
                            <div>
                                <h5 class="mb-1">KYC Verified</h5>
                                <p class="mb-0">Your KYC has been successfully verified on {{ \Carbon\Carbon::parse($kyc->verified_at)->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                @elseif($kyc->status === 'pending')
                    <div class="alert alert-warning border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hourglass-split me-3" style="font-size: 2rem;"></i>
                            <div>
                                <h5 class="mb-1">KYC Under Review</h5>
                                <p class="mb-0">Your KYC documents are being verified. This usually takes 24-48 hours.</p>
                            </div>
                        </div>
                    </div>
                @elseif($kyc->status === 'rejected')
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-x-circle-fill me-3" style="font-size: 2rem;"></i>
                            <div>
                                <h5 class="mb-1">KYC Rejected</h5>
                                <p class="mb-1">{{ $kyc->rejection_reason ?? 'Your KYC submission was rejected. Please resubmit with correct documents.' }}</p>
                                <button class="btn btn-sm btn-danger" onclick="document.getElementById('kycForm').scrollIntoView({ behavior: 'smooth' })">
                                    Resubmit Documents
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- KYC Form -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="mb-4">KYC Verification Form</h3>
                    
                    <form id="kycForm" action="{{ route('customer.kyc.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Personal Information -->
                        <h5 class="mb-3 pb-2 border-bottom">Personal Information</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="full_name" 
                                       name="full_name" 
                                       value="{{ old('full_name', auth()->user()->name) }}" 
                                       required>
                                @error('full_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date_of_birth" 
                                       name="date_of_birth" 
                                       value="{{ old('date_of_birth', $kyc->date_of_birth ?? '') }}" 
                                       max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                                       required>
                                @error('date_of_birth')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $kyc->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $kyc->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $kyc->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', auth()->user()->phone) }}" 
                                       required>
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Information -->
                        <h5 class="mb-3 pb-2 border-bottom">Address Information</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="address" class="form-label">Street Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" 
                                          id="address" 
                                          name="address" 
                                          rows="2" 
                                          required>{{ old('address', $kyc->address ?? auth()->user()->address) }}</textarea>
                                @error('address')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="city" 
                                       name="city" 
                                       value="{{ old('city', $kyc->city ?? auth()->user()->city) }}" 
                                       required>
                                @error('city')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="state" 
                                       name="state" 
                                       value="{{ old('state', $kyc->state ?? auth()->user()->state) }}" 
                                       required>
                                @error('state')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="pincode" 
                                       name="pincode" 
                                       value="{{ old('pincode', $kyc->pincode ?? auth()->user()->pincode) }}" 
                                       pattern="[0-9]{6}" 
                                       required>
                                @error('pincode')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Document Upload -->
                        <h5 class="mb-3 pb-2 border-bottom">Document Upload</h5>
                        
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Accepted Documents:</strong> Aadhaar Card, PAN Card, Driving License, Passport, Voter ID
                            <br><small>Upload clear images (JPG, PNG, PDF). Maximum 2MB per file.</small>
                        </div>

                        <div class="row g-3 mb-4">
                            <!-- Document Type Selection -->
                            <div class="col-md-6">
                                <label for="document_type" class="form-label">Document Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="document_type" name="document_type" required>
                                    <option value="">Select Document Type</option>
                                    <option value="aadhaar" {{ old('document_type', $kyc->document_type ?? '') === 'aadhaar' ? 'selected' : '' }}>Aadhaar Card</option>
                                    <option value="pan" {{ old('document_type', $kyc->document_type ?? '') === 'pan' ? 'selected' : '' }}>PAN Card</option>
                                    <option value="driving_license" {{ old('document_type', $kyc->document_type ?? '') === 'driving_license' ? 'selected' : '' }}>Driving License</option>
                                    <option value="passport" {{ old('document_type', $kyc->document_type ?? '') === 'passport' ? 'selected' : '' }}>Passport</option>
                                    <option value="voter_id" {{ old('document_type', $kyc->document_type ?? '') === 'voter_id' ? 'selected' : '' }}>Voter ID</option>
                                </select>
                                @error('document_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="document_number" class="form-label">Document Number <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control" 
                                       id="document_number" 
                                       name="document_number" 
                                       value="{{ old('document_number', $kyc->document_number ?? '') }}" 
                                       required>
                                @error('document_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Document Front -->
                            <div class="col-md-6">
                                <label for="document_front" class="form-label">
                                    Document Front <span class="text-danger">*</span>
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="document_front" 
                                       name="document_front" 
                                       accept="image/*,.pdf" 
                                       {{ isset($kyc) && $kyc->document_front ? '' : 'required' }}
                                       onchange="previewFile(this, 'frontPreview')">
                                @error('document_front')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @if(isset($kyc) && $kyc->document_front)
                                    <div class="mt-2">
                                        <small class="text-muted">Current file: <a href="{{ asset('storage/' . $kyc->document_front) }}" target="_blank">View</a></small>
                                    </div>
                                @endif
                                <div id="frontPreview" class="mt-2"></div>
                            </div>
                            
                            <!-- Document Back -->
                            <div class="col-md-6">
                                <label for="document_back" class="form-label">
                                    Document Back
                                </label>
                                <input type="file" 
                                       class="form-control" 
                                       id="document_back" 
                                       name="document_back" 
                                       accept="image/*,.pdf"
                                       onchange="previewFile(this, 'backPreview')">
                                <small class="text-muted">Optional for single-sided documents</small>
                                @error('document_back')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @if(isset($kyc) && $kyc->document_back)
                                    <div class="mt-2">
                                        <small class="text-muted">Current file: <a href="{{ asset('storage/' . $kyc->document_back) }}" target="_blank">View</a></small>
                                    </div>
                                @endif
                                <div id="backPreview" class="mt-2"></div>
                            </div>
                        </div>

                        <!-- Company Information (Optional) -->
                        <h5 class="mb-3 pb-2 border-bottom">Company Information (Optional)</h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="company_name" 
                                       name="company_name" 
                                       value="{{ old('company_name', auth()->user()->company) }}">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="gst_number" class="form-label">GST Number</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="gst_number" 
                                       name="gst_number" 
                                       value="{{ old('gst_number', $kyc->gst_number ?? '') }}" 
                                       pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}">
                                <small class="text-muted">Format: 22AAAAA0000A1Z5</small>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I hereby declare that the information provided is true and correct to the best of my knowledge. 
                                I understand that providing false information may lead to account suspension.
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" {{ isset($kyc) && $kyc->status === 'pending' ? 'disabled' : '' }}>
                                <i class="bi bi-send me-2"></i>
                                {{ isset($kyc) && $kyc->status === 'pending' ? 'Verification Pending' : 'Submit for Verification' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body">
                    <h5 class="mb-3">
                        <i class="bi bi-question-circle text-primary me-2"></i>Need Help?
                    </h5>
                    <ul class="mb-0">
                        <li class="mb-2">KYC verification typically takes 24-48 hours</li>
                        <li class="mb-2">Ensure all uploaded documents are clear and legible</li>
                        <li class="mb-2">Document information must match your profile details</li>
                        <li class="mb-2">Contact support if your verification is rejected: <a href="mailto:support@oohapp.com">support@oohapp.com</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            preview.innerHTML = '';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (file.type.startsWith('image/')) {
                preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">`;
            } else {
                preview.innerHTML = `<span class="badge bg-success"><i class="bi bi-file-pdf me-1"></i>${file.name}</span>`;
            }
        };
        
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}
</script>
@endsection
