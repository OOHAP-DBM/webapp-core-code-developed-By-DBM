@php
$perPage = in_array(request('per_page'), [10, 20, 50, 100]) ? request('per_page') : 10;
$reviews = $hoarding->ratings()->with(['user', 'vendorReply'])->latest()->paginate($perPage);
@endphp

<div class="bg-white rounded-lg shadow-sm p-4">

    <h3 class="text-base font-semibold text-gray-900 mb-4">Customer Reviews</h3>

    {{-- Search --}}
    <div class="relative mb-4">
        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
            </svg>
        </span>
        <input
            id="reviewSearch"
            type="text"
            placeholder="Search by customer name or comment..."
            oninput="filterReviews(this.value)"
            class="w-full border border-gray-200 rounded pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500"
        >
    </div>

    {{-- Bulk Selection Bar --}}
    <div id="bulkBar" class="hidden items-center gap-3 rounded px-4 py-2 mb-3">
        <span class="text-sm font-medium">
            <span id="selectedCount">0</span> review selected on this page.
        </span>
        <button onclick="bulkDelete()"
            class="w-7 h-7 rounded-full border border-red-400 flex items-center justify-center text-red-500 hover:bg-red-50 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 011-1h4a1 1 0 011 1m-7 0h8"/>
            </svg>
        </button>
    </div>

    @if($reviews->count())

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm" id="reviewsTable">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-3 text-left">
                        <input type="checkbox" id="selectAll" class="rounded" onchange="toggleSelectAll(this)">
                    </th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 text-xs">S.N</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 text-xs">RATING</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 text-xs">CUSTOMER</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 text-xs">COMMENT</th>
                    <th class="px-3 py-3 text-left font-semibold text-gray-700 text-xs">REPLY</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-700 text-xs">ACTION</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="reviewsBody">
                @foreach($reviews as $i => $review)
                <tr class="hover:bg-gray-50 review-row"
                    data-customer="{{ strtolower($review->user->name ?? '') }}"
                    data-comment="{{ strtolower($review->review ?? '') }}">

                    {{-- Checkbox --}}
                    <td class="px-3 py-4">
                        <input type="checkbox" class="rounded row-checkbox" value="{{ $review->id }}"
                            onchange="updateBulkBar()">
                    </td>

                    {{-- SN --}}
                    <td class="px-3 py-4 text-gray-600">
                        {{ $reviews->firstItem() + $i }}
                    </td>

                    {{-- Rating --}}
                    <td class="px-3 py-4">
                        <div class="flex text-yellow-400 text-base">
                            @for($s = 1; $s <= 5; $s++)
                                @if($s <= $review->rating)
                                    <span>★</span>
                                @else
                                    <span class="text-gray-300">★</span>
                                @endif
                            @endfor
                        </div>
                    </td>

                    {{-- Customer --}}
                    <td class="px-3 py-4 font-medium text-gray-800">
                        {{ $review->user->name ?? 'Customer' }}
                    </td>

                    {{-- Comment --}}
                    <td class="px-3 py-4 text-gray-600 max-w-xs">
                        <p class="line-clamp-3">{{ $review->review ?? '-' }}</p>
                        <span class="text-xs text-yellow-600 mt-1 block">
                            {{ $review->created_at->format('M d, y') }}
                        </span>
                    </td>

                    {{-- Reply --}}
                    <td class="px-3 py-4 text-gray-600 max-w-xs">
                        @if($review->vendorReply)
                            <p class="line-clamp-3">{{ $review->vendorReply->reply }}</p>
                            <span class="text-xs text-yellow-600 mt-1 block">
                                {{ $review->vendorReply->created_at->format('M d, y') }}
                            </span>
                        @else
                            <span class="text-gray-400">–</span>
                        @endif
                    </td>

                    {{-- Action --}}
                    <td class="px-3 py-4">
                        <div class="flex items-center justify-end gap-3">
                            @if(!$review->vendorReply)
                                <button
                                    onclick="openReplyModal({{ $review->id }}, '', '{{ addslashes($review->review ?? '') }}', '{{ addslashes($review->user->name ?? 'Customer') }}')"
                                    class="flex flex-col mt-2 items-center text-green-600 hover:text-green-700 cursor-pointer"
                                >
                                    <svg width="18" height="15" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0 1.75C0 1.28587 0.184374 0.840752 0.512563 0.512563C0.840752 0.184374 1.28587 0 1.75 0H15.75C16.2141 0 16.6592 0.184374 16.9874 0.512563C17.3156 0.840752 17.5 1.28587 17.5 1.75V11.375C17.5 11.8391 17.3156 12.2842 16.9874 12.6124C16.6592 12.9406 16.2141 13.125 15.75 13.125H11.7372L9.36862 15.4936C9.20454 15.6577 8.98202 15.7498 8.75 15.7498C8.51798 15.7498 8.29546 15.6577 8.13138 15.4936L5.76275 13.125H1.75C1.28587 13.125 0.840752 12.9406 0.512563 12.6124C0.184374 12.2842 0 11.8391 0 11.375V1.75ZM15.75 1.75H1.75V11.375H6.125C6.35705 11.375 6.57957 11.4673 6.74362 11.6314L8.75 13.6378L10.7564 11.6314C10.9204 11.4673 11.143 11.375 11.375 11.375H15.75V1.75ZM3.5 4.8125C3.5 4.58044 3.59219 4.35788 3.75628 4.19378C3.92038 4.02969 4.14294 3.9375 4.375 3.9375H13.125C13.3571 3.9375 13.5796 4.02969 13.7437 4.19378C13.9078 4.35788 14 4.58044 14 4.8125C14 5.04456 13.9078 5.26712 13.7437 5.43122C13.5796 5.59531 13.3571 5.6875 13.125 5.6875H4.375C4.14294 5.6875 3.92038 5.59531 3.75628 5.43122C3.59219 5.26712 3.5 5.04456 3.5 4.8125ZM3.5 8.3125C3.5 8.08044 3.59219 7.85788 3.75628 7.69378C3.92038 7.52969 4.14294 7.4375 4.375 7.4375H9.625C9.85706 7.4375 10.0796 7.52969 10.2437 7.69378C10.4078 7.85788 10.5 8.08044 10.5 8.3125C10.5 8.54456 10.4078 8.76712 10.2437 8.93122C10.0796 9.09531 9.85706 9.1875 9.625 9.1875H4.375C4.14294 9.1875 3.92038 9.09531 3.75628 8.93122C3.59219 8.76712 3.5 8.54456 3.5 8.3125Z" fill="#009A5C"/>
                                    </svg>
                                    <span class="text-xs">Reply</span>
                                </button>
                            @else
                                <button
                                    onclick="openReplyModal({{ $review->id }}, '{{ addslashes($review->vendorReply->reply) }}', '{{ addslashes($review->review ?? '') }}', '{{ addslashes($review->user->name ?? 'Customer') }}')"
                                    class="w-7 h-7 rounded-full border border-green-500 flex items-center justify-center text-green-600 hover:bg-green-50 cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828A2 2 0 0111 16H9v-2a2 2 0 01.586-1.414z"/>
                                    </svg>
                                </button>
                            @endif

                            {{-- Delete --}}
                            <button
                                onclick="deleteReview({{ $review->id }})"
                                class="w-7 h-7 rounded-full border border-red-400 flex items-center justify-center text-red-500 hover:bg-red-50 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 011-1h4a1 1 0 011 1m-7 0h8"/>
                                </svg>
                            </button>
                        </div>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="flex flex-col sm:flex-row items-center justify-between mt-4 gap-3">
        <div class="flex items-center gap-2 text-sm text-gray-600">
            <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                <option>08</option>
                <option>16</option>
                <option>24</option>
            </select>
            <span>Showing {{ $reviews->firstItem() }} to {{ $reviews->lastItem() }} of {{ $reviews->total() }} records</span>
        </div>
        <div>
            {{ $reviews->links('pagination.dashboard-compact') }}
        </div>
    </div>

    @else

    {{-- Empty State --}}
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <svg width="191" height="119" viewBox="0 0 191 119" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M95.5 22.5L100.878 32.9147L112.375 34.5853L103.938 42.1875L106.75 53.4375L95.5 47.1094L84.25 53.4375L87.0625 42.1875L78.625 34.5853L90.4375 32.9147L95.5 22.5Z" fill="#1E1B18"/>
            <path d="M100.382 84.375L95.5 81.5625L106.75 61.875H123.625C124.364 61.8761 125.096 61.7314 125.779 61.4491C126.462 61.1668 127.082 60.7525 127.605 60.23C128.128 59.7074 128.542 59.0869 128.824 58.4039C129.106 57.721 129.251 56.989 129.25 56.25V22.5C129.251 21.761 129.106 21.029 128.824 20.3461C128.542 19.6631 128.128 19.0426 127.605 18.52C127.082 17.9975 126.462 17.5832 125.779 17.3009C125.096 17.0186 124.364 16.8739 123.625 16.875H67.375C66.636 16.8739 65.904 17.0186 65.2211 17.3009C64.5381 17.5832 63.9176 17.9975 63.395 18.52C62.8725 19.0426 62.4582 19.6631 62.1759 20.3461C61.8936 21.029 61.7489 21.761 61.75 22.5V56.25C61.7489 56.989 61.8936 57.721 62.1759 58.4039C62.4582 59.0869 62.8725 59.7074 63.395 60.23C63.9176 60.7525 64.5381 61.1668 65.2211 61.4491C65.904 61.7314 66.636 61.8761 67.375 61.875H92.6875V67.5H67.375C64.3913 67.5 61.5298 66.3147 59.42 64.2049C57.3103 62.0952 56.125 59.2337 56.125 56.25V22.5C56.125 19.5163 57.3103 16.6548 59.42 14.545C61.5298 12.4353 64.3913 11.25 67.375 11.25H123.625C126.609 11.25 129.47 12.4353 131.58 14.545C133.69 16.6548 134.875 19.5163 134.875 22.5V56.25C134.875 59.2337 133.69 62.0952 131.58 64.2049C129.47 66.3147 126.609 67.5 123.625 67.5H110.027L100.382 84.375Z" fill="#1E1B18"/>
        </svg>
        <p class="text-sm text-gray-500 mt-4">No reviews yet for this hoarding</p>
    </div>

    @endif

</div>

{{-- Reply Modal --}}
<div id="replyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white w-full max-w-lg rounded-lg shadow-lg relative overflow-hidden">
        <div class="bg-green-50 h-10 w-full"></div>
        <button onclick="closeReplyModal()"
            class="absolute top-2 right-3 text-gray-500 hover:text-black text-xl cursor-pointer">✕</button>
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-1">Reply to Review</h2>
            <p class="text-gray-500 text-sm mb-6">We will use your reply to improve customer experience</p>
            <p class="text-sm text-gray-700 mb-2">Customer's Comment</p>
            <textarea id="customerComment" rows="2" readonly
                class="w-full border border-gray-200 rounded p-3 text-sm bg-gray-50 text-gray-600 resize-none mb-4 focus:outline-none"></textarea>
            <p class="text-sm text-gray-700 mb-2">Let us know what we can do better to improve your experience.</p>
            <div class="border border-gray-300 rounded">
                <textarea id="replyText" rows="5" maxlength="250"
                    class="w-full p-3 text-sm outline-none resize-none rounded-t"
                    placeholder="Write here..."
                    oninput="document.getElementById('replyCharCount').innerText = this.value.length"></textarea>
                <div class="text-xs text-gray-400 text-right px-3 pb-2">
                    <span id="replyCharCount">0</span>/250
                </div>
            </div>
            <input type="hidden" id="replyReviewId">
            <div class="flex gap-3 mt-4">
                <button onclick="closeReplyModal()"
                    class="flex-1 py-3 text-sm border border-gray-300 rounded hover:bg-gray-50 cursor-pointer">
                    Cancel
                </button>
                <button onclick="submitReply()"
                    class="flex-1 py-3 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium cursor-pointer">
                    Submit Reply
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Search ──────────────────────────────────────────────
function filterReviews(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.review-row').forEach(row => {
        const customer = row.dataset.customer || '';
        const comment  = row.dataset.comment  || '';
        row.style.display = (!q || customer.includes(q) || comment.includes(q)) ? '' : 'none';
    });
    updateBulkBar();
}

// ── Select All ──────────────────────────────────────────
function toggleSelectAll(master) {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        const row = cb.closest('tr');
        if (row.style.display !== 'none') cb.checked = master.checked;
    });
    updateBulkBar();
}

// ── Bulk bar update ─────────────────────────────────────
function updateBulkBar() {
    const checked = getCheckedIds();
    const bar = document.getElementById('bulkBar');
    document.getElementById('selectedCount').innerText = checked.length;
    if (checked.length > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }
    // sync select-all state
    const all = document.querySelectorAll('.row-checkbox:not([style*="display: none"])');
    const allChecked = [...all].every(cb => cb.checked);
    document.getElementById('selectAll').checked = all.length > 0 && allChecked;
}

function getCheckedIds() {
    return [...document.querySelectorAll('.row-checkbox:checked')].map(cb => cb.value);
}

// ── Bulk Delete ─────────────────────────────────────────
function bulkDelete() {
    const ids = getCheckedIds();
    if (!ids.length) return;

    Swal.fire({
        title: `Delete ${ids.length} review(s)?`,
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(`/vendor/reviews/bulk-delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message ?? 'Something went wrong.', confirmButtonColor: '#16a34a' });
            }
        });
    });
}

// ── Single Delete ───────────────────────────────────────
function deleteReview(id) {
    Swal.fire({
        title: 'Delete this review?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(`/vendor/reviews/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({ icon: 'success', title: 'Deleted!', timer: 1500, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Failed', text: res.message ?? 'Something went wrong.', confirmButtonColor: '#16a34a' });
            }
        });
    });
}

// ── Reply Modal ─────────────────────────────────────────
function openReplyModal(reviewId, existingReply = '', customerComment = '', customerName = '') {
    document.getElementById('replyReviewId').value = reviewId;
    document.getElementById('replyText').value = existingReply;
    document.getElementById('replyCharCount').innerText = existingReply.length;
    document.getElementById('customerComment').value = customerComment || '—';
    const modal = document.getElementById('replyModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReplyModal() {
    const modal = document.getElementById('replyModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function submitReply() {
    const id    = document.getElementById('replyReviewId').value;
    const reply = document.getElementById('replyText').value.trim();
    if (!reply) {
        Swal.fire({ icon: 'warning', title: 'Reply required', text: 'Please write a reply before submitting.', confirmButtonColor: '#16a34a' });
        return;
    }
    fetch(`/vendor/reviews/${id}/reply`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ reply })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeReplyModal();
            Swal.fire({ icon: 'success', title: 'Reply submitted!', timer: 1500, showConfirmButton: false })
                .then(() => window.location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Failed', text: res.message ?? 'Something went wrong.', confirmButtonColor: '#16a34a' });
        }
    });
}
</script>