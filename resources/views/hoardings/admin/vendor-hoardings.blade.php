@extends('layouts.admin')
@section('title', "Vendor's Hoardings")

@section('content')
<div class="px-6 py-6">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold text-gray-900">
            Vendor's Hoardings
        </h1>
        
        {{-- Bulk Actions --}}
        <div id="bulkActionsContainer" class="hidden items-center gap-3">
            <span class="text-sm text-gray-600">
                <span id="selectedCount">0</span> selected
            </span>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium flex items-center gap-2">
                    Bulk Actions
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <ul class="py-1 text-sm">
                        <li>
                            <button onclick="bulkDelete()" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">
                                Delete Selected
                            </button>
                        </li>
                        <li>
                            <button onclick="bulkActivate()" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-green-600">
                                Activate Selected
                            </button>
                        </li>
                        <li>
                            <button onclick="bulkDeactivate()" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-600">
                                Deactivate Selected
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
        <table class="min-w-[1600px] w-full text-sm text-left">

            {{-- ================= THEAD ================= --}}
            <thead class="bg-gray-50 text-[11px] uppercase text-gray-600">
                <tr>
                    <th class="px-4 py-3 w-8">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300" onchange="toggleSelectAll(this)">
                    </th>
                    <th class="px-4 py-3 w-12">SN</th>
                    <th class="px-4 py-3">Hoarding Title</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Published By</th>
                    {{-- <th class="px-4 py-3">Overall Commission</th> --}}
                    <th class="px-4 py-3">Hoarding Commission</th>
                    <th class="px-4 py-3">Location</th>
                    {{-- <th class="px-4 py-3 text-center"># of Bookings</th> --}}
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
                            <input type="checkbox" class="hoarding-checkbox rounded border-gray-300" value="{{ $hoarding->id }}" onchange="updateBulkActions()">
                        </td>

                        {{-- SN --}}
                        <td class="px-4 py-3 text-gray-500">
                            {{ $hoardings->firstItem() + $index }}
                        </td>

                        {{-- Title --}}
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.hoardings.show', $hoarding->id) }}" class="text-green-600 font-medium hover:text-green-700 hover:underline">
                                {{ $hoarding->title }}
                            </a>
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
                        {{-- <td class="px-4 py-3 text-center">
                            {{ $hoarding->bookings_count ?? 0 }}
                        </td> --}}

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


                                <span class="text-sm {{ $hoarding->status === 'pending_approval' ? 'text-red-400' : 'text-gray-700' }}">
                                    {{ $hoarding->status === 'pending_approval' ? 'UNAPPROVED' : strtoupper($hoarding->status) }}
                                </span>

                            </div>
                        </td>

                        {{-- Expiry --}}
                        <td class="px-4 py-3 text-xs">
                            {{ $hoarding->expiry_date ? $hoarding->expiry_date->format('d M, Y') : '—' }}
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
@include('hoardings.admin.modals.vendor-commission-modal')
@include('hoardings.admin.modals.hoarding-commission-modal')
@endsection
@push('scripts')
    <script>
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

                    const toggleUrl = '/admin/vendor-hoardings/' + id + '/toggle-status';
                    fetch(toggleUrl, {
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

        // Bulk Actions Functionality
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.hoarding-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.hoarding-checkbox:checked');
            const count = checkboxes.length;
            const bulkContainer = document.getElementById('bulkActionsContainer');
            const selectedCount = document.getElementById('selectedCount');
            
            if (count > 0) {
                bulkContainer.classList.remove('hidden');
                bulkContainer.classList.add('flex');
                selectedCount.textContent = count;
            } else {
                bulkContainer.classList.add('hidden');
                bulkContainer.classList.remove('flex');
            }
            
            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.hoarding-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            selectAllCheckbox.checked = count === allCheckboxes.length && count > 0;
        }

        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('.hoarding-checkbox:checked');
            return Array.from(checkboxes).map(cb => cb.value);
        }

        function bulkDelete() {
            const ids = getSelectedIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Delete Hoardings?',
                text: `Are you sure you want to delete ${ids.length} hoarding(s)? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete them',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // TODO: Implement bulk delete API call
                    console.log('Bulk delete:', ids);
                    Swal.fire('Success!', `${ids.length} hoarding(s) deleted successfully.`, 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            });
        }

        function bulkActivate() {
            const ids = getSelectedIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Activate Hoardings?',
                text: `Activate ${ids.length} hoarding(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, activate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // TODO: Implement bulk activate API call
                    console.log('Bulk activate:', ids);
                    Swal.fire('Success!', `${ids.length} hoarding(s) activated successfully.`, 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            });
        }

        function bulkDeactivate() {
            const ids = getSelectedIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Deactivate Hoardings?',
                text: `Deactivate ${ids.length} hoarding(s)?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6b7280',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, deactivate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // TODO: Implement bulk deactivate API call
                    console.log('Bulk deactivate:', ids);
                    Swal.fire('Success!', `${ids.length} hoarding(s) deactivated successfully.`, 'success');
                    setTimeout(() => location.reload(), 1500);
                }
            });
        }
    </script>
@endpush