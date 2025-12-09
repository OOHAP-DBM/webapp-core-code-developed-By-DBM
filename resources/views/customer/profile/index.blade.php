@extends('layouts.customer')

@section('title', 'My Profile - OOHAPP')

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 32px;
        color: white;
        margin-bottom: 32px;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: #667eea;
        margin-bottom: 16px;
    }
    
    .profile-tabs {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .nav-pills .nav-link {
        border-radius: 12px;
        padding: 12px 24px;
        color: #64748b;
        font-weight: 500;
    }
    
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .kyc-status-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        text-align: center;
    }
    
    .kyc-status-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 32px;
    }
    
    .kyc-status-pending .kyc-status-icon {
        background: #fff7ed;
        color: #f59e0b;
    }
    
    .kyc-status-approved .kyc-status-icon {
        background: #f0fdf4;
        color: #10b981;
    }
    
    .kyc-status-rejected .kyc-status-icon {
        background: #fef2f2;
        color: #ef4444;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="profile-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h2>{{ auth()->user()->name }}</h2>
                <p class="mb-2">{{ auth()->user()->email }}</p>
                <p class="mb-0">{{ auth()->user()->phone }}</p>
            </div>
            <div class="col-md-4 text-end mt-3 mt-md-0">
                <div class="badge bg-white text-primary px-3 py-2">
                    <i class="bi bi-shield-check me-1"></i>
                    {{ ucfirst(auth()->user()->status) }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="profile-tabs">
                <ul class="nav nav-pills flex-column" role="tablist">
                    <li class="nav-item mb-2">
                        <a class="nav-link active" data-bs-toggle="pill" href="#personal-info">
                            <i class="bi bi-person me-2"></i>Personal Information
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" data-bs-toggle="pill" href="#kyc">
                            <i class="bi bi-shield-check me-2"></i>KYC Verification
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" data-bs-toggle="pill" href="#security">
                            <i class="bi bi-lock me-2"></i>Security
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
                
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="tab-content">
                <!-- Personal Information -->
                <div class="tab-pane fade show active" id="personal-info">
                    <div class="profile-tabs">
                        <h4 class="mb-4">Personal Information</h4>

                        <form action="{{ route('customer.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()->name) }}" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', auth()->user()->phone) }}" required>
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Company Name (Optional)</label>
                                    <input type="text" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->company_name) }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3">{{ old('address', auth()->user()->address) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" value="{{ old('city', auth()->user()->city) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-control" value="{{ old('state', auth()->user()->state) }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">PIN Code</label>
                                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', auth()->user()->pincode) }}">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- KYC Verification -->
                <div class="tab-pane fade" id="kyc">
                    <div class="profile-tabs">
                        <h4 class="mb-4">KYC Verification</h4>

                        @php
                        $kycStatus = auth()->user()->kyc_status ?? 'not_submitted';
                        @endphp

                        @if($kycStatus === 'approved')
                        <div class="kyc-status-card kyc-status-approved">
                            <div class="kyc-status-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <h5>KYC Verified</h5>
                            <p class="text-muted mb-0">Your KYC has been approved</p>
                        </div>
                        @elseif($kycStatus === 'pending')
                        <div class="kyc-status-card kyc-status-pending">
                            <div class="kyc-status-icon">
                                <i class="bi bi-clock"></i>
                            </div>
                            <h5>KYC Under Review</h5>
                            <p class="text-muted mb-0">Your documents are being verified</p>
                        </div>
                        @elseif($kycStatus === 'rejected')
                        <div class="kyc-status-card kyc-status-rejected">
                            <div class="kyc-status-icon">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                            <h5>KYC Rejected</h5>
                            <p class="text-muted mb-3">{{ auth()->user()->kyc_rejection_reason ?? 'Please resubmit your documents' }}</p>
                            <a href="{{ route('customer.kyc.create') }}" class="btn btn-primary">Resubmit KYC</a>
                        </div>
                        @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Complete your KYC verification to unlock all features
                        </div>

                        <a href="{{ route('customer.kyc.create') }}" class="btn btn-primary">
                            <i class="bi bi-shield-check me-2"></i>Start KYC Verification
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Security -->
                <div class="tab-pane fade" id="security">
                    <div class="profile-tabs">
                        <h4 class="mb-4">Change Password</h4>

                        <form action="{{ route('customer.profile.change-password') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-lock me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
