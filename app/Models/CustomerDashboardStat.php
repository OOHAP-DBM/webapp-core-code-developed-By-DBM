<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDashboardStat extends Model
{
    protected $fillable = [
        'user_id',
        'total_bookings',
        'active_bookings',
        'completed_bookings',
        'cancelled_bookings',
        'total_booking_amount',
        'total_payments',
        'total_paid',
        'total_pending',
        'total_refunded',
        'total_enquiries',
        'pending_enquiries',
        'responded_enquiries',
        'total_offers',
        'active_offers',
        'accepted_offers',
        'total_quotations',
        'pending_quotations',
        'approved_quotations',
        'total_invoices',
        'paid_invoices',
        'unpaid_invoices',
        'total_invoice_amount',
        'total_threads',
        'unread_threads',
        'last_calculated_at',
    ];

    protected $casts = [
        'total_booking_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'total_pending' => 'decimal:2',
        'total_refunded' => 'decimal:2',
        'total_invoice_amount' => 'decimal:2',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the dashboard stats
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if stats need recalculation (older than 1 hour)
     */
    public function needsRecalculation(): bool
    {
        if (!$this->last_calculated_at) {
            return true;
        }

        return $this->last_calculated_at->diffInMinutes(now()) > 60;
    }

    /**
     * Get booking completion rate
     */
    public function getBookingCompletionRateAttribute(): float
    {
        if ($this->total_bookings == 0) {
            return 0;
        }

        return round(($this->completed_bookings / $this->total_bookings) * 100, 2);
    }

    /**
     * Get payment completion rate
     */
    public function getPaymentCompletionRateAttribute(): float
    {
        $totalDue = $this->total_paid + $this->total_pending;
        
        if ($totalDue == 0) {
            return 0;
        }

        return round(($this->total_paid / $totalDue) * 100, 2);
    }

    /**
     * Get enquiry response rate
     */
    public function getEnquiryResponseRateAttribute(): float
    {
        if ($this->total_enquiries == 0) {
            return 0;
        }

        return round(($this->responded_enquiries / $this->total_enquiries) * 100, 2);
    }

    /**
     * Get offer acceptance rate
     */
    public function getOfferAcceptanceRateAttribute(): float
    {
        if ($this->total_offers == 0) {
            return 0;
        }

        return round(($this->accepted_offers / $this->total_offers) * 100, 2);
    }

    /**
     * Get quotation approval rate
     */
    public function getQuotationApprovalRateAttribute(): float
    {
        if ($this->total_quotations == 0) {
            return 0;
        }

        return round(($this->approved_quotations / $this->total_quotations) * 100, 2);
    }

    /**
     * Get invoice payment rate
     */
    public function getInvoicePaymentRateAttribute(): float
    {
        if ($this->total_invoices == 0) {
            return 0;
        }

        return round(($this->paid_invoices / $this->total_invoices) * 100, 2);
    }

    /**
     * Get average booking amount
     */
    public function getAverageBookingAmountAttribute(): float
    {
        if ($this->total_bookings == 0) {
            return 0;
        }

        return round($this->total_booking_amount / $this->total_bookings, 2);
    }
}
