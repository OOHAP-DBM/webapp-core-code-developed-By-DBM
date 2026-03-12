@extends('layouts.admin')

@section('title', 'Settings Management')

@section('content')
<style>
    .settings-tabs {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 2rem;
    }
    .settings-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s;
    }
    .settings-tabs .nav-link:hover {
        color: #0d6efd;
        background: #f8f9fa;
        border-bottom-color: #dee2e6;
    }
    .settings-tabs .nav-link.active {
        color: #0d6efd;
        background: transparent;
        border-bottom-color: #0d6efd;
    }
    .setting-card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        margin-bottom: 1.5rem;
    }
    .setting-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 1.25rem;
    }
    .setting-group {
        background: white;
        border-radius: 0.5rem;
        padding: 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .setting-item {
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .setting-item:hover {
        background: #f8f9fa;
    }
    .setting-label {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .setting-description {
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
    }
    .setting-input {
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.75rem;
        transition: all 0.2s;
    }
    .setting-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    .badge-setting-type {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        font-weight: 500;
    }
    .save-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: transform 0.2s;
    }
    .save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(102, 126, 234, 0.3);
    }
    .tab-icon {
        margin-right: 0.5rem;
    }
    .sms-panel {
        background: #f3f4f6;
        border: 1px solid #d6d9de;
        border-radius: 4px;
        padding: 1rem;
    }
    .sms-info-banner {
        background: #eaf2fc;
        border: 1px solid #d0e0f5;
        color: #3f70a8;
        border-radius: 4px;
        padding: 0.9rem 1rem;
        font-size: 0.95rem;
        margin-bottom: 1rem;
    }
    .sms-accordion .accordion-item {
        border: 1px solid #cfd6df;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    .sms-accordion .accordion-button {
        background: #bcc7d3;
        color: #1f2d3d;
        font-weight: 700;
        padding: 0.6rem 1rem;
        border-radius: 0;
        box-shadow: none;
    }
    .sms-accordion .accordion-button:not(.collapsed) {
        background: #bcc7d3;
        color: #1f2d3d;
        box-shadow: none;
    }
    .sms-accordion .accordion-button:focus {
        box-shadow: none;
    }
    .sms-accordion .accordion-body {
        background: #fff;
        padding: 1.2rem 1.25rem;
    }
    .sms-accordion-panel {
        display: none;
    }
    .sms-accordion-panel.is-open {
        display: block;
    }
    .sms-gateway-help {
        color: #5f6f7f;
        font-size: 0.95rem;
        line-height: 1.45;
        margin-bottom: 0.9rem;
    }
    .sms-inline-link {
        color: #4c6fb3;
        text-decoration: none;
    }
    .sms-inline-link:hover {
        text-decoration: underline;
    }
    .sms-active-title {
        color: #34495e;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .sms-active-wrap {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }
    .sms-active-wrap .form-check {
        margin-bottom: 0;
    }
    .sms-active-wrap .form-check-label {
        color: #34495e;
    }
    .sms-fields {
        display: grid;
        gap: 0.75rem;
    }
    .sms-field-row {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .sms-field-row .form-label {
        width: 190px;
        min-width: 190px;
        margin-bottom: 0;
        color: #1f2d3d;
        font-weight: 500;
    }
    .sms-field-row .setting-input {
        width: 320px;
        max-width: 100%;
    }
    .sms-field-row .setting-input.sms-input-short {
        width: 120px;
    }
    @media (max-width: 768px) {
        .sms-field-row {
            flex-direction: column;
            align-items: stretch;
            gap: 0.35rem;
        }
        .sms-field-row .form-label {
            width: auto;
            min-width: 0;
        }
        .sms-field-row .setting-input,
        .sms-field-row .setting-input.sms-input-short {
            width: 100%;
        }
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">⚙️ System Settings</h1>
            <p class="text-muted mb-0">Configure and manage all system settings</p>
        </div>
        <div class="btn-group">
            <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Clear Cache
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

    <!-- Settings Navigation Tabs -->
    {{-- <ul class="nav nav-tabs settings-tabs" role="tablist">
        @foreach($groups as $groupKey => $groupLabel)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $activeGroup === $groupKey ? 'active' : '' }}" 
               href="{{ route('admin.settings.index', ['group' => $groupKey]) }}">
                @switch($groupKey)
                    @case('general')
                        <i class="bi bi-gear tab-icon"></i>
                        @break
                    @case('booking')
                        <i class="bi bi-calendar-check tab-icon"></i>
                        @break
                    @case('payment')
                        <i class="bi bi-credit-card tab-icon"></i>
                        @break
                    @case('commission')
                        <i class="bi bi-percent tab-icon"></i>
                        @break
                    @case('notification')
                        <i class="bi bi-bell tab-icon"></i>
                        @break
                    @case('sms')
                        <i class="bi bi-chat-dots tab-icon"></i>
                        @break
                    @case('kyc')
                        <i class="bi bi-shield-check tab-icon"></i>
                        @break
                    @case('dooh')
                        <i class="bi bi-display tab-icon"></i>
                        @break
                    @case('automation')
                        <i class="bi bi-robot tab-icon"></i>
                        @break
                    @case('cancellation')
                        <i class="bi bi-x-circle tab-icon"></i>
                        @break
                    @case('refund')
                        <i class="bi bi-cash-coin tab-icon"></i>
                        @break
                @endswitch
                {{ $groupLabel }}
            </a>
        </li>
        @endforeach
    </ul> --}}

    <!-- Settings Form -->
    @if($activeGroup === 'sms')
    @php
        $settingsByKey = $settings->keyBy('key');

        $settingValue = function ($key, $default = '') use ($settingsByKey) {
            $setting = $settingsByKey->get($key);

            if (!$setting) {
                return $default;
            }

            $value = $setting->getTypedValue();

            if ($value === null || $value === '') {
                return $default;
            }

            return $value;
        };

        $activeGateway = strtolower((string) $settingValue('sms_active_gateway', 'twilio'));
        if (!in_array($activeGateway, ['twilio', 'msg91', 'clickatell'])) {
            $activeGateway = 'twilio';
        }

        $smsEnabled = (bool) $settingValue('sms_service_enabled', true);
        $twilioActive = $activeGateway === 'twilio';
        $msg91Active = $activeGateway === 'msg91';
        $clickatellActive = $activeGateway === 'clickatell';
        $expandedGateway = in_array($activeGateway, ['twilio', 'msg91', 'clickatell']) ? $activeGateway : 'twilio';
    @endphp
    <form action="{{ route('admin.settings.update') }}" method="POST" id="sms-gateway-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="{{ $activeGroup }}">
        <input type="hidden" name="settings[sms_service_enabled]" id="sms_service_enabled_hidden" value="{{ $smsEnabled ? '1' : '0' }}">
        <input type="hidden" name="settings[sms_active_gateway]" id="sms_active_gateway_hidden" value="{{ $activeGateway }}">
        <input type="hidden" name="settings[sms_twilio_active]" id="sms_twilio_active_hidden" value="{{ $twilioActive ? '1' : '0' }}">
        <input type="hidden" name="settings[sms_msg91_active]" id="sms_msg91_active_hidden" value="{{ $msg91Active ? '1' : '0' }}">
        <input type="hidden" name="settings[sms_clickatell_active]" id="sms_clickatell_active_hidden" value="{{ $clickatellActive ? '1' : '0' }}">

        <div class="setting-group sms-panel">
            <h5 class="mb-3 fw-bold text-dark">SMS</h5>

            <div class="sms-info-banner">Only 1 active SMS gateway is allowed</div>

            <div class="accordion sms-accordion" id="smsGatewayAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingClickatell">
                        <button class="accordion-button sms-accordion-toggle {{ $expandedGateway === 'clickatell' ? '' : 'collapsed' }}" type="button" data-gateway="clickatell" aria-expanded="{{ $expandedGateway === 'clickatell' ? 'true' : 'false' }}" aria-controls="panelClickatell">
                            Clickatell
                        </button>
                    </h2>
                    <div id="panelClickatell" class="sms-accordion-panel {{ $expandedGateway === 'clickatell' ? 'is-open' : '' }}" data-gateway-panel="clickatell" aria-labelledby="headingClickatell">
                        <div class="accordion-body">
                            <div class="sms-fields">
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_clickatell_api_key">API Key</label>
                                    <input type="text" class="form-control setting-input" id="sms_clickatell_api_key" name="settings[sms_clickatell_api_key]" value="{{ $settingValue('sms_clickatell_api_key', '') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_clickatell_from">Sender ID</label>
                                    <input type="text" class="form-control setting-input" id="sms_clickatell_from" name="settings[sms_clickatell_from]" value="{{ $settingValue('sms_clickatell_from', '') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_clickatell_base_url">API URL</label>
                                    <input type="text" class="form-control setting-input" id="sms_clickatell_base_url" name="settings[sms_clickatell_base_url]" value="{{ $settingValue('sms_clickatell_base_url', 'https://platform.clickatell.com/messages/http/send') }}">
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="sms-active-title">Active</div>
                                <div class="sms-active-wrap">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input sms-active-yes" type="radio" name="clickatell_active_ui" id="clickatell_active_yes" value="1" data-gateway="clickatell" {{ $clickatellActive ? 'checked' : '' }}>
                                        <label class="form-check-label" for="clickatell_active_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input sms-active-no" type="radio" name="clickatell_active_ui" id="clickatell_active_no" value="0" data-gateway="clickatell" {{ !$clickatellActive ? 'checked' : '' }}>
                                        <label class="form-check-label" for="clickatell_active_no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingMsg91">
                        <button class="accordion-button sms-accordion-toggle {{ $expandedGateway === 'msg91' ? '' : 'collapsed' }}" type="button" data-gateway="msg91" aria-expanded="{{ $expandedGateway === 'msg91' ? 'true' : 'false' }}" aria-controls="panelMsg91">
                            MSG91
                        </button>
                    </h2>
                    <div id="panelMsg91" class="sms-accordion-panel {{ $expandedGateway === 'msg91' ? 'is-open' : '' }}" data-gateway-panel="msg91" aria-labelledby="headingMsg91">
                        <div class="accordion-body">
                            <div class="sms-fields">
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_msg91_auth_key">Auth Key</label>
                                    <input type="text" class="form-control setting-input" id="sms_msg91_auth_key" name="settings[sms_msg91_auth_key]" value="{{ $settingValue('sms_msg91_auth_key', '') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_msg91_sender_id">Sender ID</label>
                                    <input type="text" class="form-control setting-input" id="sms_msg91_sender_id" name="settings[sms_msg91_sender_id]" value="{{ $settingValue('sms_msg91_sender_id', '') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_msg91_route">Route</label>
                                    <input type="text" class="form-control setting-input sms-input-short" id="sms_msg91_route" name="settings[sms_msg91_route]" value="{{ $settingValue('sms_msg91_route', '4') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_msg91_country">Country</label>
                                    <input type="text" class="form-control setting-input sms-input-short" id="sms_msg91_country" name="settings[sms_msg91_country]" value="{{ $settingValue('sms_msg91_country', '91') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_msg91_base_url">API URL</label>
                                    <input type="text" class="form-control setting-input" id="sms_msg91_base_url" name="settings[sms_msg91_base_url]" value="{{ $settingValue('sms_msg91_base_url', 'https://api.msg91.com/api/v2/sendsms') }}">
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="sms-active-title">Active</div>
                                <div class="sms-active-wrap">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input sms-active-yes" type="radio" name="msg91_active_ui" id="msg91_active_yes" value="1" data-gateway="msg91" {{ $msg91Active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="msg91_active_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input sms-active-no" type="radio" name="msg91_active_ui" id="msg91_active_no" value="0" data-gateway="msg91" {{ !$msg91Active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="msg91_active_no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwilio">
                        <button class="accordion-button sms-accordion-toggle {{ $expandedGateway === 'twilio' ? '' : 'collapsed' }}" type="button" data-gateway="twilio" aria-expanded="{{ $expandedGateway === 'twilio' ? 'true' : 'false' }}" aria-controls="panelTwilio">
                            Twilio
                        </button>
                    </h2>
                    <div id="panelTwilio" class="sms-accordion-panel {{ $expandedGateway === 'twilio' ? 'is-open' : '' }}" data-gateway-panel="twilio" aria-labelledby="headingTwilio">
                        <div class="accordion-body">
                            <p class="sms-gateway-help">
                                Twilio SMS integration is one way messaging, means that your customers won't be able to reply to the SMS.
                                Phone numbers must be in format
                                <a href="https://www.twilio.com/docs/glossary/what-e164" target="_blank" class="sms-inline-link">E164</a>.
                                Click <a href="https://www.twilio.com/docs/glossary/what-e164" target="_blank" class="sms-inline-link">here</a> to read more how phone numbers should be formatted.
                            </p>

                            <hr class="my-3">

                            <div class="sms-fields">
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_twilio_sid">Account SID</label>
                                    <input type="text" class="form-control setting-input" id="sms_twilio_sid" name="settings[sms_twilio_sid]" value="{{ $settingValue('sms_twilio_sid', '') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_twilio_auth_token">Auth Token</label>
                                    <input type="text" class="form-control setting-input" id="sms_twilio_auth_token" name="settings[sms_twilio_auth_token]" value="{{ $settingValue('sms_twilio_auth_token', '') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_twilio_from">Twilio Phone Number</label>
                                    <input type="text" class="form-control setting-input" id="sms_twilio_from" name="settings[sms_twilio_from]" value="{{ $settingValue('sms_twilio_from', '') }}">
                                </div>
                                <div class="sms-field-row">
                                    <label class="form-label" for="sms_twilio_alphanumeric_sender_id">Alphanumeric Sender ID</label>
                                    <input type="text" class="form-control setting-input" id="sms_twilio_alphanumeric_sender_id" name="settings[sms_twilio_alphanumeric_sender_id]" value="{{ $settingValue('sms_twilio_alphanumeric_sender_id', '') }}">
                                </div>
                            </div>

                            <div class="mt-2 mb-2">
                                <a href="https://www.twilio.com/blog/personalize-sms-alphanumeric-sender-id" target="_blank" class="sms-inline-link">https://www.twilio.com/blog/personalize-sms-alphanumeric-sender-id</a>
                            </div>

                            <div class="mt-2">
                                <div class="sms-active-title">Active</div>
                                <div class="sms-active-wrap">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input sms-active-yes" type="radio" name="twilio_active_ui" id="twilio_active_yes" value="1" data-gateway="twilio" {{ $twilioActive ? 'checked' : '' }}>
                                        <label class="form-check-label" for="twilio_active_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input sms-active-no" type="radio" name="twilio_active_ui" id="twilio_active_no" value="0" data-gateway="twilio" {{ !$twilioActive ? 'checked' : '' }}>
                                        <label class="form-check-label" for="twilio_active_no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary save-btn">
                    <i class="bi bi-save me-2"></i> Save SMS Gateway Configuration
                </button>
            </div>
        </div>
    </form>
    @elseif($settings->isEmpty())
    <div class="setting-group text-center py-5">
        <i class="bi bi-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
        <h4 class="mt-3 text-muted">No Settings Found</h4>
        <p class="text-muted">There are no settings configured for this group yet.</p>
        <p class="text-muted small">Run the settings seeder to populate default values.</p>
    </div>
    @else
    <form action="{{ route('admin.settings.update') }}" method="POST" id="settings-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="{{ $activeGroup }}">

        <div class="setting-group">
            @foreach($settings as $setting)
            <div class="setting-item">
                <div class="setting-label">
                    {{ ucwords(str_replace('_', ' ', str_replace($activeGroup . '.', '', $setting->key))) }}
                    <span class="badge badge-setting-type bg-secondary">{{ strtoupper($setting->type) }}</span>
                </div>

                @if($setting->description)
                <div class="setting-description">{{ $setting->description }}</div>
                @endif

                <div class="setting-control">
                    @if($setting->type === 'boolean')
                    <div class="form-check form-switch">
                        <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="setting_{{ $setting->key }}"
                            name="settings[{{ $setting->key }}]"
                            value="1"
                            {{ $setting->getTypedValue() ? 'checked' : '' }}
                            style="width: 3rem; height: 1.5rem; cursor: pointer;">
                        <label class="form-check-label ms-2" for="setting_{{ $setting->key }}" style="cursor: pointer;">
                            {{ $setting->getTypedValue() ? 'Enabled' : 'Disabled' }}
                        </label>
                    </div>
                    @elseif($setting->type === 'integer')
                    <input
                        type="number"
                        class="form-control setting-input"
                        id="setting_{{ $setting->key }}"
                        name="settings[{{ $setting->key }}]"
                        value="{{ $setting->getTypedValue() }}"
                        step="1">
                    @elseif($setting->type === 'float')
                    <input
                        type="number"
                        class="form-control setting-input"
                        id="setting_{{ $setting->key }}"
                        name="settings[{{ $setting->key }}]"
                        value="{{ $setting->getTypedValue() }}"
                        step="0.01">
                    @elseif(in_array($setting->type, ['json', 'array']))
                    <textarea
                        class="form-control setting-input font-monospace"
                        id="setting_{{ $setting->key }}"
                        name="settings[{{ $setting->key }}]"
                        rows="5"
                        style="font-size: 0.875rem;">{{ is_array($setting->getTypedValue()) ? json_encode($setting->getTypedValue(), JSON_PRETTY_PRINT) : $setting->value }}</textarea>
                    <small class="text-muted">JSON format required</small>
                    @else
                    <input
                        type="text"
                        class="form-control setting-input"
                        id="setting_{{ $setting->key }}"
                        name="settings[{{ $setting->key }}]"
                        value="{{ $setting->getTypedValue() }}">
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Submit Button -->
            <div class="d-flex justify-content-end mt-4 pt-3">
                <button type="submit" class="btn btn-primary save-btn">
                    <i class="bi bi-save me-2"></i> Save {{ $groups[$activeGroup] }}
                </button>
            </div>
        </div>
    </form>
    @endif
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

    // Update checkbox label text on toggle
    document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (label && label.classList.contains('form-check-label')) {
                label.textContent = this.checked ? 'Enabled' : 'Disabled';
            }
        });
    });

    // Validate JSON fields before submit
    const settingsForm = document.getElementById('sms-gateway-form') || document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            const jsonFields = document.querySelectorAll('textarea[name^="settings"][name$="]"]');
            let hasError = false;

            jsonFields.forEach(field => {
                const fieldName = field.getAttribute('name');
                // Check if this is a JSON/array field by looking for the badge
                const settingItem = field.closest('.setting-item');
                const badge = settingItem?.querySelector('.badge-setting-type');

                if (badge && (badge.textContent.includes('JSON') || badge.textContent.includes('ARRAY'))) {
                    try {
                        JSON.parse(field.value);
                        field.classList.remove('is-invalid');
                    } catch (error) {
                        hasError = true;
                        field.classList.add('is-invalid');
                        alert('Invalid JSON format in field: ' + fieldName);
                        e.preventDefault();
                    }
                }
            });
        });
    }

    // SMS gateway active yes/no sync (screenshot-style UI)
    const smsGatewayForm = document.getElementById('sms-gateway-form');
    if (smsGatewayForm) {
        const activeGatewayHidden = document.getElementById('sms_active_gateway_hidden');
        const serviceEnabledHidden = document.getElementById('sms_service_enabled_hidden');
        const accordionToggles = smsGatewayForm.querySelectorAll('.sms-accordion-toggle');
        const accordionPanels = smsGatewayForm.querySelectorAll('.sms-accordion-panel');

        const gatewayHiddenInputs = {
            twilio: document.getElementById('sms_twilio_active_hidden'),
            msg91: document.getElementById('sms_msg91_active_hidden'),
            clickatell: document.getElementById('sms_clickatell_active_hidden'),
        };

        const openGatewayPanel = (gateway) => {
            accordionToggles.forEach((toggle) => {
                const isActiveToggle = toggle.getAttribute('data-gateway') === gateway;
                toggle.classList.toggle('collapsed', !isActiveToggle);
                toggle.setAttribute('aria-expanded', isActiveToggle ? 'true' : 'false');
            });

            accordionPanels.forEach((panel) => {
                const isActivePanel = panel.getAttribute('data-gateway-panel') === gateway;
                panel.classList.toggle('is-open', isActivePanel);
            });
        };

        accordionToggles.forEach((toggle) => {
            toggle.addEventListener('click', function () {
                const gateway = this.getAttribute('data-gateway');
                if (gateway) {
                    openGatewayPanel(gateway);
                }
            });
        });

        const setActiveGateway = (gateway) => {
            ['twilio', 'msg91', 'clickatell'].forEach((key) => {
                const isActive = key === gateway;
                const yes = document.querySelector(`#${key}_active_yes`);
                const no = document.querySelector(`#${key}_active_no`);

                if (yes) yes.checked = isActive;
                if (no) no.checked = !isActive;
                if (gatewayHiddenInputs[key]) gatewayHiddenInputs[key].value = isActive ? '1' : '0';
            });

            if (activeGatewayHidden) {
                activeGatewayHidden.value = gateway;
            }

            openGatewayPanel(gateway);
        };

        document.querySelectorAll('.sms-active-yes').forEach((radio) => {
            radio.addEventListener('change', function () {
                if (!this.checked) {
                    return;
                }

                const gateway = this.getAttribute('data-gateway');
                if (gateway) {
                    setActiveGateway(gateway);
                }
            });
        });

        document.querySelectorAll('.sms-active-no').forEach((radio) => {
            radio.addEventListener('change', function () {
                if (!this.checked) {
                    return;
                }

                const gateway = this.getAttribute('data-gateway');
                if (!gateway) {
                    return;
                }

                if (gatewayHiddenInputs[gateway]) {
                    gatewayHiddenInputs[gateway].value = '0';
                }

                if (activeGatewayHidden && activeGatewayHidden.value === gateway) {
                    activeGatewayHidden.value = '';
                }
            });
        });

        const initialActiveYes = document.querySelector('.sms-active-yes:checked');
        if (initialActiveYes) {
            const gateway = initialActiveYes.getAttribute('data-gateway');
            if (gateway) {
                setActiveGateway(gateway);
            }
        }

        smsGatewayForm.addEventListener('submit', function (e) {
            const activeSelections = document.querySelectorAll('.sms-active-yes:checked');
            const smsEnabled = (serviceEnabledHidden?.value ?? '1') === '1';

            if (smsEnabled && activeSelections.length !== 1) {
                e.preventDefault();
                alert('Only 1 active SMS gateway is allowed.');
                return;
            }

            if (activeSelections.length === 1) {
                const gateway = activeSelections[0].getAttribute('data-gateway');
                if (gateway) {
                    setActiveGateway(gateway);
                }
            }
        });
    }
</script>
@endpush
@endsection
