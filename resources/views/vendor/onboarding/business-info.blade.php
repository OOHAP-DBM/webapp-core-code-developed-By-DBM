@extends('layouts.vendor')
@section('content')
<div class="container mt-5">
    <h2>Step 2: Business Information</h2>
    <form method="POST" action="{{ route('vendor.onboarding.business-info.store') }}">
        @csrf
        <!-- Add business info fields here -->
        <div class="mb-3">
            <label for="year_established" class="form-label">Year Established</label>
            <input type="number" class="form-control" id="year_established" name="year_established" required>
        </div>
        <div class="mb-3">
            <label for="total_hoardings" class="form-label">Total Hoardings</label>
            <input type="number" class="form-control" id="total_hoardings" name="total_hoardings" required>
        </div>
        <button type="submit" class="btn btn-primary">Save & Continue</button>
    </form>
</div>
@endsection
