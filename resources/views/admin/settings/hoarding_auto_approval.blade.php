@extends('layouts.admin')

@section('content')
<div class="max-w-xl mx-auto mt-12 bg-white shadow-lg rounded-lg p-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Hoarding Auto Approval Setting
    </h2>
    <form method="POST" action="{{ route('admin.settings.hoarding_auto_approval.update') }}" class="space-y-6">
        @csrf
        <div>
            <label for="auto_hoarding_approval" class="block text-sm font-medium text-gray-700 mb-2">Auto Approve Hoardings?</label>
            <select name="auto_hoarding_approval" id="auto_hoarding_approval" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-gray-700">
                <option value="1" {{ config('hoarding.auto_hoarding_approval') ? 'selected' : '' }}>Yes</option>
                <option value="0" {{ !config('hoarding.auto_hoarding_approval') ? 'selected' : '' }}>No</option>
            </select>
        </div>
        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-md shadow transition">Save Setting</button>
        @if(session('success'))
            <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded">
                {{ session('success') }}
            </div>
        @endif
    </form>
</div>
@endsection