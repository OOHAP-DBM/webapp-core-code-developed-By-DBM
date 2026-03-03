<?php

namespace Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

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

    public static function normalizeQrStoragePath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $normalized = str_replace('\\', '/', trim($path));
        $normalized = preg_replace('#^/?storage/app/public/?#', '', $normalized);
        $normalized = preg_replace('#^/?public/?#', '', $normalized);
        $normalized = ltrim($normalized, '/');

        return $normalized ?: null;
    }

    public function normalizedQrImagePath(): ?string
    {
        return self::normalizeQrStoragePath($this->qr_image_path);
    }

    public function qrImageUrl(bool $absolute = false): ?string
    {
        $normalizedPath = $this->normalizedQrImagePath();
        if (!$normalizedPath) {
            return null;
        }

        $qrUrl = Storage::disk('public')->url($normalizedPath);
        if (str_starts_with($qrUrl, 'http') || !$absolute) {
            return $qrUrl;
        }

        return url('/' . ltrim($qrUrl, '/'));
    }
}