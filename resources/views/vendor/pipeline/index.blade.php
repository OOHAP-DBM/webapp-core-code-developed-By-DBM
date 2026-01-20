@extends('layouts.vendor')

@section('title', 'Booking Pipeline')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Booking Pipeline</h1>
            <p class="text-muted mb-0">Kanban board view of all your bookings</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="refreshPipeline()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('vendor.pipeline.export', ['format' => 'csv'] + request()->query()) }}">
                        <i class="fas fa-file-csv"></i> Export as CSV
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('vendor.pipeline.export', ['format' => 'pdf'] + request()->query()) }}">
                        <i class="fas fa-file-pdf"></i> Export as PDF
                    </a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Bookings</p>
                            <h3 class="mb-0">{{ $summary['total_bookings'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-calendar-alt text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Value</p>
                            <h3 class="mb-0">{{ $summary['total_value_formatted'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-rupee-sign text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Active Campaigns</p>
                            <h3 class="mb-0">{{ $summary['active_bookings'] }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-rocket text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Conversion Rate</p>
                            <h3 class="mb-0">{{ $summary['conversion_rate'] }}%</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-line text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.pipeline.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Booking ID, Customer, Hoarding..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>High Priority Only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('vendor.pipeline.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="pipeline-board">
        <div class="pipeline-container" id="pipelineBoard">
            @foreach($stages as $stageKey => $stage)
                <div class="pipeline-column" data-stage="{{ $stageKey }}">
                    <!-- Column Header -->
                    <div class="pipeline-column-header bg-{{ $stage['color'] }} bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas {{ $stage['icon'] }} text-{{ $stage['color'] }}"></i>
                                <span class="fw-bold">{{ $stage['label'] }}</span>
                                @if($stage['optional'])
                                    <span class="badge bg-secondary">Optional</span>
                                @endif
                            </div>
                            <span class="badge bg-{{ $stage['color'] }}">{{ $stage['count'] }}</span>
                        </div>
                        <div class="mt-2 small text-muted">
                            Value: ₹{{ number_format($stage['total_value']) }}
                        </div>
                    </div>

                    <!-- Cards Container -->
                    <div class="pipeline-cards" data-stage="{{ $stageKey }}">
                        @forelse($stage['bookings'] as $booking)
                            <div class="pipeline-card" 
                                 data-booking-id="{{ $booking['id'] }}" 
                                 draggable="true">
                                <!-- Card Header -->
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-secondary">{{ $booking['booking_id'] }}</span>
                                    <div class="d-flex gap-1">
                                        @if($booking['is_urgent'])
                                            <span class="badge bg-danger" title="Starting soon">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                        @endif
                                        @if($booking['is_high_value'])
                                            <span class="badge bg-warning" title="High value">
                                                <i class="fas fa-star"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Hoarding Image -->
                                @if($booking['hoarding_image'])
                                    <img src="{{ $booking['hoarding_image'] }}" 
                                         alt="{{ $booking['hoarding_title'] }}" 
                                         class="pipeline-card-image mb-2">
                                @endif

                                <!-- Hoarding Info -->
                                <h6 class="mb-1">{{ Str::limit($booking['hoarding_title'], 40) }}</h6>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt"></i> {{ $booking['hoarding_city'] }}
                                </p>

                                <!-- Customer -->
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    @if($booking['customer_avatar'])
                                        <img src="{{ $booking['customer_avatar'] }}" 
                                             alt="{{ $booking['customer_name'] }}" 
                                             class="rounded-circle" 
                                             style="width: 24px; height: 24px;">
                                    @else
                                        <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center" 
                                             style="width: 24px; height: 24px;">
                                            <i class="fas fa-user text-primary" style="font-size: 12px;"></i>
                                        </div>
                                    @endif
                                    <span class="small">{{ Str::limit($booking['customer_name'], 20) }}</span>
                                </div>

                                <!-- Amount -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success">{{ $booking['total_amount_formatted'] }}</span>
                                    @if($booking['start_date'])
                                        <span class="small text-muted">{{ $booking['start_date'] }}</span>
                                    @endif
                                </div>

                                <!-- Latest Update -->
                                @if($booking['latest_update'])
                                    <div class="small text-muted">
                                        <i class="fas fa-clock"></i> {{ $booking['latest_update'] }}
                                    </div>
                                @endif

                                <!-- View Details Button -->
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary w-100 mt-2" 
                                        onclick="viewBookingDetails({{ $booking['id'] }})">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0 small">No bookings in this stage</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.pipeline-board {
    overflow-x: auto;
    padding-bottom: 20px;
}

.pipeline-container {
    display: flex;
    gap: 20px;
    min-width: max-content;
}

.pipeline-column {
    flex: 0 0 320px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 400px);
}

.pipeline-column-header {
    padding: 16px;
    border-radius: 8px 8px 0 0;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.pipeline-cards {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
    min-height: 200px;
}

.pipeline-card {
    background: white;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: move;
    transition: all 0.2s;
}

.pipeline-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.pipeline-card.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

.pipeline-cards.drag-over {
    background: rgba(13, 110, 253, 0.1);
    border: 2px dashed #0d6efd;
}

.pipeline-card-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
}

/* Custom scrollbar */
.pipeline-cards::-webkit-scrollbar {
    width: 6px;
}

.pipeline-cards::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.pipeline-cards::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.pipeline-cards::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
@endpush

@push('scripts')
<script>
// Drag and Drop functionality
let draggedCard = null;
let draggedBookingId = null;
let draggedFromStage = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
});

function initializeDragAndDrop() {
    const cards = document.querySelectorAll('.pipeline-card');
    const columns = document.querySelectorAll('.pipeline-cards');

    cards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    columns.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('drop', handleDrop);
        column.addEventListener('dragleave', handleDragLeave);
    });
}

function handleDragStart(e) {
    draggedCard = this;
    draggedBookingId = this.getAttribute('data-booking-id');
    draggedFromStage = this.closest('.pipeline-cards').getAttribute('data-stage');
    
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragEnd(e) {
    this.classList.remove('dragging');
    
    document.querySelectorAll('.pipeline-cards').forEach(column => {
        column.classList.remove('drag-over');
    });
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
    
    return false;
}

function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    this.classList.remove('drag-over');
    
    const toStage = this.getAttribute('data-stage');
    
    if (draggedFromStage !== toStage) {
        // Move booking to new stage via AJAX
        moveBooking(draggedBookingId, draggedFromStage, toStage);
    }
    
    return false;
}

function moveBooking(bookingId, fromStage, toStage) {
    fetch('{{ route("vendor.pipeline.move") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            booking_id: bookingId,
            from_stage: fromStage,
            to_stage: toStage,
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Booking moved successfully', 'success');
            
            // Refresh pipeline to reflect changes
            refreshPipeline();
        } else {
            showToast(data.message || 'Failed to move booking', 'error');
            
            // Revert the visual change
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while moving the booking', 'error');
        location.reload();
    });
}

function refreshPipeline() {
    const currentUrl = new URL(window.location.href);
    const searchParams = currentUrl.searchParams;
    
    fetch('{{ route("vendor.pipeline.data") }}?' + searchParams.toString(), {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Simple reload for now
        }
    })
    .catch(error => {
        console.error('Error refreshing pipeline:', error);
    });
}

function viewBookingDetails(bookingId) {
    const modal = new bootstrap.Modal(document.getElementById('bookingDetailsModal'));
    const content = document.getElementById('bookingDetailsContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch booking details
    fetch('/vendor/pipeline/booking/' + bookingId, {
        headers: {
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const booking = data.booking;
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Booking Information</h6>
                        <table class="table table-sm">
                            <tr><th>Booking ID:</th><td>${booking.booking_id}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-primary">${booking.status}</span></td></tr>
                            <tr><th>Created:</th><td>${booking.created_at}</td></tr>
                        </table>
                        
                        <h6 class="mt-3">Customer Details</h6>
                        <table class="table table-sm">
                            <tr><th>Name:</th><td>${booking.customer.name}</td></tr>
                            <tr><th>Email:</th><td>${booking.customer.email}</td></tr>
                            <tr><th>Phone:</th><td>${booking.customer.phone || 'N/A'}</td></tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Hoarding Details</h6>
                        ${booking.hoarding.image_url ? `<img src="${booking.hoarding.image_url}" class="img-fluid rounded mb-2">` : ''}
                        <table class="table table-sm">
                            <tr><th>Title:</th><td><a href="/hoardings/${booking.hoarding.id}" target="_blank">${booking.hoarding.title}</a></td></tr>
                            <tr><th>Location:</th><td>${booking.hoarding.location}</td></tr>
                            <tr><th>City:</th><td>${booking.hoarding.city}</td></tr>
                            <tr><th>Type:</th><td>${booking.hoarding.type}</td></tr>
                        </table>
                        
                        <h6 class="mt-3">Financial Details</h6>
                        <table class="table table-sm">
                            <tr><th>Amount:</th><td class="fw-bold text-success">${booking.financials.total_amount_formatted}</td></tr>
                            <tr><th>Payment:</th><td>${booking.financials.payment_status}</td></tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Campaign Dates</h6>
                    <p>Start: ${booking.dates.start || 'TBD'} | End: ${booking.dates.end || 'TBD'} | Duration: ${booking.dates.duration_days || 'N/A'} days</p>
                </div>
                
                <div class="mt-3">
                    <h6>Recent Timeline</h6>
                    <div class="timeline-sm">
                        ${booking.timeline && booking.timeline.length > 0 ? booking.timeline.map(event => `
                            <div class="mb-2 small">
                                <strong>${event.title}</strong> - ${event.created_at_human}
                                ${event.description ? `<br><span class="text-muted">${event.description}</span>` : ''}
                            </div>
                        `).join('') : '<p class="text-muted">No timeline events yet</p>'}
                    </div>
                </div>
            `;
        } else {
            content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `<div class="alert alert-danger">Failed to load booking details</div>`;
    });
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush
