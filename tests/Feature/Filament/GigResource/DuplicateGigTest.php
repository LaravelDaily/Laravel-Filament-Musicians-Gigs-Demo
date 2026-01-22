<?php

use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\Pages\EditGig;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Region;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can duplicate gig', function () {
    $gig = Gig::factory()->active()->create([
        'name' => 'Original Gig',
        'venue_name' => 'Original Venue',
    ]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('replicate')
        ->assertNotified('Gig duplicated');

    $this->assertDatabaseCount('gigs', 2);

    $newGig = Gig::where('id', '!=', $gig->id)->first();
    expect($newGig->name)->toBe('Original Gig')
        ->and($newGig->venue_name)->toBe('Original Venue');
});

test('it copies date on duplicated gig', function () {
    $gig = Gig::factory()->create([
        'date' => '2025-06-15',
    ]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('replicate');

    $newGig = Gig::where('id', '!=', $gig->id)->first();
    expect($newGig->date->format('Y-m-d'))->toBe('2025-06-15');
});

test('it sets status to draft on duplicated gig', function () {
    $gig = Gig::factory()->active()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('replicate');

    $newGig = Gig::where('id', '!=', $gig->id)->first();
    expect($newGig->status)->toBe(GigStatus::Draft);
});

test('it copies all other fields', function () {
    $region = Region::factory()->create();

    $gig = Gig::factory()->create([
        'name' => 'Event Name',
        'date' => '2025-06-15',
        'call_time' => '17:00',
        'performance_time' => '18:00',
        'end_time' => '22:00',
        'venue_name' => 'Grand Ballroom',
        'venue_address' => '123 Main St',
        'client_contact_name' => 'John Client',
        'client_contact_phone' => '555-1234',
        'client_contact_email' => 'john@client.com',
        'dress_code' => 'Black tie',
        'notes' => 'Important notes',
        'pay_info' => '$200/person',
        'region_id' => $region->id,
    ]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('replicate');

    $newGig = Gig::where('id', '!=', $gig->id)->first();

    expect($newGig->name)->toBe('Event Name')
        ->and($newGig->call_time->format('H:i'))->toBe('17:00')
        ->and($newGig->performance_time->format('H:i'))->toBe('18:00')
        ->and($newGig->end_time->format('H:i'))->toBe('22:00')
        ->and($newGig->venue_name)->toBe('Grand Ballroom')
        ->and($newGig->venue_address)->toBe('123 Main St')
        ->and($newGig->client_contact_name)->toBe('John Client')
        ->and($newGig->client_contact_phone)->toBe('555-1234')
        ->and($newGig->client_contact_email)->toBe('john@client.com')
        ->and($newGig->dress_code)->toBe('Black tie')
        ->and($newGig->notes)->toBe('Important notes')
        ->and($newGig->pay_info)->toBe('$200/person')
        ->and($newGig->region_id)->toBe($region->id);
});

test('it does not copy assignments', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
    ]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('replicate');

    $newGig = Gig::where('id', '!=', $gig->id)->first();

    expect($newGig->assignments)->toHaveCount(0);
});

test('it redirects to edit page after duplication', function () {
    $gig = Gig::factory()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('replicate')
        ->assertRedirect();
});

test('it does not show duplicate action for trashed gig', function () {
    $gig = Gig::factory()->create();
    $gig->delete();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->assertActionHidden('replicate');
});
