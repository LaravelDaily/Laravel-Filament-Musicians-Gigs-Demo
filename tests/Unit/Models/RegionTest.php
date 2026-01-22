<?php

use App\Models\Gig;
use App\Models\Region;
use App\Models\User;

it('has users relationship', function () {
    $region = Region::create(['name' => 'Test Region']);
    $user = User::factory()->create(['region_id' => $region->id]);

    expect($region->users)->toHaveCount(1);
    expect($region->users->first()->id)->toBe($user->id);
});

it('has gigs relationship', function () {
    $region = Region::create(['name' => 'Test Region']);
    $gig = Gig::factory()->create(['region_id' => $region->id]);

    expect($region->gigs)->toHaveCount(1);
    expect($region->gigs->first()->id)->toBe($gig->id);
});
