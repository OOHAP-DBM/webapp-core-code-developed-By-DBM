@extends('layouts.app')

@section('title', 'Application Pending - Vendor Onboarding')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Pending Icon -->
                    <div class="mb-4">
                        <i class="fas fa-clock fa-5x text-warning"></i>
                    </div>

                    <h2 class="mb-3">Application Under Review</h2>
                    
                    <p class="text-muted mb-4">
                        Thank you for submitting your vendor application! Our team is currently reviewing your information.
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        This usually takes <strong>2-3 business days</strong>. We will notify you via email once your application is approved.
                    </div>

                    @if($profile->onboarding_completed_at)
                        <p class="text-muted small">
                            Submitted on: {{ $profile->onboarding_completed_at->format('M d, Y \a\t h:i A') }}
                        </p>
                    @endif

                    <!-- Progress Summary -->
                    <div class="mt-4 p-4 bg-light rounded">
                        <h5 class="mb-3">Submission Summary</h5>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i> Company Details</li>
                            <li><i class="fas fa-check text-success me-2"></i> Business Information</li>
                            <li><i class="fas fa-check text-success me-2"></i> KYC Documents</li>
                            <li><i class="fas fa-check text-success me-2"></i> Bank Details</li>
                            <li><i class="fas fa-check text-success me-2"></i> Terms Agreement</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('logout') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>

                    <p class="text-muted mt-4 small">
                        Need help? Contact us at <a href="mailto:support@oohapp.com">support@oohapp.com</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
