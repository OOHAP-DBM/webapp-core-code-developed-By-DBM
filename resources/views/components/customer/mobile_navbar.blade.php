<header class="bg-[#FBFBFB] border-b border-gray-100 fixed top-0 left-0 w-full z-50  md:hidden ">
    <!-- Desktop/Tablet Navbar -->
    <div class="container mx-auto px-4 lg:px-6">
            <!-- Mobile Only Search Bar -->
            <div class="block md:hidden w-full px-2 pt-2 pb-2 bg-[#FBFBFB] border-b border-gray-100">
                @include('components.customer.home_search_mobile')
            </div>
      
    </div>
</header>

@push('scripts')
<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    if (menu.classList.contains('-translate-x-full')) {
        menu.classList.remove('-translate-x-full');
        menu.classList.add('translate-x-0');
        document.body.style.overflow = 'hidden';
    } else {
        menu.classList.add('-translate-x-full');
        menu.classList.remove('translate-x-0');
        document.body.style.overflow = '';
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn  = document.getElementById('userDropdownBtn');
    const menu = document.getElementById('userDropdown');

    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', function () {
        menu.classList.add('hidden');
    });
});
</script>

<script>
function openWishlist(event) {
    event.preventDefault();
    const isAuth = document.querySelector('[data-auth]')?.dataset?.auth === '1';

    if (!isAuth) {
        // Guest — LocalStorage IDs URL mein bhejo
        const saved = JSON.parse(localStorage.getItem('guest_wishlist') || '[]');
        if (saved.length === 0) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: 'Wishlist is empty', showConfirmButton: false, timer: 1800
            });
            return;
        }
        window.location.href = "{{ route('shortlist') }}?ids=" + saved.join(',');
        return;
    }

    window.location.href = "{{ route('shortlist') }}";
}
</script>
<script>
function openCart(event) {
    event.preventDefault();
    const isAuth = event.currentTarget.dataset.auth === '1';

    if (!isAuth) {
        const saved = JSON.parse(localStorage.getItem('guest_cart') || '[]');
        if (saved.length === 0) {
            Swal.fire({
                toast: true, position: 'top-end', icon: 'info',
                title: 'Cart is empty', showConfirmButton: false, timer: 1800
            });
            return;
        }
        window.location.href = "{{ route('cart.index') }}?ids=" + saved.join(',');
        return;
    }

    window.location.href = "{{ route('cart.index') }}";
}
</script>
@endpush

