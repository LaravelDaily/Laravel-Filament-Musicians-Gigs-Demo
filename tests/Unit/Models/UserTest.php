<?php

use App\Enums\UserRole;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;

it('has instruments relationship', function () {
    $user = User::factory()->create();
    $instrument = Instrument::factory()->create();
    $user->instruments()->attach($instrument);

    expect($user->instruments)->toHaveCount(1);
    expect($user->instruments->first()->id)->toBe($instrument->id);
});

it('has tags relationship', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();
    $user->tags()->attach($tag);

    expect($user->tags)->toHaveCount(1);
    expect($user->tags->first()->id)->toBe($tag->id);
});

it('has region relationship', function () {
    $region = Region::factory()->create();
    $user = User::factory()->create(['region_id' => $region->id]);

    expect($user->region->id)->toBe($region->id);
});

it('has assignments relationship', function () {
    $user = User::factory()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $user->id]);

    expect($user->assignments)->toHaveCount(1);
    expect($user->assignments->first()->id)->toBe($assignment->id);
});

it('has gigs relationship through assignments', function () {
    $user = User::factory()->create();
    $gig = Gig::factory()->create();
    GigAssignment::factory()->create(['user_id' => $user->id, 'gig_id' => $gig->id]);

    expect($user->gigs)->toHaveCount(1);
    expect($user->gigs->first()->id)->toBe($gig->id);
});

it('casts role to UserRole enum', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBeInstanceOf(UserRole::class);
    expect($user->role)->toBe(UserRole::Admin);
});

it('returns true for isAdmin when role is admin', function () {
    $user = User::factory()->admin()->create();

    expect($user->isAdmin())->toBeTrue();
    expect($user->isMusician())->toBeFalse();
});

it('returns true for isMusician when role is musician', function () {
    $user = User::factory()->musician()->create();

    expect($user->isMusician())->toBeTrue();
    expect($user->isAdmin())->toBeFalse();
});

it('uses soft deletes', function () {
    $user = User::factory()->create();
    $user->delete();

    expect(User::query()->find($user->id))->toBeNull();
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

it('scopes active users', function () {
    $activeUser = User::factory()->create(['is_active' => true]);
    $inactiveUser = User::factory()->inactive()->create();

    $activeUsers = User::active()->get();

    expect($activeUsers->pluck('id'))->toContain($activeUser->id);
    expect($activeUsers->pluck('id'))->not->toContain($inactiveUser->id);
});

it('scopes musicians only', function () {
    $musician = User::factory()->musician()->create();
    $admin = User::factory()->admin()->create();

    $musicians = User::musicians()->get();

    expect($musicians->pluck('id'))->toContain($musician->id);
    expect($musicians->pluck('id'))->not->toContain($admin->id);
});

it('scopes admins only', function () {
    $musician = User::factory()->musician()->create();
    $admin = User::factory()->admin()->create();

    $admins = User::admins()->get();

    expect($admins->pluck('id'))->toContain($admin->id);
    expect($admins->pluck('id'))->not->toContain($musician->id);
});
