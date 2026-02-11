<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;


class AdminEnquiryNotification extends Notification
{
    use Queueable;

    protected $enquiry;

    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $items       = $this->enquiry->items;
        $totalItems  = $items->count();

        /**
         * Detect unique vendors
         * If vendor_id exists directly on item
         */
        $vendorCount = $items->pluck('vendor_id')->filter()->unique()->count();

          $isMultiVendor = $vendorCount > 1;

        $totalValue = $items->sum(fn ($item) => $item->meta['amount'] ?? 0);

        return (new MailMessage)
            ->subject(
                ($isMultiVendor ? '🚨 MULTI-VENDOR ALERT: ' : 'PLATFORM ALERT: ') .
                'New Lead Generated #' . $this->enquiry->id
            )
            ->greeting('Hello Admin,')

            ->line(
                $isMultiVendor
                    ? '🚨 **Multi-Vendor Enquiry Detected** involving hoardings from ' .
                      $vendorCount . ' different vendors.'
                    : (
                        $totalItems === 1
                            ? 'A single hoarding enquiry has been generated on the platform.'
                            : 'A multi hoarding enquiry has been generated on the platform for ' .
                              $totalItems . ' ' . Str::plural('hoarding', $totalItems) . '.'
                    )
            )

            ->line(
                '**Client:** ' .
                (is_array($this->enquiry->meta) && isset($this->enquiry->meta['customer_name'])
                    ? $this->enquiry->meta['customer_name']
                    : 'N/A')
            )

            ->line('**Total Hoardings:** ' . $totalItems)

            ->when($isMultiVendor, function (MailMessage $message) use ($vendorCount) {
                $message->line('**Vendors Involved:** ' . $vendorCount);
            })

            ->line(
                '**Total Potential Value:** ₹' . number_format($totalValue, 2)
            )

            ->action(
                'Review in Admin Panel',
                url('/admin/enquiries/' . $this->enquiry->id)
            )

            ->line(
                $isMultiVendor
                    ? '⚠️ Immediate coordination is required to ensure timely vendor responses.'
                    : 'Ensure the vendor responds to this lead promptly.'
            );
    }

    public function toDatabase($notifiable)
    {
        $items = method_exists($this->enquiry, 'items') ? $this->enquiry->items : ($this->enquiry->items ?? []);
        $itemCount = is_callable([$items, 'count']) ? $items->count() : (is_array($items) ? count($items) : 0);
        $customerName = is_array($this->enquiry->meta ?? null) && isset($this->enquiry->meta['customer_name'])
            ? $this->enquiry->meta['customer_name']
            : 'New Client';
        return [
            'enquiry_id'   => $this->enquiry->id,
            'item_count'   => $itemCount,
            'customer_name'=> $customerName,
            'message'      => 'New enquiry raised for ' . $itemCount . ' hoarding(s).',
            'action_url'   => route('admin.enquiries.show', $this->enquiry->id),
            'role'         => 'admin',
        ];
    }
}
