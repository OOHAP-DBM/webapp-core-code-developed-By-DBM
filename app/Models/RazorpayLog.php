<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RazorpayLog extends Model
{
    protected $fillable = [
        'action',
        'request_payload',
        'response_payload',
        'status_code',
        'metadata'
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // ========================
    // Scopes
    // ========================

    /**
     * Scope to filter by action
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter successful requests
     */
    public function scopeSuccessful($query)
    {
        return $query->whereBetween('status_code', [200, 299]);
    }

    /**
     * Scope to filter failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where(function ($q) {
            $q->where('status_code', '<', 200)
              ->orWhere('status_code', '>=', 400);
        });
    }

    /**
     * Scope to filter by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ========================
    // Helpers
    // ========================

    /**
     * Check if the request was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status_code >= 200 && $this->status_code < 300;
    }

    /**
     * Get formatted status
     */
    public function getStatusBadge(): string
    {
        if ($this->isSuccessful()) {
            return '<span class="badge bg-success">Success</span>';
        }
        return '<span class="badge bg-danger">Failed</span>';
    }

    /**
     * Get error message from response
     */
    public function getErrorMessage(): ?string
    {
        if ($this->isSuccessful()) {
            return null;
        }

        return $this->response_payload['error']['description'] 
            ?? $this->response_payload['error'] 
            ?? 'Unknown error';
    }

    /**
     * Get Razorpay order ID from response
     */
    public function getOrderId(): ?string
    {
        return $this->response_payload['id'] ?? null;
    }

    /**
     * Get formatted JSON for display
     */
    public function getFormattedRequest(): string
    {
        return json_encode($this->request_payload, JSON_PRETTY_PRINT);
    }

    /**
     * Get formatted response JSON for display
     */
    public function getFormattedResponse(): string
    {
        return json_encode($this->response_payload, JSON_PRETTY_PRINT);
    }
}
