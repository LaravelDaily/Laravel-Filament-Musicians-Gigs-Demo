<?php

use App\Enums\AssignmentStatus;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;

it('displays portal correctly on mobile viewport', function () {
    $user = User::factory()->musician()->create([
        'email' => 'mobile-test@example.com',
        'password' => bcrypt('password'),
    ]);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Summer Jazz Festival',
        'venue_name' => 'Central Park Amphitheater',
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $page = visit('/login')->on()->mobile();

    $page->fill('email', 'mobile-test@example.com')
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/portal')
        ->assertSee('Welcome')
        ->assertSee('Summer Jazz Festival')
        ->assertSee('Central Park Amphitheater')
        ->assertNoJavascriptErrors();
});

it('can navigate portal on mobile', function () {
    $user = User::factory()->musician()->create([
        'email' => 'navigate-test@example.com',
        'password' => bcrypt('password'),
    ]);

    $page = visit('/login')->on()->mobile();

    $page->fill('email', 'navigate-test@example.com')
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/portal')
        ->assertSee('Welcome')
        ->assertNoJavascriptErrors();
});

it('can accept gig on mobile', function () {
    $user = User::factory()->musician()->create([
        'email' => 'accept-test@example.com',
        'password' => bcrypt('password'),
    ]);
    $instrument = Instrument::factory()->create(['name' => 'Guitar']);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Jazz Night',
        'venue_name' => 'Blue Note Club',
    ]);
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $page = visit('/login')->on()->mobile();

    $page->fill('email', 'accept-test@example.com')
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/portal')
        ->click('Jazz Night')
        ->assertSee('Jazz Night')
        ->assertSee('Response Needed')
        ->assertSee('Accept Gig')
        ->click('Accept Gig')
        ->assertSee('accepted this gig')
        ->assertNoJavascriptErrors();

    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::Accepted);
});

it('can decline gig on mobile', function () {
    $user = User::factory()->musician()->create([
        'email' => 'decline-test@example.com',
        'password' => bcrypt('password'),
    ]);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Rock Concert',
    ]);
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $page = visit('/login')->on()->mobile();

    $page->fill('email', 'decline-test@example.com')
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/portal')
        ->click('Rock Concert')
        ->assertSee('Rock Concert')
        ->assertSee('Decline')
        ->click('Decline')
        ->assertSee('declined this gig')
        ->assertNoJavascriptErrors();

    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::Declined);
});

it('can request sub-out on mobile', function () {
    $user = User::factory()->musician()->create([
        'email' => 'subout-test@example.com',
        'password' => bcrypt('password'),
    ]);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Wedding Reception',
    ]);
    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $page = visit('/login')->on()->mobile();

    $page->fill('email', 'subout-test@example.com')
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/portal')
        ->click('Wedding Reception')
        ->assertSee('Wedding Reception')
        ->assertSee('Accepted')
        ->assertSee('Request Sub-out')
        ->fill('reason', 'Family emergency')
        ->click('Request Sub-out')
        ->assertSee('sub-out request has been submitted')
        ->assertNoJavascriptErrors();

    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::SuboutRequested);
    expect($assignment->subout_reason)->toBe('Family emergency');
});

it('can download attachments on mobile', function () {
    $user = User::factory()->musician()->create([
        'email' => 'attachment-test@example.com',
        'password' => bcrypt('password'),
    ]);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Corporate Event',
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $page = visit('/login')->on()->mobile();

    $page->fill('email', 'attachment-test@example.com')
        ->fill('password', 'password')
        ->click('Log in')
        ->assertPathIs('/portal')
        ->click('Corporate Event')
        ->assertSee('Corporate Event')
        ->assertNoJavascriptErrors();
});
