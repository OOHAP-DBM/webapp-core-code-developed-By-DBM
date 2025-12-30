<div class="bg-white rounded-xl overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full min-w-[1100px] text-sm">
            <thead class="bg-[#F9FAFB] text-[#6B7280]">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <input type="checkbox" class="accent-green-600">
                    </th>
                    <th class="px-4 py-3 text-left">S.N</th>
                    <th class="px-4 py-3 text-left">VENDOR NAME</th>
                    <th class="px-4 py-3 text-left">REQUESTED DATE</th>
                    <th class="px-4 py-3 text-left">CITY</th>
                    <th class="px-4 py-3 text-left">EMAIL</th>
                    <th class="px-4 py-3 text-left">PHONE NUMBER</th>
                    <th class="px-4 py-3 text-center">ACTION</th>
                    <th class="px-4 py-3 text-center"></th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($vendors as $i => $vendor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" class="accent-green-600">
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendors->firstItem() + $i }}
                        </td>

                        <td class="px-4 py-3 font-medium text-[#2563EB]">
                            {{ $vendor->user->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->created_at?->format('M d, Y') }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->city ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->user->email ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $vendor->user->phone ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            <form
                                action="{{ route('admin.vendors.approve', $vendor->id) }}"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to approve this vendor?')"
                            >
                                @csrf
                                <button
                                    type="submit"
                                    class="bg-[#F59E0B] text-white px-6 py-2 rounded-lg text-sm hover:bg-[#d48806]"
                                >
                                    Approve
                                </button>
                            </form>
                        </td>

                        <td class="px-4 py-3 text-center relative" x-data="{ open: false }">

                            <!-- Three Dots -->
                            <button
                                @click="open = !open"
                                class="text-gray-500 hover:text-gray-700 text-xl font-bold focus:outline-none"
                            >
                                ⋮
                            </button>

                            <!-- Dropdown -->
                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-6 top-8 w-36 bg-white border border-gray-200 rounded-md shadow-lg z-50"
                            >
                                <form
                                    action="{{ route('admin.vendors.reject', $vendor->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to reject this vendor?')"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                                    >
                                        Reject Now
                                    </button>
                                </form>
                            </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            No vendors found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
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
