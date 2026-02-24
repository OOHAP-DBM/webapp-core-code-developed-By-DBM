<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationRedirectController extends Controller
{
    public function open($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        $type = data_get($notification->data, 'type');
        $isCommissionAgreementNotification = in_array($type, ['commission_set', 'commission_updated'], true);

        if (!$isCommissionAgreementNotification && !$notification->read_at) {
            $notification->markAsRead();
        }

        $url = $isCommissionAgreementNotification
            ? url('/vendor/commission/my-commission')
            : (data_get($notification->data, 'action_url')
                ?? data_get($notification->data, 'actionUrl')
                ?? '/');

        return redirect($url);
    }
}
