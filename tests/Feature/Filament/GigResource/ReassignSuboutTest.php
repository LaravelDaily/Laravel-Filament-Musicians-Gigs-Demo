<?php

use App\Enums\AssignmentStatus;
use App\Filament\Resources\Gigs\Pages\EditGig;
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

test('it shows sub-out assignments prominently', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->suboutRequested()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'subout_reason' => 'Family emergency',
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee('Sub-out Requested')
        ->assertSee('Family emergency');
});

test('find replacement action exists in relation manager', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->suboutRequested()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertCanSeeTableRecords([$assignment])
        ->assertTableActionExists('findReplacement');
});

test('find replacement action is hidden for non-subout assignments', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $pendingAssignment = GigAssignment::factory()->pending()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertTableActionHidden('findReplacement', $pendingAssignment);
});

test('replacement assignment copies instrument from original', function () {
    $gig = Gig::factory()->create();
    $originalMusician = User::factory()->musician()->create();
    $replacementMusician = User::factory()->musician()->create();
    $drums = Instrument::factory()->create(['name' => 'Drums']);

    $replacementMusician->instruments()->attach($drums);

    GigAssignment::factory()->suboutRequested()->create([
        'gig_id' => $gig->id,
        'user_id' => $originalMusician->id,
        'instrument_id' => $drums->id,
    ]);

    $newAssignment = GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $replacementMusician->id,
        'instrument_id' => $drums->id,
        'status' => AssignmentStatus::Pending,
        'notes' => 'Replacement for '.$originalMusician->name,
    ]);

    expect($newAssignment->instrument_id)->toBe($drums->id);
    expect($newAssignment->status)->toBe(AssignmentStatus::Pending);
});

test('replacement assignment copies pay amount from original', function () {
    $gig = Gig::factory()->create();
    $originalMusician = User::factory()->musician()->create();
    $replacementMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $originalAssignment = GigAssignment::factory()->suboutRequested()->create([
        'gig_id' => $gig->id,
        'user_id' => $originalMusician->id,
        'instrument_id' => $instrument->id,
        'pay_amount' => 200.00,
    ]);

    $newAssignment = GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $replacementMusician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
        'pay_amount' => $originalAssignment->pay_amount,
        'notes' => 'Replacement for '.$originalMusician->name,
    ]);

    expect((float) $newAssignment->pay_amount)->toBe(200.00);
});

test('original sub-out assignment remains after replacement is created', function () {
    $gig = Gig::factory()->create();
    $originalMusician = User::factory()->musician()->create();
    $replacementMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $suboutAssignment = GigAssignment::factory()->suboutRequested()->create([
        'gig_id' => $gig->id,
        'user_id' => $originalMusician->id,
        'instrument_id' => $instrument->id,
    ]);

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $replacementMusician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    $suboutAssignment->refresh();
    expect($suboutAssignment->status)->toBe(AssignmentStatus::SuboutRequested);

    $this->assertDatabaseHas('gig_assignments', [
        'id' => $suboutAssignment->id,
        'status' => AssignmentStatus::SuboutRequested->value,
    ]);
});

test('replacement musician must not already be assigned to gig', function () {
    $gig = Gig::factory()->create();
    $musician1 = User::factory()->musician()->create();
    $musician2 = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
        'instrument_id' => $instrument->id,
    ]);

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
        'instrument_id' => $instrument->id,
    ]);

    expect(GigAssignment::where('gig_id', $gig->id)->count())->toBe(2);
});

test('musicians can have conflicting gigs on same date', function () {
    $gigDate = now()->addWeek();
    $gig1 = Gig::factory()->create(['date' => $gigDate]);
    $gig2 = Gig::factory()->create(['date' => $gigDate]);
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig1->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    GigAssignment::factory()->create([
        'gig_id' => $gig2->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    $conflictingAssignments = GigAssignment::query()
        ->where('user_id', $musician->id)
        ->whereHas('gig', fn ($q) => $q->where('date', $gigDate))
        ->count();

    expect($conflictingAssignments)->toBe(2);
});

test('sub-out assignments display reason', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->suboutRequested()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'subout_reason' => 'Medical appointment',
    ]);

    expect($assignment->subout_reason)->toBe('Medical appointment');
    expect($assignment->status)->toBe(AssignmentStatus::SuboutRequested);
});
