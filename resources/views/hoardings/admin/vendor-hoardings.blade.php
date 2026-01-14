@extends('layouts.admin')
@section('title', "Vendor's Hoardings")

@section('content')
<div class="px-6 py-6">

    <h1 class="text-lg font-semibold text-gray-900 mb-4">
        Vendor's Hoardings
    </h1>

    <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
        <table class="min-w-[1600px] w-full text-sm text-left">

            {{-- ================= THEAD ================= --}}
            <thead class="bg-gray-50 text-[11px] uppercase text-gray-600">
                <tr>
                    <th class="px-4 py-3 w-8">
                        <input type="checkbox" class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-3 w-12">SN</th>
                    <th class="px-4 py-3">Hoarding Title</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Published By</th>
                    {{-- <th class="px-4 py-3">Overall Commission</th> --}}
                    <th class="px-4 py-3">Hoarding Commission</th>
                    <th class="px-4 py-3">Location</th>
                    <th class="px-4 py-3 text-center"># of Bookings</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Hoarding Expire On</th>
                    <th class="px-4 py-3">Progress</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            {{-- ================= TBODY ================= --}}
            <tbody class="divide-y divide-gray-100">
                @forelse($hoardings as $index => $hoarding)

                    @php
                        $isActive = $hoarding->status === 'active';

                        $overallCommission = $hoarding->vendor_commission
                            ? number_format($hoarding->vendor_commission, 0) . '%'
                            : '—';

                        $hoardingCommission = $hoarding->hoarding_commission
                            ? number_format($hoarding->hoarding_commission, 0) . '%'
                            : '—';

                        $progressPercent = $hoarding->completion ?? 0;
                        $progress = $progressPercent . '% Complete';
                    @endphp

                    <tr class="hover:bg-gray-50"
                        data-id="{{ $hoarding->id }}"
                        data-source="{{ $hoarding->source }}"
                    >

                        {{-- Checkbox --}}
                        <td class="px-4 py-3">
                            <input type="checkbox" class="rounded border-gray-300">
                        </td>

                        {{-- SN --}}
                        <td class="px-4 py-3 text-gray-500">
                            {{ $hoardings->firstItem() + $index }}
                        </td>

                        {{-- Title --}}
                        <td class="px-4 py-3">
                            <span class="text-green-600 font-medium">
                                {{ $hoarding->title }}
                            </span>
                        </td>

                        {{-- Type --}}
                        <td class="px-4 py-3">
                            {{ $hoarding->type }}
                        </td>

                        {{-- Vendor --}}
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                {{ $hoarding->vendor?->name ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-400">Vendor</div>
                        </td>

                        {{-- Overall Commission --}}
                        {{-- <td class="px-4 py-3 text-blue-600">
                            {{ $overallCommission }}
                        </td> --}}

                        {{-- Hoarding Commission --}}
                        <td class="px-4 py-3 text-green-600">
                            {{ $hoardingCommission }}
                        </td>

                        {{-- Location --}}
                        <td class="px-4 py-3 text-gray-600">
                            {{ $hoarding->address ?? '-' }}
                        </td>

                        {{-- Bookings --}}
                        <td class="px-4 py-3 text-center">
                            {{ $hoarding->bookings_count ?? 0 }}
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">

                                <button
                                    type="button"
                                    class="status-toggle relative inline-flex w-9 h-5 rounded-full transition
                                        {{ $isActive ? 'bg-green-500' : 'bg-gray-300' }}"
                                    data-id="{{ $hoarding->id }}"
                                    data-source="{{ $hoarding->source }}"
                                    data-vendor-name="{{ $hoarding->vendor?->name }}"
                                    data-vendor-commission="{{ $hoarding->vendor_commission }}"
                                    data-hoarding-commission="{{ $hoarding->hoarding_commission }}"
                                    >
                                    <span
                                        class="absolute top-[2px] left-[-13px] w-4 h-4 rounded-full shadow transition-transform duration-200
                                        {{ $isActive ? 'translate-x-4 bg-white' : 'bg-white left-[2px]' }}">
                                    </span>
                                </button>


                                <span class="text-sm text-gray-700">
                                    {{ $isActive ? 'Published' : 'Unpublished' }}
                                </span>

                            </div>
                        </td>

                        {{-- Expiry --}}
                        <td class="px-4 py-3 text-xs">
                            @if($isActive)
                                <span class="text-gray-600">2 Days left</span>
                            @else
                                <span class="text-red-500">Expired</span>
                            @endif
                        </td>

                        {{-- Progress --}}
                        <td class="px-4 py-3 text-xs">
                            <span class="{{ $isActive ? 'text-green-600' : 'text-red-500' }}">
                                {{ $progress }}
                            </span>
                        </td>

                        {{-- Action --}}
                        <td class="px-4 py-3 text-right relative" x-data="{ open: false }">

                            <button @click="open = !open"
                                class="text-gray-900 hover:text-gray-600 text-xl">
                                ⋮
                            </button>

                            <div x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">

                                <ul class="py-1 text-sm text-gray-700">

                                    <li>
                                        <button class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">
                                            Delete
                                        </button>
                                    </li>

                                    <li>
                                        <button
                                            class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                            @click="
                                                open = false;
                                                window.dispatchEvent(
                                                    new CustomEvent('open-vendor-commission', {
                                                        detail: {
                                                            id: {{ $hoarding->id }},
                                                            name: '{{ $hoarding->vendor?->name }}',
                                                            vendor_profile_id: {{ $hoarding->vendor_profile_id }},
                                                        }
                                                    })
                                                )
                                            ">
                                            Update Vendor Commission
                                        </button>

                                    </li>

                                    <li>
                                        <button
                                            class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                            @click="
                                                open = false;
                                                window.dispatchEvent(
                                                    new CustomEvent('open-hoarding-commission', {
                                                        detail: {
                                                            id: {{ $hoarding->id }},
                                                            title: '{{ $hoarding->title }}',
                                                            source: '{{ $hoarding->source }}'
                                                        }
                                                    })
                                                )
                                            ">
                                            Update Hoarding Commission
                                        </button>

                                    </li>

                                </ul>
                            </div>
                        </td>
                    </tr>

                 @empty
                    <tr>
                        <td colspan="13" class="px-6 py-10 text-center text-gray-500">
                            No vendor hoardings found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 border-t">
            {{ $hoardings->links() }}
        </div>
    </div>
</div>
{{-- ================= VENDOR COMMISSION POPUP ================= --}}
<div
    x-data="vendorCommissionModal"
    x-init="init()"
    x-show="open"
    x-cloak
    @open-vendor-commission.window="openModal($event.detail)"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
   >
    <div
        @click.outside="close()"
        class="bg-[#f5f5f5] w-full max-w-lg rounded-2xl shadow-xl relative px-10 py-8"
        x-transition
    >
        {{-- Close --}}
        <button
            @click="close()"
            class="absolute top-5 right-6 text-xl text-gray-700 hover:text-black"
        >
            ✕
        </button>

        {{-- Vendor Name --}}
        <h2
            class="text-2xl font-semibold text-center mb-6"
            x-text="vendorName"
        ></h2>

        {{-- Subtitle --}}
        <p class="text-center text-gray-700 mb-6">
            Set a Vendor Commission
        </p>

        {{-- From / To --}}
        <div class="flex justify-center gap-6 mb-8">
            <div>
                <label class="block text-sm text-gray-600 mb-1">From</label>
                <input
                    type="number"
                    x-model="from"
                    class="w-28 text-center border rounded-md px-3 py-2"
                    placeholder="10"
                >
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">To</label>
                <input
                    type="number"
                    x-model="to"
                    class="w-28 text-center border rounded-md px-3 py-2"
                    placeholder="20"
                >
            </div>
        </div>

        {{-- Apply --}}
        <button
            @click="apply()"
            class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl text-lg font-medium"
        >
            Apply
        </button>
    </div>
</div>
{{-- ================= HOARDING COMMISSION MODAL ================= --}}
<div
    x-data="hoardingCommissionModal()"
    x-show="modalOpen"
    x-cloak
    @open-hoarding-commission.window="openModal($event.detail)"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
   >
    <div
        @click.outside="closeModal()"
        class="bg-white w-full max-w-md rounded-2xl shadow-xl"
    >
        <div class="flex justify-between px-6 py-4 border-b bg-gray-100 rounded-t-2xl">
            <h3 class="font-semibold text-lg">Hoarding Commission</h3>
            <button @click="closeModal()">✕</button>
        </div>

        <div class="px-6 py-6 space-y-4 text-center">
            <h2 class="font-semibold text-lg" x-text="title"></h2>

            <input
                type="number"
                x-model="commission"
                class="w-full border rounded-md px-3 py-2 text-center"
                placeholder="Enter hoarding commission %"
            >

            <button
                class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg"
                @click="save()"
            >
                Apply
            </button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script>
        function hoardingCommissionModal() {
            return {
                modalOpen: false,
                hoardingId: null,
                title: '',
                source: '',
                commission: '',

                openModal(detail) {
                    this.hoardingId = detail.id;
                    this.title = detail.title;
                    this.source = detail.source;
                    this.commission = '';
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                    this.commission = '';
                },

                save() {
                    if (!this.commission) {
                        alert('Please enter hoarding commission');
                        return;
                    }

                    fetch(`/admin/vendor-hoardings/${this.hoardingId}/set-commission`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            type: 'hoarding',
                            source: this.source,
                            commission: this.commission
                        })
                    })
                    .then(res => res.json())
                    .then(data => {

                        // ✅ CLOSE MODAL
                        this.closeModal();

                        // ✅ SUCCESS POPUP
                        Swal.fire({
                            icon: 'success',
                            title: 'Commission Set!',
                            text: 'Hoarding commission has been saved successfully.',
                            confirmButtonColor: '#16a34a',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // ✅ AUTO RELOAD AFTER POPUP
                        setTimeout(() => {
                            location.reload();
                        }, 2000);

                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save hoarding commission'
                        });
                    });
                }

            }
        }
        document.addEventListener('alpine:init', () => {

            Alpine.data('vendorCommissionModal', () => ({
                open: false,
                hoardingId: null,
                vendorProfileId: null,
                vendorName: '',
                from: '',
                to: '',

                init() {
                    this.reset();
                },

                reset() {
                    this.open = false;
                    this.hoardingId = null;
                    this.vendorName = '';
                    this.from = '';
                    this.to = '';
                },

                openModal(detail) {
                    this.vendorProfileId = detail.vendor_profile_id;
                    this.hoardingId = detail.id;
                    this.vendorName = detail.name || 'Vendor';
                    this.from = '';
                    this.to = '';
                    this.open = true;
                },

                close() {
                    this.reset();
                },

                apply() {
                    if (!this.from || !this.to) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Input',
                            text: 'Please enter vendor commission range'
                        });
                        return;
                    }

                    if (Number(this.to) < Number(this.from)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Range',
                            text: 'To commission cannot be less than From'
                        });
                        return;
                    }

                    fetch(`/admin/vendors/${this.vendorProfileId}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            commission_percentage: this.to   // ✅ reuse controller
                        })
                    })
                    .then(res => res.json())
                    .then(data => {

                        this.close();

                        Swal.fire({
                            icon: 'success',
                            title: 'Vendor Approved!',
                            text: `Commission set to ${this.to}%`,
                            confirmButtonColor: '#16a34a',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        setTimeout(() => location.reload(), 2000);
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to approve vendor'
                        });
                    });
                }
            }));

        });
        document.addEventListener('DOMContentLoaded', () => {

            document.querySelectorAll('.status-toggle').forEach(button => {

                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const btn = e.currentTarget;

                    if (btn.dataset.loading === '1') return;
                    btn.dataset.loading = '1';

                    const id = btn.dataset.id;
                    const source = btn.dataset.source;
                    const hoardingCommission = btn.dataset.hoardingCommission;

                    // ❌ RULE 1: Hoarding commission NOT set → open modal
                    if (!hoardingCommission || hoardingCommission == 0) {
                        btn.dataset.loading = '0';

                        window.dispatchEvent(
                            new CustomEvent('open-hoarding-commission', {
                                detail: {
                                    id: id,
                                    title: 'Set Hoarding Commission',
                                    source: source
                                }
                            })
                        );
                        return;
                    }

                    fetch(`/admin/vendor-hoardings/${id}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error();
                        return res.json();
                    })
                    .then(data => {

                        Swal.fire({
                            icon: 'success',
                            title: 'Status Updated',
                            text: data.status === 'active'
                                ? 'Hoarding has been published successfully.'
                                : 'Hoarding has been unpublished successfully.',
                            confirmButtonColor: '#16a34a',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 1800);

                    })
                    .catch(() => {
                        btn.dataset.loading = '0';

                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: 'Unable to update hoarding status'
                        });
                    });

                });

            });

        });
    </script>
@endpush