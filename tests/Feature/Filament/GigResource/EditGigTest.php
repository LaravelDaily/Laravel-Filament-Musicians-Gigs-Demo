<?php

use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\GigResource;
use App\Filament\Resources\Gigs\Pages\EditGig;
use App\Models\Gig;
use App\Models\Region;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render edit gig page', function () {
    $gig = Gig::factory()->create();

    $this->get(GigResource::getUrl('edit', ['record' => $gig]))
        ->assertSuccessful();
});

test('it can update gig details', function () {
    $gig = Gig::factory()->create([
        'name' => 'Original Name',
        'venue_name' => 'Original Venue',
    ]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->fillForm([
            'name' => 'Updated Name',
            'venue_name' => 'Updated Venue',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $gig->refresh();

    expect($gig->name)->toBe('Updated Name')
        ->and($gig->venue_name)->toBe('Updated Venue');
});

test('it can change gig status', function () {
    $gig = Gig::factory()->draft()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->fillForm([
            'status' => GigStatus::Active,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $gig->refresh();

    expect($gig->status)->toBe(GigStatus::Active);
});

test('it can change gig region', function () {
    $oldRegion = Region::factory()->create(['name' => 'Old Region']);
    $newRegion = Region::factory()->create(['name' => 'New Region']);

    $gig = Gig::factory()->create(['region_id' => $oldRegion->id]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->fillForm([
            'region_id' => $newRegion->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $gig->refresh();

    expect($gig->region_id)->toBe($newRegion->id);
});

test('it can update all gig fields', function () {
    $gig = Gig::factory()->create();
    $region = Region::factory()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->fillForm([
            'name' => 'Complete Update',
            'date' => '2025-07-20',
            'call_time' => '16:00',
            'performance_time' => '17:00',
            'end_time' => '21:00',
            'venue_name' => 'New Venue',
            'venue_address' => '999 New Address',
            'client_contact_name' => 'New Contact',
            'client_contact_phone' => '111-222-3333',
            'client_contact_email' => 'new@contact.com',
            'dress_code' => 'Casual',
            'notes' => 'Updated notes',
            'pay_info' => '$300/person',
            'region_id' => $region->id,
            'status' => GigStatus::Active,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $gig->refresh();

    expect($gig->name)->toBe('Complete Update')
        ->and($gig->venue_name)->toBe('New Venue')
        ->and($gig->client_contact_name)->toBe('New Contact')
        ->and($gig->dress_code)->toBe('Casual')
        ->and($gig->notes)->toBe('Updated notes')
        ->and($gig->pay_info)->toBe('$300/person')
        ->and($gig->region_id)->toBe($region->id)
        ->and($gig->status)->toBe(GigStatus::Active);
});

test('it validates required fields on update', function () {
    $gig = Gig::factory()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->fillForm([
            'name' => '',
            'venue_name' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required', 'venue_name' => 'required']);
});
