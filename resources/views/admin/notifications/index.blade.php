@extends('layouts.admin')

@section('page-title', 'Notifications')

@section('content')
<div class="p-6">
        <div class="mx-5  my-3 md:mx-0 flex md:items-center md:justify-between ">
            <h2 class="text-md md:text-xl font-bold text-gray-900">
            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            @forelse($notifications as $notification)
                <div class="px-4 py-3 border-b border-gray-100 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }} hover:bg-blue-100 cursor-pointer"
                     onclick="window.location='{{ route('admin.notifications.open', $notification->id) }}'">
                    <div class="flex items-start gap-3">
                        <a href="{{ route('admin.notifications.open', $notification->id) }}" class="flex-1 min-w-0 block">
                            <p class="text-sm font-medium text-gray-900">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </a>
                        @if(!$notification->read_at)
                            <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" onclick="event.stopPropagation();" onsubmit="event.stopPropagation();">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800" onclick="event.stopPropagation();">Mark as read</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p class="mt-4 text-lg text-gray-600">No notifications yet</p>
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection