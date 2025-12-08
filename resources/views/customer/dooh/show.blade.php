<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="api-token" content="{{ auth()->user()->api_token ?? '' }}">
    <title>DOOH Screen Details - OOHApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .screen-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        .package-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .package-card:hover {
            border-color: #667eea;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.2);
        }
        .package-card.selected {
            border-color: #667eea;
            background-color: #f0f4ff;
        }
        .price-highlight {
            font-size: 2rem;
            color: #667eea;
            font-weight: bold;
        }
        .booking-summary {
            position: sticky;
            top: 20px;
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .countdown-timer {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/customer/dooh">
                <i class="fas fa-tv"></i> OOHApp - DOOH
            </a>
            <div class="ms-auto">
                <a href="/customer/dooh" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Screens
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Screen Header -->
        <div class="screen-hero text-center" id="screenHero">
            <!-- Loaded via AJAX -->
        </div>

        <div class="row">
            <!-- Left Column: Packages -->
            <div class="col-lg-8">
                <h4 class="fw-bold mb-4">
                    <i class="fas fa-box-open"></i> Available Packages
                </h4>
                <div id="packagesContainer">
                    <!-- Packages loaded here -->
                </div>
            </div>

            <!-- Right Column: Booking Summary -->
            <div class="col-lg-4">
                <div class="booking-summary">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-calendar-check"></i> Booking Summary
                    </h5>
                    
                    <div id="selectedPackageInfo" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Selected Package</label>
                            <p id="packageName" class="text-primary fw-bold"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" class="form-control" id="startDate" min="">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" class="form-control" id="endDate" min="">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Duration</label>
                            <p id="durationDisplay" class="text-muted">Select dates</p>
                        </div>

                        <hr>

                        <div class="mb-2 d-flex justify-content-between">
                            <span>Package Price:</span>
                            <strong id="packagePrice">₹0</strong>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Duration:</span>
                            <strong id="durationMonths">0 months</strong>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Total Amount:</span>
                            <strong id="totalAmount">₹0</strong>
                        </div>
                        <div class="mb-2 d-flex justify-content-between text-success">
                            <span>Discount:</span>
                            <strong id="discountAmount">- ₹0</strong>
                        </div>
                        <div class="mb-2 d-flex justify-content-between">
                            <span>Tax (18%):</span>
                            <strong id="taxAmount">₹0</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Grand Total:</span>
                            <span class="price-highlight" id="grandTotal">₹0</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Customer Notes (Optional)</label>
                            <textarea class="form-control" id="customerNotes" rows="3" placeholder="Any special requirements..."></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="surveyRequired">
                            <label class="form-check-label" for="surveyRequired">
                                Include Survey Package
                            </label>
                        </div>

                        <button class="btn btn-primary w-100 btn-lg" id="checkAvailabilityBtn">
                            <i class="fas fa-search"></i> Check Availability
                        </button>

                        <button class="btn btn-success w-100 btn-lg mt-2" id="proceedToBookBtn" style="display: none;">
                            <i class="fas fa-shopping-cart"></i> Proceed to Book
                        </button>

                        <div id="availabilityMessage" class="alert mt-3" style="display: none;"></div>
                    </div>

                    <div id="noPackageSelected">
                        <p class="text-muted text-center">
                            <i class="fas fa-hand-pointer fa-3x mb-3 d-block"></i>
                            Select a package to continue
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-credit-card"></i> Complete Payment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="paymentLoading">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p>Initializing payment...</p>
                    </div>
                    <div id="paymentError" style="display: none;">
                        <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                        <p class="text-danger" id="paymentErrorMessage"></p>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const apiToken = document.querySelector('meta[name="api-token"]').getAttribute('content');
        const screenId = {{ $screenId ?? 'null' }}; // Pass from route
        let selectedPackage = null;
        let screenData = null;
        let bookingData = null;

        $(document).ready(function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            $('#startDate').attr('min', today);

            loadScreenDetails();

            // Date change handlers
            $('#startDate, #endDate').change(function() {
                updateDuration();
            });

            // Check availability
            $('#checkAvailabilityBtn').click(function() {
                checkAvailability();
            });

            // Proceed to book
            $('#proceedToBookBtn').click(function() {
                createBooking();
            });
        });

        function loadScreenDetails() {
            $.ajax({
                url: `/api/v1/customer/dooh/screens/${screenId}`,
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        screenData = response.data;
                        renderScreenHeader(screenData);
                        renderPackages(screenData.active_packages);
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('Failed to load screen details');
                }
            });
        }

        function renderScreenHeader(screen) {
            const html = `
                <h2 class="fw-bold mb-3">${screen.name}</h2>
                <p class="mb-2"><i class="fas fa-map-marker-alt"></i> ${screen.address}, ${screen.city}, ${screen.state}</p>
                <div class="row justify-content-center mt-4">
                    <div class="col-auto">
                        <div class="text-center">
                            <h3>${screen.slot_duration_seconds}s</h3>
                            <small>Slot Duration</small>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="text-center">
                            <h3>${screen.loop_duration_seconds / 60}min</h3>
                            <small>Loop Duration</small>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="text-center">
                            <h3>${screen.available_slots_per_day}</h3>
                            <small>Available Slots/Day</small>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="text-center">
                            <h3>₹${Number(screen.minimum_booking_amount).toLocaleString('en-IN')}</h3>
                            <small>Min Booking Amount</small>
                        </div>
                    </div>
                </div>
            `;
            $('#screenHero').html(html);
        }

        function renderPackages(packages) {
            if (!packages || packages.length === 0) {
                $('#packagesContainer').html('<p class="text-muted">No packages available</p>');
                return;
            }

            let html = '';
            packages.forEach(pkg => {
                html += `
                    <div class="package-card mb-3 p-4" onclick="selectPackage(${pkg.id}, '${pkg.package_name}', ${pkg.price_per_month}, ${pkg.discount_percent}, ${pkg.slots_per_day})">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="fw-bold mb-2">${pkg.package_name}</h5>
                                <p class="text-muted mb-2">${pkg.description || ''}</p>
                                <div class="d-flex gap-3">
                                    <span class="badge bg-primary">${pkg.slots_per_day} slots/day</span>
                                    <span class="badge bg-info">${pkg.loop_interval_minutes}min frequency</span>
                                    ${pkg.discount_percent > 0 ? `<span class="badge bg-success">${pkg.discount_percent}% OFF</span>` : ''}
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="price-highlight">₹${Number(pkg.price_per_month).toLocaleString('en-IN')}</div>
                                <small class="text-muted">per month</small>
                                <div class="mt-2">
                                    <small class="text-muted">${pkg.min_booking_months}-${pkg.max_booking_months} months</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            $('#packagesContainer').html(html);
        }

        function selectPackage(id, name, pricePerMonth, discountPercent, slotsPerDay) {
            selectedPackage = {
                id: id,
                name: name,
                price_per_month: pricePerMonth,
                discount_percent: discountPercent,
                slots_per_day: slotsPerDay
            };

            // Visual feedback
            $('.package-card').removeClass('selected');
            $(event.currentTarget).addClass('selected');

            // Show booking form
            $('#noPackageSelected').hide();
            $('#selectedPackageInfo').show();
            $('#packageName').text(name);
            $('#proceedToBookBtn').hide();
            $('#availabilityMessage').hide();

            calculatePricing();
        }

        function updateDuration() {
            const start = $('#startDate').val();
            const end = $('#endDate').val();

            if (start && end) {
                const startDate = new Date(start);
                const endDate = new Date(end);
                const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
                const months = Math.ceil(days / 30);

                $('#durationDisplay').text(`${days} days (${months} months)`);
                
                // Update end date minimum
                $('#endDate').attr('min', start);

                calculatePricing();
            }
        }

        function calculatePricing() {
            if (!selectedPackage) return;

            const start = $('#startDate').val();
            const end = $('#endDate').val();

            if (!start || !end) return;

            const startDate = new Date(start);
            const endDate = new Date(end);
            const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
            const months = Math.ceil(days / 30);

            const packagePrice = selectedPackage.price_per_month;
            const totalAmount = packagePrice * months;
            const discountAmount = (totalAmount * selectedPackage.discount_percent) / 100;
            const taxableAmount = totalAmount - discountAmount;
            const taxAmount = (taxableAmount * 18) / 100;
            const grandTotal = taxableAmount + taxAmount;

            $('#packagePrice').text(`₹${Number(packagePrice).toLocaleString('en-IN')}`);
            $('#durationMonths').text(`${months} month${months > 1 ? 's' : ''}`);
            $('#totalAmount').text(`₹${Number(totalAmount).toLocaleString('en-IN')}`);
            $('#discountAmount').text(`- ₹${Number(discountAmount).toLocaleString('en-IN')}`);
            $('#taxAmount').text(`₹${Number(taxAmount).toLocaleString('en-IN')}`);
            $('#grandTotal').text(`₹${Number(grandTotal).toLocaleString('en-IN')}`);
        }

        function checkAvailability() {
            if (!selectedPackage) {
                alert('Please select a package');
                return;
            }

            const start = $('#startDate').val();
            const end = $('#endDate').val();

            if (!start || !end) {
                alert('Please select both start and end dates');
                return;
            }

            $('#checkAvailabilityBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Checking...');

            $.ajax({
                url: `/api/v1/customer/dooh/packages/${selectedPackage.id}/check-availability`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    start_date: start,
                    end_date: end
                }),
                success: function(response) {
                    $('#checkAvailabilityBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Check Availability');

                    const msg = $('#availabilityMessage');
                    if (response.data.available) {
                        msg.removeClass('alert-danger').addClass('alert-success')
                           .html('<i class="fas fa-check-circle"></i> ' + response.data.message)
                           .show();
                        $('#proceedToBookBtn').show();
                    } else {
                        msg.removeClass('alert-success').addClass('alert-danger')
                           .html('<i class="fas fa-times-circle"></i> ' + response.data.message)
                           .show();
                        $('#proceedToBookBtn').hide();
                    }
                },
                error: function(xhr) {
                    $('#checkAvailabilityBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Check Availability');
                    alert('Availability check failed: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }

        function createBooking() {
            $('#proceedToBookBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            const data = {
                dooh_package_id: selectedPackage.id,
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
                customer_notes: $('#customerNotes').val(),
                survey_required: $('#surveyRequired').is(':checked')
            };

            $.ajax({
                url: '/api/v1/customer/dooh/bookings',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(data),
                success: function(response) {
                    if (response.success) {
                        bookingData = response.data;
                        initiatePayment(bookingData.id);
                    }
                },
                error: function(xhr) {
                    $('#proceedToBookBtn').prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Proceed to Book');
                    alert('Booking creation failed: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }

        function initiatePayment(bookingId) {
            $('#paymentModal').modal('show');

            $.ajax({
                url: `/api/v1/customer/dooh/bookings/${bookingId}/initiate-payment`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        openRazorpayCheckout(response.data);
                    }
                },
                error: function(xhr) {
                    $('#paymentLoading').hide();
                    $('#paymentError').show();
                    $('#paymentErrorMessage').text(xhr.responseJSON?.message || 'Payment initiation failed');
                }
            });
        }

        function openRazorpayCheckout(paymentData) {
            const options = {
                key: paymentData.razorpay_key,
                amount: paymentData.amount,
                currency: paymentData.currency,
                order_id: paymentData.order_id,
                name: 'OOHApp - DOOH Booking',
                description: selectedPackage.name,
                handler: function (response) {
                    confirmPayment(bookingData.id, response);
                },
                prefill: {
                    name: '{{ auth()->user()->name }}',
                    email: '{{ auth()->user()->email }}'
                },
                theme: {
                    color: '#667eea'
                }
            };

            const rzp = new Razorpay(options);
            rzp.open();
            $('#paymentModal').modal('hide');
        }

        function confirmPayment(bookingId, razorpayResponse) {
            $.ajax({
                url: `/api/v1/customer/dooh/bookings/${bookingId}/confirm-payment`,
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + apiToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(razorpayResponse),
                success: function(response) {
                    if (response.success) {
                        window.location.href = `/customer/dooh/bookings/${bookingId}/success`;
                    }
                },
                error: function(xhr) {
                    alert('Payment confirmation failed: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }
    </script>
</body>
</html>
