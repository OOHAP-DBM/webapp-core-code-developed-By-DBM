@extends('layouts.admin')

@section('title', 'Total Customers')

@section('content')
<div class="min-h-screen bg-[#F9FAFB] -m-6 p-6 font-poppins">
    
    {{-- Top Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-[20px] font-bold text-[#1E1B18]">Customer Management</h1>
            <p class="text-[12px] text-[#949291] mt-1 font-medium">
                Home > Customers Management > <span class="text-[#1E1B18] font-bold">Total Customers</span>
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" placeholder="Search customer..." class="pl-9 pr-4 py-2 bg-white border border-[#DADADA] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#10B981]">
            </div>
            <button class="bg-[#10B981] text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 hover:bg-[#0da673] transition-all">
                <i class="fas fa-plus"></i> Create Profile
            </button>
        </div>
    </div>

    @if($totalCustomerCount === 0)
        {{-- [Keep your existing empty state code here] --}}
    @else
        {{-- Figma Node 10-14424: Customer Table --}}
        <div class="bg-white border border-[#DADADA] rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F9FAFB]">
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Image</th>
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Customer ID</th>
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Name</th>
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Email</th>
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Mobile Number</th>
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Gender</th>
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Status</th>
                            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA] text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#DADADA]">
                        @foreach($customers as $customer)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <img src="{{ $customer->image_url ?? 'https://ui-avatars.com/api/?name='.$customer->name }}" 
                                     class="w-10 h-10 rounded-lg object-cover border border-gray-100 shadow-sm" alt="User">
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[13px] font-bold text-[#10B981]">#CUST-{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a
                                    href="{{ route('admin.customers.show', $customer->id) }}"
                                    class="inline-flex items-center justify-center gap-1
                                        text-[13px] font-bold text-[#2563EB]
                                        hover:text-blue-800 underline
                                        transition-all cursor-pointer"
                                >
                                    {{ $customer->name }}
                                </a>
                            </td>


                            <td class="px-6 py-4 text-[13px] text-[#949291] font-medium">
                                {{ $customer->email }}
                            </td>
                            <td class="px-6 py-4 text-[13px] text-[#1E1B18] font-semibold">
                                {{ $customer->phone ?? '+91 00000 00000' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[13px] text-[#1E1B18] font-medium">{{ $customer->gender ?? 'Male' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold {{ $customer->status == 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    {{ strtoupper($customer->status ?? 'ACTIVE') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                                        <i class="fas fa-eye text-[12px]"></i>
                                    </button>
                                    <button class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center hover:bg-orange-600 hover:text-white transition-all">
                                        <i class="fas fa-edit text-[12px]"></i>
                                    </button>
                                    <button class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all">
                                        <i class="fas fa-trash text-[12px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 flex items-center justify-between border-t border-[#DADADA]">
                <span class="text-[12px] text-[#949291] font-medium">Showing 1 to {{ $customers->count() }} of {{ $totalCustomerCount }} entries</span>
                <div class="flex gap-2">
                    {{-- {{ $customers->links() }} --}}
                </div>
            </div>
        </div>
    @endif
</div>
@endsection