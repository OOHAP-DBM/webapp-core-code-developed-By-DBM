@extends('layouts.app')

@section('title', 'Create DOOH Schedule')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('customer.dooh.schedules.index') }}">DOOH Schedules</a></li>
                    <li class="breadcrumb-item active">Create Schedule</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-calendar-plus"></i> DOOH Schedule Planner</h4>
                    <small>Define playtime slots, frequency, and date range for your creative</small>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('customer.dooh.schedules.store') }}" method="POST" id="scheduleForm">
                        @csrf

                        {{-- Creative Selection --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-film"></i> Select Creative</h5>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Creative *</label>
                                    <select class="form-select @error('creative_id') is-invalid @enderror" 
                                            name="creative_id" id="creative_id" required>
                                        <option value="">-- Select Creative --</option>
                                        @foreach($creatives as $item)
                                            <option value="{{ $item->id }}" 
                                                    data-type="{{ $item->creative_type }}"
                                                    data-duration="{{ $item->duration_seconds }}"
                                                    {{ old('creative_id', $creative?->id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->creative_name }} 
                                                ({{ ucfirst($item->creative_type) }}, 
                                                @if($item->isVideo()) {{ $item->duration_seconds }}s @else Image @endif)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('creative_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">DOOH Screen *</label>
                                    <select class="form-select @error('dooh_screen_id') is-invalid @enderror" 
                                            name="dooh_screen_id" id="dooh_screen_id" required>
                                        <option value="">-- Select Screen --</option>
                                        @foreach($screens as $item)
                                            <option value="{{ $item->id }}" 
                                                    data-loop-duration="{{ $item->loop_duration_seconds }}"
                                                    data-slot-duration="{{ $item->slot_duration_seconds }}"
                                                    {{ old('dooh_screen_id', $screen?->id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name }} - {{ $item->city }}
                                                ({{ $item->resolution }}, {{ $item->slot_duration_seconds }}s slots)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('dooh_screen_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Schedule Details --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-info-circle"></i> Schedule Details</h5>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Schedule Name *</label>
                                    <input type="text" class="form-control @error('schedule_name') is-invalid @enderror" 
                                           name="schedule_name" value="{{ old('schedule_name') }}" 
                                           placeholder="e.g., Summer Campaign 2025" required>
                                    @error('schedule_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="2" 
                                              placeholder="Optional campaign notes">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Date Range --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-calendar-range"></i> Schedule Period</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Start Date *</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           name="start_date" id="start_date" 
                                           value="{{ old('start_date', now()->addDays(1)->format('Y-m-d')) }}" 
                                           min="{{ now()->format('Y-m-d') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">End Date *</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           name="end_date" id="end_date" 
                                           value="{{ old('end_date', now()->addDays(30)->format('Y-m-d')) }}" 
                                           min="{{ now()->addDays(1)->format('Y-m-d') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Total Days: <span id="total_days">-</span></small>
                                </div>
                            </div>
                        </div>

                        {{-- Time Slots --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-clock"></i> Playtime Slots</h5>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="slot_mode" 
                                       id="slot_mode_24_7" value="24_7" checked>
                                <label class="form-check-label" for="slot_mode_24_7">
                                    <strong>24/7 Playback</strong> - Display throughout the day
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="slot_mode" 
                                       id="slot_mode_daily_range" value="daily_range">
                                <label class="form-check-label" for="slot_mode_daily_range">
                                    <strong>Daily Time Range</strong> - Specific hours each day
                                </label>
                            </div>

                            <div id="daily_range_inputs" class="ms-4 mb-3" style="display:none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Start Time</label>
                                        <input type="time" class="form-control" name="daily_start_time" 
                                               value="08:00">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">End Time</label>
                                        <input type="time" class="form-control" name="daily_end_time" 
                                               value="20:00">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="slot_mode" 
                                       id="slot_mode_custom" value="custom">
                                <label class="form-check-label" for="slot_mode_custom">
                                    <strong>Custom Time Slots</strong> - Define specific time windows
                                </label>
                            </div>

                            <div id="custom_slots_container" style="display:none;" class="ms-4">
                                <div id="time_slots_wrapper">
                                    <!-- Dynamic time slots will be added here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add_time_slot">
                                    <i class="bi bi-plus-circle"></i> Add Time Slot
                                </button>
                            </div>
                        </div>

                        {{-- Loop Frequency --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-repeat"></i> Loop Frequency</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Displays Per Hour *</label>
                                    <input type="number" class="form-control @error('displays_per_hour') is-invalid @enderror" 
                                           name="displays_per_hour" id="displays_per_hour" 
                                           value="{{ old('displays_per_hour', 12) }}" 
                                           min="1" max="60" required>
                                    <small class="text-muted">How often your ad plays (1-60 times/hour)</small>
                                    @error('displays_per_hour')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Priority (1-10)</label>
                                    <input type="number" class="form-control" name="priority" 
                                           value="{{ old('priority', 5) }}" min="1" max="10">
                                    <small class="text-muted">Higher priority = more frequent display</small>
                                </div>
                            </div>
                        </div>

                        {{-- Active Days (Recurring) --}}
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2"><i class="bi bi-calendar-week"></i> Days of Week</h5>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="all_days" checked>
                                <label class="form-check-label" for="all_days">
                                    <strong>All Days</strong>
                                </label>
                            </div>
                            <div id="specific_days" style="display:none;">
                                <div class="row">
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $index => $day)
                                        <div class="col-md-3 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input day-checkbox" type="checkbox" 
                                                       name="active_days[]" value="{{ $index + 1 }}" 
                                                       id="day_{{ $index }}">
                                                <label class="form-check-label" for="day_{{ $index }}">
                                                    {{ $day }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-4">
                            <label class="form-label">Customer Notes</label>
                            <textarea class="form-control" name="customer_notes" rows="3" 
                                      placeholder="Any special instructions or notes for the schedule">{{ old('customer_notes') }}</textarea>
                        </div>

                        {{-- Availability Check --}}
                        <div class="mb-4">
                            <button type="button" class="btn btn-warning" id="check_availability_btn">
                                <i class="bi bi-search"></i> Check Availability
                            </button>
                            <div id="availability_result" class="mt-3" style="display:none;"></div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('customer.dooh.schedules.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit_btn">
                                <i class="bi bi-check-circle"></i> Create Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Summary Sidebar --}}
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Schedule Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Total Days</small>
                        <h4 id="summary_days">-</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Displays Per Day</small>
                        <h4 id="summary_displays_per_day">-</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Total Displays</small>
                        <h3 class="text-primary" id="summary_total_displays">-</h3>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Estimated Cost</small>
                        <h3 class="text-success" id="summary_total_cost">₹ -</h3>
                    </div>
                    <small class="text-muted"><i class="bi bi-info-circle"></i> Subject to availability confirmation</small>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Higher frequency = more displays but higher cost</li>
                        <li>Custom time slots let you target peak hours</li>
                        <li>Check availability before submitting</li>
                        <li>Schedules require admin approval</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let timeSlotCounter = 0;

    // Calculate days
    function calculateDays() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
            
            if (days > 0) {
                $('#total_days, #summary_days').text(days);
                calculateSummary();
            }
        }
    }

    // Calculate summary
    function calculateSummary() {
        const days = parseInt($('#summary_days').text()) || 0;
        const displaysPerHour = parseInt($('#displays_per_hour').val()) || 12;
        
        // Calculate displays per day (24 hours or based on time range)
        let hoursPerDay = 24;
        const slotMode = $('input[name="slot_mode"]:checked').val();
        
        if (slotMode === 'daily_range') {
            const startTime = $('input[name="daily_start_time"]').val();
            const endTime = $('input[name="daily_end_time"]').val();
            if (startTime && endTime) {
                const start = new Date('2000-01-01 ' + startTime);
                const end = new Date('2000-01-01 ' + endTime);
                hoursPerDay = (end - start) / (1000 * 60 * 60);
            }
        }
        
        const displaysPerDay = Math.round(displaysPerHour * hoursPerDay);
        const totalDisplays = displaysPerDay * days;
        const costPerDisplay = 2.50; // Example rate
        const totalCost = totalDisplays * costPerDisplay;
        
        $('#summary_displays_per_day').text(displaysPerDay.toLocaleString());
        $('#summary_total_displays').text(totalDisplays.toLocaleString());
        $('#summary_total_cost').text('₹ ' + totalCost.toLocaleString('en-IN', {minimumFractionDigits: 2}));
    }

    // Event handlers
    $('#start_date, #end_date').change(calculateDays);
    $('#displays_per_hour').on('input', calculateSummary);

    // Slot mode handling
    $('input[name="slot_mode"]').change(function() {
        $('#daily_range_inputs, #custom_slots_container').hide();
        
        if ($(this).val() === 'daily_range') {
            $('#daily_range_inputs').show();
        } else if ($(this).val() === 'custom') {
            $('#custom_slots_container').show();
        }
        
        calculateSummary();
    });

    // All days checkbox
    $('#all_days').change(function() {
        if ($(this).is(':checked')) {
            $('#specific_days').hide();
            $('.day-checkbox').prop('checked', false);
        } else {
            $('#specific_days').show();
        }
    });

    // Add time slot
    $('#add_time_slot').click(function() {
        timeSlotCounter++;
        const html = `
            <div class="row mb-2 time-slot" id="slot_${timeSlotCounter}">
                <div class="col-5">
                    <input type="time" class="form-control" name="time_slots[${timeSlotCounter}][start_time]" required>
                </div>
                <div class="col-5">
                    <input type="time" class="form-control" name="time_slots[${timeSlotCounter}][end_time]" required>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-sm btn-danger remove-slot" data-slot="${timeSlotCounter}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#time_slots_wrapper').append(html);
    });

    // Remove time slot
    $(document).on('click', '.remove-slot', function() {
        const slotId = $(this).data('slot');
        $(`#slot_${slotId}`).remove();
    });

    // Check availability
    $('#check_availability_btn').click(function() {
        const formData = $('#scheduleForm').serialize();
        
        $('#check_availability_btn').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Checking...');
        
        $.post('{{ route("customer.dooh.check-availability") }}', formData, function(response) {
            if (response.success && response.availability.available) {
                $('#availability_result').html(`
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <strong>Available!</strong> ${response.availability.message}
                        ${response.availability.warnings.length > 0 ? `<br><small>${response.availability.warnings.length} minor conflicts detected</small>` : ''}
                    </div>
                `).show();
            } else {
                let conflictsHtml = '<ul class="mb-0">';
                response.availability.conflicts.forEach(function(conflict) {
                    conflictsHtml += `<li>${conflict.type}: ${conflict.message || 'Conflict detected'}</li>`;
                });
                conflictsHtml += '</ul>';
                
                $('#availability_result').html(`
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Conflicts Found</strong>
                        ${conflictsHtml}
                    </div>
                `).show();
            }
        }).fail(function() {
            $('#availability_result').html(`
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle"></i> Failed to check availability. Please try again.
                </div>
            `).show();
        }).always(function() {
            $('#check_availability_btn').prop('disabled', false).html('<i class="bi bi-search"></i> Check Availability');
        });
    });

    // Initial calculation
    calculateDays();
});
</script>
@endpush
@endsection
