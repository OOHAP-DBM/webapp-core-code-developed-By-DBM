<div id="logoutModal"
     class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/60">

    <div class="bg-white w-[70%] max-w-md rounded-2xl shadow-xl relative">

        <!-- CLOSE ICON -->
        <div class="h-12 bg-gray-100 relative rounded-t-2xl">
            <!-- CLOSE ICON -->
            <button
                onclick="closeLogoutModal()"
                class="absolute top-2 right-3 w-8 h-8 flex items-center justify-center
                       rounded-full text-black text-xl leading-none cursor-pointer">
                ×
            </button>
        </div>

        <!-- CONTENT -->
        <div class="p-8 text-center">

            <!-- ICON -->
            <div class="flex justify-center mb-5">
                <div class="w-16 h-16 flex items-center justify-center rounded-full">
                    <svg width="56" height="51" viewBox="0 0 56 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25.4167 50.8333C11.379 50.8333 0 39.4543 0 25.4167C0 11.3791 11.379 7.16314e-06 25.4167 7.16314e-06C29.363 -0.00295759 33.2557 0.914429 36.7854 2.67928C40.3151 4.44414 43.3846 7.00783 45.75 10.1667H38.8621C35.927 7.57865 32.3076 5.8925 28.438 5.31057C24.5684 4.72863 20.6131 5.27563 17.0467 6.88592C13.4803 8.49622 10.4543 11.1014 8.33176 14.3889C6.20927 17.6763 5.08046 21.5064 5.08079 25.4195C5.08111 29.3326 6.21056 33.1625 8.3336 36.4496C10.4566 39.7367 13.4831 42.3414 17.0498 43.9511C20.6164 45.5608 24.5718 46.1071 28.4413 45.5245C32.3108 44.9419 35.93 43.2552 38.8646 40.6667H45.7525C43.3869 43.8259 40.317 46.3898 36.7868 48.1546C33.2566 49.9195 29.3634 50.8367 25.4167 50.8333ZM43.2083 35.5833V27.9583H22.875V22.875H43.2083V15.25L55.9167 25.4167L43.2083 35.5833Z" fill="#E75858"/>
                    </svg>
                </div>
            </div>

            <!-- TEXT -->
            <h2 class="text-2xl font-semibold mb-2">
                Comeback Soon!
            </h2>

            <p class="text-gray-500 text-lg mb-6 font-semibold">
                Are you sure you want<br>
                to logout from OOHAPP?
            </p>

            <div class="flex justify-between items-center gap-4">
                <button
                    type="button"
                    onclick="closeLogoutModal()"
                    class="text-black font-bold cursor-pointer">
                    Cancel
                </button>

                <form method="POST" action="{{ route('logout') }}" onsubmit="clearAllSessionBeforeLogout()">
                    @csrf
                    <button
                        type="submit"
                        class="logout-btn cursor-pointer font-semibold px-6 py-3 rounded-lg">
                        Yes, Logout
                    </button>
                </form>

           </div>

        </div>
    </div>
</div>
<script>
function clearAllSessionBeforeLogout() {
    sessionStorage.clear();
    localStorage.clear();

    // optional safety
    document.cookie.split(";").forEach(c => {
        document.cookie = c
            .replace(/^ +/, "")
            .replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
    });
}
</script>
