@extends('layouts.vendor')

@section('page-title', 'Payouts')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <div class="bg-white rounded-md shadow">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 px-4 sm:px-6 py-4 bg-primary rounded-t-xl">
            <h4 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bi bi-cash-stack"></i> Payouts
            </h4>
            <button class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                <i class="bi bi-cash-stack"></i> Request Payout
            </button>
        </div>

        <div class="p-4 sm:p-6 space-y-6">
            <!-- Balance Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-green-100 text-green-600">
                        <i class="bi bi-wallet2 text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Available Balance</div>
                        <div class="text-lg font-bold text-gray-800">₹{{ number_format($balance['available'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-400">Ready to withdraw</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-yellow-100 text-yellow-600">
                        <i class="bi bi-hourglass-split text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Pending</div>
                        <div class="text-lg font-bold text-gray-800">₹{{ number_format($balance['pending'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-400">Processing</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-blue-100 text-blue-600">
                        <i class="bi bi-cash-coin text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">This Month</div>
                        <div class="text-lg font-bold text-gray-800">₹{{ number_format($balance['this_month'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-400">{{ date('F') }} earnings</div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex-shrink-0 flex items-center justify-center bg-indigo-100 text-indigo-600">
                        <i class="bi bi-graph-up text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 font-medium">Total Earned</div>
                        <div class="text-lg font-bold text-gray-800">₹{{ number_format($balance['total_earned'] ?? 0, 2) }}</div>
                        <div class="text-xs text-gray-400">All time</div>
                    </div>
                </div>
            </div>

            <!-- Bank Account -->
            <div class="bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                    <h6 class="font-semibold text-gray-700 flex items-center gap-2 text-sm">
                        <i class="bi bi-bank"></i> Bank Account
                    </h6>
                    <button class="text-xs bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition" data-bs-toggle="modal" data-bs-target="#bankDetailsModal">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                </div>
                <div class="p-4">
                    @if($bankDetails ?? false)
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Account Holder</div>
                                <div class="font-semibold text-gray-800 text-sm">{{ $bankDetails->account_holder_name }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Account Number</div>
                                <div class="font-semibold text-gray-800 text-sm">XXXX XXXX {{ substr($bankDetails->account_number, -4) }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">IFSC Code</div>
                                <div class="font-semibold text-gray-800 text-sm">{{ $bankDetails->ifsc_code }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">Bank Name</div>
                                <div class="font-semibold text-gray-800 text-sm">{{ $bankDetails->bank_name }}</div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm">
                            <i class="bi bi-exclamation-triangle"></i>
                            Please add your bank account details to receive payouts
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payout History -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                    <h6 class="font-semibold text-gray-700 text-sm">Payout History</h6>
                    <div class="flex items-center gap-2">
                        <select class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-1 focus:ring-primary focus:outline-none" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="failed">Failed</option>
                        </select>
                        <button class="text-xs bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-lg shadow">
                    <table class="min-w-full text-xs sm:text-sm">
                        <thead class="bg-gray-100 text-left">
                            <tr>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Transaction ID</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Date</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Amount</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Method</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Status</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Reference</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 font-semibold text-gray-600 text-xs uppercase whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($payouts ?? [] as $payout)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap font-bold text-gray-800">#{{ $payout->transaction_id }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-gray-600">{{ \Carbon\Carbon::parse($payout->created_at)->format('d M Y, H:i') }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap font-bold text-gray-800">₹{{ number_format($payout->amount, 2) }}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-gray-600">
                                        <i class="bi bi-bank mr-1"></i>
                                        {{ $payout->method === 'bank_transfer' ? 'Bank Transfer' : ucfirst($payout->method) }}
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                        @if($payout->status === 'completed')
                                            <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-700">Completed</span>
                                        @elseif($payout->status === 'pending')
                                            <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-700">Pending</span>
                                        @elseif($payout->status === 'processing')
                                            <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-700">Processing</span>
                                        @elseif($payout->status === 'failed')
                                            <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-700">Failed</span>
                                        @else
                                            <span class="px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst($payout->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                        @if($payout->reference_number)
                                            <span class="text-xs text-gray-500">{{ $payout->reference_number }}</span>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap">
                                        <button class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition" onclick="viewPayout('{{ $payout->id }}')">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                                        <i class="bi bi-inbox text-4xl block mb-2 text-gray-300"></i>
                                        No payout history yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($payouts) && $payouts->hasPages())
                    <div class="pt-4">
                        {{ $payouts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Withdraw Modal -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vendor.payouts.request') }}" method="POST" id="withdrawForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Available Balance: <strong>₹{{ number_format($balance['available'] ?? 0, 2) }}</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" name="amount" 
                                   min="100" max="{{ $balance['available'] ?? 0 }}" 
                                   step="0.01" required>
                        </div>
                        <small class="text-muted">Minimum withdrawal: ₹100</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Withdrawal Method</label>
                        <select class="form-select" name="method" required>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="alert alert-warning mb-0">
                        <small><i class="bi bi-clock me-1"></i> Payouts are processed within 2-3 business days</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vendor-primary">Request Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bank Details Modal -->
<div class="modal fade" id="bankDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bank Account Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vendor.payouts.update-bank') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Account Holder Name *</label>
                        <input type="text" class="form-control" name="account_holder_name" 
                               value="{{ $bankDetails->account_holder_name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number *</label>
                        <input type="text" class="form-control" name="account_number" 
                               value="{{ $bankDetails->account_number ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Account Number *</label>
                        <input type="text" class="form-control" name="account_number_confirmation" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">IFSC Code *</label>
                        <input type="text" class="form-control" name="ifsc_code" 
                               value="{{ $bankDetails->ifsc_code ?? '' }}" 
                               pattern="^[A-Z]{4}0[A-Z0-9]{6}$" required>
                        <small class="text-muted">11 character IFSC code</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Name *</label>
                        <input type="text" class="form-control" name="bank_name" 
                               value="{{ $bankDetails->bank_name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" class="form-control" name="branch_name" 
                               value="{{ $bankDetails->branch_name ?? '' }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vendor-primary">Save Details</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewPayout(id) {
    window.location.href = `/vendor/payouts/${id}`;
}

document.getElementById('statusFilter')?.addEventListener('change', function() {
    const url = new URL(window.location);
    if (this.value) {
        url.searchParams.set('status', this.value);
    } else {
        url.searchParams.delete('status');
    }
    window.location = url;
});

// Form validation
document.getElementById('withdrawForm')?.addEventListener('submit', function(e) {
    const amount = parseFloat(this.querySelector('[name="amount"]').value);
    const available = {{ $balance['available'] ?? 0 }};
    
    if (amount > available) {
        e.preventDefault();
        alert('Insufficient balance. Available: ₹' + available.toFixed(2));
    }
});
</script>
@endpush
