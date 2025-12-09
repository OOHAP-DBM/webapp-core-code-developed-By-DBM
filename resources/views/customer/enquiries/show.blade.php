@extends('layouts.customer')

@section('title', 'Enquiry Details')

@section('content')
<div class="container py-5">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('customer.enquiries.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-2"></i>Back to Enquiries
        </a>
    </div>

    <div class="row g-4">
        <!-- Left Column: Hoarding Details -->
        <div class="col-lg-8">
            <!-- Enquiry Status Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Enquiry #{{ $enquiry->id }}</h4>
                        <span class="badge 
                            @if($enquiry->status === 'pending') bg-warning text-dark
                            @elseif($enquiry->status === 'quoted') bg-info
                            @elseif($enquiry->status === 'accepted') bg-success
                            @elseif($enquiry->status === 'rejected') bg-danger
                            @else bg-secondary
                            @endif fs-6">
                            {{ ucfirst($enquiry->status) }}
                        </span>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-clock me-1"></i>
                        Created {{ \Carbon\Carbon::parse($enquiry->created_at)->format('d M Y, h:i A') }}
                    </p>
                </div>
            </div>

            <!-- Hoarding Details Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Hoarding Details</h5>
                    
                    <!-- Hoarding Image -->
                    @if($enquiry->hoarding->image)
                        <img src="{{ asset('storage/' . $enquiry->hoarding->image) }}" 
                             alt="{{ $enquiry->hoarding->title }}" 
                             class="img-fluid rounded mb-3" 
                             style="width: 100%; max-height: 300px; object-fit: cover;">
                    @else
                        <div class="bg-gradient rounded d-flex align-items-center justify-content-center mb-3" 
                             style="height: 300px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-image text-white" style="font-size: 4rem;"></i>
                        </div>
                    @endif

                    <h4 class="mb-2">{{ $enquiry->hoarding->title }}</h4>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-geo-alt me-2"></i>
                                <span>{{ $enquiry->hoarding->address }}, {{ $enquiry->hoarding->city }}, {{ $enquiry->hoarding->state }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-tag me-2"></i>
                                <span>{{ ucfirst($enquiry->hoarding->type) }} - {{ $enquiry->hoarding->illumination ? 'Illuminated' : 'Non-Illuminated' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-arrows-angle-expand me-2"></i>
                                <span>{{ $enquiry->hoarding->width }}ft × {{ $enquiry->hoarding->height }}ft</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-primary fw-bold">
                                <i class="bi bi-currency-rupee me-2"></i>
                                <span>₹{{ number_format($enquiry->hoarding->price_per_month, 0) }}/month</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('customer.search') }}?hoarding={{ $enquiry->hoarding->id }}" 
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>View Full Details
                    </a>
                </div>
            </div>

            <!-- Enquiry Message -->
            @if($enquiry->message)
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Your Message</h5>
                        <p class="mb-0">{{ $enquiry->message }}</p>
                    </div>
                </div>
            @endif

            <!-- Vendor Response / Quotation -->
            @if($enquiry->status === 'quoted' && isset($enquiry->quotation))
                <div class="card shadow-sm border-0 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-file-text-fill text-info me-2"></i>Quotation Received
                            </h5>
                            <span class="badge bg-info">New</span>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Quoted Amount</p>
                                <h4 class="text-primary mb-0">₹{{ number_format($enquiry->quotation->total_amount, 2) }}</h4>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted small mb-1">Validity</p>
                                <p class="mb-0">
                                    Valid until {{ \Carbon\Carbon::parse($enquiry->quotation->valid_until)->format('d M Y') }}
                                </p>
                            </div>
                        </div>

                        @if($enquiry->quotation->notes)
                            <div class="alert alert-light mb-3">
                                <strong>Vendor Notes:</strong><br>
                                {{ $enquiry->quotation->notes }}
                            </div>
                        @endif

                        <a href="{{ route('customer.quotations.show', $enquiry->quotation->id) }}" 
                           class="btn btn-info text-white">
                            <i class="bi bi-file-text me-2"></i>View Full Quotation
                        </a>
                    </div>
                </div>
            @endif

            <!-- Rejection Reason -->
            @if($enquiry->status === 'rejected')
                <div class="card shadow-sm border-0 border-start border-4 border-danger">
                    <div class="card-body">
                        <h5 class="card-title text-danger mb-3">
                            <i class="bi bi-x-circle-fill me-2"></i>Enquiry Rejected
                        </h5>
                        @if($enquiry->rejection_reason)
                            <p class="mb-3">{{ $enquiry->rejection_reason }}</p>
                        @else
                            <p class="mb-3">The vendor has declined this enquiry. Please contact support for more details.</p>
                        @endif
                        <a href="{{ route('customer.search') }}" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Find Another Hoarding
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="col-lg-4">
            <!-- Booking Period Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Booking Period</h5>
                    
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="me-3">
                            <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Start Date</p>
                            <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($enquiry->start_date)->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-calendar-x text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">End Date</p>
                            <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($enquiry->end_date)->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="alert alert-light mt-3 mb-0">
                        <i class="bi bi-clock me-2"></i>
                        <strong>{{ \Carbon\Carbon::parse($enquiry->start_date)->diffInDays(\Carbon\Carbon::parse($enquiry->end_date)) }} days</strong>
                    </div>
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Vendor Information</h5>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                             style="width: 50px; height: 50px;">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $enquiry->hoarding->vendor->name ?? 'N/A' }}</h6>
                            <p class="text-muted small mb-0">Vendor</p>
                        </div>
                    </div>

                    @if(isset($enquiry->hoarding->vendor->email))
                        <p class="small mb-2">
                            <i class="bi bi-envelope me-2"></i>
                            {{ $enquiry->hoarding->vendor->email }}
                        </p>
                    @endif

                    @if(isset($enquiry->hoarding->vendor->phone))
                        <p class="small mb-0">
                            <i class="bi bi-telephone me-2"></i>
                            {{ $enquiry->hoarding->vendor->phone }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">Actions</h5>

                    @if($enquiry->status === 'pending')
                        <div class="alert alert-warning">
                            <i class="bi bi-hourglass-split me-2"></i>
                            Waiting for vendor response...
                        </div>
                        <button class="btn btn-outline-danger btn-sm w-100" onclick="cancelEnquiry()">
                            <i class="bi bi-x-circle me-2"></i>Cancel Enquiry
                        </button>
                    @elseif($enquiry->status === 'quoted')
                        <a href="{{ route('customer.quotations.show', $enquiry->quotation->id ?? '#') }}" 
                           class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-file-text me-2"></i>Review Quotation
                        </a>
                    @elseif($enquiry->status === 'accepted')
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            Booking created successfully!
                        </div>
                        <a href="{{ route('customer.orders.index') }}" class="btn btn-success w-100">
                            <i class="bi bi-receipt me-2"></i>View My Bookings
                        </a>
                    @endif

                    <button class="btn btn-outline-primary btn-sm w-100 mt-2" onclick="contactSupport()">
                        <i class="bi bi-headset me-2"></i>Contact Support
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelEnquiry() {
    if (confirm('Are you sure you want to cancel this enquiry?')) {
        fetch(`/api/v1/customer/enquiries/{{ $enquiry->id }}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Enquiry cancelled successfully');
                window.location.reload();
            } else {
                alert('Failed to cancel enquiry');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}

function contactSupport() {
    alert('Support contact feature coming soon!');
    // Redirect to support page or open chat
}
</script>

<style>
.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.border-4 {
    border-width: 4px !important;
}
</style>
@endsection
