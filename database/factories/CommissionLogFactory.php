<?php

namespace Database\Factories;

use App\Models\CommissionLog;
use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionLogFactory extends Factory
{
    protected $model = CommissionLog::class;

    public function definition(): array
    {
        $grossAmount = $this->faker->numberBetween(5000, 50000);
        $commission = $grossAmount * 0.15;
        $pgFee = $grossAmount * 0.02;
        $vendorPayout = $grossAmount - $commission - $pgFee;

        return [
            'booking_id' => Booking::factory(),
            'booking_payment_id' => BookingPayment::factory(),
            'gross_amount' => $grossAmount,
            'admin_commission' => $commission,
            'vendor_payout' => $vendorPayout,
            'pg_fee' => $pgFee,
            'tax' => 0,
            'commission_rate' => 15.00,
            'commission_type' => 'percentage',
        ];
    }
}
