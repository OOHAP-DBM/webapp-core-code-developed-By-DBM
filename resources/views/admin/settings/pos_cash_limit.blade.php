@extends('layouts.admin')

@section('content')
<div class="container max-w-xl mx-auto mt-8">
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-lg font-bold mb-4">POS Cash Limit Setting</h2>
        <form method="POST" action="{{ route('admin.settings.pos-cash-limit.update') }}">
            @csrf
            @method('POST')
            <div class="mb-4">
                <label for="pos_cash_limit" class="block text-sm font-semibold mb-1">POS Cash Limit (₹)</label>
                <input type="number" step="0.01" min="0" name="pos_cash_limit" id="pos_cash_limit" class="form-input w-full border border-gray-300 rounded px-3 py-2" value="{{ old('pos_cash_limit', $posCashLimit) }}" required>
                @error('pos_cash_limit')
                    <div class="text-red-600 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded">Save</button>
        </form>
    </div>
</div>
@endsection
