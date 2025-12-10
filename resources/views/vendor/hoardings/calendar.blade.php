@extends('layouts.vendor')

@section('page-title', 'Hoarding Availability Calendar - ' . $hoarding->title)

@section('content')
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">{{ $hoarding->title }}</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-geo-alt"></i> {{ $hoarding->address }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendor.hoardings.show', $hoarding->id) }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to Hoarding
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="refreshCalendar()">
                <i class="bi bi-arrow-clockwise me-2"></i>Refresh
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4" id="statsCards">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Total Bookings</div>
                        <h4 class="mb-0" id="stat-total">-</h4>
                    </div>
                    <div class="stat-icon-sm" style="background: #dbeafe; color: #3b82f6;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Active Now</div>
                        <h4 class="mb-0" id="stat-active">-</h4>
                    </div>
                    <div class="stat-icon-sm" style="background: #d1fae5; color: #10b981;">
                        <i class="bi bi-play-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Enquiries</div>
                        <h4 class="mb-0" id="stat-enquiries">-</h4>
                    </div>
                    <div class="stat-icon-sm" style="background: #fef3c7; color: #f59e0b;">
                        <i class="bi bi-inbox"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">This Month</div>
                        <h4 class="mb-0" id="stat-month">-</h4>
                    </div>
                    <div class="stat-icon-sm" style="background: #e0e7ff; color: #6366f1;">
                        <i class="bi bi-calendar3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Revenue</div>
                        <h5 class="mb-0" id="stat-revenue">-</h5>
                    </div>
                    <div class="stat-icon-sm" style="background: #d1fae5; color: #059669;">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small mb-1">Occupancy</div>
                        <h4 class="mb-0" id="stat-occupancy">-</h4>
                    </div>
                    <div class="stat-icon-sm" style="background: #dbeafe; color: #3b82f6;">
                        <i class="bi bi-pie-chart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-4">
            <h6 class="mb-0">Legend:</h6>
            <div class="d-flex align-items-center gap-2">
                <div class="legend-box" style="background-color: #10b981;"></div>
                <span class="small">Available</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="legend-box" style="background-color: #fbbf24;"></div>
                <span class="small">Enquiry</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="legend-box" style="background-color: #f59e0b;"></div>
                <span class="small">Payment Hold</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="legend-box" style="background-color: #ea580c;"></div>
                <span class="small">Payment Pending</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="legend-box" style="background-color: #dc2626;"></div>
                <span class="small">Confirmed Booking</span>
            </div>
        </div>
    </div>
</div>

<!-- Calendar -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="eventViewDetailsBtn" class="btn btn-primary" style="display: none;">View Details</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<style>
    .stat-icon-sm {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .legend-box {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        border: 1px solid rgba(0,0,0,0.1);
    }
    
    #calendar {
        min-height: 600px;
    }
    
    .fc {
        font-family: inherit;
    }
    
    .fc-event {
        cursor: pointer;
        border: none;
    }
    
    .fc-event:hover {
        opacity: 0.85;
    }
    
    .fc-daygrid-event {
        white-space: normal;
        align-items: start;
        padding: 2px 4px;
    }
    
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 600;
    }
    
    .fc-button {
        text-transform: none !important;
        font-weight: 500 !important;
    }
    
    .fc-button-primary {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }
    
    .fc-button-primary:hover {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
    }
    
    .fc-button-primary:not(:disabled):active,
    .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #1d4ed8 !important;
        border-color: #1d4ed8 !important;
    }
    
    .fc-day-today {
        background-color: #eff6ff !important;
    }
    
    .event-detail-row {
        padding: 8px 0;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .event-detail-row:last-child {
        border-bottom: none;
    }
    
    .event-detail-label {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .event-detail-value {
        font-size: 0.95rem;
        color: #111827;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
    let calendar;
    const hoardingId = {{ $hoarding->id }};
    const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    
    document.addEventListener('DOMContentLoaded', function() {
        initializeCalendar();
        loadStats();
    });
    
    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                list: 'List'
            },
            height: 'auto',
            events: function(info, successCallback, failureCallback) {
                fetch(`/vendor/hoarding/${hoardingId}/calendar/data?start=${info.startStr}&end=${info.endStr}`)
                    .then(response => response.json())
                    .then(data => {
                        successCallback(data);
                    })
                    .catch(error => {
                        console.error('Error loading calendar data:', error);
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                showEventDetails(info.event);
            },
            eventDidMount: function(info) {
                // Add tooltip
                if (info.event.extendedProps.type === 'available') {
                    info.el.title = 'Available for booking';
                } else if (info.event.extendedProps.type === 'booking') {
                    info.el.title = `Booking: ${info.event.extendedProps.customerName}`;
                } else if (info.event.extendedProps.type === 'enquiry') {
                    info.el.title = `Enquiry: ${info.event.extendedProps.customerName}`;
                }
            },
            datesSet: function() {
                // Reload stats when month changes
                loadStats();
            }
        });
        
        calendar.render();
    }
    
    function showEventDetails(event) {
        const props = event.extendedProps;
        
        // Don't show modal for available dates
        if (props.type === 'available') {
            return;
        }
        
        let title = '';
        let content = '';
        let detailsLink = '';
        
        if (props.type === 'booking') {
            title = '<i class="bi bi-calendar-check text-danger me-2"></i>Booking Details';
            content = `
                <div class="event-detail-row">
                    <div class="event-detail-label">Booking ID</div>
                    <div class="event-detail-value">#${props.bookingId}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Customer</div>
                    <div class="event-detail-value">${props.customerName}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Phone</div>
                    <div class="event-detail-value">${props.customerPhone}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Duration</div>
                    <div class="event-detail-value">${event.startStr} to ${event.endStr}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Period</div>
                    <div class="event-detail-value">${props.duration}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Amount</div>
                    <div class="event-detail-value fw-bold">${props.amount}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Status</div>
                    <div class="event-detail-value"><span class="badge bg-danger">${props.status}</span></div>
                </div>
            `;
            detailsLink = `/vendor/bookings/${props.bookingId}`;
        } else if (props.type === 'enquiry') {
            title = '<i class="bi bi-inbox text-warning me-2"></i>Enquiry Details';
            content = `
                <div class="event-detail-row">
                    <div class="event-detail-label">Enquiry ID</div>
                    <div class="event-detail-value">#${props.enquiryId}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Customer</div>
                    <div class="event-detail-value">${props.customerName}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Phone</div>
                    <div class="event-detail-value">${props.customerPhone}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Preferred Duration</div>
                    <div class="event-detail-value">${event.startStr} to ${event.endStr}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Period</div>
                    <div class="event-detail-value">${props.duration}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Message</div>
                    <div class="event-detail-value">${props.message}</div>
                </div>
                <div class="event-detail-row">
                    <div class="event-detail-label">Status</div>
                    <div class="event-detail-value"><span class="badge bg-warning">${props.status}</span></div>
                </div>
            `;
            detailsLink = `/vendor/enquiries/${props.enquiryId}`;
        }
        
        document.getElementById('eventModalTitle').innerHTML = title;
        document.getElementById('eventModalBody').innerHTML = content;
        
        const detailsBtn = document.getElementById('eventViewDetailsBtn');
        if (detailsLink) {
            detailsBtn.href = detailsLink;
            detailsBtn.style.display = 'inline-block';
        } else {
            detailsBtn.style.display = 'none';
        }
        
        eventModal.show();
    }
    
    function loadStats() {
        fetch(`/vendor/hoarding/${hoardingId}/calendar/stats`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('stat-total').textContent = data.total_bookings;
                document.getElementById('stat-active').textContent = data.active_bookings;
                document.getElementById('stat-enquiries').textContent = data.pending_enquiries;
                document.getElementById('stat-month').textContent = data.current_month_bookings;
                document.getElementById('stat-revenue').textContent = '₹' + (data.current_month_revenue / 100000).toFixed(2) + 'L';
                document.getElementById('stat-occupancy').textContent = data.occupancy_rate + '%';
            })
            .catch(error => {
                console.error('Error loading stats:', error);
            });
    }
    
    function refreshCalendar() {
        if (calendar) {
            calendar.refetchEvents();
            loadStats();
        }
    }
</script>
@endpush
