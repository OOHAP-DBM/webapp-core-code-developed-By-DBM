@extends('layouts.admin')

@section('title', 'DOOH Slot Booking - ' . $hoarding->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>DOOH Slot Booking</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.hoardings.index') }}">Hoardings</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.hoardings.show', $hoarding->id) }}">{{ $hoarding->title }}</a></li>
                            <li class="breadcrumb-item active">DOOH Booking</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.hoarding.dooh-slots.index', $hoarding->id) }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View All Slots
                </a>
            </div>
        </div>
    </div>

    <!-- Hoarding Info Card -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-tv"></i> {{ $hoarding->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Address:</strong> {{ $hoarding->address }}</p>
                            <p><strong>Type:</strong> <span class="badge badge-info">{{ strtoupper($hoarding->hoarding_type ?? 'DOOH') }}</span></p>
                            <p><strong>Status:</strong> <span class="badge badge-success">{{ ucfirst($hoarding->status) }}</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Daily Displays:</strong> {{ number_format($stats['total_daily_displays']) }}</p>
                            <p><strong>Monthly Revenue Potential:</strong> ₹{{ number_format($stats['total_monthly_revenue_potential'], 2) }}</p>
                            <p><strong>Occupancy Rate:</strong> {{ $stats['occupancy_rate'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Slot Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Slots:</span>
                            <strong>{{ $stats['total_slots'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-success">Available:</span>
                            <strong class="text-success">{{ $stats['available'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-primary">Booked:</span>
                            <strong class="text-primary">{{ $stats['booked'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-warning">Blocked:</span>
                            <strong class="text-warning">{{ $stats['blocked'] }}</strong>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="d-flex justify-content-between">
                            <span class="text-danger">Maintenance:</span>
                            <strong class="text-danger">{{ $stats['maintenance'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Calculator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-calculator"></i> Booking Cost Calculator</h4>
                </div>
                <div class="card-body">
                    <form id="bookingCalculatorForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Time (Optional)</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Time (Optional)</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="checkAvailability()">
                            <i class="fas fa-search"></i> Check Availability
                        </button>
                        <button type="button" class="btn btn-success" onclick="calculateCost()" id="calculateBtn" disabled>
                            <i class="fas fa-calculator"></i> Calculate Cost
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Slots -->
    <div class="row" id="slotsContainer" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-clock"></i> Available Time Slots</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Select slots you want to book and click "Calculate Cost" to see pricing details.
                    </div>
                    <div id="availableSlotsList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown -->
    <div class="row mt-4" id="costBreakdownContainer" style="display: none;">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Cost Breakdown</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="costBreakdownTable">
                        <thead>
                            <tr>
                                <th>Slot</th>
                                <th>Time Range</th>
                                <th>Daily Displays</th>
                                <th>Total Displays</th>
                                <th>Cost Per Display</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody id="costBreakdownBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="fas fa-chart-line"></i> Total Summary</h4>
                </div>
                <div class="card-body">
                    <div class="summary-item mb-3">
                        <h5>Total Cost</h5>
                        <h2 class="text-success" id="totalCost">₹0.00</h2>
                    </div>
                    <div class="summary-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Total Displays:</span>
                            <strong id="totalDisplays">0</strong>
                        </div>
                    </div>
                    <div class="summary-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Cost Per Display:</span>
                            <strong id="costPerDisplay">₹0.00</strong>
                        </div>
                    </div>
                    <div class="summary-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>CPM (Cost Per 1000):</span>
                            <strong id="cpm">₹0.00</strong>
                        </div>
                    </div>
                    <hr>
                    <button class="btn btn-success btn-block btn-lg" onclick="proceedToBooking()">
                        <i class="fas fa-check"></i> Proceed to Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Looping & Frequency Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-sync"></i> Looping Logic & Display Frequency</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>How DOOH Slot Rendering Works</h5>
                            <ul>
                                <li><strong>Slot Duration:</strong> Time period when your ad will be displayed (e.g., 8 AM - 8 PM)</li>
                                <li><strong>Display Duration:</strong> How long each ad shows (typically 10 seconds)</li>
                                <li><strong>Frequency:</strong> Number of times your ad displays per hour</li>
                                <li><strong>Interval:</strong> Time gap between consecutive displays</li>
                                <li><strong>Loop Position:</strong> Your ad's position in the rotation queue</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Example Calculation</h5>
                            <div class="bg-light p-3 rounded">
                                <p><strong>Slot:</strong> 8 AM to 8 PM (12 hours)</p>
                                <p><strong>Frequency:</strong> 6 times per hour</p>
                                <p><strong>Interval:</strong> Every 10 minutes (3600 / 6)</p>
                                <p><strong>Daily Displays:</strong> 6 × 12 = 72 displays/day</p>
                                <p><strong>Monthly Displays:</strong> 72 × 30 = 2,160 displays/month</p>
                                <p><strong>If Cost Per Display:</strong> ₹2.00</p>
                                <p><strong>Monthly Cost:</strong> ₹2.00 × 2,160 = ₹4,320.00</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.slot-card {
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.slot-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.slot-card.selected {
    border-color: #28a745;
    background-color: #f0fff4;
}

.slot-checkbox {
    transform: scale(1.5);
    margin-right: 10px;
}

.stat-item {
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.summary-item h2 {
    font-size: 2.5rem;
    font-weight: bold;
}
</style>

<script>
let availableSlots = [];
let selectedSlotIds = [];

function checkAvailability() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;

    if (!startDate || !endDate) {
        alert('Please select start and end dates');
        return;
    }

    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate
    });

    if (startTime) params.append('start_time', startTime);
    if (endTime) params.append('end_time', endTime);

    fetch(`{{ route('admin.hoarding.dooh-slots.availability', $hoarding->id) }}?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                availableSlots = data.availability.available;
                displayAvailableSlots();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error checking availability');
        });
}

function displayAvailableSlots() {
    const container = document.getElementById('availableSlotsList');
    const slotsContainer = document.getElementById('slotsContainer');

    if (availableSlots.length === 0) {
        container.innerHTML = '<div class="alert alert-warning">No available slots found for the selected period.</div>';
        slotsContainer.style.display = 'block';
        return;
    }

    let html = '<div class="row">';
    availableSlots.forEach(slot => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="slot-card" id="slot-${slot.id}">
                    <div class="form-check">
                        <input class="form-check-input slot-checkbox" type="checkbox" value="${slot.id}" 
                               id="checkbox-${slot.id}" onchange="toggleSlot(${slot.id})">
                        <label class="form-check-label" for="checkbox-${slot.id}">
                            <h5 class="mb-2">${slot.slot_name || 'Slot #' + slot.id}</h5>
                        </label>
                    </div>
                    <div class="ml-4">
                        <p class="mb-1"><strong>Time:</strong> ${slot.formatted_start_time} - ${slot.formatted_end_time}</p>
                        <p class="mb-1"><strong>Frequency:</strong> ${slot.frequency_per_hour} times/hour (Every ${(slot.interval_seconds / 60).toFixed(1)} min)</p>
                        <p class="mb-1"><strong>Daily Displays:</strong> ${slot.total_daily_displays.toLocaleString()}</p>
                        <p class="mb-1"><strong>Duration:</strong> ${slot.duration_seconds} seconds per display</p>
                        <p class="mb-1"><strong>Daily Cost:</strong> ₹${parseFloat(slot.daily_cost).toLocaleString('en-IN', {minimumFractionDigits: 2})}</p>
                        ${slot.is_prime_time ? '<span class="badge badge-warning">Prime Time</span>' : ''}
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';

    container.innerHTML = html;
    slotsContainer.style.display = 'block';
    document.getElementById('calculateBtn').disabled = true;
}

function toggleSlot(slotId) {
    const checkbox = document.getElementById(`checkbox-${slotId}`);
    const slotCard = document.getElementById(`slot-${slotId}`);

    if (checkbox.checked) {
        selectedSlotIds.push(slotId);
        slotCard.classList.add('selected');
    } else {
        selectedSlotIds = selectedSlotIds.filter(id => id !== slotId);
        slotCard.classList.remove('selected');
    }

    document.getElementById('calculateBtn').disabled = selectedSlotIds.length === 0;
}

function calculateCost() {
    if (selectedSlotIds.length === 0) {
        alert('Please select at least one slot');
        return;
    }

    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    fetch('{{ route("admin.dooh.calculate-cost") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            slot_ids: selectedSlotIds,
            start_date: startDate,
            end_date: endDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCostBreakdown(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error calculating cost');
    });
}

function displayCostBreakdown(data) {
    const tbody = document.getElementById('costBreakdownBody');
    let html = '';

    data.slot_details.forEach(detail => {
        html += `
            <tr>
                <td>${detail.slot_name}</td>
                <td>${detail.time_range}</td>
                <td>${detail.cost_details.total_displays / detail.cost_details.total_days}</td>
                <td>${detail.cost_details.total_displays.toLocaleString()}</td>
                <td>₹${parseFloat(detail.cost_details.cost_per_display).toFixed(4)}</td>
                <td>₹${parseFloat(detail.cost_details.total_cost).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    document.getElementById('totalCost').textContent = '₹' + parseFloat(data.total_cost).toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('totalDisplays').textContent = data.total_displays.toLocaleString();
    document.getElementById('costPerDisplay').textContent = '₹' + parseFloat(data.cost_per_display).toFixed(4);
    document.getElementById('cpm').textContent = '₹' + parseFloat(data.cpm).toLocaleString('en-IN', {minimumFractionDigits: 2});

    document.getElementById('costBreakdownContainer').style.display = 'flex';
}

function proceedToBooking() {
    alert('Booking feature will be integrated with the booking workflow.\n\nSelected Slots: ' + selectedSlotIds.join(', '));
    // TODO: Redirect to booking creation with selected slots
}

// Set today as minimum date
document.getElementById('start_date').min = new Date().toISOString().split('T')[0];
document.getElementById('end_date').min = new Date().toISOString().split('T')[0];
</script>
@endsection
