@extends('layouts.admin')

@section('title', 'Customers Management')

@section('content')
<div class="bg-[#F7F7F7] w-full min-h-screen">

    {{-- Breadcrumb --}}
    <div class="text-sm text-[#6B7280] mb-4">
        Home - Customers Management -
        <span class="text-[#111827] font-semibold">
            {{ ucfirst(str_replace('_',' ', $tab)) }} Customers
        </span>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-6 text-sm font-medium border-b border-[#E5E7EB]">
            <a href="{{ route('admin.customers.index',['tab'=>'total']) }}"
               class="pb-3 {{ $tab=='total' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Total Customers ({{ $totalCustomers }})
            </a>
            <a href="{{ route('admin.customers.index',['tab'=>'week']) }}"
               class="pb-3 {{ $tab=='week' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Joined This Week ({{ $joinedThisWeek }})
            </a>
            <a href="{{ route('admin.customers.index',['tab'=>'month']) }}"
               class="pb-3 {{ $tab=='month' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Joined This Month ({{ $joinedThisMonth }})
            </a>
            <a href="{{ route('admin.customers.index',['tab'=>'deletion']) }}"
               class="pb-3 {{ $tab=='deletion' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Account Deletion Request ({{ $deletionRequests }})
            </a>
            <a href="{{ route('admin.customers.index',['tab'=>'disabled']) }}"
               class="pb-3 {{ $tab=='disabled' ? 'text-[#2563EB] border-b-2 border-[#2563EB]' : 'text-[#9CA3AF]' }}">
                Disabled ({{ $disabled }})
            </a>
            <a href="{{ route('admin.customers.index',['tab'=>'deleted']) }}"
               class="pb-3 {{ $tab=='deleted' ? 'text-red-500 border-b-2 border-red-500' : 'text-[#9CA3AF]' }}">
                Deleted ({{ $deleted }})
            </a>
        </div>
    </div>

    {{-- Search + Actions --}}
    <div class="bg-white rounded-xl p-4 flex items-center gap-4 mb-4">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex-1 flex items-center gap-2" id="customer-search-form">
            <input class="flex-1 border rounded-lg px-4 py-2 text-sm" name="search" value="{{ request('search') }}" placeholder="Search Customer by Name, Email, Phone..." id="customer-search-input" autocomplete="off">
        </form>
        <script>
        (function() {
            var input = document.getElementById('customer-search-input');
            var form = document.getElementById('customer-search-form');
            var timeout = null;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    form.submit();
                }, 400);
            });
        })();
        </script>
         
        @if($tab === 'deletion')
            <button class="bg-[#F59E0B] text-white px-6 py-2 rounded-lg text-sm">Approve All Deletions</button>
        @elseif($tab === 'disabled')
            <button class="bg-[#008ae0] text-white px-6 py-2 rounded-lg text-sm">Enable</button>
        @elseif($tab === 'deleted')
            <button class="bg-[#16A34A] text-white px-6 py-2 rounded-lg text-sm">Export</button>
        @endif
        <a href="{{route('admin.customers.create')}}" class="bg-black text-white px-4 py-2 rounded-lg text-sm">
            + Add Customer
        </a>
    </div>

    {{-- TABLE --}}
    @if($tab === 'total')
        @include('admin.customer.tab.total')
    @elseif($tab === 'week')
        @include('admin.customer.tab.week')
    @elseif($tab === 'month')
        @include('admin.customer.tab.month')
    @elseif($tab === 'deletion')
        @include('admin.customer.tab.deletion')
    @elseif($tab === 'disabled')
        @include('admin.customer.tab.disabled')
    @elseif($tab === 'deleted')
        @include('admin.customer.tab.deleted')
    @endif

</div>
@endsection