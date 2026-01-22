<?php

namespace Database\Seeders;

use App\Models\Instrument;
use Illuminate\Database\Seeder;

class InstrumentSeeder extends Seeder
{
    public function run(): void
    {
        $instruments = [
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

        foreach ($instruments as $name) {
            Instrument::firstOrCreate(['name' => $name]);
        }
    }
}
