<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationRedirectController extends Controller
{
    public function open($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        if (!$notification->read_at) {
            $notification->markAsRead();
        }
        $url = $notification->data['action_url'] ?? '/';
        return redirect($url);
    }
}
