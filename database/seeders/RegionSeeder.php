<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            'Downtown',
            'Midtown',
            'Uptown',
            'East Side',
            'West Side',
            'North Shore',
            'South Bay',
        ];

        foreach ($regions as $name) {
            Region::firstOrCreate(['name' => $name]);
        }
    }
}
