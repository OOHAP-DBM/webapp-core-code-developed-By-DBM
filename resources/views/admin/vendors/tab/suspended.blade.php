<div class="bg-white rounded-xl overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1400px] text-sm text-center align-middle">
            <thead class="bg-[#F9FAFB] text-[#6B7280]">
                <tr>
                    <th class="px-4 py-3">
                        <input type="checkbox" id="check-all" class="accent-green-600">
                    </th>
                    <th class="px-4 py-3">S.N</th>
                    <th class="px-4 py-3">VENDOR NAME</th>
                    <th class="px-4 py-3">JOINING DATE</th>
                    <th class="px-4 py-3">COMMISSION</th>
                    <th class="px-4 py-3">CITY</th>
                    <th class="px-4 py-3">EMAIL</th>
                    <th class="px-4 py-3">PHONE NUMBER</th>
                    <th class="px-4 py-3"> NO. OF HOARDINGS</th>
                    <th class="px-4 py-3"> NO. OF BOOKINGS</th>
                </tr>
            </thead>

            <tbody class="divide-y text-center align-middle">
                @forelse($vendors as $i => $vendor)
                    <tr class="hover:bg-gray-50 align-middle">
                        <td class="px-4 py-3 align-middle">
                            <input type="checkbox"
                                class="row-checkbox accent-green-600"
                                value="{{ $vendor->id }}">
                        </td>

                        <td class="px-4 py-3 align-middle">
                            {{ $vendors->firstItem() + $i }}
                        </td>

                        <td class="px-4 py-3 font-medium text-[#2563EB] align-middle">
                            {{ $vendor->user->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 align-middle">
                            {{ $vendor->approved_at?->format('M d, Y') ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-green-600 font-medium align-middle">
                            {{ $vendor->commission_percentage ? $vendor->commission_percentage.'%' : '10–20%' }}
                        </td>

                        <td class="px-4 py-3 align-middle">
                            {{ $vendor->city ?? '-' }}
                        </td>

                        <td class="px-4 py-3 truncate max-w-[220px] align-middle">
                            {{ $vendor->user->email ?? '-' }}
                        </td>

                        <td class="px-4 py-3 align-middle">
                            {{ $vendor->user->phone ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-[#2563EB] font-medium align-middle cursor-pointer hover:underline hover:text-blue-800"
                            onclick="window.location='{{ route('admin.vendors.hoardings', $vendor->user->id) }}'">
                            {{ ($vendor->active_hoardings_count ?? 0) . ' / ' . ($vendor->total_hoardings_count ?? 0) }}
                        </td>

                        <td class="px-4 py-3 text-[#2563EB] font-medium align-middle">
                            {{ $vendor->bookings_count ?? 0 }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-gray-400">
                            No disabled vendors found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination (same as figma) --}}
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
