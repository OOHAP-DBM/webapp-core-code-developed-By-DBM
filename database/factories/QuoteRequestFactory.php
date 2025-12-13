<?php

namespace Database\Factories;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteRequestFactory extends Factory
{
    protected $model = QuoteRequest::class;

    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'vendor_id' => User::factory(),
            'status' => 'pending',
            'quoted_amount' => $this->faker->numberBetween(5000, 50000),
            'notes' => $this->faker->sentence(),
        ];
    }
}
