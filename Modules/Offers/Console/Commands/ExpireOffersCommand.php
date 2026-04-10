<?php

namespace Modules\Offers\Console\Commands;

use Modules\Offers\Services\OfferExpiryService;
use Illuminate\Console\Command;

class ExpireOffersCommand extends Command
{
    protected $signature = 'offers:expire {--dry-run : Show offers that would be expired without actually expiring them} {--notify : Send notifications to vendors about expired offers}';
    protected $description = 'Auto-expire offers that have passed their expiry time';

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
        $dueOffers = \Modules\Offers\Models\Offer::dueToExpire()->with(['enquiry.customer', 'vendor'])->get();
        if ($dueOffers->isEmpty()) {
            $this->info('✅ No offers to expire');
            return self::SUCCESS;
        }
        $this->info("Found {$dueOffers->count()} offers due to expire:");
        $this->newLine();
        // ...rest of logic
        return self::SUCCESS;
    }
}
