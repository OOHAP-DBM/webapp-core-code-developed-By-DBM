@extends('layouts.customer')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Quotation Details</h4>
                        <div>
                            <span class="badge bg-light text-dark me-2">Version {{ $quotation->version }}</span>
                            @if($quotation->status === 'sent')
                                <span class="badge bg-warning text-dark">Pending Approval</span>
                            @elseif($quotation->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($quotation->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @elseif($quotation->status === 'revised')
                                <span class="badge bg-secondary">Revised</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Vendor & Customer Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">From (Vendor)</h6>
                            <p class="mb-1"><strong>{{ $quotation->vendor->name }}</strong></p>
                            <p class="mb-1 text-muted">{{ $quotation->vendor->email }}</p>
                            @if($quotation->vendor->phone)
                                <p class="mb-0 text-muted"><i class="bi bi-telephone"></i> {{ $quotation->vendor->phone }}</p>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <h6 class="text-muted mb-2">To (Customer)</h6>
                            <p class="mb-1"><strong>{{ $quotation->customer->name }}</strong></p>
                            <p class="mb-1 text-muted">{{ $quotation->customer->email }}</p>
                            @if($quotation->customer->phone)
                                <p class="mb-0 text-muted"><i class="bi bi-telephone"></i> {{ $quotation->customer->phone }}</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <!-- Offer Reference -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Quotation ID:</strong> #{{ $quotation->id }}</p>
                                <p class="mb-1"><strong>Offer Reference:</strong> #{{ $quotation->offer_id }} (v{{ $quotation->offer->version }})</p>
                                <p class="mb-0"><strong>Date:</strong> {{ $quotation->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Hoarding:</strong> {{ $quotation->offer->price_snapshot['hoarding_title'] ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Location:</strong> {{ $quotation->offer->price_snapshot['hoarding_location'] ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Duration:</strong> {{ $quotation->offer->price_snapshot['duration_days'] ?? 0 }} days</p>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <h5 class="mb-3">Line Items</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">#</th>
                                    <th width="40%">Description</th>
                                    <th width="15%" class="text-center">Quantity</th>
                                    <th width="15%" class="text-end">Rate</th>
                                    <th width="20%" class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($quotation->items)
                                    @foreach($quotation->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item['description'] }}</td>
                                            <td class="text-center">{{ $item['quantity'] }} {{ $item['unit'] ?? 'unit' }}</td>
                                            <td class="text-end">₹{{ number_format($item['rate'], 2) }}</td>
                                            <td class="text-end">₹{{ number_format($item['amount'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="row mb-4">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Subtotal:</strong></td>
                                    <td class="text-end">₹{{ number_format($quotation->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Tax:</td>
                                    <td class="text-end">₹{{ number_format($quotation->tax, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>Discount:</td>
                                    <td class="text-end">- ₹{{ number_format($quotation->discount, 2) }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>Grand Total:</strong></td>
                                    <td class="text-end"><strong class="text-primary fs-5">₹{{ number_format($quotation->grand_total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($quotation->notes)
                        <div class="mb-4">
                            <h6>Notes</h6>
                            <p class="p-3 bg-light border-start border-primary border-4">{{ $quotation->notes }}</p>
                        </div>
                    @endif

                    <!-- Approval Status -->
                    @if($quotation->isApproved())
                        <div class="alert alert-success">
                            <h5><i class="bi bi-check-circle-fill"></i> Quotation Approved</h5>
                            <p class="mb-0">Approved on: {{ $quotation->approved_at->format('M d, Y h:i A') }}</p>
                        </div>
                    @elseif($quotation->isRejected())
                        <div class="alert alert-danger">
                            <h5><i class="bi bi-x-circle-fill"></i> Quotation Rejected</h5>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    @if($quotation->canApprove())
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-success btn-lg" id="approveBtn">
                                <i class="bi bi-check-circle"></i> Approve Quotation
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-lg" id="rejectBtn">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                <i class="bi bi-arrow-left"></i> Back
                            </button>
                        </div>

                        <div id="spinner" class="d-none mb-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Processing...</span>
                            </div>
                        </div>

                        <div id="alertContainer"></div>
                    @else
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                            <i class="bi bi-arrow-left"></i> Back to Quotations
                        </button>
                    @endif
                </div>

                <div class="card-footer text-muted">
                    <div class="row">
                        <div class="col-md-6">
                            <small>Created: {{ $quotation->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="downloadPdfBtn">
                                <i class="bi bi-file-pdf"></i> Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Confirmation Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Approve Quotation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this quotation?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Important:</strong> Once approved, this quotation will be finalized and cannot be changed. 
                    A snapshot of all details will be saved permanently.
                </div>
                <p class="mb-0"><strong>Grand Total: ₹{{ number_format($quotation->grand_total, 2) }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">
                    <i class="bi bi-check-circle"></i> Confirm Approval
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
    const quotationId = {{ $quotation->id }};

    @if($quotation->canApprove())
    document.getElementById('approveBtn').addEventListener('click', function() {
        approveModal.show();
    });

    document.getElementById('confirmApproveBtn').addEventListener('click', async function() {
        const spinner = document.getElementById('spinner');
        const alertContainer = document.getElementById('alertContainer');

        spinner.classList.remove('d-none');
        alertContainer.innerHTML = '';
        approveModal.hide();

        try {
            const response = await fetch(`/api/v1/quotations/${quotationId}/approve`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to approve quotation');
            }

            alertContainer.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Quotation approved successfully!
                </div>`;

            setTimeout(() => location.reload(), 1500);
        } catch (error) {
            alertContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message}
                </div>`;
        } finally {
            spinner.classList.add('d-none');
        }
    });

    document.getElementById('rejectBtn').addEventListener('click', async function() {
        if (!confirm('Are you sure you want to reject this quotation?')) return;

        const spinner = document.getElementById('spinner');
        const alertContainer = document.getElementById('alertContainer');

        spinner.classList.remove('d-none');
        alertContainer.innerHTML = '';

        try {
            const response = await fetch(`/api/v1/quotations/${quotationId}/reject`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to reject quotation');
            }

            alertContainer.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Quotation rejected successfully!
                </div>`;

            setTimeout(() => location.reload(), 1500);
        } catch (error) {
            alertContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message}
                </div>`;
        } finally {
            spinner.classList.add('d-none');
        }
    });
    @endif

    document.getElementById('downloadPdfBtn').addEventListener('click', function() {
        // TODO: Implement PDF generation
        alert('PDF generation feature coming soon!');
    });
});
</script>
@endsection
