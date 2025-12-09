{{-- 
    Order Card Component
    Props: $booking
--}}
@props(['booking'])

<div class="order-card mb-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <!-- Order Image & Details -->
                <div class="col-md-8">
                    <div class="d-flex gap-3">
                        <!-- Thumbnail -->
                        <div class="flex-shrink-0">
                            @if($booking->hoarding && $booking->hoarding->primary_image)
                                <img 
                                    src="{{ asset('storage/' . $booking->hoarding->primary_image) }}" 
                                    alt="{{ $booking->hoarding->title }}" 
                                    class="rounded"
                                    style="width: 100px; height: 100px; object-fit: cover;"
                                >
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Details -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-1">
                                        <a href="{{ route('customer.bookings.show', $booking->id) }}" class="text-decoration-none text-dark">
                                            {{ $booking->hoarding ? $booking->hoarding->title : 'Booking #' . $booking->id }}
                                        </a>
                                    </h5>
                                    <p class="text-muted mb-0 small">
                                        Booking ID: <strong>#{{ $booking->booking_number }}</strong>
                                    </p>
                                </div>
                                
                                <!-- Status Badge -->
                                <span class="badge 
                                    @switch($booking->status)
                                        @case('confirmed') bg-success @break
                                        @case('pending') bg-warning @break
                                        @case('cancelled') bg-danger @break
                                        @case('completed') bg-info @break
                                        @default bg-secondary
                                    @endswitch
                                ">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </div>

                            <!-- Booking Info -->
                            <div class="row g-2 mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Start Date</small>
                                    <p class="mb-0 fw-semibold">{{ \Carbon\Carbon::parse($booking->start_date)->format('d M Y') }}</p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">End Date</small>
                                    <p class="mb-0 fw-semibold">{{ \Carbon\Carbon::parse($booking->end_date)->format('d M Y') }}</p>
                                </div>
                            </div>

                            @if($booking->hoarding)
                            <p class="text-muted mb-0 mt-2 small">
                                <i class="bi bi-geo-alt"></i> {{ $booking->hoarding->city }}, {{ $booking->hoarding->state }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Price & Actions -->
                <div class="col-md-4 border-start d-flex flex-column justify-content-between">
                    <div>
                        <p class="text-muted small mb-1">Total Amount</p>
                        <h4 class="text-primary mb-0">₹{{ number_format($booking->total_amount, 2) }}</h4>
                        
                        @if($booking->payment_status === 'paid')
                            <span class="badge bg-success-subtle text-success mt-1">
                                <i class="bi bi-check-circle"></i> Paid
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning mt-1">
                                <i class="bi bi-clock"></i> Payment Pending
                            </span>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-3">
                        <a href="{{ route('customer.bookings.show', $booking->id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        
                        @if($booking->status === 'pending' && $booking->payment_status !== 'paid')
                            <a href="{{ route('customer.payments.show', $booking->id) }}" class="btn btn-primary btn-sm w-100 mb-2">
                                <i class="bi bi-credit-card"></i> Complete Payment
                            </a>
                        @endif

                        @if($booking->status === 'confirmed')
                            <button class="btn btn-outline-secondary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#threadModal-{{ $booking->id }}">
                                <i class="bi bi-chat-dots"></i> Message Vendor
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Progress Timeline (for confirmed bookings) -->
            @if($booking->status === 'confirmed' || $booking->status === 'completed')
            <div class="mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Booking Progress</span>
                    <span class="text-muted small">
                        {{ \Carbon\Carbon::parse($booking->start_date)->diffInDays(now(), false) > 0 ? 'In Progress' : 'Upcoming' }}
                    </span>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    @php
                        $start = \Carbon\Carbon::parse($booking->start_date);
                        $end = \Carbon\Carbon::parse($booking->end_date);
                        $total = $start->diffInDays($end);
                        $elapsed = max(0, min($total, $start->diffInDays(now(), false)));
                        $progress = $total > 0 ? ($elapsed / $total) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.order-card .card {
    transition: box-shadow 0.3s ease;
}

.order-card .card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}
</style>
