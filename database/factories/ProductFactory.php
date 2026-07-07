<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_code' => $this->faker->unique()->bothify('BX-##'),
            'series_id' => \App\Models\Series::factory(),
            'name' => $this->faker->words(3, true),
            'release_date' => $this->faker->date(),
        ];
    }
}
