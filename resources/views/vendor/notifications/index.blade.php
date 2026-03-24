@extends('layouts.vendor')

@section('page-title', 'Notifications')

@section('content')
{{-- Added relative and z-10 to ensure it stays above background elements --}}
<div class="p-3 md:p-6 relative z-10">
    <div class="max-w-4xl mx-auto">
        
         <!-- HEADER -->
       <div class="mx-5  my-3 md:mx-0 flex items-center justify-between ">
            <h2 class="text-md md:text-xl font-bold text-gray-900">
                Notifications
            </h2>

            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('vendor.notifications.read-all') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class=" sm:w-auto text-center px-1 md:px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @forelse($notifications as $notification)
                <div class="px-4 py-4 border-b border-gray-100 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/50' }} hover:bg-gray-50 transition-colors cursor-pointer"
                     onclick="window.location='{{ route('notifications.open', $notification->id) }}'">
                    
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1 break-words">
                                {{ $notification->data['message'] ?? '' }}
                            </p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-xs text-gray-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                {{-- Unread indicator dot --}}
                                @if(!$notification->read_at)
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                @endif
                            </div>
                        </div>

                        {{-- Individual Mark as Read --}}
                        @if(!$notification->read_at)
                            <div class="flex-shrink-0">
                                <form action="{{ route('vendor.notifications.read', $notification->id) }}" 
                                      method="POST" 
                                      onclick="event.stopPropagation();" 
                                      onsubmit="event.stopPropagation();">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-blue-600 hover:text-blue-800 whitespace-nowrap bg-blue-50 px-2 py-1 rounded">
                                        Mark read
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 font-medium">No notifications yet</p>
                    <p class="text-sm text-gray-400 mt-1">We'll notify you when something happens.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="mt-6 px-2">
                {{ $notifications->links('pagination.vendor-compact') }}
            </div>
        @endif
    </div>
</div>
@endsection