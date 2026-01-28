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
                            value="{{ request('location') }}"
                            placeholder="Search by city, locality.."
                            class="w-full bg-transparent border-none focus:outline-none focus:ring-0 text-sm text-gray-700 placeholder-gray-600"
                        />

                    </div>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Near Me Button -->
                    <button 
                        type="button" 
                        class="flex items-center px-4 gap-2 py-2.5 text-sm text-gray-600 bg-gray-50  rounded hover:text-gray-600 hover:bg-gray-100 transition-colors"
                        onclick="getCurrentLocation()"
                    >
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.5008 21.4508V19.9898C9.41745 19.7944 7.69745 19.0184 6.34078 17.6618C4.98345 16.3044 4.20745 14.5841 4.01278 12.5008H2.55078V11.5008H4.01278C4.20745 9.41745 4.98345 7.69745 6.34078 6.34078C7.69811 4.98411 9.41811 4.20811 11.5008 4.01278V2.55078H12.5008V4.01278C14.5841 4.20745 16.3041 4.98345 17.6608 6.34078C19.0174 7.69811 19.7938 9.41811 19.9898 11.5008H21.4508V12.5008H19.9898C19.7944 14.5841 19.0184 16.3041 17.6618 17.6608C16.3044 19.0181 14.5841 19.7944 12.5008 19.9898V21.4508H11.5008ZM12.0008 19.0008C13.9341 19.0008 15.5841 18.3174 16.9508 16.9508C18.3174 15.5841 19.0008 13.9341 19.0008 12.0008C19.0008 10.0674 18.3174 8.41745 16.9508 7.05078C15.5841 5.68411 13.9341 5.00078 12.0008 5.00078C10.0674 5.00078 8.41745 5.68411 7.05078 7.05078C5.68411 8.41745 5.00078 10.0674 5.00078 12.0008C5.00078 13.9341 5.68411 15.5841 7.05078 16.9508C8.41745 18.3174 10.0674 19.0008 12.0008 19.0008ZM12.0008 15.0008C11.1761 15.0008 10.4698 14.7071 9.88178 14.1198C9.29378 13.5324 9.00011 12.8261 9.00078 12.0008C9.00145 11.1754 9.29511 10.4691 9.88178 9.88178C10.4684 9.29445 11.1748 9.00078 12.0008 9.00078C12.8268 9.00078 13.5331 9.29445 14.1198 9.88178C14.7064 10.4691 15.0001 11.1754 15.0008 12.0008C15.0014 12.8261 14.7078 13.5324 14.1198 14.1198C13.5318 14.7071 12.8254 15.0008 12.0008 15.0008ZM12.0008 14.0008C12.5508 14.0008 13.0218 13.8051 13.4138 13.4138C13.8058 13.0224 14.0014 12.5514 14.0008 12.0008C14.0001 11.4501 13.8044 10.9794 13.4138 10.5888C13.0231 10.1981 12.5521 10.0021 12.0008 10.0008C11.4494 9.99945 10.9788 10.1954 10.5888 10.5888C10.1988 10.9821 10.0028 11.4528 10.0008 12.0008C9.99878 12.5488 10.1948 13.0198 10.5888 13.4138C10.9828 13.8078 11.4534 14.0034 12.0008 14.0008Z" fill="#484848"/>
                        </svg>
                        <span class="whitespace-nowrap">Near me</span>
                    </button>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Date Range Picker -->
                    <div class="flex items-center px-4 py-2.5 min-w-[180px] gap-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18.438 4.95334H16.5V3.54534C16.5 3.28334 16.27 3.03334 16 3.04534C15.8682 3.0479 15.7425 3.1014 15.6493 3.19462C15.5561 3.28784 15.5026 3.41353 15.5 3.54534V4.95334H8.50001V3.54534C8.50001 3.28334 8.27001 3.03334 8.00001 3.04534C7.8682 3.0479 7.74251 3.1014 7.64929 3.19462C7.55607 3.28784 7.50257 3.41353 7.50001 3.54534V4.95334H5.56201C4.89921 4.95413 4.26379 5.21778 3.79512 5.68645C3.32645 6.15512 3.06281 6.79054 3.06201 7.45334V18.4533C3.06201 19.8323 4.18401 20.9533 5.56201 20.9533H18.437C19.816 20.9533 20.937 19.8323 20.937 18.4533V7.45334C20.937 6.79047 20.6738 6.15474 20.2051 5.68592C19.7365 5.21711 19.1009 4.9536 18.438 4.95334ZM5.56201 5.95334H7.50001V6.54534C7.50001 6.80734 7.73001 7.05734 8.00001 7.04534C8.27101 7.03334 8.50001 6.82534 8.50001 6.54534V5.95334H15.5V6.54534C15.5 6.80734 15.73 7.05734 16 7.04534C16.271 7.03334 16.5 6.82534 16.5 6.54534V5.95334H18.437C19.264 5.95334 19.937 6.62634 19.937 7.45334V9.03734H4.06201V7.45334C4.06201 6.62634 4.73501 5.95334 5.56201 5.95334ZM18.438 19.9533H5.56201C4.73501 19.9533 4.06201 19.2803 4.06201 18.4533V10.0373H19.937V18.4533C19.937 18.851 19.7791 19.2324 19.498 19.5136C19.2169 19.7949 18.8357 19.9531 18.438 19.9533Z" fill="#484848"/>
                        </svg>
                        <div class="flex flex-col">
                            <span class="text-xs text-gray-400 leading-tight">From - To</span>
                            <input type="hidden" name="from_date" id="from_date">
                            <input type="hidden" name="to_date" id="to_date">
                            <input 
                                type="text" 
                                id="dateRange"
                                class="bg-transparent border-none focus:outline-none focus:ring-0 text-xs text-gray-700 cursor-pointer p-0 leading-tight"
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

            </script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dateInput = document.getElementById('dateRange');
        const fromInput = document.getElementById('from_date');
        const toInput   = document.getElementById('to_date');

        const urlParams = new URLSearchParams(window.location.search);
        const urlFrom = urlParams.get('from_date');
        const urlTo   = urlParams.get('to_date');

        function formatDisplay(date) {
            return date.toLocaleDateString('en-GB', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: '2-digit'
            });
        }

        function setDefaultDates() {
            const today = new Date();
            const tomorrow = new Date();
            tomorrow.setDate(today.getDate() + 1);

            dateInput.value = `${formatDisplay(today)} - ${formatDisplay(tomorrow)}`;
            fromInput.value = toLocalYMD(today);
            toInput.value   = toLocalYMD(tomorrow);
            return [today, tomorrow];
        }

        let defaultDates;

        // ✅ If search already has dates → use them
        if (urlFrom && urlTo) {
            const fromDate = new Date(urlFrom);
            const toDate   = new Date(urlTo);

            dateInput.value = `${formatDisplay(fromDate)} - ${formatDisplay(toDate)}`;
            fromInput.value = urlFrom;
            toInput.value   = urlTo;

            defaultDates = [fromDate, toDate];
        } 
        // ✅ else fallback to today
        else {
            defaultDates = setDefaultDates();
        }

        if (typeof flatpickr !== 'undefined') {
            flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "D, d M y",
                defaultDate: defaultDates,

                onChange: function (selectedDates) {
                    if (selectedDates.length === 2) {
                        fromInput.value = toLocalYMD(selectedDates[0]);
                        toInput.value   = toLocalYMD(selectedDates[1]);
                    }
                },

                onClose: function (selectedDates) {
                    if (selectedDates.length < 2) {
                        setDefaultDates();
                    }
                }
            });
        }

        // clear filters support
        window.resetDateRange = function () {
            setDefaultDates();
        };
        function toLocalYMD(date) {
            const year  = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day   = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    });
</script>