@extends('layouts.admin')

@section('title', 'Currency Configuration')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Currency Configuration</h1>
            <p class="text-muted">Manage multi-currency settings and exchange rates</p>
        </div>
        <a href="{{ route('admin.currency.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Currency
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Default Currency Info -->
    @if($defaultCurrency)
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Default Currency:</strong> {{ $defaultCurrency->name }} ({{ $defaultCurrency->code }}) {{ $defaultCurrency->symbol }}
        <br>
        <small>All amounts in the system will be displayed using this currency by default.</small>
    </div>
    @endif

    <!-- Exchange Rate Update Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-exchange-alt"></i> Quick Exchange Rate Update</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.currency.update-rates') }}" method="POST" id="ratesForm">
                @csrf
                <div class="row">
                    @foreach($currencies as $currency)
                        @if($currency->is_active)
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ $currency->code }}</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ $currency->symbol }}</span>
                                <input type="number" 
                                       class="form-control" 
                                       name="rates[{{ $currency->code }}]" 
                                       value="{{ $currency->exchange_rate }}"
                                       step="0.000001"
                                       min="0.000001">
                            </div>
                            <small class="text-muted">1 {{ $currency->code }} = ? INR</small>
                        </div>
                        @endif
                    @endforeach
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Update All Rates
                </button>
            </form>
        </div>
    </div>

    <!-- Currencies Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-coins"></i> Configured Currencies</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Symbol</th>
                            <th>Format Example</th>
                            <th>Exchange Rate</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($currencies as $currency)
                        <tr>
                            <td><strong>{{ $currency->code }}</strong></td>
                            <td>{{ $currency->name }}</td>
                            <td><span class="fs-5">{{ $currency->symbol }}</span></td>
                            <td><code>{{ $currency->format(1234.56) }}</code></td>
                            <td>
                                @if($currency->code === 'INR')
                                    <span class="badge bg-primary">Base</span>
                                @else
                                    {{ number_format($currency->exchange_rate, 6) }}
                                @endif
                            </td>
                            <td>{{ $currency->country_code ?? '-' }}</td>
                            <td>
                                <form action="{{ route('admin.currency.toggle-active', $currency) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $currency->is_active ? 'success' : 'secondary' }}">
                                        {{ $currency->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                @if($currency->is_default)
                                    <span class="badge bg-warning">Default</span>
                                @else
                                    <form action="{{ route('admin.currency.set-default', $currency) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Set Default</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.currency.edit', $currency) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$currency->is_default)
                                    <form action="{{ route('admin.currency.destroy', $currency) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this currency?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="text-muted mb-3">No currencies configured yet.</p>
                                <a href="{{ route('admin.currency.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add First Currency
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-warning mt-4">
        <h6><i class="fas fa-exclamation-triangle"></i> Important Notes:</h6>
        <ul class="mb-0">
            <li>Exchange rates should be relative to your base currency (INR in this system)</li>
            <li>Only one currency can be set as default at a time</li>
            <li>Cannot delete or deactivate the default currency</li>
            <li>Update exchange rates regularly for accurate conversions</li>
            <li>All Purchase Orders and Invoices will use these currency settings</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-submit form on rate change (debounced)
let rateTimeout;
document.querySelectorAll('#ratesForm input').forEach(input => {
    input.addEventListener('input', function() {
        clearTimeout(rateTimeout);
        rateTimeout = setTimeout(() => {
            // Could auto-save here
        }, 1000);
    });
});
</script>
@endpush
