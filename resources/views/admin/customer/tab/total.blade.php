<table class="w-full text-left border-collapse">
    <thead>
        <tr class="bg-[#F9FAFB]">
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Image</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Customer ID</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Name</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Email</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Mobile Number</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Status</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA] text-center">Action</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-[#DADADA]">
        @foreach($customers as $customer)
        <tr class="hover:bg-gray-50/50 transition-colors">
            <td class="px-6 py-4">
                <img src="{{ $customer->image_url ?? 'https://ui-avatars.com/api/?name='.$customer->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-100 shadow-sm" alt="User">
            </td>
            <td class="px-6 py-4">
                <span class="text-[13px] font-bold text-[#10B981]">{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</span>
            </td>
            <td class="px-6 py-4">
                <a href="{{ route('admin.customers.show', $customer->id) }}" class="inline-flex items-center justify-center gap-1 text-[13px] font-bold text-[#2563EB] hover:text-blue-800 underline transition-all cursor-pointer">
                    {{ $customer->name }}
                </a>
            </td>
            <td class="px-6 py-4 text-[13px] text-[#949291] font-medium">
                {{ $customer->email }}
            </td>
            <td class="px-6 py-4 text-[13px] text-[#1E1B18] font-semibold">
                {{ $customer->phone ?? '-' }}
            </td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-[11px] font-bold {{ $customer->status == 'active' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                    {{ strtoupper($customer->status ?? 'ACTIVE') }}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                        <i class="fas fa-eye text-[12px]"></i>
                    </a>
                    <button class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all">
                        <i class="fas fa-trash text-[12px]"></i>
                    </button>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
