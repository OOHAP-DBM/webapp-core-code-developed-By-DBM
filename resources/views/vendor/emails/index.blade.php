@extends('layouts.vendor')

@section('title', 'Manage Emails')

@section('content')
<div class="container-fluid my-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Email Management</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEmailModal">
                        <i class="fas fa-plus"></i> Add Email
                    </button>
                </div>

                <div class="card-body">
                    @if($emails->isEmpty())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No emails added yet. Please add an email address.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Primary</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="emailsTableBody">
                                @foreach($emails as $email)
                                <tr>
                                    <td>{{ $email->email }}</td>
                                    <td>
                                        @if($email->isVerified())
                                        <span class="badge bg-success">Verified</span>
                                        @else
                                        <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($email->is_primary)
                                        <span class="badge bg-info">Primary</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$email->isVerified())
                                        <button class="btn btn-xs btn-warning" onclick="verifyEmail({{ $email->id }})">
                                            Verify
                                        </button>
                                        @endif
                                        @if($email->isVerified() && !$email->is_primary)
                                        <button class="btn btn-xs btn-info" onclick="makePrimary({{ $email->id }})">
                                            Make Primary
                                        </button>
                                        @endif
                                        @if(!$email->is_primary)
                                        <button class="btn btn-xs btn-danger" onclick="deleteEmail({{ $email->id }})">
                                            Delete
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Email Verification Guide</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small">
                        <p><strong>Why verify emails?</strong></p>
                        <ul class="mb-0">
                            <li>Secure your account</li>
                            <li>Required for publishing hoardings</li>
                            <li>Receive important notifications</li>
                            <li>Allow customer inquiries</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Email Modal -->
<div class="modal fade" id="addEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addEmailForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" required>
                        <small class="text-muted">Must be a valid email address</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Verify Email Modal -->
<div class="modal fade" id="verifyEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verify Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="verifyEmailForm">
                <div class="modal-body">
                    <p class="text-muted mb-3">Enter the OTP sent to your email address</p>
                    <div class="mb-3">
                        <label class="form-label">OTP (6 digits)</label>
                        <input type="text" class="form-control form-control-lg text-center" name="otp" maxlength="6" inputmode="numeric" required>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-link btn-sm" id="resendOtpBtn">Resend OTP</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentEmailId = null;

document.getElementById('addEmailForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.querySelector('#addEmailForm input[name="email"]').value;
    
    try {
        const response = await fetch('{{ route('vendor.emails.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email })
        });
        
        const data = await response.json();
        
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('addEmailModal')).hide();
            currentEmailId = data.email_id;
            document.getElementById('addEmailForm').reset();
            
            // Show verify modal
            const verifyModal = new bootstrap.Modal(document.getElementById('verifyEmailModal'));
            verifyModal.show();
            
            showAlert('Email added successfully! OTP sent to your email.', 'success');
        } else {
            showAlert(data.message || 'Failed to add email', 'danger');
        }
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
});

document.getElementById('verifyEmailForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const otp = document.querySelector('#verifyEmailForm input[name="otp"]').value;
    
    try {
        const response = await fetch(`/vendor/emails/${currentEmailId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ otp })
        });
        
        const data = await response.json();
        
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('verifyEmailModal')).hide();
            document.getElementById('verifyEmailForm').reset();
            location.reload();
        } else {
            showAlert(data.message || 'Invalid OTP', 'danger');
        }
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
});

document.getElementById('resendOtpBtn').addEventListener('click', async () => {
    try {
        const response = await fetch(`/vendor/emails/${currentEmailId}/resend-otp`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        showAlert(data.message, data.success ? 'success' : 'warning');
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
});

async function verifyEmail(emailId) {
    currentEmailId = emailId;
    const verifyModal = new bootstrap.Modal(document.getElementById('verifyEmailModal'));
    verifyModal.show();
}

async function makePrimary(emailId) {
    try {
        const response = await fetch(`/vendor/emails/${emailId}/make-primary`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
}

async function deleteEmail(emailId) {
    if (!confirm('Are you sure you want to delete this email?')) return;
    
    try {
        const response = await fetch(`/vendor/emails/${emailId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            showAlert(data.message, 'danger');
        }
    } catch (error) {
        console.error(error);
        showAlert('An error occurred', 'danger');
    }
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.row'));
}
</script>
@endsection
