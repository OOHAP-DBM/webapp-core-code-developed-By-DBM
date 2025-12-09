<?php

namespace App\Services;

use App\Models\Hoarding;
use App\Models\PriceUpdateLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PriceUpdateService
{
    /**
     * Update price for a single hoarding.
     *
     * @param int $hoardingId
     * @param array $priceData
     * @param int $adminId
     * @param string|null $reason
     * @return PriceUpdateLog
     */
    public function updateSinglePrice(
        int $hoardingId,
        array $priceData,
        int $adminId,
        ?string $reason = null
    ): PriceUpdateLog {
        return DB::transaction(function () use ($hoardingId, $priceData, $adminId, $reason) {
            $hoarding = Hoarding::findOrFail($hoardingId);

            // Create snapshot before update
            $oldWeeklyPrice = $hoarding->weekly_price;
            $oldMonthlyPrice = $hoarding->monthly_price;
            $hoardingSnapshot = $hoarding->toArray();

            // Update prices
            $updateData = [];
            if (isset($priceData['weekly_price'])) {
                $updateData['weekly_price'] = $priceData['weekly_price'];
            }
            if (isset($priceData['monthly_price'])) {
                $updateData['monthly_price'] = $priceData['monthly_price'];
            }

            $hoarding->update($updateData);

            // Log the update
            return PriceUpdateLog::create([
                'admin_id' => $adminId,
                'update_type' => PriceUpdateLog::TYPE_SINGLE,
                'hoarding_id' => $hoardingId,
                'old_weekly_price' => $oldWeeklyPrice,
                'old_monthly_price' => $oldMonthlyPrice,
                'new_weekly_price' => $updateData['weekly_price'] ?? $oldWeeklyPrice,
                'new_monthly_price' => $updateData['monthly_price'] ?? $oldMonthlyPrice,
                'reason' => $reason,
                'affected_hoardings_count' => 1,
                'hoarding_snapshot' => $hoardingSnapshot,
            ]);
        });
    }

    /**
     * Bulk update prices based on criteria.
     *
     * @param array $criteria
     * @param array $updateConfig
     * @param int $adminId
     * @param string|null $reason
     * @return array
     */
    public function bulkUpdatePrices(
        array $criteria,
        array $updateConfig,
        int $adminId,
        ?string $reason = null
    ): array {
        $batchId = (string) Str::uuid();
        $updatedCount = 0;
        $logs = [];

        DB::transaction(function () use ($criteria, $updateConfig, $adminId, $reason, $batchId, &$updatedCount, &$logs) {
            // Build query based on criteria
            $query = $this->buildCriteriaQuery($criteria);
            $hoardings = $query->get();

            foreach ($hoardings as $hoarding) {
                $oldWeeklyPrice = $hoarding->weekly_price;
                $oldMonthlyPrice = $hoarding->monthly_price;
                $hoardingSnapshot = $hoarding->toArray();

                // Calculate new prices
                $newPrices = $this->calculateNewPrices(
                    $oldWeeklyPrice,
                    $oldMonthlyPrice,
                    $updateConfig
                );

                // Update hoarding
                $hoarding->update([
                    'weekly_price' => $newPrices['weekly_price'],
                    'monthly_price' => $newPrices['monthly_price'],
                ]);

                // Create log entry
                $log = PriceUpdateLog::create([
                    'admin_id' => $adminId,
                    'update_type' => PriceUpdateLog::TYPE_BULK,
                    'batch_id' => $batchId,
                    'hoarding_id' => $hoarding->id,
                    'old_weekly_price' => $oldWeeklyPrice,
                    'old_monthly_price' => $oldMonthlyPrice,
                    'new_weekly_price' => $newPrices['weekly_price'],
                    'new_monthly_price' => $newPrices['monthly_price'],
                    'bulk_criteria' => $criteria,
                    'update_method' => $updateConfig['method'],
                    'update_value' => $updateConfig['value'] ?? null,
                    'reason' => $reason,
                    'affected_hoardings_count' => $hoardings->count(),
                    'hoarding_snapshot' => $hoardingSnapshot,
                ]);

                $logs[] = $log;
                $updatedCount++;
            }
        });

        return [
            'batch_id' => $batchId,
            'updated_count' => $updatedCount,
            'logs' => $logs,
        ];
    }

    /**
     * Build query based on criteria.
     *
     * @param array $criteria
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildCriteriaQuery(array $criteria)
    {
        $query = Hoarding::query();

        if (!empty($criteria['vendor_id'])) {
            $query->where('vendor_id', $criteria['vendor_id']);
        }

        if (!empty($criteria['type'])) {
            $query->where('type', $criteria['type']);
        }

        if (!empty($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (!empty($criteria['city'])) {
            $query->where('address', 'like', '%' . $criteria['city'] . '%');
        }

        if (!empty($criteria['area'])) {
            $query->where('address', 'like', '%' . $criteria['area'] . '%');
        }

        // Property type (assuming it might be in description or title)
        if (!empty($criteria['property_type'])) {
            $query->where(function ($q) use ($criteria) {
                $q->where('title', 'like', '%' . $criteria['property_type'] . '%')
                  ->orWhere('description', 'like', '%' . $criteria['property_type'] . '%');
            });
        }

        // Size filtering (if we have width/height fields, adjust as needed)
        if (!empty($criteria['min_price'])) {
            $query->where('monthly_price', '>=', $criteria['min_price']);
        }

        if (!empty($criteria['max_price'])) {
            $query->where('monthly_price', '<=', $criteria['max_price']);
        }

        return $query;
    }

    /**
     * Calculate new prices based on update method.
     *
     * @param float|null $oldWeeklyPrice
     * @param float|null $oldMonthlyPrice
     * @param array $config
     * @return array
     */
    protected function calculateNewPrices(
        ?float $oldWeeklyPrice,
        ?float $oldMonthlyPrice,
        array $config
    ): array {
        $method = $config['method'];
        $value = $config['value'] ?? 0;
        $priceType = $config['price_type'] ?? 'both'; // 'weekly', 'monthly', or 'both'

        $newWeeklyPrice = $oldWeeklyPrice;
        $newMonthlyPrice = $oldMonthlyPrice;

        switch ($method) {
            case PriceUpdateLog::METHOD_FIXED:
                // Set fixed price
                if ($priceType === 'weekly' || $priceType === 'both') {
                    $newWeeklyPrice = $value;
                }
                if ($priceType === 'monthly' || $priceType === 'both') {
                    $newMonthlyPrice = $value;
                }
                break;

            case PriceUpdateLog::METHOD_PERCENTAGE:
                // Increase/decrease by percentage
                if ($priceType === 'weekly' || $priceType === 'both') {
                    if ($oldWeeklyPrice !== null) {
                        $newWeeklyPrice = $oldWeeklyPrice * (1 + ($value / 100));
                    }
                }
                if ($priceType === 'monthly' || $priceType === 'both') {
                    if ($oldMonthlyPrice !== null) {
                        $newMonthlyPrice = $oldMonthlyPrice * (1 + ($value / 100));
                    }
                }
                break;

            case PriceUpdateLog::METHOD_INCREMENT:
                // Add fixed amount
                if ($priceType === 'weekly' || $priceType === 'both') {
                    if ($oldWeeklyPrice !== null) {
                        $newWeeklyPrice = $oldWeeklyPrice + $value;
                    }
                }
                if ($priceType === 'monthly' || $priceType === 'both') {
                    if ($oldMonthlyPrice !== null) {
                        $newMonthlyPrice = $oldMonthlyPrice + $value;
                    }
                }
                break;

            case PriceUpdateLog::METHOD_DECREMENT:
                // Subtract fixed amount
                if ($priceType === 'weekly' || $priceType === 'both') {
                    if ($oldWeeklyPrice !== null) {
                        $newWeeklyPrice = max(0, $oldWeeklyPrice - $value);
                    }
                }
                if ($priceType === 'monthly' || $priceType === 'both') {
                    if ($oldMonthlyPrice !== null) {
                        $newMonthlyPrice = max(0, $oldMonthlyPrice - $value);
                    }
                }
                break;
        }

        // Ensure prices don't go negative
        $newWeeklyPrice = $newWeeklyPrice !== null ? max(0, $newWeeklyPrice) : null;
        $newMonthlyPrice = $newMonthlyPrice !== null ? max(0, $newMonthlyPrice) : null;

        return [
            'weekly_price' => $newWeeklyPrice,
            'monthly_price' => $newMonthlyPrice,
        ];
    }

    /**
     * Get update logs with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUpdateLogs(array $filters = [], int $perPage = 20)
    {
        $query = PriceUpdateLog::with(['admin', 'hoarding'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['update_type'])) {
            $query->where('update_type', $filters['update_type']);
        }

        if (!empty($filters['admin_id'])) {
            $query->where('admin_id', $filters['admin_id']);
        }

        if (!empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get statistics for price updates.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_updates' => PriceUpdateLog::count(),
            'single_updates' => PriceUpdateLog::singleUpdates()->count(),
            'bulk_updates' => PriceUpdateLog::bulkUpdates()->distinct('batch_id')->count('batch_id'),
            'total_hoardings_affected' => PriceUpdateLog::sum('affected_hoardings_count'),
            'recent_updates' => PriceUpdateLog::whereDate('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
