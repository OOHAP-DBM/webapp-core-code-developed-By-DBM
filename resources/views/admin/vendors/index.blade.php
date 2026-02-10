@extends('layouts.admin')

@section('title', 'Vendors Management')

@section('content')
<div class="bg-[#F7F7F7] w-full min-h-screen">

    {{-- Breadcrumb --}}
    <div class="text-sm text-[#6B7280] mb-4">
        Home - Vendors Management -
        <span class="text-[#111827] font-semibold">
            {{ ucfirst(str_replace('_',' ', $status)) }} Vendors
        </span>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-6 text-sm font-medium border-b border-[#E5E7EB]">
            <a href="{{ route('admin.vendors.index',['status'=>'pending_approval']) }}"
               class="pb-3 {{ $status=='pending_approval' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Requested Vendors ({{ $counts->requested }})
            </a>

            <a href="{{ route('admin.vendors.index',['status'=>'approved']) }}"
               class="pb-3 {{ $status=='approved' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Active Vendors ({{ $counts->active }})
            </a>

            <a href="{{ route('admin.vendors.index',['status'=>'suspended']) }}"
               class="pb-3 {{ $status=='suspended' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Disabled Vendors ({{ $counts->disabled }})
            </a>

            <a href="{{ route('admin.vendors.index',['status'=>'rejected']) }}"
               class="pb-3 {{ $status=='rejected' ? 'text-red-500 border-b-2 border-red-500' : 'text-[#9CA3AF]' }}">
                Deleted Vendors ({{ $counts->deleted }})
            </a>
        </div>

        <a href="#" class="bg-black text-white px-4 py-2 rounded-lg text-sm">
            + Add Vendor
        </a>
    </div>

    {{-- Search + Actions --}}
    <div class="bg-white rounded-xl p-4 flex items-center gap-4 mb-4">

        <form method="GET" action="{{ route('admin.vendors.index', ['status' => $status]) }}" class="flex-1 flex items-center gap-2" id="vendor-search-form">
        <input type="hidden" name="status" value="{{ $status }}">   
        <input class="flex-1 border rounded-lg px-4 py-2 text-sm" name="search" value="{{ request('search') }}" placeholder="Search Vendor by Name, City & State..." id="vendor-search-input" autocomplete="off">
        </form>
        <script>
        (function() {
            var input = document.getElementById('vendor-search-input');
            var form = document.getElementById('vendor-search-form');
            var timeout = null;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    form.submit();
                }, 400); // debounce
            });
        })();
        </script>

        @if($status === 'pending_approval')
            <button class="bg-[#F59E0B] text-white px-6 py-2 rounded-lg text-sm">
                Approve All
            </button>
        @elseif($status === 'approved')
            <button class="bg-[#F59E0B] text-white px-4 py-2 rounded-lg text-sm">
                Disable Vendor
            </button>
            <button class="bg-[#16A34A] text-white px-6 py-2 rounded-lg text-sm">
                Export
            </button>
        @elseif($status === 'suspended')
            <button class="bg-[#008ae0] text-white px-6 py-2 rounded-lg text-sm">
                Enable
            </button>
            <button class="bg-[#16A34A] text-white px-6 py-2 rounded-lg text-sm">
                Export
            </button>
        @elseif($status === 'rejected')
            <button class="bg-[#16A34A] text-white px-6 py-2 rounded-lg text-sm">
                Export
            </button>
        @endif
    </div>
{{-- TABLE --}}
@if($status === 'pending_approval')
    @include('admin.vendors.tab.pending')
@elseif($status === 'approved')
    @include('admin.vendors.tab.approved')
@elseif($status === 'suspended')
    @include('admin.vendors.tab.suspended')
@elseif($status === 'rejected')
    @include('admin.vendors.tab.rejected')
@endif


</div>
@endsection
