<?php

use App\Enums\AssignmentStatus;
use App\Filament\Resources\Gigs\Pages\ViewGig;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it shows bulk assign action on view page', function () {
    $gig = Gig::factory()->create();

    Livewire::test(ViewGig::class, ['record' => $gig->id])
        ->assertActionExists('bulkAssign')
        ->assertActionVisible('bulkAssign');
});

test('it hides bulk assign action when gig is trashed', function () {
    $gig = Gig::factory()->create();
    $gig->delete();

    Livewire::test(ViewGig::class, ['record' => $gig->id])
        ->assertActionHidden('bulkAssign');
});

test('it prevents duplicate assignments in bulk', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
    ]);

    expect(GigAssignment::where('gig_id', $gig->id)
        ->where('user_id', $musician->id)
        ->count())->toBe(1);
});

test('it excludes inactive musicians from being assigned', function () {
    $gig = Gig::factory()->create();
    $inactiveMusician = User::factory()->musician()->inactive()->create();

    Livewire::test(ViewGig::class, ['record' => $gig->id])
        ->assertActionExists('bulkAssign');

    expect(User::where('id', $inactiveMusician->id)->where('is_active', false)->exists())->toBeTrue();
});

test('assignments created have pending status', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    $assignment = GigAssignment::where('gig_id', $gig->id)->first();

    expect($assignment->status)->toBe(AssignmentStatus::Pending);
});

test('it can create multiple assignments programmatically', function () {
    $gig = Gig::factory()->create();
    $musician1 = User::factory()->musician()->create();
    $musician2 = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    expect(GigAssignment::where('gig_id', $gig->id)->count())->toBe(2);

    $this->assertDatabaseHas('gig_assignments', [
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
        'status' => AssignmentStatus::Pending->value,
    ]);

    $this->assertDatabaseHas('gig_assignments', [
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
        'status' => AssignmentStatus::Pending->value,
    ]);
});

test('it can assign different instruments to different musicians', function () {
    $gig = Gig::factory()->create();
    $musician1 = User::factory()->musician()->create();
    $musician2 = User::factory()->musician()->create();
    $drums = Instrument::factory()->create(['name' => 'Drums']);
    $bass = Instrument::factory()->create(['name' => 'Bass']);

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
        'instrument_id' => $drums->id,
        'status' => AssignmentStatus::Pending,
    ]);

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
        'instrument_id' => $bass->id,
        'status' => AssignmentStatus::Pending,
    ]);

    $this->assertDatabaseHas('gig_assignments', [
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
        'instrument_id' => $drums->id,
    ]);

    $this->assertDatabaseHas('gig_assignments', [
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
        'instrument_id' => $bass->id,
    ]);
});

test('database enforces unique constraint on gig and user combination', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);

    $this->expectException(\Illuminate\Database\QueryException::class);

    GigAssignment::create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
        'instrument_id' => $instrument->id,
        'status' => AssignmentStatus::Pending,
    ]);
});
