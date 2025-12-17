@extends('layouts.vendor')
@section('content')
<div class="container mt-5">
    <h2>Step 5: Terms & Agreement</h2>
    <form method="POST" action="{{ route('vendor.onboarding.terms-agreement.store') }}">
        @csrf
        <div class="mb-3">
            <label for="terms" class="form-label">Terms & Conditions</label>
            <textarea class="form-control" id="terms" name="terms" rows="6" readonly>
                1. All information provided is true and correct.
                2. You agree to comply with OohApp's policies.
                3. ...
            </textarea>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="agree" name="agree" required>
            <label class="form-check-label" for="agree">I agree to the terms and conditions</label>
        </div>
        <button type="submit" class="btn btn-success">Complete Onboarding</button>
    </form>
</div>
@endsection
