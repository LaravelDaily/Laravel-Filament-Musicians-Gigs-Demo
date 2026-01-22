<?php

use App\Enums\UserRole;
use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Filament\Resources\AdminUsers\Pages\CreateAdminUser;
use App\Filament\Resources\AdminUsers\Pages\EditAdminUser;
use App\Filament\Resources\AdminUsers\Pages\ListAdminUsers;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can list admin users', function () {
    $this->get(AdminUserResource::getUrl('index'))
        ->assertSuccessful();
});

test('it can render create admin user page', function () {
    $this->get(AdminUserResource::getUrl('create'))
        ->assertSuccessful();
});

test('it can create admin user', function () {
    Notification::fake();

    $newAdmin = User::factory()->make();

    Livewire::test(CreateAdminUser::class)
        ->fillForm([
            'name' => $newAdmin->name,
            'email' => $newAdmin->email,
            'phone' => '555-123-4567',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name' => $newAdmin->name,
        'email' => $newAdmin->email,
        'phone' => '555-123-4567',
        'role' => UserRole::Admin->value,
        'is_active' => true,
    ]);
});

test('it can render edit admin user page', function () {
    $otherAdmin = User::factory()->admin()->create();

    $this->get(AdminUserResource::getUrl('edit', ['record' => $otherAdmin]))
        ->assertSuccessful();
});

test('it can edit admin user', function () {
    $otherAdmin = User::factory()->admin()->create();

    Livewire::test(EditAdminUser::class, ['record' => $otherAdmin->id])
        ->fillForm([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $otherAdmin->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

test('it can deactivate admin user', function () {
    $otherAdmin = User::factory()->admin()->create(['is_active' => true]);

    Livewire::test(EditAdminUser::class, ['record' => $otherAdmin->id])
        ->callAction('toggle_active')
        ->assertNotified('Admin user deactivated');

    $this->assertDatabaseHas('users', [
        'id' => $otherAdmin->id,
        'is_active' => false,
    ]);
});

test('it can reactivate inactive admin user', function () {
    $otherAdmin = User::factory()->admin()->create(['is_active' => false]);

    Livewire::test(EditAdminUser::class, ['record' => $otherAdmin->id])
        ->callAction('toggle_active')
        ->assertNotified('Admin user activated');

    $this->assertDatabaseHas('users', [
        'id' => $otherAdmin->id,
        'is_active' => true,
    ]);
});

test('it cannot delete own account', function () {
    Livewire::test(EditAdminUser::class, ['record' => $this->admin->id])
        ->assertActionHidden('delete');
});

test('it cannot deactivate own account', function () {
    Livewire::test(EditAdminUser::class, ['record' => $this->admin->id])
        ->assertActionHidden('toggle_active');
});

test('it ensures at least one admin remains active when deactivating', function () {
    Livewire::test(EditAdminUser::class, ['record' => $this->admin->id])
        ->assertActionHidden('toggle_active');

    $otherAdmin = User::factory()->admin()->create(['is_active' => false]);

    Livewire::test(EditAdminUser::class, ['record' => $otherAdmin->id])
        ->callAction('toggle_active');

    $otherAdmin->refresh();
    expect($otherAdmin->is_active)->toBeTrue();
});

test('it prevents deactivating last active admin via toggle action', function () {
    $lastActiveAdmin = User::factory()->admin()->create(['is_active' => true]);

    $this->actingAs($lastActiveAdmin);

    Livewire::test(EditAdminUser::class, ['record' => $this->admin->id])
        ->callAction('toggle_active')
        ->assertNotified('Admin user deactivated');

    $this->admin->refresh();
    expect($this->admin->is_active)->toBeFalse();

    Livewire::test(EditAdminUser::class, ['record' => $lastActiveAdmin->id])
        ->assertActionHidden('toggle_active');
});

test('it only shows users with admin role', function () {
    $otherAdmin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();

    Livewire::test(ListAdminUsers::class)
        ->assertCanSeeTableRecords([$this->admin, $otherAdmin])
        ->assertCanNotSeeTableRecords([$musician]);
});

test('it can filter admin users by active status', function () {
    $activeAdmin = User::factory()->admin()->create(['is_active' => true]);
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false]);

    Livewire::test(ListAdminUsers::class)
        ->filterTable('is_active', true)
        ->assertCanSeeTableRecords([$this->admin, $activeAdmin])
        ->assertCanNotSeeTableRecords([$inactiveAdmin]);

    Livewire::test(ListAdminUsers::class)
        ->filterTable('is_active', false)
        ->assertCanSeeTableRecords([$inactiveAdmin])
        ->assertCanNotSeeTableRecords([$this->admin, $activeAdmin]);
});

test('it can search admin users by name', function () {
    $john = User::factory()->admin()->create(['name' => 'John Admin']);
    $jane = User::factory()->admin()->create(['name' => 'Jane Admin']);

    Livewire::test(ListAdminUsers::class)
        ->searchTable('John')
        ->assertCanSeeTableRecords([$john])
        ->assertCanNotSeeTableRecords([$jane]);
});

test('it can search admin users by email', function () {
    $john = User::factory()->admin()->create(['email' => 'john@admin.com']);
    $jane = User::factory()->admin()->create(['email' => 'jane@admin.com']);

    Livewire::test(ListAdminUsers::class)
        ->searchTable('john@admin.com')
        ->assertCanSeeTableRecords([$john])
        ->assertCanNotSeeTableRecords([$jane]);
});

test('it validates name is required', function () {
    Livewire::test(CreateAdminUser::class)
        ->fillForm([
            'name' => '',
            'email' => 'test@example.com',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('it validates email is required', function () {
    Livewire::test(CreateAdminUser::class)
        ->fillForm([
            'name' => 'Test Admin',
            'email' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'required']);
});

test('it validates email is unique', function () {
    $existingAdmin = User::factory()->admin()->create(['email' => 'existing@admin.com']);

    Livewire::test(CreateAdminUser::class)
        ->fillForm([
            'name' => 'Test Admin',
            'email' => 'existing@admin.com',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('it prevents deleting last active admin', function () {
    $otherAdmin = User::factory()->admin()->create(['is_active' => false]);

    Livewire::test(EditAdminUser::class, ['record' => $otherAdmin->id])
        ->callAction('delete')
        ->assertNotNotified('Cannot delete');

    $this->assertSoftDeleted('users', ['id' => $otherAdmin->id]);
});

test('it can delete admin user when not the last active', function () {
    $otherAdmin = User::factory()->admin()->create(['is_active' => true]);

    Livewire::test(EditAdminUser::class, ['record' => $otherAdmin->id])
        ->callAction('delete');

    $this->assertSoftDeleted('users', ['id' => $otherAdmin->id]);
});

test('it cannot bulk deactivate self', function () {
    $otherAdmin = User::factory()->admin()->create(['is_active' => true]);

    Livewire::test(ListAdminUsers::class)
        ->callTableBulkAction('deactivate', [$this->admin, $otherAdmin])
        ->assertNotified('Cannot deactivate yourself');

    $this->admin->refresh();
    expect($this->admin->is_active)->toBeTrue();

    $otherAdmin->refresh();
    expect($otherAdmin->is_active)->toBeFalse();
});
