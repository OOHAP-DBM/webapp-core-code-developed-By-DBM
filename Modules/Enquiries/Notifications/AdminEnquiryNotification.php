<?php

namespace Modules\Enquiries\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;


class AdminEnquiryNotification extends Notification implements ShouldQueue
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

       $this->customerName = optional($enquiry->customer)->name 
                 ?? ($enquiry->meta['customer_name'] ?? 'New Client');
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    public function toMail($notifiable)
    {
        \Log::info('Preparing admin enquiry notification email', [
            'enquiry_id' => $this->enquiry->id,
            'customer_name' => $this->customerName,
            'total_items' => $this->totalItems,
            'vendor_count' => $this->vendorCount,
            'is_multi_vendor' => $this->isMultiVendor,
        ]);
        // Use a custom HTML Blade template for admin notification, matching vendor notification UI
        return (new MailMessage)
            ->subject(' New Enquiry Received (Admin) | #' . ($this->enquiry->formatted_id ?? $this->enquiry->id))
            ->view(
                'emails.admin-enquiry-notification',
                [
                    'enquiry' => $this->enquiry,
                    'customerName' => $this->customerName,
                    'totalItems' => $this->totalItems,
                    'vendorCount' => $this->vendorCount,
                    'isMultiVendor' => $this->isMultiVendor,
                    'totalValue' => $this->totalValue,
                ]
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
