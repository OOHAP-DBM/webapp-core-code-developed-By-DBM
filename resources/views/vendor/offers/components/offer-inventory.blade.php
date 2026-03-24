{{-- resources/views/vendor/offers/components/offer-inventory.blade.php --}}

<div class="bg-white rounded-lg shadow-sm border border-gray-200" style="position: sticky;">

    {{-- Header --}}
    <div class="px-4 lg:px-5 pt-4 lg:pt-5 flex items-center gap-3">
        <h3 class="font-bold text-gray-800 text-sm">Select Hoardings for Booking</h3>
        <span class="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full text-xs font-bold" id="offer-available-count">
            {{ count($hoardings) }}
        </span>
    </div>
    <p class="px-4 lg:px-5 text-xs text-gray-400 mt-0.5 mb-3">Browse and select hoardings to add them to the booking.</p>

    <div class="p-4 lg:p-5">

        {{-- Search + Filters Button --}}
        <div class="flex items-center gap-2 mb-3">
            <div class="relative flex-1">
                <input type="text" id="offer-hoarding-search"
                    placeholder="Search hoardings by location, size, or name"
                    class="w-full pl-9 border border-gray-300 text-xs focus:ring-green-500 rounded"
                    style="height:38px;"
                    oninput="offerHandleSearch(this.value)">
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/>
                    </svg>
                </span>
            </div>
            <button type="button"
                class="border border-gray-300 bg-white px-3 text-gray-700 text-xs font-medium hover:bg-gray-100 transition rounded whitespace-nowrap"
                style="height:38px;" onclick="openFilterModal()">
                Advanced Filters
            </button>
        </div>

        @include('vendor.pos.filter_modal')
        <div class="flex items-center justify-between gap-2 mb-3">
            <button id="offer-unselect-btn" onclick="offerUnselectAll()"
                class="hidden cursor-pointer text-[10px] font-bold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded transition">
                Unselect
            </button>
            <div class="ml-auto flex border border-gray-200 rounded overflow-hidden">
                <button onclick="setOfferView('grid')" id="offer-btn-grid" class="px-2 py-1.5 bg-gray-800 text-white">
                    <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M1 2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V2zM1 7a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V7zM1 12a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"/>
                    </svg>
                </button>
                <button onclick="setOfferView('list')" id="offer-btn-list" class="px-2 py-1.5 bg-white text-gray-600 hover:bg-gray-100">
                    <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Hoardings Grid --}}
        <div id="offer-grid"
            class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 max-h-[calc(100vh-300px)] overflow-y-auto pr-1 custom-scrollbar">

            @forelse($hoardings as $h)
            @php
                $isDooh = strtoupper($h->hoarding_type ?? $h->type ?? '') === 'DOOH';
                $loc    = $h->display_location ?? $h->address ?? $h->city ?? '';

                // Price
                $monthly     = $h->monthly_price ?? null;
                $baseMonthly = $h->base_monthly_price ?? null;
                $priceValue  = (is_numeric($monthly) && $monthly > 0)
                    ? $monthly
                    : ((is_numeric($baseMonthly) && $baseMonthly > 0) ? $baseMonthly : ($h->price_per_month ?? 0));
                $price = number_format($priceValue);

                // Media — pick first available media item
                $media = null;
                if ($isDooh && !empty($h->doohScreen->media)) {
                    $media = $h->doohScreen->media[0] ?? null;
                } elseif (!empty($h->hoardingMedia)) {
                    $media = $h->hoardingMedia[0] ?? null;
                }

                // Resolve final src + type
                $mediaSrc  = null;
                $mediaType = 'image'; // 'image' | 'video'
                if ($media) {
                    $mediaSrc  = $media->file_url ?? $media->url ?? $media->path ?? null;
                    $mediaType = in_array(strtolower($media->media_type ?? $media->type ?? ''), ['video', 'mp4', 'webm'])
                        ? 'video' : 'image';
                }
                if (!$mediaSrc) {
                    $mediaSrc  = $h->image_url ?? $h->thumbnail ?? '/placeholder.png';
                    $mediaType = ($mediaSrc && preg_match('/\.(mp4|webm|ogg)$/i', $mediaSrc)) ? 'video' : 'image';
                }
            @endphp

            <div class="offer-card border border-gray-200 rounded-lg overflow-hidden bg-white cursor-pointer"
                data-id="{{ $h->id }}"
                data-title="{{ addslashes($h->title ?? $h->name) }}"
                data-price="{{ $priceValue }}"
                data-type="{{ $h->hoarding_type ?? $h->type ?? 'OOH' }}"
                data-loc="{{ addslashes($loc) }}"
                data-slots="{{ $h->total_slots_per_day ?? 300 }}"
                onclick="offerToggleCard(this)">

                {{-- Media --}}
                <div class="card-media relative bg-gray-100 flex-shrink-0">
                    @if($media)
                        @component('components.media-preview', ['media' => $media, 'classes' => 'card-media-el', 'alt' => $h->title ?? $h->name])
                        @endcomponent
                    @else
                        <img src="/placeholder.png" alt="No Image" class="card-media-el">
                    @endif
                    <span class="check-badge hidden absolute top-1 left-1 bg-green-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded leading-tight z-10">✓</span>
                </div>

                {{-- Info --}}
                <div class="card-info p-2">
                    <p class="text-[10px] font-bold text-gray-800 truncate leading-tight">{{ $h->title ?? $h->name }}</p>
                    <p class="text-[9px] text-gray-400 truncate mt-0.5">{{ $loc ?: '—' }}</p>
                    <p class="text-[10px] font-bold text-gray-700 mt-0.5">
                        ₹{{ $price }}<span class="font-normal text-gray-400">/M</span>
                    </p>
                    @if($isDooh && ($h->total_slots_per_day ?? 0))
                    <p class="text-[9px] text-purple-600 font-semibold mt-0.5">{{ $h->total_slots_per_day }} slots/day</p>
                    @endif
                </div>

            </div>
            @empty
            <div class="col-span-4 flex flex-col items-center justify-center py-14 text-center">
                <svg class="w-10 h-10 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 3.5a7.5 7.5 0 0013.15 13.15z"/>
                </svg>
                <p class="text-sm font-bold text-gray-500">No hoardings found</p>
                <p class="text-xs text-gray-400">Try adjusting your filters</p>
            </div>
            @endforelse

        </div>

    </div>
</div>

<style>
/* ── Card ── */
.offer-card { transition: border-color .15s, box-shadow .15s; display: flex; flex-direction: column; }
.offer-card:hover:not(.selected) { border-color: #6ee7b7; background: #f9fafb; }
.offer-card.selected { border-color: #16a34a !important; box-shadow: 0 0 0 2px #bbf7d055; background: #f0fdf4 !important; }

/* ── Media block: fixed height, fills width ── */
.card-media { width: 100%; height: 90px; overflow: hidden; }
.card-media-el { width: 100%; height: 100%; object-fit: cover; display: block; }
.media-fallback {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    background: #f3f4f6;
}

/* ── Info always below media ── */
.card-info { flex: 1; }

/* ── List view ── */
#offer-grid.list-view { grid-template-columns: 1fr !important; }
#offer-grid.list-view .offer-card { flex-direction: row !important; align-items: stretch; }
#offer-grid.list-view .card-media { width: 80px !important; height: 70px !important; flex-shrink: 0; }
#offer-grid.list-view .card-info  { display: flex; flex-direction: column; justify-content: center; }
</style>

<script>
if (typeof offerSelectedHoardings === 'undefined') {
    window.offerSelectedHoardings = new Map();
}

function offerToggleCard(el) {
    const id         = parseInt(el.dataset.id);
    const badge      = el.querySelector('.check-badge');
    const isSelected = el.classList.toggle('selected');

    badge?.classList.toggle('hidden', !isSelected);

    if (isSelected) {
        offerSelectedHoardings.set(id, {
            id,
            title:               el.dataset.title,
            price_per_month:     parseFloat(el.dataset.price),
            hoarding_type:       el.dataset.type,
            display_location:    el.dataset.loc,
            total_slots_per_day: parseInt(el.dataset.slots),
        });

        // ✅ Selected card ko grid ke top pe move karo
        const grid = document.getElementById('offer-grid');
        grid.prepend(el);

    } else {
        offerSelectedHoardings.delete(id);
    }

    if (typeof offerUpdateSummary === 'function') offerUpdateSummary();
    document.getElementById('offer-unselect-btn')
        ?.classList.toggle('hidden', offerSelectedHoardings.size === 0);
}

function offerUnselectAll() {
    offerSelectedHoardings.clear();
    document.querySelectorAll('.offer-card.selected').forEach(el => {
        el.classList.remove('selected');
        el.querySelector('.check-badge')?.classList.add('hidden');
    });
    if (typeof offerUpdateSummary === 'function') offerUpdateSummary();
    document.getElementById('offer-unselect-btn')?.classList.add('hidden');
}

function offerHandleSearch(q) {
    q = q.trim().toLowerCase();
    let visible = 0;
    document.querySelectorAll('.offer-card').forEach(el => {
        const match = !q
            || el.dataset.title.toLowerCase().includes(q)
            || el.dataset.loc.toLowerCase().includes(q);
        el.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('offer-available-count').innerText = visible;
}

function setOfferView(mode) {
    const grid = document.getElementById('offer-grid');
    const btnG = document.getElementById('offer-btn-grid');
    const btnL = document.getElementById('offer-btn-list');
    if (mode === 'grid') {
        grid.classList.remove('list-view');
        btnG.className = 'px-2 py-1.5 bg-gray-800 text-white';
        btnL.className = 'px-2 py-1.5 bg-white text-gray-600 hover:bg-gray-100';
    } else {
        grid.classList.add('list-view');
        btnL.className = 'px-2 py-1.5 bg-gray-800 text-white';
        btnG.className = 'px-2 py-1.5 bg-white text-gray-600 hover:bg-gray-100';
    }
}
    function openFilterModal()  { document.getElementById('filterModal').classList.remove('hidden'); }
    function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); }
</script>