@if(isset($testimonials) && $testimonials->count())
<section class="py-20 bg-white overflow-hidden">
    <div class="container mx-auto px-4">

        {{-- Heading --}}
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                {{ $testimonialRole === 'vendor'
                    ? 'What Our Vendors Say'
                    : 'What Our Customers Say' }}
            </h2>
            <p class="text-gray-600 my-3">
                Trusted feedback from  users
            </p>
        </div>

        {{-- Carousel Wrapper --}}
        <div class="relative max-w-6xl mx-auto group">

            {{-- Track --}}
            <div id="testimonialTrack"
                 class="flex gap-6 transition-transform duration-700 ease-[cubic-bezier(.4,0,.2,1)]">

                @foreach($testimonials as $item)
                    <div class="testimonial-slide min-w-full md:min-w-[50%] lg:min-w-[33.333%] px-2">

                        <div
                            class="relative bg-gradient-to-br from-white to-gray-50
                                rounded-3xl px-6 pt-16 pb-8 text-center
                                border border-gray-200
                                transition-all duration-700
                                scale-95 opacity-70 shadow-lg">


                            {{-- Avatar --}}
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2">
                                <img
                                    src="{{ optional($item->user)->avatar
                                        ? (str_starts_with($item->user->avatar, 'http')
                                            ? $item->user->avatar
                                            : asset('storage/' . ltrim($item->user->avatar, '/')))
                                        : 'https://ui-avatars.com/api/?name=' . urlencode(optional($item->user)->name ?? 'User') . '&background=0D9488&color=fff'
                                    }}"
                                    alt="{{ optional($item->user)->name ?? 'User' }}"
                                    class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                            </div>


                            

                           

                            {{-- User --}}
                            <p class="font-semibold text-gray-900">
                                {{ optional($item->user)->name ?? 'Verified User' }}
                            </p>
                            <p class="text-xs text-gray-500 capitalize">
                                {{ $item->role }}
                            </p>
                             {{-- Rating --}}
                            <div class="text-yellow-400 text-sm mb-1">
                                {{ str_repeat('★', $item->rating) }}
                                {{ str_repeat('☆', 5 - $item->rating) }}
                            </div>
                            {{-- Message --}}
                            <p class="text-gray-700 text-sm leading-relaxed mb-4 italic">
                                “{{ $item->message }}”
                            </p>

                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Left Arrow --}}
            <button onclick="prevTestimonial()"
                class="absolute left-0 top-1/2 -translate-y-1/2
                    bg-white/80 backdrop-blur
                    rounded-full w-11 h-11
                    flex items-center justify-center
                    text-xl text-gray-700
                    opacity-0 group-hover:opacity-100
                    shadow-[0_6px_18px_rgba(0,0,0,0.35)]
                    hover:shadow-[0_10px_28px_rgba(0,0,0,0.45)]
                    transition-all duration-300 cursor-pointer">
                ‹
            </button>


            {{-- Right Arrow --}}
            <button onclick="nextTestimonial()"
                class="absolute right-0 top-1/2 -translate-y-1/2
                    bg-white/80 backdrop-blur
                    rounded-full w-11 h-11
                    flex items-center justify-center
                    text-xl text-gray-700
                    opacity-0 group-hover:opacity-100
                    shadow-[0_6px_18px_rgba(0,0,0,0.35)]
                    hover:shadow-[0_10px_28px_rgba(0,0,0,0.45)]
                    transition-all duration-300 cursor-pointer">
                ›
            </button>


        </div>
    </div>
</section>
<script>
(function () {
    'use strict';

    const track   = document.getElementById('testimonialTrack');
    if (!track) return;

    const GAP       = 24;      // gap-6 = 24px
    const ANIM_MS   = 700;
    const AUTO_MS   = 3500;

    // Grab original slides before any cloning
    const ORIG      = Array.from(track.querySelectorAll('.testimonial-slide'));
    const TOTAL     = ORIG.length;

    let perView     = 1;
    let index       = 0;   // current index within ORIG (0 … TOTAL-1)
    let animating   = false;
    let autoTimer   = null;

    /* ── How many slides are visible at this viewport width ── */
    function calcPerView() {
        const w = window.innerWidth;
        if (w >= 1024) return Math.min(3, TOTAL);
        if (w >= 768)  return Math.min(2, TOTAL);
        return 1;
    }

    /* ── Width of a single slide in px ── */
    function slideW() {
        return (track.parentElement.clientWidth - GAP * (perView - 1)) / perView;
    }

    /* ── Apply px width to every slide (originals + clones) ── */
    function applySizes() {
        const w = slideW();
        Array.from(track.querySelectorAll('.testimonial-slide')).forEach(s => {
            s.style.width    = w + 'px';
            s.style.minWidth = w + 'px';
        });
    }

    /* ── Clone slides for seamless infinite loop ── */
    function buildClones() {
        Array.from(track.querySelectorAll('[data-clone]')).forEach(n => n.remove());

        // Prepend clones of the LAST perView originals
        [...ORIG].slice(-perView).reverse().forEach(s => {
            const c = s.cloneNode(true);
            c.setAttribute('data-clone', 'pre');
            track.prepend(c);
        });

        // Append clones of the FIRST perView originals
        [...ORIG].slice(0, perView).forEach(s => {
            const c = s.cloneNode(true);
            c.setAttribute('data-clone', 'post');
            track.appendChild(c);
        });
    }

    /* ── DOM index of current slide (offset by prepended clones) ── */
    function domIdx() { return perView + index; }

    /* ── Translate the track ── */
    function moveTo(dIdx, animate) {
        const offset = dIdx * (slideW() + GAP);
        track.style.transition = animate
            ? `transform ${ANIM_MS}ms cubic-bezier(.4,0,.2,1)`
            : 'none';
        track.style.transform  = `translateX(-${offset}px)`;
    }

    /* ── Zoom ONLY the center card, dim everything else ── */
    function refreshCards() {
        const allSlides = Array.from(track.querySelectorAll('.testimonial-slide'));

        // Center original index when perView is odd → middle slot
        // When perView is even → right-of-center slot
        const centerSlot = Math.floor(perView / 2);
        const centerOrigIdx = index + centerSlot;

        allSlides.forEach((slide, di) => {
            const card    = slide.querySelector('div'); // the inner card div
            if (!card) return;

            const origIdx = di - perView; // map DOM index → original index
            const isCenter = origIdx === centerOrigIdx;

            if (isCenter) {
                card.style.transform = 'scale(1.06)';
                card.style.opacity   = '1';
                card.style.boxShadow = '0 20px 60px rgba(0,0,0,0.15)';
            } else {
                card.style.transform = 'scale(0.95)';
                card.style.opacity   = '0.65';
                card.style.boxShadow = '0 4px 16px rgba(0,0,0,0.08)';
            }
        });
    }

    /* ── Navigate ── */
    function next() {
        if (animating) return;
        animating = true;

        moveTo(domIdx() + 1, true);

        setTimeout(() => {
            index = (index + 1) % TOTAL;
            if (index === 0) moveTo(domIdx(), false); // silent jump to real start
            refreshCards();
            animating = false;
        }, ANIM_MS);
    }

    function prev() {
        if (animating) return;
        animating = true;

        moveTo(domIdx() - 1, true);

        setTimeout(() => {
            index = (index - 1 + TOTAL) % TOTAL;
            if (index === TOTAL - 1) moveTo(domIdx(), false); // silent jump to real end
            refreshCards();
            animating = false;
        }, ANIM_MS);
    }

    /* ── Auto scroll ── */
    function startAuto() {
        stopAuto();
        if (TOTAL <= perView) return;
        autoTimer = setInterval(next, AUTO_MS);
    }
    function stopAuto() { clearInterval(autoTimer); autoTimer = null; }

    /* ── Touch / swipe ── */
    let tx = 0, ty = 0;
    track.addEventListener('touchstart', e => {
        tx = e.touches[0].clientX;
        ty = e.touches[0].clientY;
        stopAuto();
    }, { passive: true });
    track.addEventListener('touchend', e => {
        const dx = tx - e.changedTouches[0].clientX;
        const dy = Math.abs(ty - e.changedTouches[0].clientY);
        if (Math.abs(dx) > 40 && Math.abs(dx) > dy) dx > 0 ? next() : prev();
        startAuto();
    }, { passive: true });

    /* ── Pause on hover ── */
    track.parentElement.addEventListener('mouseenter', stopAuto);
    track.parentElement.addEventListener('mouseleave', startAuto);

    /* ── Expose to onclick handlers in HTML ── */
    window.nextTestimonial = () => { stopAuto(); next(); startAuto(); };
    window.prevTestimonial = () => { stopAuto(); prev(); startAuto(); };

    /* ── Keyboard ── */
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft')  { stopAuto(); prev(); startAuto(); }
        if (e.key === 'ArrowRight') { stopAuto(); next(); startAuto(); }
    });

    /* ── Resize ── */
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(init, 200);
    });

    /* ── Init ── */
    function init() {
        stopAuto();
        animating = false;
        index     = 0;
        perView   = calcPerView();

        buildClones();
        applySizes();
        moveTo(domIdx(), false);
        refreshCards();
        startAuto();
    }

    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }

})();
</script>

@endif
