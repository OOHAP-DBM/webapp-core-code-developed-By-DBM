<?php

namespace Modules\Offers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Offers\Entities\Offer;

class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'discount' => $this->faker->randomFloat(2, 5, 50),
            'valid_from' => $this->faker->date(),
            'valid_to' => $this->faker->date(),
        ];
    }
}
