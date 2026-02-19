<!-- Newsletter Subscription -->
<section class="py-12 bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="mx-auto max-w-7xl px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Left Content -->
                <div class="text-white md:w-1/2">
                    <h2 class="text-2xl md:text-3xl mb-2">Unlock special offers just for you</h2>
                    <p class="text-gray-300 text-sm md:text-base">The finest offers are the ones that arrive in your inbox.</p>
                </div>
                <!-- Right - Email Form -->
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
                            class="w-full sm:flex-1 px-4 py-3 rounded-md border-none bg-white 
                                focus:outline-none focus:ring-2 focus:ring-teal-500 
                                text-gray-900 @error('email') ring-2 ring-red-500 @enderror"
                        >

                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-8 py-3 btn-color 
                                font-semibold rounded-md 
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
</section>
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
                        <a href="mailto:info@oohapp.io" class="text-gray-600 hover:text-gray-900">info@oohapp.io</a>
                    </div>
                </div>
                <div class="mt-6">
                    <svg width="170" height="35" viewBox="0 0 200 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.3096 16.0662C2.06411 17.3381 1.93536 18.6535 1.93536 20C1.93536 31.0457 10.6002 40 21.2889 40C31.9776 40 40.6425 31.0457 40.6425 20C40.6425 8.9543 31.9776 0 21.2889 0C19.5371 0 17.8396 0.240535 16.2257 0.691389V10.9206C17.7156 10.0314 19.4448 9.5226 21.2889 9.5226C26.8884 9.5226 31.4277 14.2135 31.4277 20C31.4277 25.7865 26.8884 30.4774 21.2889 30.4774C15.6894 30.4774 11.1502 25.7865 11.1502 20C11.1502 18.6087 11.4126 17.2808 11.889 16.0662H2.3096Z" fill="#1E1B18"/>
                    <path d="M0 1.5L14.0313 3.92268V14L0 11.5773V1.5Z" fill="#009A5C"/>
                    <path d="M79.3496 40V0H88.3868V15.625H100.35V0H109.387V40H100.35V24.375H88.3868V40H79.3496Z" fill="#1E1B18"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M59.996 0C49.5746 0 41.1263 8.95431 41.1263 20C41.1263 31.0457 49.5746 40 59.996 40C70.4175 40 78.8658 31.0457 78.8658 20C78.8658 8.95431 70.4175 0 59.996 0ZM59.996 9.5C54.5248 9.5 50.0894 14.201 50.0894 20C50.0894 25.799 54.5248 30.5 59.996 30.5C65.4673 30.5 69.9026 25.799 69.9026 20C69.9026 14.201 65.4673 9.5 59.996 9.5Z" fill="#1E1B18"/>
                    <path d="M120.036 40H110.284L121.272 0H133.625L144.612 40H134.86L127.578 11.0156H127.318L120.036 40ZM124.001 24.2188H130.895L132.937 32.3438H121.96L124.001 24.2188Z" fill="#1E1B18"/>
                    <path d="M145.53 40V0H159.898C162.369 0 164.531 0.585938 166.384 1.75781C168.237 2.92969 169.678 4.57682 170.707 6.69922C171.737 8.82161 172.251 11.3021 172.251 14.1406C172.251 17.0052 171.72 19.4857 170.658 21.582C169.607 23.6784 168.128 25.293 166.221 26.4258C164.325 27.5586 162.109 28.125 159.573 28.125H154.567V19.6875H157.753C158.815 19.6875 159.72 19.4661 160.467 19.0234C161.226 18.5677 161.805 17.9232 162.206 17.0898C162.618 16.2565 162.824 15.2734 162.824 14.1406C162.824 12.9948 162.618 12.0182 162.206 11.2109C161.805 10.3906 161.226 9.76562 160.467 9.33594C159.72 8.89323 158.815 8.67187 157.753 8.67187H154.567V40H145.53Z" fill="#1E1B18"/>
                    <path d="M173.279 40V0H187.647C190.118 0 192.279 0.585938 194.132 1.75781C195.985 2.92969 197.426 4.57682 198.456 6.69922C199.485 8.82161 200 11.3021 200 14.1406C200 17.0052 199.469 19.4857 198.407 21.582C197.356 23.6784 195.877 25.293 193.97 26.4258C192.074 27.5586 189.858 28.125 187.322 28.125H182.316V19.6875H185.502C186.563 19.6875 187.468 19.4661 188.216 19.0234C188.974 18.5677 189.554 17.9232 189.955 17.0898C190.367 16.2565 190.573 15.2734 190.573 14.1406C190.573 12.9948 190.367 12.0182 189.955 11.2109C189.554 10.3906 188.974 9.76562 188.216 9.33594C187.468 8.89323 186.563 8.67187 185.502 8.67187H182.316V40H173.279Z" fill="#1E1B18"/>
                    </svg>
                </div>
            </div>

            <!-- Keep in touch Section -->
            <div>
                <h3 class="text-gray-900 text-left text-lg font-semibold mb-4">
                    Keep in touch
                </h3>
                <div class="flex items-center gap-2 justify-start -ml-3">
                    <a href="https://www.facebook.com/profile.php?id=100083678822547" target="_blank" class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="Facebook">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.0007 1.2168H12.5007C11.3956 1.2168 10.3358 1.65578 9.55437 2.43719C8.77297 3.21859 8.33398 4.27839 8.33398 5.38346V7.88346H5.83398V11.2168H8.33398V17.8835H11.6673V11.2168H14.1673L15.0007 7.88346H11.6673V5.38346C11.6673 5.16245 11.7551 4.95049 11.9114 4.79421C12.0677 4.63793 12.2796 4.55013 12.5007 4.55013H15.0007V1.2168Z" stroke="#1E1B18" stroke-opacity="0.9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/realoohappofficial/" target="_blank" class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="Instagram">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.88 4.52297C8.96 4.52297 8.10667 4.75297 7.32 5.21297C6.53333 5.67297 5.90667 6.2963 5.44 7.08297C4.97333 7.86963 4.74 8.7263 4.74 9.65297C4.74 10.5796 4.97 11.4363 5.43 12.223C5.89 13.0096 6.51667 13.633 7.31 14.093C8.10333 14.553 8.96333 14.783 9.89 14.783C10.8167 14.783 11.6733 14.553 12.46 14.093C13.2467 13.633 13.87 13.0096 14.33 12.223C14.79 11.4363 15.02 10.5796 15.02 9.65297C15.02 8.7263 14.79 7.86963 14.33 7.08297C13.87 6.2963 13.2467 5.67297 12.46 5.21297C11.6733 4.75297 10.8133 4.52297 9.88 4.52297ZM9.88 12.823C9.30667 12.823 8.77667 12.6796 8.29 12.393C7.80333 12.1063 7.41667 11.7196 7.13 11.233C6.84333 10.7463 6.7 10.2163 6.7 9.64297C6.7 9.06963 6.84333 8.54297 7.13 8.06297C7.41667 7.58297 7.80333 7.19963 8.29 6.91297C8.77667 6.6263 9.30667 6.48297 9.88 6.48297C10.4533 6.48297 10.9833 6.6263 11.47 6.91297C11.9567 7.19963 12.3433 7.58297 12.63 8.06297C12.9167 8.54297 13.06 9.06963 13.06 9.64297C13.06 10.2163 12.9167 10.7463 12.63 11.233C12.3433 11.7196 11.9567 12.1063 11.47 12.393C10.9833 12.6796 10.4533 12.823 9.88 12.823ZM14.08 -0.237032H5.68C4.65333 -0.237032 3.7 0.0229683 2.82 0.542967C1.95333 1.04963 1.26667 1.73963 0.76 2.61297C0.253333 3.4863 0 4.4363 0 5.46297V13.843C0 14.8696 0.253333 15.8196 0.76 16.693C1.26667 17.5663 1.95333 18.2563 2.82 18.763C3.7 19.283 4.65333 19.543 5.68 19.543H14.08C15.1067 19.543 16.0667 19.283 16.96 18.763C17.8133 18.2563 18.4933 17.5763 19 16.723C19.52 15.8296 19.78 14.8696 19.78 13.843V5.46297C19.78 4.4363 19.52 3.4763 19 2.58297C18.4933 1.72963 17.8133 1.04963 16.96 0.542967C16.0667 0.0229683 15.1067 -0.237032 14.08 -0.237032ZM17.82 13.843C17.82 14.523 17.6533 15.1496 17.32 15.723C16.9867 16.2963 16.5333 16.7496 15.96 17.083C15.3867 17.4163 14.76 17.583 14.08 17.583H5.68C5 17.583 4.37667 17.4163 3.81 17.083C3.24333 16.7496 2.79333 16.2963 2.46 15.723C2.12667 15.1496 1.96 14.523 1.96 13.843V5.46297C1.96 4.78297 2.12667 4.1563 2.46 3.58297C2.79333 3.00963 3.24333 2.5563 3.81 2.22297C4.37667 1.88964 5 1.72297 5.68 1.72297H14.08C14.76 1.72297 15.3867 1.88964 15.96 2.22297C16.5333 2.5563 16.9867 3.00963 17.32 3.58297C17.6533 4.1563 17.82 4.78297 17.82 5.46297V13.843ZM15.1 3.02297C14.7267 3.02297 14.4067 3.1563 14.14 3.42297C13.8733 3.68963 13.74 4.00963 13.74 4.38297C13.74 4.7563 13.8733 5.07297 14.14 5.33297C14.4067 5.59297 14.7267 5.72297 15.1 5.72297C15.4733 5.72297 15.79 5.59297 16.05 5.33297C16.31 5.07297 16.44 4.7563 16.44 4.38297C16.44 4.00963 16.31 3.68963 16.05 3.42297C15.79 3.1563 15.4733 3.02297 15.1 3.02297Z" fill="#1E1B18"/>
                        </svg>
                    </a>
                    <a href="https://twitter.com/oohapads" target="_blank"  class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="Twitter/X">
                        <svg width="19" height="17" viewBox="0 0 19 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 0H2.08333L14.5833 16.6667H12.5L0 0ZM3.75 0H5.83333L18.3333 16.6667H16.25L3.75 0Z" fill="#1E1B18" fill-opacity="0.9"/>
                        <path d="M1.66602 0H5.83268V1.66667H1.66602V0ZM12.4993 16.6667H16.666V15H12.4993V16.6667Z" fill="#1E1B18" fill-opacity="0.9"/>
                        <path d="M14.5827 0H17.4993L3.33268 16.6667H0.416016L14.5827 0Z" fill="#1E1B18" fill-opacity="0.9"/>
                        </svg>
                    </a>
                    <a href="https://www.linkedin.com/company/oohapp/" target="_blank" class="w-9 h-9  rounded flex items-center justify-center hover:bg-gray-100 transition-colors" title="LinkedIn">
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
                    <a href="https://play.google.com/" target="_blank" class="inline-block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" class="h-10">
                    </a>
                    <a href="https://www.apple.com/" target="_blank" class="inline-block">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="Download on the App Store" class="h-10">
                    </a>
                </div>
            </div> -->

            <!-- Let us help you -->
            <div>
                <h3 class="text-gray-900 text-lg font-semibold mb-4">Let us help you</h3>
               <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('about') }}" class="text-gray-600 hover:text-gray-900">
                            About OOHAPP
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('faqs') }}" class="text-gray-600 hover:text-gray-900">
                            FAQs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('terms') }}" class="text-gray-600 hover:text-gray-900">
                            Terms & Conditions
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('disclaimer') }}" class="text-gray-600 hover:text-gray-900">
                            Legal Disclaimer
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('privacy') }}" class="text-gray-600 hover:text-gray-900">
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('refund') }}" class="text-gray-600 hover:text-gray-900">
                            Refund & Cancellation Policy
                        </a>
                    </li>
              </ul>

            </div>
        </div>

        <!-- OOHAPP Hoardings Section -->
        <div class="border-t border-gray-200 pt-8 mb-8">
            <h3 class="text-gray-900 text-base font-bold mb-6">OOHAPP Hoardings</h3>
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

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-y-2 text-sm text-gray-600">
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
<center class="text-gray-500 py-2" style="background-color:#e8e8e8;">© {{ date('Y') }} www.oohapp.io. All rights reserved.</center>
   
<!-- Bottom Bar
<div class="pt-8 border-t border-gray-800">
    <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
        <p>&copy; {{ date('Y') }} OOHAPP. All rights reserved.</p>
        <p class="mt-2 md:mt-0">Made with ❤️ in India</p>
    </div>
</div> -->
    </div>
</footer>
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

