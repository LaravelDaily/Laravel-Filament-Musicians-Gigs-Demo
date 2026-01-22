<?php

use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;

test('it shows upcoming gigs for authenticated musician', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Summer Jazz Festival',
        'venue_name' => 'Central Park',
    ]);
    $instrument = Instrument::factory()->create(['name' => 'Saxophone']);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('Summer Jazz Festival');
    $response->assertSee('Central Park');
    $response->assertSee('Saxophone');
});

test('it only shows gigs assigned to current musician', function () {
    $user = User::factory()->musician()->create();
    $otherUser = User::factory()->musician()->create();

    $myGig = Gig::factory()->active()->future()->create(['name' => 'My Gig']);
    $otherGig = Gig::factory()->active()->future()->create(['name' => 'Other Musician Gig']);

    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $myGig->id,
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $otherUser->id,
        'gig_id' => $otherGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('My Gig');
    $response->assertDontSee('Other Musician Gig');
});

test('it sorts gigs by date ascending', function () {
    $user = User::factory()->musician()->create();

    $laterGig = Gig::factory()->active()->create([
        'name' => 'Later Gig',
        'date' => now()->addWeeks(3),
    ]);
    $earlierGig = Gig::factory()->active()->create([
        'name' => 'Earlier Gig',
        'date' => now()->addWeek(),
    ]);

    GigAssignment::factory()->pending()->create(['user_id' => $user->id, 'gig_id' => $laterGig->id]);
    GigAssignment::factory()->pending()->create(['user_id' => $user->id, 'gig_id' => $earlierGig->id]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSeeInOrder(['Earlier Gig', 'Later Gig']);
});

test('it shows gig date and call time', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->create([
        'date' => now()->addWeek()->startOfDay(),
        'call_time' => '18:30',
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee($gig->date->format('M j, Y'));
    $response->assertSee('6:30 PM');
});

test('it shows venue name', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create([
        'venue_name' => 'The Grand Ballroom',
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('The Grand Ballroom');
});

test('it shows assignment status', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('Accepted');
});

test('it highlights pending assignments', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('Response Needed');
});

test('it does not show past gigs on dashboard', function () {
    $user = User::factory()->musician()->create();
    $pastGig = Gig::factory()->active()->past()->create(['name' => 'Past Event']);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $pastGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Past Event');
});

test('it does not show cancelled gigs', function () {
    $user = User::factory()->musician()->create();
    $cancelledGig = Gig::factory()->cancelled()->future()->create(['name' => 'Cancelled Event']);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $cancelledGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Cancelled Event');
});

test('it links to gig detail page', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee(route('portal.gigs.show', $gig));
});

test('it does not show declined assignments', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create(['name' => 'Declined Gig']);
    GigAssignment::factory()->declined()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Declined Gig');
});

test('it shows no upcoming gigs message when empty', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('No upcoming gigs');
    $response->assertSee('Check back later');
});

test('it does not show draft gigs', function () {
    $user = User::factory()->musician()->create();
    $draftGig = Gig::factory()->draft()->future()->create(['name' => 'Draft Event']);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $draftGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertDontSee('Draft Event');
});

test('it shows subout requested status', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->suboutRequested()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.dashboard'));

    $response->assertOk();
    $response->assertSee('Sub-out Requested');
});
