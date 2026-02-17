@extends('layouts.vendor')

@section('content')
<div class="container mt-4">
    <h2>Direct Enquiry Details</h2>
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Client: {{ $enquiry->name }}</h5>
            <p><strong>Email:</strong> {{ $enquiry->email }}</p>
            <p><strong>Phone:</strong> {{ $enquiry->formatted_phone ?? $enquiry->phone }}</p>
            <p><strong>City:</strong> {{ $enquiry->location_city }}</p>
            <p><strong>Hoarding Type:</strong> {{ $enquiry->hoarding_type }}</p>
            <p><strong>Preferred Locations:</strong> {{ is_array($enquiry->preferred_locations) ? implode(', ', $enquiry->preferred_locations) : $enquiry->preferred_locations }}</p>
            <p><strong>Remarks:</strong> {{ $enquiry->remarks }}</p>
            <p><strong>Status:</strong> {{ ucfirst($enquiry->status) }}</p>
            <a href="{{ route('vendor.enquiries.index') }}" class="btn btn-secondary mt-3">Back to Enquiries</a>
        </div>
    </div>
</div>
@endsection
