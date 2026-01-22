<?php

use App\Filament\Resources\Musicians\Pages\EditMusician;
use App\Filament\Resources\Musicians\Pages\ListMusicians;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can deactivate active musician', function () {
    $musician = User::factory()->musician()->create(['is_active' => true]);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->callAction('toggle_active')
        ->assertHasNoActionErrors();

    $musician->refresh();
    expect($musician->is_active)->toBeFalse();
});

test('it can reactivate inactive musician', function () {
    $musician = User::factory()->musician()->create(['is_active' => false]);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->callAction('toggle_active')
        ->assertHasNoActionErrors();

    $musician->refresh();
    expect($musician->is_active)->toBeTrue();
});

test('it prevents deactivated musician from accessing portal', function () {
    $musician = User::factory()->musician()->create([
        'is_active' => false,
    ]);

    $this->actingAs($musician)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('it preserves historical assignments when deactivating', function () {
    $musician = User::factory()->musician()->create(['is_active' => true]);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->callAction('toggle_active')
        ->assertHasNoActionErrors();

    $musician->refresh();
    expect($musician->is_active)->toBeFalse();
    $this->assertDatabaseHas('users', ['id' => $musician->id]);
});

test('it can bulk deactivate musicians', function () {
    $musicians = User::factory()->musician()->count(3)->create(['is_active' => true]);

    Livewire::test(ListMusicians::class)
        ->callTableBulkAction('deactivate', $musicians)
        ->assertHasNoTableBulkActionErrors();

    foreach ($musicians as $musician) {
        $musician->refresh();
        expect($musician->is_active)->toBeFalse();
    }
});

test('it can bulk activate musicians', function () {
    $musicians = User::factory()->musician()->count(3)->create(['is_active' => false]);

    Livewire::test(ListMusicians::class)
        ->callTableBulkAction('activate', $musicians)
        ->assertHasNoTableBulkActionErrors();

    foreach ($musicians as $musician) {
        $musician->refresh();
        expect($musician->is_active)->toBeTrue();
    }
});
