@extends('layouts.vendor')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Create Offer</h2>
                <a href="{{ route('vendor.offers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Offers
                </a>
            </div>

            <!-- Enquiry Summary Card -->
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Enquiry Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Customer:</strong><br>
                            {{ $enquiry->customer->name }}<br>
                            <small class="text-muted">{{ $enquiry->customer->email }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Hoarding:</strong><br>
                            {{ $enquiry->getSnapshotValue('hoarding_title', 'N/A') }}<br>
                            <small class="text-muted">{{ $enquiry->getSnapshotValue('location', 'N/A') }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Booking Period:</strong><br>
                            {{ $enquiry->preferred_start_date->format('M d, Y') }} to {{ $enquiry->preferred_end_date->format('M d, Y') }}<br>
                            <small class="text-muted">{{ $enquiry->getDurationInDays() }} days ({{ ucfirst($enquiry->duration_type) }})</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Base Price:</strong><br>
                            ₹{{ number_format($enquiry->getSnapshotValue('price', 0), 2) }}<br>
                            @if($enquiry->getSnapshotValue('allows_weekly_booking') && $enquiry->getSnapshotValue('weekly_price'))
                                <small class="text-muted">Weekly: ₹{{ number_format($enquiry->getSnapshotValue('weekly_price', 0), 2) }}</small>
                            @endif
                        </div>
                        @if($enquiry->message)
                            <div class="col-12">
                                <strong>Customer Message:</strong>
                                <p class="mb-0 mt-2 p-3 bg-light border-start border-primary border-4">{{ $enquiry->message }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Offer Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Offer Details</h5>
                </div>
                <div class="card-body">
                    <form id="offerForm">
                        @csrf
                        <input type="hidden" name="enquiry_id" value="{{ $enquiry->id }}">

                        <div class="mb-3">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" required>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="price_type" class="form-label">Price Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="price_type" name="price_type" required>
                                <option value="">Select pricing model</option>
                                <option value="total">Total Price (One-time payment)</option>
                                <option value="monthly">Per Month</option>
                                <option value="weekly">Per Week</option>
                                <option value="daily">Per Day</option>
                            </select>
                            <div class="form-text">Choose how the price will be calculated</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <small class="text-muted">(Optional)</small></label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="4" maxlength="2000"></textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/2000 characters
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="valid_until" class="form-label">Valid Until <small class="text-muted">(Optional)</small></label>
                            <input type="datetime-local" class="form-control" id="valid_until" name="valid_until" 
                                   min="{{ now()->addDay()->format('Y-m-d\TH:i') }}">
                            <div class="form-text">Leave empty for no expiry date</div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" id="saveDraftBtn">
                                <i class="bi bi-save"></i> Save as Draft
                            </button>
                            <button type="button" class="btn btn-primary" id="saveAndSendBtn">
                                <i class="bi bi-send"></i> Save & Send to Customer
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                Cancel
                            </button>
                        </div>

                        <div id="spinner" class="d-none mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Processing...</span>
                            </div>
                        </div>

                        <div id="alertContainer" class="mt-3"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('offerForm');
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    const saveAndSendBtn = document.getElementById('saveAndSendBtn');
    const spinner = document.getElementById('spinner');
    const alertContainer = document.getElementById('alertContainer');
    const descriptionField = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    // Character counter
    descriptionField.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    async function createOffer(shouldSend = false) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        alertContainer.innerHTML = '';
        
        spinner.classList.remove('d-none');
        saveDraftBtn.disabled = true;
        saveAndSendBtn.disabled = true;

        try {
            // Create draft
            const createResponse = await fetch(`/api/v1/enquiries/${data.enquiry_id}/offers`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const createResult = await createResponse.json();

            if (!createResponse.ok) {
                if (createResult.errors) {
                    Object.keys(createResult.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = createResult.errors[field][0];
                            }
                        }
                    });
                }
                throw new Error(createResult.message || 'Failed to create offer');
            }

            const offerId = createResult.data.id;

            // If should send, send the offer
            if (shouldSend) {
                const sendResponse = await fetch(`/api/v1/offers/${offerId}/send`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                        'Accept': 'application/json'
                    }
                });

                const sendResult = await sendResponse.json();

                if (!sendResponse.ok) {
                    throw new Error(sendResult.message || 'Failed to send offer');
                }

                alertContainer.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Offer sent to customer successfully!
                    </div>`;
            } else {
                alertContainer.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Offer saved as draft successfully!
                    </div>`;
            }

            setTimeout(() => {
                window.location.href = '/vendor/offers';
            }, 1500);

        } catch (error) {
            alertContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message}
                </div>`;
        } finally {
            spinner.classList.add('d-none');
            saveDraftBtn.disabled = false;
            saveAndSendBtn.disabled = false;
        }
    }

    saveDraftBtn.addEventListener('click', () => createOffer(false));
    saveAndSendBtn.addEventListener('click', () => createOffer(true));
});
</script>
@endsection
