{{-- Public Footer --}}
<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">About OOHAPP</h3>
                <p class="mt-4 text-sm text-gray-600">
                    India's leading B2B2C marketplace for OOH and DOOH advertising solutions.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Quick Links</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="{{ route('hoardings.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Hoardings</a></li>
                    <li><a href="{{ route('dooh.index') }}" class="text-sm text-gray-600 hover:text-gray-900">DOOH</a></li>
                    <li><a href="{{ route('search') }}" class="text-sm text-gray-600 hover:text-gray-900">Search</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Support</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900">Help Center</a></li>
                    <li><a href="#" class="text-sm text-gray-600 hover:text-gray-900">Contact Us</a></li>
                    <li><a href="{{ route('terms') }}" class="text-sm text-gray-600 hover:text-gray-900">Terms & Conditions</a></li>
                    <li><a href="{{ route('privacy') }}" class="text-sm text-gray-600 hover:text-gray-900">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Contact</h3>
                <ul class="mt-4 space-y-2 text-sm text-gray-600">
                    <li>Email: support@oohapp.com</li>
                    <li>Phone: +91 1234567890</li>
                </ul>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 pt-8">
            <p class="text-sm text-gray-600 text-center">
                &copy; {{ date('Y') }} OOHAPP. All rights reserved.
            </p>
        </div>
    </div>
</footer>
