<?php
// app/Models/InvoiceItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'line_number',
        'item_type',
        'description',
        'hsn_sac_code',
        'hoarding_id',
        'product_id',
        'quantity',
        'unit',
        'rate',
        'amount',
        'discount_percent',
        'discount_amount',
        'taxable_amount',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'igst_rate',
        'igst_amount',
        'total_tax',
        'total_amount',
        'service_start_date',
        'service_end_date',
        'duration_days',
        'metadata',
    ];

    protected $casts = [
        'line_number'        => 'integer',
        'quantity'           => 'decimal:4',
        'rate'               => 'decimal:4',
        'amount'             => 'decimal:4',
        'discount_percent'   => 'decimal:2',
        'discount_amount'    => 'decimal:4',
        'taxable_amount'     => 'decimal:4',
        'cgst_rate'          => 'decimal:2',
        'cgst_amount'        => 'decimal:4',
        'sgst_rate'          => 'decimal:2',
        'sgst_amount'        => 'decimal:4',
        'igst_rate'          => 'decimal:2',
        'igst_amount'        => 'decimal:4',
        'total_tax'          => 'decimal:4',
        'total_amount'       => 'decimal:4',
        'service_start_date' => 'date',
        'service_end_date'   => 'date',
        'duration_days'      => 'integer',
        'metadata'           => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    public function getFormattedRate(): string
    {
        return '' . number_format($this->rate, 2);
    }
    public function getFormattedAmount(): string
    {
        return '' . number_format($this->amount, 2);
    }
    public function getFormattedTaxableAmount(): string
    {
        return '' . number_format($this->taxable_amount, 2);
    }
    public function getFormattedTotalTax(): string
    {
        return '' . number_format($this->total_tax, 2);
    }
    public function getFormattedTotalAmount(): string
    {
        return '' . number_format($this->total_amount, 2);
    }


    public function getServicePeriod(): ?string
    {
        if (!$this->service_start_date || !$this->service_end_date) return null;
        return $this->service_start_date->format('d M Y') . ' to ' . $this->service_end_date->format('d M Y');
    }

    public function getTaxBreakdown(): array
    {
        if ($this->cgst_amount > 0 || $this->sgst_amount > 0) {
            return [
                'type' => 'intra_state',
                'cgst'  => ['rate' => $this->cgst_rate, 'amount' => $this->cgst_amount],
                'sgst'  => ['rate' => $this->sgst_rate, 'amount' => $this->sgst_amount],
                'total' => $this->total_tax
            ];
        }
        return [
            'type' => 'inter_state',
            'igst'  => ['rate' => $this->igst_rate, 'amount' => $this->igst_amount],
            'total' => $this->total_tax
        ];
    }

    /**
     * Calculate all monetary amounts for this line item.
     *
     * FIX: The old code did:
     *   if ($this->discount_percent > 0) { $this->discount_amount = ... }
     *   // implied else: discount_amount stays 0 (default model value)
     *
     * This wiped out any flat discount_amount pre-set by the caller
     * (e.g. hoarding_discount from POSBookingHoarding).
     *
     * New behaviour: only derive discount_amount from discount_percent when
     * discount_percent > 0; otherwise keep whatever was already set.
     */
    public function calculateAmounts(bool $isIntraState, float $gstRate = 18.0): void
    {
        // 1. Gross = qty × rate
        $this->amount = round((float) $this->quantity * (float) $this->rate, 4);

        // 2. Discount — percent takes priority; flat value preserved if percent is 0
        if ((float) $this->discount_percent > 0) {
            $this->discount_amount = round(($this->amount * (float) $this->discount_percent) / 100, 4);
        }
        // If discount_percent == 0, existing discount_amount stays (flat discount from controller)

        // 3. Taxable
        $this->taxable_amount = round($this->amount - (float) $this->discount_amount, 4);

        // 4. GST
        if ($isIntraState) {
            $half = $gstRate / 2;
            $this->cgst_rate   = $half;
            $this->cgst_amount = round($this->taxable_amount * $half / 100, 4);
            $this->sgst_rate   = $half;
            $this->sgst_amount = round($this->taxable_amount * $half / 100, 4);
            $this->igst_rate   = null;
            $this->igst_amount = 0;
        } else {
            $this->igst_rate   = $gstRate;
            $this->igst_amount = round($this->taxable_amount * $gstRate / 100, 4);
            $this->cgst_rate   = null;
            $this->cgst_amount = 0;
            $this->sgst_rate   = null;
            $this->sgst_amount = 0;
        }

        // 5. Totals
        $this->total_tax    = round((float)$this->cgst_amount + (float)$this->sgst_amount + (float)$this->igst_amount, 4);
        $this->total_amount = round($this->taxable_amount + $this->total_tax, 4);
    }

    /**
     * Calculate GST using single gst_rate (without CGST/SGST split)
     * This does NOT affect existing calculateAmounts() logic.
     */
    public function calculateSingleGST(float $gstRate = 18.0): void
    {
        // 1️⃣ Gross Amount
        $this->amount = round((float) $this->quantity * (float) $this->rate, 4);

        // 2️⃣ Discount (percent priority)
        if ((float) $this->discount_percent > 0) {
            $this->discount_amount = round(
                ($this->amount * (float) $this->discount_percent) / 100,
                4
            );
        }

        // 3️⃣ Taxable Amount
        $this->taxable_amount = round(
            $this->amount - (float) $this->discount_amount,
            4
        );

        // 4️⃣ Single GST Calculation
        $this->gst_rate   = $gstRate;
        $this->gst_amount = round(
            $this->taxable_amount * $gstRate / 100,
            4
        );

        // 5️⃣ Totals
        $this->total_tax    = $this->gst_amount;
        $this->total_amount = round(
            $this->taxable_amount + $this->total_tax,
            4
        );
    }
}
