<table class="w-full text-left border-collapse">
    <thead>
        <tr class="bg-[#F9FAFB]">
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Image</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Customer ID</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Name</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Email</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Mobile Number</th>
            <th class="px-6 py-4 text-[12px] font-bold text-[#949291] uppercase border-b border-[#DADADA]">Status</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-[#DADADA]">
        @forelse($customers as $customer)
        <tr class="hover:bg-gray-50/50 transition-colors">
            <td class="px-6 py-4">
                <img src="{{ $customer->image_url ?? 'https://ui-avatars.com/api/?name='.$customer->name }}" class="w-10 h-10 rounded-lg object-cover border border-gray-100 shadow-sm" alt="User">
            </td>
            <td class="px-6 py-4">
                <span class="text-[13px] font-bold text-[#10B981]">{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</span>
            </td>
            <td class="px-6 py-4">
                <span class="text-[13px] font-bold text-[#969390]">
                    {{ $customer->name }}
                </span>
            </td>
            <td class="px-6 py-4 text-[13px] text-[#969390] font-medium">
                {{ $customer->email }}
            </td>
            <td class="px-6 py-4 text-[13px] text-[#969390] font-semibold">
                {{ $customer->phone ?? '-' }}
            </td>
            <td class="px-6 py-4 text-[13px] text-[#1E1B18] font-semibold">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                    Deleted-Customer
                </span>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center text-gray-400 py-8">No any customer found</td>
        </tr>
        @endforelse
    </tbody>
</table>
