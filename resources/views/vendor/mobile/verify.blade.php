@extends('layouts.vendor')

@section('title', 'Verify Mobile Number')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-mobile-alt"></i> Verify Mobile Number</h5>
                </div>

                <div class="card-body">
                    @if($is_verified)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Your mobile number is verified
                    </div>
                    <p class="text-muted mb-4">Phone: <strong>{{ $phone }}</strong></p>
                    @else
                    <p class="text-muted mb-4">Please verify your mobile number to publish hoardings and receive customer inquiries.</p>
                    <p class="small mb-4"><strong>Phone:</strong> {{ $phone }}</p>
                    @endif

                    <div id="verificationContainer">
                        @if(!$is_verified)
                        <button type="button" class="btn btn-primary w-100 mb-3" id="sendOtpBtn">
                            <i class="fas fa-paper-plane"></i> Send OTP
                        </button>
                        @endif
                    </div>

                    <div id="otpContainer" style="display: none;">
                        <form id="verifyMobileForm">
                            <div class="mb-3">
                                <label class="form-label">Enter OTP (6 digits)</label>
                                <input type="text" class="form-control form-control-lg text-center" id="otpInput" name="otp" maxlength="6" inputmode="numeric" placeholder="000000" required>
                                <small class="text-muted">Check your email or SMS for the OTP</small>
                            </div>

                            <button type="submit" class="btn btn-success w-100 mb-2">Verify OTP</button>

                            <div class="text-center">
                                <button type="button" class="btn btn-link btn-sm" id="resendOtpBtn">
                                    Resend OTP <span id="resendTimer"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Your mobile number is required for:
                        <ul class="mb-0 mt-2">
                            <li>Publishing hoardings</li>
                            <li>Receiving customer inquiries</li>
                            <li>Account security</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let otpSentTime = null;
const RESEND_WAIT_SECONDS = 60;

document.getElementById('sendOtpBtn').addEventListener('click', async () => {
    try {
        const btn = document.getElementById('sendOtpBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

        const response = await fetch('{{ route('vendor.mobile.send-otp') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (data.success) {
            otpSentTime = Date.now();
            document.getElementById('verificationContainer').style.display = 'none';
            document.getElementById('otpContainer').style.display = 'block';
            document.getElementById('otpInput').focus();
            showAlert(data.message, 'success');
            startResendTimer();
        } else {
            showAlert(data.message || 'Failed to send OTP', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send OTP';
        }
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
});

document.getElementById('verifyMobileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const otp = document.getElementById('otpInput').value;

    try {
        const response = await fetch('{{ route('vendor.mobile.verify') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ otp })
        });

        const data = await response.json();

        if (data.success) {
            showAlert('Mobile number verified successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Invalid OTP', 'danger');
        }
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
});

document.getElementById('resendOtpBtn').addEventListener('click', async (e) => {
    e.preventDefault();

    try {
        const response = await fetch('{{ route('vendor.mobile.resend-otp') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();
        showAlert(data.message, data.success ? 'success' : 'warning');

        if (data.success) {
            otpSentTime = Date.now();
            startResendTimer();
        }
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
});

function startResendTimer() {
    const resendBtn = document.getElementById('resendOtpBtn');
    let remaining = RESEND_WAIT_SECONDS;

    const timer = setInterval(() => {
        remaining--;
        const timerSpan = document.getElementById('resendTimer');
        
        if (remaining > 0) {
            timerSpan.textContent = `(${remaining}s)`;
            resendBtn.disabled = true;
        } else {
            clearInterval(timer);
            timerSpan.textContent = '';
            resendBtn.disabled = false;
        }
    }, 1000);
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.row'));

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}
</script>
@endsection
