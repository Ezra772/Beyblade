<?php

namespace Database\Seeders;

use App\Models\Ratchet;
use Illuminate\Database\Seeder;

class RatchetSeeder extends Seeder
{
    public function run(): void
    {
        $ratchets = [
            [
                'name' => '5-70',
                'height' => 70.00,
                'weight' => 7.10,
                'stability' => 90.00,
                'burst_resistance' => 82.00,
            ],
            [
                'name' => '3-60',
                'height' => 60.00,
                'weight' => 6.50,
                'stability' => 75.00,
                'burst_resistance' => 70.00,
            ],
            [
                'name' => '4-80',
                'height' => 80.00,
                'weight' => 8.20,
                'stability' => 85.00,
                'burst_resistance' => 78.00,
            ],
        ];

        foreach ($ratchets as $ratchet) {
            Ratchet::firstOrCreate(['name' => $ratchet['name']], $ratchet);
        }
    }
}
