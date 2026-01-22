<?php

use App\Filament\Resources\Musicians\MusicianResource;
use App\Filament\Resources\Musicians\Pages\ListMusicians;
use App\Models\Instrument;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render musicians list page', function () {
    $this->get(MusicianResource::getUrl('index'))
        ->assertSuccessful();
});

test('it displays musician data in table', function () {
    $musician = User::factory()->musician()->create(['name' => 'John Doe']);

    Livewire::test(ListMusicians::class)
        ->assertCanSeeTableRecords([$musician]);
});

test('it can search musicians by name', function () {
    $john = User::factory()->musician()->create(['name' => 'John Doe']);
    $jane = User::factory()->musician()->create(['name' => 'Jane Smith']);

    Livewire::test(ListMusicians::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$john])
        ->assertCanNotSeeTableRecords([$jane]);
});

test('it can search musicians by email', function () {
    $john = User::factory()->musician()->create(['email' => 'john@example.com']);
    $jane = User::factory()->musician()->create(['email' => 'jane@example.com']);

    Livewire::test(ListMusicians::class)
        ->searchTable('john@example.com')
        ->assertCanSeeTableRecords([$john])
        ->assertCanNotSeeTableRecords([$jane]);
});

test('it can filter musicians by instrument', function () {
    $guitar = Instrument::factory()->create(['name' => 'Guitar']);
    $drums = Instrument::factory()->create(['name' => 'Drums']);

    $guitarist = User::factory()->musician()->create();
    $guitarist->instruments()->attach($guitar);

    $drummer = User::factory()->musician()->create();
    $drummer->instruments()->attach($drums);

    Livewire::test(ListMusicians::class)
        ->filterTable('instruments', [$guitar->id])
        ->assertCanSeeTableRecords([$guitarist])
        ->assertCanNotSeeTableRecords([$drummer]);
});

test('it can filter musicians by region', function () {
    $downtown = Region::factory()->create(['name' => 'Downtown']);
    $uptown = Region::factory()->create(['name' => 'Uptown']);

    $downtownMusician = User::factory()->musician()->create(['region_id' => $downtown->id]);
    $uptownMusician = User::factory()->musician()->create(['region_id' => $uptown->id]);

    Livewire::test(ListMusicians::class)
        ->filterTable('region', $downtown->id)
        ->assertCanSeeTableRecords([$downtownMusician])
        ->assertCanNotSeeTableRecords([$uptownMusician]);
});

test('it can filter musicians by tag', function () {
    $jazz = Tag::factory()->create(['name' => 'Jazz']);
    $rock = Tag::factory()->create(['name' => 'Rock']);

    $jazzMusician = User::factory()->musician()->create();
    $jazzMusician->tags()->attach($jazz);

    $rockMusician = User::factory()->musician()->create();
    $rockMusician->tags()->attach($rock);

    Livewire::test(ListMusicians::class)
        ->filterTable('tags', [$jazz->id])
        ->assertCanSeeTableRecords([$jazzMusician])
        ->assertCanNotSeeTableRecords([$rockMusician]);
});

test('it can filter musicians by active status', function () {
    $activeMusician = User::factory()->musician()->create(['is_active' => true]);
    $inactiveMusician = User::factory()->musician()->create(['is_active' => false]);

    Livewire::test(ListMusicians::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$activeMusician])
        ->assertCanNotSeeTableRecords([$inactiveMusician]);

    Livewire::test(ListMusicians::class)
        ->filterTable('is_active', false)
        ->assertCanSeeTableRecords([$inactiveMusician])
        ->assertCanNotSeeTableRecords([$activeMusician]);
});

test('it can sort musicians by name', function () {
    $alice = User::factory()->musician()->create(['name' => 'Alice']);
    $bob = User::factory()->musician()->create(['name' => 'Bob']);
    $charlie = User::factory()->musician()->create(['name' => 'Charlie']);

    Livewire::test(ListMusicians::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords([$alice, $bob, $charlie], inOrder: true);

    Livewire::test(ListMusicians::class)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords([$charlie, $bob, $alice], inOrder: true);
});

test('it paginates musicians list', function () {
    User::factory()->musician()->count(25)->create();

    Livewire::test(ListMusicians::class)
        ->assertCountTableRecords(25);
});

test('it only shows users with musician role', function () {
    $musician = User::factory()->musician()->create();
    $anotherAdmin = User::factory()->admin()->create();

    Livewire::test(ListMusicians::class)
        ->assertCanSeeTableRecords([$musician])
        ->assertCanNotSeeTableRecords([$anotherAdmin]);
});
