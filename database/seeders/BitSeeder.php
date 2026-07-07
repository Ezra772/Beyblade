<?php

namespace Database\Seeders;

use App\Models\Bit;
use Illuminate\Database\Seeder;

class BitSeeder extends Seeder
{
    public function run(): void
    {
        $bits = [
            [
                'name' => 'Ball',
                'speed' => 35.00,
                'stamina' => 100.00,
                'grip' => 40.00,
                'control' => 90.00,
                'movement' => 30.00,
                'dash' => 15.00,
            ],
            [
                'name' => 'Flat',
                'speed' => 95.00,
                'stamina' => 30.00,
                'grip' => 25.00,
                'control' => 40.00,
                'movement' => 100.00,
                'dash' => 88.00,
            ],
            [
                'name' => 'Point',
                'speed' => 50.00,
                'stamina' => 85.00,
                'grip' => 60.00,
                'control' => 75.00,
                'movement' => 45.00,
                'dash' => 30.00,
            ],
        ];

        foreach ($bits as $bit) {
            Bit::firstOrCreate(['name' => $bit['name']], $bit);
        }
    }
}
