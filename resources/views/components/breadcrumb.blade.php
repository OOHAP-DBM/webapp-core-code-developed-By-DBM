@props([
    'items' => []
])

@if(!empty($items))
<nav class="mt-1 w-full overflow-x-auto" aria-label="Breadcrumb">
    <ol class="flex items-center text-sm text-gray-500 whitespace-nowrap min-w-0 gap-1 px-1 md:px-0" style="scrollbar-width: thin;">
        @foreach($items as $item)
            @if(!$loop->last && isset($item['route']))
                <li class="flex items-center min-w-0">
                    <a href="{{ $item['route'] }}" 
                       class="hover:text-gray-700 transition-colors duration-150 truncate max-w-[120px] md:max-w-none">
                        {{ $item['label'] }}
                    </a>
                    <span class="text-gray-400 mx-1">-</span>
                </li>
            @else
                <li class="text-gray-700 font-medium truncate max-w-[120px] md:max-w-none">
                    {{ $item['label'] }}
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
