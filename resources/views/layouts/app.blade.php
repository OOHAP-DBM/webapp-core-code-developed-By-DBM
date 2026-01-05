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
function toggleCart(hoardingId) {
    const btn = document.getElementById(`cart-btn-${hoardingId}`);
    const state = btn.dataset.state; // add | remove

    const url = state === 'add'
        ? "{{ route('cart.add') }}"
        : "{{ route('cart.remove') }}";

    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ hoarding_id: hoardingId })
    })
    .then(async res => {
        if (res.status === 401) {
            window.location.href = "{{ route('login') }}";
            return;
        }
        return res.json();
    })
    .then(data => {
        if (!data || !data.success) {
            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: data?.message || 'Action failed'
            });
            return;
        }

        if (state === 'add') {
            // ADD → REMOVE
            btn.dataset.state = 'remove';
            btn.textContent = 'Remove';
            btn.className =
                'flex-1 py-2 px-3 bg-red-400 text-white text-sm font-semibold rounded hover:bg-red-500';

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Added to cart',
                showConfirmButton: false,
                timer: 2000
            });
        } else {
            // REMOVE → ADD
            btn.dataset.state = 'add';
            btn.textContent = 'Add to Cart';
            btn.className =
                'flex-1 py-2 px-3 border border-gray-300 text-gray-700 text-sm font-semibold rounded hover:bg-gray-50';

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Removed from cart',
                showConfirmButton: false,
                timer: 2000
            });
        }
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong!'
        });
    });
}
</script>


</body>
</html>
