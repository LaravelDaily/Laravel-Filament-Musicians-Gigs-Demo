<?php

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\GigResource;
use App\Filament\Resources\Gigs\Pages\ListGigs;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Region;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render gigs list page', function () {
    $this->get(GigResource::getUrl('index'))
        ->assertSuccessful();
});

test('it displays gig data in table', function () {
    $gig = Gig::factory()->create(['name' => 'Test Gig Event']);

    Livewire::test(ListGigs::class)
        ->assertCanSeeTableRecords([$gig]);
});

test('it shows staffing status count', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    Livewire::test(ListGigs::class)
        ->assertCanSeeTableRecords([$gig]);
});

test('it can search gigs by name', function () {
    $wedding = Gig::factory()->create(['name' => 'Wedding Reception']);
    $corporate = Gig::factory()->create(['name' => 'Corporate Event']);

    Livewire::test(ListGigs::class)
        ->searchTable('Wedding')
        ->assertCanSeeTableRecords([$wedding])
        ->assertCanNotSeeTableRecords([$corporate]);
});

test('it can search gigs by venue', function () {
    $hilton = Gig::factory()->create(['venue_name' => 'Hilton Hotel']);
    $marriott = Gig::factory()->create(['venue_name' => 'Marriott Center']);

    Livewire::test(ListGigs::class)
        ->searchTable('Hilton')
        ->assertCanSeeTableRecords([$hilton])
        ->assertCanNotSeeTableRecords([$marriott]);
});

test('it can filter gigs by date range', function () {
    $earlyGig = Gig::factory()->create(['date' => now()->addDays(5)]);
    $lateGig = Gig::factory()->create(['date' => now()->addDays(30)]);

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('date_range', [
            'from' => now()->addDays(1)->toDateString(),
            'until' => now()->addDays(10)->toDateString(),
        ])
        ->assertCanSeeTableRecords([$earlyGig])
        ->assertCanNotSeeTableRecords([$lateGig]);
});

test('it can filter gigs by region', function () {
    $downtown = Region::factory()->create(['name' => 'Downtown']);
    $uptown = Region::factory()->create(['name' => 'Uptown']);

    $downtownGig = Gig::factory()->create(['region_id' => $downtown->id]);
    $uptownGig = Gig::factory()->create(['region_id' => $uptown->id]);

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('region', $downtown->id)
        ->assertCanSeeTableRecords([$downtownGig])
        ->assertCanNotSeeTableRecords([$uptownGig]);
});

test('it can filter gigs by status', function () {
    $activeGig = Gig::factory()->active()->create();
    $draftGig = Gig::factory()->draft()->create();
    $cancelledGig = Gig::factory()->cancelled()->create();

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('status', GigStatus::Active->value)
        ->assertCanSeeTableRecords([$activeGig])
        ->assertCanNotSeeTableRecords([$draftGig, $cancelledGig]);
});

test('it can filter gigs needing musicians', function () {
    $needsMusicians = Gig::factory()->create();

    $fullyStaffed = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    GigAssignment::factory()->create([
        'gig_id' => $fullyStaffed->id,
        'user_id' => $musician->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('staffing', 'needs_musicians')
        ->assertCanSeeTableRecords([$needsMusicians]);
});

test('it can filter gigs with pending responses', function () {
    $hasPending = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    GigAssignment::factory()->create([
        'gig_id' => $hasPending->id,
        'user_id' => $musician->id,
        'status' => AssignmentStatus::Pending,
    ]);

    $noPending = Gig::factory()->create();

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('staffing', 'has_pending')
        ->assertCanSeeTableRecords([$hasPending])
        ->assertCanNotSeeTableRecords([$noPending]);
});

test('it can filter gigs with sub-out requests', function () {
    $hasSubout = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    GigAssignment::factory()->create([
        'gig_id' => $hasSubout->id,
        'user_id' => $musician->id,
        'status' => AssignmentStatus::SuboutRequested,
    ]);

    $noSubout = Gig::factory()->create();

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('staffing', 'has_subouts')
        ->assertCanSeeTableRecords([$hasSubout])
        ->assertCanNotSeeTableRecords([$noSubout]);
});

test('it sorts gigs by date ascending by default', function () {
    $laterGig = Gig::factory()->create(['date' => now()->addDays(10)]);
    $earlierGig = Gig::factory()->create(['date' => now()->addDays(5)]);
    $middleGig = Gig::factory()->create(['date' => now()->addDays(7)]);

    Livewire::test(ListGigs::class)
        ->assertCanSeeTableRecords([$earlierGig, $middleGig, $laterGig], inOrder: true);
});

test('it can view past gigs', function () {
    $pastGig = Gig::factory()->past()->create();
    $futureGig = Gig::factory()->future()->create();

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->assertCanSeeTableRecords([$pastGig, $futureGig]);
});
