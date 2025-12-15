@extends('layouts.app')

@section('title', 'Company Details - Vendor Onboarding')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Progress Steps -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="text-center flex-fill">
                            <div class="step-circle active">1</div>
                            <small class="d-block mt-2">Company Details</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="text-center flex-fill">
                            <div class="step-circle">2</div>
                            <small class="d-block mt-2">Business Info</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="text-center flex-fill">
                            <div class="step-circle">3</div>
                            <small class="d-block mt-2">KYC Documents</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="text-center flex-fill">
                            <div class="step-circle">4</div>
                            <small class="d-block mt-2">Bank Details</small>
                        </div>
                        <div class="step-line"></div>
                        <div class="text-center flex-fill">
                            <div class="step-circle">5</div>
                            <small class="d-block mt-2">Terms</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-building me-2"></i>Step 1: Company Details</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('vendor.onboarding.company-details.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Company Name -->
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" name="company_name" 
                                       value="{{ old('company_name', $profile->company_name) }}" required>
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Company Type -->
                            <div class="col-md-6 mb-3">
                                <label for="company_type" class="form-label">Company Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('company_type') is-invalid @enderror" 
                                        id="company_type" name="company_type" required>
                                    <option value="">Select Type</option>
                                    <option value="proprietorship" {{ old('company_type', $profile->company_type) == 'proprietorship' ? 'selected' : '' }}>Proprietorship</option>
                                    <option value="partnership" {{ old('company_type', $profile->company_type) == 'partnership' ? 'selected' : '' }}>Partnership</option>
                                    <option value="private_limited" {{ old('company_type', $profile->company_type) == 'private_limited' ? 'selected' : '' }}>Private Limited</option>
                                    <option value="public_limited" {{ old('company_type', $profile->company_type) == 'public_limited' ? 'selected' : '' }}>Public Limited</option>
                                    <option value="llp" {{ old('company_type', $profile->company_type) == 'llp' ? 'selected' : '' }}>LLP</option>
                                    <option value="other" {{ old('company_type', $profile->company_type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('company_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Registration Number -->
                            <div class="col-md-6 mb-3">
                                <label for="company_registration_number" class="form-label">Registration Number</label>
                                <input type="text" class="form-control @error('company_registration_number') is-invalid @enderror" 
                                       id="company_registration_number" name="company_registration_number" 
                                       value="{{ old('company_registration_number', $profile->company_registration_number) }}">
                                @error('company_registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- GSTIN -->
                            <div class="col-md-6 mb-3">
                                <label for="gstin" class="form-label">GSTIN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('gstin') is-invalid @enderror" 
                                       id="gstin" name="gstin" maxlength="15"
                                       value="{{ old('gstin', $profile->gstin) }}" 
                                       placeholder="15 characters" required>
                                @error('gstin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- PAN -->
                            <div class="col-md-6 mb-3">
                                <label for="pan" class="form-label">PAN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('pan') is-invalid @enderror" 
                                       id="pan" name="pan" maxlength="10" style="text-transform: uppercase;"
                                       value="{{ old('pan', $profile->pan) }}" 
                                       placeholder="ABCDE1234F" required>
                                @error('pan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Website -->
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Website</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                       id="website" name="website" 
                                       value="{{ old('website', $profile->website) }}" 
                                       placeholder="https://example.com">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Registered Address -->
                            <div class="col-12 mb-3">
                                <label for="registered_address" class="form-label">Registered Address <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('registered_address') is-invalid @enderror" 
                                          id="registered_address" name="registered_address" rows="3" required>{{ old('registered_address', $profile->registered_address) }}</textarea>
                                @error('registered_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- City -->
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" 
                                       value="{{ old('city', $profile->city) }}" required>
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- State -->
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                       id="state" name="state" 
                                       value="{{ old('state', $profile->state) }}" required>
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Pincode -->
                            <div class="col-md-4 mb-3">
                                <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                       id="pincode" name="pincode" maxlength="6"
                                       value="{{ old('pincode', $profile->pincode) }}" 
                                       placeholder="6 digits" required>
                                @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('logout') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Next <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin: 0 auto;
    }
    
    .step-circle.active {
        background: #0d6efd;
        color: white;
    }
    
    .step-circle.completed {
        background: #198754;
        color: white;
    }
    
    .step-line {
        flex: 1;
        height: 2px;
        background: #e9ecef;
        margin: 20px 10px 0 10px;
    }
</style>
@endpush
@endsection
