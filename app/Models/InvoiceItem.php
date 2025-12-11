<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
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

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'line_number' => 'integer',
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'cgst_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_rate' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_rate' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'service_start_date' => 'date',
        'service_end_date' => 'date',
        'duration_days' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Relationships
     */
    
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Get formatted amounts
     */
    
    public function getFormattedRate(): string
    {
        return '₹' . number_format($this->rate, 2);
    }

    public function getFormattedAmount(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    public function getFormattedTaxableAmount(): string
    {
        return '₹' . number_format($this->taxable_amount, 2);
    }

    public function getFormattedTotalTax(): string
    {
        return '₹' . number_format($this->total_tax, 2);
    }

    public function getFormattedTotalAmount(): string
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    /**
     * Get tax breakdown for this item
     */
    public function getTaxBreakdown(): array
    {
        if ($this->cgst_amount > 0 || $this->sgst_amount > 0) {
            return [
                'type' => 'intra_state',
                'cgst' => [
                    'rate' => $this->cgst_rate,
                    'amount' => $this->cgst_amount,
                ],
                'sgst' => [
                    'rate' => $this->sgst_rate,
                    'amount' => $this->sgst_amount,
                ],
                'total' => $this->total_tax,
            ];
        } else {
            return [
                'type' => 'inter_state',
                'igst' => [
                    'rate' => $this->igst_rate,
                    'amount' => $this->igst_amount,
                ],
                'total' => $this->total_tax,
            ];
        }
    }

    /**
     * Get service period text
     */
    public function getServicePeriod(): ?string
    {
        if (!$this->service_start_date || !$this->service_end_date) {
            return null;
        }
        
        return $this->service_start_date->format('d M Y') . ' to ' . $this->service_end_date->format('d M Y');
    }

    /**
     * Calculate and set all amounts
     */
    public function calculateAmounts(bool $isIntraState, float $gstRate = 18.0): void
    {
        // Calculate base amount
        $this->amount = $this->quantity * $this->rate;
        
        // Apply discount
        if ($this->discount_percent > 0) {
            $this->discount_amount = ($this->amount * $this->discount_percent) / 100;
        }
        
        $this->taxable_amount = $this->amount - $this->discount_amount;
        
        // Calculate tax
        if ($isIntraState) {
            // CGST + SGST (split GST rate equally)
            $halfRate = $gstRate / 2;
            $this->cgst_rate = $halfRate;
            $this->cgst_amount = ($this->taxable_amount * $halfRate) / 100;
            $this->sgst_rate = $halfRate;
            $this->sgst_amount = ($this->taxable_amount * $halfRate) / 100;
            $this->igst_rate = null;
            $this->igst_amount = 0;
        } else {
            // IGST
            $this->igst_rate = $gstRate;
            $this->igst_amount = ($this->taxable_amount * $gstRate) / 100;
            $this->cgst_rate = null;
            $this->cgst_amount = 0;
            $this->sgst_rate = null;
            $this->sgst_amount = 0;
        }
        
        $this->total_tax = $this->cgst_amount + $this->sgst_amount + $this->igst_amount;
        $this->total_amount = $this->taxable_amount + $this->total_tax;
    }
}
