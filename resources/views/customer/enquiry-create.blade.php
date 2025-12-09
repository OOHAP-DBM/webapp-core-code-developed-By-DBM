@extends('layouts.customer')

@section('title', 'Create Enquiry - OOHAPP')

@push('styles')
<style>
    .enquiry-wrapper { max-width: 1200px; margin: 0 auto; }
    .enquiry-form-card, .hoarding-preview {
        background: white;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    .hoarding-preview { position: sticky; top: 24px; }
    .form-section { margin-bottom: 32px; }
    .form-section-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="enquiry-wrapper">
        <div class="row">
            <div class="col-lg-8">
                <div class="enquiry-form-card">
                    <h2 class="mb-4">Create Enquiry</h2>
                    <form action="{{ route('customer.enquiries.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="hoarding_id" value="{{ $hoarding->id ?? request('hoarding_id') }}">
                        
                        <div class="form-section">
                            <div class="form-section-title"><i class="bi bi-megaphone me-2"></i>Campaign Details</div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Campaign Title <span class="text-danger">*</span></label>
                                    <input type="text" name="campaign_title" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="4"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <div class="form-section-title"><i class="bi bi-calendar-range me-2"></i>Booking Period</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" min="{{ now()->addDays(7)->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-2"></i>Submit Enquiry
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                @if(isset($hoarding))
                <div class="hoarding-preview">
                    <h5 class="mb-3">Selected Hoarding</h5>
                    @if($hoarding->primary_image)
                    <img src="{{ asset('storage/' . $hoarding->primary_image) }}" class="img-fluid rounded mb-3" alt="{{ $hoarding->title }}">
                    @endif
                    <h6>{{ $hoarding->title }}</h6>
                    <p class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $hoarding->city }}, {{ $hoarding->state }}</p>
                    <div class="border-top pt-3">
                        <h4 class="text-primary">₹{{ number_format($hoarding->price_per_month ?? 0) }}</h4>
                        <small class="text-muted">/month</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
