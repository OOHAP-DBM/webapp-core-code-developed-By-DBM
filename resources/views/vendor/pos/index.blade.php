@extends('layouts.vendor')

@section('page-title', 'Point of Sale')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Point of Sale</h2>
        <p class="text-muted mb-0">Create invoices and manage billing</p>
    </div>
    <button class="btn btn-vendor-primary" onclick="startNewInvoice()">
        <i class="bi bi-plus-circle me-2"></i>New Invoice
    </button>
</div>

<div class="row g-4">
    <!-- Invoice Creator (Left Side) -->
    <div class="col-lg-8">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h6 class="vendor-card-title mb-0">Create Invoice</h6>
            </div>
            <div class="vendor-card-body">
                <form id="invoiceForm">
                    @csrf
                    
                    <!-- Customer Selection -->
                    <div class="mb-4">
                        <label class="form-label">Customer *</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="customerSearch" 
                                   placeholder="Search customer by name, email or phone">
                            <button class="btn btn-outline-secondary" type="button" 
                                    data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <input type="hidden" name="customer_id" id="customerId">
                        <div id="customerInfo" class="mt-2"></div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" name="invoice_number" 
                                   value="INV-{{ date('Y') }}-{{ str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) }}" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Invoice Date *</label>
                            <input type="date" class="form-control" name="invoice_date" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" 
                                   value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                        </div>
                    </div>

                    <!-- Line Items -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label mb-0">Invoice Items</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLineItem()">
                                <i class="bi bi-plus"></i> Add Item
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="lineItemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%;">Description</th>
                                        <th style="width: 15%;">Quantity</th>
                                        <th style="width: 20%;">Rate (₹)</th>
                                        <th style="width: 20%;">Amount (₹)</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="lineItemsBody">
                                    <!-- Line items will be added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2" 
                                  placeholder="Additional notes or terms and conditions"></textarea>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Invoice Summary (Right Side) -->
    <div class="col-lg-4">
        <div class="vendor-card sticky-top" style="top: 90px;">
            <div class="vendor-card-header">
                <h6 class="vendor-card-title mb-0">Summary</h6>
            </div>
            <div class="vendor-card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <strong id="subtotalAmount">₹0.00</strong>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <label class="text-muted mb-0">
                            <input type="checkbox" id="enableDiscount"> Discount:
                        </label>
                        <div id="discountInputs" style="display: none;">
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <input type="number" class="form-control" id="discountValue" 
                                       min="0" step="0.01" placeholder="0">
                                <select class="form-select" id="discountType" style="width: 50px;">
                                    <option value="percent">%</option>
                                    <option value="fixed">₹</option>
                                </select>
                            </div>
                        </div>
                        <strong id="discountAmount">₹0.00</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <label class="text-muted mb-0">
                            <input type="checkbox" id="enableTax"> Tax (GST):
                        </label>
                        <div id="taxInputs" style="display: none;">
                            <input type="number" class="form-control form-control-sm" 
                                   id="taxRate" min="0" max="100" value="18" 
                                   style="width: 80px;" placeholder="%">
                        </div>
                        <strong id="taxAmount">₹0.00</strong>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-bold">Total Amount:</span>
                    <h4 class="text-vendor-primary mb-0" id="totalAmount">₹0.00</h4>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-vendor-primary btn-lg" onclick="saveInvoice()">
                        <i class="bi bi-save me-2"></i>Save Invoice
                    </button>
                    <button type="button" class="btn btn-outline-primary" onclick="previewInvoice()">
                        <i class="bi bi-eye me-2"></i>Preview
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetInvoice()">
                        <i class="bi bi-x-circle me-2"></i>Clear
                    </button>
                </div>

                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Invoice will be saved as draft
                    </small>
                </div>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="vendor-card mt-3">
            <div class="vendor-card-header">
                <h6 class="vendor-card-title mb-0">Recent Invoices</h6>
                <a href="{{ route('vendor.pos.history') }}" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="vendor-card-body">
                @forelse($recentInvoices ?? [] as $invoice)
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div>
                            <div class="fw-semibold">{{ $invoice->invoice_number }}</div>
                            <small class="text-muted">{{ $invoice->customer->name ?? 'N/A' }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold">₹{{ number_format($invoice->total_amount, 2) }}</div>
                            <span class="badge badge-sm 
                                @if($invoice->status === 'paid') bg-success
                                @elseif($invoice->status === 'pending') bg-warning text-dark
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center small mb-0">No recent invoices</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCustomerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vendor-primary">Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let lineItemCounter = 0;

// Add line item
function addLineItem() {
    lineItemCounter++;
    const html = `
        <tr data-item-id="${lineItemCounter}">
            <td>
                <input type="text" class="form-control" name="items[${lineItemCounter}][description]" 
                       placeholder="Item description" required>
            </td>
            <td>
                <input type="number" class="form-control quantity" name="items[${lineItemCounter}][quantity]" 
                       min="1" value="1" onchange="calculateTotal()" required>
            </td>
            <td>
                <input type="number" class="form-control rate" name="items[${lineItemCounter}][rate]" 
                       min="0" step="0.01" placeholder="0.00" onchange="calculateTotal()" required>
            </td>
            <td>
                <input type="text" class="form-control amount" readonly value="₹0.00">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLineItem(${lineItemCounter})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    document.getElementById('lineItemsBody').insertAdjacentHTML('beforeend', html);
}

// Remove line item
function removeLineItem(id) {
    document.querySelector(`tr[data-item-id="${id}"]`).remove();
    calculateTotal();
}

// Calculate totals
function calculateTotal() {
    let subtotal = 0;
    
    document.querySelectorAll('#lineItemsBody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.quantity').value) || 0;
        const rate = parseFloat(row.querySelector('.rate').value) || 0;
        const amount = qty * rate;
        row.querySelector('.amount').value = '₹' + amount.toFixed(2);
        subtotal += amount;
    });
    
    document.getElementById('subtotalAmount').textContent = '₹' + subtotal.toFixed(2);
    
    // Calculate discount
    let discount = 0;
    if (document.getElementById('enableDiscount').checked) {
        const discountValue = parseFloat(document.getElementById('discountValue').value) || 0;
        const discountType = document.getElementById('discountType').value;
        discount = discountType === 'percent' ? (subtotal * discountValue / 100) : discountValue;
    }
    document.getElementById('discountAmount').textContent = '-₹' + discount.toFixed(2);
    
    // Calculate tax
    let tax = 0;
    if (document.getElementById('enableTax').checked) {
        const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
        tax = (subtotal - discount) * taxRate / 100;
    }
    document.getElementById('taxAmount').textContent = '₹' + tax.toFixed(2);
    
    // Calculate total
    const total = subtotal - discount + tax;
    document.getElementById('totalAmount').textContent = '₹' + total.toFixed(2);
}

// Toggle inputs
document.getElementById('enableDiscount')?.addEventListener('change', function() {
    document.getElementById('discountInputs').style.display = this.checked ? 'block' : 'none';
    calculateTotal();
});

document.getElementById('enableTax')?.addEventListener('change', function() {
    document.getElementById('taxInputs').style.display = this.checked ? 'block' : 'none';
    calculateTotal();
});

document.getElementById('discountValue')?.addEventListener('input', calculateTotal);
document.getElementById('discountType')?.addEventListener('change', calculateTotal);
document.getElementById('taxRate')?.addEventListener('input', calculateTotal);

// Save invoice
function saveInvoice() {
    const form = document.getElementById('invoiceForm');
    const formData = new FormData(form);
    
    fetch('{{ route("vendor.pos.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Invoice saved successfully!');
            window.location.reload();
        }
    });
}

// Preview invoice
function previewInvoice() {
    alert('Preview functionality will open invoice in new tab');
}

// Reset form
function resetInvoice() {
    if (confirm('Clear all invoice data?')) {
        document.getElementById('invoiceForm').reset();
        document.getElementById('lineItemsBody').innerHTML = '';
        calculateTotal();
    }
}

// Start new invoice
function startNewInvoice() {
    resetInvoice();
    addLineItem();
}

// Initialize with one line item
addLineItem();
</script>
@endpush
