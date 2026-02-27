@props([
    'items' => []
])

@if(!empty($items))
<nav class="mt-1" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-1 text-sm text-gray-500">

        @foreach($items as $item)
            @if(!$loop->last && isset($item['route']))
                <li class="flex items-center space-x-1">
                    <a href="{{ $item['route'] }}" 
                       class="hover:text-gray-700 transition-colors duration-150">
                        {{ $item['label'] }}
                    </a>
                    <span class="text-gray-400">-</span>
                </li>
            @else
                <li class="text-gray-700 font-medium">
                    {{ $item['label'] }}
                </li>
            @endif
        @endforeach

    </ol>
</nav>
@endif
