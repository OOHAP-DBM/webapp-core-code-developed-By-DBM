<?php

namespace Modules\Import\Database\Factories;

use Modules\Import\Entities\Import;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Import::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'file_path' => $this->faker->filePath(),
            'file_name' => $this->faker->fileName('csv'),
            'file_type' => 'csv',
            'imported_type' => $this->faker->word(),
            'status' => 'pending',
            'total_rows' => $this->faker->randomNumber(3),
            'processed_rows' => 0,
            'failed_rows' => 0,
        ];
    }

    /**
     * Indicate that the import is processing.
     */
    public function processing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
                'started_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the import is completed.
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'processed_rows' => $attributes['total_rows'],
                'started_at' => now()->subHours(2),
                'completed_at' => now(),
            ];
        });
    }

    /**
     * Indicate that the import failed.
     */
    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'error_message' => 'Import processing failed',
                'started_at' => now()->subHours(1),
                'completed_at' => now(),
            ];
        });
    }
}
