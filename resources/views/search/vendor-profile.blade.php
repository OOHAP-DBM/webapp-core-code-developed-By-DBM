@extends('layouts.app')

@section('content')
@include('components.customer.navbar')
<div id="gridView" class="bg-gray-100">
<div class="max-w-[1460px] mx-auto px-6 py-6">
    <div class="bg-[#D9F2E6] rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-[#b9d1c5]">
            <h2 class="text-lg font-semibold text-gray-800">
                Vendor Details
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6 px-6 py-5 text-sm text-gray-700">

            {{-- LEFT --}}
            <div class="space-y-2">

                <p>
                    <span class="font-semibold">Name:</span>
                    {{ $vendor->vendorProfile->company_name ?? $vendor->name }}
                </p>

                <p>
                    <span class="font-semibold">Email:</span>
                    {{ $vendor->email ?? 'Not Available' }}
                </p>

                <p>
                    <span class="font-semibold">Address:</span>
                    {{ $vendor->vendorProfile->registered_address 
                        ?? ($vendor->city . ', ' . $vendor->state) 
                        ?? 'No address provided' }}
                </p>

            </div>

            {{-- MIDDLE --}}
            <div class="space-y-2">
                <p>
                    <span class="font-semibold">Total Hoardings:</span>
                    {{ $hoardings->total() }}
                </p>
            </div>

        </div>
    </div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">
            All Hoardings
        </h2>
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <span class="whitespace-nowrap">Sort by</span>
            <form method="GET">
                @foreach(request()->except('sort','page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <select name="sort"
                    onchange="this.form.submit()"
                    class="h-8 px-3 pr-8 text-sm border border-gray-300 rounded-md bg-white
                    focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 cursor-pointer">

                    <option value="">Recommended</option>
                    <option value="latest" {{ request('sort')=='latest'?'selected':'' }}>Latest</option>
                    <option value="low_high" {{ request('sort')=='low_high'?'selected':'' }}>Low to high</option>
                    <option value="high_low" {{ request('sort')=='high_low'?'selected':'' }}>High to low</option>

                </select>
            </form>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($hoardings as $hoarding)
            @include('components.customer.hoarding-card', [
                'hoarding' => $hoarding,
                'cartIds' => $cartIds ?? []
            ])
        @empty
            <div class="col-span-4 text-center py-16 text-gray-500">
                No hoardings available for this vendor.
            </div>
        @endforelse
    </div>
    <div class="mt-8">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Showing
                <span class="font-medium text-gray-700">
                    {{ $hoardings->firstItem() }}
                </span>
                to
                <span class="font-medium text-gray-700">
                    {{ $hoardings->lastItem() }}
                </span>
                of
                <span class="font-medium text-gray-700">
                    {{ $hoardings->total() }}
                </span>
                results
            </div>
            <div>
                {{ $hoardings->onEachSide(1)->withQueryString()->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
</div>
@endsection
