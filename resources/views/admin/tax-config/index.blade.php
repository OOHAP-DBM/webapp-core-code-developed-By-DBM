@extends('layouts.admin')

@section('title', 'Tax Configuration')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Tax Configuration</h1>
            <p class="text-muted">Manage GST, TCS, TDS and general tax settings</p>
        </div>
        <div>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#testCalculatorModal">
                <i class="fas fa-calculator"></i> Test Calculator
            </button>
            <button type="button" class="btn btn-warning" onclick="resetDefaults()">
                <i class="fas fa-undo"></i> Reset Defaults
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">GST Status</h6>
                    <h3 class="mb-0">
                        @if($gstConfigs->where('key', 'gst_enabled')->first()?->getTypedValue())
                            <i class="fas fa-check-circle"></i> Enabled
                        @else
                            <i class="fas fa-times-circle"></i> Disabled
                        @endif
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">TCS Status</h6>
                    <h3 class="mb-0">
                        @if($tcsConfigs->where('key', 'tcs_enabled')->first()?->getTypedValue())
                            <i class="fas fa-check-circle"></i> Enabled
                        @else
                            <i class="fas fa-times-circle"></i> Disabled
                        @endif
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">TDS Status</h6>
                    <h3 class="mb-0">
                        @if($tdsConfigs->where('key', 'tds_enabled')->first()?->getTypedValue())
                            <i class="fas fa-check-circle"></i> Enabled
                        @else
                            <i class="fas fa-times-circle"></i> Disabled
                        @endif
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="text-uppercase">Auto Calculate</h6>
                    <h3 class="mb-0">
                        @if($generalConfigs->where('key', 'auto_calculate_taxes')->first()?->getTypedValue())
                            <i class="fas fa-check-circle"></i> Enabled
                        @else
                            <i class="fas fa-times-circle"></i> Disabled
                        @endif
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- GST Configuration -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-percentage"></i> GST Configuration</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Current Value</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gstConfigs as $config)
                        <tr>
                            <td><strong>{{ $config->name }}</strong></td>
                            <td>
                                <span class="config-value" data-key="{{ $config->key }}">
                                    @if($config->data_type === 'boolean')
                                        <span class="badge bg-{{ $config->getTypedValue() ? 'success' : 'secondary' }}">
                                            {{ $config->getTypedValue() ? 'Yes' : 'No' }}
                                        </span>
                                    @elseif($config->data_type === 'float')
                                        {{ number_format($config->getTypedValue(), 2) }}%
                                    @else
                                        <code>{{ $config->value ?: 'Not Set' }}</code>
                                    @endif
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $config->description }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $config->is_active ? 'success' : 'warning' }}">
                                    {{ $config->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.tax-config.edit', $config) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TCS Configuration -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-receipt"></i> TCS Configuration</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Current Value</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tcsConfigs as $config)
                        <tr>
                            <td><strong>{{ $config->name }}</strong></td>
                            <td>
                                @if($config->data_type === 'boolean')
                                    <span class="badge bg-{{ $config->getTypedValue() ? 'success' : 'secondary' }}">
                                        {{ $config->getTypedValue() ? 'Yes' : 'No' }}
                                    </span>
                                @elseif($config->data_type === 'float')
                                    {{ number_format($config->getTypedValue(), 2) }}%
                                @elseif($config->data_type === 'integer')
                                    ₹{{ number_format($config->getTypedValue()) }}
                                @elseif($config->data_type === 'array')
                                    <code>{{ json_encode($config->getTypedValue()) }}</code>
                                @else
                                    <code>{{ $config->value }}</code>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $config->description }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $config->is_active ? 'success' : 'warning' }}">
                                    {{ $config->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.tax-config.edit', $config) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TDS Configuration -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> TDS Configuration</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Current Value</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tdsConfigs as $config)
                        <tr>
                            <td><strong>{{ $config->name }}</strong></td>
                            <td>
                                @if($config->data_type === 'boolean')
                                    <span class="badge bg-{{ $config->getTypedValue() ? 'success' : 'secondary' }}">
                                        {{ $config->getTypedValue() ? 'Yes' : 'No' }}
                                    </span>
                                @elseif($config->data_type === 'integer')
                                    ₹{{ number_format($config->getTypedValue()) }}
                                @else
                                    <code>{{ $config->value }}</code>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $config->description }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $config->is_active ? 'success' : 'warning' }}">
                                    {{ $config->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.tax-config.edit', $config) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- General Configuration -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-cog"></i> General Tax Settings</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Setting</th>
                            <th>Current Value</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($generalConfigs as $config)
                        <tr>
                            <td><strong>{{ $config->name }}</strong></td>
                            <td>
                                @if($config->data_type === 'boolean')
                                    <span class="badge bg-{{ $config->getTypedValue() ? 'success' : 'secondary' }}">
                                        {{ $config->getTypedValue() ? 'Yes' : 'No' }}
                                    </span>
                                @else
                                    <code>{{ $config->value }}</code>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $config->description }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $config->is_active ? 'success' : 'warning' }}">
                                    {{ $config->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.tax-config.edit', $config) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Test Calculator Modal -->
<div class="modal fade" id="testCalculatorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tax Calculator Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testCalcForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control" id="test_amount" value="10000" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Transaction Type</label>
                                <select class="form-select" id="test_transaction_type">
                                    <option value="purchase_order">Purchase Order</option>
                                    <option value="invoice">Invoice</option>
                                    <option value="payout">Payout</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Customer State Code</label>
                                <input type="text" class="form-control" id="test_customer_state" value="MH" maxlength="2">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Vendor State Code</label>
                                <input type="text" class="form-control" id="test_vendor_state" value="MH" maxlength="2">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Calculate</button>
                </form>

                <div id="testResults" class="mt-4" style="display: none;">
                    <h6>Calculation Results:</h6>
                    <div class="alert alert-info">
                        <pre id="resultJson" style="max-height: 400px; overflow-y: auto;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Test calculator
document.getElementById('testCalcForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const data = {
        amount: document.getElementById('test_amount').value,
        transaction_type: document.getElementById('test_transaction_type').value,
        customer_state_code: document.getElementById('test_customer_state').value,
        vendor_state_code: document.getElementById('test_vendor_state').value,
    };

    try {
        const response = await fetch('{{ route('admin.tax-config.test-calculation') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        document.getElementById('resultJson').textContent = JSON.stringify(result, null, 2);
        document.getElementById('testResults').style.display = 'block';
    } catch (error) {
        alert('Error calculating tax: ' + error.message);
    }
});

// Reset defaults
function resetDefaults() {
    if (confirm('This will reset all tax configurations to default values. Continue?')) {
        window.location.href = '{{ route('admin.tax-config.reset-defaults') }}';
    }
}
</script>
@endpush
@endsection
