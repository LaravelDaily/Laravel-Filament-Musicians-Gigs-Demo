<?php

use App\Models\User;

test('it allows admin to view any users', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('viewAny', User::class))->toBeTrue();
});

test('it allows admin to create user', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('create', User::class))->toBeTrue();
});

test('it allows admin to update user', function () {
    $admin = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    expect($admin->can('update', $otherUser))->toBeTrue();
});

test('it allows admin to delete other user', function () {
    $admin = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    expect($admin->can('delete', $otherUser))->toBeTrue();
});

test('it denies admin from deleting self', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('delete', $admin))->toBeFalse();
});

test('it denies musician from creating user', function () {
    $musician = User::factory()->musician()->create();

    expect($musician->can('create', User::class))->toBeFalse();
});

test('it denies musician from updating other user', function () {
    $musician = User::factory()->musician()->create();
    $otherUser = User::factory()->create();

    expect($musician->can('update', $otherUser))->toBeFalse();
});

test('it denies musician from viewing any users', function () {
    $musician = User::factory()->musician()->create();

    expect($musician->can('viewAny', User::class))->toBeFalse();
});

test('it denies musician from deleting user', function () {
    $musician = User::factory()->musician()->create();
    $otherUser = User::factory()->create();

    expect($musician->can('delete', $otherUser))->toBeFalse();
});

test('it allows admin to view user', function () {
    $admin = User::factory()->admin()->create();
    $otherUser = User::factory()->create();

    expect($admin->can('view', $otherUser))->toBeTrue();
});

test('it denies admin from force deleting self', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->can('forceDelete', $admin))->toBeFalse();
});
