@extends('layouts.admin')

@section('title', 'Requested Vendors')
@section('page_title', 'Vendor Management')

@section('breadcrumb')
    <span class="mx-1.5">></span>
    <span class="text-[#949291]">Vendor Management</span>
    <span class="mx-1.5">></span>
    <span class="text-[#1E1B18] font-bold italic">Requested Vendors</span>
@endsection

@section('content')
<div class="min-h-screen bg-[#F9FAFB] -m-6 p-6 font-poppins">
    
    {{-- Search & Filter Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-end gap-4 mb-6">
        <form method="GET" action="" class="flex items-center gap-3">
            <div class="relative group">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs transition-colors group-focus-within:text-[#10B981]"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vendors..." 
                       class="pl-9 pr-4 py-2 bg-white border border-[#DADADA] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#10B981] w-64 transition-all">
            </div>
            <button type="submit" class="px-4 py-2 bg-[#10B981] text-white rounded-lg text-sm font-bold hover:bg-[#0da673] shadow-sm transition-all">
                Search
            </button>
        </form>
    </div>

    @if($requestedVendors->count() === 0)
        <div class="flex flex-col items-center justify-center bg-white border border-[#DADADA] rounded-2xl py-24 px-6 shadow-sm">
            <h2 class="text-xl font-bold text-[#1E1B18] mb-3 text-center">No Vendor Request</h2>
            <p class="text-[#949291] mb-8 text-center max-w-sm leading-relaxed">
                There are currently no pending vendor requests.
            </p>
        </div>
    @else
        <div class="bg-white border border-[#DADADA] rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F9FAFB]">
                            <th class="px-6 py-4 text-[11px] font-bold text-[#949291] uppercase border-b border-[#DADADA] tracking-wider">Company Details</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#949291] uppercase border-b border-[#DADADA] tracking-wider">GSTIN</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#949291] uppercase border-b border-[#DADADA] tracking-wider">PAN Number</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-[#949291] uppercase border-b border-[#DADADA] tracking-wider text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DADADA]">
                        @foreach($requestedVendors as $vendor)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#F0FDF4] border border-[#DCFCE7] flex items-center justify-center text-[#10B981] font-bold text-sm">
                                        {{ strtoupper(substr($vendor->company_name, 0, 1)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[13px] font-bold text-[#1E1B18]">{{ $vendor->company_name }}</span>
                                        <span class="text-[11px] text-[#10B981] font-semibold tracking-tight">Verification Pending</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[13px] font-bold text-[#1E1B18]">{{ $vendor->gstin }}</td>
                            <td class="px-6 py-4 text-[13px] text-[#1E1B18] font-semibold">{{ $vendor->pan }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-3">
                                    <button type="button" class="approve-btn w-9 h-9 rounded-lg bg-[#ECFDF5] text-[#10B981] flex items-center justify-center hover:bg-[#10B981] hover:text-white transition-all shadow-sm" 
                                            data-vendor-id="{{ $vendor->id }}" title="Approve">
                                        <i class="fas fa-check-circle text-[16px]"></i>
                                    </button>
                                    <button type="button" class="reject-btn w-9 h-9 rounded-lg bg-[#FEF2F2] text-[#EF4444] flex items-center justify-center hover:bg-[#EF4444] hover:text-white transition-all shadow-sm" 
                                            data-vendor-id="{{ $vendor->id }}" title="Reject">
                                        <i class="fas fa-times-circle text-[16px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5 flex items-center justify-between border-t border-[#DADADA] bg-white">
                <span class="text-[12px] text-[#949291] font-medium">
                    Showing <span class="text-[#1E1B18]">{{ $requestedVendors->firstItem() }}</span> to <span class="text-[#1E1B18]">{{ $requestedVendors->lastItem() }}</span> of {{ $requestedVendors->total() }} entries
                </span>
                <div>{{ $requestedVendors->links() }}</div>
            </div>
        </div>
    @endif
</div>

{{-- MODALS SECTION --}}

<div id="commission-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-[400px] overflow-hidden">
        <div class="flex justify-end p-4">
            <button class="text-gray-400 hover:text-gray-600 close-modal" data-modal="commission-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-8 pb-10 text-center">
            <h3 class="text-xl font-semibold text-[#1E1B18] mb-8">Set a Vendor Commission</h3>
            <form id="commission-form">
                <div class="flex items-center justify-center gap-4 mb-8">
                    <span class="text-gray-500 text-sm font-medium">From</span>
                    <div class="w-20 px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-400 text-sm font-bold italic">10%</div>
                    <span class="text-gray-500 text-sm font-medium">To</span>
                    <div class="relative w-24">
                        <input type="number" id="commission_percentage" name="commission_percentage" step="0.01" min="0" max="100"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-bold text-[#1E1B18] focus:outline-none focus:ring-1 focus:ring-[#10B981]" 
                               placeholder="20%" required>
                    </div>
                </div>
                <input type="hidden" id="commission_vendor_id">
                <button type="submit" id="commission_apply_btn" class="w-full py-3 bg-[#10B981] text-white rounded-lg font-bold text-base hover:bg-[#0da673] transition-all shadow-md">
                    Apply
                </button>
            </form>
        </div>
    </div>
</div>

<div id="success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-[400px] overflow-hidden">
        <div class="flex justify-end p-4">
            <button class="text-gray-400 hover:text-gray-600 close-modal" data-modal="success-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="px-8 pb-10 text-center">
            <div class="flex justify-center mb-6">
                <div class="w-24 h-24 bg-[#ECFDF5] rounded-full flex items-center justify-center">
                    <div class="w-16 h-16 bg-[#10B981] rounded-full flex items-center justify-center shadow-lg">
                        <i class="fas fa-check text-white text-3xl"></i>
                    </div>
                </div>
            </div>
            <h3 class="text-lg font-bold text-[#1E1B18] mb-8 leading-tight">Vendor has been added successfully</h3>
            <button id="success-done-btn" class="w-full py-3.5 bg-[#1E1B18] text-white rounded-xl font-bold hover:bg-black transition-all shadow-md">
                Done
            </button>
        </div>
    </div>
</div>

<div id="reject-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden backdrop-blur-sm">
    <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-[340px] p-8 text-center">
        <h3 class="text-[18px] font-bold text-[#1E1B18] mb-2">Are you sure?</h3>
        <p class="text-[12px] text-[#949291] mb-6">You are not able to recover the Requested Vendor</p>
        <form id="reject-form" class="space-y-4">
            <input type="hidden" id="reject_vendor_id">
            <input type="text" id="reject_reason" class="w-full px-4 py-3 border border-[#DADADA] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="Reason for rejection" required>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-[#EF4444] text-white rounded-xl font-bold hover:bg-red-600">Yes, Reject</button>
                <button type="button" class="flex-1 py-3 bg-gray-100 text-[#1E1B18] rounded-xl font-bold close-modal" data-modal="reject-modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Selectors
    const commissionModal = document.getElementById('commission-modal');
    const successModal = document.getElementById('success-modal');
    const rejectModal = document.getElementById('reject-modal');
    
    // Close Logic
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.modal).classList.add('hidden');
        });
    });

    // 1. OPEN COMMISSION MODAL
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('commission_vendor_id').value = btn.dataset.vendorId;
            document.getElementById('commission_percentage').value = '';
            commissionModal.classList.remove('hidden');
        });
    });

   // 2. SUBMIT COMMISSION (Transition to Success)
    document.getElementById('commission-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const vendorId = document.getElementById('commission_vendor_id').value;
        const commission = document.getElementById('commission_percentage').value;
        const applyBtn = document.getElementById('commission_apply_btn');

        applyBtn.disabled = true;
        applyBtn.innerText = 'Applying...';

        fetch(`/admin/vendors/${vendorId}/approve`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken, 
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ commission_percentage: commission })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok && data.success) {
                // SUCCESS
                commissionModal.classList.add('hidden');
                successModal.classList.remove('hidden');
            } else {
                // FAILURE (Validation or Logic error)
                alert(data.message || 'Validation error occurred');
                console.error(data.errors); // Log Laravel validation errors
                applyBtn.disabled = false;
                applyBtn.innerText = 'Apply';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('A network error occurred.');
            applyBtn.disabled = false;
            applyBtn.innerText = 'Apply';
        });
    });
    // 3. DONE BUTTON (Refresh to remove from list)
    document.getElementById('success-done-btn').addEventListener('click', function() {
        window.location.reload();
    });

   // REJECT LOGIC
    
    // 1. THIS PART WAS MISSING: Open the modal when clicking the red button
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const vendorId = btn.getAttribute('data-vendor-id');
            document.getElementById('reject_vendor_id').value = vendorId;
            document.getElementById('reject_reason').value = ''; // Clear previous reason
            rejectModal.classList.remove('hidden');
        });
    });

    // 2. Handle the form submission (Keep your existing improved fetch logic here)
    document.getElementById('reject-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const vendorId = document.getElementById('reject_vendor_id').value;
        const reason = document.getElementById('reject_reason').value;
        const submitBtn = e.target.querySelector('button[type="submit"]');

        submitBtn.disabled = true;
        submitBtn.innerText = 'Rejecting...';

        fetch(`/admin/vendors/${vendorId}/reject`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken, 
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok && data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error: Could not reject vendor.');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Yes, Reject';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('A network error occurred.');
            submitBtn.disabled = false;
            submitBtn.innerText = 'Yes, Reject';
        });
    });
});
</script>
@endpush

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
    .font-poppins { font-family: 'Poppins', sans-serif; }
</style>
@endsection