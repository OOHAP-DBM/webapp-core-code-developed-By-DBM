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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    <div id="app" class="min-h-screen bg-gray-50">
        {{-- <!-- Header -->
        @include('layouts.partials.header') --}}

        <!-- Main Content -->
        <main class="">
            @yield('content')
        </main>

        <!-- Footer -->
        {{-- @include('layouts.partials.footer') --}}
    </div>

    @stack('modals')
    @stack('scripts')
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    btn.textContent = 'Add to Cart';
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
                .then(res => res.json())
                .then(data => {

                    if (data.status === 'login_required') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Login required',
                            text: data.message
                        });
                        return;
                    }

                    applyCartUI(btn, data.in_cart);

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 1400
                    });

                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong'
                    });
                })
                .finally(() => {
                    btn.disabled = false;
                });
            };

        })();
    </script>

</body>
</html>
