@extends('layouts.app')

@section('content')
@include('components.customer.navbar')
<div id="gridView" class="bg-gray-100 pt-5 md:pt-0 ">
<div class="max-w-[1460px] mx-auto px-6 md:py-6 py-10">
    <div class="bg-gradient-to-b from-[#D9F2E6] to-[#F4FFFB] rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-[#b9d1c5]">
            <h2 class="text-lg font-semibold text-gray-800">
                Vendor Details
            </h2>
        </div>
        <div class="flex flex-col md:flex-row items-center md:items-start justify-between px-6 py-4 gap-6">
            {{-- Vendor Image --}}
            <div class="flex-shrink-0">
                <div class="w-20 h-20 rounded-full overflow-hidden border border-gray-200 bg-white">
                    <img
                        src="{{ route('view-avatar', $vendor->id) }}?v={{ optional($vendor->updated_at)->timestamp ?? time() }}"
                        alt="Vendor Image"
                        class="w-full h-full object-cover"
                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($vendor->name ?? 'N/A') }}&background=22c55e&color=fff&size=128'"
                    >
                </div>
            </div>
            {{-- Vendor Info --}}
            <div class="flex-1 min-w-0 flex flex-col md:flex-row md:items-center md:justify-between gap-4 w-full">
                <div class="min-w-0">
                    <div class="flex flex-col gap-0.5">
                        <span class="font-semibold text-lg text-gray-900">{{ $vendor->name ?? 'N/A' }}</span>
                        <span class="text-sm text-amber-700 font-medium">{{ $vendor->vendorProfile->company_name ?? '' }}</span>
                        <span class="text-sm text-gray-600">{{ $vendor->email ?? 'Not Available' }}</span>
                        <span class="text-xs text-gray-500">{{ $vendor->vendorProfile->registered_address ?? ($vendor->city . ', ' . $vendor->state) ?? 'No address provided' }}</span>
                    </div>
                </div>
                <div class="flex flex-row gap-4 md:gap-6 items-center md:justify-end">
                    <span class="px-4 py-2 border border-green-400 text-green-700 rounded-md text-sm font-semibold flex items-center gap-2 bg-green-50">
                        Total Hoardings: <span class="font-bold">{{ $hoardings->total() }}</span>
                    </span>
                    {{-- <span class="px-4 py-2 border border-orange-400 text-orange-700 rounded-md text-sm font-semibold flex items-center gap-2 bg-orange-50">
                        Ratings:
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-orange-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.175c.969 0 1.371 1.24.588 1.81l-3.38 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.38-2.454a1 1 0 00-1.175 0l-3.38 2.454c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.05 9.394c-.783-.57-.38-1.81.588-1.81h4.175a1 1 0 00.95-.69l1.286-3.967z"/></svg>
                        <span class="font-bold">{{ $vendor->vendorProfile->rating ?? '0' }}</span>
                    </span> --}}
                </div>
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

                    <option value="" {{ request('sort') == '' ? 'selected' : '' }}>All Hoardings</option>
                    <option value="recommended" {{ request('sort')=='recommended'?'selected':'' }}>Recommended</option>
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
