@extends('layouts.app')

@section('title', 'Notification Preferences')

@section('content')
<div class="max-w-lg mx-auto bg-white rounded-xl shadow p-6 mt-8">
    <h2 class="text-lg font-semibold mb-4">Global Notification Preferences</h2>
    @if(session('success'))
        <div class="mb-4">
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded">{{ session('success') }}</div>
        </div>
    @endif
    <form method="POST" action="{{ route('notification.global-preferences.update') }}">
        @csrf
        <div class="mb-4">
            <label class="inline-flex items-center mr-4">
                <input type="checkbox" name="notification_email" value="1" {{ $user->notification_email ? 'checked' : '' }}>
                <span class="ml-2">Email Notifications</span>
            </label>
            <label class="inline-flex items-center mr-4">
                <input type="checkbox" name="notification_push" value="1" {{ $user->notification_push ? 'checked' : '' }}>
                <span class="ml-2">Push Notifications</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="notification_whatsapp" value="1" {{ $user->notification_whatsapp ? 'checked' : '' }}>
                <span class="ml-2">WhatsApp Notifications</span>
            </label>
        </div>
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Save Preferences</button>
    </form>
</div>
@endsection
