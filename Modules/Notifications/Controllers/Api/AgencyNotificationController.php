<?php

namespace Modules\Notifications\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;

class AgencyNotificationController extends Controller
{
    // Get all notifications for the authenticated agency user
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->where('data->role', 'agency')
            ->where('data->tenant_id', $user->tenant_id)
            ->latest()->paginate(20);
        return response()->json($notifications);
    }

    // Get unread notification count for agency
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()
            ->where('data->role', 'agency')
            ->where('data->tenant_id', $user->tenant_id)
            ->count();
        return response()->json(['unread_count' => $count]);
    }

    // Mark a notification as read
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()
            ->where('data->role', 'agency')
            ->where('data->tenant_id', $user->tenant_id)
            ->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    // Mark all notifications as read
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications()
            ->where('data->role', 'agency')
            ->where('data->tenant_id', $user->tenant_id)
            ->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    // Delete a notification
    public function delete($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()
            ->where('data->role', 'agency')
            ->where('data->tenant_id', $user->tenant_id)
            ->findOrFail($id);
        $notification->delete();
        return response()->json(['success' => true]);
    }
}
