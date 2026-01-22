<?php

use App\Filament\Exports\GigAssignmentExporter;
use App\Filament\Pages\ExportAssignments;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use Filament\Actions\ExportAction;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->musician = User::factory()->musician()->create();
});

it('can render export assignments page', function (): void {
    $this->actingAs($this->admin)
        ->get(ExportAssignments::getUrl())
        ->assertSuccessful();
});

it('has export action on page', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(ExportAssignments::class)
        ->assertActionExists(ExportAction::class);
});

it('requires admin authentication', function (): void {
    $this->get(ExportAssignments::getUrl())
        ->assertRedirect();
});

it('denies access to musicians', function (): void {
    $this->actingAs($this->musician)
        ->get(ExportAssignments::getUrl())
        ->assertForbidden();
});

it('exports assignments to CSV', function (): void {
    $this->actingAs($this->admin);

    $gig = Gig::factory()->create();
    $instrument = Instrument::factory()->create();

    GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $this->musician->id,
        'instrument_id' => $instrument->id,
    ]);

    Livewire::test(ExportAssignments::class)
        ->callAction(ExportAction::class);
});

it('includes all required columns', function (): void {
    $columns = GigAssignmentExporter::getColumns();

    $columnNames = collect($columns)->map(fn ($col) => $col->getName())->toArray();

    expect($columnNames)->toContain('gig.date');
    expect($columnNames)->toContain('gig.name');
    expect($columnNames)->toContain('user.name');
    expect($columnNames)->toContain('user.email');
    expect($columnNames)->toContain('instrument.name');
    expect($columnNames)->toContain('status');
    expect($columnNames)->toContain('pay_amount');
});

it('can filter by date range', function (): void {
    $this->actingAs($this->admin);

    $earlyGig = Gig::factory()->create(['date' => now()->addDays(5)]);
    $lateGig = Gig::factory()->create(['date' => now()->addDays(30)]);

    GigAssignment::factory()->create([
        'gig_id' => $earlyGig->id,
        'user_id' => $this->musician->id,
    ]);

    GigAssignment::factory()->create([
        'gig_id' => $lateGig->id,
        'user_id' => $this->musician->id,
    ]);

    Livewire::test(ExportAssignments::class)
        ->set('date_from', now()->addDays(1)->toDateString())
        ->set('date_until', now()->addDays(10)->toDateString())
        ->callAction(ExportAction::class);
});

it('can filter by musician', function (): void {
    $this->actingAs($this->admin);

    $musician1 = User::factory()->musician()->create();
    $musician2 = User::factory()->musician()->create();

    $gig = Gig::factory()->create();

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
    ]);

    GigAssignment::factory()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
    ]);

    Livewire::test(ExportAssignments::class)
        ->set('musician_id', $musician1->id)
        ->callAction(ExportAction::class);
});

it('formats data correctly', function (): void {
    $gig = Gig::factory()->create([
        'date' => '2025-06-15',
    ]);

    $instrument = Instrument::factory()->create(['name' => 'Guitar']);

    $assignment = GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $this->musician->id,
        'instrument_id' => $instrument->id,
        'pay_amount' => 150.00,
    ]);

    $columns = GigAssignmentExporter::getColumns();

    expect($columns)->not->toBeEmpty();
});

it('shows filters on page', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(ExportAssignments::class)
        ->assertSee('Filter Assignments')
        ->assertSee('From Date')
        ->assertSee('Until Date')
        ->assertSee('Musician');
});
