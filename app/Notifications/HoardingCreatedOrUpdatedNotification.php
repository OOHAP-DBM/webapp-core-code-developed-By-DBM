<?php

namespace App\Notifications;

use App\Models\Hoarding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class HoardingCreatedOrUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $hoarding;
    protected $action; // 'created' or 'updated'
    protected $hoardingType; // 'OOH' or 'DOOH'

    public function __construct(Hoarding $hoarding, string $action = 'created', string $hoardingType = 'OOH')
    {
        $this->hoarding = $hoarding;
        $this->action = $action; // 'created' or 'updated'
        $this->hoardingType = $hoardingType; // 'OOH' or 'DOOH'
    }

    public function via($notifiable)
    {
        // Only send database notification to vendor
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $actionTitle = ucfirst($this->action);
        $isCreated = $this->action === 'created';

        return [
            'type'              => $isCreated ? 'hoarding_created' : 'hoarding_updated',
            'title'             => $this->hoardingType . ' Hoarding ' . $actionTitle,
            'message'           => 'Your ' . $this->hoardingType . ' hoarding "' . ($this->hoarding->title ?? $this->hoarding->name ?? 'N/A') . '" has been ' . $this->action . ' successfully.',
            'hoarding_id'       => $this->hoarding->id,
            'hoarding_type'     => $this->hoardingType,
            'address'           => $this->hoarding->address ?? '',
            'action'            => $this->action,
            'action_url'        => route('vendor.myHoardings.show', $this->hoarding->id),
            'created_at'        => now(),
        ];
    }
}
