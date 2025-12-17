@extends('layouts.app')

@section('title', 'Company Details - Vendor Onboarding')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-9">

            <!-- Stepper -->
            <div class="onboarding-steps mb-4">
                <div class="step active">
                    <span>1</span>
                    <small>Company</small>
                </div>
                <div class="step">
                    <span>2</span>
                    <small>Business</small>
                </div>
                <div class="step">
                    <span>3</span>
                    <small>KYC</small>
                </div>
                <div class="step">
                    <span>4</span>
                    <small>Bank</small>
                </div>
                <div class="step">
                    <span>5</span>
                    <small>Terms</small>
                </div>
            </div>

            <!-- Card -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 px-4 pt-4">
                    <h4 class="fw-semibold mb-1">
                        <i class="fas fa-building text-primary me-2"></i>
                        Company Details
                    </h4>
                    <p class="text-muted mb-0">
                        Provide official company information for verification
                    </p>
                </div>

                <div class="card-body px-4 pb-4">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('vendor.onboarding.company-details.store') }}" method="POST">
                        @csrf

                        <!-- Company Info -->
                        <h6 class="section-title">Company Information</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('company_name') is-invalid @enderror"
                                       name="company_name"
                                       value="{{ old('company_name', $profile->company_name) }}"
                                       required>
                                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('company_type') is-invalid @enderror"
                                        name="company_type" required>
                                    <option value="">Select company type</option>
                                    @foreach([
                                        'proprietorship' => 'Proprietorship',
                                        'partnership' => 'Partnership',
                                        'private_limited' => 'Private Limited',
                                        'public_limited' => 'Public Limited',
                                        'llp' => 'LLP',
                                        'other' => 'Other'
                                    ] as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ old('company_type', $profile->company_type) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Legal Info -->
                        <h6 class="section-title mt-4">Legal & Tax Details</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Registration Number</label>
                                <input type="text"
                                       class="form-control @error('company_registration_number') is-invalid @enderror"
                                       name="company_registration_number"
                                       value="{{ old('company_registration_number', $profile->company_registration_number) }}">
                                @error('company_registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">GSTIN <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control text-uppercase @error('gstin') is-invalid @enderror"
                                       name="gstin"
                                       maxlength="15"
                                       value="{{ old('gstin', $profile->gstin) }}"
                                       placeholder="15 character GSTIN"
                                       required>
                                <small class="text-muted">Example: 22AAAAA0000A1Z5</small>
                                @error('gstin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">PAN <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control text-uppercase @error('pan') is-invalid @enderror"
                                       name="pan"
                                       maxlength="10"
                                       placeholder="ABCDE1234F"
                                       value="{{ old('pan', $profile->pan) }}"
                                       required>
                                @error('pan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="url"
                                       class="form-control @error('website') is-invalid @enderror"
                                       name="website"
                                       placeholder="https://example.com"
                                       value="{{ old('website', $profile->website) }}">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <!-- Address -->
                        <h6 class="section-title mt-4">Registered Address</h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <textarea class="form-control @error('registered_address') is-invalid @enderror"
                                          name="registered_address"
                                          rows="3"
                                          placeholder="Full registered address"
                                          required>{{ old('registered_address', $profile->registered_address) }}</textarea>
                                @error('registered_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control" name="city"
                                       placeholder="City"
                                       value="{{ old('city', $profile->city) }}" required>
                            </div>

                            <div class="col-md-4">
                                <input type="text" class="form-control" name="state"
                                       placeholder="State"
                                       value="{{ old('state', $profile->state) }}" required>
                            </div>

                            <div class="col-md-4">
                                <input type="text"
                                       class="form-control"
                                       name="pincode"
                                       maxlength="6"
                                       placeholder="Pincode"
                                       value="{{ old('pincode', $profile->pincode) }}"
                                       required>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('logout') }}" class="btn btn-outline-secondary px-4">
                                Logout
                            </a>

                            <button type="submit" class="btn btn-primary px-5">
                                Save & Continue
                                <i class="fas fa-arrow-right ms-2"></i>
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
.onboarding-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 1.5rem;
}
.onboarding-steps::before {
    content: '';
    position: absolute;
    top: 18px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}
.onboarding-steps .step {
    text-align: center;
    z-index: 1;
}
.onboarding-steps .step span {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #e9ecef;
    font-weight: 600;
}
.onboarding-steps .step.active span {
    background: #0d6efd;
    color: #fff;
}
.onboarding-steps small {
    display: block;
    margin-top: 6px;
    font-size: 12px;
}
.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: .75rem;
}
</style>
@endpush
@endsection
