{{-- Breadcrumb Navigation --}}
@if(isset($breadcrumbs) && count($breadcrumbs) > 0)
<nav class="mb-6 hidden md:block" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm">
        @foreach($breadcrumbs as $key => $breadcrumb)
            @if($loop->last)
                <li class="flex items-center text-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">{{ $breadcrumb['title'] }}</span>
                </li>
            @else
                <li class="flex items-center">
                    @if(!$loop->first)
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    @endif
                    <a href="{{ $breadcrumb['url'] }}" class="text-gray-700 hover:text-gray-900">
                        {{ $breadcrumb['title'] }}
                    </a>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
