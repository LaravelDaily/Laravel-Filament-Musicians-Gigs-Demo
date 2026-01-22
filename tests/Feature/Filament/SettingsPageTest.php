<?php

use App\Filament\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('it can render settings page', function () {
    $this->get(Settings::getUrl())
        ->assertSuccessful();
});

test('it displays current settings values', function () {
    Setting::set('company_name', 'Test Company');
    Setting::set('notification_email', 'notify@test.com');
    Setting::set('timezone', 'America/New_York');

    Livewire::test(Settings::class)
        ->assertSet('company_name', 'Test Company')
        ->assertSet('notification_email', 'notify@test.com')
        ->assertSet('timezone', 'America/New_York');
});

test('it can update company name', function () {
    Livewire::test(Settings::class)
        ->set('company_name', 'New Company Name')
        ->set('timezone', 'UTC')
        ->callAction('save')
        ->assertNotified('Settings saved');

    expect(Setting::get('company_name'))->toBe('New Company Name');
});

test('it can update notification email', function () {
    Livewire::test(Settings::class)
        ->set('company_name', 'Test Company')
        ->set('notification_email', 'new-notify@example.com')
        ->set('timezone', 'UTC')
        ->callAction('save')
        ->assertNotified('Settings saved');

    expect(Setting::get('notification_email'))->toBe('new-notify@example.com');
});

test('it can update timezone', function () {
    Livewire::test(Settings::class)
        ->set('company_name', 'Test Company')
        ->set('timezone', 'America/Los_Angeles')
        ->callAction('save')
        ->assertNotified('Settings saved');

    expect(Setting::get('timezone'))->toBe('America/Los_Angeles');
});

test('it validates company name is required', function () {
    Livewire::test(Settings::class)
        ->set('company_name', '')
        ->set('timezone', 'UTC')
        ->callAction('save')
        ->assertHasErrors(['company_name' => 'required']);
});

test('it validates email format for notification email', function () {
    Livewire::test(Settings::class)
        ->set('company_name', 'Test Company')
        ->set('notification_email', 'not-an-email')
        ->set('timezone', 'UTC')
        ->callAction('save')
        ->assertHasErrors(['notification_email' => 'email']);
});

test('it validates timezone is required', function () {
    Livewire::test(Settings::class)
        ->set('company_name', 'Test Company')
        ->set('timezone', '')
        ->callAction('save')
        ->assertHasErrors(['timezone' => 'required']);
});

test('it allows empty notification email', function () {
    Livewire::test(Settings::class)
        ->set('company_name', 'Test Company')
        ->set('notification_email', null)
        ->set('timezone', 'UTC')
        ->callAction('save')
        ->assertNotified('Settings saved');

    expect(Setting::get('notification_email'))->toBeNull();
});

test('it uses default values when settings not configured', function () {
    Setting::query()->delete();

    Livewire::test(Settings::class)
        ->assertSet('company_name', config('app.name'))
        ->assertSet('timezone', config('app.timezone'));
});

test('it uses settings in portal header', function () {
    Setting::set('company_name', 'Portal Test Company');

    $musician = User::factory()->musician()->create();
    $this->actingAs($musician);

    $this->get(route('portal.dashboard'))
        ->assertSee('Portal Test Company');
});

test('it requires admin authentication', function () {
    $musician = User::factory()->musician()->create();
    $this->actingAs($musician);

    $this->get(Settings::getUrl())
        ->assertForbidden();
});
