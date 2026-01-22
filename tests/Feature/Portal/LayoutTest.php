<?php

use App\Models\User;

test('it shows portal navigation when authenticated as musician', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('Dashboard');
    $response->assertSee('Past Gigs');
    $response->assertSee('My Profile');
});

test('it redirects to login when not authenticated', function () {
    $response = $this->get(route('portal.dashboard'));

    $response->assertRedirect(route('login'));
});

test('it blocks admin from accessing portal', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertForbidden();
});

test('it can logout from portal', function () {
    $user = User::factory()->musician()->create();

    $this->actingAs($user);

    $response = $this->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('it shows user name in header', function () {
    $user = User::factory()->musician()->create([
        'name' => 'John Doe',
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('Welcome, John Doe');
});

test('it can navigate to past gigs page', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('Past Gigs');
});

test('it can navigate to profile page', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('My Profile');
});
