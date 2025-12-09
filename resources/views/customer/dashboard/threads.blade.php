@extends('layouts.customer')

@section('title', 'My Messages')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Messages</h1>
            <p class="text-muted">Communicate with vendors and support</p>
        </div>
        <button class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Message
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Threads</h6>
                    <h3 class="mb-0">{{ $summary['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Unread</h6>
                    <h3 class="mb-0 text-danger">{{ $summary['unread'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Active</h6>
                    <h3 class="mb-0 text-success">{{ $summary['active'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search messages..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="unread_only" value="1" 
                               id="unreadOnly" {{ request('unread_only') ? 'checked' : '' }}>
                        <label class="form-check-label" for="unreadOnly">
                            Unread only
                        </label>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Threads List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($threads as $thread)
                <a href="{{ route('customer.threads.show', $thread->id) }}" 
                   class="list-group-item list-group-item-action {{ $thread->has_unread_messages ? 'bg-light' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                @if($thread->has_unread_messages)
                                <span class="badge bg-danger me-2">New</span>
                                @endif
                                <h6 class="mb-0">{{ $thread->subject }}</h6>
                            </div>
                            <p class="mb-1 text-muted small">{{ Str::limit($thread->last_message ?? 'No messages yet', 100) }}</p>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($thread->updated_at)->diffForHumans() }}
                            </small>
                        </div>
                        <div class="text-end">
                            @php
                            $statusColors = ['active' => 'success', 'closed' => 'secondary'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$thread->status] ?? 'secondary' }}">
                                {{ ucfirst($thread->status) }}
                            </span>
                        </div>
                    </div>
                </a>
                @empty
                <div class="p-5 text-center">
                    <i class="bi bi-chat-dots fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">No messages found</p>
                </div>
                @endforelse
            </div>
        </div>
        @if($threads->hasPages())
        <div class="card-footer bg-white">{{ $threads->links() }}</div>
        @endif
    </div>
</div>
@endsection
