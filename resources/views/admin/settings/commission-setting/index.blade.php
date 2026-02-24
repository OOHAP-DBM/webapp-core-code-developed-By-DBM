{{-- resources/views/admin/commission/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Commission Settings')
@section('page_title', 'Commission Settings')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'Commission Settings']
]" />
@endsection

@section('content')
<div class="p-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Commission Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage commission rates for vendors and their hoardings</p>
        </div>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" action="{{ route('admin.commission.index') }}" id="filterForm" class="bg-white pt-5 pb-2 mb-3 rounded-md px-5">
        <div class="flex gap-3 mb-6">
            <div class="flex-1 relative">
                <svg class="absolute left-3 mt-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search Vendor by Name, City & State..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:border-[#009A5C] focus:ring-1 focus:ring-[#009A5C]">
            </div>

            <div class="relative">
                <select name="state" onchange="this.form.submit()"
                    class="appearance-none border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm outline-none focus:border-[#009A5C] bg-white min-w-[140px]">
                    <option value="">All States</option>
                    @foreach($states as $state)
                        <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <div class="relative">
                <select name="city" onchange="this.form.submit()"
                    class="appearance-none border border-gray-200 rounded-xl px-4 py-2.5 pr-10 text-sm outline-none focus:border-[#009A5C] bg-white min-w-[140px]">
                    <option value="">All Cities</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 mt-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            <!-- <button type="submit"
                class="px-4 py-2.5 bg-[#E8F7F0] text-[#009A5C] rounded-xl border border-[#009A5C]/20 hover:bg-[#009A5C] hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
            </button> -->
        </div>
    </form>

    {{-- Vendors Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">S.N</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        <div class="flex items-center gap-1">
                            Vendor Name
                            <!-- <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                            </svg> -->
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        <div class="flex items-center gap-1">
                            City
                            <!-- <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                            </svg> -->
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase"> NO. OF HOARDINGS</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">PHONE NUMBER</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">ACTION</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($vendors as $i => $vendor)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 text-gray-500">
                        {{ str_pad($vendors->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.commission.vendor.hoardings', $vendor) }}"
                            class="font-medium text-gray-900 hover:text-[#009A5C] underline">
                            {{ $vendor->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        {{ $vendor->city ?? $vendor->vendorProfile->city ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-gray-700 font-medium">{{ $vendor->hoardings_count }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $vendor->phone ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.commission.vendor.hoardings', $vendor) }}"
                                class="px-4 py-1.5 bg-[#009A5C] text-white rounded-lg text-xs font-semibold hover:bg-[#007a49] transition">
                                View All
                            </a>
                            <!-- <button class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                                </svg>
                            </button> -->
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">No vendors found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
<select class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm" 
        onchange="window.location.href='?per_page='+this.value+'&{{ http_build_query(request()->except('per_page')) }}'">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-sm text-gray-500">
                    Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }} of {{ $vendors->total() }} records
                </span>
            </div>
            {{ $vendors->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>
@endsection