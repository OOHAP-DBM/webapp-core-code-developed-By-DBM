<?php

namespace Modules\Notifications\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Notifications\DatabaseNotification;

class VendorNotificationController extends Controller
{
    // Get all notifications for the authenticated vendor
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()->where('data->role', 'vendor')->latest()->paginate(20);
        return response()->json($notifications);
    }

    // Get unread notification count for vendor
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->where('data->role', 'vendor')->count();
        return response()->json(['unread_count' => $count]);
    }

    // Mark a vendor notification as read
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('data->role', 'vendor')->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    // Mark all vendor notifications as read
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications()->where('data->role', 'vendor')->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    // Delete a vendor notification
    public function delete($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('data->role', 'vendor')->findOrFail($id);
        $notification->delete();
        return response()->json(['success' => true]);
    }
}
