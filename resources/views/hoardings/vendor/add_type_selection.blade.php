{{-- 
    Add Hoarding Type Selection (Vendor)
    Architectural rules strictly enforced:
    - Only OOH handled here
    - DOOH delegated to DOOH module
    - Pixel-perfect Figma match
--}}
@extends('layouts.vendor')

@section('content')
<div class="container add-hoarding-type-selection">
    <div class="header">
        <img src="/images/hoarding_type_illustration.svg" alt="Hoarding Illustration" />
        <h2>Start Listing Your Hoarding</h2>
        <p>Select the hoarding type which you want to list</p>
    </div>

    <form method="POST" action="{{ route('vendor.hoardings.select-type') }}">
        @csrf

        <div class="card-row">
            <label class="card-option">
                <input type="radio" name="hoarding_type" value="OOH" required>
                <div class="card">
                    <span class="title">OOH Hoarding</span><br>
                    <span class="sub">(Out-of-Home)</span>
                </div>
            </label>

            <label class="card-option">
                <input type="radio" name="hoarding_type" value="DOOH" required>
                <div class="card">
                    <span class="title">DOOH Hoarding</span><br>
                    <span class="sub">(Digital Out-of-Home)</span>
                </div>
            </label>
        </div>

        <button type="submit" class="continue-btn">
            Continue
        </button>
    </form>
</div>

<style>
/* Layout */
.add-hoarding-type-selection {
    max-width: 480px;
    margin: 0 auto;
    padding-top: 80px;
}

.header {
    text-align: center;
}

.header img {
    width: 120px;
    margin-bottom: 32px;
}

.header h2 {
    font-weight: 600;
    font-size: 1.5rem;
    margin-bottom: 8px;
}

.header p {
    color: var(--gray-text);
    font-size: 1rem;
    margin-bottom: 32px;
}

/* Cards */
.card-row {
    display: flex;
    gap: 24px;
    justify-content: center;
    margin-bottom: 32px;
}

.card-option {
    flex: 1;
    cursor: pointer;
}

.card-option input {
    display: none;
}

.card {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 24px 0;
    text-align: center;
    background: var(--white);
    transition: box-shadow .2s, border-color .2s;
}

.card .title {
    font-weight: 500;
    font-size: 1.1rem;
}

.card .sub {
    color: var(--gray-text);
    font-size: .95rem;
}

/* Hover */
.card-option .card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}

/* Selected */
.card-option input:checked + .card {
    border-color: var(--btn-color);
    box-shadow: 0 0 0 2px rgba(34,197,94,.25);
}

/* Continue Button */
.continue-btn {
    width: 100%;
    height: 48px;
    font-size: 1.1rem;
    font-weight: 500;
    background: var(--gray-bg);
    color: var(--gray-text);
    border: none;
    border-radius: 8px;
    cursor: not-allowed;
    transition: background .2s, color .2s;
}

/* Enable when selected */
form:has(input[type="radio"]:checked) .continue-btn {
    background: var(--btn-color);
    color: var(--white);
    cursor: pointer;
}

/* Hover enabled */
form:has(input[type="radio"]:checked) .continue-btn:hover {
    background: var(--btn-color-dark);
}
</style>
@endsection
