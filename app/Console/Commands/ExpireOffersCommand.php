<?php

namespace App\Console\Commands;

use App\Services\OfferExpiryService;
use Illuminate\Console\Command;

/**
 * PROMPT 105: Offer Auto-Expiry Logic
 * 
 * Scheduled command to auto-expire offers that have passed their expiry time
 * Should run hourly or daily via Laravel scheduler
 */
class ExpireOffersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:expire
                          {--dry-run : Show offers that would be expired without actually expiring them}
                          {--notify : Send notifications to vendors about expired offers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-expire offers that have passed their expiry time';

    /**
     * Execute the console command.
     */
    public function handle(OfferExpiryService $expiryService): int
    {
        $isDryRun = $this->option('dry-run');
        $shouldNotify = $this->option('notify');

        $this->info('🔍 Checking for offers to expire...');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  DRY RUN MODE - No offers will be actually expired');
            $this->newLine();
        }

        // Get offers due to expire
        $dueOffers = \Modules\Offers\Models\Offer::dueToExpire()
            ->with(['enquiry.customer', 'vendor'])
            ->get();

        if ($dueOffers->isEmpty()) {
            $this->info('✅ No offers to expire');
            return self::SUCCESS;
        }

        $this->info("Found {$dueOffers->count()} offers due to expire:");
        $this->newLine();

        // Display table of offers
        $tableData = $dueOffers->map(function ($offer) {
            $expiryDate = $offer->expires_at ?? $offer->valid_until;
            
            return [
                $offer->id,
                $offer->enquiry->customer->name ?? 'N/A',
                $offer->vendor->name ?? 'N/A',
                '₹' . number_format($offer->price, 2),
                $expiryDate ? $expiryDate->format('Y-m-d H:i') : 'N/A',
                $expiryDate ? $expiryDate->diffForHumans() : 'N/A',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Customer', 'Vendor', 'Price', 'Expired At', 'Time Ago'],
            $tableData
        );

        $this->newLine();

        if ($isDryRun) {
            $this->warn('DRY RUN: Would expire ' . $dueOffers->count() . ' offers');
            return self::SUCCESS;
        }

        // Confirm before proceeding
        if (!$this->confirm('Proceed with expiring these offers?', true)) {
            $this->info('Cancelled');
            return self::SUCCESS;
        }

        // Expire offers
        $expiredCount = $expiryService->expireAllDueOffers();

        $this->info("✅ Successfully expired {$expiredCount} offers");

        // Optionally send notifications
        if ($shouldNotify) {
            $this->info('📧 Sending notifications to vendors...');
            // TODO: Implement notification logic
            $this->comment('Notification feature not yet implemented');
        }

        // Show statistics
        $this->newLine();
        $this->showStatistics($expiryService);

        return self::SUCCESS;
    }

    /**
     * Display expiry statistics
     */
    protected function showStatistics(OfferExpiryService $expiryService): void
    {
        $stats = $expiryService->getExpiryStatistics();

        $this->info('📊 Offer Expiry Statistics:');
        $this->newLine();

        $this->line("  Sent Offers:              {$stats['sent_offers']}");
        $this->line("  Expired Offers:           {$stats['expired_offers']}");
        $this->line("  Expiring Today:           {$stats['expiring_today']}");
        $this->line("  Expiring Within 7 Days:   {$stats['expiring_within_7_days']}");
        $this->line("  Accepted Before Expiry:   {$stats['accepted_before_expiry']}");
        $this->line("  Default Expiry Days:      {$stats['default_expiry_days']}");
    }
}
