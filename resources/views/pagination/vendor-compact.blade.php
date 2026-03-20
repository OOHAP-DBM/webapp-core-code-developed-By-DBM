@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pages = [];

        if ($lastPage <= 5) {
            $pages = range(1, $lastPage);
        } elseif ($currentPage <= 5) {
            $pages = array_merge(range(1, 5), [$lastPage]);
        } elseif ($currentPage >= $lastPage - 4) {
            $pages = array_merge([1], range($lastPage - 4, $lastPage));
        } else {
            $pages = [1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage];
        }

        $pages = array_values(array_unique(array_filter($pages, function ($page) use ($lastPage) {
            return $page >= 1 && $page <= $lastPage;
        })));

        sort($pages);
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="overflow-x-auto">
        <div class="inline-flex items-center gap-3 whitespace-nowrap text-sm font-medium select-none">
            @if ($paginator->onFirstPage())
                <span
                    aria-hidden="true"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-400 cursor-not-allowed"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    aria-label="{{ __('Previous page') }}"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-gray-900"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @endif

            @php $previousPage = null; @endphp

            @foreach ($pages as $page)
                @if (!is_null($previousPage) && $page - $previousPage > 1)
                    <span class="text-gray-400">...</span>
                @endif

                @if ($page === $currentPage)
                    <span aria-current="page" class="h-6 min-w-[36px] px-2 inline-flex items-center justify-center rounded-md bg-[#00A86B] text-white">{{ $page }}</span>
                @else
                    <a
                        href="{{ $paginator->url($page) }}"
                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                        class="h-6 min-w-[36px] px-2 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-gray-900"
                    >
                        {{ $page }}
                    </a>
                @endif

                @php $previousPage = $page; @endphp
            @endforeach

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    aria-label="{{ __('Next page') }}"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-gray-900"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            @else
                <span
                    aria-hidden="true"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-400 cursor-not-allowed"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif