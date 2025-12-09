@extends('layouts.customer')

@section('title', 'My Enquiries')

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Enquiries</h2>
            <p class="text-muted mb-0">Track your enquiries and quotations</p>
        </div>
        <a href="{{ route('customer.enquiries.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Enquiry
        </a>
    </div>

    <!-- Status Filter Pills -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <a href="{{ route('customer.enquiries.index') }}" 
               class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}">
                All
            </a>
            <a href="{{ route('customer.enquiries.index', ['status' => 'pending']) }}" 
               class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning text-white' : 'btn-outline-warning' }}">
                Pending
            </a>
            <a href="{{ route('customer.enquiries.index', ['status' => 'quoted']) }}" 
               class="btn btn-sm {{ request('status') === 'quoted' ? 'btn-info text-white' : 'btn-outline-info' }}">
                Quoted
            </a>
            <a href="{{ route('customer.enquiries.index', ['status' => 'accepted']) }}" 
               class="btn btn-sm {{ request('status') === 'accepted' ? 'btn-success text-white' : 'btn-outline-success' }}">
                Accepted
            </a>
            <a href="{{ route('customer.enquiries.index', ['status' => 'rejected']) }}" 
               class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger text-white' : 'btn-outline-danger' }}">
                Rejected
            </a>
        </div>
    </div>

    @if($enquiries->count() > 0)
        <!-- Enquiries List -->
        <div class="row g-4">
            @foreach($enquiries as $enquiry)
                <div class="col-12">
                    <div class="card shadow-sm border-0 h-100 hover-lift">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Hoarding Image -->
                                <div class="col-md-2">
                                    @if($enquiry->hoarding->image)
                                        <img src="{{ asset('storage/' . $enquiry->hoarding->image) }}" 
                                             alt="{{ $enquiry->hoarding->title }}" 
                                             class="img-fluid rounded" 
                                             style="height: 100px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="bg-gradient rounded d-flex align-items-center justify-content-center" 
                                             style="height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            <i class="bi bi-image text-white" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Enquiry Details -->
                                <div class="col-md-7">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('customer.enquiries.show', $enquiry->id) }}" 
                                                   class="text-decoration-none text-dark">
                                                    {{ $enquiry->hoarding->title }}
                                                </a>
                                            </h5>
                                            <p class="text-muted small mb-0">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                {{ $enquiry->hoarding->city }}, {{ $enquiry->hoarding->state }}
                                            </p>
                                        </div>
                                        <span class="badge 
                                            @if($enquiry->status === 'pending') bg-warning text-dark
                                            @elseif($enquiry->status === 'quoted') bg-info
                                            @elseif($enquiry->status === 'accepted') bg-success
                                            @elseif($enquiry->status === 'rejected') bg-danger
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst($enquiry->status) }}
                                        </span>
                                    </div>

                                    <div class="small text-muted mb-2">
                                        <div class="row g-2">
                                            <div class="col-auto">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ \Carbon\Carbon::parse($enquiry->start_date)->format('d M Y') }} - 
                                                {{ \Carbon\Carbon::parse($enquiry->end_date)->format('d M Y') }}
                                            </div>
                                            <div class="col-auto">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($enquiry->created_at)->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>

                                    @if($enquiry->message)
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-chat-left-text me-1"></i>
                                            {{ Str::limit($enquiry->message, 80) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="col-md-3 text-end">
                                    <a href="{{ route('customer.enquiries.show', $enquiry->id) }}" 
                                       class="btn btn-outline-primary btn-sm mb-2 w-100">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>

                                    @if($enquiry->status === 'quoted')
                                        <a href="{{ route('customer.quotations.show', $enquiry->quotation->id ?? '#') }}" 
                                           class="btn btn-primary btn-sm w-100">
                                            <i class="bi bi-file-text me-1"></i>View Quotation
                                        </a>
                                    @endif

                                    @if($enquiry->status === 'accepted')
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
            {{ $enquiries->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ddd;"></i>
            </div>
            <h4 class="text-muted mb-3">No Enquiries Found</h4>
            <p class="text-muted mb-4">
                @if(request('status'))
                    No {{ request('status') }} enquiries at the moment.
                @else
                    You haven't made any enquiries yet.
                @endif
            </p>
            <a href="{{ route('customer.search') }}" class="btn btn-primary">
                <i class="bi bi-search me-2"></i>Browse Hoardings
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
