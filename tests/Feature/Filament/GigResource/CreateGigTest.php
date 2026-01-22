<?php

use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\GigResource;
use App\Filament\Resources\Gigs\Pages\CreateGig;
use App\Models\Gig;
use App\Models\Region;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render create gig page', function () {
    $this->get(GigResource::getUrl('create'))
        ->assertSuccessful();
});

test('it can create gig with required fields only', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Test Gig',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => 'Test Venue',
            'venue_address' => '123 Main St',
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('gigs', [
        'name' => 'Test Gig',
        'venue_name' => 'Test Venue',
        'status' => GigStatus::Draft->value,
    ]);
});

test('it can create gig with all fields', function () {
    $region = Region::factory()->create();

    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Full Gig',
            'date' => '2025-06-15',
            'call_time' => '17:00',
            'performance_time' => '18:00',
            'end_time' => '22:00',
            'venue_name' => 'Grand Ballroom',
            'venue_address' => '456 Event Ave, City, ST 12345',
            'client_contact_name' => 'John Client',
            'client_contact_phone' => '555-123-4567',
            'client_contact_email' => 'john@client.com',
            'dress_code' => 'Black tie',
            'notes' => 'Important notes here',
            'pay_info' => '$200/musician',
            'region_id' => $region->id,
            'status' => GigStatus::Active,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $gig = Gig::where('name', 'Full Gig')->first();

    expect($gig)->not->toBeNull()
        ->and($gig->venue_name)->toBe('Grand Ballroom')
        ->and($gig->client_contact_name)->toBe('John Client')
        ->and($gig->client_contact_email)->toBe('john@client.com')
        ->and($gig->dress_code)->toBe('Black tie')
        ->and($gig->notes)->toBe('Important notes here')
        ->and($gig->pay_info)->toBe('$200/musician')
        ->and($gig->region_id)->toBe($region->id)
        ->and($gig->status)->toBe(GigStatus::Active);
});

test('it can create gig with region', function () {
    $region = Region::factory()->create(['name' => 'Downtown']);

    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Regional Gig',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => 'Downtown Hall',
            'venue_address' => '789 Center St',
            'region_id' => $region->id,
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $gig = Gig::where('name', 'Regional Gig')->first();
    expect($gig->region_id)->toBe($region->id);
});

test('it validates name is required', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => '',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => 'Test Venue',
            'venue_address' => '123 Main St',
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('it validates date is required', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Test Gig',
            'date' => null,
            'call_time' => '18:00',
            'venue_name' => 'Test Venue',
            'venue_address' => '123 Main St',
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['date' => 'required']);
});

test('it validates call_time is required', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Test Gig',
            'date' => '2025-06-15',
            'call_time' => null,
            'venue_name' => 'Test Venue',
            'venue_address' => '123 Main St',
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['call_time' => 'required']);
});

test('it validates venue_name is required', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Test Gig',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => '',
            'venue_address' => '123 Main St',
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['venue_name' => 'required']);
});

test('it validates venue_address is required', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Test Gig',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => 'Test Venue',
            'venue_address' => '',
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['venue_address' => 'required']);
});

test('it validates client_contact_email format when provided', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Test Gig',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => 'Test Venue',
            'venue_address' => '123 Main St',
            'client_contact_email' => 'invalid-email',
            'status' => GigStatus::Draft,
        ])
        ->call('create')
        ->assertHasFormErrors(['client_contact_email' => 'email']);
});

test('it sets status to draft by default', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Default Status Gig',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => 'Test Venue',
            'venue_address' => '123 Main St',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $gig = Gig::where('name', 'Default Status Gig')->first();
    expect($gig->status)->toBe(GigStatus::Draft);
});

test('it can set status to active on creation', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Active Gig',
            'date' => '2025-06-15',
            'call_time' => '18:00',
            'venue_name' => 'Test Venue',
            'venue_address' => '123 Main St',
            'status' => GigStatus::Active,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $gig = Gig::where('name', 'Active Gig')->first();
    expect($gig->status)->toBe(GigStatus::Active);
});
