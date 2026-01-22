<?php

use App\Enums\UserRole;
use App\Filament\Resources\Musicians\MusicianResource;
use App\Filament\Resources\Musicians\Pages\CreateMusician;
use App\Models\Instrument;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render create musician page', function () {
    $this->get(MusicianResource::getUrl('create'))
        ->assertSuccessful();
});

test('it can create musician with required fields', function () {
    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'role' => UserRole::Musician->value,
    ]);
});

test('it can create musician with all fields including instruments', function () {
    $guitar = Instrument::factory()->create(['name' => 'Guitar']);
    $bass = Instrument::factory()->create(['name' => 'Bass']);
    $region = Region::factory()->create();

    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'instruments' => [$guitar->id, $bass->id],
            'region_id' => $region->id,
            'notes' => 'Great musician',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $musician = User::where('email', 'john@example.com')->first();
    expect($musician)->not->toBeNull()
        ->and($musician->instruments)->toHaveCount(2)
        ->and($musician->region_id)->toBe($region->id)
        ->and($musician->notes)->toBe('Great musician');
});

test('it can create musician with tags', function () {
    $jazz = Tag::factory()->create(['name' => 'Jazz']);
    $rock = Tag::factory()->create(['name' => 'Rock']);

    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'tags' => [$jazz->id, $rock->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $musician = User::where('email', 'john@example.com')->first();
    expect($musician->tags)->toHaveCount(2);
});

test('it can create musician with region', function () {
    $region = Region::factory()->create(['name' => 'Downtown']);

    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'region_id' => $region->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $musician = User::where('email', 'john@example.com')->first();
    expect($musician->region_id)->toBe($region->id);
});

test('it sets role to musician automatically', function () {
    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $musician = User::where('email', 'john@example.com')->first();
    expect($musician->role)->toBe(UserRole::Musician);
});

test('it sets is_active to true by default', function () {
    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $musician = User::where('email', 'john@example.com')->first();
    expect($musician->is_active)->toBeTrue();
});

test('it validates name is required', function () {
    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => '',
            'email' => 'john@example.com',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('it validates email is required', function () {
    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'required']);
});

test('it validates email is unique', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'existing@example.com',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('it validates email format', function () {
    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'not-an-email',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'email']);
});

test('it sends welcome email to new musician', function () {
    Notification::fake();

    Livewire::test(CreateMusician::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $musician = User::where('email', 'john@example.com')->first();

    Notification::assertSentTo($musician, ResetPassword::class);
});
