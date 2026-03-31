<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Enquiries\Models\Enquiry;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\VendorReminderMail;
use App\Mail\AdminReminderMail;
use App\Notifications\EnquiryReminderNotification;
use App\Models\EnquiryReminder;
use App\Models\User;

class ReminderController extends Controller
{
    public function send(Request $request, int $enquiryId)
    {
        // ── 1. Enquiry fetch + ownership check ──────────────────
        $enquiry = Enquiry::where('id', $enquiryId)
            ->where('customer_id', auth()->id())
            ->with(['items.hoarding.vendor'])
            ->firstOrFail();

        // ── 2. Unique vendors nikalo ─────────────────────────────
        $vendors = $enquiry->items
            ->map(fn($item) => optional($item->hoarding)->vendor)
            ->filter()
            ->unique('id')
            ->values();

        if ($vendors->isEmpty()) {
            return redirect()->back()->with('error', 'No vendors found for this enquiry.');
        }

        $customer       = auth()->user();
        $isSingleVendor = $vendors->count() === 1;

        try {
            if ($isSingleVendor) {
                // ── CASE 1: Single Vendor ────────────────────────
                $vendor = $vendors->first();

                $vendorHoardings = $enquiry->items
                    ->filter(fn($item) => optional($item->hoarding)->vendor_id === $vendor->id)
                    ->map(fn($item) => $item->hoarding)
                    ->filter()
                    ->values();

                $reminderData = $this->buildReminderData(
                    $enquiry,
                    $customer,
                    $vendorHoardings
                );

                // Mail to vendor — queue mein jayegi
                if ($vendor->email) {
                    Mail::to($vendor->email, $vendor->name)
                        ->queue(new VendorReminderMail($reminderData));
                }

                // In-app notification to vendor — queue mein jayegi
                $vendor->notify(
                    new EnquiryReminderNotification(
                        title  : 'Reminder: Enquiry ' . $enquiry->formatted_id,
                        message: $customer->name . ' is waiting for your response on enquiry ' . $enquiry->formatted_id,
                        url    : route('vendor.enquiries.show', $enquiry->id),
                        meta   : ['enquiry_id' => $enquiry->id]
                    )
                );

            } else {
                // ── CASE 2: Multiple Vendors → Admin ─────────────
                $admin = User::role('admin')->first();

                $allHoardings = $enquiry->items
                    ->map(fn($item) => $item->hoarding)
                    ->filter()
                    ->values();

                $reminderData = $this->buildReminderData(
                    $enquiry,
                    $customer,
                    $allHoardings,
                    $vendors
                );

                // Mail to admin — queue mein jayegi
                if ($admin && $admin->email) {
                    Mail::to($admin->email, $admin->name ?? 'Admin')
                        ->queue(new AdminReminderMail($reminderData));
                }

                // In-app notification to admin — queue mein jayegi
                if ($admin) {
                    $admin->notify(
                        new EnquiryReminderNotification(
                            title  : 'Multi-Vendor Reminder: ' . $enquiry->formatted_id,
                            message: $customer->name . ' sent a reminder for enquiry ' . $enquiry->formatted_id . ' with ' . $vendors->count() . ' vendors.',
                            url    : route('admin.enquiries.show', $enquiry->id),
                            meta   : [
                                'enquiry_id'   => $enquiry->id,
                                'vendor_count' => $vendors->count(),
                            ]
                        )
                    );
                }
            }

            // ── 3. Log reminder ──────────────────────────────────
            EnquiryReminder::create([
                'enquiry_id'  => $enquiry->id,
                'customer_id' => $customer->id,
                'vendor_ids'  => $vendors->pluck('id')->toArray(),
                'sent_to'     => $isSingleVendor ? 'vendor' : 'admin',
                'sent_at'     => now(),
            ]);

            return redirect()->back()->with('success', 'Reminder sent successfully!');

        } catch (\Exception $e) {
            Log::error('Reminder send failed', [
                'enquiry_id' => $enquiry->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to send reminder. Please try again.');
        }
    }

    /* ================================================================
     |  Build reminder data
     ================================================================ */
    private function buildReminderData(
        $enquiry,
        $customer,
        $hoardings,
        $vendors = null
    ): array {
        return [
            'enquiry_id'     => $enquiry->formatted_id,
            'enquiry_date'   => $enquiry->created_at->format('d M, Y'),
            'customer_name'  => $customer->name,
            'customer_phone' => $customer->phone  ?? 'N/A',
            'customer_email' => $customer->email  ?? 'N/A',
            'customer_note'  => $enquiry->customer_note ?? '',
            'vendor_count'   => $vendors ? $vendors->count() : 1,
            'vendors'        => $vendors
                ? $vendors->map(fn($v) => [
                    'name'  => $v->name,
                    'email' => $v->email  ?? 'N/A',
                    'phone' => $v->phone  ?? 'N/A',
                ])->toArray()
                : [],
            'hoardings'      => $hoardings->map(fn($h) => [
                'title'    => $h->title    ?? 'N/A',
                'type'     => strtoupper($h->hoarding_type ?? ''),
                'location' => trim(
                    ($h->locality ?? '') . ' ' .
                    ($h->city     ?? '') . ' ' .
                    ($h->state    ?? '')
                ),
                'size'     => $h->size         ?? 'N/A',
                'price'    => number_format($h->monthly_price ?? 0),
                'vendor'   => optional($h->vendor)->name ?? 'N/A',
            ])->toArray(),
        ];
    }
}