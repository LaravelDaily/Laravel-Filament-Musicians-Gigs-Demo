<?php

use App\Models\User;

test('it allows active user to proceed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

test('it blocks inactive user and redirects to login', function () {
    $user = User::factory()->inactive()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('error', 'Your account has been deactivated.');
});

test('it logs out inactive user', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)->get(route('dashboard'));

    $this->assertGuest();
});
