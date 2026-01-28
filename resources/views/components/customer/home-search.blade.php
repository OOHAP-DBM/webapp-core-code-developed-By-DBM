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
                        <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.95 18.9V17.439C6.86667 17.2437 5.14667 16.4677 3.79 15.111C2.43267 13.7537 1.65667 12.0333 1.462 9.95H0V8.95H1.462C1.65667 6.86667 2.43267 5.14667 3.79 3.79C5.14733 2.43333 6.86733 1.65733 8.95 1.462V0H9.95V1.462C12.0333 1.65667 13.7533 2.43267 15.11 3.79C16.4667 5.14733 17.243 6.86733 17.439 8.95H18.9V9.95H17.439C17.2437 12.0333 16.4677 13.7533 15.111 15.11C13.7537 16.4673 12.0333 17.2437 9.95 17.439V18.9H8.95ZM9.45 16.45C11.3833 16.45 13.0333 15.7667 14.4 14.4C15.7667 13.0333 16.45 11.3833 16.45 9.45C16.45 7.51667 15.7667 5.86667 14.4 4.5C13.0333 3.13333 11.3833 2.45 9.45 2.45C7.51667 2.45 5.86667 3.13333 4.5 4.5C3.13333 5.86667 2.45 7.51667 2.45 9.45C2.45 11.3833 3.13333 13.0333 4.5 14.4C5.86667 15.7667 7.51667 16.45 9.45 16.45ZM9.45 12.45C8.62533 12.45 7.919 12.1563 7.331 11.569C6.743 10.9817 6.44933 10.2753 6.45 9.45C6.45067 8.62467 6.74433 7.91833 7.331 7.331C7.91767 6.74367 8.624 6.45 9.45 6.45C10.276 6.45 10.9823 6.74367 11.569 7.331C12.1557 7.91833 12.4493 8.62467 12.45 9.45C12.4507 10.2753 12.157 10.9817 11.569 11.569C10.981 12.1563 10.2747 12.45 9.45 12.45ZM9.45 11.45C10 11.45 10.471 11.2543 10.863 10.863C11.255 10.4717 11.4507 10.0007 11.45 9.45C11.4493 8.89933 11.2537 8.42867 10.863 8.038C10.4723 7.64733 10.0013 7.45133 9.45 7.45C8.89867 7.44867 8.428 7.64467 8.038 8.038C7.648 8.43133 7.452 8.902 7.45 9.45C7.448 9.998 7.644 10.469 8.038 10.863C8.432 11.257 8.90267 11.4527 9.45 11.45Z" fill="currentColor"/>
                        </svg>
                        <span class="whitespace-nowrap">Near me</span>
                    </button>

                    <!-- Divider -->
                    <div class="h-8 w-px bg-gray-200"></div>

                    <!-- Date Range Picker -->
                    <div class="flex items-center px-4 py-2.5 min-w-[180px] gap-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.6667 14C11.8974 14 12.123 13.9316 12.3148 13.8034C12.5067 13.6752 12.6562 13.493 12.7445 13.2798C12.8328 13.0666 12.8559 12.832 12.8109 12.6057C12.7659 12.3794 12.6548 12.1715 12.4916 12.0084C12.3285 11.8452 12.1206 11.7341 11.8943 11.6891C11.668 11.6441 11.4334 11.6672 11.2202 11.7555C11.007 11.8438 10.8248 11.9933 10.6966 12.1852C10.5684 12.377 10.5 12.6026 10.5 12.8333C10.5 13.1428 10.6229 13.4395 10.8417 13.6583C11.0605 13.8771 11.3572 14 11.6667 14ZM17.5 14C17.7307 14 17.9563 13.9316 18.1482 13.8034C18.34 13.6752 18.4896 13.493 18.5779 13.2798C18.6662 13.0666 18.6893 12.832 18.6443 12.6057C18.5992 12.3794 18.4881 12.1715 18.325 12.0084C18.1618 11.8452 17.9539 11.7341 17.7276 11.6891C17.5013 11.6441 17.2667 11.6672 17.0535 11.7555C16.8404 11.8438 16.6581 11.9933 16.53 12.1852C16.4018 12.377 16.3333 12.6026 16.3333 12.8333C16.3333 13.1428 16.4562 13.4395 16.675 13.6583C16.8938 13.8771 17.1906 14 17.5 14ZM11.6667 18.6667C11.8974 18.6667 12.123 18.5982 12.3148 18.4701C12.5067 18.3419 12.6562 18.1596 12.7445 17.9465C12.8328 17.7333 12.8559 17.4987 12.8109 17.2724C12.7659 17.0461 12.6548 16.8382 12.4916 16.675C12.3285 16.5119 12.1206 16.4008 11.8943 16.3558C11.668 16.3107 11.4334 16.3338 11.2202 16.4221C11.007 16.5104 10.8248 16.66 10.6966 16.8518C10.5684 17.0437 10.5 17.2693 10.5 17.5C10.5 17.8094 10.6229 18.1062 10.8417 18.325C11.0605 18.5438 11.3572 18.6667 11.6667 18.6667ZM17.5 18.6667C17.7307 18.6667 17.9563 18.5982 18.1482 18.4701C18.34 18.3419 18.4896 18.1596 18.5779 17.9465C18.6662 17.7333 18.6893 17.4987 18.6443 17.2724C18.5992 17.0461 18.4881 16.8382 18.325 16.675C18.1618 16.5119 17.9539 16.4008 17.7276 16.3558C17.5013 16.3107 17.2667 16.3338 17.0535 16.4221C16.8404 16.5104 16.6581 16.66 16.53 16.8518C16.4018 17.0437 16.3333 17.2693 16.3333 17.5C16.3333 17.8094 16.4562 18.1062 16.675 18.325C16.8938 18.5438 17.1906 18.6667 17.5 18.6667ZM5.83333 14C6.06408 14 6.28964 13.9316 6.4815 13.8034C6.67336 13.6752 6.82289 13.493 6.91119 13.2798C6.9995 13.0666 7.0226 12.832 6.97758 12.6057C6.93257 12.3794 6.82145 12.1715 6.65829 12.0084C6.49513 11.8452 6.28725 11.7341 6.06094 11.6891C5.83463 11.6441 5.60005 11.6672 5.38687 11.7555C5.17369 11.8438 4.99148 11.9933 4.86329 12.1852C4.73509 12.377 4.66667 12.6026 4.66667 12.8333C4.66667 13.1428 4.78958 13.4395 5.00838 13.6583C5.22717 13.8771 5.52391 14 5.83333 14ZM19.8333 2.33333H18.6667V1.16667C18.6667 0.857247 18.5438 0.560501 18.325 0.341709C18.1062 0.122916 17.8094 0 17.5 0C17.1906 0 16.8938 0.122916 16.675 0.341709C16.4562 0.560501 16.3333 0.857247 16.3333 1.16667V2.33333H7V1.16667C7 0.857247 6.87708 0.560501 6.65829 0.341709C6.4395 0.122916 6.14275 0 5.83333 0C5.52391 0 5.22717 0.122916 5.00838 0.341709C4.78958 0.560501 4.66667 0.857247 4.66667 1.16667V2.33333H3.5C2.57174 2.33333 1.6815 2.70208 1.02513 3.35846C0.368749 4.01484 0 4.90508 0 5.83333V19.8333C0 20.7616 0.368749 21.6518 1.02513 22.3082C1.6815 22.9646 2.57174 23.3333 3.5 23.3333H19.8333C20.7616 23.3333 21.6518 22.9646 22.3082 22.3082C22.9646 21.6518 23.3333 20.7616 23.3333 19.8333V5.83333C23.3333 4.90508 22.9646 4.01484 22.3082 3.35846C21.6518 2.70208 20.7616 2.33333 19.8333 2.33333ZM21 19.8333C21 20.1428 20.8771 20.4395 20.6583 20.6583C20.4395 20.8771 20.1428 21 19.8333 21H3.5C3.19058 21 2.89383 20.8771 2.67504 20.6583C2.45625 20.4395 2.33333 20.1428 2.33333 19.8333V9.33333H21V19.8333ZM21 7H2.33333V5.83333C2.33333 5.52391 2.45625 5.22717 2.67504 5.00838C2.89383 4.78958 3.19058 4.66667 3.5 4.66667H19.8333C20.1428 4.66667 20.4395 4.78958 20.6583 5.00838C20.8771 5.22717 21 5.52391 21 5.83333V7ZM5.83333 18.6667C6.06408 18.6667 6.28964 18.5982 6.4815 18.4701C6.67336 18.3419 6.82289 18.1596 6.91119 17.9465C6.9995 17.7333 7.0226 17.4987 6.97758 17.2724C6.93257 17.0461 6.82145 16.8382 6.65829 16.675C6.49513 16.5119 6.28725 16.4008 6.06094 16.3558C5.83463 16.3107 5.60005 16.3338 5.38687 16.4221C5.17369 16.5104 4.99148 16.66 4.86329 16.8518C4.73509 17.0437 4.66667 17.2693 4.66667 17.5C4.66667 17.8094 4.78958 18.1062 5.00838 18.325C5.22717 18.5438 5.52391 18.6667 5.83333 18.6667Z" fill="#adb2ba"/>
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