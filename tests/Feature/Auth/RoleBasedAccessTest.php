<?php

use App\Models\User;

test('it redirects admin to admin panel after login', function () {
    $user = User::factory()->admin()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
});

test('it redirects musician to portal after login', function () {
    $user = User::factory()->musician()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/portal');
});

test('it prevents musician from accessing admin panel', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('it prevents inactive user from logging in', function () {
    $user = User::factory()->inactive()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('it prevents unauthenticated user from accessing portal', function () {
    $response = $this->get('/portal');

    $response->assertRedirect(route('login'));
});

test('it prevents unauthenticated user from accessing admin panel', function () {
    $response = $this->get('/admin');

    $response->assertRedirect(route('filament.admin.auth.login'));
});

test('it allows musician to access portal', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get('/portal');

    $response->assertOk();
});

test('it prevents admin from accessing musician portal', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get('/portal');

    $response->assertForbidden();
});
