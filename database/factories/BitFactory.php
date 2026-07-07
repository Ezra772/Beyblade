<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bit>
 */
class BitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'speed' => $this->faker->randomFloat(2, 0, 100),
            'stamina' => $this->faker->randomFloat(2, 0, 100),
            'grip' => $this->faker->randomFloat(2, 0, 100),
            'control' => $this->faker->randomFloat(2, 0, 100),
            'movement' => $this->faker->randomFloat(2, 0, 100),
            'dash' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
