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

test('it displays correct status colors', function () {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create();

    $assignments = [];
    foreach (AssignmentStatus::cases() as $status) {
        $musician = User::factory()->musician()->create();
        $assignments[] = GigAssignment::factory()->create([
            'gig_id' => $gig->id,
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
            'status' => $status,
        ]);
    }

    $component = Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ]);

    foreach (AssignmentStatus::cases() as $status) {
        $component->assertSee($status->getLabel());
    }
});

test('it shows status counts summary', function () {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->count(2)->create([
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    GigAssignment::factory()->count(3)->create([
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertCanSeeTableRecords(GigAssignment::where('gig_id', $gig->id)->get());

    expect(GigAssignment::where('gig_id', $gig->id)
        ->where('status', AssignmentStatus::Pending)
        ->count())->toBe(2);

    expect(GigAssignment::where('gig_id', $gig->id)
        ->where('status', AssignmentStatus::Accepted)
        ->count())->toBe(3);
});

test('it can manually change assignment status', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('changeStatus', $assignment, data: [
            'status' => AssignmentStatus::Accepted->value,
        ])
        ->assertHasNoTableActionErrors();

    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::Accepted);
});

test('it logs manual status change in audit log', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('changeStatus', $assignment, data: [
            'status' => AssignmentStatus::Accepted->value,
        ]);

    $this->assertDatabaseHas('assignment_status_logs', [
        'gig_assignment_id' => $assignment->id,
        'old_status' => AssignmentStatus::Pending->value,
        'new_status' => AssignmentStatus::Accepted->value,
        'changed_by_user_id' => $this->admin->id,
    ]);
});

test('it can change status to declined with reason', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('changeStatus', $assignment, data: [
            'status' => AssignmentStatus::Declined->value,
            'reason' => 'Schedule conflict',
        ]);

    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::Declined);
    expect($assignment->decline_reason)->toBe('Schedule conflict');
});

test('it can change status to subout requested with reason', function () {
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
        ->callTableAction('changeStatus', $assignment, data: [
            'status' => AssignmentStatus::SuboutRequested->value,
            'reason' => 'Family emergency',
        ]);

    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::SuboutRequested);
    expect($assignment->subout_reason)->toBe('Family emergency');
});

test('it updates responded_at when status changes', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
        'responded_at' => null,
    ]);

    $this->travel(1)->hours();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('changeStatus', $assignment, data: [
            'status' => AssignmentStatus::Accepted->value,
        ]);

    $assignment->refresh();
    expect($assignment->responded_at)->not->toBeNull();
});

test('it shows warning when status is unchanged', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('changeStatus', $assignment, data: [
            'status' => AssignmentStatus::Pending->value,
        ])
        ->assertNotified();
});

test('it can filter assignments by status', function () {
    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create();

    $pendingAssignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => User::factory()->musician()->create()->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    $acceptedAssignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => User::factory()->musician()->create()->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Accepted,
    ]);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->filterTable('status', AssignmentStatus::Pending->value)
        ->assertCanSeeTableRecords([$pendingAssignment])
        ->assertCanNotSeeTableRecords([$acceptedAssignment]);
});
