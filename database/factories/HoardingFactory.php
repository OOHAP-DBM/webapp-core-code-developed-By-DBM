<?php

namespace Database\Factories;

use App\Models\Hoarding;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hoarding>
 */
class HoardingFactory extends Factory
{
    protected $model = Hoarding::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['billboard', 'digital', 'transit', 'street_furniture', 'wallscape', 'mobile'];
        $statuses = ['draft', 'pending_approval', 'active', 'inactive', 'suspended'];
        $enableWeeklyBooking = $this->faker->boolean(50);

        return [
            'vendor_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'address' => $this->faker->address(),
            'lat' => $this->faker->latitude(),
            'lng' => $this->faker->longitude(),
            'type' => $this->faker->randomElement($types),
            'status' => $this->faker->randomElement($statuses),
            'monthly_price' => $this->faker->randomFloat(2, 1000, 10000),
            'weekly_price' => $enableWeeklyBooking ? $this->faker->randomFloat(2, 200, 2500) : null,
            'enable_weekly_booking' => $enableWeeklyBooking,
        ];
    }

    /**
     * Indicate that the hoarding is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the hoarding is a billboard.
     */
    public function billboard(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'billboard',
        ]);
    }

    /**
     * Indicate that weekly booking is enabled.
     */
    public function withWeeklyBooking(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_weekly_booking' => true,
            'weekly_price' => $this->faker->randomFloat(2, 200, 2500),
        ]);
    }
}
