<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\POS\Models\POSBooking;
use Illuminate\Support\Facades\Log;
class ReleaseExpiredPosBookings extends Command
{
       protected $signature   = 'pos:release-expired-bookings';
    protected $description = 'Release POS bookings whose payment hold has expired';

    public function handle(): void
    {
        $expired = POSBooking::where('payment_status', 'unpaid')
            ->whereIn('status', ['draft', 'pending_payment'])
            ->whereNotNull('hold_expiry_at')
            ->where('hold_expiry_at', '<=', now())
            ->get();

        foreach ($expired as $booking) {
            try {
                $booking->update([
                    'status'              => 'cancelled',
                    'cancelled_at'        => now(),
                    'cancellation_reason' => 'Payment hold expired — auto-released',
                ]);

                // Release hold on each associated hoarding
                foreach ($booking->bookingHoardings as $bh) {
                    $hoarding = $bh->hoarding;
                    if ($hoarding && $hoarding->held_by_booking_id == $booking->id) {
                        $hoarding->update([
                            'is_on_hold'          => false,
                            'hold_till'            => null,
                            'held_by_booking_id'   => null,
                        ]);
                    }
                }

                Log::info('POS booking auto-released (hold expired)', [
                    'booking_id'    => $booking->id,
                    'hold_expiry_at'=> $booking->hold_expiry_at,
                ]);

                // Optionally notify customer that hold expired
                try {
                    if ($booking->customer_id) {
                        $customer = \App\Models\User::find($booking->customer_id);
                        if ($customer && method_exists($customer, 'notify')) {
                            $customer->notify(new \App\Notifications\PosBookingHoldExpiredNotification($booking));
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Hold expiry notification failed', ['booking_id' => $booking->id]);
                }

            } catch (\Throwable $e) {
                Log::error('Failed to release expired POS booking', [
                    'booking_id' => $booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        $this->info("Released {$expired->count()} expired POS bookings.");
    }

}
