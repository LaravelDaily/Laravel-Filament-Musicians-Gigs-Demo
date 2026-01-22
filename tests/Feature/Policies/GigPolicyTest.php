<?php

use App\Models\Gig;
use App\Models\User;

test('it allows admin to view any gigs', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('viewAny', Gig::class))->toBeTrue();
});

test('it allows admin to create gig', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', Gig::class))->toBeTrue();
});

test('it allows admin to update gig', function () {
    $admin = User::factory()->admin()->create();
    $gig = Gig::factory()->create();

    expect($admin->can('update', $gig))->toBeTrue();
});

test('it allows admin to delete gig', function () {
    $admin = User::factory()->admin()->create();
    $gig = Gig::factory()->create();

    expect($admin->can('delete', $gig))->toBeTrue();
});

test('it denies musician from creating gig', function () {
    $musician = User::factory()->musician()->create();

    expect($musician->can('create', Gig::class))->toBeFalse();
});

test('it denies musician from updating gig', function () {
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->create();

    expect($musician->can('update', $gig))->toBeFalse();
});

test('it denies musician from deleting gig', function () {
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->create();

    expect($musician->can('delete', $gig))->toBeFalse();
});

test('it allows admin to view gig', function () {
    $admin = User::factory()->admin()->create();
    $gig = Gig::factory()->create();

    expect($admin->can('view', $gig))->toBeTrue();
});

test('it denies musician from viewing gig via policy', function () {
    $musician = User::factory()->musician()->create();
    $gig = Gig::factory()->create();

    expect($musician->can('view', $gig))->toBeFalse();
});
