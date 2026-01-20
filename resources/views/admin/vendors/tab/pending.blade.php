<div class="bg-white rounded-xl overflow-hidden">
{{-- DEBUG --}}

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1100px] text-sm">
            <thead class="bg-[#F9FAFB] text-[#6B7280]">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" class="accent-green-600">
                    </th>
                    <th class="px-4 py-3 text-left">S.N</th>
                    <th class="px-4 py-3 text-left">VENDOR NAME</th>
                    <th class="px-4 py-3 text-left">REQUESTED DATE</th>
                    <th class="px-4 py-3 text-left">CITY</th>
                    <th class="px-4 py-3 text-left">EMAIL</th>
                    <th class="px-4 py-3 text-left">PHONE NUMBER</th>
                    <th class="px-4 py-3 text-center">ACTION</th>
                    <th class="px-4 py-3 text-center"></th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($vendors as $i => $vendor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="accent-green-600">
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendors->firstItem() + $i }}
                        </td>

                        <td class="px-4 py-3 font-medium">  
                            <a href="{{ route('admin.vendors.show', $vendor->user->id) }}"
                            class="text-[#2563EB] underline hover:text-blue-800">
                                {{ $vendor->user->name ?? '-' }}
                            </a>
                        </td>


                        <td class="px-4 py-3">
                            {{ $vendor->created_at?->format('M d, Y') }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->city ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->user->email ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->user->phone ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <button
                                class="bg-[#F59E0B] text-white px-6 py-2 rounded-lg text-sm"
                                @click="
                                    let commission = {{ (float) $vendor->commission_percentage ?? 0 }};
                                    if (!commission || commission == 0) {
                                        $dispatch('open-vendor-commission', {
                                            vendorId: {{ $vendor->id }},
                                            vendorName: '{{ $vendor->user->name }}',
                                            commission: 0
                                        });
                                    } else {
                                        $dispatch('open-vendor-commission', {
                                            vendorId: {{ $vendor->id }},
                                            vendorName: '{{ $vendor->user->name }}',
                                            commission: commission
                                        });
                                    }
                                "
                            >
                                Approve
                            </button>


                        </td>

                        <td class="px-4 py-3 text-center relative" x-data="{ open: false }">

                            <!-- Three Dots -->
                            <button
                                @click="open = !open"
                                class="text-gray-500 hover:text-gray-700 text-xl font-bold focus:outline-none"
                            >
                                ⋮
                            </button>

                            <!-- Dropdown -->
                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-6 top-8 w-36 bg-white border border-gray-200 rounded-md shadow-lg z-50"
                            >
                                <button
                                    type="button"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                    onclick="rejectVendor({{ $vendor->id }})"
                                >
                                    Reject Now
                                </button>

                            </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            No vendors found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 text-sm text-[#6B7280]">
        <div class="flex items-center gap-2">
            <select class="border rounded-md px-2 py-1 text-sm">
                <option>10</option>
            </select>
            <span>
                Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }}
                of {{ $vendors->total() }} records
            </span>
        </div>

        <div>
            {{ $vendors->links() }}
        </div>
    </div>

</div>
{{--vendor commision modal--}}
<div
    x-data="vendorCommissionModal()"
    x-cloak
    x-show="open"
    x-transition
    @open-vendor-commission.window="
            $event.detail.commission > 0
                ? approveDirect($event.detail)
                : openModal($event.detail)
        "    
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
    <div
        @click.outside="close()"
        class="bg-[#f5f5f5] w-full max-w-lg rounded-2xl shadow-xl relative px-10 py-8"
    >
        <!-- Close -->
        <button
            @click="close()"
            class="absolute top-5 right-6 text-xl text-gray-700 hover:text-black"
        >
            ✕
        </button>

        <h2 class="text-2xl font-semibold text-center mb-2" x-text="vendorName"></h2>
        <p class="text-center text-gray-700 mb-6">Set a Vendor Commission</p>

        <div class="flex justify-center gap-6 mb-8">
            <div>
                <label class="block text-sm text-gray-600 mb-1">From</label>
                <input type="number" x-model="from"
                       class="w-28 text-center border rounded-md px-3 py-2">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">To</label>
                <input type="number" x-model="to"
                       class="w-28 text-center border rounded-md px-3 py-2">
            </div>
        </div>

        <button
            @click="apply()"
            class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-lg font-medium"
        >
            Apply
        </button>
    </div>
</div>

{{--vendor reject--}}
<div id="reject-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 hidden backdrop-blur-sm">
    <div class="bg-white rounded-[24px] shadow-2xl w-full max-w-[340px] p-8 text-center">
        <h3 class="text-[18px] font-bold text-[#1E1B18] mb-2">Are you sure?</h3>
        <p class="text-[12px] text-[#949291] mb-6">You are not able to recover the Requested Vendor</p>
        <form id="reject-form" class="space-y-4">
            <input type="hidden" id="reject_vendor_id">
            <input type="text" id="reject_reason" class="w-full px-4 py-3 border border-[#DADADA] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-red-500" placeholder="Reason for rejection" required>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-[#EF4444] text-white rounded-xl font-bold hover:bg-red-600">Yes, Reject</button>
                <button type="button" class="flex-1 py-3 bg-gray-100 text-[#1E1B18] rounded-xl font-bold close-modal" data-modal="reject-modal" onclick="closeRejectModal()"
                >Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('vendorCommissionModal', () => ({
            open: false,
            vendorId: null,
            vendorName: '',
            from: 10,
            to: '',

            openModal(detail) {
                this.vendorId = detail.vendorId;
                this.vendorName = detail.vendorName;
                this.from = 10;
                this.to = '';
                this.open = true;
            },

            approveDirect(detail) {
                fetch(`/admin/vendors/${detail.vendorId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        commission_percentage: detail.commission
                    })
                }).then(() => location.reload());
            },

            close() {
                this.open = false;
            },

            apply() {
                if (!this.to || this.to <= 0) {
                    alert('Please enter valid commission');
                    return;
                }

                if (this.to < this.from) {
                    alert('Commission cannot be less than minimum');
                    return;
                }

                fetch(`/admin/vendors/${this.vendorId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        commission_percentage: this.to   // ✅ FINAL VALUE
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
            }

        }));
    });

</script>
<script>
    function closeRejectModal() {
        document.getElementById('reject-modal').classList.add('hidden');
    }
    function rejectVendor(id) {
        document.getElementById('reject_vendor_id').value = id;
        document.getElementById('reject-modal').classList.remove('hidden');
    }
    document.getElementById('reject-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('reject_vendor_id').value;
        const reason = document.getElementById('reject_reason').value;

        fetch(`/admin/vendors/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reason })
        })
        .then(() => location.reload());
    });
</script>

