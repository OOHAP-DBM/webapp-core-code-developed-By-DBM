<style>
    .flatpickr-calendar {
        margin-top: 6px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        border-radius: 10px;
    }

    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #145efc !important;
        border-color: #145efc !important;
    }
</style>
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

    {{-- TOP SECTION: Image + Content --}}
    <div class="p-3 sm:p-4"
         onclick="if(!event.target.closest('.cart-btn') && !event.target.closest('.text-blue-500')) window.location.href='{{ route('hoardings.show', $item->slug ?? $item->hoarding_id) }}';">

        <div class="flex gap-3">

            {{-- IMAGE --}}
            <div class="w-20 h-20 sm:w-28 sm:h-24 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                @php
                    $mediaItem = null;
                    if (isset($item->media) && $item->media instanceof \Illuminate\Support\Collection && $item->media->count()) {
                        $mediaItem = $item->media->first();
                    } elseif (isset($item->media) && is_object($item->media) && method_exists($item->media, 'isVideo')) {
                        $mediaItem = $item->media;
                    }
                @endphp
                @if($mediaItem)
                    <x-media-preview :media="$mediaItem" :alt="$item->title ?? 'Hoarding'" />
                @else
                    <img
                        src="{{ $item->image_url ?? 'https://via.placeholder.com/300x200' }}"
                        alt="{{ $item->title }}"
                        class="w-full h-full object-cover"
                    >
                @endif
            </div>

            {{-- CONTENT --}}
            <div class="flex-1 min-w-0">

                {{-- TITLE --}}
                <h2 class="text-sm sm:text-base font-semibold text-gray-900 leading-snug line-clamp-2">
                    {{ $item->title }}
                </h2>

                {{-- LOCATION --}}
                <p class="text-xs text-gray-500 mt-1 flex items-start gap-1">
                    <svg class="flex-shrink-0 mt-[2px]" width="9" height="12" viewBox="0 0 10 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.569 4.7845C9.569 4.15619 9.44525 3.53403 9.2048 2.95355C8.96436 2.37307 8.61194 1.84563 8.16765 1.40135C7.72337 0.957066 7.19593 0.604642 6.61545 0.364198C6.03497 0.123755 5.41281 0 4.7845 0C4.15619 0 3.53403 0.123755 2.95355 0.364198C2.37307 0.604642 1.84563 0.957066 1.40135 1.40135C0.957066 1.84563 0.604642 2.37307 0.364198 2.95355C0.123755 3.53403 -9.36254e-09 4.15619 0 4.7845C0 5.73251 0.279552 6.61423 0.755268 7.35788H0.7498L4.7845 13.67L8.8192 7.35788H8.81442C9.30713 6.59002 9.56904 5.69685 9.569 4.7845ZM4.7845 6.835C4.24067 6.835 3.71912 6.61897 3.33458 6.23442C2.95003 5.84988 2.734 5.32833 2.734 4.7845C2.734 4.24067 2.95003 3.71912 3.33458 3.33458C3.71912 2.95003 4.24067 2.734 4.7845 2.734C5.32833 2.734 5.84988 2.95003 6.23442 3.33458C6.61897 3.71912 6.835 4.24067 6.835 4.7845C6.835 5.32833 6.61897 5.84988 6.23442 6.23442C5.84988 6.61897 5.32833 6.835 4.7845 6.835Z" fill="#E75858"/>
                    </svg>
                    <span>{{ $item->locality ?? '' }} {{ $item->city }}, {{ $item->state }}</span>
                </p>

                {{-- META LINE --}}
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    <span class="font-medium uppercase">{{ $item->hoarding_type }}</span>
                    &nbsp;•&nbsp;
                    <span class="font-medium">{{ ucfirst($item->category) }}</span>
                    &nbsp;•&nbsp;
                    <span class="font-medium">{{ $item->size }}</span>
                </p>

                {{-- ACTIONS: Remove + Share + PRICE all in one row --}}
                <div class="mt-2 flex items-center justify-between gap-2">

                    {{-- LEFT: Remove + Share --}}
                    <div class="flex items-center gap-3 text-xs">
                        <button
                            id="cart-btn-{{ $item->hoarding_id }}"
                            type="button"
                            class="cart-btn text-red-500 border border-none hover:underline remove cursor-pointer"
                            data-id="{{ $item->hoarding_id }}"
                            data-in-cart="1"
                            data-auth="{{ auth()->check() ? '1' : '0' }}"
                            onclick="toggleCart(this, {{ $item->hoarding_id }})"
                        >
                            Remove
                        </button>

                        <div class="relative inline-block">
                            <button
                                type="button"
                                class="text-blue-500 hover:underline cursor-pointer"
                                onclick="toggleShareMenu(this)">
                                Share
                            </button>
                            <div class="cart-share-menu hidden absolute z-20 bg-white border rounded-lg shadow-md p-2 mt-1 min-w-[160px]">
                                <div class="flex flex-col gap-1 text-xs">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('hoardings.show', $item->slug ?? $item->hoarding_id)) }}"
                                       target="_blank" rel="noopener noreferrer" class="text-blue-700 hover:underline">Facebook</a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('hoardings.show', $item->slug ?? $item->hoarding_id)) }}&text={{ urlencode($item->title) }}"
                                       target="_blank" rel="noopener noreferrer" class="text-sky-500 hover:underline">Twitter</a>
                                    <a href="https://api.whatsapp.com/send?text={{ urlencode($item->title . ' - ' . route('hoardings.show', $item->slug ?? $item->hoarding_id)) }}"
                                       target="_blank" rel="noopener noreferrer" class="text-green-600 hover:underline">WhatsApp</a>
                                    <button type="button" class="text-gray-700 hover:underline text-left cursor-pointer"
                                            onclick="copyCartLink('{{ route('hoardings.show', $item->slug ?? $item->hoarding_id) }}')">Copy Link</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: PRICE (same row as Remove/Share) --}}
                    @php
                        $finalPrice = (!empty($item->monthly_price) && $item->monthly_price > 0)
                            ? $item->monthly_price
                            : $item->base_monthly_price;
                        $basePrice = $item->base_monthly_price ?? 0;
                    @endphp
                    <div class="text-right flex-shrink-0">
                        @if($basePrice > $finalPrice)
                            <p id="base-price-{{ $item->hoarding_id }}"
                               data-base-price="{{ $basePrice }}"
                               class="text-xs text-gray-400 line-through">
                                ₹{{ number_format($basePrice) }}
                            </p>
                        @endif
                        <p id="final-price-{{ $item->hoarding_id }}"
                           data-default-price="{{ $finalPrice }}"
                           class="text-sm font-semibold text-gray-900">
                            ₹{{ number_format($finalPrice) }}
                            <span class="text-sm text-gray-400 font-bold">/ Month</span>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- OFFERS SECTION --}}
    @if(!empty($item->packages) && count($item->packages))
        <div class="px-3 sm:px-4 pb-3 sm:pb-4 border-t border-gray-100 pt-3">
            <p class="text-xs font-medium text-gray-600 mb-2">Available Offers</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-3">
                @foreach($item->packages as $pkg)
                    @php
                        $duration = $pkg->duration ?? 1;
                        $percent = $pkg->percent ?? 0;
                        $basePrice = $item->base_monthly_price ?? 0;
                        $packagePrice = $basePrice * $duration;
                        $discountAmount = 0;
                        if($percent > 0){
                            $discountAmount = $packagePrice * $percent / 100;
                            $packagePrice = $packagePrice - $discountAmount;
                        }
                    @endphp
                    @include('cart.partials.offer-card', [
                        'pkg' => $pkg,
                        'item' => $item,
                        'selected' => isset($item->selected_package) && $item->selected_package && $item->selected_package->id == $pkg->id,
                        'packagePrice' => $packagePrice,
                        'discountAmount' => $discountAmount
                    ])
                @endforeach
            </div>
        </div>
    @endif

</div>

<script>
    function copyCartLink(link) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(link).then(() => {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Link copied to clipboard!',
                        showConfirmButton: false,
                        timer: 1800,
                        timerProgressBar: true
                    });
                } else {
                    alert('Link copied to clipboard!');
                }
            });
        } else {
            const tempInput = document.createElement('input');
            tempInput.value = link;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Link copied to clipboard!',
                    showConfirmButton: false,
                    timer: 1800,
                    timerProgressBar: true
                });
            } else {
                alert('Link copied to clipboard!');
            }
    
        }
    }

    function toggleShareMenu(btn) {
        // close all other menus
        document.querySelectorAll('.cart-share-menu').forEach(menu => {
            if (menu !== btn.nextElementSibling) {
                menu.classList.add('hidden');
            }
        });

        const menu = btn.nextElementSibling;
        menu.classList.toggle('hidden');

        // close on outside click
        function outsideClick(e) {
            if (!menu.contains(e.target) && e.target !== btn) {
                menu.classList.add('hidden');
                document.removeEventListener('mousedown', outsideClick);
            }
        }

        setTimeout(() => {
            document.addEventListener('mousedown', outsideClick);
        }, 0);
    }
</script>
