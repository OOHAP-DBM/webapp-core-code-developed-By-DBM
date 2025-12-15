@extends('layouts.guest')

@section('title', 'Login - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>


* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    width: 100%;
    height: 100%;
    overflow:hidden;
}

body {
    font-family: 'Inter', sans-serif;
    background: #ffffff;
}

/* FULL SCREEN */
.auth-wrapper {
    width: 100vw;
    height: 100vh;
}

/* LEFT IMAGE */
.auth-left {
    background: #000;
    padding: 0;
    background-size:cover;
}

.auth-left img {
    width: 100%;
    height: 100%;
    object-fit: cover;      /* IMPORTANT for clarity */
    display: block;
}

/* RIGHT FORM */
.auth-right {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
}

/* FORM BOX */
.login-box {
    width: 100%;
    max-width: 380px;
}

/* BRAND */
.brand {
    margin-bottom: 10px;
}

.brand h2 {
    font-size: 20px;
    font-weight: 400;
}

/* INPUTS */
.form-control {
    height: 46px;
    border-radius: 4px;
    font-size: 14px;
}

/* BUTTON */
.btn-primary {
    height: 46px;
    border-radius: 8px;
    font-size: 14px;
}

/* MOBILE */
@media (max-width: 768px) {
    .auth-left {
        display: none;
    }

    .auth-right {
        width: 100%;
    }
}
.btn-continue {
    height: 46px;
    border-radius: 8px;
    font-size: 14px;
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.btn-continue.active {
    background: #2bb57c;
    color: #ffffff;
    cursor: pointer;
}

</style>
@endpush

@section('content')
<div class="container-fluid auth-wrapper">
    <div class="row h-100">

        <!-- LEFT IMAGE : col-5 -->
        <div class="col-md-5 d-none d-md-block auth-left">
            <img src="{{ asset('login_image/Left Blue.png') }}" alt="OOHAPP Login">
        </div>

        <!-- RIGHT FORM : col-7 -->
        <div class="col-md-7 col-12 auth-right">
            <div class="login-box">

                <!-- BRAND -->
                <div class="brand">
                    <h2>Login to your account</h2>
                </div>

                <!-- FORM -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="mb-3">
                        <input type="text"
                               name="identifier"
                               class="form-control"
                               placeholder="Email"
                               value="{{ old('identifier') }}"
                               required>
                               <small>We will send you a 4-digit OTP to confirm your email</small>
                    </div>          
                    <button type="submit" class="btn btn-continue w-100 mb-3 text-light border border-1">
                        Continue
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[name="identifier"]');
    const button = document.querySelector('.btn-continue');

    if (!input || !button) return;

    input.addEventListener('input', function () {
        if (this.value.trim() !== '') {
            button.disabled = false;
            button.classList.add('active');
        } else {
            button.disabled = true;
            button.classList.remove('active');
        }
    });
});
</script>
@endpush

