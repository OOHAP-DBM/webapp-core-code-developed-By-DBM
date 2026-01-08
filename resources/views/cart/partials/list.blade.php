<div class="space-y-6">
    @foreach($items as $item)
        @include('cart.partials.item', ['item' => $item])
    @endforeach
</div>
