<?php

use App\Filament\Resources\Instruments\InstrumentResource;
use App\Filament\Resources\Instruments\Pages\ManageInstruments;
use App\Models\Instrument;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render instruments list page', function () {
    $this->get(InstrumentResource::getUrl('index'))
        ->assertSuccessful();
});

test('it can render create instrument page', function () {
    Livewire::test(ManageInstruments::class)
        ->assertActionExists('create');
});

test('it can create instrument', function () {
    Livewire::test(ManageInstruments::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Guitar',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('instruments', [
        'name' => 'Guitar',
    ]);
});

test('it can render edit instrument page', function () {
    $instrument = Instrument::factory()->create();

    Livewire::test(ManageInstruments::class)
        ->assertTableActionExists('edit');
});

test('it can update instrument', function () {
    $instrument = Instrument::factory()->create(['name' => 'Old Name']);

    Livewire::test(ManageInstruments::class)
        ->callTableAction('edit', $instrument, data: [
            'name' => 'New Name',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('instruments', [
        'id' => $instrument->id,
        'name' => 'New Name',
    ]);
});

test('it can delete instrument without musicians', function () {
    $instrument = Instrument::factory()->create();

    Livewire::test(ManageInstruments::class)
        ->callTableAction(DeleteAction::class, $instrument)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('instruments', [
        'id' => $instrument->id,
    ]);
});

test('it cannot delete instrument with musicians assigned', function () {
    $instrument = Instrument::factory()->create();
    $musician = User::factory()->musician()->create();
    $musician->instruments()->attach($instrument);

    Livewire::test(ManageInstruments::class)
        ->callTableAction(DeleteAction::class, $instrument)
        ->assertNotified('Cannot delete instrument');

    $this->assertDatabaseHas('instruments', [
        'id' => $instrument->id,
    ]);
});

test('it validates name is required', function () {
    Livewire::test(ManageInstruments::class)
        ->callAction(CreateAction::class, data: [
            'name' => '',
        ])
        ->assertHasActionErrors(['name' => 'required']);
});

test('it validates name is unique', function () {
    Instrument::factory()->create(['name' => 'Drums']);

    Livewire::test(ManageInstruments::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Drums',
        ])
        ->assertHasActionErrors(['name' => 'unique']);
});

test('it displays musician data in table', function () {
    $instrument = Instrument::factory()->create(['name' => 'Bass']);

    Livewire::test(ManageInstruments::class)
        ->assertCanSeeTableRecords([$instrument]);
});

test('it can search instruments by name', function () {
    $guitar = Instrument::factory()->create(['name' => 'Guitar']);
    $drums = Instrument::factory()->create(['name' => 'Drums']);

    Livewire::test(ManageInstruments::class)
        ->searchTable('Guitar')
        ->assertCanSeeTableRecords([$guitar])
        ->assertCanNotSeeTableRecords([$drums]);
});
