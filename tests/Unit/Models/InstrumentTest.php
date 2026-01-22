<?php

use App\Models\Instrument;
use App\Models\User;

it('has users relationship', function () {
    $instrument = Instrument::create(['name' => 'Guitar']);
    $user = User::factory()->create();
    $user->instruments()->attach($instrument);

    expect($instrument->users)->toHaveCount(1);
    expect($instrument->users->first()->id)->toBe($user->id);
});
