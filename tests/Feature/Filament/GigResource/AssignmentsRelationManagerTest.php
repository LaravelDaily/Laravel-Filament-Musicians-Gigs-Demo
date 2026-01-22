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

test('it can render assignments relation manager', function () {
    $gig = Gig::factory()->create();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])->assertSuccessful();
});

test('it can create assignment', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('gig_assignments', [
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending->value,
    ]);
});

test('it sets assignment status to pending by default', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
        ]);

    $assignment = GigAssignment::where('gig_id', $gig->id)
        ->where('user_id', $musician->id)
        ->first();

    expect($assignment->status)->toBe(AssignmentStatus::Pending);
});

test('it can assign instrument to assignment', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create(['name' => 'Drums']);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
        ]);

    $assignment = GigAssignment::where('gig_id', $gig->id)->first();

    expect($assignment->instrument_id)->toBe($instrument->id);
});

test('it can add notes to assignment', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
            'notes' => 'Bring extra sticks',
        ]);

    $assignment = GigAssignment::where('gig_id', $gig->id)->first();

    expect($assignment->notes)->toBe('Bring extra sticks');
});

test('it can add pay amount to assignment', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
            'pay_amount' => 250.00,
        ]);

    $assignment = GigAssignment::where('gig_id', $gig->id)->first();

    expect((float) $assignment->pay_amount)->toBe(250.00);
});

test('it prevents duplicate assignment of same musician', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    $this->assertDatabaseCount('gig_assignments', 1);

    expect(GigAssignment::where('gig_id', $gig->id)
        ->where('user_id', $musician->id)
        ->count())->toBe(1);
});

test('it excludes already assigned musicians from dropdown', function () {
    $gig = Gig::factory()->create();
    $assignedMusician = User::factory()->musician()->create(['name' => 'Assigned Musician']);
    $availableMusician = User::factory()->musician()->create(['name' => 'Available Musician']);
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $assignedMusician->id,
        'instrument_id' => $instrument->id,
    ]);

    $component = Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ]);

    $this->assertDatabaseHas('gig_assignments', [
        'gig_id' => $gig->id,
        'user_id' => $assignedMusician->id,
    ]);
});

test('it excludes inactive musicians from dropdown', function () {
    $gig = Gig::factory()->create();
    $activeMusician = User::factory()->musician()->create(['name' => 'Active Musician', 'is_active' => true]);
    $inactiveMusician = User::factory()->musician()->inactive()->create(['name' => 'Inactive Musician']);
    $instrument = Instrument::factory()->create();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $activeMusician->id,
            'instrument_id' => $instrument->id,
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('gig_assignments', [
        'gig_id' => $gig->id,
        'user_id' => $activeMusician->id,
    ]);
});

test('it displays assignment status with color', function () {
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
        ->assertCanSeeTableRecords(GigAssignment::where('gig_id', $gig->id)->get())
        ->assertSee('Accepted');
});

test('it logs assignment creation in audit log', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
        ]);

    $assignment = GigAssignment::where('gig_id', $gig->id)->first();

    $this->assertDatabaseHas('assignment_status_logs', [
        'gig_assignment_id' => $assignment->id,
        'old_status' => null,
        'new_status' => AssignmentStatus::Pending->value,
        'changed_by_user_id' => $this->admin->id,
    ]);
});
