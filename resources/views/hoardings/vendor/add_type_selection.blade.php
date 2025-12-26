{{--
    Add Hoarding Type Selection (Vendor)
    Architectural rules strictly enforced:
    - Only OOH handled here
    - DOOH delegated to DOOH module
    - Pixel-perfect Figma match
--}}
@extends('layouts.vendor')


@section('content')
<div class="container add-hoarding-type-selection" style="max-width: 480px; margin: 0 auto; padding-top: 80px;">
    <div style="text-align: center;">
        <img src="/images/hoarding_type_illustration.svg" alt="Hoarding Illustration" style="width: 120px; margin-bottom: 32px;" />
        <h2 style="font-weight: 600; font-size: 1.5rem; margin-bottom: 8px;">Start Listing Your Hoarding</h2>
        <p style="color: #888; font-size: 1rem; margin-bottom: 32px;">Select the hoarding type which you want to list</p>
    </div>
    <form method="POST" action="{{ route('vendor.hoardings.select-type') }}">
        @csrf
        <div class="row" style="display: flex; gap: 24px; justify-content: center; margin-bottom: 32px;">
            <label class="card-option" style="flex: 1; cursor: pointer;">
                <input type="radio" name="hoarding_type" value="OOH" style="display: none;" required />
                <div class="card" style="border: 1px solid #d9d9d9; border-radius: 8px; padding: 24px 0; text-align: center; background: #fff; transition: box-shadow 0.2s;">
                    <span style="font-weight: 500; font-size: 1.1rem;">OOH Hoarding</span><br>
                    <span style="color: #888; font-size: 0.95rem;">(Out-of-Home)</span>
                </div>
            </label>
            <label class="card-option" style="flex: 1; cursor: pointer;">
                <input type="radio" name="hoarding_type" value="DOOH" style="display: none;" required />
                <div class="card" style="border: 1px solid #d9d9d9; border-radius: 8px; padding: 24px 0; text-align: center; background: #fff; transition: box-shadow 0.2s;">
                    <span style="font-weight: 500; font-size: 1.1rem;">DOOH Hoarding</span><br>
                    <span style="color: #888; font-size: 0.95rem;">(Digital Out-of-Home)</span>
                </div>
            </label>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; height: 48px; font-size: 1.1rem; font-weight: 500; background: #e5e5e5; color: #888; border: none; border-radius: 8px; cursor: pointer;">Continue</button>
    </form>
</div>

<style>
    .card-option input[type="radio"]:checked + .card {
        border-color: #2d7cf7;
        box-shadow: 0 0 0 2px #2d7cf733;
    }
    .card-option .card:hover {
        box-shadow: 0 2px 8px #00000011;
    }
</style>
@endsection
