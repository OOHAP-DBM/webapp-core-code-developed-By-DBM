<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorMetric extends Model
    /**
     * Only allow updates if vendor is approved
     */

   {
    use SoftDeletes;

    protected $table = 'vendor_metrics';

    protected $fillable = [
        'vendor_id',
        'reliability_score',
        'reliability_tier',
        'sla_violations_count',
        'sla_violations_this_month',
        'total_penalty_points',
        'enquiries_accepted_count',
        'quotes_submitted_count',
        'quotes_accepted_count',
        'avg_acceptance_time_hours',
        'avg_quote_time_hours',
        'on_time_acceptance_rate',
        'on_time_quote_rate',
        'quote_win_rate',
        'last_sla_violation_at',
        'last_score_update_at',
        'last_recovery_at',
        'vendor_sla_setting_id',
    ];

    public function canUpdateMetrics(): bool
    {
        return $this->vendorProfile && $this->vendorProfile->onboarding_status === 'approved';
    }

    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_id');
    }
}
