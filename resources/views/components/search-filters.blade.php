{{-- 
    Search and Filter Component
    Props: $filters (current filters array), $cities, $states
--}}
@props(['filters' => [], 'cities' => [], 'states' => []])

<div class="search-filters-wrapper mb-4">
    <form id="search-filter-form" method="GET" action="{{ route('search') }}">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <!-- Search Input -->
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control border-start-0 ps-0" 
                                placeholder="Search by location, title..." 
                                value="{{ $filters['search'] ?? '' }}"
                            >
                        </div>
                    </div>

                    <!-- City Filter -->
                    <div class="col-6 col-md-2">
                        <select name="city" class="form-select">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ ($filters['city'] ?? '') === $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- State Filter -->
                    <div class="col-6 col-md-2">
                        <select name="state" class="form-select">
                            <option value="">All States</option>
                            @foreach($states as $state)
                                <option value="{{ $state }}" {{ ($filters['state'] ?? '') === $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div class="col-6 col-md-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="billboard" {{ ($filters['type'] ?? '') === 'billboard' ? 'selected' : '' }}>Billboard</option>
                            <option value="hoarding" {{ ($filters['type'] ?? '') === 'hoarding' ? 'selected' : '' }}>Hoarding</option>
                            <option value="unipole" {{ ($filters['type'] ?? '') === 'unipole' ? 'selected' : '' }}>Unipole</option>
                            <option value="digital" {{ ($filters['type'] ?? '') === 'digital' ? 'selected' : '' }}>Digital Screen</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="col-6 col-md-2">
                        <select name="price_range" class="form-select">
                            <option value="">All Prices</option>
                            <option value="0-10000" {{ ($filters['price_range'] ?? '') === '0-10000' ? 'selected' : '' }}>Under ₹10k</option>
                            <option value="10000-25000" {{ ($filters['price_range'] ?? '') === '10000-25000' ? 'selected' : '' }}>₹10k - ₹25k</option>
                            <option value="25000-50000" {{ ($filters['price_range'] ?? '') === '25000-50000' ? 'selected' : '' }}>₹25k - ₹50k</option>
                            <option value="50000-999999" {{ ($filters['price_range'] ?? '') === '50000-999999' ? 'selected' : '' }}>Above ₹50k</option>
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters Toggle -->
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                        <i class="bi bi-funnel"></i> Advanced Filters
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>

                <!-- Advanced Filters (Collapsed) -->
                <div class="collapse mt-3" id="advancedFilters">
                    <div class="row g-3">
                        <!-- Illumination -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Illumination</label>
                            <select name="illumination" class="form-select form-select-sm">
                                <option value="">Any</option>
                                <option value="lit" {{ ($filters['illumination'] ?? '') === 'lit' ? 'selected' : '' }}>Lit</option>
                                <option value="non-lit" {{ ($filters['illumination'] ?? '') === 'non-lit' ? 'selected' : '' }}>Non-Lit</option>
                            </select>
                        </div>

                        <!-- Min Width -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Min Width (ft)</label>
                            <input type="number" name="min_width" class="form-select form-select-sm" min="0" value="{{ $filters['min_width'] ?? '' }}">
                        </div>

                        <!-- Min Height -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Min Height (ft)</label>
                            <input type="number" name="min_height" class="form-select form-select-sm" min="0" value="{{ $filters['min_height'] ?? '' }}">
                        </div>

                        <!-- Sort By -->
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Sort By</label>
                            <select name="sort" class="form-select form-select-sm">
                                <option value="latest" {{ ($filters['sort'] ?? '') === 'latest' ? 'selected' : '' }}>Latest</option>
                                <option value="price_low" {{ ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="popular" {{ ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('search') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit on filter change (optional)
    const autoSubmitSelects = document.querySelectorAll('#search-filter-form select[name="city"], #search-filter-form select[name="state"]');
    autoSubmitSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('search-filter-form').submit();
        });
    });
});
</script>
