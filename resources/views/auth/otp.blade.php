@extends('layouts.guest')

@section('title', 'OTP Login - OOHAPP')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo & Header -->
        <div>
            <div class="flex justify-center">
                <img class="h-12 w-auto" src="{{ asset('images/logo.png') }}" alt="OOHAPP Logo">
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Login with OTP
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    sign in with password
                </a>
            </p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            {{ $errors->first() }}
                        </h3>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Messages -->
        @if (session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (!session('show_verify_form'))
            <!-- Send OTP Form -->
            <form class="mt-8 space-y-6" method="POST" action="{{ route('otp.send') }}">
                @csrf

                <div>
                    <label for="identifier" class="block text-sm font-medium text-gray-700">
                        Email or Phone Number
                    </label>
                    <input 
                        id="identifier" 
                        name="identifier" 
                        type="text" 
                        required 
                        class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('identifier') border-red-500 @enderror" 
                        placeholder="Email or +91 9876543210"
                        value="{{ old('identifier') }}"
                        autofocus
                    >
                    @error('identifier')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Enter your registered email or phone number to receive OTP
                    </p>
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </span>
                        Send OTP
                    </button>
                </div>
            </form>
        @else
            <!-- Verify OTP Form -->
            <form class="mt-8 space-y-6" method="POST" action="{{ route('otp.verify') }}">
                @csrf

                <input type="hidden" name="identifier" value="{{ session('otp_identifier') }}">

                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700">
                        Enter OTP
                    </label>
                    <input 
                        id="otp" 
                        name="otp" 
                        type="text" 
                        required 
                        maxlength="6"
                        pattern="[0-9]{6}"
                        class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest font-bold @error('otp') border-red-500 @enderror" 
                        placeholder="● ● ● ● ● ●"
                        autofocus
                    >
                    @error('otp')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500 text-center">
                        We've sent a 6-digit code to <span class="font-semibold">{{ session('otp_identifier') }}</span>
                    </p>
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        Verify OTP
                    </button>
                </div>

                <!-- Resend OTP -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Didn't receive the code?
                    </p>
                    <form method="POST" action="{{ route('otp.resend') }}" class="inline">
                        @csrf
                        <input type="hidden" name="identifier" value="{{ session('otp_identifier') }}">
                        <button 
                            type="submit" 
                            class="font-medium text-indigo-600 hover:text-indigo-500 text-sm mt-1"
                            id="resendBtn"
                        >
                            Resend OTP
                        </button>
                    </form>
                </div>

                <!-- Back Button -->
                <div class="text-center">
                    <a href="{{ route('login.otp') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        ← Change email/phone
                    </a>
                </div>
            </form>

            <!-- Auto-focus OTP input -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const otpInput = document.getElementById('otp');
                    if (otpInput) {
                        otpInput.focus();
                        
                        // Only allow numbers
                        otpInput.addEventListener('input', function(e) {
                            this.value = this.value.replace(/[^0-9]/g, '');
                        });
                    }

                    // Resend cooldown (optional)
                    const resendBtn = document.getElementById('resendBtn');
                    if (resendBtn && localStorage.getItem('otp_resend_time')) {
                        const lastResend = parseInt(localStorage.getItem('otp_resend_time'));
                        const cooldown = 60000; // 60 seconds
                        const elapsed = Date.now() - lastResend;
                        
                        if (elapsed < cooldown) {
                            const remaining = Math.ceil((cooldown - elapsed) / 1000);
                            resendBtn.disabled = true;
                            resendBtn.textContent = `Resend in ${remaining}s`;
                            
                            const timer = setInterval(() => {
                                const newElapsed = Date.now() - lastResend;
                                const newRemaining = Math.ceil((cooldown - newElapsed) / 1000);
                                
                                if (newRemaining <= 0) {
                                    clearInterval(timer);
                                    resendBtn.disabled = false;
                                    resendBtn.textContent = 'Resend OTP';
                                    localStorage.removeItem('otp_resend_time');
                                } else {
                                    resendBtn.textContent = `Resend in ${newRemaining}s`;
                                }
                            }, 1000);
                        }
                    }

                    // Set resend time on button click
                    if (resendBtn) {
                        resendBtn.closest('form').addEventListener('submit', function() {
                            localStorage.setItem('otp_resend_time', Date.now().toString());
                        });
                    }
                });
            </script>
        @endif
    </div>
</div>
@endsection
