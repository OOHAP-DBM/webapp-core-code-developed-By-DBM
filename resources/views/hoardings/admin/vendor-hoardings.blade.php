@extends('layouts.admin')
@section('title', "Vendor's Hoardings")
@section('content')
    <div class="px-6 py-6">

        <h1 class="text-lg font-semibold text-gray-900 mb-4">
            Vendor's Hoardings
        </h1>

        {{-- TABLE WRAPPER (X SCROLL ENABLED) --}}
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
                        <th class="px-4 py-3">Overall Commission</th>
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
                            // ---------------- CALCULATED UI RULES ----------------
                            $isActive = $hoarding->status === \App\Models\Hoarding::STATUS_ACTIVE;

                            // Overall Commission (UI rule)
                            $overallCommission = '10% - 20%';

                            // Vendor Commission
                            $vendorCommission = optional($hoarding->vendor?->vendorProfile)->commission_percentage;

                            // Fake expiry logic (until DB field exists)
                            $expired = !$isActive;

                            // Progress UI rule
                            $progress = $isActive ? '100% Complete' : '60% Complete';
                        @endphp

                        <tr class="hover:bg-gray-50" data-id="{{ $hoarding->id }}">

                            {{-- Checkbox --}}
                            <td class="px-4 py-3">
                                <input type="checkbox" class="rounded border-gray-300">
                            </td>

                            {{-- SN --}}
                            <td class="px-4 py-3 text-gray-500">
                                {{ $hoardings->firstItem() + $index }}
                            </td>

                            {{-- Hoarding Title --}}
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.hoardings.show', $hoarding->id) }}"
                                class="text-green-600 font-medium hover:underline">
                                    {{ $hoarding->title }}
                                </a>
                            </td>

                            {{-- Type --}}
                            <td class="px-4 py-3">
                                {{ $hoarding->type === 'digital' ? 'DOOH' : 'OOH' }}
                            </td>

                            {{-- Published By --}}
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">
                                    {{ $hoarding->vendor?->name ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-400">Vendor</div>
                            </td>

                            {{-- Overall Commission --}}
                            <td class="px-4 py-3 text-blue-600">
                                {{ $overallCommission }}
                            </td>

                            {{-- Hoarding Commission --}}
                            <td class="px-4 py-3 text-green-600">
                                {{ $vendorCommission !== null ? number_format($vendorCommission,0).'%' : '—' }}
                            </td>

                            {{-- Location --}}
                            <td class="px-4 py-3 text-gray-600">
                                {{ $hoarding->address }}
                            </td>

                            {{-- Bookings --}}
                            <td class="px-4 py-3 text-center">
                                {{ $hoarding->bookings_count }}
                            </td>

                            {{-- STATUS (EXACT UI MATCH) --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">

                                    <button
                                        type="button"
                                        class="status-toggle relative inline-flex w-9 h-5 rounded-full transition
                                            {{ $isActive ? 'bg-gray-300' : 'bg-green-500' }}"
                                        
                                    >
                                        <span
                                            class="absolute top-[2px] left-[-12px] w-4 h-4 rounded-full shadow transition-transform duration-200
                                                {{ $isActive ? 'translate-x-4 bg-white' : 'bg-white left-[2px]' }}">
                                        </span>
                                    </button>

                                    <span class="text-sm text-gray-700 status-text">
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

                                {{-- 3 DOT BUTTON --}}
                                <button
                                    @click="open = !open"
                                    class="text-gray-900 hover:text-gray-600 text-xl focus:outline-none"
                                >
                                    ⋮
                                </button>

                                {{-- DROPDOWN --}}
                                <div
                                    x-show="open"
                                    @click.outside="open = false"
                                    x-transition
                                    class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                                >

                                    <ul class="py-1 text-sm text-gray-700">

                                        {{-- Delete --}}
                                        <li>
                                            <button
                                                class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600"
                                                onclick="confirmDelete({{ $hoarding->id }})"
                                            >
                                                Delete
                                            </button>
                                        </li>

                                        {{-- Update Vendor Commission --}}
                                        <li>
                                            <button
                                                class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                                @click="
                                                    open = false;
                                                    window.dispatchEvent(
                                                        new CustomEvent('open-vendor-commission', {
                                                            detail: {
                                                                id: {{ $hoarding->id }},
                                                                name: '{{ $hoarding->vendor?->name }}'
                                                            }
                                                        })
                                                    )
                                                ">
                                                Update Vendor Commission
                                            </button>

                                        </li>

                                        {{-- Update Hoarding Commission --}}
                                        <li>
                                            <button
                                                class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                             >
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

            {{-- PAGINATION --}}
            <div class="px-4 py-3 border-t">
                {{ $hoardings->links() }}
            </div>
        </div>
    </div>
    {{-- ==== VENDOR COMMISSION MODAL ==== --}}
        <div
                x-data="vendorCommissionModal()"
                x-show="modalOpen"
                x-cloak
                @open-vendor-commission.window="openModal($event.detail.id, $event.detail.name)"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            >


            <div
                @click.outside="closeModal()"
                class="bg-white w-full max-w-md rounded-2xl shadow-xl relative"
            >

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-100 rounded-t-2xl">
                    <span class="text-xs text-gray-500">commission pop-up</span>
                    <button @click="closeModal()" class="text-xl text-gray-500 hover:text-black">
                        ✕
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-6 text-center space-y-5">

                    <h2 class="text-xl font-semibold" x-text="vendorName"></h2>

                    <p class="text-gray-700">Set a Vendor Commission</p>

                    <div class="flex justify-center gap-4">
                        <div>
                            <label class="text-xs text-gray-500">From</label>
                            <input type="number" x-model="from"
                                class="w-24 text-center border rounded-md px-3 py-2">
                        </div>

                        <div>
                            <label class="text-xs text-gray-500">To</label>
                            <input type="number" x-model="to"
                                class="w-24 text-center border rounded-md px-3 py-2">
                        </div>
                    </div>

                    <button
                        @click="applyCommission()"
                        class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg"
                    >
                        Apply
                    </button>

                </div>
            </div>
        </div>

@endsection
@push('scripts')
    <script>
        function vendorCommissionModal() {
            return {
                modalOpen: false,
                vendorName: '',
                hoardingId: null,
                from: '',
                to: '',

                openModal(id, name) {
                    this.hoardingId = id;
                    this.vendorName = name;
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                applyCommission() {
                    this.closeModal();
                }
            }
        }
    </script>
    <script>
        document.querySelectorAll('.status-toggle').forEach(btn => {
            btn.addEventListener('click', async function () {
                const id = this.closest('tr').dataset.id;

                try {
                    const res = await fetch(
                        `/admin/vendor-hoardings/${id}/toggle-status`,
                        {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        }
                    );

                    if (!res.ok) {
                        const data = await res.json();

                        // ❌ commission missing → open modal
                        if (data.needs_commission) {
                            window.dispatchEvent(
                                new CustomEvent('open-vendor-commission', {
                                    detail: { id, name: '' }
                                })
                            );
                        }

                        return;
                    }

                    // ✅ published
                    location.reload();

                } catch (e) {
                    console.error(e);
                }
            });
        });
    </script>

@endpush
