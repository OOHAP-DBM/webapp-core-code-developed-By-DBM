<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'quotation_id' => 1, // Dummy value for testing
            'customer_id' => User::factory(),
            'vendor_id' => User::factory(),
            'hoarding_id' => 1, // Dummy value for testing
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'duration_type' => 'days',
            'duration_days' => 30,
            'total_amount' => $this->faker->numberBetween(5000, 50000),
            'status' => 'confirmed',
            'payment_status' => 'pending',
            'payment_mode' => 'online',
        ];
    }
}
