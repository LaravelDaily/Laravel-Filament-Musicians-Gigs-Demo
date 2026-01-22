<?php

use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;

test('it shows past gigs for authenticated musician', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee($gig->name);
});

test('it only shows gigs with date in the past', function () {
    $user = User::factory()->musician()->create();
    $pastGig = Gig::factory()->active()->past()->create(['name' => 'Past Gig']);
    $futureGig = Gig::factory()->active()->future()->create(['name' => 'Future Gig']);

    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $pastGig->id,
    ]);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $futureGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('Past Gig');
    $response->assertDontSee('Future Gig');
});

test('it only shows gigs assigned to current musician', function () {
    $user = User::factory()->musician()->create();
    $otherUser = User::factory()->musician()->create();

    $myGig = Gig::factory()->active()->past()->create(['name' => 'My Past Gig']);
    $otherGig = Gig::factory()->active()->past()->create(['name' => 'Other Past Gig']);

    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $myGig->id,
    ]);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $otherUser->id,
        'gig_id' => $otherGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('My Past Gig');
    $response->assertDontSee('Other Past Gig');
});

test('it sorts by date descending', function () {
    $user = User::factory()->musician()->create();

    $olderGig = Gig::factory()->active()->create([
        'name' => 'Older Gig',
        'date' => now()->subDays(30),
    ]);
    $newerGig = Gig::factory()->active()->create([
        'name' => 'Newer Gig',
        'date' => now()->subDays(5),
    ]);

    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $olderGig->id,
    ]);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $newerGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSeeInOrder(['Newer Gig', 'Older Gig']);
});

test('it shows gig date', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->create([
        'date' => now()->subDays(10),
    ]);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee($gig->date->format('M j, Y'));
});

test('it shows venue name', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create([
        'venue_name' => 'Historic Jazz Club',
    ]);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('Historic Jazz Club');
});

test('it shows instrument', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create();
    $instrument = Instrument::factory()->create(['name' => 'Saxophone']);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('Saxophone');
});

test('it shows final assignment status for accepted', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('Accepted');
});

test('it shows final assignment status for declined', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create();
    GigAssignment::factory()->declined()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('Declined');
});

test('it links to gig detail page', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee(route('portal.gigs.show', $gig));
});

test('it paginates results', function () {
    $user = User::factory()->musician()->create();

    // Create more than 12 gigs to trigger pagination
    for ($i = 0; $i < 15; $i++) {
        $gig = Gig::factory()->active()->create([
            'date' => now()->subDays($i + 1),
        ]);
        GigAssignment::factory()->accepted()->create([
            'user_id' => $user->id,
            'gig_id' => $gig->id,
        ]);
    }

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    // Should only show 12 items per page
    expect($response->viewData('assignments')->count())->toBe(12);
    expect($response->viewData('assignments')->hasPages())->toBeTrue();
});

test('it shows empty state when no past gigs', function () {
    $user = User::factory()->musician()->create();

    $response = $this->actingAs($user)->get(route('portal.gigs.past'));

    $response->assertOk();
    $response->assertSee('No past gigs');
});

test('it does not show action buttons on past gig detail', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertDontSee('Request Sub-out');
    $response->assertDontSee('Need to cancel?');
});

test('it does not show action buttons on past gig detail for pending status', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->past()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertDontSee('Accept Gig');
    $response->assertDontSee('Decline');
    $response->assertDontSee('Respond to Assignment');
});

test('it requires authentication', function () {
    $response = $this->get(route('portal.gigs.past'));

    $response->assertRedirect(route('login'));
});

test('it requires musician role', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('portal.gigs.past'));

    $response->assertForbidden();
});
