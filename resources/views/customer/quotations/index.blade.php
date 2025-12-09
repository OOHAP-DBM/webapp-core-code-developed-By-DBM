@extends('layouts.customer')

@section('title', 'My Quotations')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="mb-4">
        <h2 class="mb-1">My Quotations</h2>
        <p class="text-muted mb-0">Review and manage your received quotations</p>
    </div>

    <!-- Status Filter Pills -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="{{ route('customer.quotations.index') }}" 
               class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                All
            </a>
            <a href="{{ route('customer.quotations.index', ['status' => 'pending']) }}" 
               class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning text-white' : 'btn-outline-warning' }}">
                Pending
            </a>
            <a href="{{ route('customer.quotations.index', ['status' => 'accepted']) }}" 
               class="btn btn-sm {{ request('status') === 'accepted' ? 'btn-success text-white' : 'btn-outline-success' }}">
                Accepted
            </a>
            <a href="{{ route('customer.quotations.index', ['status' => 'rejected']) }}" 
               class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger text-white' : 'btn-outline-danger' }}">
                Rejected
            </a>
            <a href="{{ route('customer.quotations.index', ['status' => 'expired']) }}" 
               class="btn btn-sm {{ request('status') === 'expired' ? 'btn-secondary text-white' : 'btn-outline-secondary' }}">
                Expired
            </a>
        </div>
    </div>

    @if($quotations->count() > 0)
        <!-- Quotations List -->
        <div class="row g-4">
            @foreach($quotations as $quotation)
                <div class="col-12">
                    <div class="card shadow-sm border-0 h-100 hover-lift">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Hoarding Image -->
                                <div class="col-md-2">
                                    @if($quotation->enquiry->hoarding->image)
                                        <img src="{{ asset('storage/' . $quotation->enquiry->hoarding->image) }}" 
                                             alt="{{ $quotation->enquiry->hoarding->title }}" 
                                             class="img-fluid rounded" 
                                             style="height: 100px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="bg-gradient rounded d-flex align-items-center justify-content-center" 
                                             style="height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <i class="bi bi-image text-white" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Quotation Details -->
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('customer.quotations.show', $quotation->id) }}" 
                                                   class="text-decoration-none text-dark">
                                                    {{ $quotation->enquiry->hoarding->title }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ $quotation->enquiry->hoarding->city }}, {{ $quotation->enquiry->hoarding->state }}
                                            </p>
                                        </div>
                                        <span class="badge 
                                            @if($quotation->status === 'pending') bg-warning text-dark
                                            @elseif($quotation->status === 'accepted') bg-success
                                            @elseif($quotation->status === 'rejected') bg-danger
                                            @elseif($quotation->status === 'expired') bg-secondary
                                            @else bg-info
                                            @endif">
                                            {{ ucfirst($quotation->status) }}
                                        </span>
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <div class="row g-2">
                                            <div class="col-auto">
                                                <i class="bi bi-file-text me-1"></i>
                                                Quotation #{{ $quotation->id }}
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ \Carbon\Carbon::parse($quotation->created_at)->format('d M Y') }}
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-hourglass-split me-1"></i>
                                                Valid until {{ \Carbon\Carbon::parse($quotation->valid_until)->format('d M Y') }}
                                            </div>
                                        </div>
                                    </div>

                                    @if($quotation->notes)
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-chat-left-text me-1"></i>
                                            {{ Str::limit($quotation->notes, 80) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Amount & Actions -->
                                <div class="col-md-4 text-end">
                                    <h3 class="text-primary mb-2">₹{{ number_format($quotation->total_amount, 2) }}</h3>
                                    <p class="text-muted small mb-3">Total Amount</p>

                                    <a href="{{ route('customer.quotations.show', $quotation->id) }}" 
                                       class="btn btn-outline-primary btn-sm mb-2 w-100">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>

                                    @if($quotation->status === 'pending')
                                        @php
                                            $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($quotation->valid_until), false);
                                        @endphp
                                        @if($daysLeft > 0)
                                            <form action="{{ route('customer.quotations.accept', $quotation->id) }}" method="POST" class="d-inline w-100">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm w-100" 
                                                        onclick="return confirm('Accept this quotation and proceed to booking?')">
                                                    <i class="bi bi-check-circle me-1"></i>Accept
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-secondary w-100 py-2">Expired</span>
                                        @endif
                                    @elseif($quotation->status === 'accepted')
                                        <span class="badge bg-success-subtle text-success w-100 py-2">
                                            <i class="bi bi-check-circle me-1"></i>Booking Created
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $quotations->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-file-text" style="font-size: 4rem; color: #ddd;"></i>
            </div>
            <h4 class="text-muted mb-3">No Quotations Found</h4>
            <p class="text-muted mb-4">
                @if(request('status'))
                    No {{ request('status') }} quotations at the moment.
                @else
                    You haven't received any quotations yet. Start by making an enquiry.
                @endif
            </p>
            <a href="{{ route('customer.enquiries.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Create Enquiry
            </a>
        </div>
    @endif
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
@endsection
