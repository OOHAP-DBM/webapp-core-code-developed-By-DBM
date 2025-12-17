@extends('layouts.vendor')
@section('content')
<div class="container mt-5">
    <h2>Step 4: Bank Details</h2>
    <form method="POST" action="{{ route('vendor.onboarding.bank-details.store') }}">
        @csrf
        <!-- Add bank details fields here -->
        <div class="mb-3">
            <label for="bank_name" class="form-label">Bank Name</label>
            <input type="text" class="form-control" id="bank_name" name="bank_name" required>
        </div>
        <div class="mb-3">
            <label for="account_number" class="form-label">Account Number</label>
            <input type="text" class="form-control" id="account_number" name="account_number" required>
        </div>
        <div class="mb-3">
            <label for="ifsc_code" class="form-label">IFSC Code</label>
            <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" required>
        </div>
        <button type="submit" class="btn btn-primary">Save & Continue</button>
    </form>
</div>
@endsection
