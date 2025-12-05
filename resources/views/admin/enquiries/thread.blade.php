{{-- Enquiry Thread Partial - Can be included in admin/vendor views --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">
                    <i class="bi bi-chat-dots"></i> Enquiry #{{ $enquiry->id }}
                    <span class="badge 
                        @if($enquiry->status === 'pending') bg-warning
                        @elseif($enquiry->status === 'accepted') bg-success
                        @elseif($enquiry->status === 'rejected') bg-danger
                        @else bg-secondary
                        @endif
                        ms-2">
                        {{ ucfirst($enquiry->status) }}
                    </span>
                </h5>
            </div>
            <div class="col-auto">
                <small class="text-muted">
                    <i class="bi bi-clock"></i> {{ $enquiry->created_at->diffForHumans() }}
                </small>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Customer Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">
                    <i class="bi bi-person-circle"></i> Customer Information
                </h6>
                <div class="ps-3">
                    <p class="mb-1"><strong>{{ $enquiry->customer->name }}</strong></p>
                    <p class="mb-1 text-muted small">
                        <i class="bi bi-envelope"></i> {{ $enquiry->customer->email }}
                    </p>
                    @if($enquiry->customer->phone)
                    <p class="mb-1 text-muted small">
                        <i class="bi bi-telephone"></i> {{ $enquiry->customer->phone }}
                    </p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">
                    <i class="bi bi-calendar-range"></i> Booking Details
                </h6>
                <div class="ps-3">
                    <p class="mb-1">
                        <strong>Duration:</strong> {{ ucfirst($enquiry->duration_type) }}
                    </p>
                    <p class="mb-1">
                        <strong>Start:</strong> {{ $enquiry->preferred_start_date->format('d M Y') }}
                    </p>
                    <p class="mb-1">
                        <strong>End:</strong> {{ $enquiry->preferred_end_date->format('d M Y') }}
                    </p>
                    <p class="mb-0">
                        <strong>Duration:</strong> {{ $enquiry->getDurationInDays() }} days
                    </p>
                </div>
            </div>
        </div>

        <!-- Hoarding Info (from snapshot) -->
        <div class="mb-4">
            <h6 class="text-muted mb-2">
                <i class="bi bi-card-image"></i> Hoarding Details (at enquiry time)
            </h6>
            <div class="alert alert-light border">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="mb-1">{{ $enquiry->getSnapshotValue('hoarding_title') }}</h6>
                        <p class="text-muted small mb-1">
                            <i class="bi bi-tag"></i> {{ ucfirst($enquiry->getSnapshotValue('hoarding_type')) }}
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-geo-alt"></i> {{ $enquiry->getSnapshotValue('location') }}
                        </p>
                        @if($enquiry->getSnapshotValue('width') && $enquiry->getSnapshotValue('height'))
                        <p class="text-muted small mb-0">
                            <i class="bi bi-rulers"></i> 
                            {{ $enquiry->getSnapshotValue('width') }}m × {{ $enquiry->getSnapshotValue('height') }}m
                        </p>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="fs-5 fw-bold text-primary">
                            ₹{{ number_format($enquiry->getSnapshotValue('price')) }}/month
                        </div>
                        @if($enquiry->getSnapshotValue('allows_weekly_booking') && $enquiry->getSnapshotValue('weekly_price'))
                        <div class="small text-muted">
                            ₹{{ number_format($enquiry->getSnapshotValue('weekly_price')) }}/week
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Message -->
        @if($enquiry->message)
        <div class="mb-4">
            <h6 class="text-muted mb-2">
                <i class="bi bi-chat-text"></i> Customer Message
            </h6>
            <div class="ps-3 border-start border-3 border-primary">
                <p class="mb-0 ps-3">{{ $enquiry->message }}</p>
            </div>
        </div>
        @endif

        <!-- Status Actions (for vendor/admin) -->
        @auth
            @if(auth()->user()->hasRole(['vendor', 'admin']) && $enquiry->isPending())
            <div class="border-top pt-3">
                <h6 class="text-muted mb-3">Actions</h6>
                <form id="statusForm-{{ $enquiry->id }}" class="d-inline">
                    <input type="hidden" name="enquiry_id" value="{{ $enquiry->id }}">
                    <button type="button" 
                            class="btn btn-success btn-sm me-2" 
                            onclick="updateStatus({{ $enquiry->id }}, 'accepted')">
                        <i class="bi bi-check-circle"></i> Accept Enquiry
                    </button>
                    <button type="button" 
                            class="btn btn-danger btn-sm" 
                            onclick="updateStatus({{ $enquiry->id }}, 'rejected')">
                        <i class="bi bi-x-circle"></i> Reject Enquiry
                    </button>
                </form>
            </div>
            @endif
        @endauth
    </div>
</div>

@push('scripts')
<script>
async function updateStatus(enquiryId, status) {
    if (!confirm(`Are you sure you want to ${status} this enquiry?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/v1/enquiries/${enquiryId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Authorization': 'Bearer ' + (localStorage.getItem('auth_token') || '')
            },
            body: JSON.stringify({ status })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('✅ Enquiry status updated successfully!');
            location.reload();
        } else {
            alert('❌ ' + (result.message || 'Failed to update status'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ An error occurred. Please try again.');
    }
}
</script>
@endpush
