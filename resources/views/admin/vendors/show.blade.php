@extends('layouts.admin')

@section('title', 'Vendor Details')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Vendor Details</h1>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">{{ $vendor->name }}</h5>
            <p class="card-text"><strong>Email:</strong> {{ $vendor->email }}</p>
            <p class="card-text"><strong>Status:</strong> {{ $vendor->status }}</p>
            <p class="card-text"><strong>Created At:</strong> {{ $vendor->created_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>
    <a href="{{ route('admin.vendors.index') }}" class="btn btn-secondary">Back to Vendors</a>
</div>
@endsection
