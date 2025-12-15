@extends('layouts.customer')

@section('title', 'Campaign Details - ' . $booking['booking_id'])

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('customer.campaigns.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
            <h1 class="h3 mb-1">{{ $booking['hoarding']['title'] }}</h1>
            <p class="text-muted mb-0">
                Campaign ID: <strong>{{ $booking['booking_id'] }}</strong>
                <span class="badge bg-{{ $booking['status_color'] }} ms-2">{{ $booking['status_label'] }}</span>
            </p>
        </div>
        <div>
            @if($purchase_order && $purchase_order['pdf_url'])
                <a href="{{ $purchase_order['pdf_url'] }}" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-file-pdf"></i> Purchase Order
                </a>
            @endif
            <a href="{{ route('customer.campaigns.download-report', $booking['id']) }}" class="btn btn-outline-secondary">
                <i class="fas fa-download"></i> Download Report
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Campaign Overview -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Campaign Overview</h5>
                </div>
                <div class="card-body">
                    @if($booking['hoarding']['image_url'])
                        <img src="{{ $booking['hoarding']['image_url'] }}" 
                             alt="{{ $booking['hoarding']['title'] }}"
                             class="img-fluid rounded mb-3"
                             style="max-height: 300px; width: 100%; object-fit: cover;">
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted">Location</label>
                            <p class="mb-0"><strong>{{ $booking['hoarding']['location'] }}</strong></p>
                            <p class="text-muted small">{{ $booking['hoarding']['city'] }}, {{ $booking['hoarding']['state'] }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Type</label>
                            <p class="mb-0"><strong>{{ ucfirst($booking['hoarding']['type']) }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Campaign Duration</label>
                            <p class="mb-0">
                                <strong>{{ \Carbon\Carbon::parse($booking['dates']['start'])->format('M d, Y') }}</strong>
                                to
                                <strong>{{ \Carbon\Carbon::parse($booking['dates']['end'])->format('M d, Y') }}</strong>
                            </p>
                            <p class="text-muted small">{{ $booking['dates']['duration_days'] }} days</p>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted">Status</label>
                            <p class="mb-0">
                                @if($booking['dates']['is_active'])
                                    <span class="badge bg-success">Live - Running</span>
                                    <br><small class="text-muted">{{ abs($booking['dates']['days_remaining']) }} days remaining</small>
                                @elseif($booking['dates']['days_until_start'] > 0)
                                    <span class="badge bg-info">Upcoming</span>
                                    <br><small class="text-muted">Starts in {{ abs($booking['dates']['days_until_start']) }} days</small>
                                @else
                                    <span class="badge bg-secondary">Completed</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar for Active Campaigns -->
                    @if($booking['dates']['is_active'])
                        @php
                            $totalDays = $booking['dates']['duration_days'];
                            $elapsed = $totalDays - abs($booking['dates']['days_remaining']);
                            $progress = $totalDays > 0 ? ($elapsed / $totalDays) * 100 : 0;
                        @endphp
                        <div class="mt-3">
                            <label class="small text-muted">Campaign Progress</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" 
                                     role="progressbar" 
                                     style="width: {{ min($progress, 100) }}%"
                                     aria-valuenow="{{ $progress }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    {{ number_format($progress, 1) }}% Complete
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-timeline me-2"></i>Campaign Timeline</h5>
                </div>
                <div class="card-body">
                    @if(count($timeline) > 0)
                        <div class="timeline-vertical">
                            @foreach($timeline as $event)
                                <div class="timeline-item mb-4">
                                    <div class="timeline-marker">
                                        <span class="badge rounded-circle bg-{{ $event['event_type'] === 'stage_change' ? 'primary' : 'secondary' }}" 
                                              style="width: 15px; height: 15px;"></span>
                                    </div>
                                    <div class="timeline-content ms-4">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $event['title'] }}</strong>
                                                @if($event['stage'])
                                                    <span class="badge bg-info ms-2">{{ ucfirst($event['stage']) }}</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($event['created_at'])->format('M d, Y H:i') }}</small>
                                        </div>
                                        @if($event['description'])
                                            <p class="mb-0 text-muted mt-1">{{ $event['description'] }}</p>
                                        @endif
                                        @if($event['metadata'])
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($event['created_at'])->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No timeline events yet</p>
                    @endif
                </div>
            </div>

            <!-- Creatives -->
            @if(count($creatives) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Creatives & Artwork</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($creatives as $creative)
                            <div class="col-md-6">
                                <div class="card border">
                                    @if(isset($creative->file_url))
                                        <img src="{{ asset('storage/' . $creative->file_url) }}" 
                                             class="card-img-top" 
                                             alt="Creative"
                                             style="height: 200px; object-fit: cover;">
                                    @endif
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $creative->title ?? 'Creative ' . $loop->iteration }}</h6>
                                        <p class="card-text small text-muted">
                                            Status: <span class="badge bg-{{ $creative->status === 'approved' ? 'success' : 'warning' }}">
                                                {{ ucfirst($creative->status) }}
                                            </span>
                                        </p>
                                        @if(isset($creative->file_url))
                                            <a href="{{ asset('storage/' . $creative->file_url) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Mounting Proofs -->
            @if(count($proofs) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Mounting Proofs</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($proofs as $proof)
                            <div class="col-md-4">
                                <div class="card border">
                                    @if(isset($proof->image_url))
                                        <img src="{{ asset('storage/' . $proof->image_url) }}" 
                                             class="card-img-top" 
                                             alt="Proof"
                                             style="height: 150px; object-fit: cover;">
                                    @endif
                                    <div class="card-body">
                                        <p class="card-text small">
                                            <strong>{{ ucfirst($proof->type ?? 'mounting') }} Proof</strong>
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($proof->created_at)->format('M d, Y H:i') }}</small>
                                        </p>
                                        @if(isset($proof->image_url))
                                            <a href="{{ asset('storage/' . $proof->image_url) }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-outline-primary btn-block w-100">
                                                <i class="fas fa-eye"></i> View Full
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Financial Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-rupee-sign me-2"></i>Financial Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Amount:</span>
                        <strong>₹{{ number_format($booking['financials']['total_amount']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Status:</span>
                        <span class="badge bg-{{ $booking['financials']['payment_status'] === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($booking['financials']['payment_status']) }}
                        </span>
                    </div>
                    @if($purchase_order)
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>PO Number:</span>
                            <strong>{{ $purchase_order['po_number'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>PO Status:</span>
                            <span class="badge bg-info">{{ ucfirst($purchase_order['status']) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Vendor Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>Vendor Information</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>{{ $booking['vendor']['name'] }}</strong></p>
                    @if($booking['vendor']['phone'])
                        <p class="mb-2">
                            <i class="fas fa-phone me-2 text-muted"></i>{{ $booking['vendor']['phone'] }}
                        </p>
                    @endif
                    <a href="#" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-comments"></i> Contact Vendor
                    </a>
                </div>
            </div>

            <!-- Invoices -->
            @if(count($invoices) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoices</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($invoices as $invoice)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                        <br>
                                        <small class="text-muted">₹{{ number_format($invoice->amount) }}</small>
                                    </div>
                                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </div>
                                <small class="text-muted">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($purchase_order && $purchase_order['pdf_url'])
                            <a href="{{ $purchase_order['pdf_url'] }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-pdf"></i> View Purchase Order
                            </a>
                        @endif
                        <a href="{{ route('customer.campaigns.download-report', $booking['id']) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download"></i> Download Report
                        </a>
                        <a href="#" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-comments"></i> Send Message
                        </a>
                        @if($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed')
                            <button class="btn btn-outline-danger btn-sm" onclick="confirm('Cancel this campaign?')">
                                <i class="fas fa-times-circle"></i> Request Cancellation
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline-vertical {
    position: relative;
    padding-left: 30px;
}
.timeline-vertical::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 10px;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
}
</style>
@endpush
