<section class="py-12 bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6 md:p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Find Your Perfect Advertising Space</h3>
                
                <form action="{{ route('search') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Location Search -->
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Location
                            </label>
                            <input 
                                type="text" 
                                name="location" 
                                placeholder="Enter city or area"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                        </div>

                        <!-- Category/Type -->
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Category
                            </label>
                            <select 
                                name="type" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white"
                            >
                                <option value="">All Types</option>
                                <option value="billboard">Billboard</option>
                                <option value="digital">Digital Screen</option>
                                <option value="transit">Transit</option>
                                <option value="street_furniture">Street Furniture</option>
                                <option value="wallscape">Wallscape</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>

                        <!-- Budget Range -->
                        <div class="relative">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Budget
                            </label>
                            <select 
                                name="budget" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all appearance-none bg-white"
                            >
                                <option value="">Any Budget</option>
                                <option value="0-50000">Under ₹50,000</option>
                                <option value="50000-100000">₹50,000 - ₹1,00,000</option>
                                <option value="100000-200000">₹1,00,000 - ₹2,00,000</option>
                                <option value="200000+">Above ₹2,00,000</option>
                            </select>
                        </div>
                    </div>

                    <!-- Search Button -->
                    <div class="flex justify-center pt-2">
                        <button 
                            type="submit" 
                            class="px-12 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg flex items-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Search Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
