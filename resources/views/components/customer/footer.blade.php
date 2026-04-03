<!-- Newsletter Subscription
<section class="py-12 bg-gray-900" id="newsletter">
    <div class="container mx-auto px-4">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-white md:w-1/2">
                    <h2 class="text-2xl md:text-3xl mb-2">Unlock special offers just for you</h2>
                    <p class="text-gray-300 text-sm md:text-base">The finest offers are the ones that arrive in your inbox.</p>
                </div>
                Right - Email Form
                <div class="md:w-1/2 w-full max-w-md">
                    <form 
                        action="{{ route('newsletter.subscribe') }}" 
                        method="POST" 
                        class="flex flex-col sm:flex-row gap-2 w-full"
                        id="newsletterForm"
                    >
                        @csrf

                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Your email" 
                            value="{{ old('email') }}"
                            required
                            class="w-full sm:flex-1 px-4 py-3 rounded border-none bg-white 
                                focus:outline-none focus:ring-2 focus:ring-teal-500 
                                text-gray-900 @error('email') ring-2 ring-red-500 @enderror"
                        >

                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-8 py-3 btn-color 
                                font-semibold rounded 
                                transition-colors whitespace-nowrap"
                          >
                            Subscribe
                        </button>
                    </form>
                    <div id="newsletterMessage" class="hidden mt-4 flex items-start gap-2 rounded-md px-4 py-3 text-sm transition-all duration-300"></div>
                </div>
            </div>
        </div>
    </div>
</section> -->
<footer class="bg-gray-50 text-gray-700 pt-12 pb-6 border-t border-gray-200">
    <div class="container mx-auto px-4">
        <!-- Main Footer Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
            <!-- Contact Section -->
            <div>
                <h3 class="text-gray-900 text-lg font-semibold mb-4">Contact</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="font-semibold mb-1">Call Us</p>
                        <a href="tel:+918118805835" class="text-gray-600 hover:text-gray-900">+91 8118805835</a>
                    </div>
                    <div>
                        <p class="font-semibold mb-1">Email Us</p>
                        <a href="mailto:info@oohapp.io" class="text-gray-600 hover:text-gray-900">info&#64;oohapp&#46;io</a>
                    </div>
                </div>
                <div class="">
                   <a href="{{ route('home') }}" class="flex items-center space-x-1.5">
                        <x-optimized-image
                            :src="route('brand.oohapp-logo')"
                            alt="OOHApp company logo"
                            class="w-48 md:w-[180px] ml-[-20px]"
                            width="150"
                            height="48"
                            style="max-height:48px;object-fit:contain;"
                        />
                    </a>
                </div>
            </div>

            <!-- Keep in touch Section -->
            <div>
                <h3 class="text-gray-900 text-left text-lg font-semibold mb-4">
                    Keep in touch
                </h3>
                <div class="flex items-center gap-2 justify-start -ml-3">
                    <a href="https://www.facebook.com/profile.php?id=100083678822547" target="_blank" rel="noopener noreferrer" class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="Facebook">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.0007 1.2168H12.5007C11.3956 1.2168 10.3358 1.65578 9.55437 2.43719C8.77297 3.21859 8.33398 4.27839 8.33398 5.38346V7.88346H5.83398V11.2168H8.33398V17.8835H11.6673V11.2168H14.1673L15.0007 7.88346H11.6673V5.38346C11.6673 5.16245 11.7551 4.95049 11.9114 4.79421C12.0677 4.63793 12.2796 4.55013 12.5007 4.55013H15.0007V1.2168Z" stroke="#1E1B18" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/realoohappofficial/" target="_blank" rel="noopener noreferrer" class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="Instagram">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.88 4.52297C8.96 4.52297 8.10667 4.75297 7.32 5.21297C6.53333 5.67297 5.90667 6.2963 5.44 7.08297C4.97333 7.86963 4.74 8.7263 4.74 9.65297C4.74 10.5796 4.97 11.4363 5.43 12.223C5.89 13.0096 6.51667 13.633 7.31 14.093C8.10333 14.553 8.96333 14.783 9.89 14.783C10.8167 14.783 11.6733 14.553 12.46 14.093C13.2467 13.633 13.87 13.0096 14.33 12.223C14.79 11.4363 15.02 10.5796 15.02 9.65297C15.02 8.7263 14.79 7.86963 14.33 7.08297C13.87 6.2963 13.2467 5.67297 12.46 5.21297C11.6733 4.75297 10.8133 4.52297 9.88 4.52297ZM9.88 12.823C9.30667 12.823 8.77667 12.6796 8.29 12.393C7.80333 12.1063 7.41667 11.7196 7.13 11.233C6.84333 10.7463 6.7 10.2163 6.7 9.64297C6.7 9.06963 6.84333 8.54297 7.13 8.06297C7.41667 7.58297 7.80333 7.19963 8.29 6.91297C8.77667 6.6263 9.30667 6.48297 9.88 6.48297C10.4533 6.48297 10.9833 6.6263 11.47 6.91297C11.9567 7.19963 12.3433 7.58297 12.63 8.06297C12.9167 8.54297 13.06 9.06963 13.06 9.64297C13.06 10.2163 12.9167 10.7463 12.63 11.233C12.3433 11.7196 11.9567 12.1063 11.47 12.393C10.9833 12.6796 10.4533 12.823 9.88 12.823ZM14.08 -0.237032H5.68C4.65333 -0.237032 3.7 0.0229683 2.82 0.542967C1.95333 1.04963 1.26667 1.73963 0.76 2.61297C0.253333 3.4863 0 4.4363 0 5.46297V13.843C0 14.8696 0.253333 15.8196 0.76 16.693C1.26667 17.5663 1.95333 18.2563 2.82 18.763C3.7 19.283 4.65333 19.543 5.68 19.543H14.08C15.1067 19.543 16.0667 19.283 16.96 18.763C17.8133 18.2563 18.4933 17.5763 19 16.723C19.52 15.8296 19.78 14.8696 19.78 13.843V5.46297C19.78 4.4363 19.52 3.4763 19 2.58297C18.4933 1.72963 17.8133 1.04963 16.96 0.542967C16.0667 0.0229683 15.1067 -0.237032 14.08 -0.237032ZM17.82 13.843C17.82 14.523 17.6533 15.1496 17.32 15.723C16.9867 16.2963 16.5333 16.7496 15.96 17.083C15.3867 17.4163 14.76 17.583 14.08 17.583H5.68C5 17.583 4.37667 17.4163 3.81 17.083C3.24333 16.7496 2.79333 16.2963 2.46 15.723C2.12667 15.1496 1.96 14.523 1.96 13.843V5.46297C1.96 4.78297 2.12667 4.1563 2.46 3.58297C2.79333 3.00963 3.24333 2.5563 3.81 2.22297C4.37667 1.88964 5 1.72297 5.68 1.72297H14.08C14.76 1.72297 15.3867 1.88964 15.96 2.22297C16.5333 2.5563 16.9867 3.00963 17.32 3.58297C17.6533 4.1563 17.82 4.78297 17.82 5.46297V13.843ZM15.1 3.02297C14.7267 3.02297 14.4067 3.1563 14.14 3.42297C13.8733 3.68963 13.74 4.00963 13.74 4.38297C13.74 4.7563 13.8733 5.07297 14.14 5.33297C14.4067 5.59297 14.7267 5.72297 15.1 5.72297C15.4733 5.72297 15.79 5.59297 16.05 5.33297C16.31 5.07297 16.44 4.7563 16.44 4.38297C16.44 4.00963 16.31 3.68963 16.05 3.42297C15.79 3.1563 15.4733 3.02297 15.1 3.02297Z" fill="#1E1B18"/>
                        </svg>
                    </a>
                    <a href="https://twitter.com/oohapads" target="_blank" rel="noopener noreferrer" class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="Twitter/X">
                        <svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0H2.08333L14.5833 16.6667H12.5L0 0ZM3.75 0H5.83333L18.3333 16.6667H16.25L3.75 0Z" fill="#1E1B18" fill-opacity="0.9"/>
                        <path d="M1.66602 0H5.83268V1.66667H1.66602V0ZM12.4993 16.6667H16.666V15H12.4993V16.6667Z" fill="#1E1B18" fill-opacity="0.9"/>
                        <path d="M14.5827 0H17.4993L3.33268 16.6667H0.416016L14.5827 0Z" fill="#1E1B18" fill-opacity="0.9"/>
                        </svg>
                    </a>
                    <a href="https://www.linkedin.com/company/oohapp/" target="_blank" rel="noopener noreferrer" class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="LinkedIn">
                        <svg width="19" height="18" viewBox="0 0 19 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.6667 6C13.9927 6 15.2645 6.52678 16.2022 7.46447C17.1399 8.40215 17.6667 9.67392 17.6667 11V16.8333H14.3333V11C14.3333 10.558 14.1577 10.134 13.8452 9.82149C13.5326 9.50893 13.1087 9.33333 12.6667 9.33333C12.2246 9.33333 11.8007 9.50893 11.4882 9.82149C11.1756 10.134 11 10.558 11 11V16.8333H7.66667V11C7.66667 9.67392 8.19345 8.40215 9.13113 7.46447C10.0688 6.52678 11.3406 6 12.6667 6ZM1 6.83333H4.33333V16.8333H1V6.83333Z" stroke="#1E1B18" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2.66667 4.33333C3.58714 4.33333 4.33333 3.58714 4.33333 2.66667C4.33333 1.74619 3.58714 1 2.66667 1C1.74619 1 1 1.74619 1 2.66667C1 3.58714 1.74619 4.33333 2.66667 4.33333Z" stroke="#1E1B18" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Experience OOHAPP on mobile -->
            <!-- <div>
                <h3 class="text-gray-900 text-lg font-semibold mb-4">Experience OOHAPP on mobile</h3>
                <div class="flex gap-3 space-y-3">
                    <a href="https://play.google.com/" target="_blank" rel="noopener noreferrer" class="inline-block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" class="h-10">
                    </a>
                    <a href="https://www.apple.com/" target="_blank" rel="noopener noreferrer" class="inline-block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="Download on the App Store" class="h-10">
                    </a>
                </div>
            </div> -->

            <!-- Let us help you -->
            <div>
                <h3 class="text-gray-900 text-lg font-semibold mb-4">Let us help you</h3>
               <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('about') }}" class="text-gray-600 hover:text-gray-900" target="_blank" rel="noopener noreferrer">
                            About OOHAPP
                        </a>
                    </li>
                     <li>
                        <a href="https://oohapp.io/service" class="text-gray-600 hover:text-gray-900" target="_blank" rel="noopener noreferrer">
                            Services
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faqs') }}" class="text-gray-600 hover:text-gray-900" target="_blank" rel="noopener noreferrer">
                            FAQs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900" target="_blank" rel="noopener noreferrer">
                            Terms & Conditions
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('disclaimer') }}" class="text-gray-600 hover:text-gray-900" target="_blank" rel="noopener noreferrer">
                            Legal Disclaimer
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900" target="_blank" rel="noopener noreferrer">
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('refund') }}" class="text-gray-600 hover:text-gray-900" target="_blank" rel="noopener noreferrer">
                            Refund & Cancellation Policy
                        </a>
                    </li>
              </ul>

            </div>
        </div>

        <!-- OOHAPP Hoardings Section -->
        <div class="border-t border-gray-200 pt-4 mb-4">
            <h3 class="text-gray-900 text-base font-bold mb-3">OOHAPP Hoardings</h3>
                @php
                    // Dynamic city list from active hoardings (top 10 + special entries)
                    $dynamicCities = \App\Models\Hoarding::select('city')
                        ->whereNotNull('city')
                        ->where('city', '!=', '')
                        ->whereIn('status', ['active', 'booked'])
                        ->groupBy('city')
                        ->orderBy('city', 'asc')
                        ->limit(28)
                        ->pluck('city')
                        ->map(fn($city) => 'Hoardings in ' . ucwords(strtolower($city)))
                        ->toArray();
                    
                    // Prepend special links (keep existing behavior)
                    array_unshift($dynamicCities, 'Hoardings near me');
                    $dynamicCities[] = 'All Cities Hoardings';
                    
                    $cities = $dynamicCities;
                @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-y-1 text-sm text-gray-600">
                @foreach($cities as $city)

                    {{-- NEAR ME --}}
                    @if(Str::contains(strtolower($city), 'near me'))
                        <a href="javascript:void(0)"
                        onclick="getCurrentLocation()"
                        class="hover:text-gray-900 hover:underline">
                            {{ $city }}
                        </a>

                    {{-- ALL CITIES --}}
                    @elseif(Str::contains(strtolower($city), 'all cities'))
                        <a href="{{ route('search') }}"
                        class="hover:text-gray-900 hover:underline">
                            {{ $city }}
                        </a>

                    {{-- CITY BASED --}}
                    @else
                        @php
                            // Extract city name from "Hoardings in XYZ"
                            $cityName = trim(str_replace(['Hoardings in', 'hoardings in'], '', $city));
                        @endphp

                        <a href="{{ route('search', ['location' => $cityName]) }}"
                        class="hover:text-gray-900 hover:underline">
                            {{ $city }}
                        </a>
                    @endif

                @endforeach
            </div>

        </div>

        <!-- Bottom Bar -->
        <!-- <div class="border-t border-gray-200 pt-6 flex flex-col md:flex-row justify-center items-center text-sm text-gray-600 text-center">
            <div class="flex items-center space-x-2 mb-2 md:mb-0 text-center bg-light">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
                <span class="font-medium">English</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div> -->
    </div>
</footer>
<div class="text-center text-gray-500 py-1" style="background-color:#e8e8e8;" id="copyright">© {{ date('Y') }} www.oohapp.io All rights reserved.</div>
@if(Route::is('hoardings.show'))
    <div class="h-[112px] w-full block md:hidden"></div>
@endif
<!-- Bottom Bar
<div class="pt-8 border-t border-gray-800">
    <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
        <p>&copy; {{ date('Y') }} OOHAPP. All rights reserved.</p>
        <p class="mt-2 md:mt-0">Made with ❤️ in India</p>
    </div>
</div> -->
    
<script>
    document.getElementById('newsletterForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this;
        const messageBox = document.getElementById('newsletterMessage');

        // reset
        messageBox.className = 'hidden mt-4 flex items-start gap-2 rounded-md px-4 py-3 text-sm transition-all duration-300';
        messageBox.innerHTML = '';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {

            let baseClass = '';
            let icon = '';

            if (data.status === 'success') {
                baseClass = 'bg-green-900/40 text-green-300 border border-green-700';
                icon = '✔️';
                form.reset();
            } 
            else if (data.status === 'info') {
                baseClass = 'bg-blue-900/40 text-blue-300 border border-blue-700';
                icon = 'ℹ️';
            } 
            else {
                baseClass = 'bg-red-900/40 text-red-300 border border-red-700';
                icon = '⚠️';
            }

            messageBox.className = `mt-4 flex items-start gap-2 rounded-md px-4 py-3 text-sm ${baseClass}`;
            messageBox.innerHTML = `<span class="text-lg leading-none">${icon}</span><span>${data.message}</span>`;
        })
        .catch(() => {
            messageBox.className =
                'mt-4 flex items-start gap-2 rounded-md px-4 py-3 text-sm bg-red-900/40 text-red-300 border border-red-700';
            messageBox.innerHTML = '<span class="text-lg">⚠️</span><span>Server error. Please try again.</span>';
        });
    });
</script>

