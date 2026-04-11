<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Dynamic page title: child views can override via @section('title', '...') --}}
    <title>@yield('title', 'Book Outdoor Ads in India | Billboard & Hoarding | OOHAPP')</title>

    {{-- ── SEO Meta ──────────────────────────────────────────────── --}}
    <meta name="description" content="@yield('meta_description', 'Book billboard and hoarding ads across India with OOHAPP. Explore 1000+ outdoor advertising locations, compare prices, and launch your ad campaign instantly.')">
    <meta name="keywords" content="outdoor advertising India, billboard advertising India, hoarding advertising India, outdoor media booking, OOH advertising platform, billboard booking India">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large')">
    <meta name="author" content="OOHAPP">
    <meta name="google-site-verification" content="Cj2NPz4_gTnvaitO3OAv51DDtIaNmE1N4VIP6WiGlDM" />

    {{-- ── Canonical (dynamic — reflects the actual page URL sans query string) --}}
    @php $canonicalUrl = url()->current(); @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- ── Favicon ────────────────────────────────────────────────── --}}
    <link rel="icon"            type="image/png"  href="{{ asset('assets/images/favicon/Vector (1).png') }}">
    <link rel="shortcut icon"                     href="{{ asset('assets/images/favicon/Vector (1).png') }}">
    <link rel="apple-touch-icon"                  href="{{ asset('assets/images/favicon/Vector (1).png') }}">

    {{-- ── Open Graph ─────────────────────────────────────────────── --}}
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="@yield('og_title', 'Book Outdoor Ads in India | Billboard & Hoarding | OOHAPP')">
    <meta property="og:description" content="@yield('og_description', 'Book billboard and hoarding ads across India with OOHAPP. Explore 1000+ outdoor advertising locations, compare prices, and launch your ad campaign instantly.')">
    <meta property="og:url"         content="{{ $canonicalUrl }}">
    <meta property="og:site_name"   content="OOHAPP">
    <meta property="og:image"       content="{{ asset('assets/images/logo/logo_image.webp') }}">
    <meta property="og:image:width" content="600">
    <meta property="og:image:height" content="120">

    {{-- ── Twitter Card ────────────────────────────────────────────── --}}
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@oohapp">
    <meta name="twitter:creator"     content="@oohapp">
    <meta name="twitter:title"       content="@yield('twitter_title', 'Outdoor Advertising in India | Book Billboard & Hoarding Ads | OOHAPP')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Book billboard and hoarding ads across India with OOHAPP. Explore 1000+ outdoor advertising locations and launch campaigns instantly.')">
    <meta name="twitter:image"       content="{{ asset('assets/images/logo/logo_image.webp') }}">

    {{-- ── Fonts (preconnect first to avoid extra round-trips) ──────── --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap"></noscript>
    {{-- Alpine Collapse Plugin — x-collapse ke liye --}}
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
{{-- Alpine Core — collapse plugin ke BAAD load hona chahiye --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ── CSS (non-blocking order: base → icons → datepicker) ──────── --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

    {{-- ── Google Analytics (async — does NOT block render) ──────────── --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ST51N3C0N6"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-ST51N3C0N6');
    </script>

    {{-- ── Structured Data (JSON-LD) ──────────────────────────────────── --}}
    @verbatim
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "https://oohapp.io/#organization",
          "name": "OOHAPP",
          "url": "https://oohapp.io/",
          "logo": {
            "@type": "ImageObject",
            "@id": "https://oohapp.io/#logo",
            "url": "https://oohapp.io/assets/images/favicon/Vector%20(1).png",
            "width": 512,
            "height": 512
          },
          "telephone": "+91 8118805835",
          "email": "enquiry@oohapp.io",
          "foundingDate": "2022",
          "areaServed": { "@type": "Country", "name": "India" }
        },
        {
          "@type": "LocalBusiness",
          "@id": "https://oohapp.io/#localbusiness",
          "name": "OOHAPP",
          "parentOrganization": { "@id": "https://oohapp.io/#organization" },
          "url": "https://oohapp.io/",
          "image": { "@type": "ImageObject", "url": "https://oohapp.io/assets/images/favicon/Vector%20(1).png" },
          "telephone": "+91 8118805835",
          "email": "enquiry@oohapp.io",
          "priceRange": "INR"
        },
        {
          "@type": "WebSite",
          "@id": "https://oohapp.io/#website",
          "url": "https://oohapp.io/",
          "name": "OOHAPP"
        }
      ]
    }
    </script>
    @endverbatim

</head>
<body class="antialiased">
    <div id="app" class="min-h-screen bg-white w-full ">
        <!-- Main Content -->
        <main class="mt-[90px]">
            @yield('content')
        </main>

        @include('components.customer.footer')
    </div>

    @stack('modals')
    @stack('scripts')

    {{-- JS loaded at end of body to avoid render-blocking --}}
    {{-- All JS is now bundled via Vite --}}

    {{-- ================================
         GUEST STATE RESTORE — page load pe
         LocalStorage se wishlist + cart buttons restore karo
    ================================= --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isAuth = document.querySelector('[data-auth]')?.dataset?.auth === '1';

        if (!isAuth) {
            // ─── Wishlist buttons restore ─────────────────────────
            const savedWishlist = JSON.parse(localStorage.getItem('guest_wishlist') || '[]');

            document.querySelectorAll('.shortlist-btn').forEach(function (btn) {
                if (savedWishlist.includes(String(btn.dataset.id))) {
                    btn.classList.add('is-wishlisted', 'bg-[#daf2e7]');
                    btn.classList.remove('bg-[#9e9e9b]');
                }
            });

            // Wishlist header count update
            const wishlistBadges = document.querySelectorAll('.shortlist-count');
            wishlistBadges.forEach(function (b) {
                b.textContent = savedWishlist.length;
                b.style.display = savedWishlist.length > 0 ? 'flex' : 'none';
            });

            // ─── Cart buttons restore ─────────────────────────────
            const savedCart = JSON.parse(localStorage.getItem('guest_cart') || '[]');

            document.querySelectorAll('.cart-btn').forEach(function (btn) {
                const id = String(btn.dataset.id);
                const inCart = savedCart.includes(id);
                applyCartUI(btn, inCart);
            });

            // Cart header count update
            const cartBadges = document.querySelectorAll('.cart-count');
            cartBadges.forEach(function (b) {
                b.textContent = savedCart.length;
                b.style.display = savedCart.length > 0 ? 'flex' : 'none';
            });
        } else {
            // ─── Logged in — normal DB based cart buttons ─────────
            document.querySelectorAll('.cart-btn').forEach(function (btn) {
                applyCartUI(btn, btn.dataset.inCart === '1');
            });
        }
    });
    </script>

   {{-- ================================
        CART
    ================================= --}}
    <script>
        function applyCartUI(btn, inCart) {
            btn.dataset.inCart = inCart ? '1' : '0';
            btn.classList.remove('add', 'remove');
            if (inCart) {
                btn.textContent = 'Remove';
                btn.classList.add('remove');
            } else {
                btn.textContent = 'Shortlist';
                btn.classList.add('add');
            }
        }

        // URL ko update karo bina page reload ke (guest cart page ke liye)
        function updateCartUrl(savedIds) {
            const url = new URL(window.location.href);
            if (savedIds.length > 0) {
                url.searchParams.set('ids', savedIds.join(','));
            } else {
                url.searchParams.delete('ids');
            }
            window.history.replaceState({}, '', url.toString());
        }

        // Cart page heading + empty state update
        function updateCartPageCount(count) {
            // "Shortlisted (2 Hoardings)" heading update
            document.querySelectorAll('h1, h2').forEach(function (h) {
                if (h.textContent.includes('Hoardings')) {
                    h.innerHTML = 'Shortlisted <span class="text-gray-500 font-normal text-base">(' + count + ' Hoardings)</span>';
                }
            });

            // 0 items ho gaye toh empty state dikhao
            if (count === 0) {
                const container = document.querySelector('[data-cart-items]');
                if (container) {
                    container.innerHTML = `
                        <div class="text-center py-16 text-gray-400">
                            <p class="text-lg">Your shortlist is empty.</p>
                            <a href="/" class="mt-4 inline-block text-blue-500 hover:underline">Browse Hoardings</a>
                        </div>
                    `;
                }

                // Right side summary panel bhi hide karo
                const summaryPanel = document.querySelector('[data-cart-summary]');
                if (summaryPanel) summaryPanel.style.display = 'none';
            }
        }

        window.toggleCart = function (btn, hoardingId) {
            const isAuth = btn.dataset.auth === '1';
            const inCart = btn.dataset.inCart === '1';

            /* ─── GUEST — LocalStorage ─── */
            if (!isAuth) {
                let saved = JSON.parse(localStorage.getItem('guest_cart') || '[]');
                const id  = String(hoardingId);
                const idx = saved.indexOf(id);

                if (idx === -1) {
                    // ADD
                    saved.push(id);
                    applyCartUI(btn, true);
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Added to shortlist', showConfirmButton: false, timer: 1400 });
                } else {
                    // REMOVE
                    saved.splice(idx, 1);
                    applyCartUI(btn, false);

                    // ── Cart page pe ho toh: card hatao, URL update karo ──
                    if (window.location.pathname.includes('cart') || window.location.search.includes('ids=')) {
                        const card = btn.closest('.bg-white.border');
                        if (card) card.remove();
                        updateCartUrl(saved);
                        updateCartPageCount(saved.length);
                        setTimeout(function () { location.reload(); }, 400);
                    }

                    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Removed from shortlist', showConfirmButton: false, timer: 1400 });
                }

                localStorage.setItem('guest_cart', JSON.stringify(saved));

                // Header badge update
                document.querySelectorAll('.cart-count').forEach(function (b) {
                    b.textContent = saved.length;
                    b.style.display = saved.length > 0 ? 'flex' : 'none';
                });
                return;
            }

            /* ─── LOGGED IN — DB ─── */
            const url = inCart
                ? "{{ route('cart.remove') }}"
                : "{{ route('cart.add') }}";

            btn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ hoarding_id: hoardingId })
            })
            .then(function (res) {
                if (res.status === 401 || res.status === 419) {
                    window.location.href = '/login?intended=' + encodeURIComponent(window.location.href);
                    return;
                }
                return res.json();
            })
            .then(function (data) {
                if (!data) return;
                applyCartUI(btn, data.in_cart);
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 1400 });
                setTimeout(function () { location.reload(); }, 800);
            })
            .catch(function () {
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Server error occurred', showConfirmButton: false, timer: 2000 });
            })
            .finally(function () { btn.disabled = false; });
        };
    </script>

    {{-- ================================
         SHORTLIST MANAGER (btn-wishlist class)
    ================================= --}}
    <script>
        (function () {
            const shortlistManager = {
                baseUrl: '/customer/shortlist',

                init() {
                    this.bindWishlistButtons();
                    this.updateCount();
                },

                bindWishlistButtons() {
                    document.addEventListener('click', function (e) {
                        const btn = e.target.closest('.btn-wishlist');
                        if (btn) {
                            e.preventDefault();
                            e.stopPropagation();
                            const hoardingId = btn.dataset.hoardingId;
                            if (!hoardingId) return;
                            shortlistManager.toggle(hoardingId, btn);
                        }
                    });
                },

                async toggle(hoardingId, btn) {
                    @auth
                        @if(!auth()->user()->hasRole('customer'))
                            Swal.fire({ icon: 'error', title: 'Access Denied', text: 'Only customers can add items to shortlist' });
                            return;
                        @endif
                    @endauth

                    try {
                        btn.disabled = true;
                        const icon = btn.querySelector('i');

                        const response = await fetch(`${this.baseUrl}/toggle/${hoardingId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        });

                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                        const data = await response.json();

                        if (data.success) {
                            if (data.isWishlisted) {
                                icon?.classList.remove('bi-heart');
                                icon?.classList.add('bi-heart-fill');
                                btn.classList.add('active');
                                btn.setAttribute('title', 'Remove from shortlist');
                            } else {
                                icon?.classList.remove('bi-heart-fill');
                                icon?.classList.add('bi-heart');
                                btn.classList.remove('active');
                                btn.setAttribute('title', 'Add to shortlist');
                            }
                            this.updateCountBadge(data.count);
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 1500 });
                        } else {
                            throw new Error(data.message || 'Failed to update shortlist');
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Failed to update shortlist' });
                    } finally {
                        btn.disabled = false;
                    }
                },

                updateCountBadge(count) {
                    document.querySelectorAll('.shortlist-count').forEach(function (badge) {
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'inline-block' : 'none';
                    });
                },

                async updateCount() {
                    @auth
                    @if(auth()->user()->hasRole('customer'))
                    try {
                        const response = await fetch(`${this.baseUrl}/count`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const data = await response.json();
                        if (data.success) this.updateCountBadge(data.count);
                    } catch (error) {
                        console.error('Error fetching shortlist count:', error);
                    }
                    @endif
                    @endauth
                }
            };

            document.addEventListener('DOMContentLoaded', function () { shortlistManager.init(); });
            window.shortlistManager = shortlistManager;
        })();
    </script>

    @stack('modals')
    @include('hoardings.partials.enquiry-modal')
    @include('hoardings.scripts.enquiry-modal')
    @include('layouts.partials.logout')

    <script>
        function openLogoutModal() { document.getElementById('logoutModal').classList.remove('hidden'); }
        function closeLogoutModal() { document.getElementById('logoutModal').classList.add('hidden'); }
    </script>

    {{-- ================================
         HEART BUTTON (toggleShortlist)
         — Guest LocalStorage + Logged in DB
    ================================= --}}
    <script>
    function toggleShortlist(btn) {
        const hoardingId = btn.dataset.id;
        const isAuth     = btn.dataset.auth === '1';

        if (!hoardingId) return;

        /* ─── GUEST — LocalStorage ─── */
        if (!isAuth) {
            let saved = JSON.parse(localStorage.getItem('guest_wishlist') || '[]');
            const idx = saved.indexOf(String(hoardingId));

            if (idx === -1) {
                saved.push(String(hoardingId));
                btn.classList.add('is-wishlisted', 'bg-[#daf2e7]');
                btn.classList.remove('bg-[#9e9e9b]');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Saved to wishlist', showConfirmButton: false, timer: 1800 });
            } else {
                saved.splice(idx, 1);
                btn.classList.remove('is-wishlisted', 'bg-[#daf2e7]');
                btn.classList.add('bg-[#9e9e9b]');
                Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Removed from wishlist', showConfirmButton: false, timer: 1800 });
            }

            localStorage.setItem('guest_wishlist', JSON.stringify(saved));

            // Header wishlist count update
            document.querySelectorAll('.shortlist-count').forEach(function (b) {
                b.textContent = saved.length;
                b.style.display = saved.length > 0 ? 'flex' : 'none';
            });

            return;
        }

        /* ─── LOGGED IN USER — DB ─── */
        btn.disabled = true;

        fetch(`/shortlist/toggle/${hoardingId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(function (res) {
            if (res.status === 401 || res.status === 419) throw new Error('Unauthorized');
            return res.json();
        })
        .then(function (data) {
            if (!data.success) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: data.message || 'Something went wrong', showConfirmButton: false, timer: 2500 });
                return;
            }
            const isAdded = data.action === 'added';
            if (isAdded) {
                btn.classList.add('is-wishlisted', 'bg-[#daf2e7]');
                btn.classList.remove('bg-[#9e9e9b]');
            } else {
                btn.classList.remove('is-wishlisted', 'bg-[#daf2e7]');
                btn.classList.add('bg-[#9e9e9b]');
            }
            Swal.fire({ toast: true, position: 'top-end', icon: isAdded ? 'success' : 'info', title: isAdded ? 'Added to wishlist' : 'Removed from wishlist', showConfirmButton: false, timer: 1800 });
            setTimeout(function () { window.location.reload(); }, 1200);
        })
        .catch(function () {
            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'Session expired. Please login again.', showConfirmButton: false, timer: 2500 });
            setTimeout(function () { window.location.href = "{{ route('login') }}"; }, 2000);
        })
        .finally(function () { btn.disabled = false; });
    }
    </script>
    <script>
        @if(session('merge_guest_data') && auth()->check())
            document.addEventListener('DOMContentLoaded', function () {
                const wishlist = JSON.parse(localStorage.getItem('guest_wishlist') || '[]');
                const cart     = JSON.parse(localStorage.getItem('guest_cart') || '[]');

                if (wishlist.length === 0 && cart.length === 0) return;

                fetch('/guest/merge', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ wishlist: wishlist, cart: cart })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        localStorage.removeItem('guest_wishlist');
                        localStorage.removeItem('guest_cart');
                        let msg = 'Your wishlist and cart have been merged.';
                        if (data.skipped_owner_hoardings && (data.skipped_owner_hoardings.wishlist.length > 0 || data.skipped_owner_hoardings.cart.length > 0)) {
                            msg += '\nSome hoardings you own were not merged.';
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Welcome!',
                            text: msg,
                            timer: 2200,
                            showConfirmButton: false
                        });
                        setTimeout(function() { window.location.reload(); }, 1500);
                    }
                })
                .catch(err => {
                    console.error('Merge failed:', err);
                    Swal.fire({ icon: 'error', title: 'Merge failed', text: 'Could not merge your wishlist/cart.' });
                });
            });
        @endif
    </script>

    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1651081729239968');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=1651081729239968&ev=PageView&noscript=1" alt="facebook_pixel"
        />
    </noscript>

</body>
</html>