<?php

namespace App\Listeners;

use App\Events\HoardingStatusChanged;
use Modules\Hoardings\Notifications\HoardingStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendHoardingStatusNotification implements ShouldQueue
{
    public function handle(HoardingStatusChanged $event)
    {
        \Log::info('[SendHoardingStatusNotification] Listener triggered', [
            'hoardings_count' => $event->hoardings->count(),
            'action' => $event->action,
            'admin_id' => $event->admin ? $event->admin->id : null,
        ]);
        $notifiedVendors = [];
        foreach ($event->hoardings as $hoarding) {
            \Log::info('[SendHoardingStatusNotification] Processing hoarding', [
                'hoarding_id' => $hoarding->id,
                'vendor_loaded' => $hoarding->relationLoaded('vendor'),
                'vendor_id' => $hoarding->vendor ? $hoarding->vendor->id : null,
            ]);
            // Notify vendor (avoid duplicate)
            if ($hoarding->vendor && !in_array($hoarding->vendor->id, $notifiedVendors)) {
                \Log::info('[SendHoardingStatusNotification] Notifying vendor', [
                    'vendor_id' => $hoarding->vendor->id,
                ]);
                $hoarding->vendor->notify(new HoardingStatusNotification($hoarding, $event->action, $event->extra));
                $notifiedVendors[] = $hoarding->vendor->id;
            }
        }
        // Optionally: notify admin (activity log/confirmation)
        if ($event->admin) {
            \Log::info('[SendHoardingStatusNotification] Notifying admin', [
                'admin_id' => $event->admin->id,
            ]);
            $event->admin->notify(new HoardingStatusNotification($event->hoardings->first(), $event->action, ['admin' => true] + $event->extra));
        }
    }
}
