<?php

use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\Pages\EditGig;
use App\Filament\Resources\Gigs\Pages\ListGigs;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can cancel gig', function () {
    $gig = Gig::factory()->active()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('cancel')
        ->assertNotified('Gig cancelled');

    $gig->refresh();

    expect($gig->status)->toBe(GigStatus::Cancelled);
});

test('it shows cancelled gig in list with indicator', function () {
    $cancelledGig = Gig::factory()->cancelled()->create();

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->assertCanSeeTableRecords([$cancelledGig]);
});

test('it can soft delete gig', function () {
    $gig = Gig::factory()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('delete');

    $this->assertSoftDeleted('gigs', ['id' => $gig->id]);
});

test('it soft deletes associated assignments when gig deleted', function () {
    $gig = Gig::factory()->create();
    $musician = User::factory()->musician()->create();

    $assignment = GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician->id,
    ]);

    $gig->delete();

    $this->assertSoftDeleted('gigs', ['id' => $gig->id]);
    $this->assertDatabaseHas('gig_assignments', ['id' => $assignment->id]);
});

test('it requires confirmation before cancel', function () {
    $gig = Gig::factory()->active()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->assertActionExists('cancel')
        ->mountAction('cancel')
        ->assertActionMounted('cancel');
});

test('it requires confirmation before delete', function () {
    $gig = Gig::factory()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->assertActionExists('delete');
});

test('it can restore soft deleted gig', function () {
    $gig = Gig::factory()->create();
    $gig->delete();

    $this->assertSoftDeleted('gigs', ['id' => $gig->id]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->callAction('restore');

    $this->assertNotSoftDeleted('gigs', ['id' => $gig->id]);
});

test('it does not show cancel action for already cancelled gig', function () {
    $gig = Gig::factory()->cancelled()->create();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->assertActionHidden('cancel');
});

test('it does not show cancel action for trashed gig', function () {
    $gig = Gig::factory()->create();
    $gig->delete();

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->assertActionHidden('cancel');
});
