<?php

use App\Models\GigAssignment;
use App\Models\User;

test('it allows musician to respond to own assignment', function () {
    $musician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $musician->id]);

    expect($musician->can('respond', $assignment))->toBeTrue();
});

test('it denies musician from responding to other musician assignment', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $otherMusician->id]);

    expect($musician->can('respond', $assignment))->toBeFalse();
});

test('it allows admin to respond to any assignment', function () {
    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $musician->id]);

    expect($admin->can('respond', $assignment))->toBeTrue();
});

test('it allows musician to view own assignment', function () {
    $musician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $musician->id]);

    expect($musician->can('view', $assignment))->toBeTrue();
});

test('it denies musician from viewing other musician assignment', function () {
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $otherMusician->id]);

    expect($musician->can('view', $assignment))->toBeFalse();
});

test('it allows admin to view any assignment', function () {
    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $musician->id]);

    expect($admin->can('view', $assignment))->toBeTrue();
});

test('it allows admin to create assignment', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', GigAssignment::class))->toBeTrue();
});

test('it denies musician from creating assignment', function () {
    $musician = User::factory()->musician()->create();

    expect($musician->can('create', GigAssignment::class))->toBeFalse();
});

test('it allows admin to delete assignment', function () {
    $admin = User::factory()->admin()->create();
    $assignment = GigAssignment::factory()->create();

    expect($admin->can('delete', $assignment))->toBeTrue();
});

test('it denies musician from deleting assignment', function () {
    $musician = User::factory()->musician()->create();
    $assignment = GigAssignment::factory()->create(['user_id' => $musician->id]);

    expect($musician->can('delete', $assignment))->toBeFalse();
});
