<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    private static array $tags = [
        'Jazz',
        'Rock',
        'Blues',
        'Soul',
        'Funk',
        'R&B',
        'Wedding',
        'Corporate',
        'Private Event',
        'Festival',
    ];

    private static int $tagIndex = 0;

    public function definition(): array
    {
        $name = self::$tags[self::$tagIndex % count(self::$tags)].' '.self::$tagIndex;
        self::$tagIndex++;

        return [
            'name' => $name,
        ];
    }
}
