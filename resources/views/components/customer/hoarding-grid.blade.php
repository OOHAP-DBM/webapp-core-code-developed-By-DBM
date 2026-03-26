<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    @foreach($bestHoardings as $hoarding)
        @include('components.customer.hoarding-card', ['hoarding' => $hoarding])
    @endforeach
</div>