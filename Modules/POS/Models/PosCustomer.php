<?php


namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosCustomer extends Model
{
    use HasFactory;

    // Table name if it differs from the class plural (optional here)
    protected $table = 'pos_customers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'vendor_id',
        'created_by',
        'gstin',
        'business_name',
        'address',
    ];

    /**
     * Get the user that owns the customer profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the vendor/user who owns this customer mapping.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

   
}