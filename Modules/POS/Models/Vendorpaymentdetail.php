<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class VendorPaymentDetail extends Model
{
    protected $table = 'vendor_payment_details';

    protected $fillable = [
        'vendor_id',
        'type',           // 'bank' | 'upi'
        // Bank fields
        'bank_name',
        'ifsc_code',
        'account_number',
        'account_holder',
        // UPI fields
        'upi_id',
        'qr_image_path',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}