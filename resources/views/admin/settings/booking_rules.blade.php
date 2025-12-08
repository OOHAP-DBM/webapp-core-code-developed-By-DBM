@extends('layouts.admin')

@section('title', 'Booking Rules Configuration')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Booking Rules Configuration
                    </h4>
                    <div>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Settings
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Validation Errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.booking-rules.update') }}" method="POST" id="bookingRulesForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <!-- Booking Hold Time -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <label for="booking_hold_minutes" class="form-label fw-bold">
                                            <i class="fas fa-clock text-primary me-2"></i>Booking Hold Time
                                        </label>
                                        <input 
                                            type="number" 
                                            class="form-control @error('booking_hold_minutes') is-invalid @enderror" 
                                            id="booking_hold_minutes" 
                                            name="booking_hold_minutes" 
                                            value="{{ old('booking_hold_minutes', $bookingRules['booking_hold_minutes'] ?? 30) }}"
                                            min="1"
                                            max="1440"
                                            required
                                        >
                                        <small class="form-text text-muted">
                                            Minutes to hold a booking before payment is required (1-1440)
                                        </small>
                                        @error('booking_hold_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Grace Period -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <label for="grace_period_minutes" class="form-label fw-bold">
                                            <i class="fas fa-hourglass-half text-warning me-2"></i>Grace Period
                                        </label>
                                        <input 
                                            type="number" 
                                            class="form-control @error('grace_period_minutes') is-invalid @enderror" 
                                            id="grace_period_minutes" 
                                            name="grace_period_minutes" 
                                            value="{{ old('grace_period_minutes', $bookingRules['grace_period_minutes'] ?? 15) }}"
                                            min="0"
                                            max="1440"
                                            required
                                        >
                                        <small class="form-text text-muted">
                                            Grace period before booking start time for cancellation (0-1440 minutes)
                                        </small>
                                        @error('grace_period_minutes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Max Future Booking -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <label for="max_future_booking_start_months" class="form-label fw-bold">
                                            <i class="fas fa-calendar-alt text-info me-2"></i>Max Future Booking
                                        </label>
                                        <input 
                                            type="number" 
                                            class="form-control @error('max_future_booking_start_months') is-invalid @enderror" 
                                            id="max_future_booking_start_months" 
                                            name="max_future_booking_start_months" 
                                            value="{{ old('max_future_booking_start_months', $bookingRules['max_future_booking_start_months'] ?? 12) }}"
                                            min="1"
                                            max="24"
                                            required
                                        >
                                        <small class="form-text text-muted">
                                            Maximum months in future a booking can start (1-24 months)
                                        </small>
                                        @error('max_future_booking_start_months')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Min Booking Duration -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <label for="booking_min_duration_days" class="form-label fw-bold">
                                            <i class="fas fa-calendar-check text-success me-2"></i>Minimum Duration
                                        </label>
                                        <input 
                                            type="number" 
                                            class="form-control @error('booking_min_duration_days') is-invalid @enderror" 
                                            id="booking_min_duration_days" 
                                            name="booking_min_duration_days" 
                                            value="{{ old('booking_min_duration_days', $bookingRules['booking_min_duration_days'] ?? 7) }}"
                                            min="1"
                                            max="365"
                                            required
                                        >
                                        <small class="form-text text-muted">
                                            Minimum booking duration in days (1-365 days)
                                        </small>
                                        @error('booking_min_duration_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Max Booking Duration -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <label for="booking_max_duration_months" class="form-label fw-bold">
                                            <i class="fas fa-calendar-times text-danger me-2"></i>Maximum Duration
                                        </label>
                                        <input 
                                            type="number" 
                                            class="form-control @error('booking_max_duration_months') is-invalid @enderror" 
                                            id="booking_max_duration_months" 
                                            name="booking_max_duration_months" 
                                            value="{{ old('booking_max_duration_months', $bookingRules['booking_max_duration_months'] ?? 12) }}"
                                            min="1"
                                            max="36"
                                            required
                                        >
                                        <small class="form-text text-muted">
                                            Maximum booking duration in months (1-36 months)
                                        </small>
                                        @error('booking_max_duration_months')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Allow Weekly Booking -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <label for="allow_weekly_booking" class="form-label fw-bold">
                                            <i class="fas fa-toggle-on text-secondary me-2"></i>Weekly Booking Option
                                        </label>
                                        <div class="form-check form-switch">
                                            <input 
                                                class="form-check-input @error('allow_weekly_booking') is-invalid @enderror" 
                                                type="checkbox" 
                                                id="allow_weekly_booking" 
                                                name="allow_weekly_booking"
                                                value="1"
                                                {{ old('allow_weekly_booking', $bookingRules['allow_weekly_booking'] ?? false) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="allow_weekly_booking">
                                                Enable weekly booking option for customers
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Allow customers to book on a weekly basis
                                        </small>
                                        @error('allow_weekly_booking')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Save Booking Rules
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-2"></i>Reset Changes
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Configuration Guidelines</h6>
                        <ul class="mb-0">
                            <li><strong>Booking Hold Time:</strong> Time window for customers to complete payment before booking expires</li>
                            <li><strong>Grace Period:</strong> Buffer time before booking start for cancellations without penalties</li>
                            <li><strong>Max Future Booking:</strong> Limit how far in advance bookings can be made</li>
                            <li><strong>Duration Limits:</strong> Ensure min/max values align with your business model</li>
                            <li><strong>Weekly Booking:</strong> Enable if you offer weekly recurring bookings</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetForm() {
    if (confirm('Are you sure you want to reset all changes? This will reload the form with saved values.')) {
        document.getElementById('bookingRulesForm').reset();
        location.reload();
    }
}

// Form validation
document.getElementById('bookingRulesForm').addEventListener('submit', function(e) {
    const minDuration = parseInt(document.getElementById('booking_min_duration_days').value);
    const maxDuration = parseInt(document.getElementById('booking_max_duration_months').value) * 30; // Rough conversion
    
    if (minDuration > maxDuration) {
        e.preventDefault();
        alert('Warning: Minimum duration appears to exceed maximum duration. Please review your values.');
    }
});
</script>
@endsection
