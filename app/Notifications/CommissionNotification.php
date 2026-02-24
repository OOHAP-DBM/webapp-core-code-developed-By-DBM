<?php
// app/Notifications/CommissionNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommissionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $type,           // 'set' | 'updated'
        protected float  $commission,     // base commission percent
        protected string $commissionType, // 'all' | 'ooh' | 'dooh' | 'mixed'
        protected ?float $oohCommission  = null,
        protected ?float $doohCommission = null,
        protected ?string $hoardingName   = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $actionUrl = url('/vendor/commission/my-commission');

        $subject = $this->type === 'set'
            ? 'Your Commission Has Been Set'
            : 'Your Commission Has Been Updated';

        $intro = match(true) {
            $this->hoardingName !== null && $this->type === 'set' =>
                "A commission rate has been set for your hoarding \"{$this->hoardingName}\". Please review the details below.",
            $this->hoardingName !== null && $this->type === 'updated' =>
                "The commission rate for your hoarding \"{$this->hoardingName}\" has been updated by the administrator. Please review and confirm your acceptance.",
            $this->type === 'set' =>
                'We are pleased to inform you that a commission rate has been set for your account. Please review the details below and confirm your acceptance.',
            default =>
                'Your commission rate has been updated by the administrator. Please review the updated details below and confirm your acceptance.',
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.commission-notification', [
                'subject'         => $subject,
                'vendorName'      => $notifiable->name ?? 'Vendor',
                'intro'           => $intro,
                'type'            => $this->type,
                'commission'      => $this->commission,
                'commissionType'  => $this->commissionType,
                'oohCommission'   => $this->oohCommission,
                'doohCommission'  => $this->doohCommission,
                'hoardingName'    => $this->hoardingName,
                'actionUrl'       => $actionUrl,
                'dashboardUrl'    => url('/vendor/hoardings'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        $requiresAgreement = in_array($this->type, ['set', 'updated'], true);

        $commissionDisplay = match(true) {
            $this->hoardingName !== null => "{$this->commission}% for hoarding \"{$this->hoardingName}\"",
            $this->commissionType === 'all' => "{$this->commission}% (OOH & DOOH)",
            default => collect([
                $this->oohCommission  !== null ? "OOH: {$this->oohCommission}%"  : null,
                $this->doohCommission !== null ? "DOOH: {$this->doohCommission}%" : null,
            ])->filter()->implode(', '),
        };

        return [
            'type'               => 'commission_' . $this->type,
            'title'              => $this->type === 'set'
                                        ? 'Commission Set'
                                        : 'Commission Updated',
            'message'            => $this->type === 'set'
                                        ? "Your commission has been set. Please agree to proceed."
                                        : "Your commission has been updated. Please agree to proceed.",
            'commission'         => $this->commission,
            'commission_type'    => $this->commissionType,
            'ooh_commission'     => $this->oohCommission,
            'dooh_commission'    => $this->doohCommission,
            'hoarding_title'     => $this->hoardingName,
            'action_url'         => url('/vendor/commission/my-commission'),
            'actionUrl'          => url('/vendor/commission/my-commission'),
            'requires_agreement' => $requiresAgreement,
            'mark_read_on_open'  => !$requiresAgreement,
        ];
    }
}