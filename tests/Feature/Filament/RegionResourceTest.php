<?php

use App\Filament\Resources\Regions\Pages\ManageRegions;
use App\Filament\Resources\Regions\RegionResource;
use App\Models\Gig;
use App\Models\Region;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render regions list page', function () {
    $this->get(RegionResource::getUrl('index'))
        ->assertSuccessful();
});

test('it can render create region page', function () {
    Livewire::test(ManageRegions::class)
        ->assertActionExists('create');
});

test('it can create region', function () {
    Livewire::test(ManageRegions::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'North',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('regions', [
        'name' => 'North',
    ]);
});

test('it can render edit region page', function () {
    $region = Region::factory()->create();

    Livewire::test(ManageRegions::class)
        ->assertTableActionExists('edit');
});

test('it can update region', function () {
    $region = Region::factory()->create(['name' => 'Old Name']);

    Livewire::test(ManageRegions::class)
        ->callTableAction('edit', $region, data: [
            'name' => 'New Name',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('regions', [
        'id' => $region->id,
        'name' => 'New Name',
    ]);
});

test('it can delete region without users or gigs', function () {
    $region = Region::factory()->create();

    Livewire::test(ManageRegions::class)
        ->callTableAction(DeleteAction::class, $region)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('regions', [
        'id' => $region->id,
    ]);
});

test('it cannot delete region with users assigned', function () {
    $region = Region::factory()->create();
    User::factory()->musician()->create(['region_id' => $region->id]);

    Livewire::test(ManageRegions::class)
        ->callTableAction(DeleteAction::class, $region)
        ->assertNotified('Cannot delete region');

    $this->assertDatabaseHas('regions', [
        'id' => $region->id,
    ]);
});

test('it cannot delete region with gigs assigned', function () {
    $region = Region::factory()->create();
    Gig::factory()->create(['region_id' => $region->id]);

    Livewire::test(ManageRegions::class)
        ->callTableAction(DeleteAction::class, $region)
        ->assertNotified('Cannot delete region');

    $this->assertDatabaseHas('regions', [
        'id' => $region->id,
    ]);
});

test('it validates name is required', function () {
    Livewire::test(ManageRegions::class)
        ->callAction(CreateAction::class, data: [
            'name' => '',
        ])
        ->assertHasActionErrors(['name' => 'required']);
});

test('it validates name is unique', function () {
    Region::factory()->create(['name' => 'Downtown']);

    Livewire::test(ManageRegions::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Downtown',
        ])
        ->assertHasActionErrors(['name' => 'unique']);
});

test('it displays region data in table', function () {
    $region = Region::factory()->create(['name' => 'South']);

    Livewire::test(ManageRegions::class)
        ->assertCanSeeTableRecords([$region]);
});

test('it can search regions by name', function () {
    $north = Region::factory()->create(['name' => 'North']);
    $south = Region::factory()->create(['name' => 'South']);

    Livewire::test(ManageRegions::class)
        ->searchTable('North')
        ->assertCanSeeTableRecords([$north])
        ->assertCanNotSeeTableRecords([$south]);
});
