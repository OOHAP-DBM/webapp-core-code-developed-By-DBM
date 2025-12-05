@extends('layouts.app')

@section('title', 'Send Enquiry - ' . $hoarding->title)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope"></i> Send Enquiry
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Hoarding Summary -->
                    <div class="alert alert-light border">
                        <div class="d="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">{{ $hoarding->title }}</h6>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-geo-alt"></i> {{ $hoarding->location }}
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="fs-5 fw-bold text-primary">
                                    ₹{{ number_format($hoarding->price) }}/month
                                </div>
                                @if($hoarding->allows_weekly_booking && $hoarding->weekly_price)
                                <div class="small text-muted">
                                    ₹{{ number_format($hoarding->weekly_price) }}/week
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Enquiry Form -->
                    <form id="enquiryForm" method="POST" action="{{ route('api.v1.enquiries.store') }}">
                        @csrf
                        <input type="hidden" name="hoarding_id" value="{{ $hoarding->id }}">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="preferred_start_date" class="form-label">
                                    Preferred Start Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="preferred_start_date" 
                                       name="preferred_start_date" 
                                       min="{{ date('Y-m-d') }}"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="preferred_end_date" class="form-label">
                                    Preferred End Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="preferred_end_date" 
                                       name="preferred_end_date" 
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="duration_type" class="form-label">
                                Booking Duration Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="duration_type" name="duration_type" required>
                                <option value="days">Days</option>
                                @if($hoarding->allows_weekly_booking)
                                <option value="weeks">Weeks</option>
                                @endif
                                <option value="months" selected>Months</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">
                                Message / Additional Requirements
                            </label>
                            <textarea class="form-control" 
                                      id="message" 
                                      name="message" 
                                      rows="4" 
                                      maxlength="1000"
                                      placeholder="Tell us about your campaign, specific requirements, or any questions..."></textarea>
                            <div class="form-text">Optional. Max 1000 characters.</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Note:</strong> This enquiry will be sent to the vendor. They will review your request and respond shortly.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="bi bi-send"></i> Send Enquiry
                            </button>
                            <a href="{{ route('hoardings.show', $hoarding->id) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('enquiryForm');
    const submitBtn = document.getElementById('submitBtn');
    const startDate = document.getElementById('preferred_start_date');
    const endDate = document.getElementById('preferred_end_date');

    // Update end date min when start date changes
    startDate.addEventListener('change', function() {
        const startVal = new Date(this.value);
        startVal.setDate(startVal.getDate() + 1);
        endDate.min = startVal.toISOString().split('T')[0];
        
        if (endDate.value && new Date(endDate.value) <= new Date(this.value)) {
            endDate.value = '';
        }
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        
        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
        
        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Authorization': 'Bearer ' + (localStorage.getItem('auth_token') || '')
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok) {
                // Success
                alert('✅ Enquiry submitted successfully! The vendor will be notified.');
                window.location.href = '{{ route("hoardings.show", $hoarding->id) }}';
            } else {
                // Validation errors
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = result.errors[field][0];
                            }
                        }
                    });
                } else {
                    alert('❌ ' + (result.message || 'Failed to submit enquiry'));
                }
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send"></i> Send Enquiry';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('❌ An error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send"></i> Send Enquiry';
        }
    });
});
</script>
@endpush
@endsection
