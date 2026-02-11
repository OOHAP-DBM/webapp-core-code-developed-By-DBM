<div class="bg-white rounded-xl overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1400px] text-sm">
            <thead class="bg-[#F9FAFB] text-[#6B7280]">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" id="check-all" class="accent-green-600">
                    </th>
                    <th class="px-4 py-3 text-left">S.N</th>
                    <th class="px-4 py-3 text-left">VENDOR NAME</th>
                    <th class="px-4 py-3 text-left">JOINING DATE</th>
                    <th class="px-4 py-3 text-left">COMMISSION</th>
                    <th class="px-4 py-3 text-left">CITY</th>
                    <th class="px-4 py-3 text-left">EMAIL</th>
                    <th class="px-4 py-3 text-left">PHONE NUMBER</th>
                    <th class="px-4 py-3 text-left">#OF HOARDINGS</th>
                    <th class="px-4 py-3 text-left">#OF BOOKINGS</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($vendors as $i => $vendor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox"
                                class="row-checkbox accent-green-600"
                                value="{{ $vendor->id }}">
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendors->firstItem() + $i }}
                        </td>

                        <td class="px-4 py-3 font-medium">  
                            <a href="{{ route('admin.vendors.show', $vendor->user->id) }}"
                            class="text-[#2563EB] underline hover:text-blue-800">
                                {{ $vendor->user->name ?? '-' }}
                            </a>
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->approved_at?->format('M d, Y') ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-green-600 font-medium">
                            {{ $vendor->commission_percentage ? $vendor->commission_percentage.'%' : '10–20%' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->city ?? '-' }}
                        </td>

                        <td class="px-4 py-3 truncate max-w-[220px]">
                            {{ $vendor->user->email ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->user->phone ?? '-' }}
                        </td>

                        {{-- NOTE: Abhi inventory module nahi hai, isliye static UI match --}}
                        <td class="px-4 py-3 text-[#2563EB] font-medium">
                            {{ $vendor->hoardings_count ?? 0 }}
                        </td>

                        <td class="px-4 py-3 text-[#2563EB] font-medium">
                            {{ $vendor->bookings_count ?? 0 }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-gray-400">
                            No active vendors found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination (exact same look) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 text-sm text-[#6B7280]">
        <div class="flex items-center gap-2">
            <select class="border rounded-md px-2 py-1 text-sm">
                <option>10</option>
            </select>
            <span>
                Showing {{ $vendors->firstItem() }} to {{ $vendors->lastItem() }}
                of {{ $vendors->total() }} records
            </span>
        </div>

        <div>
            {{ $vendors->links() }}
        </div>
    </div>

</div>
