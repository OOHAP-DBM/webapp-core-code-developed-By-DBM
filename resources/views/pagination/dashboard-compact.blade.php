@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $startPage = max(1, $currentPage - 1);
        $endPage = min($lastPage, $startPage + 2);
        $startPage = max(1, $endPage - 2);
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="overflow-x-auto">
        <div class="flex items-center gap-2 whitespace-nowrap">
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 rounded-md border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed text-sm">{!! __('pagination.previous') !!}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">{!! __('pagination.previous') !!}</a>
            @endif

            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page === $currentPage)
                    <span aria-current="page" class="min-w-[40px] text-center px-3 py-2 rounded-md border border-[#00A86B] bg-[#00A86B] text-white font-semibold text-sm">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="min-w-[40px] text-center px-3 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">{{ $page }}</a>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">{!! __('pagination.next') !!}</a>
            @else
                <span class="px-4 py-2 rounded-md border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed text-sm">{!! __('pagination.next') !!}</span>
            @endif
        </div>
    </nav>
@endif