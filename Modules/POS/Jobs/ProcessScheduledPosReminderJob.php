<?php

namespace Modules\POS\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Services\POSReminderService;

class ProcessScheduledPosReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(public int $reminderId) {}

    public function handle(POSReminderService $reminderService): void
    {
        $reminderService->processScheduledReminder($this->reminderId);
    }
}
