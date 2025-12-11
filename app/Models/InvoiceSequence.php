<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InvoiceSequence extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'financial_year',
        'last_sequence',
        'year_start_date',
        'year_end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_sequence' => 'integer',
        'year_start_date' => 'date',
        'year_end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scopes
     */
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForYear($query, string $financialYear)
    {
        return $query->where('financial_year', $financialYear);
    }

    /**
     * Get current financial year string (e.g., "2024-25")
     * Financial year: April 1 to March 31
     */
    public static function getCurrentFinancialYear(): string
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        
        // If month is Jan-Mar, FY started last year
        if ($currentMonth < 4) {
            $startYear = $currentYear - 1;
            $endYear = $currentYear;
        } else {
            // If month is Apr-Dec, FY started this year
            $startYear = $currentYear;
            $endYear = $currentYear + 1;
        }
        
        // Format: 2024-25 (last 2 digits of end year)
        return $startYear . '-' . str_pad($endYear % 100, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get financial year dates
     */
    public static function getFinancialYearDates(string $financialYear): array
    {
        // Parse "2024-25" format
        $years = explode('-', $financialYear);
        $startYear = (int) $years[0];
        
        return [
            'start' => Carbon::create($startYear, 4, 1)->startOfDay(),
            'end' => Carbon::create($startYear + 1, 3, 31)->endOfDay(),
        ];
    }

    /**
     * Get or create sequence record for current financial year
     */
    public static function getCurrentSequence(): self
    {
        $financialYear = self::getCurrentFinancialYear();
        
        $sequence = self::forYear($financialYear)->first();
        
        if (!$sequence) {
            $dates = self::getFinancialYearDates($financialYear);
            
            // Deactivate all previous FY sequences
            self::where('is_active', true)->update(['is_active' => false]);
            
            $sequence = self::create([
                'financial_year' => $financialYear,
                'last_sequence' => 0,
                'year_start_date' => $dates['start'],
                'year_end_date' => $dates['end'],
                'is_active' => true,
            ]);
        }
        
        return $sequence;
    }

    /**
     * Get next sequence number (thread-safe with database lock)
     */
    public function getNextSequence(): int
    {
        // Lock the row to prevent race conditions
        $sequence = self::where('id', $this->id)->lockForUpdate()->first();
        
        $sequence->last_sequence++;
        $sequence->save();
        
        return $sequence->last_sequence;
    }

    /**
     * Get next invoice number
     * Format: INV/2024-25/000001
     */
    public static function getNextInvoiceNumber(string $prefix = 'INV'): string
    {
        $sequence = self::getCurrentSequence();
        $nextNumber = $sequence->getNextSequence();
        
        return sprintf(
            '%s/%s/%06d',
            $prefix,
            $sequence->financial_year,
            $nextNumber
        );
    }

    /**
     * Check if a financial year is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if we are currently in this financial year
     */
    public function isCurrentYear(): bool
    {
        $now = Carbon::now();
        return $now->between($this->year_start_date, $this->year_end_date);
    }

    /**
     * Get total invoices generated in this FY
     */
    public function getTotalInvoices(): int
    {
        return $this->last_sequence;
    }
}
