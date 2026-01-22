<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    private static array $regions = [
        'Downtown',
        'Midtown',
        'Uptown',
        'East Side',
        'West Side',
        'North Shore',
        'South Bay',
        'Central District',
        'Harbor Area',
        'Financial District',
    ];

    private static int $regionIndex = 0;

    public function definition(): array
    {
        $name = self::$regions[self::$regionIndex % count(self::$regions)].' '.self::$regionIndex;
        self::$regionIndex++;

        return [
            'name' => $name,
        ];
    }
}
