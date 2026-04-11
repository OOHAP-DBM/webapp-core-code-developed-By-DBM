<?php

namespace App\Services;

use App\Models\{AdminOverride, User, Booking, BookingPayment, CommissionLog};
use Modules\Offers\Models\Offer;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Database\Eloquent\Model;
use Exception;

class AdminOverrideService
{
    /**
     * Override a booking.
     * 
     * @param Booking $booking
     * @param array $data
     * @param User $admin
     * @param string $reason
     * @return AdminOverride
     */
    public function overrideBooking(Booking $booking, array $data, User $admin, string $reason): AdminOverride
    {
        return DB::transaction(function () use ($booking, $data, $admin, $reason) {
            // Capture original state
            $originalData = $booking->toArray();
            
            // Determine severity based on changes
            $severity = $this->determineSeverity($data, [
                'critical' => ['payment_status', 'status', 'total_amount'],
                'high' => ['vendor_id', 'customer_id'],
                'medium' => ['start_date', 'end_date', 'duration_days'],
            ]);
            
            // Track specific changes
            $changes = $this->extractChanges($booking, $data);
            
            // Update the booking
            $booking->update($data);
            
            // Create override log
            return $this->logOverride(
                model: $booking,
                admin: $admin,
                action: 'update',
                originalData: $originalData,
                newData: $booking->fresh()->toArray(),
                changes: $changes,
                reason: $reason,
                overrideType: 'booking',
                severity: $severity,
                fieldChanged: $this->getFieldsList($changes)
            );
        });
    }

    /**
     * Override payment status.
     * 
     * @param BookingPayment $payment
     * @param array $data
     * @param User $admin
     * @param string $reason
     * @return AdminOverride
     */
    public function overridePayment(BookingPayment $payment, array $data, User $admin, string $reason): AdminOverride
    {
        return DB::transaction(function () use ($payment, $data, $admin, $reason) {
            $originalData = $payment->toArray();
            
            $severity = $this->determineSeverity($data, [
                'critical' => ['vendor_payout_amount', 'admin_commission_amount', 'vendor_payout_status'],
                'high' => ['gross_amount', 'pg_fee_amount'],
                'medium' => ['payout_mode', 'payout_reference'],
            ]);
            
            $changes = $this->extractChanges($payment, $data);
            
            // Update payment
            $payment->update($data);
            
            return $this->logOverride(
                model: $payment,
                admin: $admin,
                action: 'payment_override',
                originalData: $originalData,
                newData: $payment->fresh()->toArray(),
                changes: $changes,
                reason: $reason,
                overrideType: 'payment',
                severity: $severity,
                fieldChanged: $this->getFieldsList($changes)
            );
        });
    }

    /**
     * Override offer details.
     * 
     * @param Offer $offer
     * @param array $data
     * @param User $admin
     * @param string $reason
     * @return AdminOverride
     */
    public function overrideOffer(Offer $offer, array $data, User $admin, string $reason): AdminOverride
    {
        return DB::transaction(function () use ($offer, $data, $admin, $reason) {
            $originalData = $offer->toArray();
            
            $severity = $this->determineSeverity($data, [
                'critical' => ['status', 'total_amount'],
                'high' => ['expiry_date', 'customer_id'],
                'medium' => ['discount_amount', 'notes'],
            ]);
            
            $changes = $this->extractChanges($offer, $data);
            
            $offer->update($data);
            
            return $this->logOverride(
                model: $offer,
                admin: $admin,
                action: 'update',
                originalData: $originalData,
                newData: $offer->fresh()->toArray(),
                changes: $changes,
                reason: $reason,
                overrideType: 'offer',
                severity: $severity,
                fieldChanged: $this->getFieldsList($changes)
            );
        });
    }

    /**
     * Override quote request.
     * 
     * @param QuoteRequest $quote
     * @param array $data
     * @param User $admin
     * @param string $reason
     * @return AdminOverride
     */
    public function overrideQuote(QuoteRequest $quote, array $data, User $admin, string $reason): AdminOverride
    {
        return DB::transaction(function () use ($quote, $data, $admin, $reason) {
            $originalData = $quote->toArray();
            
            $severity = $this->determineSeverity($data, [
                'critical' => ['status', 'quoted_amount'],
                'high' => ['vendor_id', 'customer_id'],
                'medium' => ['notes', 'expiry_date'],
            ]);
            
            $changes = $this->extractChanges($quote, $data);
            
            $quote->update($data);
            
            return $this->logOverride(
                model: $quote,
                admin: $admin,
                action: 'update',
                originalData: $originalData,
                newData: $quote->fresh()->toArray(),
                changes: $changes,
                reason: $reason,
                overrideType: 'quote',
                severity: $severity,
                fieldChanged: $this->getFieldsList($changes)
            );
        });
    }

    /**
     * Override commission calculation.
     * 
     * @param CommissionLog $commission
     * @param array $data
     * @param User $admin
     * @param string $reason
     * @return AdminOverride
     */
    public function overrideCommission(CommissionLog $commission, array $data, User $admin, string $reason): AdminOverride
    {
        return DB::transaction(function () use ($commission, $data, $admin, $reason) {
            $originalData = $commission->toArray();
            
            // Commission changes are always critical
            $severity = 'critical';
            
            $changes = $this->extractChanges($commission, $data);
            
            // Note: CommissionLog is typically immutable, but admin override allows it
            DB::table('commission_logs')
                ->where('id', $commission->id)
                ->update(array_merge($data, ['updated_at' => now()]));
            
            $commission = $commission->fresh();
            
            return $this->logOverride(
                model: $commission,
                admin: $admin,
                action: 'commission_override',
                originalData: $originalData,
                newData: $commission->toArray(),
                changes: $changes,
                reason: $reason,
                overrideType: 'commission',
                severity: $severity,
                fieldChanged: $this->getFieldsList($changes),
                metadata: [
                    'warning' => 'Commission log is typically immutable - admin override applied',
                    'booking_id' => $commission->booking_id,
                ]
            );
        });
    }

    /**
     * Override vendor KYC status.
     * 
     * @param Model $vendorKyc
     * @param array $data
     * @param User $admin
     * @param string $reason
     * @return AdminOverride
     */
    public function overrideVendorKyc($vendorKyc, array $data, User $admin, string $reason): AdminOverride
    {
        return DB::transaction(function () use ($vendorKyc, $data, $admin, $reason) {
            $originalData = $vendorKyc->toArray();
            
            $severity = $this->determineSeverity($data, [
                'critical' => ['verification_status', 'payout_status'],
                'high' => ['razorpay_subaccount_id'],
                'medium' => ['notes'],
            ]);
            
            $changes = $this->extractChanges($vendorKyc, $data);
            
            $vendorKyc->update($data);
            
            return $this->logOverride(
                model: $vendorKyc,
                admin: $admin,
                action: 'kyc_override',
                originalData: $originalData,
                newData: $vendorKyc->fresh()->toArray(),
                changes: $changes,
                reason: $reason,
                overrideType: 'vendor_kyc',
                severity: $severity,
                fieldChanged: $this->getFieldsList($changes)
            );
        });
    }

    /**
     * Revert an admin override.
     * 
     * @param AdminOverride $override
     * @param User $admin
     * @param string $reason
     * @return bool
     */
    public function revertOverride(AdminOverride $override, User $admin, string $reason): bool
    {
        if (!$override->canRevert()) {
            throw new Exception('This override cannot be reverted.');
        }

        return DB::transaction(function () use ($override, $admin, $reason) {
            $model = $override->overridable;
            
            if (!$model) {
                throw new Exception('Original model not found.');
            }

            // Restore original data
            $restorableData = $this->getRestorableData($override->original_data, $model);
            $model->update($restorableData);
            
            // Mark override as reverted
            $override->markReverted($admin, $reason, $model->fresh()->toArray());
            
            // Log the revert action in audit log
            Log::info('Admin override reverted', [
                'override_id' => $override->id,
                'override_type' => $override->override_type,
                'model_id' => $override->overridable_id,
                'reverted_by' => $admin->name,
                'reason' => $reason,
            ]);
            
            return true;
        });
    }

    /**
     * Get override history for a model.
     * 
     * @param Model $model
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOverrideHistory(Model $model)
    {
        return AdminOverride::where('overridable_type', get_class($model))
            ->where('overridable_id', $model->id)
            ->with(['user', 'reverter'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get override statistics.
     * 
     * @param array $filters
     * @return array
     */
    public function getStatistics(array $filters = []): array
    {
        $query = AdminOverride::query();

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['override_type'])) {
            $query->where('override_type', $filters['override_type']);
        }

        $total = $query->count();
        $reverted = (clone $query)->where('is_reverted', true)->count();
        $bySeverity = (clone $query)->select('severity', DB::raw('count(*) as count'))
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();
        $byType = (clone $query)->select('override_type', DB::raw('count(*) as count'))
            ->groupBy('override_type')
            ->pluck('count', 'override_type')
            ->toArray();

        return [
            'total_overrides' => $total,
            'total_reverted' => $reverted,
            'revert_rate' => $total > 0 ? round(($reverted / $total) * 100, 2) : 0,
            'by_severity' => $bySeverity,
            'by_type' => $byType,
            'critical_count' => $bySeverity['critical'] ?? 0,
            'high_count' => $bySeverity['high'] ?? 0,
            'medium_count' => $bySeverity['medium'] ?? 0,
            'low_count' => $bySeverity['low'] ?? 0,
        ];
    }

    /**
     * Log an override action.
     */
    protected function logOverride(
        Model $model,
        User $admin,
        string $action,
        array $originalData,
        array $newData,
        array $changes,
        string $reason,
        string $overrideType,
        string $severity,
        ?string $fieldChanged = null,
        array $metadata = []
    ): AdminOverride {
        return AdminOverride::create([
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'user_email' => $admin->email,
            'overridable_type' => get_class($model),
            'overridable_id' => $model->id,
            'action' => $action,
            'field_changed' => $fieldChanged,
            'original_data' => $originalData,
            'new_data' => $newData,
            'changes' => $changes,
            'reason' => $reason,
            'override_type' => $overrideType,
            'severity' => $severity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Extract changes between original model and new data.
     */
    protected function extractChanges(Model $model, array $newData): array
    {
        $changes = [];
        
        foreach ($newData as $key => $value) {
            $originalValue = $model->getOriginal($key);
            
            if ($originalValue != $value) {
                $changes[$key] = [
                    'old' => $originalValue,
                    'new' => $value,
                ];
            }
        }
        
        return $changes;
    }

    /**
     * Determine severity based on changed fields.
     */
    protected function determineSeverity(array $data, array $severityMap): string
    {
        foreach (['critical', 'high', 'medium', 'low'] as $level) {
            if (isset($severityMap[$level])) {
                foreach ($severityMap[$level] as $field) {
                    if (array_key_exists($field, $data)) {
                        return $level;
                    }
                }
            }
        }
        
        return 'low';
    }

    /**
     * Get comma-separated list of changed fields.
     */
    protected function getFieldsList(array $changes): string
    {
        return implode(', ', array_keys($changes));
    }

    /**
     * Get restorable data from original data array.
     */
    protected function getRestorableData(array $originalData, Model $model): array
    {
        // Only restore fillable fields
        $fillable = $model->getFillable();
        
        return array_filter($originalData, function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);
    }
}
