<?php

namespace Database\Factories;

use App\Models\BookingPayment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingPaymentFactory extends Factory
{
    protected $model = BookingPayment::class;

    public function definition(): array
    {
        $grossAmount = $this->faker->numberBetween(5000, 50000);
        $commission = $grossAmount * 0.15; // 15%
        $pgFee = $grossAmount * 0.02; // 2%
        $vendorPayout = $grossAmount - $commission - $pgFee;

        return [
            'booking_id' => Booking::factory(),
            'gross_amount' => $grossAmount,
            'admin_commission_amount' => $commission,
            'vendor_payout_amount' => $vendorPayout,
            'pg_fee_amount' => $pgFee,
            'razorpay_payment_id' => 'pay_' . $this->faker->bothify('??##??##??##'),
            'razorpay_order_id' => 'order_' . $this->faker->bothify('??##??##??##'),
            'vendor_payout_status' => 'pending',
            'status' => 'captured',
        ];
    }
}
