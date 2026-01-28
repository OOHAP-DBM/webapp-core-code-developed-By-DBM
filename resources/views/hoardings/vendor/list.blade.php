@extends('layouts.vendor')

@section('title', 'My Hoardings')

@section('content')
<div class="bg-[#F8F9FA] min-h-screen pb-12">
    <div class="sticky top-0 z-1 bg-white border-b border-gray-100 shadow-sm">
        <div class="max-w-[1600px] mx-auto px-6">
            <div class="flex gap-8">
                <a href="{{ route('vendor.hoardings.myHoardings', ['tab' => 'all']) }}" 
                   class="py-4 px-1 border-b-2 {{ $activeTab !== 'draft' ? 'border-[#00A86B] text-[#00A86B] font-semibold' : 'border-transparent text-gray-400' }} text-sm transition-all whitespace-nowrap">
                    My Hoardings
                </a>
                <a href="{{ route('vendor.hoardings.myHoardings', ['tab' => 'draft']) }}" 
                   class="py-4 px-1 border-b-2 {{ $activeTab === 'draft' ? 'border-[#00A86B] text-[#00A86B] font-semibold' : 'border-transparent text-gray-400' }} text-sm transition-all whitespace-nowrap">
                    Hoarding In Draft
                </a>
            </div>

            <div class="py-4 flex flex-col md:flex-row items-center gap-3">

                <form method="GET" action="{{ route('vendor.hoardings.myHoardings', ['tab' => $activeTab]) }}" class="relative flex-1 flex items-center">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('vendor.hoardings.index') }}" placeholder="Search hoardings..." 
                        class="block w-full pl-9 pr-3 py-2 bg-[#F3F4F6] border-none rounded-md focus:ring-1 focus:ring-emerald-500 text-[13px]">
                    <button type="submit" class="hidden"></button>
                </form>

                <div class="flex items-center gap-2">
                    <button class="p-2 bg-[#E6F6F0] text-[#00A86B] rounded-md border border-emerald-50">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                    {{-- <button class="px-10 py-2 bg-[#00A86B] hover:bg-emerald-700 text-white font-medium rounded-md text-[13px]">
                        Export
                    </button> --}}
                    <a href="{{ route('vendor.hoardings.create') }}" 
                        class="px-5 py-2 bg-[#0087D1] text-white font-medium rounded-md text-[13px] flex items-center">
                        + Add New Hoarding
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-[1600px] mx-auto p-6 space-y-5">
        {{-- <div class="flex flex-wrap gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
            @foreach(range('A', 'Z') as $char)
                <a href="#" class="hover:text-blue-500 {{ $char == 'H' ? 'text-blue-500 underline' : '' }}">{{ $char }}</a>
            @endforeach
        </div> --}}

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white border-b border-gray-100 text-[10px] font-black text-gray-600 uppercase tracking-widest">
                            <th class="px-6 py-5 w-12"><input type="checkbox" class="rounded-sm border-gray-300"></th>
                            <th class="px-4 py-5">SN</th>
                            <th class="px-4 py-5">Hoarding Title</th>
                            <th class="px-4 py-5">Type</th>
                            <th class="px-4 py-5">Location</th>
                            @if($activeTab !== 'draft')
                            <th class="px-4 py-5">No of Bookings</th>
                            @endif
                            <th class="px-4 py-5">Status</th>
                            <th class="px-4 py-5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-[13px]">
                        @forelse($hoardings as $index => $hoarding)
                        @php 
                            $status = strtolower($hoarding['status']);
                            $isActive = in_array($status, ['active', 'published']);
                            $isPending = $status === 'pending_approval';
                        @endphp
                        <tr class="hover:bg-[#F0F9FF] border-b border-gray-50 last:border-0 transition-colors">
                            <td class="px-6 py-4"><input type="checkbox" class="rounded-sm border-gray-300"></td>
                            <td class="px-4 py-4 text-gray-400 font-medium">{{ sprintf('%02d', $index + 1) }}</td>
                            <td class="px-4 py-4 text-gray-700">
                                <a href="{{ route('vendor.myHoardings.show', $hoarding['id']) }}"
                                target="_blank"
                                class="text-[#00A86B] font-medium hover:underline">
                                    {{ !empty($hoarding['title']) ? $hoarding['title'] : 'NA' }}
                                </a>
                            </td>
                            <td class="px-4 py-4 uppercase tracking-wide text-gray-500">
                                {{ !empty($hoarding['hoarding_type']) ? $hoarding['hoarding_type'] : 'NA' }}
                            </td>
                            <td class="px-4 py-4 text-gray-400 truncate max-w-[180px]">
                                {{ !empty($hoarding['location']) ? $hoarding['location'] : 'NA' }}
                            </td>
                            
                            @if($activeTab !== 'draft')
                            <td class="px-4 py-4 text-gray-500 underline decoration-gray-200">
                                {{ $hoarding['bookings_count'] ?? '0' }}
                            </td>
                            @endif

                            <td class="px-4 py-4">
                            @if(!$isPending)
                                <form action="{{ route('vendor.hoardings.toggle', $hoarding['id']) }}"
                                    method="POST"
                                    class="inline-flex items-center gap-2">
                                    @csrf

                                    <!-- TOGGLE -->
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                                type="checkbox"
                                                class="sr-only peer"
                                                {{ $isActive ? 'checked' : '' }}
                                                onclick="return confirmToggle(event, this)"
                                            >

                                        <div class="
                                            w-9 h-5 bg-gray-300 rounded-full
                                            peer-checked:bg-emerald-500
                                            transition-colors
                                            after:content-['']
                                            after:absolute after:top-[2px] after:left-[2px]
                                            after:h-4 after:w-4
                                            after:bg-white after:rounded-full
                                            after:transition-all
                                            peer-checked:after:translate-x-4
                                        "></div>
                                    </label>

                                    <!-- STATUS TEXT -->
                                    <span class="
                                        text-[11px] font-bold uppercase tracking-wide
                                        {{ $isActive ? 'text-emerald-600' : 'text-gray-400' }}
                                    ">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </form>
                            @else
                                <span class="text-orange-500 text-[11px] font-bold uppercase italic">
                                    Pending Approval
                                </span>
                            @endif
                        </td>



                            <td class="px-4 py-4 text-center">
                                <div class="relative inline-block text-left">
                                    <button type="button" onclick="toggleActionMenu(event, 'menu-{{ $hoarding['id'] }}')" 
                                            class="w-7 h-7 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded">
                                        <i class="fa-solid fa-ellipsis-vertical text-xs"></i>
                                    </button>

                                    <div id="menu-{{ $hoarding['id'] }}" 
                                         class="hidden absolute right-0 -mt-[100px] w-44 bg-white rounded-lg shadow-xl border border-gray-100 z-[100] overflow-hidden">
                                        <div class="p-1 space-y-1 text-left">
                                            {{-- Edit option for both draft and active/inactive hoardings --}}
                                            <a href="{{ route('vendor.hoardings.edit', $hoarding['id']) }}" 
                                               class="flex items-center gap-2 px-3 py-2 text-[12px] font-medium text-blue-600 hover:bg-blue-50 rounded">
                                                <i class="fa-solid fa-pen-to-square opacity-60"></i> 
                                                {{ $activeTab === 'draft' ? 'Edit Draft' : 'Edit Hoarding' }}
                                            </a>

                                            {{-- Toggle Active/Inactive only for non-draft and non-pending hoardings --}}
                                            @if($activeTab !== 'draft' && !in_array($status, ['pending_approval']))
                                                <form action="{{ route('vendor.hoardings.toggle', $hoarding['id']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-[12px] font-medium {{ $isActive ? 'text-orange-500 hover:bg-orange-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded">
                                                        <i class="fa-solid {{ $isActive ? 'fa-pause-circle' : 'fa-play-circle' }} opacity-60"></i> 
                                                        {{ $isActive ? 'Make Inactive' : 'Make Active' }}
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Delete option with confirmation --}}
                                            <button
                                                type="button"
                                                onclick="deleteHoarding({{ $hoarding['id'] }})"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-[12px] font-medium text-red-500 hover:bg-red-50 rounded">
                                                <i class="fa-solid fa-trash-can opacity-60"></i> Delete
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="p-24 text-center text-gray-400">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="bg-white px-6 py-4 flex items-center justify-between border-t border-gray-50 text-[12px] text-gray-500">
                <div class="flex items-center gap-3 font-medium">
                    <select class="bg-[#F3F4F6] border-none rounded-md px-3 py-1 text-gray-700 font-bold">
                        <option>08</option>
                        <option>10</option>
                    </select>
                    <span>
                        Showing {{ ($hoardings->firstItem() ?? 0) }} to {{ ($hoardings->lastItem() ?? count($hoardings)) }} of {{ ($hoardings->total() ?? count($hoardings)) }} records
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <button class="w-7 h-7 flex items-center justify-center rounded text-gray-300"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                    <button class="w-7 h-7 flex items-center justify-center rounded bg-[#00A86B] text-white font-bold">1</button>
                    <button class="w-7 h-7 flex items-center justify-center rounded text-gray-400"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.swal-compact {
    border-radius: 12px !important;
}

@media (max-width: 640px) {
    .swal-compact {
        width: 90% !important;
        padding: 1rem !important;
    }
}
/* CONFIRM BUTTON */
.swal-btn-confirm {
    background-color: #00A86B !important;
    color: #ffffff !important;
    border-radius: 6px !important;
    padding: 10px 28px !important;
    font-weight: 500;
}

/* CANCEL BUTTON — FIRST IMAGE STYLE */
.swal-btn-cancel {
    background-color: #e5e7eb !important; /* light gray */
    color: #000000 !important;
    border: 1px solid #d1d5db !important;
    border-radius: 6px !important;
    padding: 10px 28px !important;
    font-weight: 500;
}

/* hover effect (optional but image jaisa) */
.swal-btn-cancel:hover {
    background-color: #d1d5db !important;
}

</style>

<script>
    function closeAllMenus() {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
    function toggleActionMenu(event, menuId) {
        event.stopPropagation();
        const targetMenu = document.getElementById(menuId);
        closeAllMenus(); 
        targetMenu.classList.toggle('hidden');
    }
    document.addEventListener('click', closeAllMenus);
    document.addEventListener('scroll', closeAllMenus, {
        capture: true,
        passive: true
    });
</script>
<script>
    function deleteHoarding(id) {
        Swal.fire({
            title: 'Delete Hoarding?',
            html: '<p class="text-sm text-gray-600">This action cannot be undone.</p>',
            icon: 'warning',

            // 🎯 Size control
            width: '30rem',          // desktop
            padding: '1.25rem',      // compact height
            iconColor: '#ef4444',

            // 🎯 Buttons
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',

            // 🎯 Button styling
            buttonsStyling: true,
            reverseButtons: true,

            // 🎯 Mobile optimization
            customClass: {
                popup: 'swal-compact',
                title: 'text-base font-semibold',
                confirmButton: 'px-4 py-2 text-sm',
                cancelButton: 'px-4 py-2 text-sm'
            }
        }).then((result) => {

            if (!result.isConfirmed) return;

            // loader
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/vendor/hoardings/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Hoarding deleted successfully.',
                        timer: 1800,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });

                    setTimeout(() => location.reload(), 1800);
                }
            });
        });
    }
</script>
<script>
function confirmToggle(e, checkbox) {
    e.preventDefault();

    const form = checkbox.closest('form');
    const isActiveNow = checkbox.checked;

    Swal.fire({
        title: isActiveNow ? 'Active Hoarding?' : 'Inactive Hoarding?',
        html: isActiveNow
            ? '<p class="text-sm text-gray-600">Are you sure you want to Active this hoarding? It will not be visible to customers until re-activated.</p>'
            : '<p class="text-sm text-gray-600">Are you sure you want to Inactive this hoarding? It will be visible to customers.</p>',
        width:'25rem',
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: isActiveNow ? 'Active' : 'Inactive',
        cancelButtonText: 'Cancel',
        confirmButtonColor: isActiveNow ? '#00A86B' : '#ef4444',
        buttonsStyling: true,
        customClass: {
            popup: 'rounded-lg',
            title: 'text-base font-semibold text-gray-800',
            confirmButton: 'px-6 py-2 text-sm',
            cancelButton: 'px-6 py-2 text-sm swal-cancel-black'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Toggle the checkbox state and submit
            checkbox.checked = !isActiveNow;
            form.submit();
        }
        // If cancelled, do nothing: checkbox remains in its original state, form is not submitted
    });

    // Always return false to prevent the default checkbox toggle
    return false;
}
</script>
@if(session('swal_success'))
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            icon: '{{ session('swal_type', 'success') }}',
            title: '{{ session('swal_type') === 'warning' ? 'Inactive!' : 'Success!' }}',
            text: '{{ session('swal_success') }}',
            showConfirmButton: false,
            timer: 1800,
            toast: true,
            position: 'top-end'
        });
    </script>
@endif
@endsection