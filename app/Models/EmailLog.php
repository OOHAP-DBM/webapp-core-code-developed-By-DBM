<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'template_id',
        'recipient_email',
        'subject_final',
        'body_final',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // ye log kis template ka hai
    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    // sirf sent logs
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    // sirf failed logs
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // sirf pending logs
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}