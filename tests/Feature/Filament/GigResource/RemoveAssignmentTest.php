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

test('it can remove assignment from gig', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('delete', $assignment)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('gig_assignments', [
        'id' => $assignment->id,
    ]);
});

test('it requires confirmation before removal', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertTableActionExists('delete');

    $this->assertDatabaseHas('gig_assignments', [
        'id' => $assignment->id,
    ]);
});

test('it removes accepted assignment successfully', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('delete', $assignment)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('gig_assignments', [
        'id' => $assignment->id,
    ]);
});

test('it removes assignment regardless of status', function () {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create();

    $statuses = [
        AssignmentStatus::Pending,
        AssignmentStatus::Accepted,
        AssignmentStatus::Declined,
        AssignmentStatus::SuboutRequested,
    ];

    foreach ($statuses as $status) {
        $musician = User::factory()->musician()->create();
        $assignment = GigAssignment::factory()->create([
            'gig_id' => $gig->id,
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
            'status' => $status,
        ]);

        Livewire::test(AssignmentsRelationManager::class, [
            'ownerRecord' => $gig,
            'pageClass' => EditGig::class,
        ])
            ->callTableAction('delete', $assignment)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('gig_assignments', [
            'id' => $assignment->id,
        ]);
    }
});

test('it can bulk delete assignments', function () {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create();

    $assignments = collect([
        GigAssignment::factory()->create([
            'gig_id' => $gig->id,
            'user_id' => User::factory()->musician()->create()->id,
            'instrument_id' => $instrument->id,
        ]),
        GigAssignment::factory()->create([
            'gig_id' => $gig->id,
            'user_id' => User::factory()->musician()->create()->id,
            'instrument_id' => $instrument->id,
        ]),
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableBulkAction('delete', $assignments);

    foreach ($assignments as $assignment) {
        $this->assertDatabaseMissing('gig_assignments', [
            'id' => $assignment->id,
        ]);
    }
});

test('it can bulk delete multiple assignments', function () {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create();

    $assignments = collect([
        GigAssignment::factory()->create([
            'gig_id' => $gig->id,
            'user_id' => User::factory()->musician()->create()->id,
            'instrument_id' => $instrument->id,
            'status' => AssignmentStatus::Pending,
        ]),
        GigAssignment::factory()->create([
            'gig_id' => $gig->id,
            'user_id' => User::factory()->musician()->create()->id,
            'instrument_id' => $instrument->id,
            'status' => AssignmentStatus::Accepted,
        ]),
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableBulkAction('delete', $assignments)
        ->assertHasNoTableBulkActionErrors();

    foreach ($assignments as $assignment) {
        $this->assertDatabaseMissing('gig_assignments', [
            'id' => $assignment->id,
        ]);
    }
});
