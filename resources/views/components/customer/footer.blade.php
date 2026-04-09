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
                        <a href="mailto:enquiry@oohapp.io" class="text-gray-600 hover:text-gray-900">enquiry&#64;oohapp&#46;io</a>
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

<footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-3 z-50 md:hidden">
    <div class="flex justify-between items-center max-w-md mx-auto">
        
        <a href="{{ route('home') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('home') ? 'text-green-600' : 'text-gray-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <span class="text-[10px] font-medium">Explore</span>
        </a>
      <a href="javascript:void(0)"
            onclick="openWishlist(event)"
            class="flex flex-col items-center space-y-1 {{ request()->is('shortlist*') ? 'text-green-600' : 'text-gray-400' }}">
            <svg width="24" height="21" viewBox="0 0 24 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 1.82269C10.6038 0.581429 8.77392 -0.0703905 6.90015 0.00603565C5.02637 0.0824618 3.25672 0.881095 1.96804 2.23186C0.67937 3.58263 -0.026497 5.3788 0.000761073 7.23786C0.0280191 9.09693 0.786248 10.872 2.11398 12.1851L10.3042 20.3037C10.754 20.7496 11.364 21 12 21C12.636 21 13.246 20.7496 13.6958 20.3037L21.886 12.1851C23.2138 10.872 23.972 9.09693 23.9992 7.23786C24.0265 5.3788 23.3206 3.58263 22.032 2.23186C20.7433 0.881095 18.9736 0.0824618 17.0999 0.00603565C15.2261 -0.0703905 13.3962 0.581429 12 1.82269ZM10.5944 3.77745L11.1521 4.32916C11.377 4.55206 11.682 4.67729 12 4.67729C12.318 4.67729 12.623 4.55206 12.8479 4.32916L13.4056 3.77745C13.8481 3.32319 14.3775 2.96086 14.9628 2.7116C15.5481 2.46233 16.1776 2.33113 16.8146 2.32564C17.4515 2.32016 18.0832 2.44049 18.6728 2.67963C19.2624 2.91878 19.798 3.27193 20.2484 3.7185C20.6989 4.16506 21.0551 4.69609 21.2963 5.2806C21.5375 5.86511 21.6589 6.49139 21.6534 7.1229C21.6478 7.75441 21.5155 8.37851 21.2641 8.95877C21.0126 9.53904 20.6472 10.0639 20.189 10.5026L12 18.6225L3.81102 10.5026C2.93716 9.60557 2.45362 8.40417 2.46455 7.15713C2.47548 5.9101 2.98 4.7172 3.86946 3.83538C4.75892 2.95356 5.96214 2.45337 7.21997 2.44253C8.47781 2.43169 9.68961 2.91108 10.5944 3.77745Z" fill="#A4A4A4"/>
            </svg>


            <span class="text-[10px] font-medium">Wishlists</span>
        </a>

       <a href="javascript:void(0)"
            onclick="openCart(event)"
            class="flex flex-col items-center space-y-1 {{ request()->is('cart*') ? 'text-green-600' : 'text-gray-400' }}">
           <svg width="24" height="21" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00001 2.0625C8.55245 2.0625 8.12323 2.24029 7.80676 2.55676C7.4903 2.87322 7.31251 3.30245 7.31251 3.75V3.945C7.73026 3.9375 8.18851 3.9375 8.69251 3.9375H9.30826C9.81076 3.9375 10.2698 3.9375 10.6883 3.945V3.75C10.6883 3.52833 10.6446 3.30883 10.5597 3.10405C10.4749 2.89926 10.3505 2.7132 10.1937 2.55649C10.037 2.39978 9.85084 2.2755 9.64602 2.19074C9.44119 2.10598 9.22168 2.0624 9.00001 2.0625ZM11.8125 3.996V3.75C11.8125 3.00408 11.5162 2.28871 10.9887 1.76126C10.4613 1.23382 9.74593 0.9375 9.00001 0.9375C8.25409 0.9375 7.53872 1.23382 7.01127 1.76126C6.48382 2.28871 6.18751 3.00408 6.18751 3.75V3.996C6.08051 4.005 5.97701 4.01575 5.87701 4.02825C5.11951 4.122 4.49551 4.3185 3.96451 4.75875C3.43351 5.199 3.12601 5.7765 2.89501 6.504C2.67001 7.209 2.50051 8.11425 2.28751 9.2535L2.27176 9.336C1.97026 10.9432 1.73326 12.21 1.68901 13.2083C1.64401 14.232 1.79701 15.0795 2.37451 15.7748C2.95201 16.4708 3.75676 16.7768 4.77076 16.9215C5.76076 17.0625 7.04851 17.0625 8.68426 17.0625H9.31801C10.953 17.0625 12.2415 17.0625 13.2308 16.9215C14.2448 16.7768 15.0503 16.4708 15.6278 15.7748C16.2053 15.0788 16.3568 14.232 16.3125 13.2083C16.269 12.21 16.0313 10.9432 15.7298 9.336L15.7148 9.2535C15.501 8.11425 15.3308 7.20825 15.1073 6.504C14.8748 5.7765 14.5673 5.199 14.0363 4.75875C13.506 4.3185 12.8813 4.12125 12.1238 4.02825C12.0205 4.01554 11.917 4.00479 11.8133 3.996M6.01501 5.145C5.37376 5.22375 4.98601 5.373 4.68301 5.625C4.38076 5.8755 4.16251 6.22875 3.96601 6.84525C3.76576 7.47525 3.60751 8.31375 3.38551 9.498C3.07351 11.1607 2.85226 12.348 2.81251 13.2577C2.77351 14.1503 2.91751 14.6678 3.23926 15.057C3.56176 15.4447 4.04401 15.681 4.92901 15.807C5.82901 15.936 7.03801 15.9375 8.73001 15.9375H9.27001C10.9628 15.9375 12.1703 15.936 13.071 15.8077C13.956 15.681 14.4383 15.4447 14.7608 15.057C15.0833 14.6685 15.2265 14.151 15.1883 13.257C15.1478 12.3488 14.9265 11.1607 14.6145 9.498C14.3925 8.313 14.235 7.476 14.034 6.84525C13.8375 6.22875 13.62 5.8755 13.317 5.62425C13.014 5.373 12.627 5.22375 11.985 5.14425C11.328 5.06325 10.4753 5.0625 9.27001 5.0625H8.73001C7.52476 5.0625 6.67201 5.06325 6.01501 5.145Z" fill="black"/>
           </svg>

            <span class="text-[10px] font-medium">Shortlist</span>
        </a>
        <a href="{{ route('login') }}"
            class="flex flex-col items-center space-y-1 {{ request()->is('login') ? 'text-green-600' : 'text-gray-400' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-[10px] font-medium">Log in</span>
        </a>

    </div>
</footer>

<div class="text-center text-gray-500 py-1" style="background-color:#e8e8e8;" id="copyright">© {{ date('Y') }} www.oohapp.io All rights reserved.</div>

<div class="h-[112px] w-full block md:hidden"></div>



<!-- Bottom Bar
<div class="pt-8 border-t border-gray-800">
    <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-400">
        <p>&copy; {{ date('Y') }} OOHAPP. All rights reserved.</p>
        <p class="mt-2 md:mt-0">Made with ❤️ in India</p>
    </div>
</div> -->
    
<!-- <script>
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
</script> -->
<script>
// Newsletter AJAX
document.getElementById('newsletterForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const messageBox = document.getElementById('newsletterMessage');
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

// Mobile Footer Cart/Wishlist logic for guests (localStorage)
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
function openCart(event) {
    event.preventDefault();

    const isAuth = event.currentTarget.dataset.auth === '1';

    if (isAuth) {
        window.location.href = "{{ route('cart.index') }}";
        return;
    }

    const saved = JSON.parse(localStorage.getItem('guest_cart') || '[]');

    if (saved.length === 0) {
        alert('Shortlist is empty');
        return;
    }

    window.location.href = "{{ route('cart.index') }}?ids=" + saved.join(',');
}
</script>