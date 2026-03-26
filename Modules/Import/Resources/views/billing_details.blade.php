@extends('layouts.app')
@section('title', 'Billing Details')

@section('content')
@include('components.customer.navbar')

{{-- ── Breadcrumb ─────────────────────────────────────────── --}}
<div class="br-wrap">
    <nav class="br-nav">
        <a href="{{ route('home') }}">Home</a><span>/</span>
        <a href="#">OOH</a><span>/</span>
        <a href="#">Unipole</a><span>/</span>
        @if(isset($draft) && isset($draft->hoarding))
            <a href="{{ route('hoardings.show', $draft->hoarding->id) }}">{{ $draft->hoarding->title }}</a>
        @else
            <span>Hoarding</span>
        @endif
        <span>/</span>
        <a href="{{ route('cart.index') }}">Your Cart</a><span>/</span>
        <span class="br-active">Billing Details</span>
    </nav>
    <a href="{{ url()->previous() }}" class="br-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
</div>

{{-- ── Page Layout ────────────────────────────────────────── --}}
<div class="ck-layout">

    {{-- ══════════════════════════════════════════════════════
         LEFT COLUMN
    ══════════════════════════════════════════════════════ --}}
    <div class="ck-left">

        {{-- ════════════════════════════
             STATE A: Billing form
             (shown when billing NOT yet saved)
        ════════════════════════════ --}}
        <div id="state-form">
            <div class="ck-step">
                <div class="ck-step-head">
                    <span class="ck-step-num">1.</span>
                    <span class="ck-step-title">Enter Billing Details</span>
                </div>
                <div class="ck-step-body">
                    <form id="billing-form" onsubmit="submitBilling(event)">
                        @csrf
                        <div class="ck-row">
                            <div class="ck-field">
                                <label>Full Name <span class="req">*</span></label>
                                <input type="text" name="full_name" placeholder="Enter full name"
                                       value="{{ old('full_name', auth()->user()->name) }}" required>
                            </div>
                            <div class="ck-field">
                                <label>Mobile Number</label>
                                <div class="ck-input-icon">
                                    <input type="tel" name="mobile" placeholder="Mobile number"
                                           value="{{ old('mobile', auth()->user()->phone ?? '') }}">
                                    <span class="ck-icon-right">
                                        <svg width="20" height="20" viewBox="0 0 20 18" fill="none">
                                            <path d="M7 0L6 3H2L3 7L0 9L3 11L2 15H6L7 18L10 16L13 18L14 15H18L17 11L20 9L17 7L18 3H14L13 0L10 2L7 0ZM14 5L15 6L8 13L5 10L6 9L8 11L14 5Z" fill="#009A5C"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="ck-row">
                            <div class="ck-field">
                                <label>Email <span class="req">*</span></label>
                                <input type="email" name="email" placeholder="Enter email address"
                                       value="{{ old('email', auth()->user()->email) }}" required>
                            </div>
                            <div class="ck-field">
                                <label>Billing Address <span class="req">*</span></label>
                                <input type="text" name="billing_address" placeholder="Enter billing address"
                                       value="{{ old('billing_address') }}" required id="inp-address">
                            </div>
                        </div>
                        <div class="ck-row">
                            <div class="ck-field">
                                <label>Pincode <span class="req">*</span></label>
                                <input type="text" name="pincode" placeholder="Enter pincode"
                                       value="{{ old('pincode') }}" maxlength="6" required id="inp-pincode">
                            </div>
                            <div class="ck-field">
                                <label>City</label>
                                <input type="text" name="city" placeholder="City"
                                       value="{{ old('city') }}" id="inp-city">
                            </div>
                        </div>
                        <div class="ck-row">
                            <div class="ck-field ck-field--full">
                                <label>State</label>
                                <input type="text" name="state" placeholder="State"
                                       value="{{ old('state') }}" id="inp-state">
                            </div>
                        </div>
                        <div class="ck-subhead">Add Company Details</div>
                        <div class="ck-row">
                            <div class="ck-field">
                                <label>Company Name</label>
                                <input type="text" name="company_name" placeholder="Enter Company Name"
                                       value="{{ old('company_name') }}">
                            </div>
                            <div class="ck-field">
                                <label>GSTIN Number</label>
                                <input type="text" name="gstin" placeholder="Enter GSTIN Number"
                                       value="{{ old('gstin') }}" maxlength="15">
                            </div>
                        </div>
                        <div class="ck-actions">
                            <button type="submit" class="ck-btn-continue" id="continue-btn">
                                Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Step 2 placeholder (locked) --}}
            <div class="ck-step ck-step--locked">
                <div class="ck-step-head">
                    <span class="ck-step-num">2.</span>
                    <span class="ck-step-title">Payments</span>
                </div>
            </div>
        </div>{{-- /#state-form --}}

        {{-- ════════════════════════════
             STATE B: After billing saved
             (shown after billing confirmed)
        ════════════════════════════ --}}
        <div id="state-payment" style="display:none;">

            {{-- 1. Billing Address (confirmed) --}}
            <div class="ck-step">
                <div class="ck-step-head" style="justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="ck-step-num">1.</span>
                        <span class="ck-step-title">Billing Address</span>
                    </div>
                    <button class="edit-link" onclick="editBilling()">edit address</button>
                </div>
                <div class="ck-step-body" style="padding-top:0;">
                    <div class="saved-billing-card">
                        <div class="saved-billing-name" id="saved-name">—</div>
                        <div class="saved-billing-addr" id="saved-addr">—</div>
                    </div>
                </div>
            </div>

            {{-- 2. Payments --}}
            <div class="ck-step">
                <div class="ck-step-head" style="border-bottom:2px solid #22c55e;padding-bottom:14px;">
                    <span class="ck-step-num">2.</span>
                    <span class="ck-step-title">Payments</span>
                </div>
                <div class="ck-step-body" style="padding-top:16px;">

                    {{-- Payment method tabs --}}
                    <div class="pay-tabs">
                        <button class="pay-tab active" onclick="switchTab('card',this)">Credit / Debit</button>
                        <button class="pay-tab"        onclick="switchTab('netbanking',this)">Net Banking</button>
                        <button class="pay-tab"        onclick="switchTab('upi',this)">UPI (Pay via any App)</button>
                    </div>

                    {{-- ── Tab: Credit/Debit Card ── --}}
                    <div id="tab-card" class="pay-tab-body">
                        <div class="card-form-label">Enter Card Details</div>
                        <div class="ck-row" style="margin-bottom:12px;">
                            <div class="ck-field" style="grid-column:1/-1;">
                                <label>Enter Card Number <span class="req">*</span></label>
                                <div class="ck-input-icon">
                                    <input type="text" id="card-number" placeholder="0000 0000 0000 0000"
                                           maxlength="19" oninput="formatCard(this)">
                                    <span class="ck-icon-right">
                                        <img id="card-logo" src="" alt="" style="height:20px;display:none;">
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="ck-row" style="margin-bottom:12px;">
                            <div class="ck-field">
                                <label>Name on Card <span class="req">*</span></label>
                                <input type="text" id="card-name" placeholder="Enter Name Here">
                            </div>
                        </div>
                        <div class="ck-row" style="margin-bottom:20px;">
                            <div class="ck-field">
                                <label>Expiry Date <span class="req">*</span></label>
                                <input type="text" id="card-expiry" placeholder="MM / YY" maxlength="7"
                                       oninput="formatExpiry(this)">
                            </div>
                            <div class="ck-field">
                                <label>CVV <span class="req">*</span></label>
                                <div class="ck-input-icon">
                                    <input type="password" id="card-cvv" placeholder="MM / YY" maxlength="4">
                                    <span class="ck-icon-right" style="cursor:help;" title="3-digit code on back of card">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Tab: Net Banking ── --}}
                    <div id="tab-netbanking" class="pay-tab-body" style="display:none;">
                        <div class="card-form-label">Select your bank</div>
                        <div class="bank-grid">
                            @foreach(['SBI','HDFC','ICICI','Axis','Kotak','PNB','Yes Bank','IDBI'] as $bank)
                            <label class="bank-opt">
                                <input type="radio" name="bank" value="{{ $bank }}">
                                <span class="bank-box">{{ $bank }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div class="ck-field" style="margin-top:14px;">
                            <label>Or search bank</label>
                            <input type="text" placeholder="Search bank name…">
                        </div>
                    </div>

                    {{-- ── Tab: UPI ── --}}
                    <div id="tab-upi" class="pay-tab-body" style="display:none;">
                        <div class="card-form-label">Enter UPI ID</div>
                        <div class="ck-field" style="margin-bottom:16px;">
                            <label>UPI ID <span class="req">*</span></label>
                            <input type="text" id="upi-id" placeholder="yourname@upi">
                        </div>
                        <div class="upi-apps">
                            <span class="upi-app-label">Or pay with</span>
                            <div class="upi-app-row">
                                <button class="upi-app-btn" onclick="openUPIApp('gpay')">GPay</button>
                                <button class="upi-app-btn" onclick="openUPIApp('phonepe')">PhonePe</button>
                                <button class="upi-app-btn" onclick="openUPIApp('paytm')">Paytm</button>
                            </div>
                        </div>
                    </div>

                    {{-- ── Pay button ── --}}
                    <div class="pay-footer">
                        <div class="pay-secure">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Encrypted and secure payments</span>
                        </div>
                        <button class="pay-btn" onclick="initiatePayment()">
                            Pay ₹<span id="pay-display-amount">{{ number_format($draft->total_amount) }}</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>{{-- /#state-payment --}}

    </div>{{-- /ck-left --}}

    {{-- ══════════════════════════════════════════════════════
         RIGHT COLUMN — Booking Summary (always visible)
    ══════════════════════════════════════════════════════ --}}
    <div class="ck-right">
        <div class="bsum-card">

            <h3 class="bsum-title">Booking Summary</h3>

            @if(isset($hoarding))
                <div class="bsum-hoard-name">{{ $hoarding->title }}</div>
                <div class="bsum-location">
                    <svg width="12" height="14" viewBox="0 0 12 14" fill="none">
                        <path d="M6 0C3.79 0 2 1.79 2 4c0 3 4 10 4 10s4-7 4-10c0-2.21-1.79-4-4-4z" fill="#e53935"/>
                    </svg>
                    {{ $hoarding->city ?? '' }}{{ $hoarding->state ? ', '.$hoarding->state : '' }}
                </div>
            @endif

            <div class="bsum-tags">
                <span class="bsum-tag bsum-tag--green">{{ strtoupper($hoarding->type ?? 'OOH') }}</span>
                @if(isset($hoarding) && $hoarding->width && $hoarding->height)
                    <span class="bsum-tag">{{ $hoarding->width }}*{{ $hoarding->height }}sq.ft</span>
                @endif
            </div>

            <div class="bsum-duration">
                Duration – {{ $reviewSummary['booking_period']['duration_display'] ?? 'N/A' }}
            </div>

            <hr class="bsum-hr">

            {{-- Subtotal --}}
            <div class="bsum-row">
                <span>Subtotal</span>
                <span class="bsum-price-wrap">
                    @if($draft->discount_amount > 0)
                        <span class="bsum-strike">₹{{ number_format($draft->base_price) }}</span>
                    @endif
                    <span class="bsum-price-main">₹{{ number_format($draft->base_price - $draft->discount_amount) }}</span>
                </span>
            </div>

            @if($draft->discount_amount > 0)
            <div class="bsum-row bsum-row--discount">
                <span>Offer Discount
                    @if(!empty($draft->price_snapshot['discount_percent']))
                        –{{ $draft->price_snapshot['discount_percent'] }}%
                    @endif
                </span>
                <span class="bsum-discount-val">−₹{{ number_format($draft->discount_amount) }}</span>
            </div>
            @endif

            {{-- Coupon discount row (shown after coupon applied) --}}
            <div id="coupon-discount-row" class="bsum-row bsum-row--discount" style="display:none;">
                <span>
                    Coupon Discount
                    <button class="coupon-remove-btn" onclick="removeCoupon()">Remove</button>
                </span>
                <span class="bsum-discount-val" id="coupon-discount-val"></span>
            </div>

            {{-- Coupon input --}}
            <div class="bsum-coupon-wrap" id="coupon-wrap">
                <span class="bsum-coupon-lbl">Any coupon code?</span>
                <div class="bsum-coupon-row">
                    <input type="text" id="coupon-input" placeholder="Enter coupon code"
                           value="{{ $draft->coupon_code ?? '' }}">
                    <button type="button" onclick="applyCoupon()" id="coupon-btn">Apply</button>
                </div>
                <div id="coupon-msg" class="bsum-coupon-msg"></div>
            </div>

            <hr class="bsum-hr">

            <div class="bsum-row bsum-row--sm">
                <span>Printing Charge</span>
                <span>₹{{ number_format($reviewSummary['pricing']['price_snapshot']['printing_charges'] ?? 0) }}</span>
            </div>
            <div class="bsum-row bsum-row--sm">
                <span>Mounting Charge</span>
                <span>₹{{ number_format($reviewSummary['pricing']['price_snapshot']['mounting_charges'] ?? 0) }}</span>
            </div>
            <div class="bsum-row bsum-row--sm">
                <span>Taxes <span class="bsum-info" title="GST as per govt norms">ⓘ</span></span>
                <span>₹{{ number_format($draft->gst_amount) }}</span>
            </div>

            <hr class="bsum-hr">

            <div class="bsum-row bsum-total">
                <span>Total</span>
                <span id="bsum-total-display">
                    ₹{{ number_format($draft->total_amount) }}
                    for {{ $reviewSummary['booking_period']['duration_display'] ?? '' }}
                </span>
            </div>

        </div>
    </div>

</div>{{-- /ck-layout --}}

<input type="hidden" id="draft-id"     value="{{ $draft->id }}">
<input type="hidden" id="total-amount" value="{{ $draft->total_amount }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Razorpay SDK --}}
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

@endsection

@push('styles')
<style>
*,*::before,*::after{box-sizing:border-box;}
body{background:#f5f5f5;}

/* ── breadcrumb ──────────────────────────── */
.br-wrap{max-width:1100px;margin:0 auto;padding:10px 20px 0;}
.br-nav{font-size:13px;color:#555;display:flex;align-items:center;flex-wrap:wrap;gap:4px;}
.br-nav a{color:#555;text-decoration:none;}.br-nav a:hover{text-decoration:underline;}
.br-nav span{color:#999;}.br-active{color:#111;font-weight:600;}
.br-back{display:inline-flex;align-items:center;color:#333;margin-top:8px;text-decoration:none;transition:color .15s;}
.br-back:hover{color:#000;}

/* ── layout ──────────────────────────────── */
.ck-layout{display:grid;grid-template-columns:1fr 320px;gap:24px;max-width:1100px;margin:16px auto 40px;padding:0 20px;align-items:start;}
@media(max-width:768px){.ck-layout{grid-template-columns:1fr;}.ck-right{order:-1;}}

/* ── step card ───────────────────────────── */
.ck-step{background:#fff;border:1px solid #e5e5e5;border-radius:8px;margin-bottom:12px;overflow:hidden;}
.ck-step--locked{opacity:.5;pointer-events:none;}
.ck-step-head{padding:16px 20px;display:flex;align-items:center;gap:8px;}
.ck-step-num{font-size:15px;font-weight:700;color:#111;}
.ck-step-title{font-size:15px;font-weight:600;color:#111;}
.ck-step-body{padding:0 20px 20px;}

/* ── form ────────────────────────────────── */
.ck-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;}
.ck-field{display:flex;flex-direction:column;gap:5px;}
.ck-field--full{grid-column:1/-1;}
.ck-field label{font-size:13px;font-weight:500;color:#333;}.req{color:#e53935;}
.ck-field input{height:40px;border:1px solid #d9d9d9;border-radius:6px;padding:0 12px;font-size:13px;color:#111;outline:none;transition:border-color .15s;background:#fff;width:100%;}
.ck-field input:focus{border-color:#22c55e;}.ck-field input::placeholder{color:#bbb;}
.ck-input-icon{position:relative;}.ck-input-icon input{padding-right:38px;}
.ck-icon-right{position:absolute;right:10px;top:50%;transform:translateY(-50%);display:flex;align-items:center;}
.ck-subhead{font-size:14px;font-weight:600;color:#111;margin:6px 0 14px;}
.ck-actions{display:flex;justify-content:flex-end;margin-top:4px;}
.ck-btn-continue{background:#d9d9d9;color:#888;border:none;border-radius:6px;padding:10px 28px;font-size:14px;font-weight:600;cursor:not-allowed;transition:background .2s,color .2s;}
.ck-btn-continue.active{background:#111;color:#fff;cursor:pointer;}
.ck-btn-continue.active:hover{background:#333;}

/* ── saved billing card ──────────────────── */
.saved-billing-card{background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:14px 16px;}
.saved-billing-name{font-size:14px;font-weight:600;color:#111;margin-bottom:4px;}
.saved-billing-addr{font-size:13px;color:#555;line-height:1.5;}
.edit-link{background:none;border:none;color:#555;font-size:13px;cursor:pointer;text-decoration:underline;padding:0;}
.edit-link:hover{color:#111;}

/* ── payment tabs ────────────────────────── */
.pay-tabs{display:flex;gap:0;margin-bottom:20px;border:1px solid #d9d9d9;border-radius:8px;overflow:hidden;}
.pay-tab{flex:1;padding:10px 6px;font-size:13px;font-weight:500;color:#555;background:#fff;border:none;border-right:1px solid #d9d9d9;cursor:pointer;transition:background .15s,color .15s;}
.pay-tab:last-child{border-right:none;}
.pay-tab.active{background:#fff;color:#111;font-weight:700;box-shadow:inset 0 -2px 0 #111;}
.pay-tab:hover:not(.active){background:#f9f9f9;}

/* ── card form ───────────────────────────── */
.card-form-label{font-size:14px;font-weight:600;color:#111;margin-bottom:14px;}
.pay-tab-body{}

/* ── bank grid ───────────────────────────── */
.bank-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:8px;}
.bank-opt{cursor:pointer;}.bank-opt input{display:none;}
.bank-box{display:block;text-align:center;border:1px solid #d9d9d9;border-radius:6px;padding:8px 4px;font-size:12px;font-weight:500;color:#333;transition:border-color .15s,background .15s;}
.bank-opt input:checked + .bank-box{border-color:#22c55e;background:#f0fdf4;color:#166534;}
.bank-box:hover{border-color:#aaa;}

/* ── UPI ─────────────────────────────────── */
.upi-apps{margin-top:4px;}
.upi-app-label{font-size:12px;color:#777;display:block;margin-bottom:8px;}
.upi-app-row{display:flex;gap:8px;}
.upi-app-btn{padding:8px 18px;border:1px solid #d9d9d9;border-radius:6px;font-size:13px;font-weight:600;background:#fff;cursor:pointer;color:#111;transition:border-color .15s,background .15s;}
.upi-app-btn:hover{border-color:#22c55e;background:#f0fdf4;}

/* ── pay footer ──────────────────────────── */
.pay-footer{display:flex;align-items:center;justify-content:space-between;margin-top:24px;gap:12px;}
.pay-secure{display:flex;align-items:center;gap:6px;font-size:12px;color:#555;}
.pay-btn{background:#22c55e;color:#fff;border:none;border-radius:8px;padding:12px 28px;font-size:15px;font-weight:700;cursor:pointer;transition:background .15s;white-space:nowrap;}
.pay-btn:hover{background:#16a34a;}

/* ── booking summary ─────────────────────── */
.bsum-card{background:#fff;border:1px solid #e5e5e5;border-radius:8px;padding:20px;position:sticky;top:80px;}
.bsum-title{font-size:16px;font-weight:700;color:#111;margin:0 0 14px;}
.bsum-hoard-name{font-size:14px;font-weight:700;color:#111;margin-bottom:4px;}
.bsum-location{font-size:12px;color:#555;display:flex;align-items:center;gap:4px;margin-bottom:8px;}
.bsum-tags{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:6px;}
.bsum-tag{font-size:11px;font-weight:600;padding:2px 8px;border-radius:4px;background:#f3f4f6;color:#555;}
.bsum-tag--green{background:#dcfce7;color:#166534;}
.bsum-duration{font-size:13px;color:#555;margin-top:4px;}
.bsum-hr{border:none;border-top:1px solid #e5e5e5;margin:14px 0;}
.bsum-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;color:#333;margin-bottom:8px;}
.bsum-row--sm{font-size:12px;color:#555;margin-bottom:6px;}
.bsum-row--discount{color:#555;}
.bsum-discount-val{color:#22c55e;font-weight:600;}
.bsum-price-wrap{display:flex;align-items:center;gap:6px;}
.bsum-strike{text-decoration:line-through;color:#aaa;font-size:12px;}
.bsum-price-main{font-weight:700;color:#111;font-size:15px;}
.bsum-total{font-size:15px;font-weight:700;color:#111;margin-bottom:0;}
.bsum-info{font-size:11px;color:#888;cursor:help;}

/* ── coupon ──────────────────────────────── */
.bsum-coupon-wrap{margin:10px 0;}
.bsum-coupon-lbl{font-size:13px;color:#22c55e;font-weight:500;cursor:pointer;display:block;margin-bottom:8px;}
.bsum-coupon-row{display:flex;gap:6px;}
.bsum-coupon-row input{flex:1;height:36px;border:1px solid #d9d9d9;border-radius:6px;padding:0 10px;font-size:13px;outline:none;transition:border-color .15s;}
.bsum-coupon-row input:focus{border-color:#22c55e;}
.bsum-coupon-row button{height:36px;padding:0 14px;border-radius:6px;border:none;background:#d9d9d9;color:#888;font-size:13px;font-weight:600;cursor:not-allowed;transition:background .2s,color .2s;}
.bsum-coupon-row button.active{background:#111;color:#fff;cursor:pointer;}
.bsum-coupon-row button.active:hover{background:#333;}
.bsum-coupon-msg{font-size:12px;margin-top:4px;}
.bsum-coupon-msg.ok{color:#22c55e;}.bsum-coupon-msg.err{color:#e53935;}
.coupon-remove-btn{background:none;border:none;color:#22c55e;font-size:12px;cursor:pointer;padding:0 0 0 6px;text-decoration:underline;}

@media(max-width:520px){.ck-row{grid-template-columns:1fr;}.bank-grid{grid-template-columns:repeat(2,1fr);}.pay-footer{flex-direction:column;align-items:stretch;}.pay-btn{width:100%;text-align:center;}}
</style>
@endpush

@push('scripts')
<script>
const CSRF     = document.querySelector('meta[name=csrf-token]').content;
const draftId  = document.getElementById('draft-id').value;
let   currentTotal = parseFloat(document.getElementById('total-amount').value);

/* ── Watch required fields → activate Continue ── */
(function(){
    const btn  = document.getElementById('continue-btn');
    const reqs = document.getElementById('billing-form').querySelectorAll('[required]');
    const check = () => btn.classList.toggle('active', [...reqs].every(i=>i.value.trim()!==''));
    reqs.forEach(i=>i.addEventListener('input',check));
    check();
})();

/* ── Watch coupon input → activate Apply ── */
(function(){
    const inp = document.getElementById('coupon-input');
    const btn = document.getElementById('coupon-btn');
    const check = () => btn.classList.toggle('active', inp.value.trim().length>0);
    inp.addEventListener('input',check);
    check();
})();

/* ── Submit billing form ── */
function submitBilling(e){
    e.preventDefault();
    const btn = document.getElementById('continue-btn');
    if(!btn.classList.contains('active')) return;

    btn.textContent = 'Saving…';
    btn.classList.remove('active');

    const fd = new FormData(e.target);
    fd.append('draft_id', draftId);

    // Build display strings before fetch
    const name    = fd.get('full_name') || '';
    const address = fd.get('billing_address') || '';
    const city    = fd.get('city') || '';
    const state   = fd.get('state') || '';
    const pincode = fd.get('pincode') || '';
    const addrLine = [address, city, state, pincode].filter(Boolean).join(', ');

    fetch('{{ route("bookings.billing.save") }}', {
        method : 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
        body   : fd,
    })
    .then(r => r.json())
    .then(res => {
        if(res.success){
            // Fill saved billing card
            document.getElementById('saved-name').textContent = 'Bill to – ' + name;
            document.getElementById('saved-addr').textContent = addrLine;
            // Swap states
            document.getElementById('state-form').style.display    = 'none';
            document.getElementById('state-payment').style.display = 'block';
            // Smooth scroll to payment
            document.getElementById('state-payment').scrollIntoView({behavior:'smooth',block:'start'});
        } else {
            btn.textContent = 'Continue';
            btn.classList.add('active');
            alert(res.message || 'Could not save billing details.');
        }
    })
    .catch(()=>{
        btn.textContent = 'Continue';
        btn.classList.add('active');
        alert('Network error. Please try again.');
    });
}

/* ── Edit billing (go back to form) ── */
function editBilling(){
    document.getElementById('state-payment').style.display = 'none';
    document.getElementById('state-form').style.display    = 'block';
}

/* ── Payment tabs ── */
function switchTab(tab, el){
    ['card','netbanking','upi'].forEach(t=>{
        document.getElementById('tab-'+t).style.display = t===tab ? 'block' : 'none';
    });
    document.querySelectorAll('.pay-tab').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
}

/* ── Card number formatting ── */
function formatCard(inp){
    let v = inp.value.replace(/\D/g,'').substring(0,16);
    inp.value = v.replace(/(.{4})/g,'$1 ').trim();
    // Simple card type detection
    const logo = document.getElementById('card-logo');
    if(/^4/.test(v)){logo.src='https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg';logo.style.display='block';}
    else if(/^5[1-5]/.test(v)||/^2[2-7]/.test(v)){logo.src='https://upload.wikimedia.org/wikipedia/commons/a/a4/Mastercard_2019_logo.svg';logo.style.display='block';}
    else if(/^3[47]/.test(v)){logo.src='https://upload.wikimedia.org/wikipedia/commons/f/fa/American_Express_logo_%282018%29.svg';logo.style.display='block';}
    else{logo.style.display='none';}
}

/* ── Expiry formatting ── */
function formatExpiry(inp){
    let v = inp.value.replace(/\D/g,'');
    if(v.length>=2) v = v.substring(0,2)+' / '+v.substring(2,4);
    inp.value = v;
}

/* ── Apply coupon ── */
function applyCoupon(){
    const code = document.getElementById('coupon-input').value.trim();
    const msg  = document.getElementById('coupon-msg');
    if(!code) return;

    fetch('{{ route("bookings.coupon.apply") }}', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body   : JSON.stringify({ draft_id: draftId, coupon_code: code }),
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.success){
            msg.className   = 'bsum-coupon-msg ok';
            msg.textContent = `Coupon applied! You save ₹${res.discount_amount.toLocaleString('en-IN')}`;
            // Show coupon discount row
            document.getElementById('coupon-discount-row').style.display = 'flex';
            document.getElementById('coupon-discount-val').textContent    = '−₹'+parseInt(res.discount_amount).toLocaleString('en-IN');
            // Update total
            currentTotal = res.new_total;
            updateTotalDisplay(res.new_total);
            // Hide coupon input
            document.getElementById('coupon-wrap').style.display = 'none';
        } else {
            msg.className   = 'bsum-coupon-msg err';
            msg.textContent = res.message || 'Invalid coupon code.';
        }
    })
    .catch(()=>{
        document.getElementById('coupon-msg').className   = 'bsum-coupon-msg err';
        document.getElementById('coupon-msg').textContent = 'Could not apply coupon.';
    });
}

/* ── Remove coupon ── */
function removeCoupon(){
    fetch('{{ route("bookings.coupon.remove") }}', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body   : JSON.stringify({ draft_id: draftId }),
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.success){
            document.getElementById('coupon-discount-row').style.display = 'none';
            document.getElementById('coupon-wrap').style.display         = 'block';
            document.getElementById('coupon-input').value                = '';
            document.getElementById('coupon-msg').textContent            = '';
            currentTotal = res.new_total;
            updateTotalDisplay(res.new_total);
        }
    });
}

function updateTotalDisplay(total){
    const fmt = parseInt(total).toLocaleString('en-IN');
    document.getElementById('pay-display-amount').textContent = fmt;
    const dur = '{{ $reviewSummary["booking_period"]["duration_display"] ?? "" }}';
    document.getElementById('bsum-total-display').textContent = '₹'+fmt+(dur?' for '+dur:'');
}

/* ── UPI app shortcut ── */
function openUPIApp(app){
    alert('Redirecting to '+app+'…  (wire to your gateway UPI intent URL)');
}

/* ── Initiate payment (Razorpay) ── */
function initiatePayment(){
fetch("{{ route('payment.createOrder') }}", {
            method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body   : JSON.stringify({ source: 'draft', source_id: draftId }),
    })
    .then(r=>r.json())
    .then(res=>{
        if(!res.success){ alert(res.message || 'Could not initiate payment.'); return; }

        if(res.driver === 'razorpay'){
            openRazorpay(res.data);
        } else if(res.driver === 'payu'){
            submitPayUForm(res.data);
        }
    })
    .catch(()=>alert('Network error. Please try again.'));
}

/* ── Razorpay widget ── */
function openRazorpay(data){
    const rzp = new Razorpay({
        key        : data.razorpay_key,
        amount     : data.amount,
        currency   : data.currency || 'INR',
        order_id   : data.razorpay_order_id,
        name       : data.name || 'OOHAPP',
        description: data.description || 'Hoarding Booking',
        prefill    : data.prefill || {},
        theme      : { color: '#22c55e' },
        handler    : function(response){
            fetch('/payment/verify', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body   : JSON.stringify({
                    source              : 'draft',
                    source_id           : draftId,
                    razorpay_order_id   : response.razorpay_order_id,
                    razorpay_payment_id : response.razorpay_payment_id,
                    razorpay_signature  : response.razorpay_signature,
                }),
            })
            .then(r=>r.json())
            .then(v=>{
                if(v.success) window.location.href = v.redirect_url;
                else          alert('Payment verification failed. Please contact support.');
            });
        },
    });
    rzp.open();
}

/* ── PayU form POST ── */
function submitPayUForm(data){
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = data.action;
    Object.entries(data.fields).forEach(([k,v])=>{
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = k; inp.value = v;
        form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush