<?php

namespace App\Console\Commands;

use App\Services\SLATrackingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * MonitorVendorSLAs Command
 * PROMPT 68: Vendor SLA Tracking System
 * 
 * Scheduled command to monitor vendor SLA compliance
 * Checks deadlines, detects violations, sends warnings
 */
class MonitorVendorSLAs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sla:monitor
                            {--force : Force monitoring even if disabled}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor vendor SLA deadlines and detect violations';

    protected SLATrackingService $slaService;

    /**
     * Create a new command instance.
     */
    public function __construct(SLATrackingService $slaService)
    {
        parent::__construct();
        $this->slaService = $slaService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting SLA monitoring...');
        
        try {
            $startTime = microtime(true);
            
            // Monitor all pending SLA deadlines
            $results = $this->slaService->monitorPendingDeadlines();
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            // Display results
            $this->newLine();
            $this->info('SLA Monitoring Complete');
            $this->line('─────────────────────────────────────');
            $this->line("Acceptances Checked: {$results['acceptances_checked']}");
            $this->line("Quotes Checked: {$results['quotes_checked']}");
            $this->line("Warnings Sent: {$results['warnings_sent']}");
            $this->line("Violations Detected: {$results['violations_detected']}");
            $this->line("Duration: {$duration}s");
            $this->line('─────────────────────────────────────');

            if (!empty($results['errors'])) {
                $this->warn('Errors encountered:');
                foreach ($results['errors'] as $error) {
                    $this->error('  • ' . $error);
                }
            }

            // Log results
            Log::info('SLA monitoring completed', [
                'acceptances_checked' => $results['acceptances_checked'],
                'quotes_checked' => $results['quotes_checked'],
                'warnings_sent' => $results['warnings_sent'],
                'violations_detected' => $results['violations_detected'],
                'duration' => $duration,
                'errors' => $results['errors'],
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('SLA monitoring failed: ' . $e->getMessage());
            Log::error('SLA monitoring failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }
}
