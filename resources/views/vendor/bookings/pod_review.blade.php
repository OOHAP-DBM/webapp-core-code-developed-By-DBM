@extends('layouts.vendor')

@section('title', 'POD Review')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Proof of Delivery (POD) Review</h4>
                    <div class="badge bg-warning text-dark fs-6">
                        <span id="pending-count">{{ $pendingCount ?? 0 }}</span> Pending
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Stats Row -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-muted">Pending</h5>
                                    <h2 class="text-warning" id="stat-pending">{{ $stats['pending'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-muted">Approved</h5>
                                    <h2 class="text-success" id="stat-approved">{{ $stats['approved'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-muted">Rejected</h5>
                                    <h2 class="text-danger" id="stat-rejected">{{ $stats['rejected'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-muted">Total</h5>
                                    <h2 class="text-primary" id="stat-total">{{ $stats['total'] ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending PODs Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="pod-table">
                            <thead>
                                <tr>
                                    <th>Preview</th>
                                    <th>Booking</th>
                                    <th>Hoarding</th>
                                    <th>Type</th>
                                    <th>Uploaded By</th>
                                    <th>Uploaded At</th>
                                    <th>Distance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pod-tbody">
                                @forelse($pendingPODs ?? [] as $proof)
                                <tr data-pod-id="{{ $proof->id }}">
                                    <td>
                                        <a href="javascript:void(0)" onclick="viewPOD({{ $proof->id }})">
                                            @if($proof->type === 'photo')
                                                <img src="{{ $proof->thumbnail_url }}" alt="POD Preview" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                    <i class="bi bi-play-circle fs-3"></i>
                                                </div>
                                            @endif
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('vendor.bookings.show', $proof->booking_id) }}">
                                            {{ $proof->booking->booking_reference ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>{{ $proof->booking->hoarding->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $proof->type === 'photo' ? 'info' : 'primary' }}">
                                            {{ ucfirst($proof->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $proof->uploader->name ?? 'N/A' }}</td>
                                    <td>{{ $proof->uploaded_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $proof->distance_from_hoarding <= 50 ? 'success' : ($proof->distance_from_hoarding <= 100 ? 'warning' : 'danger') }}">
                                            {{ $proof->distance_from_hoarding }}m
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="approvePOD({{ $proof->id }})">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectPOD({{ $proof->id }})">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="no-pods-row">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1"></i>
                                        <p class="mt-2">No pending PODs to review</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- POD View Modal -->
<div class="modal fade" id="podViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Proof of Delivery Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="pod-modal-body">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve POD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approve-form">
                <div class="modal-body">
                    <input type="hidden" id="approve-pod-id">
                    <p>Are you sure you want to approve this POD? The booking will become active immediately.</p>
                    <div class="mb-3">
                        <label for="approve-notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="approve-notes" rows="3" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject POD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reject-form">
                <div class="modal-body">
                    <input type="hidden" id="reject-pod-id">
                    <p class="text-danger">Please provide a reason for rejecting this POD. The mounter will need to upload a new proof.</p>
                    <div class="mb-3">
                        <label for="reject-notes" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject-notes" rows="4" required minlength="10" maxlength="1000"></textarea>
                        <div class="form-text">Minimum 10 characters</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // View POD details
    function viewPOD(podId) {
        $.ajax({
            url: `/api/v1/vendors/pod/${podId}`,
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const pod = response.data;
                    let mediaHtml = '';
                    
                    if (pod.type === 'photo') {
                        mediaHtml = `<img src="${pod.proof_url}" alt="POD" class="img-fluid">`;
                    } else {
                        mediaHtml = `<video controls class="w-100"><source src="${pod.proof_url}" type="video/mp4">Your browser does not support video.</video>`;
                    }
                    
                    const html = `
                        <div class="row">
                            <div class="col-md-8">
                                ${mediaHtml}
                            </div>
                            <div class="col-md-4">
                                <h6>Booking Details</h6>
                                <p class="mb-1"><strong>Reference:</strong> ${pod.booking_reference}</p>
                                <p class="mb-1"><strong>Hoarding:</strong> ${pod.hoarding.name}</p>
                                <hr>
                                <h6>Upload Details</h6>
                                <p class="mb-1"><strong>Uploaded By:</strong> ${pod.uploaded_by.name}</p>
                                <p class="mb-1"><strong>Uploaded At:</strong> ${new Date(pod.uploaded_at).toLocaleString()}</p>
                                <hr>
                                <h6>Location Details</h6>
                                <p class="mb-1"><strong>GPS:</strong> ${pod.latitude}, ${pod.longitude}</p>
                                <p class="mb-1"><strong>Distance:</strong> <span class="badge bg-${pod.distance_from_hoarding <= 50 ? 'success' : (pod.distance_from_hoarding <= 100 ? 'warning' : 'danger')}">${pod.distance_from_hoarding}m</span></p>
                                <p class="mb-1"><strong>Hoarding GPS:</strong> ${pod.hoarding.latitude}, ${pod.hoarding.longitude}</p>
                                ${pod.metadata && pod.metadata.gps_accuracy ? `<p class="mb-1"><strong>GPS Accuracy:</strong> ${pod.metadata.gps_accuracy}m</p>` : ''}
                            </div>
                        </div>
                    `;
                    
                    $('#pod-modal-body').html(html);
                    new bootstrap.Modal($('#podViewModal')).show();
                }
            },
            error: function() {
                alert('Failed to load POD details');
            }
        });
    }

    // Approve POD
    function approvePOD(podId) {
        $('#approve-pod-id').val(podId);
        new bootstrap.Modal($('#approveModal')).show();
    }

    $('#approve-form').on('submit', function(e) {
        e.preventDefault();
        const podId = $('#approve-pod-id').val();
        const notes = $('#approve-notes').val();
        
        $.ajax({
            url: `/api/v1/vendors/pod/${podId}/approve`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ notes: notes }),
            success: function(response) {
                if (response.success) {
                    bootstrap.Modal.getInstance($('#approveModal')).hide();
                    $(`tr[data-pod-id="${podId}"]`).remove();
                    updateStats();
                    alert('POD approved successfully! Booking is now active.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to approve POD');
            }
        });
    });

    // Reject POD
    function rejectPOD(podId) {
        $('#reject-pod-id').val(podId);
        $('#reject-notes').val('');
        new bootstrap.Modal($('#rejectModal')).show();
    }

    $('#reject-form').on('submit', function(e) {
        e.preventDefault();
        const podId = $('#reject-pod-id').val();
        const notes = $('#reject-notes').val();
        
        if (notes.length < 10) {
            alert('Rejection reason must be at least 10 characters');
            return;
        }
        
        $.ajax({
            url: `/api/v1/vendors/pod/${podId}/reject`,
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ notes: notes }),
            success: function(response) {
                if (response.success) {
                    bootstrap.Modal.getInstance($('#rejectModal')).hide();
                    $(`tr[data-pod-id="${podId}"]`).remove();
                    updateStats();
                    alert('POD rejected. Mounter will be notified to upload a new proof.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to reject POD');
            }
        });
    });

    // Update stats
    function updateStats() {
        $.ajax({
            url: '/api/v1/vendors/pod/stats',
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    $('#stat-pending').text(response.data.pending);
                    $('#stat-approved').text(response.data.approved);
                    $('#stat-rejected').text(response.data.rejected);
                    $('#stat-total').text(response.data.total);
                    $('#pending-count').text(response.data.pending);
                    
                    if (response.data.pending === 0 && $('#pod-tbody tr').length === 0) {
                        $('#pod-tbody').html(`
                            <tr id="no-pods-row">
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1"></i>
                                    <p class="mt-2">No pending PODs to review</p>
                                </td>
                            </tr>
                        `);
                    }
                }
            }
        });
    }
</script>
@endpush
