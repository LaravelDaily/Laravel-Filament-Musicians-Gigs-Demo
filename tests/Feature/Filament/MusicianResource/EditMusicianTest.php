<?php

use App\Filament\Resources\Musicians\MusicianResource;
use App\Filament\Resources\Musicians\Pages\EditMusician;
use App\Models\Instrument;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render edit musician page', function () {
    $musician = User::factory()->musician()->create();

    $this->get(MusicianResource::getUrl('edit', ['record' => $musician]))
        ->assertSuccessful();
});

test('it can update musician name', function () {
    $musician = User::factory()->musician()->create(['name' => 'Old Name']);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'name' => 'New Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $musician->id,
        'name' => 'New Name',
    ]);
});

test('it can update musician email', function () {
    $musician = User::factory()->musician()->create(['email' => 'old@example.com']);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'email' => 'new@example.com',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $musician->id,
        'email' => 'new@example.com',
    ]);
});

test('it can update musician phone', function () {
    $musician = User::factory()->musician()->create(['phone' => '111-111-1111']);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'phone' => '222-222-2222',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $musician->id,
        'phone' => '222-222-2222',
    ]);
});

test('it can update musician instruments', function () {
    $guitar = Instrument::factory()->create(['name' => 'Guitar']);
    $bass = Instrument::factory()->create(['name' => 'Bass']);
    $drums = Instrument::factory()->create(['name' => 'Drums']);

    $musician = User::factory()->musician()->create();
    $musician->instruments()->attach([$guitar->id, $bass->id]);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'instruments' => [$drums->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $musician->refresh();
    expect($musician->instruments)->toHaveCount(1)
        ->and($musician->instruments->first()->id)->toBe($drums->id);
});

test('it can update musician region', function () {
    $oldRegion = Region::factory()->create(['name' => 'Old Region']);
    $newRegion = Region::factory()->create(['name' => 'New Region']);

    $musician = User::factory()->musician()->create(['region_id' => $oldRegion->id]);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'region_id' => $newRegion->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $musician->id,
        'region_id' => $newRegion->id,
    ]);
});

test('it can update musician tags', function () {
    $jazz = Tag::factory()->create(['name' => 'Jazz']);
    $rock = Tag::factory()->create(['name' => 'Rock']);
    $blues = Tag::factory()->create(['name' => 'Blues']);

    $musician = User::factory()->musician()->create();
    $musician->tags()->attach([$jazz->id, $rock->id]);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'tags' => [$blues->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $musician->refresh();
    expect($musician->tags)->toHaveCount(1)
        ->and($musician->tags->first()->id)->toBe($blues->id);
});

test('it can update musician notes', function () {
    $musician = User::factory()->musician()->create(['notes' => 'Old notes']);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'notes' => 'New notes',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $musician->id,
        'notes' => 'New notes',
    ]);
});

test('it validates email uniqueness on update', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);
    $musician = User::factory()->musician()->create(['email' => 'musician@example.com']);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'email' => 'existing@example.com',
        ])
        ->call('save')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('it allows same email when not changed', function () {
    $musician = User::factory()->musician()->create(['email' => 'musician@example.com']);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->fillForm([
            'name' => 'Updated Name',
            'email' => 'musician@example.com',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $musician->id,
        'name' => 'Updated Name',
        'email' => 'musician@example.com',
    ]);
});
