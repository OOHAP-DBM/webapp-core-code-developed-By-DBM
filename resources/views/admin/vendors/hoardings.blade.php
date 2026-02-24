@extends('layouts.admin')

@section('title', 'Vendor Hoardings')
@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'Vendors Management', 'route' => route('admin.vendors.index', ['status' => 'approved'])],
    ['label' => $vendor->vendorProfile->company_name ?? $vendor->name ?? 'Vendor'],
    ['label' => 'All Hoardings']
]" />
@endsection

@section('content')
<div class="w-full min-h-screen bg-[#F7F7F7] p-6">
    <!-- Vendor Details Card -->
    <div class="bg-white rounded-md p-5 mb-6 shadow-sm">
        <div class="font-semibold text-lg mb-2">Vendor Details</div>
        <div class="text-sm text-gray-700 leading-6">
            <div><b>Name:</b> {{ $vendor->vendorProfile->contact_person_name ?? $vendor->name ?? '-' }}</div>
            <div><b>Business Name:</b> {{ $vendor->vendorProfile->company_name ?? '-' }}</div>
            <div><b>GSTIN:</b> {{ $vendor->vendorProfile->gstin ?? '-' }}</div>
            <div><b>Mobile Number:</b> {{ $vendor->phone ?? '-' }}</div>
            <div><b>Email:</b> {{ $vendor->email ?? '-' }}</div>
            <div><b>Address:</b> {{ $vendor->vendorProfile->registered_address ?? '-' }}, {{ $vendor->vendorProfile->city ?? '-' }} {{ $vendor->vendorProfile->state ?? '-' }} {{ $vendor->vendorProfile->pincode ?? '' }}</div>
        </div>
    </div>

    <!-- Hoarding Counts & Tabs -->
    <div x-data="{ tab: (new URL(window.location.href).searchParams.get('tab') === 'pending') ? 'pending' : 'approved' }" class="bg-white p-5 py-5 rounded-md">
        <div class="flex items-center gap-4 mb-4">
            <div class="text-lg font-semibold">Total Hoardings ({{ ($approvedHoardings->total() ?? 0) + ($pendingHoardings->total() ?? 0) }})</div>
        </div>
        <div class="flex items-center gap-6 border-b border-[#E5E7EB] mb-4"></div>
        <div class="flex items-center gap-6  mb-4">
            <button :class="tab === 'approved' ? 'pb-3 text-[#2563EB] border-b-2 border-[#2563EB] font-medium' : 'pb-3 text-[#9CA3AF]'" @click="tab = 'approved'; history.replaceState(null, '', '?tab=approved');">Approved ({{ $approvedHoardings->total() ?? 0 }})</button>
            <button :class="tab === 'pending' ? 'pb-3 text-[#2563EB] border-b-2 border-[#2563EB] font-medium' : 'pb-3 text-[#9CA3AF]'" @click="tab = 'pending'; history.replaceState(null, '', '?tab=pending');">Pending Approval ({{ $pendingHoardings->total() ?? 0 }})</button>
        </div>
        <form method="GET" action="" class="w-full flex items-center gap-3 mb-5">
            <input type="hidden" name="tab" x-bind:value="tab">
            <div class="relative w-full">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by hoarding type, city, location..."
                    class="w-full h-11 bg-[#F3F4F6] border border-transparent rounded-lg
                        pl-11 pr-4 text-sm text-gray-700 placeholder-gray-400
                        focus:outline-none focus:ring-2 focus:ring-[#22C55E]/40 focus:bg-white"
                />
                <svg class="absolute left-4 mt-2 top-1/2 -translate-y-1/2 text-gray-400"
                    width="18" height="18" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </div>
        </form>

        <!-- Approved Table -->
        <div x-show="tab === 'approved'">
            <div class="rounded-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1200px] text-sm text-center align-middle">
                        <thead class="bg-[#F9FAFB] text-[#6B7280]">
                            <tr>
                                <th class="px-4 py-3 text-left">SN</th>
                                <th class="px-4 py-3 text-left">HOARDING NAME</th>
                                <th class="px-4 py-3 text-left">CITY</th>
                                <th class="px-4 py-3 text-left">LOCATION</th>
                                <th class="px-4 py-3">COMMISSION</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-center align-middle">
                            @forelse($approvedHoardings as $i => $hoarding)
                                <tr class="hover:bg-gray-50 align-middle">
                                    <td class="px-4 py-3 text-left">{{ $approvedHoardings->firstItem() + $i }}</td>
                                    <td class="px-4 py-3 align-middle">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $mediaItem = $hoarding->primaryMediaItem('hero_image') ?? null;
                                            @endphp
                                            @if($mediaItem)
                                                <div style="border-radius:10px;width:40px;height:40px;min-width:40px;min-height:40px;max-width:40px;max-height:40px;display:flex;align-items:center;justify-content:center;">
                                                    <x-media-preview :media="$mediaItem" :alt="$hoarding->title ?? 'Hoarding'" class="w-full h-full object-cover border rounded-full" style="width:40px;height:40px;min-width:40px;min-height:40px;max-width:40px;max-height:40px;border-radius:9px;" />
                                                </div>
                                            @else
                                                <img src="https://placehold.co/40x40" class="w-10 h-10 object-cover border rounded-full" style="width:40px;height:40px;min-width:40px;min-height:40px;max-width:40px;max-height:40px;border-radius:9999px;" alt="No Image">
                                            @endif
                                            <div class="text-left">
                                                <div class="font-semibold text-[#2563EB] hover:underline cursor-pointer">
                                                    <a href="{{ route('admin.hoardings.show', $hoarding->id) }}">{{ $hoarding->title ?? '-' }}</a>
                                                </div>
                                                <div class="text-xs text-gray-500">{{ strtoupper($hoarding->hoarding_type ?? '-') }} | {{ $hoarding->size ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-left">{{ $hoarding->city ?? '-' }}</td>
                                    <td class="px-4 py-3 text-left">{{ $hoarding->address ?? '-' }}</td>
                                    <td class="px-4 py-3 align-middle text-green-700 font-semibold">{{ $hoarding->commission_percent ? number_format($hoarding->commission_percent,0) . '%' : '-' }}</td>
                                    <td class="px-4 py-3 align-middle">
                                        <svg width="3" height="15" viewBox="0 0 3 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 13.5C0 13.1022 0.158035 12.7206 0.43934 12.4393C0.720644 12.158 1.10218 12 1.5 12C1.89782 12 2.27936 12.158 2.56066 12.4393C2.84196 12.7206 3 13.1022 3 13.5C3 13.8978 2.84196 14.2794 2.56066 14.5607C2.27936 14.842 1.89782 15 1.5 15C1.10218 15 0.720644 14.842 0.43934 14.5607C0.158035 14.2794 0 13.8978 0 13.5ZM0 7.5C0 7.10218 0.158035 6.72064 0.43934 6.43934C0.720644 6.15804 1.10218 6 1.5 6C1.89782 6 2.27936 6.15804 2.56066 6.43934C2.84196 6.72064 3 7.10218 3 7.5C3 7.89782 2.84196 8.27936 2.56066 8.56066C2.27936 8.84196 1.89782 9 1.5 9C1.10218 9 0.720644 8.84196 0.43934 8.56066C0.158035 8.27936 0 7.89782 0 7.5ZM0 1.5C0 1.10218 0.158035 0.720644 0.43934 0.43934C0.720644 0.158035 1.10218 0 1.5 0C1.89782 0 2.27936 0.158035 2.56066 0.43934C2.84196 0.720644 3 1.10218 3 1.5C3 1.89782 2.84196 2.27936 2.56066 2.56066C2.27936 2.84196 1.89782 3 1.5 3C1.10218 3 0.720644 2.84196 0.43934 2.56066C0.158035 2.27936 0 1.89782 0 1.5Z" fill="black"/>
                                        </svg>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-gray-400">
                                        @if(request('search') && $pendingHoardings->total() > 0)
                                            No approved hoardings found for this search. Try checking the <span class="text-[#2563EB] font-semibold cursor-pointer" onclick="document.querySelector('[x-data] button:nth-child(2)').click()">Pending Approval</span> tab.
                                        @else
                                            No hoardings found for this vendor.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 text-sm text-[#6B7280]">
                    <div class="flex items-center gap-2">
                        <span>
                            Showing {{ $approvedHoardings->firstItem() }} to {{ $approvedHoardings->lastItem() }}
                            of {{ $approvedHoardings->total() }} records
                        </span>
                    </div>
                    <div>
                        {{ $approvedHoardings->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Table -->
        <div x-show="tab === 'pending'">
            <div class="rounded-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1200px] text-sm text-center align-middle">
                        <thead class="bg-[#F9FAFB] text-[#6B7280]">
                            <tr>
                                <th class="px-4 py-3 text-left">SN</th>
                                <th class="px-4 py-3 text-left">HOARDING NAME</th>
                                <th class="px-4 py-3 text-left">CITY</th>
                                <th class="px-4 py-3 text-left">LOCATION</th>
                                <th class="px-4 py-3">STATUS</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-center align-middle">
                            @forelse($pendingHoardings as $i => $hoarding)
                                <tr class="hover:bg-gray-50 align-middle">
                                    <td class="px-4 py-3 text-left">{{ $pendingHoardings->firstItem() + $i }}</td>
                                    <td class="px-4 py-3 align-middle">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $mediaItem = $hoarding->primaryMediaItem('hero_image') ?? null;
                                            @endphp
                                            @if($mediaItem)
                                                <div style="border-radius:10px;width:40px;height:40px;min-width:40px;min-height:40px;max-width:40px;max-height:40px;display:flex;align-items:center;justify-content:center;">
                                                    <x-media-preview :media="$mediaItem" :alt="$hoarding->title ?? 'Hoarding'" class="w-full h-full object-cover border rounded-full" style="width:40px;height:40px;min-width:40px;min-height:40px;max-width:40px;max-height:40px;border-radius:9px;" />
                                                </div>
                                            @else
                                                <img src="https://placehold.co/40x40" class="w-10 h-10 object-cover border rounded-full" style="width:40px;height:40px;min-width:40px;min-height:40px;max-width:40px;max-height:40px;border-radius:9999px;" alt="No Image">
                                            @endif
                                            <div class="text-left">
                                                <div class="font-semibold text-[#2563EB] hover:underline cursor-pointer">
                                                    <a href="{{ route('admin.hoardings.show', $hoarding->id) }}">{{ $hoarding->title ?? '-' }}</a>
                                                </div>
                                                <div class="text-xs text-gray-500">{{ strtoupper($hoarding->hoarding_type ?? '-') }} | {{ $hoarding->size ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-left">{{ $hoarding->city ?? '-' }}</td>
                                    <td class="px-4 py-3 text-left">{{ $hoarding->address ?? '-' }}</td>
                                    <td class="px-4 py-3 align-middle font-semibold">
                                        <button
                                            class="bg-[#F59E0B] text-white px-4 py-2 rounded-lg text-sm approve-btn"
                                            data-hoarding-id="{{ $hoarding->id }}"
                                        >
                                            Approve
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <svg width="3" height="15" viewBox="0 0 3 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M0 13.5C0 13.1022 0.158035 12.7206 0.43934 12.4393C0.720644 12.158 1.10218 12 1.5 12C1.89782 12 2.27936 12.158 2.56066 12.4393C2.84196 12.7206 3 13.1022 3 13.5C3 13.8978 2.84196 14.2794 2.56066 14.5607C2.27936 14.842 1.89782 15 1.5 15C1.10218 15 0.720644 14.842 0.43934 14.5607C0.158035 14.2794 0 13.8978 0 13.5ZM0 7.5C0 7.10218 0.158035 6.72064 0.43934 6.43934C0.720644 6.15804 1.10218 6 1.5 6C1.89782 6 2.27936 6.15804 2.56066 6.43934C2.84196 6.72064 3 7.10218 3 7.5C3 7.89782 2.84196 8.27936 2.56066 8.56066C2.27936 8.84196 1.89782 9 1.5 9C1.10218 9 0.720644 8.84196 0.43934 8.56066C0.158035 8.27936 0 7.89782 0 7.5ZM0 1.5C0 1.10218 0.158035 0.720644 0.43934 0.43934C0.720644 0.158035 1.10218 0 1.5 0C1.89782 0 2.27936 0.158035 2.56066 0.43934C2.84196 0.720644 3 1.10218 3 1.5C3 1.89782 2.84196 2.27936 2.56066 2.56066C2.27936 2.84196 1.89782 3 1.5 3C1.10218 3 0.720644 2.84196 0.43934 2.56066C0.158035 2.27936 0 1.89782 0 1.5Z" fill="black"/>
                                        </svg>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-gray-400">
                                        @if(request('search') && $approvedHoardings->total() > 0)
                                            No pending hoardings found for this search. Try checking the <span class="text-[#2563EB] font-semibold cursor-pointer" onclick="document.querySelector('[x-data] button:nth-child(1)').click()">Approved</span> tab.
                                        @else
                                            No hoardings found for this vendor.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 text-sm text-[#6B7280]">
                    <div class="flex items-center gap-2">
                        <span>
                            Showing {{ $pendingHoardings->firstItem() }} to {{ $pendingHoardings->lastItem() }}
                            of {{ $pendingHoardings->total() }} records
                        </span>
                    </div>
                    <div>
                        {{ $pendingHoardings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.approve-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const hoardingId = btn.getAttribute('data-hoarding-id');
                btn.disabled = true;
                btn.innerText = 'Approving...';
                fetch('/admin/vendor-hoardings/bulk-approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ids: [hoardingId] })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        btn.innerText = 'Approved';
                        btn.classList.remove('bg-[#F59E0B]');
                        btn.classList.add('bg-green-600');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        btn.innerText = 'Error';
                        btn.disabled = false;
                    }
                })
                .catch(() => {
                    btn.innerText = 'Error';
                    btn.disabled = false;
                });
            });
        });
    });
    </script>
</div>
@endsection
