<?php

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Enums\UserRole;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;

it('creates region using factory', function () {
    $region = Region::factory()->create();

    expect($region)->toBeInstanceOf(Region::class);
    expect($region->name)->not->toBeEmpty();
});

it('creates instrument using factory', function () {
    $instrument = Instrument::factory()->create();

    expect($instrument)->toBeInstanceOf(Instrument::class);
    expect($instrument->name)->not->toBeEmpty();
});

it('creates tag using factory', function () {
    $tag = Tag::factory()->create();

    expect($tag)->toBeInstanceOf(Tag::class);
    expect($tag->name)->not->toBeEmpty();
});

it('creates gig using factory with default values', function () {
    $gig = Gig::factory()->create();

    expect($gig)->toBeInstanceOf(Gig::class);
    expect($gig->name)->not->toBeEmpty();
    expect($gig->date)->not->toBeNull();
    expect($gig->call_time)->not->toBeNull();
    expect($gig->venue_name)->not->toBeEmpty();
    expect($gig->venue_address)->not->toBeEmpty();
    expect($gig->status)->toBe(GigStatus::Draft);
});

it('creates gig with draft state', function () {
    $gig = Gig::factory()->draft()->create();

    expect($gig->status)->toBe(GigStatus::Draft);
});

it('creates gig with active state', function () {
    $gig = Gig::factory()->active()->create();

    expect($gig->status)->toBe(GigStatus::Active);
});

it('creates gig assignment using factory', function () {
    $assignment = GigAssignment::factory()->create();

    expect($assignment)->toBeInstanceOf(GigAssignment::class);
    expect($assignment->gig_id)->not->toBeNull();
    expect($assignment->user_id)->not->toBeNull();
    expect($assignment->instrument_id)->not->toBeNull();
    expect($assignment->status)->toBe(AssignmentStatus::Pending);
});

it('creates user with musician state', function () {
    $user = User::factory()->musician()->create();

    expect($user->role)->toBe(UserRole::Musician);
});

it('creates user with admin state', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin);
});

it('creates user with inactive state', function () {
    $user = User::factory()->inactive()->create();

    expect($user->is_active)->toBeFalse();
});

it('creates user with instruments', function () {
    $user = User::factory()->create();
    $instrument = Instrument::factory()->create();
    $user->instruments()->attach($instrument);

    expect($user->instruments)->toHaveCount(1);
});

it('creates user with tags', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();
    $user->tags()->attach($tag);

    expect($user->tags)->toHaveCount(1);
});

it('creates user with region', function () {
    $user = User::factory()->withRegion()->create();

    expect($user->region_id)->not->toBeNull();
    expect($user->region)->toBeInstanceOf(Region::class);
});
