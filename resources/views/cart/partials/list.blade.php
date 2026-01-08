<div class="space-y-4">
    @foreach($items as $item)
        @include('cart.partials.item', ['item' => $item])
    @endforeach
</div>

