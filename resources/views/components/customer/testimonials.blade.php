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
                    transition-all duration-300">
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
                    transition-all duration-300">
                ›
            </button>


        </div>
    </div>
</section>
<script>
    let testimonialIndex = 0;
    let autoInterval;

    const track = document.getElementById('testimonialTrack');

    const getSlides = () => [...track.children];

    const visibleSlides = () => {
        if (window.innerWidth >= 1024) return 3;
        if (window.innerWidth >= 768) return 2;
        return 1;
    };

    const slideWidth = () => {
        const slide = track.children[0];
        return slide ? slide.offsetWidth + 24 : 0; // gap-6 = 24px
    };

    // 🔁 Clone first slides for infinite effect
    const initClones = () => {
        const slides = getSlides();
        const cloneCount = visibleSlides();

        slides.slice(0, cloneCount).forEach(slide => {
            track.appendChild(slide.cloneNode(true));
        });
    };

    const updateTestimonial = (animate = true) => {
    const slides = getSlides();
    const slideW = slideWidth();
    const containerCenter =
        (testimonialIndex * slideW) + (slideW * visibleSlides() / 2);

    track.style.transition = animate
        ? 'transform 0.8s cubic-bezier(.4,0,.2,1)'
        : 'none';

    track.style.transform = `translateX(-${testimonialIndex * slideW}px)`;

    slides.forEach((slide, i) => {
        const card = slide.querySelector('div.relative');

        // slide ka center position
        const slideCenter = (i * slideW) + (slideW / 2);

        // distance from viewport center
        const distance = Math.abs(slideCenter - containerCenter);

            if (distance < slideW / 2) {
                // ✅ ONLY TRUE CENTER SLIDE
                card.classList.add('scale-105', 'opacity-100', 'shadow-2xl');
                card.classList.remove('scale-95', 'opacity-70');
                } else {
                    // ❌ ALL OTHERS NORMAL
                    card.classList.remove('scale-105', 'opacity-100', 'shadow-2xl');
                    card.classList.add('scale-95', 'opacity-70');
                }
            });
        };


    function nextTestimonial() {
        testimonialIndex++;
        updateTestimonial();

        const total = getSlides().length;
        const resetPoint = total - visibleSlides();

        if (testimonialIndex >= resetPoint) {
            setTimeout(() => {
                testimonialIndex = 0;
                updateTestimonial(false);
            }, 800);
        }
    }

    function prevTestimonial() {
        if (testimonialIndex === 0) {
            testimonialIndex = getSlides().length - visibleSlides();
            updateTestimonial(false);
        }
        testimonialIndex--;
        updateTestimonial();
    }

    function startAutoScroll() {
        stopAutoScroll();
        autoInterval = setInterval(nextTestimonial, 3500);
    }

    function stopAutoScroll() {
        clearInterval(autoInterval);
    }

    // 🧠 Events
    track.parentElement.addEventListener('mouseenter', stopAutoScroll);
    track.parentElement.addEventListener('mouseleave', startAutoScroll);

    window.addEventListener('load', () => {
        initClones();
        updateTestimonial(false);
        startAutoScroll();
    });

    window.addEventListener('resize', () => {
        testimonialIndex = 0;
        updateTestimonial(false);
    });
</script>

@endif
