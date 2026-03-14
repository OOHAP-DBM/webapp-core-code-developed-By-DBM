@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $pages = [];

        if ($lastPage <= 7) {
            $pages = range(1, $lastPage);
        } elseif ($currentPage <= 4) {
            $pages = array_merge(range(1, 5), [$lastPage]);
        } elseif ($currentPage >= $lastPage - 3) {
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
            @php $previousPage = null; @endphp

            @foreach ($pages as $page)
                @if (!is_null($previousPage) && $page - $previousPage > 1)
                    <span class="text-gray-400">...</span>
                @endif

                @if ($page === $currentPage)
                    <span aria-current="page" class="h-9 min-w-[36px] px-2 inline-flex items-center justify-center rounded-md bg-[#00A86B] text-white">{{ $page }}</span>
                @else
                    <a
                        href="{{ $paginator->url($page) }}"
                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                        class="h-9 min-w-[36px] px-2 inline-flex items-center justify-center rounded-md text-gray-700 hover:text-gray-900"
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
                    &gt;
                </a>
            @else
                <span
                    aria-hidden="true"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-md text-gray-300 cursor-not-allowed"
                >
                    &gt;
                </span>
            @endif
        </div>
    </nav>
@endif