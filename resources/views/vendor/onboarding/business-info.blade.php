@extends('layouts.app')

@section('title', 'Add Business Info')

@section('content')
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer" />
<div class="vendor-page-white">

    <!-- Header -->
    <div class="vendor-header mt-5">
        <img src="{{ asset('assets/images/logo/logo_image.jpeg') }}" alt="OOHAPP">
        <span>Vendor</span>
    </div>

    <!-- Stepper -->
    <div class="vendor-signup-wrapper">
        <div class="signup-steps">
            <div class="step completed">
                <span>1</span>
                <p>ADD USER ACCOUNT INFO</p>
            </div>
            <div class="line active"></div>
            <div class="step active">
                <span>2</span>
                <p>ADD BUSINESS INFO</p>
            </div>
        </div>

        <!-- Card -->
        <div class="signup-card">

            <!-- ================= GENERAL DETAILS ================= -->
            <h3 class="section-title">General Details</h3>

            <div class="form-group">
                <label>GSTIN Number<span>*</span></label>
                <input type="text" placeholder="Enter GSTIN">
            </div>

            <div class="form-group">
                <label>Business Type<span>*</span></label>
                <select>
                    <option value="">Choose Business Type</option>
                    <option>Proprietorship</option>
                    <option>Partnership</option>
                    <option>Private Limited</option>
                </select>
            </div>

            <div class="form-group">
                <label>Business Name<span>*</span></label>
                <input type="text" placeholder="Enter Business Name">
            </div>

            <!-- ================= ADDRESS ================= -->
            <h3 class="section-title">Registered Business Address</h3>

            <div class="row">
                <div class="col">
                    <label>Street Address<span>*</span></label>
                    <input type="text" placeholder="Enter Street Address">
                    <small>0/64 Characters</small>
                </div>
                <div class="col small">
                    <label>Pincode<span>*</span></label>
                    <input type="text" placeholder="Enter Pincode">
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>City</label>
                    <input type="text">
                </div>
                <div class="col">
                    <label>State</label>
                    <input type="text">
                </div>
                <div class="col">
                    <label>Country</label>
                    <input type="text">
                </div>
            </div>

            <!-- ================= BANK DETAILS ================= -->
            <h3 class="section-title">
                Bank Account Details
                <span class="hint">
                    For a successful bank verification, account name must match with the registered GSTIN name
                </span>
            </h3>

            <div class="row">
                <div class="col">
                    <label>Bank Name<span>*</span></label>
                    <select>
                        <option value="">Choose Bank</option>
                        <option>SBI</option>
                        <option>HDFC</option>
                        <option>ICICI</option>
                    </select>
                </div>
                <div class="col">
                    <label>Account Number<span>*</span></label>
                    <input type="text" placeholder="Enter Account Number">
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>IFSC Code<span>*</span></label>
                    <input type="text" placeholder="Enter IFSC Code">
                </div>
                <div class="col">
                    <label>Account Holder Name</label>
                    <input type="text" placeholder="Enter Account Holder Name">
                </div>
            </div>

            <!-- ================= IDENTITY ================= -->
            <h3 class="section-title">
                Identity Verification
                <span class="info">ⓘ</span>
            </h3>

            <div class="form-group">
                <label>ID Type (PAN Card)<span>*</span></label>
                <input type="text" placeholder="Enter PAN Number">
            </div>

            <div class="form-group upload-box">
                <label>Upload PAN<span>*</span></label>
                <button class="upload-btn">
                    Upload <i class="fa fa-upload "></i>
                </button>
                <small>Maximum File Size: 5MB</small>
            </div>

            <!-- SUBMIT -->
            <button class="submit-btn" disabled>Submit</button>

        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
.vendor-page-white {
    background: #fff;
    min-height: 100vh;
    padding-bottom: 60px;
}

.vendor-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 18px 30px;
}
.vendor-header img { height: 28px; }
.vendor-header span { font-size: 13px; color: #6b7280; }

.vendor-signup-wrapper {
    max-width: 900px;
    margin: 20px auto;
    font-family: Inter, sans-serif;
}

/* Stepper */
.signup-steps {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
}
.step {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #9ca3af;
}
.step span {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
}
.step.completed span,
.step.active span {
    background: #22c55e;
    color: #fff;
}
.step.active { color: #111827; }
.line {
    flex: 1;
    height: 1px;
    background: #e5e7eb;
    margin: 0 16px;
}
.line.active { background: #22c55e; }

/* Card */
.signup-card {
    background: #fff;
}

.section-title {
    font-size: 15px;
    font-weight: 600;
    margin: 28px 0 12px;
}

.section-title .hint {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
}

label {
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 6px;
    display: block;
}
label span { color: red; }

input, select {
    width: 100%;
    height: 42px;
    padding: 0 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
}

.form-group { margin-bottom: 16px; }

.row {
    display: flex;
    gap: 20px;
    margin-bottom: 16px;
}
.col { flex: 1; }
.col.small { flex: 0.6; }

small {
    font-size: 11px;
    color: #9ca3af;
}

.upload-box {
    display: flex;
    align-items: center;
    gap: 12px;
}
.upload-btn {
    background: #22c55e;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 4px;
    cursor: pointer;
}

.submit-btn {
    margin-top: 30px;
    width: 140px;
    height: 42px;
    background: #e5e7eb;
    color: #9ca3af;
    border: none;
    border-radius: 6px;
}
</style>
@endpush
