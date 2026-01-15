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
<div class="bg-white border border-gray-200 rounded-xl p-4">

    <div class="flex gap-4">

        {{-- IMAGE --}}
        <div class="w-28 h-20 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
            <img
                src="{{ $item->image_url ?? 'https://via.placeholder.com/300x200' }}"
                alt="{{ $item->title }}"
                class="w-full h-full object-cover"
            >
        </div>

        {{-- CONTENT --}}
        <div class="flex-1">

            {{-- HEADER --}}
        <div class="flex justify-between items-start gap-4">

            {{-- LEFT CONTENT --}}
            <div class="flex-1">

                {{-- TITLE --}}
                <h2 class="text-lg font-semibold text-gray-900 leading-snug">
                    {{ $item->title }}
                </h2>

                {{-- LOCATION --}}
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <svg width="10" height="14" viewBox="0 0 10 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.569 4.7845C9.569 4.15619 9.44525 3.53403 9.2048 2.95355C8.96436 2.37307 8.61194 1.84563 8.16765 1.40135C7.72337 0.957066 7.19593 0.604642 6.61545 0.364198C6.03497 0.123755 5.41281 0 4.7845 0C4.15619 0 3.53403 0.123755 2.95355 0.364198C2.37307 0.604642 1.84563 0.957066 1.40135 1.40135C0.957066 1.84563 0.604642 2.37307 0.364198 2.95355C0.123755 3.53403 -9.36254e-09 4.15619 0 4.7845C0 5.73251 0.279552 6.61423 0.755268 7.35788H0.7498L4.7845 13.67L8.8192 7.35788H8.81442C9.30713 6.59002 9.56904 5.69685 9.569 4.7845ZM4.7845 6.835C4.24067 6.835 3.71912 6.61897 3.33458 6.23442C2.95003 5.84988 2.734 5.32833 2.734 4.7845C2.734 4.24067 2.95003 3.71912 3.33458 3.33458C3.71912 2.95003 4.24067 2.734 4.7845 2.734C5.32833 2.734 5.84988 2.95003 6.23442 3.33458C6.61897 3.71912 6.835 4.24067 6.835 4.7845C6.835 5.32833 6.61897 5.84988 6.23442 6.23442C5.84988 6.61897 5.32833 6.835 4.7845 6.835Z" fill="#E75858"/>
                    </svg>
                    {{ $item->locality ?? '' }}
                    {{ $item->city }}, {{ $item->state }}
                </p>

                {{-- META LINE --}}
                <p class="text-xs text-gray-600 mt-1">
                    Type:
                    <span class="font-medium uppercase">{{ $item->hoarding_type }}</span>
                    &nbsp;&nbsp;•&nbsp;&nbsp;
                    Category:
                    <span class="font-medium">{{ ucfirst($item->category) }}</span>
                    &nbsp;&nbsp;•&nbsp;&nbsp;
                    Size:
                    <span class="font-medium">{{ $item->size }}</span>
                </p>
                {{-- CAMPAIGN DATE ROW --}}
                <div class="mt-3 flex items-center gap-3 text-xs">

                    {{-- CAMPAIGN DATE (FIGMA EXACT) --}}
                    <div class="relative mt-3">

                        <div
                            class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-lg px-3 py-2 cursor-pointer"
                            data-campaign-trigger="{{ $item->hoarding_id }}"data-grace-days="{{ (int) ($item->grace_period_days ?? 0) }}"
                            data-block-dates='@json($item->block_dates ?? [])'
                        >

                            {{-- ICON --}}
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-orange-50">
                                <svg class="w-4 h-4 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1z"/>
                                </svg>
                            </div>

                            {{-- TEXT --}}
                            <div class="leading-tight">
                                <p class="text-[11px] text-gray-500">Campaign Date</p>
                                <p class="text-xs font-medium text-gray-900"
                                id="campaign-text-{{ $item->hoarding_id }}">
                                    Select Date
                                </p>
                            </div>
                        </div>

                        {{-- HIDDEN INPUT (flatpickr attaches here) --}}
                        <input
                            type="text"
                            id="campaign-input-{{ $item->hoarding_id }}"
                            class="absolute opacity-0 pointer-events-none"
                            readonly
                        >
                    </div>


                    {{-- ACTION LINKS --}}
                    <button
                        type="button"
                        class="text-red-500 border border-none hover:underline cart-btn remove"
                        data-in-cart="1"
                        onclick="toggleCart(this, {{ $item->hoarding_id }})"
                         >
                    </button>


                    {{-- <a href="#" class="text-blue-500 hover:underline">
                        Bookmark
                    </a>--}}

                    {{-- SHARE BUTTON --}}
                    <div class="relative share-container-{{ $item->hoarding_id }}">
                        <button 
                            type="button"
                            class="text-blue-500 hover:underline focus:outline-none"
                            onclick="toggleSharePopup({{ $item->hoarding_id }})"
                        >
                            Share
                        </button>

                        {{-- SHARE POPUP --}}
                        <div 
                            id="share-popup-{{ $item->hoarding_id }}"
                            class="absolute hidden left-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 w-64"
                            style="box-shadow: 0 10px 40px rgba(0,0,0,0.15);"
                        >
                            <div class="p-4">
                                <p class="text-sm font-semibold text-gray-900 mb-3">Share this hoarding</p>
                                
                                <div class="space-y-2">
                                    {{-- WhatsApp --}}
                                    <button 
                                        type="button"
                                        onclick="shareVia('whatsapp', {{ $item->hoarding_id }}, '{{ addslashes($item->title) }}', '{{ $item->city }}, {{ $item->state }}', '{{ $item->hoarding_type === 'ooh' ? '₹' . number_format($item->monthly_price) . '/Month' : '₹' . number_format($item->slot_price) . '/Slot' }}')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="w-9 h-9 flex items-center justify-center rounded-full bg-green-50">
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">WhatsApp</span>
                                    </button>

                                    {{-- Facebook --}}
                                    <button 
                                        type="button"
                                        onclick="shareVia('facebook', {{ $item->hoarding_id }}, '{{ addslashes($item->title) }}', '{{ $item->city }}, {{ $item->state }}', '{{ $item->hoarding_type === 'ooh' ? '₹' . number_format($item->monthly_price) . '/Month' : '₹' . number_format($item->slot_price) . '/Slot' }}')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-50">
                                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">Facebook</span>
                                    </button>

                                    {{-- Twitter --}}
                                    <button 
                                        type="button"
                                        onclick="shareVia('twitter', {{ $item->hoarding_id }}, '{{ addslashes($item->title) }}', '{{ $item->city }}, {{ $item->state }}', '{{ $item->hoarding_type === 'ooh' ? '₹' . number_format($item->monthly_price) . '/Month' : '₹' . number_format($item->slot_price) . '/Slot' }}')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="w-9 h-9 flex items-center justify-center rounded-full bg-sky-50">
                                            <svg class="w-5 h-5 text-sky-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">Twitter</span>
                                    </button>

                                    {{-- Copy Link --}}
                                    <button 
                                        type="button"
                                        onclick="copyShareLink(event, {{ $item->hoarding_id }}, '{{ addslashes($item->title) }}')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-50">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700">Copy Link</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


           {{-- OFFERS --}}
           {{-- OFFERS --}}
            @if(!empty($item->packages) && count($item->packages))
                <div class="mt-4">
                    <p class="text-xs font-medium text-gray-600 mb-2">
                        Available Offers
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($item->packages as $pkg)
                            @include('cart.partials.offer-card', [
                                'pkg' => $pkg,
                                'item' => $item,
                                'selected' => isset($item->selected_package) && $item->selected_package && $item->selected_package->id == $pkg->id
                            ])
                        @endforeach
                    </div>
                </div>
            @endif

           {{-- PRICE --}}
            <div class="flex justify-end mt-4 text-right">

                {{-- OOH --}}
                @if($item->hoarding_type === 'ooh')
                    <div>
                        @if($item->base_monthly_price > $item->monthly_price)
                            <p
                                id="base-price-{{ $item->hoarding_id }}"
                                data-base-price="{{ $item->base_monthly_price }}"
                                class="text-sm text-gray-400 line-through {{ $item->base_monthly_price > $item->monthly_price ? '' : 'hidden' }}"
                              >
                                ₹{{ number_format($item->base_monthly_price) }}
                            </p>

                        @endif
                        <p
                            id="final-price-{{ $item->hoarding_id }}"
                            data-default-price="{{ $item->monthly_price }}"
                            class="text-lg font-semibold text-gray-900"
                          >                            
                            ₹{{ number_format($item->monthly_price) }}
                            <span class="text-sm text-gray-400">/ Month</span>
                        </p>
                    </div>
                @endif

                {{-- DOOH --}}
                @if($item->hoarding_type === 'dooh')
                    <div>
                        <p class="text-lg font-semibold text-gray-900">
                            ₹{{ number_format($item->slot_price) }}
                            <span class="text-sm text-gray-400">/ Slot</span>
                        </p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

{{-- Share Functionality Script (only included once per page) --}}
@once
<script>
    // Track currently open share popup
    let currentOpenSharePopup = null;

    // Toggle share popup
    function toggleSharePopup(hoardingId) {
        const popup = document.getElementById(`share-popup-${hoardingId}`);
        
        // Close any other open popup
        if (currentOpenSharePopup && currentOpenSharePopup !== popup) {
            currentOpenSharePopup.classList.add('hidden');
        }
        
        // Toggle current popup
        if (popup.classList.contains('hidden')) {
            popup.classList.remove('hidden');
            currentOpenSharePopup = popup;
        } else {
            popup.classList.add('hidden');
            currentOpenSharePopup = null;
        }
    }

    // Close popup when clicking outside
    document.addEventListener('click', function(event) {
        const shareContainers = document.querySelectorAll('[class*="share-container-"]');
        let clickedInsideShare = false;
        
        shareContainers.forEach(container => {
            if (container.contains(event.target)) {
                clickedInsideShare = true;
            }
        });
        
        if (!clickedInsideShare && currentOpenSharePopup) {
            currentOpenSharePopup.classList.add('hidden');
            currentOpenSharePopup = null;
        }
    });

    // Generate SEO-friendly slug
    function generateSlug(title) {
        return title
            .toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-')      // Replace spaces with hyphens
            .replace(/--+/g, '-')      // Replace multiple hyphens with single
            .trim();
    }

    // Generate shareable URL
    function generateShareUrl(hoardingId, title) {
        const slug = generateSlug(title);
        const baseUrl = window.location.origin;
        return `${baseUrl}/hoardings/${hoardingId}/${slug}`;
    }

    // Share via different platforms
    function shareVia(platform, hoardingId, title, location, price) {
        const shareUrl = generateShareUrl(hoardingId, title);
        const shareText = `${title}\n📍 ${location}\n💰 ${price}`;
        
        let url = '';
        
        switch(platform) {
            case 'whatsapp':
                url = `https://wa.me/?text=${encodeURIComponent(shareText + '\n\n' + shareUrl)}`;
                break;
                
            case 'facebook':
                url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
                break;
                
            case 'twitter':
                url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`;
                break;
        }
        
        if (url) {
            window.open(url, '_blank', 'width=600,height=400');
            // Close the popup after sharing
            const popup = document.getElementById(`share-popup-${hoardingId}`);
            if (popup) {
                popup.classList.add('hidden');
                currentOpenSharePopup = null;
            }
        }
    }

    // Copy link to clipboard with fallback
    function copyShareLink(event, hoardingId, title) {
        const shareUrl = generateShareUrl(hoardingId, title);
        const copyButton = event.target.closest('button');
        const popup = document.getElementById(`share-popup-${hoardingId}`);
        const originalText = copyButton.querySelector('span').textContent;
        
        // Try modern clipboard API first
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(shareUrl)
                .then(() => {
                    showCopiedFeedback(copyButton, originalText, popup);
                })
                .catch(() => {
                    // Fallback to legacy method
                    if (fallbackCopyToClipboard(shareUrl)) {
                        showCopiedFeedback(copyButton, originalText, popup);
                    } else {
                        alert('Unable to copy. Please copy manually: ' + shareUrl);
                    }
                });
        } else {
            // Use fallback for older browsers
            if (fallbackCopyToClipboard(shareUrl)) {
                showCopiedFeedback(copyButton, originalText, popup);
            } else {
                alert('Unable to copy. Please copy manually: ' + shareUrl);
            }
        }
    }

    // Fallback copy method for older browsers or non-secure contexts
    function fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.top = '-9999px';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            return successful;
        } catch (err) {
            document.body.removeChild(textArea);
            return false;
        }
    }

    // Show "Copied!" feedback
    function showCopiedFeedback(copyButton, originalText, popup) {
        copyButton.querySelector('span').textContent = 'Copied!';
        copyButton.querySelector('span').classList.add('text-green-600', 'font-semibold');
        
        setTimeout(() => {
            copyButton.querySelector('span').textContent = originalText;
            copyButton.querySelector('span').classList.remove('text-green-600', 'font-semibold');
            
            if (popup) {
                popup.classList.add('hidden');
                currentOpenSharePopup = null;
            }
        }, 2000);
    }
</script>
@endonce