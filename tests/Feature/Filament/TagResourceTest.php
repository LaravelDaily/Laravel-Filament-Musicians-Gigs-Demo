<?php

use App\Filament\Resources\Tags\Pages\ManageTags;
use App\Filament\Resources\Tags\TagResource;
use App\Models\Tag;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render tags list page', function () {
    $this->get(TagResource::getUrl('index'))
        ->assertSuccessful();
});

test('it can render create tag page', function () {
    Livewire::test(ManageTags::class)
        ->assertActionExists('create');
});

test('it can create tag', function () {
    Livewire::test(ManageTags::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jazz',
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('tags', [
        'name' => 'Jazz',
    ]);
});

test('it can render edit tag page', function () {
    $tag = Tag::factory()->create();

    Livewire::test(ManageTags::class)
        ->assertTableActionExists('edit');
});

test('it can update tag', function () {
    $tag = Tag::factory()->create(['name' => 'Old Name']);

    Livewire::test(ManageTags::class)
        ->callTableAction('edit', $tag, data: [
            'name' => 'New Name',
        ])
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
        'name' => 'New Name',
    ]);
});

test('it can delete tag without musicians', function () {
    $tag = Tag::factory()->create();

    Livewire::test(ManageTags::class)
        ->callTableAction(DeleteAction::class, $tag)
        ->assertHasNoTableActionErrors();

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->id,
    ]);
});

test('it cannot delete tag with musicians assigned', function () {
    $tag = Tag::factory()->create();
    $musician = User::factory()->musician()->create();
    $musician->tags()->attach($tag);

    Livewire::test(ManageTags::class)
        ->callTableAction(DeleteAction::class, $tag)
        ->assertNotified('Cannot delete tag');

    $this->assertDatabaseHas('tags', [
        'id' => $tag->id,
    ]);
});

test('it validates name is required', function () {
    Livewire::test(ManageTags::class)
        ->callAction(CreateAction::class, data: [
            'name' => '',
        ])
        ->assertHasActionErrors(['name' => 'required']);
});

test('it validates name is unique', function () {
    Tag::factory()->create(['name' => 'Rock']);

    Livewire::test(ManageTags::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Rock',
        ])
        ->assertHasActionErrors(['name' => 'unique']);
});

test('it displays tag data in table', function () {
    $tag = Tag::factory()->create(['name' => 'Blues']);

    Livewire::test(ManageTags::class)
        ->assertCanSeeTableRecords([$tag]);
});

test('it can search tags by name', function () {
    $jazz = Tag::factory()->create(['name' => 'Jazz']);
    $rock = Tag::factory()->create(['name' => 'Rock']);

    Livewire::test(ManageTags::class)
        ->searchTable('Jazz')
        ->assertCanSeeTableRecords([$jazz])
        ->assertCanNotSeeTableRecords([$rock]);
});
