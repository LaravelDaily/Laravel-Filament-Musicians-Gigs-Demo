<?php

use App\Enums\GigStatus;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Region;

it('has region relationship', function () {
    $region = Region::factory()->create();
    $gig = Gig::factory()->create(['region_id' => $region->id]);

    expect($gig->region->id)->toBe($region->id);
});

it('has assignments relationship', function () {
    $gig = Gig::factory()->create();
    $assignment = GigAssignment::factory()->create(['gig_id' => $gig->id]);

    expect($gig->assignments)->toHaveCount(1);
    expect($gig->assignments->first()->id)->toBe($assignment->id);
});

it('has media relationship for attachments', function () {
    $gig = Gig::factory()->create();

    expect($gig->getMedia('attachments'))->toBeEmpty();
});

it('casts status to GigStatus enum', function () {
    $gig = Gig::factory()->create(['status' => GigStatus::Active]);

    expect($gig->status)->toBeInstanceOf(GigStatus::class);
    expect($gig->status)->toBe(GigStatus::Active);
});

it('uses soft deletes', function () {
    $gig = Gig::factory()->create();
    $gig->delete();

    expect(Gig::query()->find($gig->id))->toBeNull();
    expect(Gig::withTrashed()->find($gig->id))->not->toBeNull();
});

it('scopes upcoming gigs', function () {
    $pastGig = Gig::factory()->past()->create();
    $futureGig = Gig::factory()->future()->create();

    $upcomingGigs = Gig::upcoming()->get();

    expect($upcomingGigs->pluck('id'))->toContain($futureGig->id);
    expect($upcomingGigs->pluck('id'))->not->toContain($pastGig->id);
});

it('scopes active gigs', function () {
    $draftGig = Gig::factory()->draft()->create();
    $activeGig = Gig::factory()->active()->create();
    $cancelledGig = Gig::factory()->cancelled()->create();

    $activeGigs = Gig::active()->get();

    expect($activeGigs->pluck('id'))->toContain($activeGig->id);
    expect($activeGigs->pluck('id'))->not->toContain($draftGig->id);
    expect($activeGigs->pluck('id'))->not->toContain($cancelledGig->id);
});
