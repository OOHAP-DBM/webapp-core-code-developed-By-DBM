@props(['href', 'class' => '', 'title' => null])
<a href="{{ $href }}" target="_blank" rel="noopener noreferrer" class="{{ $class }}" @if($title) title="{{ $title }}" @endif>
    {{ $slot }}
</a>
