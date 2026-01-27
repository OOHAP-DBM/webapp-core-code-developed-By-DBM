@extends('layouts.vendor')

@section('title', 'Hoarding Preview')

@section('content')
<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $hoarding->title ?? 'Hoarding Preview' }}</h5>
                    <span class="badge bg-{{ $hoarding->getStatusBadge() }}">{{ $hoarding->getStatusLabel() }}</span>
                </div>

                <div class="card-body">
                    <!-- Hoarding Details Preview -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Location</h6>
                            <p class="mb-3">{{ $hoarding->display_location }}</p>

                            <h6 class="text-muted">Type</h6>
                            <p class="mb-3">{{ ucfirst($hoarding->hoarding_type) }}</p>

                            <h6 class="text-muted">Visibility</h6>
                            <p class="mb-3">
                                @if($hoarding->visibility_start && $hoarding->visibility_end)
                                    {{ $hoarding->visibility_start }} - {{ $hoarding->visibility_end }}
                                @else
                                    All day
                                @endif
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Base Price (Monthly)</h6>
                            <p class="mb-3"><strong>₹{{ number_format($hoarding->base_monthly_price, 2) }}</strong></p>

                            <h6 class="text-muted">Grace Period</h6>
                            <p class="mb-3">{{ $hoarding->grace_period_days }} days</p>

                            <h6 class="text-muted">Hoarding Visibility</h6>
                            <p class="mb-3">{{ $hoarding->hoarding_visibility ?? 'Not specified' }}</p>
                        </div>
                    </div>

                    <hr>

                    <!-- Description -->
                    <div class="mb-4">
                        <h6 class="text-muted">Description</h6>
                        <p>{{ $hoarding->description }}</p>
                    </div>

                    <!-- Gallery Preview -->
                    @if($hoarding->hoardingMedia->isNotEmpty())
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Gallery</h6>
                        <div class="row g-3">
                            @foreach($hoarding->hoardingMedia as $media)
                            <div class="col-md-4">
                                <img src="{{ $media->media_url ?? '#' }}" class="img-fluid rounded" alt="Hoarding Image">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Audience & Traffic -->
                    @if($hoarding->audience_types || $hoarding->expected_footfall)
                    <div class="mb-4">
                        <h6 class="text-muted">Audience & Traffic</h6>
                        @if($hoarding->audience_types)
                        <p><strong>Audience Types:</strong> {{ is_array($hoarding->audience_types) ? implode(', ', $hoarding->audience_types) : $hoarding->audience_types }}</p>
                        @endif
                        @if($hoarding->expected_footfall)
                        <p><strong>Expected Footfall:</strong> {{ number_format($hoarding->expected_footfall) }} per day</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    @if($hoarding->canBeEdited())
                    <a href="{{ route('vendor.hoardings.edit', $hoarding->id) }}" class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Hoarding
                    </a>
                    @endif

                    @if($hoarding->isDraft() || $hoarding->isPreview())
                    <button class="btn btn-success w-100 mb-2" onclick="publishHoarding({{ $hoarding->id }})">
                        <i class="fas fa-paper-plane"></i> Publish Now
                    </button>
                    @endif

                    @if($hoarding->preview_token)
                    <button class="btn btn-info w-100" onclick="copyPreviewLink()">
                        <i class="fas fa-link"></i> Copy Preview Link
                    </button>
                    <input type="hidden" id="previewUrl" value="{{ route('hoarding.preview.show', $hoarding->preview_token) }}">
                    @endif
                </div>
            </div>

            <!-- Status Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Status Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Status</small>
                        <p class="mb-0"><span class="badge bg-{{ $hoarding->getStatusBadge() }}">{{ $hoarding->getStatusLabel() }}</span></p>
                    </div>

                    @if($hoarding->isDraft())
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-info-circle"></i> This hoarding is in draft status. Click "Publish Now" to make it live.
                    </div>
                    @elseif($hoarding->isPreview())
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle"></i> This hoarding is in preview mode. Customers can view it before publishing.
                    </div>
                    @elseif($hoarding->isPublished())
                    <div class="alert alert-success small mb-0">
                        <i class="fas fa-check-circle"></i> This hoarding is published and auto-approved!
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function publishHoarding(hoardingId) {
    if (!confirm('Are you sure you want to publish this hoarding? It will be auto-approved and go live immediately.')) {
        return;
    }

    fetch(`/vendor/hoardings/${hoardingId}/publish`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to publish', 'danger');
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error(error);
        showAlert('An error occurred', 'danger');
    });
}

function copyPreviewLink() {
    const url = document.getElementById('previewUrl').value;
    navigator.clipboard.writeText(url).then(() => {
        showAlert('Preview link copied to clipboard!', 'success');
    }).catch(() => {
        showAlert('Failed to copy link', 'danger');
    });
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));

    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}
</script>
@endsection
