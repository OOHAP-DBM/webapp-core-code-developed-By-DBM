<?php

namespace Database\Factories;

use App\Models\BookingDraft;
use App\Models\User;
use App\Models\Hoarding;
use App\Models\DOOHPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingDraft>
 */
class BookingDraftFactory extends Factory
{
    protected $model = BookingDraft::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+3 days', '+30 days');
        $endDate = (clone $startDate)->modify('+30 days');

        return [
            'customer_id' => User::factory(),
            'hoarding_id' => Hoarding::factory(),
            'package_id' => null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => 30,
            'duration_type' => BookingDraft::DURATION_MONTHS,
            'price_snapshot' => json_encode([
                'base_price' => 50000,
                'discount_applied' => 0,
                'vendor_offer_applied' => 0,
                'gst' => 9000,
                'final_price' => 59000
            ]),
            'base_price' => 50000.00,
            'discount_amount' => 0.00,
            'gst_amount' => 9000.00,
            'total_amount' => 59000.00,
            'applied_offers' => null,
            'coupon_code' => null,
            'step' => BookingDraft::STEP_DATES_SELECTED,
            'last_updated_step_at' => now(),
            'session_id' => $this->faker->uuid(),
            'expires_at' => now()->addMinutes(30),
            'is_converted' => false,
            'booking_id' => null,
            'converted_at' => null,
        ];
    }

    /**
     * Indicate that the draft is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(31),
        ]);
    }

    /**
     * Indicate that the draft has been converted to a booking.
     */
    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_converted' => true,
            'booking_id' => \App\Models\Booking::factory(),
            'converted_at' => now(),
            'step' => BookingDraft::STEP_PAYMENT_PENDING,
        ]);
    }

    /**
     * Indicate that the draft is at hoarding selection step.
     */
    public function atHoardingStep(): static
    {
        return $this->state(fn (array $attributes) => [
            'step' => BookingDraft::STEP_HOARDING_SELECTED,
            'start_date' => null,
            'end_date' => null,
            'duration_days' => null,
            'duration_type' => null,
            'base_price' => null,
            'total_amount' => null,
        ]);
    }

    /**
     * Indicate that the draft is at package selection step.
     */
    public function atPackageStep(): static
    {
        return $this->state(fn (array $attributes) => [
            'step' => BookingDraft::STEP_PACKAGE_SELECTED,
            'package_id' => DOOHPackage::factory(),
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    /**
     * Indicate that the draft is at review step.
     */
    public function atReviewStep(): static
    {
        return $this->state(fn (array $attributes) => [
            'step' => BookingDraft::STEP_REVIEW,
        ]);
    }

    /**
     * Indicate that the draft has a package selected.
     */
    public function withPackage(): static
    {
        return $this->state(fn (array $attributes) => [
            'package_id' => DOOHPackage::factory(),
        ]);
    }

    /**
     * Indicate that the draft has a coupon applied.
     */
    public function withCoupon(string $code = 'TESTCODE', float $discount = 5000): static
    {
        return $this->state(fn (array $attributes) => [
            'coupon_code' => $code,
            'discount_amount' => $discount,
            'applied_offers' => json_encode([
                [
                    'coupon_code' => $code,
                    'discount_type' => 'fixed',
                    'discount_value' => $discount,
                    'discount_amount' => $discount
                ]
            ]),
        ]);
    }
}
