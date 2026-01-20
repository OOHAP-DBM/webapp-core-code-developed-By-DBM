@extends('layouts.vendor')

@section('content')
<div class="px-6 py-6 bg-white" x-data="enquiryModal()">

    {{-- FILTER BAR --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
          <div class="mb-6">
            <h1 class="text-lg font-bold text-gray-900">
                Enquiry & Manage Offers
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Check all your sent offers to customers, you can track and manage them here
            </p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" class="flex items-center gap-2 flex-1 md:flex-none">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search customer by name, email, mobile number..."
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 flex-1 md:w-72"
                >
                <button
                    type="submit"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400 font-medium"
                >
                    Filter
                </button>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white border border-gray-200  overflow-x-auto shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Sn #</th>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Enquiry ID</th>
                    <!-- <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Offer ID</th> -->
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Customer Name</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs"># of Hoardings</th>
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs"># of Locations</th>
                    <!-- <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Offer Valid Till</th> -->
                    <th class="px-4 py-4 text-left font-semibold text-gray-700 text-xs">Status</th>
                    <th class="px-4 py-4 text-center font-semibold text-gray-700 text-xs">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($enquiries as $index => $enquiry)
                    <tr class="hover:bg-gray-50 transition-colors">

                        {{-- SN --}}
                        <td class="px-4 py-4 text-gray-700">
                            {{ ($enquiries->currentPage() - 1) * $enquiries->perPage() + $index + 1 }}
                        </td>

                        {{-- ENQUIRY ID --}}
                        <td class="px-4 py-4">
                            <button @click="openModal({{ $enquiry->toJson() }})" class="text-green-600 font-semibold hover:text-green-700 hover:underline cursor-pointer bg-transparent border-0 p-0">
                                {{ 'ENQ' . str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}
                            </button>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $enquiry->created_at->format('d M, y') }}
                            </div>
                        </td>

                        {{-- OFFER ID --}}
                        <!-- <td class="px-4 py-4">
                            @php
                                $latestOffer = $enquiry->offers()->latest()->first();
                            @endphp
                            @if($latestOffer)
                                <span class="text-green-600 font-semibold">#{{ $latestOffer->id }}</span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td> -->

                        {{-- CUSTOMER --}}
                        <td class="px-4 py-4">
                            <div class="font-semibold text-gray-900">
                                {{ $enquiry->customer->name ?? 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $enquiry->customer->email ?? '' }}
                            </div>
                        </td>

                        {{-- HOARDINGS --}}
                        <td class="px-4 py-4 text-center">
                            <span class="text-gray-900 font-semibold">
                                {{ $enquiry->items()->count() }}
                            </span>
                        </td>

                        {{-- LOCATIONS --}}
                        <td class="px-4 py-4 max-w-xs">
                            <div class="space-y-1">
                                @forelse($enquiry->locations as $index => $loc)
                                    @if($index < 3)
                                        <div class="text-xs text-gray-600">{{ $index + 1 }}. {{ $loc }}</div>
                                    @elseif($index == 3)
                                        <div class="text-xs text-gray-500 italic">+{{ count($enquiry->locations) - 3 }} more</div>
                                        @break
                                    @endif
                                @empty
                                    <span class="text-gray-400 text-xs">-</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- VALID TILL --}}
                        <!-- <td class="px-4 py-4">
                            @if($enquiry->valid_till)
                                <div class="text-gray-900 font-semibold">{{ $enquiry->valid_till->format('d M, y') }}</div>
                                <div class="text-xs mt-1">
                                    @if($enquiry->validity_status === 'expired')
                                        <span class="text-red-600 font-medium">Expired</span>
                                    @elseif($enquiry->validity_status === 'expiring_soon')
                                        <span class="text-orange-600 font-medium">{{ $enquiry->days_left }} days left</span>
                                    @else
                                        <span class="text-green-600 font-medium">{{ $enquiry->days_left }} days left</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td> -->

                        {{-- STATUS --}}
                        <td class="px-4 py-4">
                            <div class="space-y-1">
                                <div class="text-xs font-semibold {{ $enquiry->status_color['text'] }}">
                                    {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $enquiry->updated_at->format('d M, y | H:i') }}
                                </div>
                            </div>
                        </td>

                        {{-- ACTION --}}
                        <td class="px-4 py-4 text-center">
                            <div class="flex gap-2 justify-center flex-wrap">
                                @if($enquiry->status === 'submitted')
                                    <button @click="openModal({{ $enquiry->toJson() }})"
                                       class="px-4 py-2 bg-gray-900 text-white  text-xs hover:bg-gray-800 font-semibold cursor-pointer whitespace-nowrap">
                                        View Enquiry
                                    </button>
                                @elseif($enquiry->status === 'accepted')
                                    <a href="{{ route('vendor.quotation.create', $enquiry->id) }}"
                                       class="px-4 py-2 bg-green-600 text-white  text-xs hover:bg-green-700 font-semibold inline-block whitespace-nowrap">
                                        Create Quotation
                                    </a>
                                @elseif(in_array($enquiry->status, ['draft', 'responded', 'pending']))
                                    <a href="{{ route('vendor.enquiries.respond', $enquiry->id) }}"
                                       class="px-4 py-2 bg-green-600 text-white  text-xs hover:bg-green-700 font-semibold inline-block whitespace-nowrap">
                                        Send Counter Offer
                                    </a>
                                @else
                                    <button class="px-4 py-2 bg-gray-500 text-white  text-xs font-semibold cursor-not-allowed whitespace-nowrap">
                                        Send Reminder
                                    </button>
                                @endif
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                            <div class="space-y-2">
                                <p class="font-medium">No enquiries found</p>
                                <p class="text-xs">Start by waiting for customers to submit enquiries for your hoardings</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-6 flex items-center justify-between text-sm text-gray-600">
        <div class="font-medium">
            Showing {{ $enquiries->firstItem() ?? 0 }} - {{ $enquiries->lastItem() ?? 0 }} of {{ $enquiries->total() }}
        </div>
        <div>
            {{ $enquiries->links() }}
        </div>
    </div>

    {{-- INCLUDE ENQUIRY DETAILS MODAL --}}
    @include('vendor.enquiries.modals.details')

</div>

<script>
function enquiryModal() {
    return {
        showModal: false,
        enquiryData: null,
        
        openModal(data) {
            this.enquiryData = data;
            this.showModal = true;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.showModal = false;
            this.enquiryData = null;
            document.body.style.overflow = 'auto';
        },
        
        goToDetails() {
            if (this.enquiryData?.id) {
                window.location.href = `/vendor/enquiries/${this.enquiryData.id}`;
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        },
        
        init() {
            // Close modal on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.showModal) {
                    this.closeModal();
                }
            });
        }
    }
}
</script>
@endsection
