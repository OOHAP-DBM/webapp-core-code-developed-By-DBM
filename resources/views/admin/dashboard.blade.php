@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Admin Dashboard</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-6">{{ $userCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Bookings</h5>
                    <p class="card-text display-6">{{ $bookingCount }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="alert alert-info mt-4">
        <strong>Welcome, Admin!</strong> This is a placeholder dashboard. Customize as needed.
    </div>
</div>
@endsection
