<?php


namespace App\Http\Controllers;

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

    // Get notification preferences
    public function preferences(Request $request)
    {
        $user = Auth::user();
        $preferences = [
            'email' => $user->notification_email,
            'push' => $user->notification_push,
            'whatsapp' => $user->notification_whatsapp,
        ];
        // Vendor: multiple emails and preferences
        if ($user->isVendor()) {
            $vendorProfile = $user->vendorProfile;
            $preferences['primary_email'] = $user->email;
            $preferences['additional_emails'] = $vendorProfile?->additional_emails ?? [];
            $preferences['email_preferences'] = $vendorProfile?->email_preferences ?? [];
        }
        return response()->json($preferences);
    }

    // Update notification preferences
    public function updatePreferences(Request $request)
    {
        \Log::info('Updating notification preferences', ['user_id' => Auth::id(), 'data' => $request->all()]);
        $user = Auth::user();
        $data = $request->validate([
            'email' => 'boolean',
            'push' => 'boolean',
            'whatsapp' => 'boolean',
            'primary_email' => 'nullable|email',
            'additional_emails' => 'nullable|array',
            'additional_emails.*' => 'email',
            'email_preferences' => 'nullable|array',
        ]);

        // Update user preferences
        $user->notification_email = $data['email'] ?? $user->notification_email;
        $user->notification_push = $data['push'] ?? $user->notification_push;
        $user->notification_whatsapp = $data['whatsapp'] ?? $user->notification_whatsapp;

           if ($user->isVendor()) {
            $vendorProfile = $user->vendorProfile;
            if ($vendorProfile) {
                if (!empty($data['primary_email'])) {
                    $user->email = $data['primary_email'];
                    $user->save();
                }
                if (isset($data['additional_emails'])) {
                    $vendorProfile->additional_emails = $data['additional_emails'];
                }
                if (isset($data['email_preferences'])) {
                    $vendorProfile->email_preferences = $data['email_preferences'];
                }
                $vendorProfile->save();
            }
        }
        else {
            if (!empty($data['primary_email'])) {
                $user->email = $data['primary_email'];
            }
        }

        $user->save();

        // Vendor: handle multiple emails and preferences
     

        return response()->json(['success' => true]);
    }

    /**
     * Show global notification preferences page (UI)
     */
    public function showGlobalPreferences()
    {
        $user = Auth::user();
        return view('notification.global-preferences', [
            'user' => $user
        ]);
    }

    /**
     * Update global notification preferences (POST handler)
     */
    public function updateGlobalPreferences(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'notification_email' => 'nullable|boolean',
            'notification_push' => 'nullable|boolean',
            'notification_whatsapp' => 'nullable|boolean',
        ]);
        $user->notification_email = $request->has('notification_email');
        $user->notification_push = $request->has('notification_push');
        $user->notification_whatsapp = $request->has('notification_whatsapp');
        $user->save();
        return redirect()->back()->with('success', 'Notification preferences updated successfully.');
    }
}
