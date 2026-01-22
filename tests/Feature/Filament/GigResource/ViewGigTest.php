<?php

use App\Enums\AssignmentStatus;
use App\Filament\Resources\Gigs\GigResource;
use App\Filament\Resources\Gigs\Pages\EditGig;
use App\Filament\Resources\Gigs\Pages\ViewGig;
use App\Filament\Resources\Gigs\RelationManagers\AssignmentsRelationManager;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render view gig page', function () {
    $gig = Gig::factory()->create();

    $this->get(GigResource::getUrl('view', ['record' => $gig]))
        ->assertSuccessful();
});

test('it displays all gig information', function () {
    $gig = Gig::factory()->create([
        'name' => 'Wedding Reception',
        'venue_name' => 'Grand Ballroom',
        'venue_address' => '123 Main St',
        'dress_code' => 'Black tie',
        'notes' => 'Important event',
    ]);

    Livewire::test(ViewGig::class, ['record' => $gig->id])
        ->assertSee('Wedding Reception')
        ->assertSee('Grand Ballroom')
        ->assertSee('Black tie')
        ->assertSee('Important event');
});

test('it displays assignments in relation manager', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create(['name' => 'John Musician']);
    $instrument = Instrument::factory()->create(['name' => 'Guitar']);

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee('John Musician')
        ->assertSee('Guitar');
});

test('it shows assignment status in relation manager', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee('Accepted');
});

test('it highlights sub-out requests in relation manager', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::SuboutRequested,
        'subout_reason' => 'Family emergency',
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee('Sub-out Requested')
        ->assertSee('Family emergency');
});

test('it shows sub-out reason in relation manager', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::SuboutRequested,
        'subout_reason' => 'Medical appointment',
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee('Medical appointment');
});

test('it shows response timestamps in relation manager', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $respondedAt = now()->subDay();
    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Accepted,
        'responded_at' => $respondedAt,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee($respondedAt->format('M j'));
});

test('it displays call time and optional times', function () {
    $gig = Gig::factory()->create([
        'call_time' => '17:00',
        'performance_time' => '18:00',
        'end_time' => '22:00',
    ]);

    $response = $this->get(GigResource::getUrl('view', ['record' => $gig]));

    $response->assertSee('5:00 PM')
        ->assertSee('6:00 PM')
        ->assertSee('10:00 PM');
});

test('it displays venue with address', function () {
    $gig = Gig::factory()->create([
        'venue_name' => 'Grand Hotel',
        'venue_address' => '500 Park Avenue, New York, NY',
    ]);

    Livewire::test(ViewGig::class, ['record' => $gig->id])
        ->assertSee('Grand Hotel')
        ->assertSee('500 Park Avenue');
});
