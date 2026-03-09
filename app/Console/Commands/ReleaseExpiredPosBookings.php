<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\Whatsapp\TwilioWhatsappService;
use Modules\POS\Models\POSBooking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredPosBookings extends Command
{
    protected $signature   = 'pos:release-expired-bookings';
    protected $description = 'Release POS bookings whose payment hold has expired';

    public function handle(): void
    {
        $now = now();
        $expired = POSBooking::where('payment_status', 'unpaid')
            ->whereIn('status', ['draft', 'pending_payment'])
            ->whereNotNull('hold_expiry_at')
            ->where('hold_expiry_at', '<=', $now)
            ->get();

        if ($expired->isEmpty()) {
            Log::info('POS hold expiry scan completed with no expired bookings', [
                'ran_at' => $now->toDateTimeString(),
            ]);
            $this->info('Released 0 expired POS bookings.');
            return;
        }

        Log::info('POS hold expiry scan found expired bookings', [
            'ran_at' => $now->toDateTimeString(),
            'count' => $expired->count(),
            'booking_ids' => $expired->pluck('id')->all(),
        ]);

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
                    'hold_expiry_at' => $booking->hold_expiry_at,
                ]);

                // Optionally notify customer that hold expired
                try {
                    $notification = new \App\Notifications\PosBookingHoldExpiredNotification($booking);

                    if ($booking->customer_id) {
                        $customer = User::find($booking->customer_id);
                        if ($customer && method_exists($customer, 'notifyNow')) {
                            $customer->notifyNow($notification);
                        }
                    }

                    if ($booking->vendor_id) {
                        $vendor = User::find($booking->vendor_id);
                        if ($vendor && method_exists($vendor, 'notifyNow')) {
                            $vendor->notifyNow($notification);
                        }
                    }

                    $admins = User::whereHas('roles', function ($query) {
                        $query->whereIn('name', ['admin', 'superadmin', 'super_admin']);
                    })->get();

                    foreach ($admins as $admin) {
                        if (method_exists($admin, 'notifyNow')) {
                            $admin->notifyNow($notification);
                        }
                    }

                    $this->sendHoldExpiryWhatsAppNotifications($booking);
                } catch (\Throwable $e) {
                    Log::warning('Hold expiry notification failed', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                    ]);
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

    protected function sendHoldExpiryWhatsAppNotifications(POSBooking $booking): void
    {
        try {
            $whatsapp = app(TwilioWhatsappService::class);
        } catch (\Throwable $e) {
            Log::warning('Hold expiry WhatsApp service unavailable', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $vendor = $booking->vendor_id ? User::find($booking->vendor_id) : null;
        $customer = $booking->customer_id ? User::find($booking->customer_id) : null;

        $customerPhone = $this->normalizePhone($customer?->phone ?: $booking->customer_phone);
        $vendorPhone = $this->normalizePhone($vendor?->phone);

        if ($customerPhone && ($customer ? (bool) $customer->notification_whatsapp : true)) {
            $customerActionUrl = url('/customer/bookings/' . $booking->id);
            $sent = $whatsapp->send($customerPhone, $this->buildCustomerHoldExpiredMessage($booking, $vendor, $customerActionUrl));
            Log::info('Hold expiry WhatsApp attempted for customer', [
                'booking_id' => $booking->id,
                'phone' => $customerPhone,
                'sent' => $sent,
                'action_url' => $customerActionUrl,
            ]);
        }

        if ($vendorPhone && ($vendor ? (bool) $vendor->notification_whatsapp : false)) {
            $vendorActionUrl = url('/vendor/pos/bookings/' . $booking->id);
            $sent = $whatsapp->send($vendorPhone, $this->buildVendorHoldExpiredMessage($booking, $vendor, $vendorActionUrl));
            Log::info('Hold expiry WhatsApp attempted for vendor', [
                'booking_id' => $booking->id,
                'phone' => $vendorPhone,
                'sent' => $sent,
                'action_url' => $vendorActionUrl,
            ]);
        }
    }

    protected function buildCustomerHoldExpiredMessage(POSBooking $booking, ?User $vendor, string $actionUrl): string
    {
        $invoice = $booking->invoice_number ?: ('#' . $booking->id);
        $vendorName = $vendor?->name ?? 'Vendor';
        $amount = number_format((float) $booking->total_amount, 2);
        $expiredAt = $this->formatHoldExpiryAt($booking->hold_expiry_at);

        return "Hello {$booking->customer_name},\n\n"
            . "Your POS booking hold has expired due to pending payment and booking is now cancelled.\n\n"
            . "Invoice: {$invoice}\n"
            . "Amount: ₹{$amount}\n"
            . "Expired At: {$expiredAt}\n"
            . "Vendor: {$vendorName}\n\n"
            . "Booking link: {$actionUrl}\n"
            . "Please create a new booking if you want to continue.";
    }

    protected function buildVendorHoldExpiredMessage(POSBooking $booking, ?User $vendor, string $actionUrl): string
    {
        $invoice = $booking->invoice_number ?: ('#' . $booking->id);
        $vendorName = $vendor?->name ?? 'Vendor';
        $amount = number_format((float) $booking->total_amount, 2);
        $expiredAt = $this->formatHoldExpiryAt($booking->hold_expiry_at);

        return "Hello {$vendorName},\n\n"
            . "A POS booking hold has expired and the booking was auto-cancelled.\n\n"
            . "Booking ID: {$booking->id}\n"
            . "Invoice: {$invoice}\n"
            . "Customer: {$booking->customer_name}\n"
            . "Amount: ₹{$amount}\n"
            . "Expired At: {$expiredAt}\n\n"
            . "Booking link: {$actionUrl}";
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $phone);
        return $normalized !== '' ? $normalized : null;
    }

    protected function formatHoldExpiryAt($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d M Y, h:i A');
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value)->format('d M Y, h:i A');
            } catch (\Throwable) {
                return 'N/A';
            }
        }

        return 'N/A';
    }
}
