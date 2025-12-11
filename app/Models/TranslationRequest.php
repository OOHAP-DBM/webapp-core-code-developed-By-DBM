<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationRequest extends Model
{
    protected $fillable = [
        'key',
        'locale',
        'group',
        'current_value',
        'suggested_value',
        'requested_by',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the language
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'locale', 'code');
    }

    /**
     * Get the user who requested this translation
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who reviewed this translation
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Approve the translation request
     */
    public function approve($reviewerId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Create/update the actual translation
        Translation::updateOrCreate(
            [
                'key' => $this->key,
                'locale' => $this->locale,
                'group' => $this->group,
            ],
            [
                'value' => $this->suggested_value,
                'updated_by' => $reviewerId,
            ]
        );

        return $this;
    }

    /**
     * Reject the translation request
     */
    public function reject($reviewerId, $notes)
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $this;
    }
}
