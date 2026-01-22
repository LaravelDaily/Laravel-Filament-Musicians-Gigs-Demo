<?php

use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\Region;
use App\Models\User;

test('it shows gig detail page for assigned musician', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create(['name' => 'Summer Jazz Festival']);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Summer Jazz Festival');
});

test('it denies access to gig not assigned to musician', function () {
    $user = User::factory()->musician()->create();
    $otherUser = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $otherUser->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertForbidden();
});

test('it displays all gig information', function () {
    $user = User::factory()->musician()->create();
    $region = Region::factory()->create(['name' => 'Downtown']);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Corporate Gala',
        'venue_name' => 'Grand Ballroom',
        'venue_address' => '123 Main Street, City',
        'dress_code' => 'Black tie',
        'notes' => 'Please arrive early for setup',
        'region_id' => $region->id,
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Corporate Gala');
    $response->assertSee('Grand Ballroom');
    $response->assertSee('123 Main Street, City');
    $response->assertSee('Black tie');
    $response->assertSee('Please arrive early for setup');
    $response->assertSee('Downtown');
});

test('it displays call time and optional times', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create([
        'call_time' => '17:30',
        'performance_time' => '18:30',
        'end_time' => '22:00',
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('5:30 PM'); // Call Time
    $response->assertSee('6:30 PM'); // Performance Time
    $response->assertSee('10:00 PM'); // End Time
});

test('it displays venue with map link', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create([
        'venue_name' => 'City Convention Center',
        'venue_address' => '500 Convention Way, Metro City',
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('City Convention Center');
    $response->assertSee('500 Convention Way, Metro City');
    $response->assertSee('View on Google Maps');
    $response->assertSee('https://www.google.com/maps/search/');
});

test('it displays dress code and notes', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create([
        'dress_code' => 'Business casual',
        'notes' => 'Parking available at rear entrance',
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Business casual');
    $response->assertSee('Parking available at rear entrance');
});

test('it displays musician assignment details', function () {
    $user = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create(['name' => 'Guitar']);
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
        'pay_amount' => 250.00,
        'notes' => 'Solo during third set',
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Guitar');
    $response->assertSee('$250.00');
    $response->assertSee('Solo during third set');
});

test('it shows other assigned musicians', function () {
    $user = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create(['name' => 'Jane Drummer']);
    $drumInstrument = Instrument::factory()->create(['name' => 'Drums']);
    $gig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);
    GigAssignment::factory()->accepted()->create([
        'user_id' => $otherMusician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $drumInstrument->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Jane Drummer');
    $response->assertSee('Drums');
});

test('it shows accept and decline buttons for pending assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Accept Gig');
    $response->assertSee('Decline');
});

test('it shows sub-out section for accepted assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Need to cancel?');
    $response->assertSee('Request Sub-out');
    $response->assertDontSee('Accept Gig');
});

test('it hides action buttons for declined assignment', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->declined()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertDontSee('Accept Gig');
    $response->assertDontSee('Respond to Assignment');
    $response->assertDontSee('Need to cancel?');
});

test('it shows correct status badge for each status', function () {
    $user = User::factory()->musician()->create();

    // Test pending status
    $pendingGig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $pendingGig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $pendingGig));
    $response->assertSee('Response Needed');
});

test('it shows accepted status badge', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->accepted()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));
    $response->assertSee('Accepted');
});

test('it shows declined status badge', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->declined()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Declined');
});

test('it shows subout requested status badge', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->suboutRequested()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));
    $response->assertSee('Sub-out Requested');
});

test('it does not show declined musicians in other musicians list', function () {
    $user = User::factory()->musician()->create();
    $declinedMusician = User::factory()->musician()->create(['name' => 'Declined Dave']);
    $gig = Gig::factory()->active()->future()->create();

    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);
    GigAssignment::factory()->declined()->create([
        'user_id' => $declinedMusician->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertDontSee('Declined Dave');
});

test('it shows back to dashboard link', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->future()->create();
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Back to Dashboard');
    $response->assertSee(route('portal.dashboard'));
});

test('it shows formatted date with day of week', function () {
    $user = User::factory()->musician()->create();
    $gig = Gig::factory()->active()->create([
        'date' => '2025-03-15', // Saturday
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $user->id,
        'gig_id' => $gig->id,
    ]);

    $response = $this->actingAs($user)->get(route('portal.gigs.show', $gig));

    $response->assertOk();
    $response->assertSee('Saturday, March 15, 2025');
});
