@extends('layouts.app')

@section('content')
<div class="container text-center">
    <h1 class="display-4">404 - Page Not Found</h1>
    <p class="lead">Sorry, the page you are looking for does not exist.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Return to Home</a>
</div>
@endsection