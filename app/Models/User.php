<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'otp',
        'otp_expires_at',
        'status',
        'avatar',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        // GST and company details (PROMPT 64)
        'gstin',
        'company_name',
        'pan',
        'customer_type',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_state_code',
        'billing_pincode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if OTP is valid
     */
    public function isOTPValid(string $otp): bool
    {
        return $this->otp === $otp 
            && $this->otp_expires_at 
            && $this->otp_expires_at->isFuture();
    }

    /**
     * Generate OTP
     */
    public function generateOTP(): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        return $otp;
    }

    /**
     * Clear OTP
     */
    public function clearOTP(): void
    {
        $this->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get user's primary role name
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    /**
     * Get user's dashboard route based on role
     */
    public function getDashboardRoute(): string
    {
        $role = $this->getPrimaryRole();

        return match($role) {
            'super_admin', 'admin' => 'admin.dashboard',
            'vendor', 'subvendor' => 'vendor.dashboard',
            'staff' => 'staff.dashboard',
            default => 'customer.dashboard',
        };
    }

    /**
     * Get the hoardings owned by the vendor.
     */
    public function hoardings(): HasMany
    {
        return $this->hasMany(Hoarding::class, 'vendor_id');
    }

    /**
     * Get bookings where user is the vendor (PROMPT 48)
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vendor_id');
    }

    /**
     * Get bookings where user is the customer (PROMPT 48)
     */
    public function customerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    /**
     * Get tasks assigned to vendor (PROMPT 48)
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'vendor_id');
    }

    /**
     * Get the vendor's KYC record
     */
    public function vendorKYC()
    {
        return $this->hasOne(VendorKYC::class, 'vendor_id');
    }

    /**
     * Get the vendor's ledger entries
     */
    public function ledgerEntries()
    {
        return $this->hasMany(VendorLedger::class, 'vendor_id');
    }

    /**
     * Get the user's wishlist/shortlist items (PROMPT 50)
     */
    public function wishlist(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the user's invoices (PROMPT 64)
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(\App\Models\Invoice::class, 'customer_id');
    }

    /**
     * Check if user has wishlisted a hoarding (PROMPT 50)
     */
    public function hasWishlisted(int $hoardingId): bool
    {
        return $this->wishlist()->where('hoarding_id', $hoardingId)->exists();
    }

    /**
     * Get wishlist count (PROMPT 50)
     */
    public function wishlistCount(): int
    {
        return $this->wishlist()->count();
    }
}
