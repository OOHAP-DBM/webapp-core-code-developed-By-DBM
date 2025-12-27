@extends('layouts.admin')

@section('title', 'Vendors Management')

@section('content')
    <div class="bg-[#F7F7F7] w-full min-h-screen">

            {{-- ===== Breadcrumb ===== --}}
        <div class="text-sm text-[#6B7280] mb-4">
            Home <span class="mx-1">-</span>
            Vendors Management <span class="mx-1">-</span>
            <span class="text-[#111827] font-semibold">
                {{ ucfirst(str_replace('_', ' ', $status)) }} Vendors
            </span>
        </div>

            {{-- ===== Tabs + Add Vendor ===== --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="flex gap-6 text-sm font-medium border-b border-[#E5E7EB]">
                    <a href="{{ route('admin.vendors.index', ['status'=>'pending_approval']) }}"
                    class="pb-3 {{ $status=='pending_approval' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                        Requested Vendors ({{ $counts->requested }})
                    </a>

                    <a href="{{ route('admin.vendors.index', ['status'=>'approved']) }}"
                    class="pb-3 {{ $status=='approved' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                        Active Vendors ({{ $counts->active }})
                    </a>

                    <a href="{{ route('admin.vendors.index', ['status'=>'suspended']) }}"
                    class="pb-3 {{ $status=='suspended' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                        Disabled Vendors ({{ $counts->disabled }})
                    </a>

                    <a href="{{ route('admin.vendors.index', ['status'=>'rejected']) }}"
                    class="pb-3 {{ $status=='rejected' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                        Deleted Vendors ({{ $counts->deleted }})
                    </a>
                </div>

                <a href="" class="bg-black text-white px-4 py-2 rounded-lg text-sm">
                    + Add Vendor
                </a>
        </div>

            {{-- ===== Search + Approve All ===== --}}
        <div class="bg-white rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
                <div class="flex-1 relative">
                    <svg class="absolute mt-2 left-3 top-1/2 -translate-y-1/2 text-gray-400" width="18" height="18"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>

                    <input type="text"
                        placeholder="Search Vendor by Name, City & State..."
                        class="w-full pl-10 pr-4 py-2 text-sm border border-[#E5E7EB] rounded-lg focus:outline-none">
                </div>

                <button class="bg-[#F3F4F6] p-2 rounded-lg">
                    <svg width="18" height="18" fill="none" stroke="green" stroke-width="2"
                        viewBox="0 0 24 24">
                        <polygon points="3 4 21 4 14 13 14 20 10 18 10 13 3 4"/>
                    </svg>
                </button>

                @if($status === 'pending_approval')
                    <button class="bg-[#F59E0B] px-10 py-2 rounded-lg text-sm">
                        Approve All
                    </button>
                @endif
                @if($status === 'approved')
                    <button class="bg-[#F59E0B] text-white px-4 py-2 rounded-lg text-sm">
                        Disable Vendor
                    </button>
                <button class="bg-[#16A34A] text-white px-10 py-2 rounded-lg text-sm">
                        Export
                </button>

                @endif
                @if($status === 'suspended')
                <button class="bg-[#008ae0] text-white px-10 py-2 rounded-lg text-sm">
                        Enable
                </button>

                <button class="bg-[#16A34A] text-white px-10 py-2 rounded-lg text-sm">
                        Export
                </button>

                @endif
                @if($status === 'rejected')
                <button class="bg-[#16A34A] text-white px-10 py-2 rounded-lg text-sm">
                        Export
                </button>
                @endif
        </div>

            {{-- ===== Table ===== --}}
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[1100px] text-sm">
                    <thead class="bg-[#F3F4F6] text-[#6B7280]">
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
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($vendors as $i => $vendor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="accent-green-600">
                            </td>
                            <td class="px-4 py-3">{{ $vendors->firstItem() + $i }}</td>
                            <td class="px-4 py-3 font-medium text-[#2563EB]">
                                {{ $vendor->user->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $vendor->created_at->format('M d, Y') }}
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
                                <button class="text-gray-500">
                                    ⋮
                                </button>
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

                    {{-- ===== Pagination ===== --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 text-sm text-[#6B7280]">
                        <div>
                            Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }}
                            of {{ $vendors->total() }} records
                        </div>
                        <div>
                            {{ $vendors->links() }}
                        </div>
            </div>
        </div>

    </div>
@endsection
