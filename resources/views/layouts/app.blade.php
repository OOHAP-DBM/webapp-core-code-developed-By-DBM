<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'OOHAPP') }} - @yield('title', 'Hoarding Marketplace')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    <div id="app" class="min-h-screen bg-white">
        {{-- <!-- Header -->
        @include('layouts.partials.header') --}}

        <!-- Main Content -->
        <main class="">
            @yield('content')
        </main>

        <!-- Footer -->
        {{-- @include('layouts.partials.footer') --}}
        @include('components.customer.footer')

    </div>

    @stack('modals')
    @stack('scripts')
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        (function () {

            /* ================================
            | APPLY BUTTON UI
            ================================= */
            function applyCartUI(btn, inCart) {
                btn.dataset.inCart = inCart ? '1' : '0';
                btn.classList.remove('add', 'remove');

                if (inCart) {
                    btn.textContent = 'Remove';
                    btn.classList.add('remove');
                } else {
                    btn.textContent = 'Add to cart';
                    btn.classList.add('add');
                }
            }

            /* ================================
            | INITIALIZE ALL BUTTONS (🔥 FIX)
            ================================= */
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.cart-btn').forEach(btn => {
                    const inCart = btn.dataset.inCart === '1';
                    applyCartUI(btn, inCart);
                });
            });

            /* ================================
            | TOGGLE CART
            ================================= */
            window.toggleCart = function (btn, hoardingId) {

                const inCart = btn.dataset.inCart === '1';
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
                .then(res => {

                    // ✅ LOGIN REQUIRED — NO JSON PARSING
                    if (res.status === 401 || res.status === 419) {
                        window.location.href =
                            '/login?intended=' + encodeURIComponent(window.location.href);
                        return;
                    }

                    // ✅ NOW SAFE TO PARSE JSON
                    return res.json();
                })
                .then(data => {
                    if (!data) return;

                    applyCartUI(btn, data.in_cart);

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 1400
                    });

                    setTimeout(() => location.reload(), 800);
                })
                .catch(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Server error occurred',
                        showConfirmButton: false,
                        timer: 2000
                    });
                })
                .finally(() => {
                    btn.disabled = false;
                });

            };

        })();
    </script>
    {{-- Shortlist/Wishlist Functionality --}}
    <script>
        (function () {
            /* ================================
            | SHORTLIST MANAGER
            ================================= */
            const shortlistManager = {
                baseUrl: '/customer/shortlist',
                
                init() {
                    this.bindWishlistButtons();
                    this.updateCount();
                },

                bindWishlistButtons() {
                    document.addEventListener('click', (e) => {
                        const btn = e.target.closest('.btn-wishlist');
                        if (btn) {
                            e.preventDefault();
                            e.stopPropagation();
                            const hoardingId = btn.dataset.hoardingId;
                            if (!hoardingId) {
                                console.error('No hoarding ID found on button');
                                return;
                            }
                            this.toggle(hoardingId, btn);
                        }
                    });
                },

                async toggle(hoardingId, btn) {
                    // Check if user is authenticated
                    @guest
                    Swal.fire({
                        icon: 'info',
                        title: 'Login Required',
                        text: 'Please login to add items to your shortlist',
                        confirmButtonText: 'Go to Login',
                        showCancelButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('login') }}?intended=" + encodeURIComponent(window.location.href);
                        }
                    });
                    return;
                    @endguest

                    // Check if user is customer
                    @auth
                    @if(!auth()->user()->hasRole('customer'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Access Denied',
                        text: 'Only customers can add items to shortlist'
                    });
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

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.success) {
                            // Update button UI
                            if (data.isWishlisted) {
                                icon.classList.remove('bi-heart');
                                icon.classList.add('bi-heart-fill');
                                btn.classList.add('active');
                                btn.setAttribute('title', 'Remove from shortlist');
                            } else {
                                icon.classList.remove('bi-heart-fill');
                                icon.classList.add('bi-heart');
                                btn.classList.remove('active');
                                btn.setAttribute('title', 'Add to shortlist');
                            }
                            
                            // Update count badge
                            this.updateCountBadge(data.count);
                            
                            // Show toast
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            throw new Error(data.message || 'Failed to update shortlist');
                        }
                    } catch (error) {
                        console.error('Error toggling shortlist:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Failed to update shortlist'
                        });
                    } finally {
                        btn.disabled = false;
                    }
                },

                updateCountBadge(count) {
                    const badges = document.querySelectorAll('.shortlist-count');
                    badges.forEach(badge => {
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'inline-block' : 'none';
                    });
                },

                async updateCount() {
                    @auth
                    @if(auth()->user()->hasRole('customer'))
                    try {
                        const response = await fetch(`${this.baseUrl}/count`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.updateCountBadge(data.count);
                        }
                    } catch (error) {
                        console.error('Error fetching shortlist count:', error);
                    }
                    @endif
                    @endauth
                }
            };

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', () => {
                shortlistManager.init();
            });

            // Make it global
            window.shortlistManager = shortlistManager;
        })();
    </script>
@include('hoardings.partials.enquiry-modal')
@include('hoardings.scripts.enquiry-modal')
@include('layouts.partials.logout')
<script>
    function openLogoutModal() {
        document.getElementById('logoutModal').classList.remove('hidden');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
    }
</script>
<script>
function toggleShortlist(btn) {
    const hoardingId = btn.dataset.id;
    const isAuth = btn.dataset.auth === '1';
    const role = btn.dataset.role;

    if (!hoardingId) return;

    /* ❌ NOT LOGGED IN */
    if (!isAuth) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'Please login to save this hoarding',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });

        setTimeout(() => {
            window.location.href = "{{ route('login') }}";
        }, 2000);
        return;
    }

    /* ❌ ROLE NOT ALLOWED */
    if (role === 'vendor' || role === 'admin') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'You are not allowed to add items to wishlist',
            text: 'This action is only available for customers.',
            showConfirmButton: false,
            timer: 3000
        });
        return;
    }

    /* ✅ CUSTOMER ACTION */
    fetch(`/customer/shortlist/toggle/${hoardingId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (res.status === 401 || res.status === 419) {
            throw new Error('Unauthorized');
        }
        return res.json();
    })
    .then(data => {
        if (!data.success) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: data.message || 'Something went wrong',
                showConfirmButton: false,
                timer: 2500
            });
            return;
        }

        const isAdded = data.action === 'added';

        /* UI TOGGLE (instant feedback) */
        if (isAdded) {
            btn.classList.add('is-wishlisted', 'bg-[#daf2e7]');
            btn.classList.remove('bg-[#9e9e9b]');
        } else {
            btn.classList.remove('is-wishlisted', 'bg-[#daf2e7]');
            btn.classList.add('bg-[#9e9e9b]');
        }

        /* Toast */
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: isAdded ? 'success' : 'info',
            title: isAdded
                ? 'Added to wishlist'
                : 'Removed from wishlist',
            showConfirmButton: false,
            timer: 1800
        });

        /* 🔄 RELOAD AFTER ACTION */
        setTimeout(() => {
            window.location.reload();
        }, 1200);
    })
    .catch(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Session expired. Please login again.',
            showConfirmButton: false,
            timer: 2500
        });

        setTimeout(() => {
            window.location.href = "{{ route('login') }}";
        }, 2000);
    });
}
</script>



</body>
</html>