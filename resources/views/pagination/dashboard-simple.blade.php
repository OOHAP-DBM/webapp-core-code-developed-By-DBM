@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="overflow-x-auto">
        <div class="flex items-center gap-2 whitespace-nowrap">
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 rounded-md border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed text-sm">{!! __('pagination.previous') !!}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">{!! __('pagination.previous') !!}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">{!! __('pagination.next') !!}</a>
            @else
                <span class="px-4 py-2 rounded-md border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed text-sm">{!! __('pagination.next') !!}</span>
            @endif
        </div>
    </nav>
@endif