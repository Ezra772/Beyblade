<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blade>
 */
class BladeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'series_id' => \App\Models\Series::factory(),
            'product_code' => $this->faker->optional()->bothify('BX-##'),
            'weight' => $this->faker->randomFloat(2, 28.00, 42.00),
            'attack' => $this->faker->randomFloat(2, 0, 100),
            'defense' => $this->faker->randomFloat(2, 0, 100),
            'stamina' => $this->faker->randomFloat(2, 0, 100),
            'smash' => $this->faker->randomFloat(2, 0, 100),
            'upper' => $this->faker->randomFloat(2, 0, 100),
            'recoil' => $this->faker->randomFloat(2, 0, 100),
            'burst_resistance' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
