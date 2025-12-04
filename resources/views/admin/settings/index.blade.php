@extends('layouts.admin')

@section('title', 'Settings Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Settings Management</h1>
            <p class="text-muted mb-0">Configure system-wide settings for OOHAPP</p>
        </div>
        <div class="btn-group">
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Clear Cache
                </button>
            </form>
            <form action="{{ route('admin.settings.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reset all settings to default values?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset to Default
                </button>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Settings Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- General Settings -->
            @if(isset($groupedSettings['general']))
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i> General Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($groupedSettings['general'] as $setting)
                                @include('admin.settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Booking Settings -->
            @if(isset($groupedSettings['booking']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i> Booking Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($groupedSettings['booking'] as $setting)
                                @include('admin.settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Settings -->
            @if(isset($groupedSettings['payment']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i> Payment Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($groupedSettings['payment'] as $setting)
                                @include('admin.settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Commission Settings -->
            @if(isset($groupedSettings['commission']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-percent me-2"></i> Commission Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($groupedSettings['commission'] as $setting)
                                @include('admin.settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- DOOH Settings -->
            @if(isset($groupedSettings['dooh']))
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-display me-2"></i> DOOH Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($groupedSettings['dooh'] as $setting)
                                @include('admin.settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notification Settings -->
            @if(isset($groupedSettings['notification']))
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-bell me-2"></i> Notification Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($groupedSettings['notification'] as $setting)
                                @include('admin.settings.partials.setting-field', ['setting' => $setting])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-save me-2"></i> Save All Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
@endpush
@endsection
