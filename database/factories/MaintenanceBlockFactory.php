<?php

namespace Database\Factories;

use App\Models\MaintenanceBlock;
use App\Models\Hoarding;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceBlock>
 */
class MaintenanceBlockFactory extends Factory
{
    protected $model = MaintenanceBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::today()->addDays(rand(1, 30));
        $endDate = $startDate->copy()->addDays(rand(3, 10));

        return [
            'hoarding_id' => Hoarding::factory(),
            'created_by' => User::factory(),
            'title' => fake()->randomElement([
                'Annual Maintenance',
                'Painting Work',
                'Structural Repair',
                'Board Replacement',
                'Lighting Installation',
                'Safety Inspection',
            ]),
            'description' => fake()->sentence(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => MaintenanceBlock::STATUS_ACTIVE,
            'block_type' => fake()->randomElement([
                MaintenanceBlock::TYPE_MAINTENANCE,
                MaintenanceBlock::TYPE_REPAIR,
                MaintenanceBlock::TYPE_INSPECTION,
                MaintenanceBlock::TYPE_OTHER,
            ]),
            'affected_by' => fake()->optional()->randomElement(['Weather', 'Permits', 'Parts Availability']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the block is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MaintenanceBlock::STATUS_ACTIVE,
        ]);
    }

    /**
     * Indicate that the block is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MaintenanceBlock::STATUS_COMPLETED,
        ]);
    }

    /**
     * Indicate that the block is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => MaintenanceBlock::STATUS_CANCELLED,
        ]);
    }

    /**
     * Set specific dates for the block.
     */
    public function withDates(Carbon $startDate, Carbon $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
