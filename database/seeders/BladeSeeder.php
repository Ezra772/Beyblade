<?php

namespace Database\Seeders;

use App\Models\Blade;
use Illuminate\Database\Seeder;

class BladeSeeder extends Seeder
{
    public function run(): void
    {
        $blades = [
            [
                'name' => 'Wizard Rod',
                'series' => 'UX',
                'product_code' => 'UX-03',
                'weight' => 35.20,
                'attack' => 68.00,
                'defense' => 82.00,
                'stamina' => 98.00,
                'smash' => 55.00,
                'upper' => 40.00,
                'recoil' => 25.00,
                'burst_resistance' => 86.00,
            ],
            [
                'name' => 'Dran Sword',
                'series' => 'X',
                'product_code' => 'BX-01',
                'weight' => 33.50,
                'attack' => 90.00,
                'defense' => 55.00,
                'stamina' => 60.00,
                'smash' => 88.00,
                'upper' => 72.00,
                'recoil' => 70.00,
                'burst_resistance' => 50.00,
            ],
            [
                'name' => 'Hell Scythe',
                'series' => 'X',
                'product_code' => 'BX-09',
                'weight' => 34.80,
                'attack' => 45.00,
                'defense' => 92.00,
                'stamina' => 88.00,
                'smash' => 30.00,
                'upper' => 20.00,
                'recoil' => 15.00,
                'burst_resistance' => 95.00,
            ],
        ];

        foreach ($blades as $blade) {
            Blade::firstOrCreate(['name' => $blade['name']], $blade);
        }
    }
}
