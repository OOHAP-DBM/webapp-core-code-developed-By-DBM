<?php

namespace App\Console\Commands;

use App\Services\QuotationExpiryService;
use Illuminate\Console\Command;

/**
 * PROMPT 106: Process Expired Quotations Command
 * 
 * Auto-cancel booking flow, notify parties, and update threads
 * for quotations that have expired based on offer deadlines.
 */
class ProcessExpiredQuotationsCommand extends Command
{
    protected $signature = 'quotations:process-expired
                            {--dry-run : Preview quotations to be processed without actually processing them}
                            {--warnings : Send warning notifications for quotations expiring soon}';

    protected $description = 'Process expired quotations: auto-cancel bookings, notify parties, update threads';

    protected QuotationExpiryService $expiryService;

    public function __construct(QuotationExpiryService $expiryService)
    {
        parent::__construct();
        $this->expiryService = $expiryService;
    }

    public function handle(): int
    {
        $this->info('Processing expired quotations...');
        $this->newLine();

        // Send expiry warnings if requested
        if ($this->option('warnings')) {
            $this->processWarnings();
            $this->newLine();
        }

        // Get quotations that will be processed
        $quotations = $this->getExpiredQuotations();

        if ($quotations->isEmpty()) {
            $this->info('✅ No expired quotations found.');
            return Command::SUCCESS;
        }

        $this->info(sprintf('Found %d expired quotation(s):', $quotations->count()));
        $this->newLine();

        // Display table of quotations
        $this->displayQuotationsTable($quotations);
        $this->newLine();

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: No quotations were actually processed.');
            return Command::SUCCESS;
        }

        // Confirm before processing
        if (!$this->confirm(sprintf('Do you want to process these %d quotation(s)?', $quotations->count()), true)) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Process expired quotations
        $this->info('Processing...');
        $count = $this->expiryService->processExpiredQuotations();

        $this->newLine();
        $this->info(sprintf('✅ Successfully processed %d quotation(s).', $count));
        $this->newLine();

        // Display statistics
        $this->displayStatistics();

        return Command::SUCCESS;
    }

    protected function processWarnings(): void
    {
        $this->info('Sending expiry warnings...');

        $count = $this->expiryService->sendExpiryWarnings();

        if ($count > 0) {
            $this->info(sprintf('✅ Sent %d expiry warning(s).', $count));
        } else {
            $this->info('No quotations expiring soon.');
        }
    }

    protected function getExpiredQuotations()
    {
        return \App\Models\Quotation::with(['offer', 'customer', 'vendor'])
            ->whereIn('status', [\App\Models\Quotation::STATUS_SENT, \App\Models\Quotation::STATUS_DRAFT])
            ->whereHas('offer', function ($query) {
                $query->where('status', \Modules\Offers\Models\Offer::STATUS_SENT)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now());
            })
            ->get();
    }

    protected function displayQuotationsTable($quotations): void
    {
        $rows = $quotations->map(function ($quotation) {
            return [
                'ID' => $quotation->id,
                'Customer' => $quotation->customer->name ?? 'N/A',
                'Vendor' => $quotation->vendor->name ?? 'N/A',
                'Amount' => '$' . number_format($quotation->grand_total, 2),
                'Offer ID' => $quotation->offer_id,
                'Expired At' => $quotation->offer->expires_at?->format('Y-m-d H:i:s') ?? 'N/A',
                'Time Ago' => $quotation->offer->expires_at?->diffForHumans() ?? 'N/A',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Customer', 'Vendor', 'Amount', 'Offer ID', 'Expired At', 'Time Ago'],
            $rows
        );
    }

    protected function displayStatistics(): void
    {
        $stats = $this->expiryService->getExpiryStatistics();

        $this->info('Quotation Expiry Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Active Quotations', $stats['total_active']],
                ['Total Expired Quotations', $stats['total_expired']],
                ['Expiring Today', $stats['expiring_today']],
                ['Expiring Soon', $stats['expiring_soon']],
                ['Expiry Rate', $stats['expiry_rate'] . '%'],
            ]
        );
    }
}
