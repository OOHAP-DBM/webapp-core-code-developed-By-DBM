<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="api-token" content="{{ auth()->user()->api_token ?? '' }}">
    <title>DOOH Screens - OOHApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .screen-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
        }
        .screen-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .screen-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        .badge-slots {
            background-color: #28a745;
            padding: 0.4rem 0.8rem;
        }
        .filter-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .location-badge {
            background-color: #6c757d;
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.875rem;
        }
        .price-tag {
            font-size: 1.75rem;
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('customer.dashboard') }}">
                <i class="fas fa-tv"></i> OOHApp - DOOH
            </a>
            <div class="ms-auto">
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h2 class="fw-bold">
                    <i class="fas fa-desktop text-primary"></i> Digital OOH Screens
                </h2>
                <p class="text-muted">Browse and book digital advertising screens with flexible packages</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="/customer/dooh/bookings" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i> My Bookings
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-city"></i> City
                    </label>
                    <select class="form-select" id="cityFilter">
                        <option value="">All Cities</option>
                        <option value="Mumbai">Mumbai</option>
                        <option value="Delhi">Delhi</option>
                        <option value="Bangalore">Bangalore</option>
                        <option value="Hyderabad">Hyderabad</option>
                        <option value="Chennai">Chennai</option>
                        <option value="Pune">Pune</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-map-marker-alt"></i> State
                    </label>
                    <select class="form-select" id="stateFilter">
                        <option value="">All States</option>
                        <option value="Maharashtra">Maharashtra</option>
                        <option value="Delhi">Delhi</option>
                        <option value="Karnataka">Karnataka</option>
                        <option value="Telangana">Telangana</option>
                        <option value="Tamil Nadu">Tamil Nadu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-sort-numeric-up"></i> Min Slots/Day
                    </label>
                    <input type="number" class="form-control" id="minSlotsFilter" placeholder="e.g., 6" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search"></i> Search
                    </label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Name or address...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-end">
                    <button class="btn btn-secondary" id="resetBtn">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button class="btn btn-primary" id="applyFiltersBtn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Screens List -->
        <div id="screensContainer" class="row g-4">
            <!-- Screens will be loaded here via AJAX -->
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="d-flex justify-content-center mt-4">
            <!-- Pagination will be rendered here -->
        </div>

        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center my-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading screens...</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const apiToken = document.querySelector('meta[name="api-token"]').getAttribute('content');
        let currentPage = 1;

        $(document).ready(function() {
            loadScreens();

            $('#applyFiltersBtn').click(function() {
                currentPage = 1;
                loadScreens();
            });

            $('#resetBtn').click(function() {
                $('#cityFilter, #stateFilter, #minSlotsFilter, #searchInput').val('');
                currentPage = 1;
                loadScreens();
            });

            // Enter key on search
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    currentPage = 1;
                    loadScreens();
                }
            });
        });

        function loadScreens() {
            const filters = {
                city: $('#cityFilter').val(),
                state: $('#stateFilter').val(),
                min_slots: $('#minSlotsFilter').val(),
                search: $('#searchInput').val(),
                page: currentPage
            };

            $('#loadingSpinner').show();
            $('#screensContainer').html('');

            $.ajax({
                url: '/api/v1/customer/dooh/screens',
                method: 'GET',
                data: filters,
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    $('#loadingSpinner').hide();
                    
                    if (response.success && response.data.data.length > 0) {
                        renderScreens(response.data.data);
                        renderPagination(response.data);
                    } else {
                        $('#screensContainer').html(`
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-desktop fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No screens found</h4>
                                <p>Try adjusting your filters</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr) {
                    $('#loadingSpinner').hide();
                    console.error('Error:', xhr);
                    $('#screensContainer').html(`
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-4x text-danger mb-3"></i>
                            <h4 class="text-danger">Failed to load screens</h4>
                            <p>Please try again later</p>
                        </div>
                    `);
                }
            });
        }

        function renderScreens(screens) {
            let html = '';
            
            screens.forEach(screen => {
                const packages = screen.active_packages || [];
                const minPrice = packages.length > 0 
                    ? Math.min(...packages.map(p => parseFloat(p.price_per_month)))
                    : parseFloat(screen.price_per_month || 0);
                
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card screen-card h-100">
                            <div class="screen-image">
                                <i class="fas fa-desktop"></i>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title fw-bold">${screen.name}</h5>
                                <p class="text-muted small mb-2">
                                    <i class="fas fa-map-marker-alt"></i> ${screen.address}
                                </p>
                                <div class="mb-2">
                                    <span class="location-badge me-2">${screen.city}</span>
                                    <span class="location-badge">${screen.state}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center my-3">
                                    <div>
                                        <small class="text-muted d-block">Available Slots/Day</small>
                                        <span class="badge badge-slots">${screen.available_slots_per_day}</span>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Min Slots Required</small>
                                        <strong>${screen.min_slots_per_day}</strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Starting from</small>
                                    <div class="price-tag">₹${Number(minPrice).toLocaleString('en-IN')}<small class="text-muted">/month</small></div>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted"><i class="fas fa-clock"></i> ${screen.slot_duration_seconds}s slot | ${screen.loop_duration_seconds/60}min loop</small>
                                </div>
                                <a href="/customer/dooh/screens/${screen.id}" class="btn btn-primary w-100">
                                    <i class="fas fa-eye"></i> View Details & Book
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            $('#screensContainer').html(html);
        }

        function renderPagination(data) {
            if (data.last_page <= 1) {
                $('#paginationContainer').html('');
                return;
            }

            let html = '<nav><ul class="pagination">';
            
            // Previous button
            html += `
                <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${data.current_page - 1}); return false;">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;
            
            // Page numbers
            for (let i = 1; i <= data.last_page; i++) {
                if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                    html += `
                        <li class="page-item ${i === data.current_page ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                        </li>
                    `;
                } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Next button
            html += `
                <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${data.current_page + 1}); return false;">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;
            
            html += '</ul></nav>';
            
            $('#paginationContainer').html(html);
        }

        function changePage(page) {
            currentPage = page;
            loadScreens();
            $('html, body').animate({scrollTop: 0}, 'fast');
        }
    </script>
</body>
</html>
