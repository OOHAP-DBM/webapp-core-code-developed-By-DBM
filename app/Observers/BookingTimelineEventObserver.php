<?php

namespace App\Observers;

use App\Models\BookingTimelineEvent;
use App\Models\User;
use App\Notifications\BookingTimelineStageNotification;
use Illuminate\Support\Facades\Log;

class BookingTimelineEventObserver
{
    /**
     * Handle the BookingTimelineEvent "created" event.
     */
    public function created(BookingTimelineEvent $event): void
    {
        $this->sendNotifications($event);
    }

    /**
     * Handle the BookingTimelineEvent "updated" event.
     */
    public function updated(BookingTimelineEvent $event): void
    {
        // Only send notifications if status changed
        if ($event->wasChanged('status')) {
            $this->sendNotifications($event);
            
            // Mark as notified
            if (!$event->notified_at) {
                $event->update(['notified_at' => now()]);
            }
        }
    }

    /**
     * Send notifications to relevant parties
     */
    protected function sendNotifications(BookingTimelineEvent $event): void
    {
        try {
            $booking = $event->booking;
            
            if (!$booking) {
                Log::warning("BookingTimelineEvent #{$event->id} has no associated booking");
                return;
            }

            // Notify customer
            if ($event->notify_customer && $booking->customer) {
                $booking->customer->notify(
                    new BookingTimelineStageNotification($booking, $event, 'customer')
                );
            }

            // Notify vendor
            if ($event->notify_vendor && $booking->vendor) {
                $booking->vendor->notify(
                    new BookingTimelineStageNotification($booking, $event, 'vendor')
                );
            }

            // Notify admin (notify all admins)
            if ($event->notify_admin) {
                $admins = User::where('role', 'admin')->where('is_active', true)->get();
                foreach ($admins as $admin) {
                    $admin->notify(
                        new BookingTimelineStageNotification($booking, $event, 'admin')
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send timeline notifications for event #{$event->id}: " . $e->getMessage());
        }
    }
}
