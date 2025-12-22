@extends('layouts.admin')

@section('title', 'Vendors')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">All Vendors</h1>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td>{{ $vendor->id }}</td>
                        <td>{{ $vendor->name }}</td>
                        <td>{{ $vendor->email }}</td>
                        <td>{{ $vendor->status }}</td>
                        <td>{{ $vendor->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.vendors.show', $vendor->id) }}" class="btn btn-sm btn-primary">View</a>
                            @if($vendor->status !== 'active')
                                <form action="{{ route('admin.vendors.approve', $vendor->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                            @endif
                            @if($vendor->status !== 'suspended')
                                <form action="{{ route('admin.vendors.suspend', $vendor->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Suspend</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No vendors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $vendors->links() }}
    </div>
</div>
@endsection
