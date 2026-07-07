<?php

namespace Database\Factories;

use App\Models\Beyblade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Beyblade>
 */
class BeybladeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'blade_id' => \App\Models\Blade::factory(),
            'ratchet_id' => \App\Models\Ratchet::factory(),
            'bit_id' => \App\Models\Bit::factory(),
        ];
    }
}
