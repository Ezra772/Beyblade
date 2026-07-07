<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ratchet>
 */
class RatchetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $prongs = $this->faker->numberBetween(3, 9);
        $height = $this->faker->randomElement([60, 70, 80, 85]);

        return [
            'name' => "{$prongs}-{$height}-" . $this->faker->unique()->randomNumber(3),
            'height' => (float) $height,
            'weight' => $this->faker->randomFloat(2, 5.00, 10.00),
            'stability' => $this->faker->randomFloat(2, 0, 100),
            'burst_resistance' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
