<div class="bg-white rounded-xl overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1400px] text-sm">
            <thead class="bg-[#F9FAFB] text-[#6B7280]">
                <tr>
                    <!-- <th class="px-4 py-3 text-left">
                        <input type="checkbox" class="accent-green-600">
                    </th> -->
                    <th class="px-4 py-3 text-left">S.N</th>
                    <th class="px-4 py-3 text-left">VENDOR NAME</th>
                    <th class="px-4 py-3 text-left">JOINING DATE</th>
                    <th class="px-4 py-3 text-left">DELETED ON</th>
                    <th class="px-4 py-3 text-left">DELETED BY</th>
                    <th class="px-4 py-3 text-left">DELETED REASON</th>
                    <th class="px-4 py-3 text-left">PHONE NUMBER</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($vendors as $i => $vendor)
                    <tr class="hover:bg-gray-50">
                        <!-- <td class="px-4 py-3">
                            <input type="checkbox" class="accent-green-600">
                        </td> -->

                        <td class="px-4 py-3">
                            {{ $vendors->firstItem() + $i }}
                        </td>

                        <td class="px-4 py-3 font-medium text-[#2563EB]">
                            {{ $vendor->user->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->approved_at?->format('M d, Y') ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->deleted_at?->format('M d, Y') ?? '-' }}
                        </td>

                        <td class="px-4 py-3 font-medium
                            {{ ($vendor->deleted_by ?? 'admin') === 'admin'
                                ? 'text-[#2563EB]'
                                : 'text-green-600' }}">
                            {{ ucfirst($vendor->deleted_by ?? 'Admin') }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->rejection_reason ?? 'Not Appropriate' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->user->phone ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            No deleted vendors found
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
