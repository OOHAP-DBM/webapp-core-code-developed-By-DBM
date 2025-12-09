@extends('layouts.customer')

@section('title', 'Create Booking')

@section('content')
<div class="container py-5">
    <!-- Progress Steps -->
    <div class="mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center position-relative">
                    <!-- Progress Line -->
                    <div class="position-absolute w-100" style="height: 2px; background: #e0e0e0; top: 20px; z-index: 0;"></div>
                    <div class="position-absolute" id="progressLine" style="height: 2px; background: #667eea; top: 20px; z-index: 0; width: 0%; transition: width 0.3s;"></div>
                    
                    <!-- Step 1 -->
                    <div class="text-center position-relative" style="z-index: 1;">
                        <div class="step-circle active" id="step1Circle">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <p class="small mt-2 mb-0">Dates</p>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="text-center position-relative" style="z-index: 1;">
                        <div class="step-circle" id="step2Circle">
                            <i class="bi bi-file-text"></i>
                        </div>
                        <p class="small mt-2 mb-0">Details</p>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="text-center position-relative" style="z-index: 1;">
                        <div class="step-circle" id="step3Circle">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <p class="small mt-2 mb-0">Payment</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Booking Form -->
        <div class="col-lg-8">
            <form id="bookingForm" action="{{ route('customer.bookings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="hoarding_id" value="{{ $hoarding->id }}">
                <input type="hidden" name="quotation_id" value="{{ $quotation->id ?? '' }}">

                <!-- Step 1: Date Selection -->
                <div class="booking-step active" id="step1">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h4 class="mb-4">Select Booking Period</h4>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" 
                                           class="form-control form-control-lg" 
                                           id="start_date" 
                                           name="start_date" 
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                           value="{{ old('start_date', $quotation->enquiry->start_date ?? '') }}"
                                           required>
                                    @error('start_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" 
                                           class="form-control form-control-lg" 
                                           id="end_date" 
                                           name="end_date" 
                                           min="{{ date('Y-m-d', strtotime('+2 days')) }}"
                                           value="{{ old('end_date', $quotation->enquiry->end_date ?? '') }}"
                                           required>
                                    @error('end_date')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="alert alert-light mt-3" id="durationAlert">
                                <i class="bi bi-info-circle me-2"></i>
                                <span id="durationText">Please select dates to see duration</span>
                            </div>

                            <button type="button" class="btn btn-primary" onclick="nextStep(2)">
                                Next: Booking Details
                                <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Booking Details -->
                <div class="booking-step" id="step2">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h4 class="mb-4">Booking Details</h4>
                            
                            <div class="mb-3">
                                <label for="campaign_name" class="form-label">Campaign Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="campaign_name" 
                                       name="campaign_name" 
                                       placeholder="e.g., Summer Sale 2024"
                                       value="{{ old('campaign_name') }}">
                                <small class="text-muted">Optional: Give your campaign a name for easy reference</small>
                            </div>

                            <div class="mb-3">
                                <label for="artwork_details" class="form-label">Artwork Details</label>
                                <textarea class="form-control" 
                                          id="artwork_details" 
                                          name="artwork_details" 
                                          rows="3" 
                                          placeholder="Describe your artwork requirements...">{{ old('artwork_details') }}</textarea>
                                <small class="text-muted">Mention any specific requirements for printing and installation</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Additional Services</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="printing_required" id="printing_required" value="1">
                                    <label class="form-check-label" for="printing_required">
                                        Printing & Installation Required
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="maintenance_required" id="maintenance_required" value="1">
                                    <label class="form-check-label" for="maintenance_required">
                                        Monthly Maintenance Required
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="special_notes" class="form-label">Special Instructions</label>
                                <textarea class="form-control" 
                                          id="special_notes" 
                                          name="special_notes" 
                                          rows="2" 
                                          placeholder="Any special requests or instructions...">{{ old('special_notes') }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(1)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button type="button" class="btn btn-primary" onclick="nextStep(3)">
                                    Next: Payment
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Payment -->
                <div class="booking-step" id="step3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h4 class="mb-4">Payment</h4>
                            
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Payment Terms:</strong> 50% advance required to confirm booking
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_online" value="online" checked>
                                    <label class="form-check-label" for="payment_online">
                                        <i class="bi bi-credit-card me-2"></i>Online Payment (UPI, Card, Net Banking)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="bank_transfer">
                                    <label class="form-check-label" for="payment_bank">
                                        <i class="bi bi-bank me-2"></i>Bank Transfer / NEFT
                                    </label>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" required>
                                <label class="form-check-label" for="terms_accepted">
                                    I agree to the <a href="#" target="_blank">Terms & Conditions</a> and <a href="#" target="_blank">Cancellation Policy</a>
                                </label>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="prevStep(2)">
                                    <i class="bi bi-arrow-left me-2"></i>Back
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Confirm & Pay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column: Booking Summary -->
        <div class="col-lg-4">
            <!-- Hoarding Card -->
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title mb-3">Booking Summary</h5>
                    
                    @if($hoarding->image)
                        <img src="{{ asset('storage/' . $hoarding->image) }}" 
                             alt="{{ $hoarding->title }}" 
                             class="img-fluid rounded mb-3" 
                             style="width: 100%; height: 150px; object-fit: cover;">
                    @endif

                    <h6 class="mb-2">{{ $hoarding->title }}</h6>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ $hoarding->city }}, {{ $hoarding->state }}
                    </p>

                    <hr>

                    <!-- Pricing Breakdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Base Price/month</span>
                            <span>₹{{ number_format($hoarding->price_per_month, 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Duration</span>
                            <span id="summaryDuration">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" id="printingCostRow" style="display: none !important;">
                            <span class="text-muted">Printing & Installation</span>
                            <span id="printingCost">₹0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" id="maintenanceCostRow" style="display: none !important;">
                            <span class="text-muted">Maintenance</span>
                            <span id="maintenanceCost">₹0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="subtotal">₹0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-muted small">
                            <span>GST (18%)</span>
                            <span id="gst">₹0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Total Amount</h5>
                            <h4 class="mb-0 text-primary" id="totalAmount">₹0</h4>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted small">Advance Payment (50%)</span>
                            <span class="fw-bold" id="advanceAmount">₹0</span>
                        </div>
                    </div>

                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Final price may vary based on services selected
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
const basePricePerMonth = {{ $hoarding->price_per_month }};
const printingCostDefault = 15000;
const maintenanceCostPerMonth = 2000;

// Date change handlers
document.getElementById('start_date').addEventListener('change', calculatePrice);
document.getElementById('end_date').addEventListener('change', calculatePrice);
document.getElementById('printing_required').addEventListener('change', calculatePrice);
document.getElementById('maintenance_required').addEventListener('change', calculatePrice);

function nextStep(step) {
    // Validation
    if (currentStep === 1) {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return;
        }
        if (new Date(endDate) <= new Date(startDate)) {
            alert('End date must be after start date');
            return;
        }
    }

    // Hide current step
    document.getElementById('step' + currentStep).classList.remove('active');
    document.getElementById('step' + currentStep + 'Circle').classList.remove('active');
    
    // Show next step
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');
    document.getElementById('step' + currentStep + 'Circle').classList.add('active');
    
    // Update progress line
    const progress = ((currentStep - 1) / 2) * 100;
    document.getElementById('progressLine').style.width = progress + '%';
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function prevStep(step) {
    // Hide current step
    document.getElementById('step' + currentStep).classList.remove('active');
    document.getElementById('step' + currentStep + 'Circle').classList.remove('active');
    
    // Show previous step
    currentStep = step;
    document.getElementById('step' + currentStep).classList.add('active');
    document.getElementById('step' + currentStep + 'Circle').classList.add('active');
    
    // Update progress line
    const progress = ((currentStep - 1) / 2) * 100;
    document.getElementById('progressLine').style.width = progress + '%';
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function calculatePrice() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (!startDate || !endDate) return;
    
    // Calculate duration in days and months
    const start = new Date(startDate);
    const end = new Date(endDate);
    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
    const months = Math.ceil(days / 30);
    
    // Update duration display
    document.getElementById('durationText').textContent = `${days} days (${months} months approx.)`;
    document.getElementById('summaryDuration').textContent = `${months} month${months > 1 ? 's' : ''}`;
    
    // Calculate base cost
    let subtotal = basePricePerMonth * months;
    
    // Add printing cost
    const printingRequired = document.getElementById('printing_required').checked;
    if (printingRequired) {
        subtotal += printingCostDefault;
        document.getElementById('printingCostRow').style.display = 'flex';
        document.getElementById('printingCost').textContent = '₹' + printingCostDefault.toLocaleString();
    } else {
        document.getElementById('printingCostRow').style.display = 'none';
    }
    
    // Add maintenance cost
    const maintenanceRequired = document.getElementById('maintenance_required').checked;
    if (maintenanceRequired) {
        const maintenanceCost = maintenanceCostPerMonth * months;
        subtotal += maintenanceCost;
        document.getElementById('maintenanceCostRow').style.display = 'flex';
        document.getElementById('maintenanceCost').textContent = '₹' + maintenanceCost.toLocaleString();
    } else {
        document.getElementById('maintenanceCostRow').style.display = 'none';
    }
    
    // Calculate GST and total
    const gst = subtotal * 0.18;
    const total = subtotal + gst;
    const advance = total * 0.5;
    
    // Update display
    document.getElementById('subtotal').textContent = '₹' + subtotal.toLocaleString();
    document.getElementById('gst').textContent = '₹' + Math.round(gst).toLocaleString();
    document.getElementById('totalAmount').textContent = '₹' + Math.round(total).toLocaleString();
    document.getElementById('advanceAmount').textContent = '₹' + Math.round(advance).toLocaleString();
}

// Initial calculation if dates are pre-filled
calculatePrice();
</script>

<style>
.booking-step {
    display: none;
}

.booking-step.active {
    display: block;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    transition: all 0.3s;
}

.step-circle.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: scale(1.1);
}

.sticky-top {
    position: sticky;
}
</style>
@endsection
