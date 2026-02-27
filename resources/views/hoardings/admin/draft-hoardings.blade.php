@extends('layouts.admin')

@section('title', 'All Hoardings')
@section('page_title', "Draft Hoardings")

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'All Hoardings', 'route' => route('admin.my-hoardings')],
    ['label' => 'Draft Hoardings']
]" />
@endsection
@section('content')
<div class="px-6 py-6">
    <h1 class="text-lg font-semibold text-gray-900 mb-4">
        Hoardings in Draft
    </h1>
    <div class="bg-white border border-gray-200 rounded-lg overflow-x-auto">
        <table class="min-w-[1200px] w-full text-sm text-left">
            <thead class="bg-gray-50 text-[11px] uppercase text-gray-600">
                <tr>
                    <th class="px-4 py-3 w-12">SN</th>
                    <th class="px-4 py-3">Hoarding Title</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Published By</th>
                   
                    <th class="px-4 py-3">Hoarding Commission</th>
                    <th class="px-4 py-3">Location</th>
                   
                    <th class="px-4 py-3">Progress</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($hoardings as $index => $hoarding)
                    @php
                        $overallCommission = $hoarding->vendor_commission
                            ? number_format($hoarding->vendor_commission, 0) . '%'
                            : '—';
                        $hoardingCommission = $hoarding->hoarding_commission
                            ? number_format($hoarding->hoarding_commission, 0) . '%'
                            : '—';
                        $progressPercent = $hoarding->completion ?? 0;
                        $progress = $progressPercent . '% Complete';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">
                            {{ $hoardings->firstItem() + $index }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-green-600 font-medium">
                                {{ $hoarding->title }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            {{ $hoarding->type }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">
                                {{ $hoarding->vendor?->name ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-400">Vendor</div>
                        </td>
                        {{-- <td class="px-4 py-3 text-blue-600">
                            {{ $overallCommission }}
                        </td> --}}
                        <td class="px-4 py-3 text-green-600">
                            {{ $hoardingCommission }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $hoarding->address ?? '-' }}
                        </td>
                        {{-- <td class="px-4 py-3 text-center">
                            {{ $hoarding->bookings_count ?? 0 }}
                        </td> --}}
                        <td class="px-4 py-3 text-xs">
                            <span class="text-yellow-600">
                                {{ $progress }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button class="text-gray-900 hover:text-gray-600 text-xl">⋮</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-8 text-gray-400">No draft hoardings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $hoardings->links() }}
        </div>
    </div>
</div>
@endsection
