<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EnquiryReminder extends Model
{
    use HasFactory;

    protected $table = 'enquiry_reminders';

    protected $fillable = [
        'enquiry_id',
        'customer_id',
        'vendor_ids',
        'sent_to',
        'sent_at',
    ];

    protected $casts = [
        'vendor_ids' => 'array',   // JSON auto decode/encode
        'sent_at'    => 'datetime',
    ];

    /* ── RELATIONSHIPS ── */

    public function enquiry()
    {
        return $this->belongsTo(\Modules\Enquiries\Models\Enquiry::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /* ── SCOPES ── */

    public function scopeForEnquiry($query, int $enquiryId)
    {
        return $query->where('enquiry_id', $enquiryId);
    }

    public function scopeSentToVendor($query)
    {
        return $query->where('sent_to', 'vendor');
    }

    public function scopeSentToAdmin($query)
    {
        return $query->where('sent_to', 'admin');
    }
}