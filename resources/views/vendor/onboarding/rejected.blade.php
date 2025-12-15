@extends('layouts.app')

@section('title', 'Application Rejected - Vendor Onboarding')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Rejected Icon -->
                    <div class="mb-4">
                        <i class="fas fa-times-circle fa-5x text-danger"></i>
                    </div>

                    <h2 class="mb-3">Application Rejected</h2>
                    
                    <p class="text-muted mb-4">
                        Unfortunately, we are unable to approve your vendor application at this time.
                    </p>

                    @if($profile->rejection_reason)
                        <div class="alert alert-danger">
                            <h6 class="mb-2"><strong>Reason:</strong></h6>
                            <p class="mb-0">{{ $profile->rejection_reason }}</p>
                        </div>
                    @endif

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Please contact our support team if you have any questions or wish to reapply.
                    </div>

                    <div class="mt-4">
                        <a href="mailto:support@oohapp.com" class="btn btn-primary me-2">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                        <a href="{{ route('logout') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
