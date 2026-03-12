<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\PosBookingCreatedNotification;
use Illuminate\Support\Facades\Log;
use Modules\POS\Events\PosBookingCreated;

class SendPosBookingCreatedNotification
{
    public function handle(PosBookingCreated $event): void
    {
        try {
            $booking = $event->booking;

            $recipientIds = collect();

            if (!empty($booking->customer_id)) {
                $recipientIds->push((int) $booking->customer_id);
            }

            if (!empty($booking->vendor_id)) {
                $recipientIds->push((int) $booking->vendor_id);
            }

            $adminIds = User::query()
                ->whereHas('roles', function ($query) {
                    $query->whereIn('name', ['admin', 'super_admin']);
                })
                ->pluck('id');

            $recipientIds = $recipientIds
                ->merge($adminIds)
                ->filter(fn($id) => (int) $id > 0)
                ->unique()
                ->values();

            if ($recipientIds->isEmpty()) {
                return;
            }

            $notification = new PosBookingCreatedNotification($booking);

            User::query()
                ->whereIn('id', $recipientIds->all())
                ->get()
                ->each(function (User $user) use ($notification) {
                    $user->notify($notification);
                });
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch POS booking created notifications', [
                'booking_id' => $event->booking->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
