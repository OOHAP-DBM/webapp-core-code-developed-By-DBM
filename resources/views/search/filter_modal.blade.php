<form method="GET" action="{{ route('search') }}">
<div id="filterModal" class="fixed inset-0 z-[9999] hidden">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40" onclick="closeFilterModal()"></div>

    <!-- Modal -->
    <div class="relative bg-white w-full max-w-4xl mx-auto mt-8 rounded-xl shadow-xl overflow-hidden">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">Filters</h2>
            <button type="button" onclick="closeFilterModal()" class="text-xl">&times;</button>
        </div>

        {{-- BODY --}}
        <div class="p-6 max-h-[75vh] overflow-y-auto space-y-8">

            {{-- TYPES OF HOARDING --}}
            <section>
                <h3 class="font-medium mb-3">Types of Hoarding</h3>
                <input type="hidden" name="type" id="filterType" value="{{ request('type') }}">
                <div class="flex gap-3 text-sm">
                    <button type="button" class="filter-btn {{ request('type')==''?'active':'' }}" onclick="setType('')">Any Type</button>
                    <button type="button" class="filter-btn {{ request('type')=='dooh'?'active':'' }}" onclick="setType('dooh')">DOOH</button>
                    <button type="button" class="filter-btn {{ request('type')=='ooh'?'active':'' }}" onclick="setType('ooh')">OOH</button>
                </div>
            </section>

            {{-- CATEGORIES --}}
            <section>
                <h3 class="font-medium mb-3">Categories</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    @foreach([
                        'Flag Sign','Traffic Booth','Standee','LED Screens',
                        'Digital Standee','Metro Panels','Taxi Branding','Unipole',
                        'Canopy','Sandwich Board','Glow Sign Board','Gantry',
                        'BQS','Flag Pole','Slides Scrolling','Flagpole',
                        'Bus Shelter','Balloon Display','Metro Pillars'
                    ] as $cat)
                        <label class="flex items-center gap-2">
                            <input type="checkbox"
                                   name="category[]"
                                   value="{{ $cat }}"
                                   {{ in_array($cat, request('category', [])) ? 'checked' : '' }}>
                            <span>{{ $cat }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- CAMPAIGN DURATION --}}
            <section>
                <h3 class="font-medium mb-1">Campaign Duration</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Select campaign duration. like how long you want to book the hoarding.
                </p>
                <div class="flex gap-3 text-sm">
                    <label class="filter-btn {{ request('duration')=='weekly'?'active':'' }}">
                        <input type="radio" name="duration" value="weekly" hidden {{ request('duration')=='weekly'?'checked':'' }}>
                        Weekly
                    </label>
                    <label class="filter-btn {{ request('duration','monthly')=='monthly'?'active':'' }}">
                        <input type="radio" name="duration" value="monthly" hidden {{ request('duration','monthly')=='monthly'?'checked':'' }}>
                        Monthly
                    </label>
                </div>
            </section>

            {{-- PRICE RANGE --}}
            <section>
                <h3 class="font-medium mb-1">Price range</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Select Price Range Monthly, Weekly & Yearly, with Tax & before Tax.
                </p>

                <div class="flex items-end gap-[2px] h-16 mb-4">
                    @for($i=1;$i<=40;$i++)
                        <div class="w-[3px] bg-black" style="height: {{ rand(20,60) }}%"></div>
                    @endfor
                </div>

                <div class="flex gap-4">
                    <input type="number" name="min_price" value="{{ request('min_price') }}"
                        placeholder="Minimum ₹ 2,000"
                        class="w-full border rounded-md px-3 py-2 text-sm">

                    <input type="number" name="max_price" value="{{ request('max_price') }}"
                        placeholder="Maximum ₹ 10,000"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
            </section>

            {{-- VENDORS --}}
            <section>
                <h3 class="font-medium mb-3">Vendors Hoardings</h3>
                <div class="flex flex-wrap gap-3 text-sm">
                    @foreach(['Digital Brain Media','Shiva Hoardings','Metro Ads','City Media'] as $v)
                        <label class="flex items-center gap-2 border px-3 py-1 rounded">
                            <input type="checkbox" name="vendor[]" value="{{ $v }}"
                                   {{ in_array($v, request('vendor', [])) ? 'checked' : '' }}>
                            <span>{{ $v }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- REVIEW SCORE --}}
            <section>
                <h3 class="font-medium mb-3">Review Score</h3>
                <div class="flex flex-wrap gap-3 text-sm">
                    @foreach([5,4,3,2,1] as $r)
                        <label class="flex items-center gap-2 border px-3 py-1 rounded">
                            <input type="checkbox" name="rating[]" value="{{ $r }}"
                                   {{ in_array($r, request('rating', [])) ? 'checked' : '' }}>
                            <span>{{ $r }} ★ Rating</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- VISIBILITY --}}
            <section>
                <h3 class="font-medium mb-3">Hoarding Visibility</h3>
                <div class="flex flex-wrap gap-3 text-sm">
                    @foreach(['Metro Ride','From Flyover','From the Road','Roof Top'] as $v)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="visibility[]" value="{{ $v }}"
                                   {{ in_array($v, request('visibility', [])) ? 'checked' : '' }}>
                            <span>{{ $v }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- GAZEFLOW --}}
            <section>
                <h3 class="font-medium mb-1">Gazeflow</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Average number of peoples roaming near the hoarding.
                </p>

                <div class="flex items-end gap-[2px] h-16 mb-4">
                    @for($i=1;$i<=40;$i++)
                        <div class="w-[3px] bg-black" style="height: {{ rand(20,60) }}%"></div>
                    @endfor
                </div>

                <div class="flex gap-4">
                    <input type="number" name="min_gazeflow" value="{{ request('min_gazeflow') }}"
                        placeholder="Minimum 100 Daily"
                        class="w-full border rounded-md px-3 py-2 text-sm">

                    <input type="number" name="max_gazeflow" value="{{ request('max_gazeflow') }}"
                        placeholder="Maximum 100,000 Daily"
                        class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
            </section>

            {{-- SIZE --}}
            <section>
                <h3 class="font-medium mb-3">Hoarding Size</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <input type="number" name="min_height" value="{{ request('min_height') }}"
                        placeholder="Select Minimum Height (12 ft)"
                        class="border rounded-md px-3 py-2">

                    <input type="number" name="max_height" value="{{ request('max_height') }}"
                        placeholder="Select Maximum Height (16 ft)"
                        class="border rounded-md px-3 py-2">
                </div>
            </section>

            {{-- AUDIENCE --}}
            <section>
                <h3 class="font-medium mb-3">Audience Type</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    @foreach([
                        'Students','Political activities','Foodies','Public',
                        'Amusement Park Visitors','Business Travelers','Environmentalists',
                        'Small Business Owners','College Students','Art Lovers','Tourists'
                    ] as $aud)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="audience[]" value="{{ $aud }}"
                                   {{ in_array($aud, request('audience', [])) ? 'checked' : '' }}>
                            <span>{{ $aud }}</span>
                        </label>
                    @endforeach
                </div>
            </section>

        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between px-6 py-4 border-t">
            <a href="{{ route('search') }}" class="text-sm text-gray-500 underline">Clear all</a>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md text-sm">
                Apply
            </button>
        </div>

    </div>
</div>
</form>
