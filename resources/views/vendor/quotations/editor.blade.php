@extends('layouts.vendor')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Quotation Editor</h4>
                    <span class="badge bg-primary">Version: <span id="versionBadge">{{ $quotation->version ?? 1 }}</span></span>
                </div>
                <div class="card-body">
                    <!-- Offer Details -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="bi bi-info-circle"></i> Offer Details</h6>
                        <p class="mb-1"><strong>Offer ID:</strong> #{{ $offer->id }} (v{{ $offer->version }})</p>
                        <p class="mb-1"><strong>Customer:</strong> {{ $offer->enquiry->customer->name }}</p>
                        <p class="mb-1"><strong>Hoarding:</strong> {{ $offer->price_snapshot['hoarding_title'] ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Duration:</strong> {{ $offer->price_snapshot['duration_days'] ?? 0 }} days</p>
                    </div>

                    <form id="quotationForm">
                        @csrf
                        <input type="hidden" name="offer_id" value="{{ $offer->id }}">
                        <input type="hidden" id="quotationId" value="{{ $quotation->id ?? '' }}">

                        <!-- Line Items -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Line Items</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                                    <i class="bi bi-plus-circle"></i> Add Item
                                </button>
                            </div>

                            <div id="itemsContainer">
                                @if(isset($quotation) && $quotation->items)
                                    @foreach($quotation->items as $index => $item)
                                        <div class="item-row card mb-2 p-3" data-index="{{ $index }}">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label small">Description</label>
                                                    <input type="text" class="form-control form-control-sm item-description" 
                                                           value="{{ $item['description'] }}" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Quantity</label>
                                                    <input type="number" class="form-control form-control-sm item-quantity" 
                                                           value="{{ $item['quantity'] }}" min="0" step="0.01" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Unit</label>
                                                    <input type="text" class="form-control form-control-sm item-unit" 
                                                           value="{{ $item['unit'] ?? 'unit' }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Rate (₹)</label>
                                                    <input type="number" class="form-control form-control-sm item-rate" 
                                                           value="{{ $item['rate'] }}" min="0" step="0.01" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Amount (₹)</label>
                                                    <input type="number" class="form-control form-control-sm item-amount" 
                                                           value="{{ $item['amount'] }}" readonly>
                                                    <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-item">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <!-- Default item from offer -->
                                    <div class="item-row card mb-2 p-3" data-index="0">
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label small">Description</label>
                                                <input type="text" class="form-control form-control-sm item-description" 
                                                       value="{{ $offer->price_snapshot['hoarding_title'] ?? 'Hoarding Advertisement' }}" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Quantity</label>
                                                <input type="number" class="form-control form-control-sm item-quantity" 
                                                       value="{{ $offer->price_snapshot['duration_days'] ?? 1 }}" min="0" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Unit</label>
                                                <input type="text" class="form-control form-control-sm item-unit" value="days">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Rate (₹)</label>
                                                <input type="number" class="form-control form-control-sm item-rate" 
                                                       value="{{ $offer->price }}" min="0" step="0.01" required>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Amount (₹)</label>
                                                <input type="number" class="form-control form-control-sm item-amount" 
                                                       value="{{ $offer->price * ($offer->price_snapshot['duration_days'] ?? 1) }}" readonly>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-item">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="row mb-4">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end">
                                            <span id="subtotalDisplay">₹0.00</span>
                                            <input type="hidden" id="totalAmount" name="total_amount" value="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label class="form-label small mb-0">Tax (₹):</label>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" class="form-control form-control-sm" id="tax" 
                                                   name="tax" value="{{ $quotation->tax ?? 0 }}" min="0" step="0.01">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <label class="form-label small mb-0">Discount (₹):</label>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" class="form-control form-control-sm" id="discount" 
                                                   name="discount" value="{{ $quotation->discount ?? 0 }}" min="0" step="0.01">
                                        </td>
                                    </tr>
                                    <tr class="table-active">
                                        <td><strong>Grand Total:</strong></td>
                                        <td class="text-end">
                                            <strong id="grandTotalDisplay">₹0.00</strong>
                                            <input type="hidden" id="grandTotal" name="grand_total" value="0">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Payment Mode & Milestones (PROMPT 70 Phase 2) -->
                        <div class="mb-4">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bi bi-credit-card"></i> Payment Configuration</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Mode</label>
                                        <select class="form-select" id="paymentMode" name="payment_mode">
                                            <option value="full" {{ (!isset($quotation) || $quotation->payment_mode === 'full') ? 'selected' : '' }}>
                                                Full Payment (Customer pays complete amount)
                                            </option>
                                            <option value="milestone" {{ (isset($quotation) && $quotation->payment_mode === 'milestone') ? 'selected' : '' }}>
                                                Milestone Payments (Split into multiple payments)
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Milestone Configuration -->
                                    <div id="milestoneSection" class="mt-3" style="display: {{ (isset($quotation) && $quotation->payment_mode === 'milestone') ? 'block' : 'none' }};">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Milestone Configuration</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="addMilestoneBtn">
                                                <i class="bi bi-plus-circle"></i> Add Milestone
                                            </button>
                                        </div>

                                        <div class="alert alert-info small mb-3">
                                            <i class="bi bi-info-circle"></i> Create payment milestones to split the total amount. 
                                            Total must equal 100% or match grand total.
                                        </div>

                                        <div id="milestonesContainer">
                                            @if(isset($quotation) && $quotation->milestones && $quotation->milestones->count() > 0)
                                                @foreach($quotation->milestones as $index => $milestone)
                                                <div class="milestone-row card mb-3 p-3" data-index="{{ $index }}">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <h6 class="mb-0">Milestone #<span class="milestone-number">{{ $index + 1 }}</span></h6>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-md-5">
                                                            <label class="form-label small">Title <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-sm milestone-title" 
                                                                   value="{{ $milestone->title }}" 
                                                                   placeholder="e.g., Advance Payment" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label small">Amount Type <span class="text-danger">*</span></label>
                                                            <select class="form-select form-select-sm milestone-amount-type">
                                                                <option value="percentage" {{ $milestone->amount_type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                                <option value="fixed" {{ $milestone->amount_type === 'fixed' ? 'selected' : '' }}>Fixed (₹)</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label small">Amount <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control form-control-sm milestone-amount" 
                                                                   value="{{ $milestone->amount }}" 
                                                                   min="0" step="0.01" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label small">Calculated</label>
                                                            <input type="text" class="form-control form-control-sm milestone-calculated" 
                                                                   value="₹{{ number_format($milestone->calculated_amount, 2) }}" 
                                                                   readonly>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Due Date</label>
                                                            <input type="date" class="form-control form-control-sm milestone-due-date" 
                                                                   value="{{ $milestone->due_date ? $milestone->due_date->format('Y-m-d') : '' }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small">Description</label>
                                                            <input type="text" class="form-control form-control-sm milestone-description" 
                                                                   value="{{ $milestone->description ?? '' }}" 
                                                                   placeholder="Optional description">
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <div class="alert alert-secondary mt-3 mb-0">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Total Percentage:</strong> 
                                                    <span id="totalPercentage" class="badge bg-info">0%</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Total Amount:</strong> 
                                                    <span id="totalMilestoneAmount" class="badge bg-success">₹0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Notes <small class="text-muted">(Optional)</small></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      maxlength="2000">{{ $quotation->notes ?? '' }}</textarea>
                            <div class="form-text"><span id="notesCount">0</span>/2000 characters</div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" id="saveDraftBtn">
                                <i class="bi bi-save"></i> Save Draft
                            </button>
                            <button type="button" class="btn btn-primary" id="saveAndSendBtn">
                                <i class="bi bi-send"></i> Save & Send
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                                Cancel
                            </button>
                        </div>

                        <div id="spinner" class="d-none mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Saving...</span>
                            </div>
                        </div>

                        <div id="alertContainer" class="mt-3"></div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-pdf"></i> Preview</h5>
                </div>
                <div class="card-body">
                    <div id="previewContent">
                        <h6>Quotation Preview</h6>
                        <p class="text-muted small">Save to generate PDF preview</p>
                    </div>
                    <button type="button" class="btn btn-outline-primary w-100 mt-3" id="generatePdfBtn" disabled>
                        <i class="bi bi-file-pdf"></i> Generate PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = document.querySelectorAll('.item-row').length;
    let milestoneIndex = document.querySelectorAll('.milestone-row').length;

    // Payment mode toggle
    document.getElementById('paymentMode').addEventListener('change', function() {
        const milestoneSection = document.getElementById('milestoneSection');
        if (this.value === 'milestone') {
            milestoneSection.style.display = 'block';
            if (document.querySelectorAll('.milestone-row').length === 0) {
                // Add default milestones
                addMilestone('Advance Payment', 'percentage', 50);
                addMilestone('Final Payment', 'percentage', 50);
            }
        } else {
            milestoneSection.style.display = 'none';
        }
    });

    // Add milestone
    document.getElementById('addMilestoneBtn').addEventListener('click', function() {
        addMilestone();
    });

    function addMilestone(title = '', amountType = 'percentage', amount = 0) {
        milestoneIndex++;
        const template = `
            <div class="milestone-row card mb-3 p-3" data-index="${milestoneIndex}">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Milestone #<span class="milestone-number">${milestoneIndex}</span></h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-milestone">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="row g-2">
                    <div class="col-md-5">
                        <label class="form-label small">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm milestone-title" 
                               value="${title}" 
                               placeholder="e.g., Advance Payment" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Amount Type <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm milestone-amount-type">
                            <option value="percentage" ${amountType === 'percentage' ? 'selected' : ''}>Percentage (%)</option>
                            <option value="fixed" ${amountType === 'fixed' ? 'selected' : ''}>Fixed (₹)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm milestone-amount" 
                               value="${amount}" 
                               min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Calculated</label>
                        <input type="text" class="form-control form-control-sm milestone-calculated" 
                               value="₹0.00" 
                               readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Due Date</label>
                        <input type="date" class="form-control form-control-sm milestone-due-date">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Description</label>
                        <input type="text" class="form-control form-control-sm milestone-description" 
                               placeholder="Optional description">
                    </div>
                </div>
            </div>`;
        
        document.getElementById('milestonesContainer').insertAdjacentHTML('beforeend', template);
        updateMilestoneNumbers();
        calculateMilestoneTotals();
    }

    // Remove milestone
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-milestone')) {
            if (document.querySelectorAll('.milestone-row').length > 1) {
                e.target.closest('.milestone-row').remove();
                updateMilestoneNumbers();
                calculateMilestoneTotals();
            } else {
                alert('At least one milestone is required for milestone payment mode.');
            }
        }
    });

    // Update milestone numbers
    function updateMilestoneNumbers() {
        document.querySelectorAll('.milestone-row').forEach((row, index) => {
            row.querySelector('.milestone-number').textContent = index + 1;
        });
    }

    // Calculate milestone totals
    function calculateMilestoneTotals() {
        const grandTotal = parseFloat(document.getElementById('grandTotal').value) || 0;
        let totalPercentage = 0;
        let totalAmount = 0;

        document.querySelectorAll('.milestone-row').forEach(row => {
            const amountType = row.querySelector('.milestone-amount-type').value;
            const amount = parseFloat(row.querySelector('.milestone-amount').value) || 0;
            
            let calculated = 0;
            if (amountType === 'percentage') {
                calculated = (grandTotal * amount) / 100;
                totalPercentage += amount;
            } else {
                calculated = amount;
                if (grandTotal > 0) {
                    totalPercentage += (amount / grandTotal) * 100;
                }
            }
            
            totalAmount += calculated;
            row.querySelector('.milestone-calculated').value = `₹${calculated.toFixed(2)}`;
        });

        document.getElementById('totalPercentage').textContent = `${totalPercentage.toFixed(1)}%`;
        document.getElementById('totalMilestoneAmount').textContent = `₹${totalAmount.toFixed(2)}`;

        // Validation warning
        const percentageBadge = document.getElementById('totalPercentage');
        const amountBadge = document.getElementById('totalMilestoneAmount');
        
        if (Math.abs(totalPercentage - 100) > 0.1) {
            percentageBadge.className = 'badge bg-warning';
        } else {
            percentageBadge.className = 'badge bg-success';
        }

        if (Math.abs(totalAmount - grandTotal) > 0.01) {
            amountBadge.className = 'badge bg-warning';
        } else {
            amountBadge.className = 'badge bg-success';
        }
    }

    // Listen for milestone changes
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('milestone-amount') || 
            e.target.classList.contains('milestone-amount-type')) {
            calculateMilestoneTotals();
        }
    });

    // Collect milestones data
    function collectMilestones() {
        const milestones = [];
        document.querySelectorAll('.milestone-row').forEach((row, index) => {
            milestones.push({
                title: row.querySelector('.milestone-title').value,
                description: row.querySelector('.milestone-description').value || null,
                amount_type: row.querySelector('.milestone-amount-type').value,
                amount: parseFloat(row.querySelector('.milestone-amount').value),
                due_date: row.querySelector('.milestone-due-date').value || null,
            });
        });
        return milestones;
    }

    // Add item
    document.getElementById('addItemBtn').addEventListener('click', function() {
        const template = `
            <div class="item-row card mb-2 p-3" data-index="${itemIndex}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small">Description</label>
                        <input type="text" class="form-control form-control-sm item-description" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Quantity</label>
                        <input type="number" class="form-control form-control-sm item-quantity" value="1" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Unit</label>
                        <input type="text" class="form-control form-control-sm item-unit" value="unit">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Rate (₹)</label>
                        <input type="number" class="form-control form-control-sm item-rate" value="0" min="0" step="0.01" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Amount (₹)</label>
                        <input type="number" class="form-control form-control-sm item-amount" value="0" readonly>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        
        document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', template);
        itemIndex++;
        calculateTotals();
    });

    // Remove item
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.item-row').remove();
            calculateTotals();
        }
    });

    // Calculate item amount
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-rate')) {
            const row = e.target.closest('.item-row');
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const rate = parseFloat(row.querySelector('.item-rate').value) || 0;
            row.querySelector('.item-amount').value = (quantity * rate).toFixed(2);
            calculateTotals();
        }

        if (e.target.id === 'tax' || e.target.id === 'discount') {
            calculateTotals();
        }

        if (e.target.id === 'notes') {
            document.getElementById('notesCount').textContent = e.target.value.length;
        }
    });

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-amount').forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });

        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const grandTotal = subtotal + tax - discount;

        document.getElementById('subtotalDisplay').textContent = `₹${subtotal.toFixed(2)}`;
        document.getElementById('totalAmount').value = subtotal.toFixed(2);
        document.getElementById('grandTotalDisplay').textContent = `₹${grandTotal.toFixed(2)}`;
        document.getElementById('grandTotal').value = grandTotal.toFixed(2);
        
        // Recalculate milestones when total changes
        calculateMilestoneTotals();
    }

    function collectItems() {
        const items = [];
        document.querySelectorAll('.item-row').forEach(row => {
            items.push({
                description: row.querySelector('.item-description').value,
                quantity: parseFloat(row.querySelector('.item-quantity').value),
                unit: row.querySelector('.item-unit').value,
                rate: parseFloat(row.querySelector('.item-rate').value),
                amount: parseFloat(row.querySelector('.item-amount').value),
            });
        });
        return items;
    }

    async function saveQuotation(shouldSend = false) {
        const offerId = document.querySelector('[name="offer_id"]').value;
        const quotationId = document.getElementById('quotationId').value;
        const paymentMode = document.getElementById('paymentMode').value;
        
        const data = {
            offer_id: parseInt(offerId),
            items: collectItems(),
            total_amount: parseFloat(document.getElementById('totalAmount').value),
            tax: parseFloat(document.getElementById('tax').value) || 0,
            discount: parseFloat(document.getElementById('discount').value) || 0,
            grand_total: parseFloat(document.getElementById('grandTotal').value),
            payment_mode: paymentMode,
            notes: document.getElementById('notes').value,
        };

        // Add milestones if milestone payment mode
        if (paymentMode === 'milestone') {
            data.milestones = collectMilestones();
            
            // Validate milestones
            if (data.milestones.length === 0) {
                document.getElementById('alertContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> Please add at least one milestone for milestone payment mode.
                    </div>`;
                return;
            }

            // Validate total
            const totalPercentage = parseFloat(document.getElementById('totalPercentage').textContent);
            const totalAmount = parseFloat(document.getElementById('totalMilestoneAmount').textContent.replace('₹', '').replace(',', ''));
            
            if (Math.abs(totalPercentage - 100) > 0.1 || Math.abs(totalAmount - data.grand_total) > 0.01) {
                if (!confirm('Milestone totals do not match quotation total. Do you want to continue?')) {
                    return;
                }
            }
        }

        document.getElementById('spinner').classList.remove('d-none');
        document.getElementById('alertContainer').innerHTML = '';

        try {
            // Create or update quotation
            const saveResponse = await fetch(`/api/v1/offers/${offerId}/quotations`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const saveResult = await saveResponse.json();
            if (!saveResponse.ok) throw new Error(saveResult.message);

            const newQuotationId = saveResult.data.id;
            document.getElementById('quotationId').value = newQuotationId;
            document.getElementById('generatePdfBtn').disabled = false;

            // Send if requested
            if (shouldSend) {
                const sendResponse = await fetch(`/api/v1/quotations/${newQuotationId}/send`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                        'Accept': 'application/json'
                    }
                });

                const sendResult = await sendResponse.json();
                if (!sendResponse.ok) throw new Error(sendResult.message);

                document.getElementById('alertContainer').innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Quotation sent successfully!
                    </div>`;
            } else {
                document.getElementById('alertContainer').innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Quotation saved as draft!
                    </div>`;
            }

            setTimeout(() => window.location.href = '/vendor/quotations', 1500);
        } catch (error) {
            document.getElementById('alertContainer').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message}
                </div>`;
        } finally {
            document.getElementById('spinner').classList.add('d-none');
        }
    }

    document.getElementById('saveDraftBtn').addEventListener('click', () => saveQuotation(false));
    document.getElementById('saveAndSendBtn').addEventListener('click', () => saveQuotation(true));

    // Initial calculation
    calculateTotals();
    document.getElementById('notesCount').textContent = document.getElementById('notes').value.length;
});
</script>
@endsection
