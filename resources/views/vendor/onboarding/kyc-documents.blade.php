@extends('layouts.vendor')
@section('content')
<div class="container mt-5">
    <h2>Step 3: KYC Documents</h2>
    <form method="POST" action="{{ route('vendor.onboarding.kyc-documents.store') }}" enctype="multipart/form-data">
        @csrf
        <!-- Add KYC document upload fields here -->
        <div class="mb-3">
            <label for="pan_card" class="form-label">PAN Card</label>
            <input type="file" class="form-control" id="pan_card" name="pan_card" required>
        </div>
        <div class="mb-3">
            <label for="gst_certificate" class="form-label">GST Certificate</label>
            <input type="file" class="form-control" id="gst_certificate" name="gst_certificate" required>
        </div>
        <button type="submit" class="btn btn-primary">Save & Continue</button>
    </form>
</div>
@endsection
