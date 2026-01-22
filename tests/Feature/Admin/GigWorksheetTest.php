<?php

use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->musician = User::factory()->musician()->create();
});

it('renders worksheet for gig', function (): void {
    $gig = Gig::factory()->create([
        'name' => 'Test Wedding Gig',
        'venue_name' => 'Grand Ballroom',
        'venue_address' => '123 Main Street, City, State 12345',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('Test Wedding Gig')
        ->assertSee('Grand Ballroom')
        ->assertSee('123 Main Street, City, State 12345');
});

it('includes all gig information', function (): void {
    $gig = Gig::factory()->create([
        'name' => 'Corporate Event',
        'call_time' => '17:00',
        'performance_time' => '18:00',
        'end_time' => '22:00',
        'venue_name' => 'Event Center',
        'venue_address' => '456 Event Drive',
        'client_contact_name' => 'John Client',
        'client_contact_phone' => '555-123-4567',
        'client_contact_email' => 'john@client.com',
        'dress_code' => 'Black tie formal',
        'notes' => 'Park in the back lot',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('5:00 PM')
        ->assertSee('6:00 PM')
        ->assertSee('10:00 PM')
        ->assertSee('Event Center')
        ->assertSee('456 Event Drive')
        ->assertSee('John Client')
        ->assertSee('555-123-4567')
        ->assertSee('john@client.com')
        ->assertSee('Black tie formal')
        ->assertSee('Park in the back lot');
});

it('includes all assigned musicians', function (): void {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create(['name' => 'Guitar']);

    $musician1 = User::factory()->musician()->create([
        'name' => 'Alice Musician',
        'phone' => '555-111-2222',
    ]);

    $musician2 = User::factory()->musician()->create([
        'name' => 'Bob Musician',
        'phone' => '555-333-4444',
    ]);

    GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
        'instrument_id' => $instrument->id,
    ]);

    GigAssignment::factory()->pending()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('Alice Musician')
        ->assertSee('555-111-2222')
        ->assertSee('Bob Musician')
        ->assertSee('555-333-4444')
        ->assertSee('Guitar');
});

it('includes musician phone numbers', function (): void {
    $gig = Gig::factory()->create();

    $musician = User::factory()->musician()->create([
        'name' => 'Test Musician',
        'phone' => '555-987-6543',
    ]);

    GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('555-987-6543');
});

it('includes musician instruments', function (): void {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create(['name' => 'Drums']);

    $musician = User::factory()->musician()->create();

    GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('Drums');
});

it('has print-friendly layout', function (): void {
    $gig = Gig::factory()->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig));

    $response->assertOk();
    $response->assertSee('Print Worksheet');
    $response->assertSee('@media print');
});

it('requires admin authentication', function (): void {
    $gig = Gig::factory()->create();

    $this->get(route('admin.gigs.worksheet', $gig))
        ->assertRedirect(route('login'));
});

it('denies access to musicians', function (): void {
    $gig = Gig::factory()->create();

    $this->actingAs($this->musician)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertForbidden();
});

it('shows only accepted and pending assignments', function (): void {
    $gig = Gig::factory()->create();

    $acceptedMusician = User::factory()->musician()->create(['name' => 'Accepted Player']);
    $pendingMusician = User::factory()->musician()->create(['name' => 'Pending Player']);
    $declinedMusician = User::factory()->musician()->create(['name' => 'Declined Player']);

    GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $acceptedMusician->id,
    ]);

    GigAssignment::factory()->pending()->create([
        'gig_id' => $gig->id,
        'user_id' => $pendingMusician->id,
    ]);

    GigAssignment::factory()->declined()->create([
        'gig_id' => $gig->id,
        'user_id' => $declinedMusician->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('Accepted Player')
        ->assertSee('Pending Player')
        ->assertDontSee('Declined Player');
});

it('displays assignment status', function (): void {
    $gig = Gig::factory()->create();

    $musician = User::factory()->musician()->create();

    GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('Accepted');
});

it('shows message when no musicians are assigned', function (): void {
    $gig = Gig::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.gigs.worksheet', $gig))
        ->assertOk()
        ->assertSee('No musicians assigned to this gig');
});
