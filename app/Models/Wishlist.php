<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'hoarding_id',
    ];

    /**
     * Get the user that owns the wishlist item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the hoarding associated with the wishlist item.
     */
    public function hoarding(): BelongsTo
    {
        return $this->belongsTo(Hoarding::class);
    }

    /**
     * Check if a hoarding is in user's wishlist.
     *
     * @param int $userId
     * @param int $hoardingId
     * @return bool
     */
    public static function isInWishlist(int $userId, int $hoardingId): bool
    {
        return self::where('user_id', $userId)
            ->where('hoarding_id', $hoardingId)
            ->exists();
    }

    /**
     * Toggle wishlist status for a hoarding.
     *
     * @param int $userId
     * @param int $hoardingId
     * @return array ['action' => 'added'|'removed', 'count' => int]
     */
    public static function toggle(int $userId, int $hoardingId): array
    {
        $wishlistItem = self::where('user_id', $userId)
            ->where('hoarding_id', $hoardingId)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            $action = 'removed';
        } else {
            self::create([
                'user_id' => $userId,
                'hoarding_id' => $hoardingId,
            ]);
            $action = 'added';
        }

        $count = self::where('user_id', $userId)->count();

        return [
            'action' => $action,
            'count' => $count,
        ];
    }

    /**
     * Get wishlist count for a user.
     *
     * @param int $userId
     * @return int
     */
    public static function getCount(int $userId): int
    {
        return self::where('user_id', $userId)->count();
    }
}
