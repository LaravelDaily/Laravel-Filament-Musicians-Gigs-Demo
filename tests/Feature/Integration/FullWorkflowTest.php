<?php

use App\Enums\AssignmentStatus;
use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\GigResource;
use App\Filament\Resources\Gigs\Pages\CreateGig;
use App\Filament\Resources\Gigs\Pages\EditGig;
use App\Filament\Resources\Gigs\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\Musicians\Pages\EditMusician;
use App\Models\Gig;
use App\Models\GigAssignment;
use App\Models\Instrument;
use App\Models\User;
use App\Notifications\GigAssignmentDeclined;
use App\Notifications\SubOutRequested;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('it completes full gig creation and assignment workflow', function () {
    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    $instrument = Instrument::factory()->create(['name' => 'Drums']);
    $musician->instruments()->attach($instrument);

    // Step 1: Admin creates a gig
    $this->actingAs($admin);

    Livewire::test(CreateGig::class)
        ->fillForm([
            'name' => 'Corporate Event',
            'date' => now()->addWeeks(2)->format('Y-m-d'),
            'call_time' => '17:00',
            'venue_name' => 'Grand Ballroom',
            'venue_address' => '123 Event Center Drive',
            'status' => GigStatus::Active,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $gig = Gig::where('name', 'Corporate Event')->first();
    expect($gig)->not->toBeNull()
        ->and($gig->status)->toBe(GigStatus::Active);

    // Step 2: Admin assigns musician to gig
    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->callTableAction('create', data: [
            'user_id' => $musician->id,
            'instrument_id' => $instrument->id,
            'notes' => 'Bring extra equipment',
        ])
        ->assertHasNoTableActionErrors();

    // Verify assignment was created with pending status
    $assignment = GigAssignment::where('gig_id', $gig->id)
        ->where('user_id', $musician->id)
        ->first();

    expect($assignment)->not->toBeNull()
        ->and($assignment->status)->toBe(AssignmentStatus::Pending)
        ->and($assignment->instrument_id)->toBe($instrument->id);

    // Step 3: Verify assignment shows in musician's portal
    $this->actingAs($musician);

    $response = $this->get(route('portal.dashboard'));
    $response->assertOk()
        ->assertSee('Corporate Event')
        ->assertSee('Grand Ballroom');
});

test('it completes musician accept workflow', function () {
    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create(['name' => 'Jane Smith']);
    $instrument = Instrument::factory()->create(['name' => 'Bass']);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Wedding Reception',
        'venue_name' => 'Rose Garden',
    ]);
    $assignment = GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    // Step 1: Musician logs in and sees gig in dashboard
    $this->actingAs($musician);

    $dashboardResponse = $this->get(route('portal.dashboard'));
    $dashboardResponse->assertOk()
        ->assertSee('Wedding Reception');

    // Step 2: Musician views gig detail
    $detailResponse = $this->get(route('portal.gigs.show', $gig));
    $detailResponse->assertOk()
        ->assertSee('Wedding Reception')
        ->assertSee('Rose Garden')
        ->assertSee('Accept')
        ->assertSee('Decline');

    // Step 3: Musician accepts the gig
    $acceptResponse = $this->post(route('portal.gigs.accept', $gig));
    $acceptResponse->assertRedirect(route('portal.gigs.show', $gig))
        ->assertSessionHas('success');

    // Verify assignment status changed
    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::Accepted)
        ->and($assignment->responded_at)->not->toBeNull();

    // Step 4: Admin sees accepted status in admin panel
    $this->actingAs($admin);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee('Accepted')
        ->assertSee('Jane Smith');
});

test('it completes musician decline workflow with notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create(['name' => 'Mike Johnson']);
    $instrument = Instrument::factory()->create(['name' => 'Guitar']);
    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Summer Festival',
        'date' => now()->addDays(14),
    ]);
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    // Step 1: Musician logs in
    $this->actingAs($musician);

    // Step 2: Musician views gig
    $this->get(route('portal.gigs.show', $gig))
        ->assertOk()
        ->assertSee('Summer Festival');

    // Step 3: Musician declines with reason
    $this->post(route('portal.gigs.decline', $gig), [
        'reason' => 'Schedule conflict with another booking',
    ])
        ->assertRedirect(route('portal.gigs.show', $gig))
        ->assertSessionHas('success');

    // Step 4: Verify assignment status changed
    $assignment = GigAssignment::where('gig_id', $gig->id)
        ->where('user_id', $musician->id)
        ->first();

    expect($assignment->status)->toBe(AssignmentStatus::Declined)
        ->and($assignment->decline_reason)->toBe('Schedule conflict with another booking');

    // Step 5: Verify notification sent to admin
    Notification::assertSentTo($admin, GigAssignmentDeclined::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains($mail->subject, 'Summer Festival')
            && str_contains(implode(' ', $mail->introLines), 'Mike Johnson')
            && str_contains(implode(' ', $mail->introLines), 'Schedule conflict with another booking');
    });
});

test('it completes sub-out request workflow with notification', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $originalMusician = User::factory()->musician()->create(['name' => 'Sarah Wilson']);
    $replacementMusician = User::factory()->musician()->create(['name' => 'Tom Davis']);
    $drums = Instrument::factory()->create(['name' => 'Drums']);

    $originalMusician->instruments()->attach($drums);
    $replacementMusician->instruments()->attach($drums);

    $gig = Gig::factory()->active()->future()->create([
        'name' => 'Annual Gala',
        'date' => now()->addDays(7),
    ]);

    $assignment = GigAssignment::factory()->accepted()->create([
        'user_id' => $originalMusician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $drums->id,
    ]);

    // Step 1: Musician logs in and requests sub-out
    $this->actingAs($originalMusician);

    $this->get(route('portal.gigs.show', $gig))
        ->assertOk()
        ->assertSee('Request Sub-out');

    $this->post(route('portal.gigs.subout', $gig), [
        'reason' => 'Family emergency - need replacement urgently',
    ])
        ->assertRedirect(route('portal.gigs.show', $gig))
        ->assertSessionHas('success');

    // Step 2: Verify assignment status changed
    $assignment->refresh();
    expect($assignment->status)->toBe(AssignmentStatus::SuboutRequested)
        ->and($assignment->subout_reason)->toBe('Family emergency - need replacement urgently');

    // Step 3: Verify urgent notification sent to admin
    Notification::assertSentTo($admin, SubOutRequested::class, function ($notification) {
        $mail = $notification->toMail($notification->assignment->user);

        return str_contains($mail->subject, 'URGENT:')
            && str_contains($mail->subject, 'Annual Gala')
            && str_contains(implode(' ', $mail->introLines), 'Sarah Wilson')
            && str_contains(implode(' ', $mail->introLines), 'Family emergency - need replacement urgently');
    });

    // Step 4: Admin sees sub-out in admin panel and assigns replacement
    $this->actingAs($admin);

    Livewire::test(AssignmentsRelationManager::class, [
        'ownerRecord' => $gig,
        'pageClass' => EditGig::class,
    ])
        ->assertSee('Sub-out Requested')
        ->assertSee('Sarah Wilson')
        ->callTableAction('create', data: [
            'user_id' => $replacementMusician->id,
            'instrument_id' => $drums->id,
            'notes' => 'Replacement for Sarah Wilson',
        ])
        ->assertHasNoTableActionErrors();

    // Step 5: Verify replacement assignment created
    $replacementAssignment = GigAssignment::where('gig_id', $gig->id)
        ->where('user_id', $replacementMusician->id)
        ->first();

    expect($replacementAssignment)->not->toBeNull()
        ->and($replacementAssignment->status)->toBe(AssignmentStatus::Pending)
        ->and($replacementAssignment->instrument_id)->toBe($drums->id);
});

test('it enforces deactivated musician cannot login', function () {
    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create([
        'is_active' => true,
        'email' => 'active.musician@example.com',
    ]);

    // Step 1: Verify musician can access portal when active
    $this->actingAs($musician);
    $this->get(route('portal.dashboard'))->assertOk();
    $this->post(route('logout'));

    // Step 2: Admin deactivates musician
    $this->actingAs($admin);

    Livewire::test(EditMusician::class, ['record' => $musician->id])
        ->callAction('toggle_active')
        ->assertHasNoActionErrors();

    $musician->refresh();
    expect($musician->is_active)->toBeFalse();

    // Step 3: Attempt to login as deactivated musician
    $this->post(route('logout'));

    $this->post(route('login.store'), [
        'email' => 'active.musician@example.com',
        'password' => 'password',
    ]);

    // Step 4: Verify deactivated musician is blocked from accessing portal
    $response = $this->get(route('portal.dashboard'));
    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('it enforces role-based access throughout workflow', function () {
    $admin = User::factory()->admin()->create();
    $musician = User::factory()->musician()->create();
    $otherMusician = User::factory()->musician()->create();
    $instrument = Instrument::factory()->create();
    $gig = Gig::factory()->active()->future()->create(['name' => 'Private Event']);
    GigAssignment::factory()->pending()->create([
        'user_id' => $musician->id,
        'gig_id' => $gig->id,
        'instrument_id' => $instrument->id,
    ]);

    // Test 1: Musician cannot access admin panel
    $this->actingAs($musician);
    $this->get('/admin')->assertForbidden();
    $this->get(GigResource::getUrl('index'))->assertForbidden();
    $this->get(GigResource::getUrl('create'))->assertForbidden();

    // Test 2: Musician cannot view other musician's gig assignment
    $this->actingAs($otherMusician);
    $this->get(route('portal.gigs.show', $gig))->assertForbidden();
    $this->post(route('portal.gigs.accept', $gig))->assertForbidden();
    $this->post(route('portal.gigs.decline', $gig))->assertForbidden();

    // Test 3: Admin cannot access musician portal
    $this->actingAs($admin);
    $this->get('/portal')->assertForbidden();

    // Test 4: Unauthenticated user cannot access any protected routes
    $this->post(route('logout'));
    $this->get('/admin')->assertRedirect(route('filament.admin.auth.login'));
    $this->get('/portal')->assertRedirect(route('login'));
    $this->get(route('portal.gigs.show', $gig))->assertRedirect(route('login'));

    // Test 5: Musician can only interact with their own assignments
    $this->actingAs($musician);
    $this->get(route('portal.gigs.show', $gig))->assertOk();
    $this->post(route('portal.gigs.accept', $gig))->assertRedirect();

    // Verify assignment was accepted
    $assignment = GigAssignment::where('gig_id', $gig->id)
        ->where('user_id', $musician->id)
        ->first();
    expect($assignment->status)->toBe(AssignmentStatus::Accepted);
});
