<?php

use App\Models\Instrument;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;

test('it shows profile page for authenticated musician', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('My Profile');
});

test('it displays musician name', function () {
    $user = User::factory()->musician()->create([
        'name' => 'John Smith',
    ]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('John Smith');
});

test('it displays musician email', function () {
    $user = User::factory()->musician()->create([
        'email' => 'john@example.com',
    ]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('john@example.com');
});

test('it displays musician phone', function () {
    $user = User::factory()->musician()->create([
        'phone' => '555-123-4567',
    ]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('555-123-4567');
});

test('it handles missing phone gracefully', function () {
    $user = User::factory()->musician()->create([
        'phone' => null,
    ]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertDontSee('Phone');
});

test('it displays musician instruments', function () {
    $user = User::factory()->musician()->create();
    $drums = Instrument::factory()->create(['name' => 'Drums']);
    $bass = Instrument::factory()->create(['name' => 'Bass']);
    $user->instruments()->attach([$drums->id, $bass->id]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('Drums');
    $response->assertSee('Bass');
});

test('it shows message when no instruments assigned', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('No instruments assigned');
});

test('it displays musician region', function () {
    $region = Region::factory()->create(['name' => 'Portland']);
    $user = User::factory()->musician()->create([
        'region_id' => $region->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('Portland');
});

test('it shows message when no region assigned', function () {
    $user = User::factory()->musician()->create([
        'region_id' => null,
    ]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('No region assigned');
});

test('it displays musician tags', function () {
    $user = User::factory()->musician()->create();
    $jazz = Tag::factory()->create(['name' => 'Jazz']);
    $rock = Tag::factory()->create(['name' => 'Rock']);
    $user->tags()->attach([$jazz->id, $rock->id]);

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('Jazz');
    $response->assertSee('Rock');
});

test('it shows message when no tags assigned', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('No tags assigned');
});

test('it shows contact admin message', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    $response->assertSee('Need to update your profile?');
    $response->assertSee('Please contact an admin');
});

test('it does not allow editing profile', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.profile'));

    $response->assertOk();
    // Verify there are no form elements for editing
    $response->assertDontSee('<form');
    $response->assertDontSee('type="submit"');
});

test('it requires authentication', function () {
    $response = $this->get(route('portal.profile'));

    $response->assertRedirect(route('login'));
});

test('it requires musician role', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('portal.profile'));

    $response->assertForbidden();
});
