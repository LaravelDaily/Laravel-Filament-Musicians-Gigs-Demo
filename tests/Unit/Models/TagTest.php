<?php

use App\Models\Tag;
use App\Models\User;

it('has users relationship', function () {
    $tag = Tag::create(['name' => 'Jazz']);
    $user = User::factory()->create();
    $user->tags()->attach($tag);

    expect($tag->users)->toHaveCount(1);
    expect($tag->users->first()->id)->toBe($user->id);
});
