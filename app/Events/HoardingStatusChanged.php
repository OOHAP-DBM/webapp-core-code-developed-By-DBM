<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HoardingStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $hoardings;
    public $action;
    public $admin;
    public $extra;

    /**
     * @param array|\Illuminate\Support\Collection $hoardings
     * @param string $action
     * @param \App\Models\User $admin
     * @param array $extra
     */
    public function __construct($hoardings, string $action, $admin, array $extra = [])
    {
        $this->hoardings = $hoardings;
        $this->action = $action;
        $this->admin = $admin;
        $this->extra = $extra;
    }
}
