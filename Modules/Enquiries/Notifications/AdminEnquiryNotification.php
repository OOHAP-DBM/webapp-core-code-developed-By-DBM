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
    protected $totalItems;
    protected $vendorCount;
    protected $isMultiVendor;
    protected $totalValue;
    protected $customerName;

    public function __construct($enquiry)
    {
        $this->enquiry = $enquiry;

        $items = $enquiry->items ?? collect();

        $this->totalItems  = $items->count();
        $this->vendorCount = $items->pluck('vendor_id')
                                ->filter(fn($id) => !empty($id))
                                ->unique()
                                ->count();

        $this->isMultiVendor = $this->vendorCount > 1;
        $this->totalValue = $items->sum(fn ($item) => $item->meta['amount'] ?? 0);

        $this->customerName = is_array($enquiry->meta ?? null) && isset($enquiry->meta['customer_name'])
            ? $enquiry->meta['customer_name']
            : 'New Client';
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(
                ($this->isMultiVendor 
                    ? '🚨 MULTI-VENDOR ALERT: ' 
                    : '📩 SINGLE-VENDOR LEAD: ')
                . 'New Enquiry #' . $this->enquiry->id
            )
            ->greeting('Hello Admin,')

            ->line(
                $this->isMultiVendor
                    ? "🚨 Multi-vendor enquiry involving {$this->vendorCount} vendors."
                    : "📩 Single-vendor enquiry."
            )

            ->line(
                "Total Hoardings: {$this->totalItems}"
            )

            ->line(
                "Client: {$this->customerName}"
            )

            ->line(
                "Total Potential Value: ₹" . number_format($this->totalValue, 2)
            )

            ->action(
                'Review in Admin Panel',
                url('/admin/enquiries/' . $this->enquiry->id)
            )

            ->line(
                $this->isMultiVendor
                    ? '⚠️ Coordination between multiple vendors is required.'
                    : 'Ensure the vendor responds promptly.'
            );
    }

    public function toDatabase($notifiable)
    {
        return [
            'enquiry_id'    => $this->enquiry->id,
            'customer_name' => $this->customerName,
            'item_count'    => $this->totalItems,
            'vendor_count'  => $this->vendorCount,
            'is_multi_vendor' => $this->isMultiVendor,
            'total_value'   => $this->totalValue,

            'message' => $this->isMultiVendor
                ? "🚨 Multi-vendor enquiry ({$this->vendorCount} vendors, {$this->totalItems} hoardings)."
                    : "📩 Single-vendor enquiry ({$this->totalItems} " 
                      . Str::plural('hoarding', $this->totalItems) . ").",

            'action_url' => route('admin.enquiries.show', $this->enquiry->id),
            'role'       => 'admin',
        ];
    }


}
