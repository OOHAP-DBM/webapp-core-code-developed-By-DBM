<?php

namespace Modules\Notifications\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    // Get all notifications for the authenticated user
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->paginate(20);
        return response()->json($notifications);
    }

    // Get unread notification count
    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()->count();
        return response()->json(['unread_count' => $count]);
    }

    // Mark a notification as read
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    // Mark all notifications as read
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    // Delete a notification
    public function delete($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
        return response()->json(['success' => true]);
    }

    // Get notification preferences (stub)
    public function preferences(Request $request)
    {
        // Implement user preferences logic as needed
        return response()->json(['email' => true, 'push' => true]);
    }

    // Update notification preferences (stub)
    public function updatePreferences(Request $request)
    {
        // Implement update logic as needed
        return response()->json(['success' => true]);
    }
}
