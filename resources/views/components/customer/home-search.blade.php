<div class="hidden md:flex items-center flex-1 max-w-3xl mx-4 bg-[#f0f0f0] rounded-md">
                <form action="{{ route('search') }}" method="GET" class="flex items-center w-full bg-[#f0f0f0] rounded-md border border-white overflow-hidden">
                    <!-- Location Search Input -->
                    <div class="flex items-center flex-1 px-4 py-2.5">
                        <svg class="w-4 h-4 text-gray-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input 
                            type="text" 
                            name="location" 
                            placeholder="Search by city, locality.." 
                            class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm text-gray-700 placeholder-gray-600"
                        >
                    </div>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Near Me Button -->
                    <button 
                        type="button" 
                        class="flex items-center px-4 py-2.5 text-sm text-gray-600 bg-gray-50  rounded hover:text-gray-600 hover:bg-gray-100 transition-colors"
                        onclick="getCurrentLocation()"
                    >
                        <svg class="w-4 h-4 mr-1.5 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="whitespace-nowrap">Near me</span>
                    </button>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Date Range Picker -->
                    <div class="flex items-center px-4 py-2.5 min-w-[180px]">
                        <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-400 leading-tight">From - To</span>
                            <input type="hidden" name="from_date" id="from_date">
                            <input type="hidden" name="to_date" id="to_date">
                            <input 
                                type="text" 
                                id="dateRange"
                                class="bg-transparent border-none focus:outline-none focus:ring-0 text-xs text-gray-700 cursor-pointer p-0 leading-tight"
                                value="Wed, 11 Dec 24 - Thu, 12 Dec 24"
                                readonly
                            >
                        </div>
                    </div>

                    <!-- Search Button -->
                    <button 
                        type="submit" 
                        class="px-6 py-4 bg-black text-white text-sm font-semibold hover:bg-[#383434] transition-colors"
                        >
                        Search
                    </button>
                </form>
            </div>

            <script>
                 function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    // Redirect to search with coordinates
                    window.location.href = `{{ route('search') }}?lat=${lat}&lng=${lng}&near_me=1`;
                },
                function(error) {
                    alert('Unable to get your location. Please enable location services.');
                }
            );
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    }

    // Date Range Picker (Using flatpickr or similar library)
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('dateRange');
        if (dateInput && typeof flatpickr !== 'undefined') {
            flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "D, d M y",
                onChange: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        document.getElementById('from_date').value =
                            selectedDates[0].toISOString().split('T')[0];

                        document.getElementById('to_date').value =
                            selectedDates[1].toISOString().split('T')[0];
                    }
                }
            });

        } else {
            // Fallback: Set default text
            if (dateInput) {
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                
                const formatDate = (date) => {
                    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear().toString().slice(-2)}`;
                };
                
                dateInput.value = `${formatDate(today)} - ${formatDate(tomorrow)}`;
            }
        }
    });
            </script>