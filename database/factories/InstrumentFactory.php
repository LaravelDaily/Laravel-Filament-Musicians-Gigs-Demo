<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instrument>
 */
class InstrumentFactory extends Factory
{
    private static array $instruments = [
        'Drums',
        'Bass',
        'Guitar',
        'Keys',
        'Vocals',
        'Saxophone',
        'Trumpet',
        'Trombone',
        'Violin',
        'Percussion',
    ];

    private static int $instrumentIndex = 0;

    public function definition(): array
    {
        $name = self::$instruments[self::$instrumentIndex % count(self::$instruments)].' '.self::$instrumentIndex;
        self::$instrumentIndex++;

        return [
            'name' => $name,
        ];
    }
}
