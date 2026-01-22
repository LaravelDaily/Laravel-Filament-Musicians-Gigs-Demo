<?php

use App\Enums\GigStatus;
use App\Filament\Exports\GigExporter;
use App\Filament\Resources\Gigs\Pages\ListGigs;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Region;
use App\Models\User;
use Filament\Actions\ExportAction;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->musician = User::factory()->musician()->create();
});

it('has export action on gigs list page', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(ListGigs::class)
        ->assertActionExists(ExportAction::class);
});

it('requires admin authentication', function (): void {
    $this->get(route('filament.admin.resources.gigs.index'))
        ->assertRedirect();
});

it('denies access to musicians', function (): void {
    $this->actingAs($this->musician);

    $this->get(route('filament.admin.resources.gigs.index'))
        ->assertForbidden();
});

it('exports gigs to CSV', function (): void {
    $this->actingAs($this->admin);

    $region = Region::factory()->create(['name' => 'Downtown']);
    $gig = Gig::factory()->active()->create([
        'name' => 'Wedding Reception',
        'venue_name' => 'Grand Hotel',
        'venue_address' => '123 Main St',
        'region_id' => $region->id,
        'date' => '2025-06-15',
    ]);

    Livewire::test(ListGigs::class)
        ->callAction(ExportAction::class);
});

it('includes all required columns', function (): void {
    $columns = GigExporter::getColumns();

    $columnNames = collect($columns)->map(fn ($col) => $col->getName())->toArray();

    expect($columnNames)->toContain('date');
    expect($columnNames)->toContain('name');
    expect($columnNames)->toContain('venue_name');
    expect($columnNames)->toContain('venue_address');
    expect($columnNames)->toContain('region.name');
    expect($columnNames)->toContain('status');
    expect($columnNames)->toContain('staffing');
});

it('formats dates correctly', function (): void {
    $gig = Gig::factory()->create([
        'date' => '2025-06-15',
    ]);

    $columns = GigExporter::getColumns();
    $dateColumn = collect($columns)->firstWhere(fn ($col) => $col->getName() === 'date');

    expect($dateColumn)->not->toBeNull();
});

it('formats status correctly', function (): void {
    $this->actingAs($this->admin);

    $gig = Gig::factory()->active()->create();

    $columns = GigExporter::getColumns();
    $statusColumn = collect($columns)->firstWhere(fn ($col) => $col->getName() === 'status');

    expect($statusColumn)->not->toBeNull();
});

it('includes staffing count', function (): void {
    $this->actingAs($this->admin);

    $gig = Gig::factory()->create();
    $musician1 = User::factory()->musician()->create();
    $musician2 = User::factory()->musician()->create();

    GigAssignment::factory()->accepted()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician1->id,
    ]);

    GigAssignment::factory()->pending()->create([
        'gig_id' => $gig->id,
        'user_id' => $musician2->id,
    ]);

    $columns = GigExporter::getColumns();
    $staffingColumn = collect($columns)->firstWhere(fn ($col) => $col->getName() === 'staffing');

    expect($staffingColumn)->not->toBeNull();
    expect($staffingColumn->getLabel())->toBe('Staffing');
});

it('applies date filters', function (): void {
    $this->actingAs($this->admin);

    $earlyGig = Gig::factory()->create(['date' => now()->addDays(5)]);
    $lateGig = Gig::factory()->create(['date' => now()->addDays(30)]);

    Livewire::test(ListGigs::class)
        ->filterTable('date_range', [
            'from' => now()->addDays(1)->toDateString(),
            'until' => now()->addDays(10)->toDateString(),
        ])
        ->callAction(ExportAction::class);
});

it('applies region filters', function (): void {
    $this->actingAs($this->admin);

    $downtown = Region::factory()->create(['name' => 'Downtown']);
    $uptown = Region::factory()->create(['name' => 'Uptown']);

    $downtownGig = Gig::factory()->create(['region_id' => $downtown->id]);
    $uptownGig = Gig::factory()->create(['region_id' => $uptown->id]);

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('region', $downtown->id)
        ->callAction(ExportAction::class);
});

it('applies status filters', function (): void {
    $this->actingAs($this->admin);

    $activeGig = Gig::factory()->active()->create();
    $draftGig = Gig::factory()->draft()->create();

    Livewire::test(ListGigs::class)
        ->removeTableFilters()
        ->filterTable('status', GigStatus::Active->value)
        ->callAction(ExportAction::class);
});
